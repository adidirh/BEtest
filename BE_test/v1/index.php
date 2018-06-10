<?php

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php'
require '.././libs/Slim/Slim.php'

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$username = NULL;

/**
 * Otentikasi API key di db
 */
 funtion otentikasi(\Slim\Route $route) {
   $headers = apache_request_headers();
   $response = array();
   $app = \Slim\Slim::getInstance();

   //pengecekan APIkey
   if (isset($headers['Authorization'])) {
     $db = new DbHandler();

     $api_key = $headers['Authorization'];
     if(!$db->validasiAPI($api_key)) {
       $response["error"] = true;
       $response["message"] = "APIkey salah";
       responEcho(401, $response);
       $app->stop();
     } else {
       $username;
       $username = $db->getIdUser($api_key);
     }
   } else {
     $response["error"] = true;
     $response["message"] = "tolong input APIkey di header";
     responEcho(400, $response);
     $app->stop();
   }
 }

//pengecekan parameter

function verifikasiParameter($required_fields){
  $error = false;
  $error_fields = "";
  $request_params = array();
  $request_params = $_REQUEST;
  //handle parameter pute
  if ($_SERVER['REQUEST_METHOD'] == 'PUT'){
    $app = \Slim\Slim::getInstance();
    parse_str($app->request()->getBody(), $request_params);
  }
  foreach ($required_fields as $fields) {
    if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
  }

  if($error) {
    //parameter tidak diisi
    //tampilkan error dan stop app
    $response = array();
    $app = \Slim\Slim::getInstance();
    $response["error"] = true;
    $response["message"] = 'Required field(s)' .substr($error_fields, 0, -2) .'tidak ditemukan';
    responEcho(400, $response);
    $app->stop();
  }
}

//validasi username
function validasiUsername($username){
  $app = \Slim\Slim::getInstance();
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $response["error"] = true;
    $response["message"] = 'Username tidak valid';
    responEcho(400, $response);
    $app->stop();
  }
}

//menampilkan respon berbentuk JSON ke client
function responEcho($status_code, $response){
  $app = \Slim\Slim::getInstance();
  $app->status($status_code);
  $app->contentType('application/json');

  echo json_encode($response);
}

$app->post('/buatUser/:1', function() use ($app) {
  verivikasiParameter(array('name', 'priority', 'location', 'timeStart', 'username', 'password'));

              $response = array();

              // ngebaca parameter yang dimasukan dan memasukan ke variabel
              $name = $app->request->post('name');
              $priority = $app->request->post('priority');
              $location = $app->request->post('location');
              $timeStart = $app->request->post('timeStart');
              $username = $app->request->post('username');
              $password = $app->request->post('password');

              // memvalidasi alamat email
              validasiUsername($username);

              $db = new DbHandler();
              $res = $db->buatUser($name, $priority, $location, $timeStart, $username, $password);

              if ($res == USER_CREATED_SUCCESSFULLY) {
                  $response["error"] = false;
                  $response["message"] = "USER CREATED";
              } else if ($res == USER_CREATE_FAILED) {
                  $response["error"] = true;
                  $response["message"] = "REGISTRATION FAILED";
              } else if ($res == USER_ALREADY_EXISTED) {
                  $response["error"] = true;
                  $response["message"] = "SORRY, USER ALREADY EXISTED";
              }
              // response echo dengan json
              responEcho(201, $response);
          });


   $app->put('/ubahData/:2', 'otentikasi', function($username) use($app) {
            // check for required params
            verivikasiParameter(array('username', 'password'));

            global $username;
            $id_barang = $app->request->post('username');

            $db = new DbHandler();
            $response = array();

            $result = $db->ubahData($name, $priority, $location, $timeStart, $username);

            if ($result) {
                // History transaksi berhasil di ubah
                $response["error"] = false;
                $response["message"] = "Data telah diubah";
            } else {
                // History transaksi gagal di ubah
                $response["error"] = true;
                $response["message"] = "Data gagal diubah";
            }
            responEcho(200, $response);
        });


        $app->delete('/deleteData/:3', 'otentikasi', function($username) use($app) {
                  global $username;

                  $db = new DbHandler();
                  $response = array();
                  $result = $db->deleteData($username);
                  if ($result) {
                      // task deleted successfully
                      $response["error"] = false;
                      $response["message"] = "Data telah dihapus";
                  } else {
                      // task failed to delete
                      $response["error"] = true;
                      $response["message"] = "Data gagal dihapus";
                  }
                  responEcho(200, $response);
              });
$app->run();
 ?>
