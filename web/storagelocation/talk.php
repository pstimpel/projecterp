<?php
error_reporting(E_ERROR);
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


// include database and object files
include_once '../config/database.php';
include_once '../objects/storagelocation.php';
include_once '../objects/fuzzy.php';
include_once '../objects/stock.php';
include_once '../objects/product.php';
include_once '../objects/mailer.php';
include_once '../objects/storage.php';
include_once '../objects/orderbacklog.php';

// instantiate database and product object
$database = new Database();
$db = $database->getConnection();

//echo file_get_contents('php://input');

$json = json_decode(file_get_contents('php://input'), true);
$processed=false;

//todo: refactor this whole thing, remove spaqghetties

if( !isset($json['workflow']) && isset($json['action']) &&
        (
            substr(strtolower($json['action']),0,strlen("finde")) == "finde" ||
            substr(strtolower($json['action']),0,strlen("suche")) == "suche"
        )
    ) {

    //move action to queryaction
    $json['queryaction']=$json['action'];
    unset($json['action']);

    //set workflow
    $json['workflow'] = 'search';
    $json['workflowstep'] = 1;

    $processed=true;

}

if( !isset($json['workflow']) && isset($json['action']) &&
    (
        substr(strtolower($json['action']),0,strlen("detail")) == "detail"
    )
) {

    //move action to queryaction
    $json['queryaction']=$json['action'];
    unset($json['action']);

    //set workflow
    $json['workflow'] = 'detailsearch';
    $json['workflowstep'] = 1;

    $processed=true;

}

if( !isset($json['workflow']) && isset($json['action']) &&
    (
        substr(strtolower($json['action']),0,strlen("hilfe")) == "hilfe"
    )
) {

    //move action to queryaction
    $json['queryaction']=$json['action'];
    unset($json['action']);

    //set workflow
    $json['workflow'] = 'help';
    $json['workflowstep'] = 1;

    $processed=true;

}

if( !isset($json['workflow']) && isset($json['action']) &&
    (
        substr(strtolower($json['action']),0,strlen("bestelle")) == "bestelle"
    )
) {

    //move action to queryaction
    $json['queryaction']=$json['action'];
    unset($json['action']);

    //set workflow
    $json['workflow'] = 'order';
    $json['workflowstep'] = 1;

    $processed=true;

}

if( !isset($json['workflow']) && isset($json['action']) &&
    (
        substr(strtolower($json['action']),0,strlen("bestellungen")) == "bestellungen"
    )
) {

    //move action to queryaction
    $json['queryaction']=$json['action'];
    unset($json['action']);

    //set workflow
    $json['workflow'] = 'listorders';
    $json['workflowstep'] = 1;

    $processed=true;

}

if( !isset($json['workflow']) && isset($json['action']) &&
    (
        substr(strtolower($json['action']),0,strlen("lösche bestellung")) == "lösche bestellung"
    )
) {

    //move action to queryaction
    $json['queryaction']=$json['action'];
    unset($json['action']);

    //set workflow
    $json['workflow'] = 'deleteorder';
    $json['workflowstep'] = 1;

    $processed=true;

}

if( !isset($json['workflow']) && isset($json['action']) &&
    (
        substr(strtolower($json['action']),0,strlen("liste")) == "liste"
    )
) {

    //move action to queryaction
    $json['queryaction']=$json['action'];
    unset($json['action']);

    //set workflow
    $json['workflow'] = 'list';
    $json['workflowstep'] = 1;

    $processed=true;

}


//todo: do we need to check against odd words since we use AI to complete the commands?
if( !isset($json['workflow']) && isset($json['action']) &&
    (
        substr(strtolower($json['action']),0,strlen("einlagern")) == "einlagern" ||
        substr(strtolower($json['action']),0,strlen("einladung")) == "einladung" ||
        substr(strtolower($json['action']),0,strlen("einlage")) == "einlage"
    )
) {

    //move action to queryaction
    $json['queryaction']=$json['action'];
    unset($json['action']);

    //set workflow
    $json['workflow'] = 'add';
    $json['workflowstep'] = 1;

    $processed=true;

}

if( isset($json['workflow']) && $json['workflow'] == 'help') {
    switch($json['workflowstep']) {
        case 1:
            $json['talk'] = "Mögliche Befehle: liste Lagername, suche Artikelname, detail Artikelname für Suche nach Produktnamenteilen, einlagern Artikelname, Notiz Text, Bestelle Text, Bestellungen, Lösche Bestellung";
            $json['nextaction'] = 'exit';
            $json['followupworkflowstep'] = 99;
            break;
    }
}

if ( isset($json['workflow']) && $json['workflow'] == 'deleteorder') {
    switch($json['workflowstep']) {
        case 1:
            //Finde die Bestellnummer im String
            $ordernumber_s = substr($json['queryaction'], strlen("lösche bestellung")+1);

            if(strlen($ordernumber_s)==0) {
                $json['talk'] = "Keine Bestellungnummer verstanden";
                $json['nextaction'] = 'exit';
                $json['followupworkflowstep'] = 99;
            } else {
                if(is_numeric($ordernumber_s)) {
                    $ordernumber = (int)$ordernumber_s;
                    $orderbacklog = new OrderBacklog($db);
                    $orderbacklog->id = $ordernumber;
                    if($orderbacklog->markOrderById()) {
                        $json['talk'] = "OK";
                    } else {
                        $json['talk'] = "Order ".$ordernumber_s." nicht gefunden";
                    }
                    $json['nextaction'] = 'exit';
                    $json['followupworkflowstep'] = 99;
                } else {
                    $json['talk'] = "Falsche bestellnummer verstanden, Eingabe war ".$ordernumber_s;
                    $json['nextaction'] = 'exit';
                    $json['followupworkflowstep'] = 99;
                }
            }

    }
}

if ( isset($json['workflow']) && $json['workflow'] == 'listorders') {
    switch($json['workflowstep']) {
        case 1:
            $orderbacklog = new OrderBacklog($db);
            $orders = $orderbacklog->readAll();
            $json['talk'] = $orders;
            $json['nextaction'] = 'exit';
            $json['followupworkflowstep'] = 99;
            break;
    }
}

if( isset($json['workflow']) && $json['workflow'] == 'order') {

    //schreibe die order ins Backlog
    $ordertext = str_replace("Bestelle ", '', $json['queryaction']);
    $ordertext = str_replace("bestelle ", '', $ordertext);

    $orderbacklog = new OrderBacklog($db);
    $orderbacklog->ts=date("Y-m-d H:i:s");
    $orderbacklog->name=$ordertext;
    $orderbacklog->tobedone=1;
    $orderbacklog->addStorage();
    $processed=true;

}


if( isset($json['workflow']) && $json['workflow'] == 'list') {
    switch($json['workflowstep']) {
        case 1:
            $suchstring = substr($json['queryaction'], 6);
            $fuzzer = new Fuzzy($db);
            $suchstring = $fuzzer->doFuzzy($suchstring);
            $json['suchstring'] = $suchstring;
            $sucharray = $fuzzer->createFuzzArray($suchstring);
            $stock = new Stock($db);
            $result = $stock->getStoragecontentBySearchArray($sucharray);
            //print_r($result);
            $json['talk'] = $result['output'];
            $json['nextaction'] = 'exit';
            $json['followupworkflowstep'] = 99;
            break;
    }
}

if( isset($json['workflow']) && $json['workflow'] == 'add') {
    switch($json['workflowstep']) {
        case 1:
            $suchstring = substr($json['queryaction'], 10);
            $fuzzer = new Fuzzy($db);
            $suchstring = $fuzzer->doFuzzy($suchstring);
            $json['suchstring'] = $suchstring;
            $sucharray = $fuzzer->createFuzzArray($suchstring);
            $product = new Product($db);
            $result = $product->getStockBySearchArray($sucharray);
            //print_r($result);
            if($result['found']==true) {
                $json['talk'] = $result['productname'].", ".$result['output'].", bitte Menge angeben";
                $json['productname'] = $result['productname'];
                $json['product_id'] = $result['product_id'];
                $json['followupworkflowstep'] = 3;
                $json['nextaction'] = 'queryamount';
            } else {
                $json['talk'] = "Artikel nicht gefunden, Artikel ".$json['suchstring']." anlegen?";
                $json['productname'] = $json['suchstring'];
                $json['product_id'] = 0;
                $json['followupworkflowstep'] = 2;
                $json['nextaction'] = 'queryconfirmation';
            }
            $processed=true;
            break;
        case 2:
            $answer = $json['action'];
            if(strtolower($answer)=="ja") {
                $json['talk'] = "OK, bitte Menge angeben";
                $json['product_id'] = 0;
                $json['followupworkflowstep'] = 3;
                $json['nextaction'] = 'queryamount';
            } else {
                $json['talk'] = "Abbruch erfolgreich";
                $json['followupworkflowstep'] = 99;
                $json['nextaction'] = 'exit';
            }
            $processed=true;
            break;
        case 3:
            $json['followupworkflowstep'] = 4;
            if(strlen($json['action']==0)) {
                $json['followupworkflowstep'] = 3;
                $json['talk']="Bitte Menge angeben";
                $json['nextaction'] = 'queryamount';
                $processed=true;
                break;
            }
            $fuzzer = new Fuzzy($db);
            $amount = $fuzzer->doFuzzy($json['action']);

            $amount = str_replace(",",".", $amount);
            $json['amount']=(int)$amount;

            $json['talk'] = "Menge ".$json['action'].", bitte Lagerort angeben";
            $json['nextaction'] = 'querylocation';
            $processed=true;
            break;
        case 4:
            $fuzzer = new Fuzzy($db);
            $suchstring = $fuzzer->doFuzzy($json['action']);
            $json['suchstring'] = $suchstring;
            $sucharray = $fuzzer->createFuzzArray($suchstring);
            $storage = new Storage($db);
            $result = $storage->getStorageBySearchArray($sucharray);
            if($result['found']==true) {
                $json['talk'] = $result['output'].", Artikel gespeichert: ".$json['productname']." ".$json['amount']." Stück in ".$result['storagename'].", ".$result['storagelocationname'];
                $json['storagename'] = $result['storagename'];
                $json['storage_id'] = $result['storage_id'];
                $json['followupworkflowstep'] = 99;
                $json['nextaction'] = 'exit';

                if($json['product_id']==0) {
                    //store product and get id
                    $product = new Product($db);
                    $productid=$product->insertProduct($json['productname']);
                    $json['product_id']=$productid;

                }

                $stock = new Stock($db);
                $stock_id = $stock->getStockId($json['product_id'], $json['storage_id']);
                if($stock_id==0) {
                    $stock->addStock($json['storage_id'],$json['product_id'], $json['amount']);
                } else {
                    $stock->changeStock($stock_id, $json['amount']);
                }


            } else {
                $storagelocation = new Storagelocation($db);
                $sl = $storagelocation->getAllStorageLocations();

                $json['talk'] = "Lager nicht gefunden, Lager ".$json['suchstring']." anlegen? Mögliche Antworten: : ".$sl." oder Abbruch";
                $json['storagename'] = $json['suchstring'];
                $json['storage_id'] = 0;
                $json['followupworkflowstep'] = 5;
                $json['nextaction'] = 'queryconfirmation';
            }

            $processed=true;
            break;
        case 5:
            $fuzzer = new Fuzzy($db);
            $suchstring = $fuzzer->doFuzzy($json['action']);
            if(strtolower($suchstring)=="abbruch") {
                $json['talk'] = "Abbruch erfolgreich";
                $json['followupworkflowstep'] = 99;
                $json['nextaction'] = 'exit';

            } else {
                $json['suchstring'] = $suchstring;
                $storagelocation = new Storagelocation($db);
                $slid = $storagelocation->getStorageLocationIDByName(strtolower($suchstring));
                if($slid==0) {
                    $sl = $storagelocation->getAllStorageLocations();
                    $json['suchstring'] = $suchstring;
                    $json['talk'] = "Lager nicht gefunden, Lager ".$json['suchstring']." anlegen? Mögliche Antworten: : ".$sl." oder Abbruch";
                    $json['storagename'] = $json['suchstring'];
                    $json['storage_id'] = 0;
                    $json['followupworkflowstep'] = 5;
                    $json['nextaction'] = 'queryconfirmation';
                } else {
                    //yeah, we found all we need
                    $productid=0;
                    if($json['product_id']==0) {
                        //store product and get id
                        $product = new Product($db);
                        $productid=$product->insertProduct($json['productname']);
                        $json['product_id']=$productid;
                    }
                    $storage_id=0;
                    if($json['storage_id']==0) {
                        //store storage and get id
                        $storage = new Storage($db);
                        $storage_id=$storage->addStorage($json['storagename'], $slid);
                        $json['storage_id']=$storage_id;
                    }
                    //$mail = new Mail(print_r($json, true));
                    //$mail->sendmail();
                    $stock = new Stock($db);
                    $stock->addStock($json['storage_id'],$json['product_id'], $json['amount']);
                    $json['talk'] = "Artikel gespeichert: ".$json['productname']." ".$json['amount']." Stück in ".$json['storagename'].", ".$suchstring;
                    $json['followupworkflowstep'] = 99;
                    $json['nextaction'] = 'exit';
                }

            }
            $processed=true;
            break;
    }
}

if( isset($json['workflow']) && $json['workflow'] == 'search') {
    switch($json['workflowstep']) {
        case 1:
            $suchstring = substr($json['queryaction'], 6);
            $fuzzer = new Fuzzy($db);
            $suchstring = $fuzzer->doFuzzy($suchstring);
            $json['suchstring'] = $suchstring;
            $sucharray = $fuzzer->createFuzzArray($suchstring);
            $stock = new Stock($db);
            $result = $stock->getStockBySearchArray($sucharray, false);
            //print_r($result);
            $json['talk'] = $result['output'];
            $json['nextaction'] = 'exit';
            $json['followupworkflowstep'] = 99;
            break;
    }
}

if( isset($json['workflow']) && $json['workflow'] == 'detailsearch') {
    switch($json['workflowstep']) {
        case 1:
            $suchstring = substr($json['queryaction'], 7);
            $fuzzer = new Fuzzy($db);
            $suchstring = $fuzzer->doFuzzy($suchstring);
            $json['suchstring'] = $suchstring;
            $sucharray = $fuzzer->createFuzzArray($suchstring);
            $stock = new Stock($db);
            $result = $stock->getStockBySearchArray($sucharray, true);
            //print_r($result);
            $json['talk'] = $result['output'];
            $json['nextaction'] = 'exit';
            $json['followupworkflowstep'] = 99;
            break;
    }
}


if(!isset($json)) {
    $json = array("talk" => "Kein Kommando gefunden", "nextaction" => 'exit');
}
if(!$processed) {
    if(!$json['action']) {
        $json['action']='unbekannt';
    }
    $json = array("talk" => "Kein Kommando erkannt, Eingabe war ".$json['action'], "nextaction" => 'exit', "host"=>$_SERVER['REMOTE_ADDR'], "data" => $json);
    //$mail = new Mail(print_r($json, true));
    //$mail->sendmail();

}
echo json_encode($json);

