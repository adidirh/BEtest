<?php
/**
 *set connection to database
 */

class DbConnect {
  private $conn;
  function __construct() {
}
    /**
    *membangun koneksi ke database
    */
    function connect(){
      include_once dirname(__FILE__) . '/Config.php';

      //connect to mysql

      $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
      if(mysqli_connect_error()) {
        echo "Gagal connect ke MySQL: " . mysqli_connect_error();
      }

      return $this->conn;
  }
}
?>
