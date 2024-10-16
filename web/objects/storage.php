<?php

class Storage
{

    // database connection and table name
    private $conn;
    private $table_name = "storage";

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

    public function addStorage($storagename, $storagelocation_id) {
        $query = "insert into storage (storagelocationid, storagename) values(?, ?)";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue(1, $storagelocation_id);
        $stmt->bindValue(2, $storagename);

        if(!$stmt->execute()) print_r($stmt->errorInfo());
        $storage_id=0;
        $sql = "SELECT currval('storage_storage_id_seq') as thisid";
        $stmt = $this->conn->prepare( $sql );
        if(!$stmt->execute()) print_r($stmt->errorInfo());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $storage_id = $row['thisid'];
        }
        return $storage_id;


    }

    public function getStorageBySearchArray($searcharray) {

        $searchresult=array('output'=>"Kein passendes Lager gefunden", 'found'=>false);
        if(sizeof($searcharray)==0) {
            return $searchresult;
        }


        $where="";
        $f=0;
        for($i=0;$i<sizeof($searcharray);$i++) {
            if($searcharray[$i] != '' && $searcharray[$i] != ' ') {
                $fa=$f+1;
                $fb=$f+2;
                $fc=$f+3;
                $fd=$f+4;
                $f=$f+4;
                if(strlen($where)>0) {
                    $where = $where . " and ";
                }
                $where=$where.'( lower(storagename) like :t'.$fa.' or lower(storagename) like :t'.$fb.' 
                or lower(storagename) like :t'.$fc.' or lower(storagename)=:t'.$fd.')';
            }
        }
        if(strlen($where)==0) {
            $where='1=2';
        }

        $query = "SELECT * from storage left join storagelocation on id=storagelocationid where ".$where." order by storagename,storage_id  limit 1";
        //echo $query;
        $stmt = $this->conn->prepare( $query );
        $f=0;
        for($i=0;$i<sizeof($searcharray);$i++) {

            if($searcharray[$i] != '' && $searcharray[$i] != ' ') {

                $lookfor = strtolower($searcharray[$i]);
                $fa = ':t' . ($f + 1);
                $fb = ':t' . ($f + 2);
                $fc = ':t' . ($f + 3);
                $fd = ':t' . ($f + 4);
                $f = $f + 4;
                $sa = '% ' . (string)$lookfor . ' %';
                $sb = '% ' . (string)$lookfor;
                $sc = (string)$lookfor . ' %';
                $sd = (string)$lookfor;
                $stmt->bindValue($fa, $sa);
                $stmt->bindValue($fb, $sb);
                $stmt->bindValue($fc, $sc);
                $stmt->bindValue($fd, $sd);

            }
        }

        if(!$stmt->execute()) print_r($stmt->errorInfo());

        $found=0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($found==0) {
                $searchresult['output']='';
            }
            $found++;

            $searchresult['output']="Lager gefunden";
            $searchresult['storagename']=$row['storagename'];
            $searchresult['storagelocationname']=$row['name'];
            $searchresult['storage_id']=$row['storage_id'];
            $searchresult['found']=true;

        }
        if($found==0) {
            $searchresult['output']="Kein passendes Lager gefunden";
            $searchresult['found']=false;
        }
        //print_r($searchresult);
        return $searchresult;
    }

}
