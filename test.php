<?php

// Notificar todos los errores de PHP (ver el registro de cambios)
error_reporting(E_ALL);

// If you installed via composer, just use this code to requrie autoloader on the top of your projects.
require 'vendor/autoload.php';

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

// selects
$select = '
  tasador.id_tasador as id,
  usuario.id_usuario as id_usuario,
  login as alias,
  tasador as alias2,
  nombre,
  1apellido as ape1,
  2apellido as ape2,
  email as email_user,
  password,
  fecha,
  baja,
  correo2 as email,
  dni as nif,
  direccion,
  codigo_postal as cp,
  localidad,
  provincia,
  telefono2 as telefono,
  telefono_movil,
  fax,
  titulacion,
  especialidad,
  modo_facturacion,
  nombre_empresa_facturacion as billing_name,
  cif_empresa_facturacion as billing_cif,
  fax_empresa_facturacion as billing_fax,
  correo_empresa_facturacion as billing_email,
  direccion_empresa_facturacion as billing_address,
  telefono_empresa_facturacion as billing_phone,
  fax_oficina as business_fax,
  correo_electronico_oficina as business_email,
  direccion_oficina as business_address,
  localidad_oficina as business_location,
  telefono_oficina as business_phone,
  zona_actuacion as zones
';

$users = $db->query("SELECT $select FROM `usuario`
  inner join `tasador` on `usuario`.`id_usuario` = `tasador`.`id_usuario`
  inner join `infinterna` on `tasador`.`id_tasador` = `infinterna`.`id_tasador`
  inner join `infpersonal` on `tasador`.`id_tasador` = `infpersonal`.`id_tasador`
  inner join `infempresa` on `tasador`.`id_tasador` = `infempresa`.`id_tasador`"
)->fetchAll();

if ($users) {

  // [id] => 1
  // [id_usuario] => 1
  // [alias] => AAV
  // [alias2] => AAV
  // [nombre] => Carlos
  // [ape1] => Aznares
  // [ape2] => Varela
  // [email_user] =>
  // [password] => 98dae0e08c01f9e64dc3f9650eb5a714
  // [fecha] =>
  // [baja] => 1
  // [email] =>
  // [nif] =>
  // [direccion] =>
  // [cp] =>
  // [localidad] =>
  // [provincia] =>
  // [telefono] =>
  // [telefono_movil] =>
  // [fax] =>
  // [titulacion] =>
  // [especialidad] =>
  // [modo_facturacion] =>
  // [billing_name] =>
  // [billing_cif] =>
  // [billing_fax] =>
  // [billing_email] =>
  // [billing_address] =>
  // [billing_phone] =>
  // [business_fax] =>
  // [business_email] =>
  // [business_address] =>
  // [business_location] =>
  // [business_phone] =>
  // [zones] =>


  foreach ($users as $u) {
    $phone = "";
    if ($u['telefono_movil']) { $phone = $u['telefono_movil']; }
    if (!$phone && $u['telefono']) { $phone = $u['telefono']; }
    if (!$phone && $u['billing_phone']) { $phone = $u['billing_phone']; }
    if (!$phone && $u['business_phone']) { $phone = $u['business_phone']; }

    if ($u['baja']) { echo "baja - "; }
    else { echo "ALTA - "; }

    if ($u['modo_facturacion'] == "Autónomo" || $u['modo_facturacion'] == "Empresa") { echo "externo<br>"; }
    else { echo "interno<br>"; }

    echo "<h4>" . $u['alias'] . " " . $u['nombre'] . "</h4>";
    echo $u['telefono_movil'] ? $u['telefono_movil'] . "<br>" : "";
    echo $u['telefono'] ? $u['telefono'] . "<br>" : "";
    echo $u['billing_phone'] ? $u['billing_phone'] . "<br>" : "";
    echo $u['business_phone'] ? $u['business_phone'] . "<br>" : "";
    echo "<b>" . $phone . "</b><br>";
  }
  echo "<pre>";
  print_r($users);
  exit();

  $client = new \GuzzleHttp\Client();
  $base_url = "http://localhost/WoPapAPI2/public/api/";
  $general_pass = "123456";


  foreach ($users as $u) {

    if ($u['email_user']) {

      $email = "";
      $user = "";
      $account = "";

      try {
        $res = $client->request('POST', $base_url.'v1/uniqueEmail', [
          'form_params' => [
            'email' => trim($u['email_user'])
          ]
        ]);

        if ($res->getStatusCode() == "200") {

          $data = json_decode($res->getBody());

          if ($data->success && $data->message) {
            $email = trim($u['email_user']);
          }
        }
      }
      catch (RequestException $e) {
        // if ($e->hasResponse()) {
        //   echo Psr7\str($e->getResponse());
        // } else {
        //   echo Psr7\str($e->getRequest());
        // }
      }


      // Si no existe el mail en la base de datos creamos nuevo usuario
      if ($email) {

        // Sacamos un telefono
        $phone = "";
        if ($u['telefono_movil']) { $phone = $u['telefono_movil']; }
        if (!$phone && $u['telefono']) { $phone = $u['telefono']; }
        if (!$phone && $u['billing_phone']) { $phone = $u['billing_phone']; }
        if (!$phone && $u['business_phone']) { $phone = $u['business_phone']; }

        // Si tiene fecha de alta, se la guardamos en creacion
        if ($u['fecha'] && $u['fecha'] != "0000-00-00") {
          $created_at = date_create($u['fecha'])->format("Y-m-d H:i:s");
        } else {
          $created_at = date("Y-m-d H:i:s");
        }

        try {
          $res = $client->request('POST', $base_url.'v1/signupDelfos', [
            'form_params' => [
              'delfos' => 1,
              'first_name' => trim($u['nombre']),
              'last_name' => trim($u['ape1'] . " " . $u['ape2']),
              'telephone' => $phone,
              'email' => $email,
              'password' => $general_pass,
              'created_at' => $created_at
            ]
          ]);

          if ($res->getStatusCode() == "200") {

            $data = json_decode($res->getBody());

            echo "<pre>";
            print_r($data);
            exit();

            if ($data->success && isset($data->data->user) && $data->data->user) {

              $user = $data->data->user;
            }
          }
        }
        catch (RequestException $e) {
          // if ($e->hasResponse()) {
          //   echo Psr7\str($e->getResponse());
          // } else {
          //   echo Psr7\str($e->getRequest());
          // }
        }


        // Si hemos creado el user con exito, vamos a sacar su account
        if ($user) {

          try {
            $res = $client->request('POST', $base_url.'v1/login', [
              'form_params' => [
                'email' => $user->email,
                'password' => $general_pass
              ]
            ]);

            if ($res->getStatusCode() == "200") {

              $data = json_decode($res->getBody());

              if ($data->success && isset($data->data->user->accounts[0]->id)) {

                // Sobreescribimos el user
                $user = $data->data->user;
                // Nos quedamos con la primera cuenta
                $account = $data->data->user->accounts[0];
              }
            }
          }
          catch (RequestException $e) {
            if ($e->hasResponse()) {
              echo Psr7\str($e->getResponse());
            } else {
              echo Psr7\str($e->getRequest());
            }
            exit();
          }

          // Si hemos encontrado cuenta en el login
          if ($account) {

            echo $u['modo_facturacion'] . " - ";

            if ($u['modo_facturacion'] == "Autónomo" || $u['modo_facturacion'] == "Empresa") {
              echo "externo";
              $url_crea_from_type = "v1/item/createFromType/Empleado%20Externo";
            } else {
              $url_crea_from_type = "v1/item/createFromType/Empleado%20Interno";
              echo "interno";
            }

            try {
              $res = $client->request('POST', $base_url . $url_crea_from_type, [
                'headers' => [
                  'Authorization' => 'Bearer ' . $user->api_token
                ],
                'form_params' => [
                  'account_id' => $account->id
                ]
              ]);

              if ($res->getStatusCode() == "200") {

                $data = json_decode($res->getBody());

                if ($data->success && isset($data->data->result)) {

                  $item = $data->data->result;

                  echo "<pre>";
                  print_r($item);
                  exit();
                }
              }
            }
            catch (RequestException $e) {
              // if ($e->hasResponse()) {
              //   echo Psr7\str($e->getResponse());
              // } else {
              //   echo Psr7\str($e->getRequest());
              // }
            }

          } // Fin if $account
        } // Fin if $user
      } // Fin if $email
    } // Fin if 'hay_email'
  } // Fin foreach de $users
} // Fin if $users
