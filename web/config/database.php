<?php

class Database
{

    // specify your own database credentials
	// TODO: put your credentials in here, both DB and Role have to exist
    private $host = "a.b.c.d";
    private $db_name = "api_db";
    private $username = "api_user";
    private $password = "password";
    public $conn;

    // get the database connection
    public function getConnection()
    {

        $this->conn = null;

        try {
            $this->conn = new PDO("pgsql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
