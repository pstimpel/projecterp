<?php

class Storagelocation
{

    // database connection and table name
    private $conn;
    private $table_name = "storagelocation";

    // object properties
    public $id;
    public $name;
    public $description;
    public $price;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // read
    public function getStorageLocationIDByName($suchtext) {
        $query = "SELECT
                id
            FROM
                storagelocation
            where lower(name)=?";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue(1, $suchtext);

        if(!$stmt->execute()) print_r($stmt->errorInfo());
        $id=0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

            $id = $row['id'];
        }
        return $id;
    }

    public function getAllStorageLocations() {
        $query = "SELECT
                name
            FROM
                storagelocation
            ORDER BY
                name";
        $stmt = $this->conn->prepare( $query );

        if(!$stmt->execute()) print_r($stmt->errorInfo());
        $out="";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if(strlen($out)>0) {
                $out = $out.", ";
            }
            $out = $out.$row['name'];
        }
        return $out;
    }


    function read(){

        // select all query
        $query = "SELECT
                p.id, p.name, p.description
            FROM
                " . $this->table_name . " as p
            ORDER BY
                p.name";


        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        return $stmt;
    }

    function readOne(){

        if(isset($this->id)) {
            $query = "SELECT p.id, p.name, p.description 
            FROM
                " . $this->table_name . " p
                
            WHERE
                p.id = ?
            LIMIT
                1";
            $stmt = $this->conn->prepare( $query );
            $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        } else if(isset($this->name)) {
            $query = "SELECT p.id, p.name, p.description 
            FROM
                " . $this->table_name . " p
                
            WHERE
                lower(p.name) = ?
            LIMIT
                1";
            $stmt = $this->conn->prepare( $query );
            $name = strtolower($this->name);
            $stmt->bindParam(1, $name, PDO::PARAM_STR);
        } else {
            return;
        }


        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row > 0) {
            // set values to object properties
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->description = $row['description'];

        }

    }
}
