<?php
class Database 
{
    private $host = "localhost";
    private $dbName = "ecommerce";
    private $username = "root";
    private $password = "";
    public $conn;

    # Get DB connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:dbname='.$this->dbName.'; host='.$this->host.';',$this->username,$this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        }catch (PDOException $e) {
            echo $e->getMessage();
        }

        return $this->conn;
    }
    
}
?>