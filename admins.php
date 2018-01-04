<?php

// Notificar todos los errores de PHP (ver el registro de cambios)
error_reporting(E_ALL);

// If you installed via composer, just use this code to requrie autoloader on the top of your projects.
require 'vendor/autoload.php';
require 'lang.php'; // Donde hemos metido los nombres traducidos que teníamos en wopap

// Using Medoo namespace
use Medoo\Medoo;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;


// Initialize
$db = new Medoo([
    // 'driver' => 'mysql',
    'database_type' => 'mysql',
    'database_name' => 'c0aplicacion',
    'server' => 'localhost',
    'charset' => 'utf8',
    'username' => 'root',
    'password' => ''
]);

$users = $db->select("administrador", "login");


$client = new \GuzzleHttp\Client();
$base_url = "http://localhost/API2/public/api/";
$general_pass = "123456";

if ($users) {
  foreach ($users as $u) {


  } // Fin foreach de $users
} // Fin if $users
