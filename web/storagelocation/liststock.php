<?php

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


// include database and object files
include_once '../config/database.php';
include_once '../objects/stock.php';

// instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// initialize object
$stock = new Stock($db);

$data = array('data'=>$stock->listStock());

echo json_encode($data);

