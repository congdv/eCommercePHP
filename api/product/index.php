<?php 
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

define('TABLE', 'product');

# Database Connection
include "../../config/database.php";

$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'get') {
   $allProucts = getProducts();   
} 
else {
    http_response_code("403");
    echo '{}';
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
            'ID' => $data['ID'],
            'description' => $data['Description'],
            'image' => $data['Image'],
            'pricing' => $data['Pricing'],
            'shippingCost' => $data['ShippingCost']);
        array_push($dataArray,$data);
    }
    return $dataArray;
}

# Sending back to client
if(!isset($allProucts)) 
    {
        echo "{}";
    }
    else
    {
        $resp = new stdclass();
        $resp->products = $allProucts;
        echo(json_encode($resp));
    }
?>
