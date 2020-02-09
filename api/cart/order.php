<?php
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# Root Path
include('../../root.php');

include(HELPER_PATH."/authenticationHelper.php");

define('CART', 'cart');

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
if($verb = 'post'){
    
    try{
        $data = json_decode(trim(file_get_contents("php://input")), true);
        if(isValidData($data)){
            updateCartStatusOfUser($data, $user);

        }else{
            throw new Exception("Invalid Data");
        }
    }catch(Exception $e){
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Failed to get order history";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }
    http_response_code(200);
    $resp = new stdClass();
    $resp->message = "Success";
    echo json_encode($resp);
}else{
    throw new Exception("Invalid Cart Data");
}

function isValidData($data){
    return isset($data['shippingAddress']) &&
    isset($data['purchasedDate']) &&
    isset($data['paymentMethod']);
}

function updateCartStatusOfUser($data, $user){
    //Create connection through Database class
    $database = new Database();
    $dbConn = $database->getConnection();
    $updateCmd = 'UPDATE ' . CART . ' SET ' .CART.'.CartStatus = :purchased, ' 
                .CART.'.ShippingAddress = :shippingAddress, '
                .CART.'.PaymentMethod = :paymentMethod, '
                .CART.'.PurchasedDate = :purchasedDate 
                WHERE '.CART. '.UserID = :userID AND '.CART.'.CartStatus = :currentStatus';
    $sql = $dbConn->prepare($updateCmd);
    $sql->bindValue(':userID',$user['ID']);
    $sql->bindValue(':currentStatus', 0);
    $sql->bindValue(':purchased', 1);
    $sql->bindValue(':shippingAddress',$data['shippingAddress']);
    $sql->bindValue(':paymentMethod',$data['paymentMethod']);
    $sql->bindValue(':purchasedDate',$data['purchasedDate']);
    $sql->execute();
}

?>