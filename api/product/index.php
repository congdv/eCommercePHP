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
        foreach($allProucts as $arr)
        {
        $data =  array(
            'ID' => $arr['ID'],
            'description' => $arr['Description'],
            'image' => $arr['Image'],
            'pricing' => $arr['Pricing'],
            'shippingCost' => $arr['ShippingCost']);
        }

        $resp = new stdclass();
        $resp->products = $data;
        echo(json_encode($resp));
    }
?>
