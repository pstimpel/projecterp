<?php

class Stock
{

    // database connection and table name
    private $conn;


    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listStock() {
        $query = "select amount, productname, storagename, name as storagelocationname from product 
            left join stock on product_id=productid 
            left join storage on storage_id=storageid 
            left join storagelocation on storagelocation.id=storagelocationid
            order by productname, storagename, storagelocationname";
        $stmt = $this->conn->prepare( $query );

        if(!$stmt->execute()) print_r($stmt->errorInfo());
        $stock = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $amount = $row['amount'];
            if(strlen($amount)==0) {
                $amount=0;
            }

            array_push($stock, array(
                'amount'=>(int)$amount,
                'productname'=>$row['productname'],
                'storagename'=>$row['storagename'],
                'storagelocationname'=>$row['storagelocationname']
            ));
        }
        return $stock;
    }

    public function getStockId($productid, $storageid) {
        $query = "select stock_id from stock where storageid=? and productid=?";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue(1, $storageid);
        $stmt->bindValue(2, $productid);

        if(!$stmt->execute()) print_r($stmt->errorInfo());
        $stock_id=0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $stock_id = $row['stock_id'];
        }
        return $stock_id;
    }

    public function changeStock($stock_id,$byamount) {
        $query = "update stock set amount=amount + ?, ts=now() where stock_id=?";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue(1, $byamount);
        $stmt->bindValue(2, $stock_id);

        if(!$stmt->execute()) print_r($stmt->errorInfo());
    }

    public function addStock($storageid,$productid,$toamount) {
        $query = "insert into stock (storageid, productid, amount, ts) values(?, ?, ?, now())";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue(1, $storageid);
        $stmt->bindValue(2, $productid);
        $stmt->bindValue(3, $toamount);

        if(!$stmt->execute()) print_r($stmt->errorInfo());
        $stock_id=0;
        $sql = "SELECT currval('stock_stock_id_seq') as thisid";
        $stmt = $this->conn->prepare( $sql );
        if(!$stmt->execute()) print_r($stmt->errorInfo());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $stock_id = $row['thisid'];
        }
        return $stock_id;
    }

    public function getStockBySearchArray($searcharray, $searchsubstring) {

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

                if($searchsubstring===true) {
                    $fe=$f+1;
                    $ff=$f+2;
                    $fg=$f+3;
                    $f=$f+3;
                }
                if(strlen($where)>0) {
                    $where = $where . " and ";
                }
                $where=$where.'( lower(productname) like :t'.$fa.' or lower(productname) like :t'.$fb.' or 
                lower(productname) like :t'.$fc.' or lower(productname) = :t'.$fd.' ';
                if($searchsubstring===true) {
                    $where=$where.' or lower(productname) like :t'.$fe.' ';
                    $where=$where.' or lower(productname) like :t'.$ff.' ';
                    $where=$where.' or lower(productname) like :t'.$fg.' ';
                }
                $where=$where.')';
            }
        }
        if(strlen($where)==0) {
            $where='1=2';
        }

        $query = "SELECT * from stock 
        left join product on productid=product_id 
        left join storage on storageid=storage_id
        left join storagelocation on storagelocationid=id where ".$where." order by productname";
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
                if($searchsubstring===true) {
                    $fe = ':t' . ($f + 1);
                    $ff = ':t' . ($f + 2);
                    $fg = ':t' . ($f + 3);
                    $f = $f + 3;
                }
                $sa = '% ' . (string)$lookfor . ' %';
                $sb = '% ' . (string)$lookfor;
                $sc = (string)$lookfor . ' %';
                $sd = (string)$lookfor;
                $stmt->bindValue($fa, $sa);
                $stmt->bindValue($fb, $sb);
                $stmt->bindValue($fc, $sc);
                $stmt->bindValue($fd, $sd);
                if($searchsubstring===true) {
                    $se = '%' . (string)$lookfor . '%';
                    $sf = '%' . (string)$lookfor;
                    $sg = (string)$lookfor . '%';
                    $stmt->bindValue($fe, $se);
                    $stmt->bindValue($ff, $sf);
                    $stmt->bindValue($fg, $sg);
                }
            }
        }

        if(!$stmt->execute()) print_r($stmt->errorInfo());

        $found=0;
        $sumofitems = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $sumofitems++;
            if($found==0) {
                $searchresult['output']='';
            }
            $found++;
            if(strlen($searchresult['output'])>0 && $sumofitems < 5) {
                $searchresult['output']=$searchresult['output']." und ";
            }
            /*
             * "stock_id","productid","storageid","amount","ts","product_id","productname","storage_id","storagelocationid","storagename","id","name","description"
"1","1","1","0","2021-06-09 19:42:56.958228","1","Widerstand 100 Ohm","1","1","Box 17","1","Bastelzimmer","Bastelzimmer"
             * */
            if($sumofitems < 5) {
                $searchresult['output'] = $searchresult['output'] . $row['productname'] . ": Anzahl " . $row['amount'] . " gefunden in " . $row['storagename'] . " in " . $row['name'];
            }
            $searchresult['found']=true;

        }
        if($sumofitems > 5) {
            $searchresult['output'] = $searchresult['output'].", sowie ".$sumofitems." weitere Artikel oder Lagerorte";
        }
        if($found==0) {
            $searchresult['output']="Keine passenden Artikel gefunden";
            $searchresult['found']=false;
        }

        return $searchresult;
    }

    public function getStoragecontentBySearchArray($searcharray) {

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
                $where=$where.'( lower(storagename) like :t'.$fa.' or lower(storagename) like :t'.$fb.' or 
                lower(storagename) like :t'.$fc.' or lower(storagename) = :t'.$fd.' )';
            }
        }
        if(strlen($where)==0) {
            $where='1=2';
        }

        $query = "SELECT * from stock 
        left join product on productid=product_id 
        left join storage on storageid=storage_id
        left join storagelocation on storagelocationid=id where ".$where." order by productname";
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
        $sumofitems = 0;
        $storagedescription = '';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $sumofitems++;
            if($found==0) {
                $searchresult['output']='';
            }
            $found++;

            $searchresult['output']=$searchresult['output'].", ".$row['productname'];
            $searchresult['found']=true;
            $storagedescription=$row['storagename'] . " in " . $row['name'];
        }
        if($sumofitems > 0) {
            $searchresult['output'] = "Lager ".$storagedescription." enthält ".$sumofitems." Artikel".$searchresult['output'];
        }
        if($found==0) {
            $searchresult['output']="Keine passenden Artikel oder Lager gefunden";
            $searchresult['found']=false;
        }

        return $searchresult;
    }
}
