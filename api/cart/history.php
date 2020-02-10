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
        $carts = getOrderHistory($user); 

        # Sending back to client
        $resp = new stdClass();
        $resp->carts = $carts;
        echo json_encode($resp);
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
function getOrderHistory($user){
    try{
        $database = new Database();
        $dbConn = $database->getConnection();
    
        //get cartID for user with CartStatus is one
        $cmd = 'SELECT * FROM '.CART.' WHERE '.CART.'.UserID = :id AND '.CART.'.CartStatus = :cartStatus';
        $sql = $dbConn->prepare($cmd);
        $sql->bindValue(':id',$user['ID']);
        $sql->bindValue(':cartStatus', 1);
        $sql->execute();

        // Get all products in each cart
        $carts = array();
        while($row = $sql->fetch(PDO::FETCH_ASSOC)) {
            $cart = getProductFromCart($row);
            array_push($carts,$cart);
        }

        return $carts;
        
        
    }
    catch(Exception $e){
        throw new Exception("No Orders!");
    }

}

function getProductFromCart($cart) {
    if($cart['CartID']){
        $database = new Database();
        $dbConn = $database->getConnection();
        $getProductsCmd = 'SELECT * FROM '.CART.' 
            INNER JOIN '.CART_DETAILS.'
            ON '.CART.'.CartID = '.CART_DETAILS.'.CartID                
            INNER JOIN '.PRODUCT.'
            ON '.CART_DETAILS.'.ProductID = '.PRODUCT.'.ID
            WHERE '.CART.'.CartID = :cartID';
        
        $sql = $dbConn->prepare($getProductsCmd);
        $sql->bindValue(':cartID', $cart['CartID']);
        $sql->execute();

        $products = array();
        while($data = $sql->fetch(PDO::FETCH_ASSOC))
        {
            //return everything from product table as well
            $product =  array(
                'cartDetailsID' => $data['CartDetailsID'],
                'productID' => $data['ProductID'],
                'quantities' => $data['Quantities'],
                'description' => $data['Description']
            );
            array_push($products,$product);
        }

        //sending response to client
        $returnedCart = new stdClass();
        $returnedCart->cartID = $cart['CartID'];
        $returnedCart->products = $products;
        $returnedCart->purchasedDate = $cart['PurchasedDate'];
        $returnedCart->shippingAddress = $cart['ShippingAddress'];
        $returnedCart->paymentMethod = $cart['PaymentMethod'];
        return $returnedCart;

    }       
}

?>