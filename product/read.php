<?php 
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# Read one product buy id of product in database

# Sending back to client
echo "{}"
?>