<?php 

//SV
#$usuario="HH";
#$password="HHLACTOSA";
#$ruta="oci:dbname=//10.10.4.9:1521/lactosa";

// FROM ENV
$usuario = getenv('USER');
$password = getenv('PASS');
$ruta = getenv('ROUTE');

?>