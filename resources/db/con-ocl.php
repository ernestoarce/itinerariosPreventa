<?php 

include 'CORS.php';
include 'PHPRequest.php';
include 'APIRequest.php';
include 'credenciales.php';

$ora = new PHPRequest($ruta,$usuario,$password);
// $ora = new APIRequest();


?>