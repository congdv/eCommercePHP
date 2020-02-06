<?php
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# Root Path
include('../../root.php');

define('CART', 'cart');
define('CART_DETAILS', 'cart_details');

include(HELPER_PATH."/utilsHelper.php");
include(HELPER_PATH."/authenticationHelper.php");

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

# Get product history from cart(with cartStatus = 1)
$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'get'){
    try
    {
        $purchasedProducts = userProducts($user); 

        # Sending back to client
        echo (json_encode ($purchasedProducts));
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

//Read all the items User purchased
function userProducts($user){
    try{
        $database = new Database();
        $dbConn = $database->getConnection();
    
        //get cartID for user with CartStatus is one
        $cmd = 'SELECT * FROM '.CART.' WHERE '.CART.'.UserID = '.$user['ID']. ' AND '.CART.'.CartStatus ='. 1;
        $sql = $dbConn->prepare($cmd);
        $sql->execute();

        //return a single row
        $cartID = $sql->fetch(PDO::FETCH_ASSOC);
        echo ($cartID['CartID']);
        //get products in the CartID fetched
        if($cartID['CartID']){
            // $getProductsCmd = 'SELECT * FROM '.CART_DETAILS.'';
            $getProductsCmd = 'SELECT * FROM '.CART.' INNER JOIN '.CART_DETAILS.' ON '.CART.'.CartID = '. CART_DETAILS.'.CartID 
                WHERE '.CART.'.CartID = '. $cartID['CartID'];
            $sql = $dbConn->prepare($getProductsCmd);
            $sql->execute();
            
            $dataArray = array();
            while($data = $sql->fetch(PDO::FETCH_ASSOC))
            {
                //return everything from product table as well
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

?>