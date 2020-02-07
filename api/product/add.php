<?php 
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# root path
include("../../root.php");

# Database Connection
include(CONFIG_PATH."/database.php");

# authentication functions
include(HELPER_PATH."/authenticationHelper.php");

# Table name

define("TABLE","product");

# Require Authentication first
$user = getAuthenticationUser();

// Not found user from token
if(!$user) {
    http_response_code("401");
    $error = new stdClass();
    $error->error = "Forbidden Request";
    $error->message = "Request has invalid authentication credentials";
    echo json_encode($error);
    return;
}

$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == "post") {
    try {
        $data = json_decode(trim(file_get_contents("php://input")), true);
        if(isValidInsertNewProduct($data)) {

            insertProductToDB($data);
            http_response_code(200);
            $resp = new stdClass();
            $resp->success = true;
            $resp->message = "Successfully add new product";
            echo json_encode($resp);
        } else {
            throw new Exception("Invalid Product Data");
        }
    }catch ( Exception $e) {
        http_response_code(400);
        $error = new stdClass();
        $error->error = "failed to add new product";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }
}

# Check product data is valid for inserting
function isValidInsertNewProduct($product)
{
    return isset($product['name']) &&
    isset($product['description']) &&
    isset($product['image']) && 
    isset($product['price']) && 
    isset($product['shippingCost']);
}

# Insert product to database
function insertProductToDB($product) 
{
    //Create connection through Database class
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'INSERT INTO ' . TABLE . ' (Name, Description, Image, Pricing, ShippingCost) ' .
        'VALUES (:name, :description, :image, :price, :shippingCost)';
    
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':name', $product['name']);
    $sql->bindValue(':description', $product['description'] );
    $sql->bindValue(':image', $product['image']);
    $sql->bindValue(':price', $product['price']);
    $sql->bindValue(':shippingCost', $product['shippingCost']);
    $sql->execute();
}

?>