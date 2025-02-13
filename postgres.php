<?php 

// show php errors
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// pg connection
$host = getenv('PG_HOST');
$port = getenv('PG_PORT');
$dbname = getenv('PG_DBNAME');
$username = getenv('PG_USERNAME');
$password = getenv('PG_PASSWORD');

// Conexión a la base de datos
try {
    // verificar si ya existe una conexión
    if (isset($conexion)) {
        pg_close($conexion);
    }
  
    $conexion = pg_connect("host=$host port=$port dbname=$dbname user=$username password=$password");
} catch (Exception $e) {
    error_log($e->getMessage());
    echo "Error al conectar a la base de datos.";
}


// Verificar si se ha especificado un ID
if (isset($_GET) && isset($_GET['endpoint']) && $_GET['endpoint'] == 'guardarEnCRM') {

    $itineraries = $_GET['itineraries'];

    $daysOfWeek = [
        'LU' => 'next monday',
        'MA' => 'next tuesday',
        'MI' => 'next wednesday',
        'JU' => 'next thursday',
        'VI' => 'next friday',
        'SA' => 'next saturday'
    ];

    // if today is a weekday, then use today instead of next weekday
    $today = date('D');
    $daysOfWeekMap = [
        'Mon' => 'LU',
        'Tue' => 'MA',
        'Wed' => 'MI',
        'Thu' => 'JU',
        'Fri' => 'VI',
        'Sat' => 'SA'
    ];

    if (array_key_exists($today, $daysOfWeekMap)) {
        $daysOfWeek[$daysOfWeekMap[$today]] = 'today';
    }

    // Insert all itineraries using a single query, and update if already exists
    $values = [];
    foreach ($itineraries as $itinerary) {
        $itinerary = json_decode($itinerary, true);
        $lastThree = substr($itinerary['PREVENDEDOR'], -3);
        $vctext = "CALLCENTER " . $lastThree;

        foreach ($daysOfWeek as $day => $nextDay) {
            if ($itinerary[$day] == '1') {
                $date = date('Y-m-d', strtotime($nextDay));
                $kunnr = pg_escape_string($itinerary['CODIGO']);
                $route = pg_escape_string($itinerary['RUTA']);
                $vroute = pg_escape_string($itinerary['PREVENDEDOR']);
                $orden = pg_escape_string($itinerary['ORDEN_' . $day]);
                $values[] = "('$kunnr', '$route', '$date', '$vctext', '$vroute', '$orden')";
            }
        }
    }

    if (!empty($values)) {
        $query = "MERGE INTO \"visitas-posicion\" AS target
                  USING (VALUES " . implode(', ', $values) . ") AS source (kunnr, route, exdat1, vctext, vroute, sequ)
                  ON target.kunnr = source.kunnr AND target.exdat1 = Date(source.exdat1) AND target.route = source.route
                  WHEN MATCHED THEN
                    UPDATE SET vctext = source.vctext, vroute = source.vroute, sequ = source.sequ
                  WHEN NOT MATCHED THEN
                    INSERT (kunnr, route, exdat1, vctext, vroute, sequ)
                    VALUES (source.kunnr, source.route, Date(source.exdat1), source.vctext, source.vroute, source.sequ);";

        $result = pg_query($conexion, $query);
        
        //print_r($query);
        //die();

        if ($result) {
            echo json_encode(array('exito' => 1));
        } else {
            echo json_encode(array('exito' => 0));
        }
    } else {
        echo json_encode(array('exito' => 0, 'mensaje' => 'No itineraries to process.'));
    }

} else if (isset($_GET) && isset($_GET['endpoint']) && $_GET['endpoint'] == 'updateClientDetails') {
    
    if (isset($_GET['kunnr']) && isset($_GET['field']) && isset($_GET['value'])) {
        $kunnr = pg_escape_string($_GET['kunnr']);
        $field = pg_escape_string($_GET['field']);
        $value = pg_escape_string($_GET['value']);

        //$query = "UPDATE param_clientes SET {$field} = '{$value}' WHERE id_cliente = '{$kunnr}'";
        $query = "MERGE INTO param_clientes AS target
                  USING (VALUES ('$kunnr', '$value')) AS source (kunnr, value)
                  ON target.id_cliente = source.kunnr
                  WHEN MATCHED THEN
                    UPDATE SET $field = source.value
                  WHEN NOT MATCHED THEN
                    INSERT (id_cliente, $field)
                    VALUES (source.kunnr, source.value);";
        $result = pg_query($conexion, $query);

        if ($result) {
            echo json_encode(array('exito' => 1));
        } else {
            $error_msg = pg_last_error($conexion);
            error_log($error_msg);
            echo json_encode(array('exito' => 0, 'mensaje' => $error_msg));
        }
    } else {
        echo json_encode(array('exito' => 0, 'mensaje' => 'Parámetros insuficientes.'));
    }

} else {
    echo "Error: No se ha especificado un ID.";
}

// Cerrar la conexión a la base de datos
pg_close($conexion);

?>