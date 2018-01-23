<?php

// Notificar todos los errores de PHP (ver el registro de cambios)
error_reporting(E_ALL);

// If you installed via composer, just use this code to requrie autoloader on the top of your projects.
require 'vendor/autoload.php';

// Using Medoo namespace
use Medoo\Medoo;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$inputFileName = 'bienes.xlsx';
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$spreadsheet = $reader->load($inputFileName);

$ids = [];

// 8216
for ($i=1; $i < 8216; $i++) {

  $id = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
  $nombre = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();

  $nombre = mb_convert_case($nombre, MB_CASE_TITLE, "UTF-8");

  // Creamos el array
  $ids[$id] = $nombre;

  // Quitamos si contiene la palabra "Máquina" + número
  if (strpos($nombre, 'Máquina') !== false) {

    $trozos = explode(" ", $nombre);

    // Sacamos la segunda palabra (para ver si es un numero)
    if (isset($trozos[1])) {

      // Sacamos los primeros chars para ver si es un numero (porque aveces acaban en letra)
      $primer_char = substr($trozos[1], 0, 2);

      if (is_numeric($primer_char)) {
        unset($ids[$id]);
      }
    }
  }

  // Quitamos nombres con interrogantes
  if (strpos($nombre, '?') !== false) {
    unset($ids[$id]);
  }
}



// Initialize
$db = new Medoo([
    // 'driver' => 'mysql',
    'database_type' => 'mysql',
    'database_name' => 'espases_db',
    'server' => 'localhost',
    'charset' => 'utf8',
    'username' => 'root',
    'password' => ''
]);

echo "<pre>";

foreach ($ids as $id => $nombre) {

  //  AND `name` LIKE '%Maquina%'
  $item = $db->query("SELECT id, name FROM `items` WHERE `id` = $id")->fetch();

  if ($item) {

    $nombre_prod = $item['name'];

    if (strpos($nombre_prod, 'Máquina') !== false) {

      $trozos = explode(" ", $nombre_prod);

      // Sacamos la segunda palabra (para ver si es un numero)
      if (isset($trozos[1])) {

        // Sacamos los primeros chars para ver si es un numero (porque aveces acaban en letra)
        $primer_char = substr($trozos[1], 0, 2);

        if (is_numeric($primer_char)) {

          $nombre = trim($nombre);

          $data = $db->update("items", [ "name" => $nombre ], [
            "id" => $id
          ]);

          if ($data->rowCount()) { echo $nombre_prod . " > " . $nombre . "<br>"; }
          else { echo "-<b>" . $nombre_prod . "</b> ok 1.$id<br>"; }

        } else {
          echo "-<b>" . $nombre_prod . "</b> ok 2.$id<br>";
        }
      } else {
        echo "-<b>" . $nombre_prod . "</b> ok 3.$id<br>";
      }
    } else {
      echo "-<b>" . $nombre_prod . "</b> ok $id<br>";
    }


  }


} // End foreach

exit();
