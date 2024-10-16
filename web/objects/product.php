<?php

class Product
{

    // database connection and table name
    private $conn;


    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function insertProduct($productname) {
        $query = "insert into product (productname) values(?)";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue(1, $productname);
        if(!$stmt->execute()) print_r($stmt->errorInfo());

        $productid=0;
        $sql = "SELECT currval('product_product_id_seq') as thisid";
        $stmt = $this->conn->prepare( $sql );
        if(!$stmt->execute()) print_r($stmt->errorInfo());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $productid = $row['thisid'];
        }
        return $productid;
    }


    public function getStockBySearchArray($searcharray) {

        $searchresult=array('output'=>"Keine passenden Artikel gefunden", 'found'=>false);
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
                $where=$where.'( lower(productname) like :t'.$fa.' or lower(productname) like :t'.$fb.' 
                or lower(productname) like :t'.$fc.' or lower(productname) = :t'.$fd.' )';
            }
        }
        if(strlen($where)==0) {
            $where='1=2';
        }

        $query = "SELECT * from product where ".$where." order by productname, product_id  limit 1";
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

            $searchresult['output']="Artikel gefunden";
            $searchresult['productname']=$row['productname'];
            $searchresult['product_id']=$row['product_id'];
            $searchresult['found']=true;

        }
        if($found==0) {
            $searchresult['output']="Keine passenden Artikel gefunden";
            $searchresult['found']=false;
        }

        return $searchresult;
    }

}
