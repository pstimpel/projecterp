<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// include database and object files
include_once '../config/database.php';
include_once '../objects/storagelocation.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare product object
$storagelocation = new Storagelocation($db);

// set ID property of record to read
$wasset=false;
if(isset($_GET['id'])) {
    $storagelocation->id = $_GET['id'];
    $wasset=true;
}
if(isset($_GET['name'])) {
    $storagelocation->name = $_GET['name'];
    $wasset=true;
}
if($wasset==false) {
    die();
}


// read the details of product to be edited
$storagelocation->readOne();

if($storagelocation->name!=null){
    // create array
    $storagelocation_arr = array(
        "id" =>  $storagelocation->id,
        "name" => $storagelocation->name,
        "description" => $storagelocation->description

    );

    // set response code - 200 OK
    http_response_code(200);

    // make it json format
    echo json_encode($storagelocation_arr);
}

else{
    // set response code - 404 Not found
    http_response_code(404);

    // tell the user product does not exist
    echo json_encode(array("message" => "Storagelocation does not exist."));
}

