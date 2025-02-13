<?php 

include 'CORS.php';
include 'PHPRequest.php';
include 'APIRequest.php';
include 'credenciales.php';

$ruta = getenv('ROUTE');
$usuario = getenv('USER');
$password = getenv('PASS');

$ora = new PHPRequest($ruta,$usuario,$password);
// $ora = new APIRequest();

?>