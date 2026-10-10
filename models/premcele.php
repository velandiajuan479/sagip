<?php
$connPath = file_exists('models/conexion.php') ? 'models/conexion.php' : (file_exists('conexion.php') ? 'conexion.php' : '../models/conexion.php');
require_once $connPath;

$velePath = file_exists('models/premvele.php') ? 'models/premvele.php' : (file_exists('premvele.php') ? 'premvele.php' : '../models/premvele.php');
require_once $velePath;

if (!class_exists('Mcele')) {
    class Mcele extends Mele {}
}
