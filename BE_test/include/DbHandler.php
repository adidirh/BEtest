<?php
/**
 *class berisi method get, create, update, edit, delete
 */

 class DbHandler {

   private $conn;

   function __createNew() {
     require_once dirname(__FILE__) . '/DbConnect.php';
     /*membuka koneksi db*/
     $db = new DbConnect();
     $this->conn = $db->connect();
   }

   /*---------- 'infoprivacy' table -------------*/
   //create user baru

   public function buatUser($name, $priority, $location, $timeStart, $username, $password) {
     require_once 'PassHash.php';
     $response = array();

     //cek user, apakah sudah ada atau belum
     if(!$this->isUserExists($name)) {
       //generate hash dari $password
       $pass = PassHash::hash($password);

       //generate API key
       $api_key = $this->generateApiKey();

       //query
       $stmt = $this->conn->prepare("INSERT INTO infoprivacy(name, priority, location, timeStart, username, password, api_key) values(?, ?, ?, ?, ?, ?, ?)");
       $stmt->bind_param("sssssss", $name, $priority, $location, $timeStart, $username, $password, $api_key);

       $result = $stmt->execute();

       $stmt->close();

       //jika data berhasil masuk
       if($result) {
         return USER_CREATED_SUCCESSFULLY;
       } else {
         return USER_CREATE_FAILED;
       }

     } else {
       return USER_ALREADY_EXISTED;
     }
  return $response;
   }

//user login
    public function cekLogin($username, $password){
      $stmt = $this->conn->prepare("SELECT password FROM infoprivacy WHERE username = ?");

      $stmt->bind_param("s",$username);

      $stmt->execute();

      $stmt->bind_result($pass);

      $stmt->store_result();

      if($stmt->num_rows > 0){

        $stmt->fetch();

        $stmt->close();

        if(PassHash::cek_pass($pass, $password)) {
          return TRUE;
        } else {
          return FALSE;
        }
      } else {
    $stmt->close();
    return FALSE;
    }
  }

//cekUser
    private function isUserExists($username){
      $stmt = $this->conn->prepare("SELECT name FROM infoprivacy WHERE username = ?");
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $stmt->store_result();
      $num_rows = $stmt->num_rows;
      $stmt->close();
      return $num_rows > 0;
    }

//mendapatkan username berdasarkan APIkey
    public function getUser($api_key){
      $stmt = $this->conn->prepare("SELECT username FROM infoprivacy WHERE api_key = ?");
      $stmt->bind_param("i",$api_key);
      if($stmt->execute()){
        $stmt->bind_result($username);
        $stmt->fetch();
        $stmt->close();
        return $username;
      } else {
        return NULL;
      }
    }

  //melakukan validasi terhadap APIkey dari user
    public function validasiAPI($api_key){
      $stmt = $this->conn->prepare("SELECT username FROM infoprivacy WHERE api_key = ?");
      $stmt->bind_param("s", $api_key);
      $stmt->execute();
      $stmt->store_result();
      $num_rows = $stmt->num_rows;
      $stmt->close();
      return $num_rows > 0;
    }
//generate APIkey menggunakan md5

    private function generateApiKey(){
      return md5(uniqid(rand(), true));
    }

    //updatedata

    public function ubahData($name, $priority, $location, $timeStart, $username) {
        $stmt = $this->conn->prepare("UPDATE infoprivacy set name = ?, priority = ?, location = ?, timeStart = ? WHERE username = ?");
        $stmt->bind_param("sssss", $name, $priority, $location, $timeStart, $username);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //deleteData

    public function deleteData($username) {
           $stmt = $this->conn->prepare("DELETE FROM infoprivacy WHERE username = ?");
           $stmt->bind_param("s", $username);
           $stmt->execute();
           $num_affected_rows = $stmt->affected_rows;
           $stmt->close();
           return $num_affected_rows > 0;
       }
}
 ?>
