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
    $productData = array();
    $productData = getProduct();
} 
else {
    http_response_code("403");
    echo '{}';
}

# Read one product buy id of product in database
function getProduct()
{
    
    //$data = json_decode(trim(file_get_contents("php://input")), true);

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
        #reorganising json
        $data =  array(
            'ID' => $productData['ID'],
            'description' => $productData['Description'],
            'image' => $productData['Image'],
            'pricing' => $productData['Pricing'],
            'shippingCost' => $productData['ShippingCost']);

        $resp = new stdclass();
        $resp->product = $data;
        echo(json_encode($resp));
        


    }

?>