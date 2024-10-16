<?php

class Fuzzy
{

    // database connection and table name
    private $conn;


    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function doFuzzy($stringtofuzz) {

        $query = "SELECT * 
            FROM
                fuzzer f
            order by priority, fuzzerin";
        $stmt = $this->conn->prepare( $query );

        if(!$stmt->execute()) print_r($stmt->errorInfo());;

        $fuzzedString=$stringtofuzz;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $fuzzedString = str_ireplace($row['fuzzerin'], $row['fuzzerout'], $fuzzedString);
            //echo "->".$row['fuzzerin']."-".$row['fuzzerout']."-".$fuzzedString."\n";
        }
        for($i=0;$i<10;$i++) {
            $fuzzedString=str_replace('  ',' ', $fuzzedString);

        }
        return $fuzzedString;
    }

    public function createFuzzArray($searchstring) {
        $array = explode(' ', $searchstring);
        for($i=0;$i<sizeof($array);$i++) {
            if(strlen($array[$i])==0 || $array[$i]==' ') {
                unset($array[$i]);
            }
        }
        $uarr = array_values($array);
        //var_dump($uarr);
        return $uarr;
    }


}
