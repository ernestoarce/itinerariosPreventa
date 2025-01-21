<?php 

/**
* 
*/
class PHPRequest
{
	public $c1;
	
	function __construct($ruta,$usuario,$password)
	{
		try {
			$this->c1 = new PDO($ruta . ';charset=UTF8',$usuario,$password);
			$this->c1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}catch (Exception $e) {
			echo json_encode(array('exito'=>'0', 'mensaje'=>'error de conexion'));
		}//catch
	}

function query($query)
	{	
		try {
			$encode = array();
			$cantidad = $this->cantidadFilas($query);
			$resultado = $this->c1->query($query,PDO::FETCH_ASSOC);
			if($cantidad!='0' && $cantidad!='-1'){
				while($valor=$resultado->fetch()) {
					//$encode[]=$this->utf8ize($valor);
					array_push($encode, $valor);
				}//while
			}
			if (sizeof($encode)==0) {
				$encode = array("exito"=>0,'mensaje'=>'no encontraron registros');
				$encode = '';
			}
			$enconde = $this->utf8ize($encode);
			
	 		return json_encode($encode,JSON_UNESCAPED_UNICODE);
 		} catch (Exception $e) {
 			$encode = array("exito"=>0,'mensaje'=>'error:'.$e->getMessage());
 			return json_encode($encode);
 		}
	}

function queryBinding($query,$parametros)
	{	
		try {
 			$encode = array();
	 		$cantidad = $this->cantidadFilas($query);
	 		$resultado = $this->c1->prepare($query);
	 		$resultado->bindValue(':codigo', 'DET005');
	 		$resultado->execute();
	 		if($cantidad!='0' && $cantidad!='-1'){
	 		while($valor=$resultado->fetch(PDO::FETCH_ASSOC)) {
	 			$encode[]=$this->utf8ize($valor);
	 		}//while
	 		}
	 		if (sizeof($encode)==0) {
	 			$encode = array("exito"=>0,'mensaje'=>'no encontraron registros');
	 		}
	 		$enconde = $this->utf8ize($encode);
	 		return json_encode($encode,JSON_PRETTY_PRINT);
 		} catch (Exception $e) {
 			$encode = array("exito"=>0,'mensaje'=>'error:'.$e->getMessage());
 			return json_encode($encode);
 		}

	}


public function cantidadFilas($query)
{
	$cantidad = $this->c1->query($query);
	return $cantidad->fetchColumn();
}


function insertaConTransaccion($jsonVar,$tabla)
 	{	 
 		try {
 			$tmp="";
 			$llave_array=array();
			$valor_array=array();
 			$error=0;
	 		$this->c1->beginTransaction();
			foreach ($jsonVar as $llave => $valor) {//recorre cada fila del json

			foreach ($valor as $key => $value) {//recorre cada columna
				if (!is_numeric($key)) {
					$val="";
					 if (is_numeric($value) and $key!="ID" and $key!="DOCUMENTO") {
						$val = "$value";
					}else if ($this->validateDate($value,'Y-m-d h:i:s a')) {
						$val="to_date('$value','yyyy-mm-dd hh:mi:ss am')";
					}else{
						$val="'$value'";
					}

					if ($key=="LONGITUD" || $key=="LATITUD") {
							$val="'$value'";
					}
					

				array_push($llave_array, $key);
				array_push($valor_array, $val);
					}

			}
			//$validacion=0;
			//echo "insert into ".$tabla." (".implode(',', $llave_array).") values (".implode(',', $valor_array).")";
			$validacion= $this->c1->exec("insert into ".$tabla." (".implode(',', $llave_array).") values (".implode(',', $valor_array).")");
			if ($validacion==0) {$error++;}
			$llave_array=array();
			$valor_array=array();
			//var_dump($llave_array);
			//var_dump($valor_array);
 		}//fin de foreach

 		if($error>0){
 			$this->c1->rollback();
 			return json_encode(array('exito'=>0,'mensaje'=>'error:'.$e->getMessage()));
 		}else if ($error==0) {
 			$this->c1->commit();
 			return json_encode(array('exito'=>1));
 		}else{return json_encode(array('exito'=>0));}
 		} catch (Exception $e) {
 			echo 'error:'.$e->getMessage();
 			return json_encode(array('exito'=>0,'mensaje'=>'error:'.$e->getMessage()));
 		}

 	}//fin de insertaConTransaccion

function insertaConTransaccionPorQuery($querys)
 	{	
 		try {
 			$error=0;
	 		$this->c1->beginTransaction();
			foreach ($querys as $valor) {
			$validacion= $this->c1->exec($valor);
			if ($validacion==0) {$error++;}
				
 			}//fin de foreach

 		if($error>0){
 			$this->c1->rollback();
 			return json_encode(array('exito'=>0));
 		}else if ($error==0) {

 			$this->c1->commit();
 			return json_encode(array('exito'=>1));
 		}else{return json_encode(array('exito'=>0));}
 		} catch (Exception $e) {
 			echo $e->getMessage();
 			return json_encode(array('exito'=>0,'mensaje'=>'error:'.$e->getMessage()));
 		}


 	}//fin de insertaConTransaccion


//auxiliares
function ConvertirADecimal($numero)
{
if (preg_match('/,/',$numero)){	
  $numero=explode(',',$numero);
  if($numero[0]==''){
    $numero[0]=0;
  }
  if (!isset($numero[1])) {
  	$numero[1]=0;
  }
  $numero= $numero[0].'.'.$numero[1];
  //$numero = round($numero,2);
  }
  return $numero;  
}

function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = $this->utf8ize($v);
            }
        } else if (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
    }

}



 ?>