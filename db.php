<?php

class DB{

    private $host="127.0.0.1";
    private $user="root";
    private $password="";
    private $database="db_survei";
    private $koneksi;

    public function __construct(){
       $this->koneksi = $this->koneksiDB();
       
       // Jika koneksi gagal, log error tapi jangan stop execution
       if (!$this->koneksi) {
           error_log('Database connection failed: ' . mysqli_connect_error());
       }
    }


    public function koneksiDB(){
        $koneksi = mysqli_connect($this->host, $this->user, $this->password, $this->database);
        
        if (!$koneksi) {
            error_log('Failed to connect to database: ' . mysqli_connect_error());
        }
        
        return $koneksi;
    }

    public function getALL($sql){
        $data = array();
        
        if (!$this->koneksi) {
            error_log('Database connection not available in getALL');
            return $data;
        }
        
        $result = mysqli_query($this->koneksi, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        } else {
            error_log('Query failed in getALL: ' . mysqli_error($this->koneksi));
        }
        
        return $data;
    }

    public function getITEM($sql){
        if (!$this->koneksi) {
            error_log('Database connection not available in getITEM');
            return null;
        }
        
        $result = mysqli_query($this->koneksi, $sql);
        
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? $row : null;
        } else {
            error_log('Query failed in getITEM: ' . mysqli_error($this->koneksi));
        }
        
        return null;
    }

    public function rowCOUNT($sql){
        if (!$this->koneksi) {
            error_log('Database connection not available in rowCOUNT');
            return 0;
        }
        
        $result = mysqli_query($this->koneksi, $sql);
        
        if ($result) {
            return mysqli_num_rows($result);
        } else {
            error_log('Query failed in rowCOUNT: ' . mysqli_error($this->koneksi));
        }
        
        return 0;
    }

    public function runSQL($sql){
        if (!$this->koneksi) {
            error_log('Database connection not available in runSQL');
            return false;
        }
        
        $result = mysqli_query($this->koneksi, $sql);
        
        if (!$result) {
            error_log('Query failed in runSQL: ' . mysqli_error($this->koneksi));
        }
        
        return $result;
    }

    public function getConnection(){
        return $this->koneksi;
    }

    public function pesan($text=""){
         echo $text;
    }

}

?>