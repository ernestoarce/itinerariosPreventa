<?php
    
include 'resources/db/con-ocl.php';
session_start();

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

if (isset($_GET) && $_GET['pass'] == 'getVirtualSellers') {
    
    $query = "SELECT * FROM HH.VENDEDORES_VIRTUALES ORDER BY ID";
    $virtualSellers = json_decode( $ora->query($query), true );

    $SAP_API = getenv('SAP_API');

    $return = array();
    array_push($return, $virtualSellers);
    array_push($return, $SAP_API);

    print_r(json_encode($return));

} else if (isset($_GET) && $_GET['pass'] == 'setVirtualItinerary') {

    $code = $_GET['code'];
    $day = $_GET['day'];
    $seller = $_GET['day'] == 'PREVENDEDOR' ? "'{$_GET['virtualSeller']}'" : $_GET['virtualSeller'];
    $seller = ($seller == 'true') ? 1 : (($seller == 'false') ? 0 : $seller);
    $ruta = $_GET['ruta'];

    // Verificando si existe registro para el codigo
    $query = "SELECT COUNT(*) AS CANT FROM HH.VENDEDORES_ITINERARIO_VIRTUAL WHERE CODIGO = '{$code}'";
    @$cant = json_decode( $ora->query($query), true )[0]['CANT'];
    // print_r($cant);

    // Si existe registro, se actualiza
    $queryTransaction = array();
    if ($cant > 0) {
        $query = "UPDATE HH.VENDEDORES_ITINERARIO_VIRTUAL SET {$day} = {$seller}, RUTA = '{$ruta}' WHERE CODIGO = '{$code}'";
        array_push($queryTransaction, $query);
    } else {
        $query = "INSERT INTO HH.VENDEDORES_ITINERARIO_VIRTUAL (CODIGO, {$day}, RUTA) VALUES ('{$code}', {$seller}, '{$ruta}')";
        array_push($queryTransaction, $query);
    }

    // Ejecutando transaccion
    $result = json_decode($ora->insertaConTransaccionPorQuery($queryTransaction), true);
    // $result = '';

    $resultsArray = array();
    array_push($resultsArray, $result);
    array_push($resultsArray, $cant);
    array_push($resultsArray, $queryTransaction);
    print_r(json_encode($resultsArray));

} else if (isset($_GET) && $_GET['pass'] == 'getItinerary') {
    
    $rute = $_GET['rute'] ?? 'TODOS';
    $whereClauses = array();

    if ($rute !== 'TODOS') {
        $whereClauses[] = "RUTA = '{$rute}'";
    }

    $where = !empty($whereClauses) ? "WHERE " . implode(' AND ', $whereClauses) : "";

    $query = "SELECT * FROM HH.VENDEDORES_ITINERARIO_VIRTUAL {$where}";
    $itinerary = json_decode($ora->query($query), true);
    echo json_encode($itinerary);

} else if (isset($_GET) && $_GET['pass'] == 'getClients') { 
    $rute = $_GET['rute'];
    $where = "";
    if ($rute != 'TODOS'){
        $where = "WHERE GESTOR = '{$rute}'";
    }

    $query = "SELECT CODIGO KUNNR, NOMBRE NAME1, DIR3 NAME2, GESTOR SORTL, DIRECCION STRAS, FHONE TELF1 FROM sysadm.vcliente {$where}";
    // print_r($query);
    // die();
    $clients = json_decode( $ora->query($query), true );
    print_r(json_encode($clients));

} else if (isset($_GET) && $_GET['pass'] == 'setOrder') {

    $kunnr = $_GET['kunnr'];
    $field = $_GET['field'];
    $value = $_GET['value'];

    $query = "SELECT COUNT(*) AS CANT FROM HH.VENDEDORES_ITINERARIO_VIRTUAL WHERE CODIGO = '{$kunnr}'";
    @$cant = json_decode( $ora->query($query), true )[0]['CANT'];

    $queryTransaction = array();
    if ($cant > 0) {
        $query = "UPDATE HH.VENDEDORES_ITINERARIO_VIRTUAL SET {$field} = '{$value}' WHERE CODIGO = '{$kunnr}'";
        array_push($queryTransaction, $query);
    } else {
        $query = "INSERT INTO HH.VENDEDORES_ITINERARIO_VIRTUAL (CODIGO, {$field}) VALUES ('{$kunnr}', '{$value}')";
        array_push($queryTransaction, $query);
    }

    $result = json_decode($ora->insertaConTransaccionPorQuery($queryTransaction), true);
    print_r(json_encode($result));
} else if (isset($_GET) && $_GET['pass'] == 'getAllItineraries') {

    $query = "SELECT * FROM HH.VENDEDORES_ITINERARIO_VIRTUAL WHERE PREVENDEDOR LIKE 'TEL%'";
    $itineraries = json_decode( $ora->query($query), true );
    print_r(json_encode($itineraries));
}

?>