<?php

class Talk
{

    // database connection and table name
    private $conn;
    private $table_name = "storagelocation";


    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }


}
