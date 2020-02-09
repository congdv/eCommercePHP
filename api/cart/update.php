<?php 
// Allow POST Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# Root Path
include('../../root.php');

include(HELPER_PATH."/authenticationHelper.php");
include(HELPER_PATH."/responseHelper.php");

define('CART', 'cart');
define('CART_DETAILS', 'cart_details');

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
# Get all products from cart
$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'post'){
    try
    {
        $currentCartID = getCurrentCartIdOfUser($user);
        if($currentCartID) {
            $data = json_decode(trim(file_get_contents("php://input")), true);
            updateQuantitiesOfProduct($currentCartID,$data);
            succesResponse(true,"Succesfully Change quantities of the product",NULL);
        } else {
            throw new Exception("The user doesn't have any cart");
        }
    }
    catch(Exception $e)
    {
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Failed to change quantities of product in cart";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }  
}

# Get current Cart ID of the user
function getCurrentCartIdOfUser($user) 
{
    $database = new Database();
    $dbConn = $database->getConnection();
    
    $cmd = 'SELECT CartID FROM '.CART.' WHERE '.CART.'.UserID = :id AND '.CART.'.CartStatus = 0';
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':id',$user['ID']);
    $sql->execute();
    $cartID = $sql->fetch(PDO::FETCH_ASSOC);
    return $cartID && isset($cartID['CartID']) ? $cartID['CartID'] : NULL;
}

function updateQuantitiesOfProduct($cartID, $data) {
    $database = new Database();
    $dbConn = $database->getConnection();

    $cmd = 'UPDATE ' . CART_DETAILS . 
           ' SET Quantities = :quantities' .
           ' WHERE CartID = :cartID AND ProductID = :productID';
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue('quantities',$data['quantities']);
    $sql->bindValue('cartID',$cartID);
    $sql->bindValue('productID',$data['productID']);
    $sql->execute();
}
?>