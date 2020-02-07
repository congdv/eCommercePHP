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
include(HELPER_PATH."/authenticationHelper.php");

define('CART', 'cart');
define('CART_DETAILS', 'cart_details');
define('PRODUCT','Product');

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
if($verb == 'get'){
    try
    {
        $cartProducts = userCart($user); 
        # Sending back to client
        if(!empty($cartProducts)){
            sendResponseToClient($cartProducts);
        } else {
            //If the cart is empty
            http_response_code(200);
            $resp = new stdClass();
            $resp->products = array();
            echo json_encode($resp);
        }
    }
    catch(Exception $e)
    {
        http_response_code(401);
        $resp = new stdClass();
        $resp->error = "Invalid Data";
        $resp->message = "No product in Cart.";
        echo json_encode($resp);
    }
        
}else{
    http_response_code("403");
    $resp = new stdClass();
    $resp->error = "Invalid";
    $resp->message = "Unknown Endpoint";
    echo json_encode($resp);
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
            $cmdjoin = 'SELECT * FROM '.CART_DETAILS.
                        ' INNER JOIN '.PRODUCT.' ON '.CART_DETAILS.'.ProductID = '.PRODUCT.'.ID'.
                        ' WHERE '.CART_DETAILS.'.CartID = '. $cartID['CartID'];
            $sql = $dbConn->prepare($cmdjoin);
            $sql->execute();

            $dataArray = array();
            while($data = $sql->fetch(PDO::FETCH_ASSOC))
            {
                $data =  array(
                    'productID' => $data['ProductID'],
                    'name' => $data['Name'],
                    'image' => $data['Image'],
                    'price' => $data['Pricing'],
                    'shippingCost' => $data['ShippingCost'],
                    'quantities' => $data['Quantities'],
                    'subTotal' => (string)($data['Pricing'] * $data['Quantities'] + $data['ShippingCost']));
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
    $resp->products = $cartProducts;
    echo(json_encode($resp));
}

?>