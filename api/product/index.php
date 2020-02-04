<?php 
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

<<<<<<< HEAD
define('TABLE', 'product');

# Database Connection
include( "../../config/database.php");

$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'get') {
    try 
    {
        $allProucts = getProducts();   
        sendDataToClient($allProucts);

    }
    catch(Exception $e)
    {
        http_response_code(401);
        $resp = new stdClass();
        $resp->error = "No Data";
        $resp->message = "No products in Database";
        echo json_encode($resp);
    }
} 
else {
    http_response_code("403");
    echo '{}';
}

=======
>>>>>>> 703307fd776851d7f05d2b018c94187e2a1aa458
# Read all products in database

# Sending back to client
<<<<<<< HEAD
function sendDataToClient($allProucts)
{
    $resp = new stdclass();
    $resp->product = $allProucts;
    echo(json_encode($resp));

}

?>
=======
echo "{}"
?>
>>>>>>> 703307fd776851d7f05d2b018c94187e2a1aa458
