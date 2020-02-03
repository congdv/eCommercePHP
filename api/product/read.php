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
if($verb == 'get') 
{
    try
    {
        $productData = array();
        $productData = getProduct();
    }
    catch(Exception $e)
    {
        http_response_code(401);
        $resp = new stdClass();
        $resp->error = "No Data";
        $resp->message = "No product select.";
        echo json_encode($resp);
    }
} 
else {
    http_response_code("403");
    echo '{}';
}

# Read one product buy id of product in database
function getProduct()
{
    if(!isset( $_GET['id'])) 
    {
        return NULL;
    }
    else
    {
        # get product id from user
        $productID =  $_GET['id'];
        $database = new Database();
        $dbConn = $database->getConnection();
        $cmd = 'SELECT * FROM '.TABLE.' WHERE ID = '.$productID;
        $sql = $dbConn->prepare($cmd);
        $sql->execute();
        $product = $sql->fetch(PDO::FETCH_ASSOC);
        #reorganising json
        $product =  array(
            'ID' => $productData['ID'],
            'description' => $productData['Description'],
            'image' => $productData['Image'],
            'pricing' => $productData['Pricing'],
            'shippingCost' => $productData['ShippingCost']);
        return $product;
    }
}

# Sending back to client
if(!isset($productData))
    {
        echo "{}";
    }
    else
    {
        $resp = new stdclass();
        $resp->product = $productData;
        echo(json_encode($resp));
    }
?>