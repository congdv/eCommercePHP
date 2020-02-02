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
    $productData = getProduct();
} 
else {
    http_response_code("403");
    echo '{}';
}

# Read one product buy id of product in database
function getProduct()
{
    # data from client
    $data = json_decode(trim(file_get_contents("php://input")), true);

    if(!isset($data) || !isset($data['ID'])) 
    {
        return NULL;
    }
    else
    {
        $database = new Database();
        $dbConn = $database->getConnection();
        $cmd = 'SELECT * FROM '.TABLE.' WHERE ID = '.$data['ID'];
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
        $resp = new stdclass();
        $resp->product = $productData;
        echo(json_encode($resp));
    }

?>