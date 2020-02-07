<?php 
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

define('TABLE', 'product');

# Root path
include("../../root.php");

# Database Connection

include (CONFIG_PATH."/database.php");

$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'get') {
    try {
        $productData = array();
        $productData = getProduct();

        if(!empty($productData)) {
            sendDataToClient($productData);
        } else {
            throw new Exception("Invalid id of product!");
        }
    }
    catch(Exception $e) {
        http_response_code(401);
        $resp = new stdClass();

        $resp->error = "Invalid Data";
        $resp->message = $e->getMessage();
        echo json_encode($resp);
    }
} 
else {
    http_response_code(401);
        $resp = new stdClass();
        $resp->error = "No Data";
        $resp->message = "No product to select.";
        echo json_encode($resp);
}

# Read one product buy id of product in database
function getProduct() {
    # Check id is valid
    if(!isset($_GET['id'])) {
        throw new Exception("Invalid id of product");
    }
    
    # get product id from user
    $productID =  $_GET['id'];
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'SELECT * FROM '.TABLE.' WHERE ID = :id';

    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':id', $productID);
    $sql->execute();
    $product = $sql->fetch(PDO::FETCH_ASSOC);

    if(empty($product)) {
       throw new Exception("Not found product!!");
    }

    else {
    #reorganising json
        $product =  array(
            'id' => $product['ID'],
            'name' => $product['Name'],
            'description' => $product['Description'],
            'image' => $product['Image'],
            'pricing' => $product['Pricing'],
            'shippingCost' => $product['ShippingCost']);
        return $product;
    }
}

# Sending back to client
    function sendDataToClient($productData) {
        $resp = new stdclass();
        $resp->product = $productData;
        echo(json_encode($resp));
    }
?>
