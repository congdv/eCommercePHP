<?php 
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

define('TABLE', 'product');

# root path
include("../../root.php");

# Database Connection

include( CONFIG_PATH."/database.php");

$verb = strtolower($_SERVER['REQUEST_METHOD']);

if($verb == 'get') {
    try 
    {
        $allProucts = getProducts();   

        if(!empty($allProucts))
        {
            sendDataToClient($allProucts);
        }else {
            http_response_code(401);
            $resp = new stdClass();
            $resp->error = "Invalid";
            $resp->message = "Oops! No products!";
            echo json_encode($resp);
        }
    }
    catch(Exception $e)
    {
        http_response_code(401);
        $resp = new stdClass();
        $resp->error = "No Data";
        $resp->message = "No products in Database";
        echo json_encode($resp);
    }
} 
else {
    http_response_code("403");
    $resp = new stdClass();
    $resp->error = "Invalid";
    $resp->message = "Unknown Endpoint";
    echo json_encode($resp);
}

# Read all products in database
function getProducts()
{
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'SELECT * FROM '.TABLE;
    $sql = $dbConn->prepare($cmd);
    $sql->execute();
    $dataArray = array();
    while($data = $sql->fetch(PDO::FETCH_ASSOC))
    {
        $data =  array(
            'id' => $data['ID'],
            'name' => $data['Name'],
            'description' => $data['Description'],
            'image' => $data['Image'],
            'pricing' => $data['Pricing'],
            'shippingCost' => $data['ShippingCost']);
        array_push($dataArray,$data);
    }
    return $dataArray;
}

# Sending back to client
function sendDataToClient($allProucts)
{
    $resp = new stdclass();
    $resp->product = $allProucts;
    echo(json_encode($resp));
}
?>
