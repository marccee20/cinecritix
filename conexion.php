<?php

$server = 'localhost';
$user = 'root';
$pass ='';
$db = 'peliculas_proyecto';

$conexion = new  mysqli($server, $user, $pass, $db);
if($conexion->connect_errno){
    die("error de conexion:" . $conexion->connect_errno);
}

?>