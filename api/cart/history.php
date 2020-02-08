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
define('PRODUCT', 'product');

include(HELPER_PATH."/authenticationHelper.php");

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
        
}

//Read all the items User purchased
function userProducts($user){
    try{
        //print_r($user);
        $database = new Database();
        $dbConn = $database->getConnection();
    
        //get cartID for user with CartStatus is one
        $cmd = 'SELECT * FROM '.CART.' WHERE '.CART.'.UserID = :id AND '.CART.'.CartStatus = :cartStatus';
        $sql = $dbConn->prepare($cmd);
        $sql->bindValue(':id',$user['ID']);
        $sql->bindValue(':cartStatus', 1);
        $sql->execute();

        //return a single row
        $cartID = $sql->fetch(PDO::FETCH_ASSOC);
        echo ($cartID['CartID']);
        //get products in the CartID fetched
        if($cartID['CartID']){
            $getProductsCmd = 'SELECT * FROM '.CART.' 
                INNER JOIN '.CART_DETAILS.'
                ON '.CART.'.CartID = '.CART_DETAILS.'.CartID                
                INNER JOIN '.PRODUCT.'
                ON '.CART_DETAILS.'.ProductID = '.PRODUCT.'.ID
                WHERE '.CART.'.CartID = :cartID';
            
            $sql = $dbConn->prepare($getProductsCmd);
            $sql->bindValue(':cartID', $cartID['CartID']);
            $sql->execute();

            $dataArray = array();
            while($data = $sql->fetch(PDO::FETCH_ASSOC))
            {
                //return everything from product table as well
                $data =  array(
                    'cartDetailsID' => $data['CartDetailsID'],
                    'productID' => $data['ProductID'],
                    'quantities' => $data['Quantities'],
                    'description' => $data['Description']
                );
                array_push($dataArray,$data);
            }

            //sending response to client
            $res = new stdClass();
            $res->cartID = $cartID['CartID'];
            $res->products = $dataArray;
            return $res;

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