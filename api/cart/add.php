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

define('CART', 'cart');
define('CART_DETAILS', 'cart_details');
define('PRODUCT', 'product');

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
        //get CartID for the User
        $cartID = addProductToCart($user);
        if($cartID){
            $data = json_decode(trim(file_get_contents("php://input")), true);
            if(isValidData($data)){
                //Check if product exist in product List
                if(isProductFound($data)){
                    //if productID and cartID found then add to DB
                    addProductToDB($data, $cartID);
                    http_response_code(200);
                    $resp = new stdClass();
                    $resp->message = "Successfully Added";
                    echo json_encode($resp);
                }else{
                    throw new Exception("Product Not Found");
                }
            }
            else{
                throw new Exception("Invalid Cart Data");
            }
        }
    }
    catch(Exception $e)
    {
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Failed to add product";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }  
}

# Read all purchased that the user bought it
function addProductToCart($user){
    try{
        $database = new Database();
        $dbConn = $database->getConnection();
        
        //get current cartID for user (CartStatus is zero for current cart)
        $cmd = 'SELECT * FROM '.CART.' WHERE '.CART.'.UserID = :id AND '.CART.'.CartStatus = :cartStatus';
        $sql = $dbConn->prepare($cmd);
        $sql->bindValue(':id',$user['ID']);
        $sql->bindValue(':cartStatus', 0);
        $sql->execute();
        
        //return a single row
        $currentCartID = $sql->fetch(PDO::FETCH_ASSOC);

        if($currentCartID['CartID']){
            return $currentCartID['CartID'];
        }
        else{
            //if doesn't exist create new cart ID for the user
            $insertCmd = 'INSERT INTO '.CART.' (CartID, UserID, CartStatus) VALUES ( null, :id , :cartStatus);';
            $sql = $dbConn->prepare($insertCmd);
            $sql->bindValue(':id',$user['ID']);
            $sql->bindValue(':cartStatus', 0);
            $sql->execute();

            //store the created CartID in a variable
            $createdId = $dbConn->lastInsertId();
            return $createdId;
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

function isValidData($data){
    return isset($data['productID']) &&
    isset($data['quantities']);
}

function addProductToDB($data, $cartID){
    $database = new Database();
    $dbConn = $database->getConnection();
    
    $addToDbCmd = 'INSERT INTO ' . CART_DETAILS . '(CartDetailsID, ProductID, Quantities, CartID) 
                    VALUES (:cartDetailsID, :productID, :quantities, :cartID);';    
    $sql = $dbConn->prepare($addToDbCmd);
    $sql->bindValue(':cartDetailsID','null');
    $sql->bindValue(':productID', isset($data['productID']) ? $data['productID'] : '');
    $sql->bindValue(':quantities', isset($data['quantities']) ? $data['quantities'] : '');
    $sql->bindValue(':cartID', isset($cartID) ? $cartID : '');
    $sql->execute();
}

function isProductFound($data){
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'SELECT ID FROM '.PRODUCT.' WHERE ID = :productID;';
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue('productID',$data['productID']);
    $sql->execute();
    return $sql->rowCount() > 0 ? true : false;
}
    
?>