<?php 
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

define('TABLE', 'product');

# Secret key for JWT
define('SCERET_KEY','eyJ0eXAiOi');

# Database Connection
include "../config/database.php";
# Read one product buy id of product in database

# Sending back to client

?>