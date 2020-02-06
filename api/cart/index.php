<?php 
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

#ecommerce database connection
// include "../../config/database.php";

# Root Path
include('../../root.php');

include(HELPER_PATH."/utilsHelper.php");
include(HELPER_PATH."/authenticationHelper.php");

define('CART', 'cart');
define('CART_DETAILS', 'cart_details');

# Require Authentication first
$token = getTokenFromAuthorizationHeader();
$user = getAuthenticationUser($token);

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
if($verb == 'get'){
    try
    {
        $cartProducts = userCart($user); 

        # Sending back to client
        if(!empty($cartProducts)){
            sendResponseToClient($cartProducts);
        }
    }
    catch(Exception $e)
    {
        http_response_code(401);
        $resp = new stdClass();
        $resp->error = "No Data";
        $resp->message = "No product select.";
        echo json_encode($resp);
    }
        
}else{
    http_response_code("403");
    echo '{}';
}

# Read all current Cart items of the User
function userCart($user){
    try{
        $database = new Database();
        $dbConn = $database->getConnection();
    
        //get current cartID for user (CartStatus is zero for current cart)
        $cmd = 'SELECT * FROM '.CART.' WHERE '.CART.'.UserID = '.$user['ID']. ' AND '.CART.'.CartStatus ='. 0;
        $sql = $dbConn->prepare($cmd);
        $sql->execute();
        
        //return a single row
        $cartID = $sql->fetch(PDO::FETCH_ASSOC);
        
        if($cartID != null){
            //get product details for cartID assigned to a user
            $cmdjoin = 'SELECT * FROM '.CART.' INNER JOIN '.CART_DETAILS.' ON '.CART.'.CartID = '. CART_DETAILS.'.CartID 
                WHERE '.CART.'.CartID = '. $cartID['CartID'];
            $sql = $dbConn->prepare($cmdjoin);
            $sql->execute();

            $dataArray =array();
            while($data = $sql->fetch(PDO::FETCH_ASSOC))
            {
            $data =  array(
                'cartDetailsID' => $data['CartDetailsID'],
                'cartID' => $data['CartID'],
                'productID' => $data['ProductID'],
                'quantities' => $data['Quantities']);
                array_push($dataArray,$data);
            }
            return $dataArray;
        }else{
            return null;
        }
    }
    catch(Exception $e){
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Request Failed";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }
}

function sendResponseToClient($cartProducts){
    $resp = new stdclass();
    $resp->cartProduct = $cartProducts;
    echo(json_encode($resp));
}

?>