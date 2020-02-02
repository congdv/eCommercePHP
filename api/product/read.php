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
    
} 
else {
    http_response_code("403");
    echo '{}';
}

# Read one product buy id of product in database

# Sending back to client

?>