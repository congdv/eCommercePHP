<?php 
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

#ecommerce database connection
include "../../confid/database.php";

# Root Path
include('../../root.php');

include(HELPER_PATH."/utilsHelper.php");
include(HELPER_PATH."/authenticationHelper.php");

define('CART', 'cart');
define('COLUMNS', 'CartID','UserID');

define('CART_DETAILS', 'cart_details');
define('COLUMNS', 'CartDetailsID','ProductID', 'Quantities', 'CartID');



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
$verb = strtolower($_SERVER['REQUEST_METHOD]']);
if($verb == 'post'){
    userCart();
}else{
    http_response_code("403");
    echo '{}';


# Read all purchased that the user bought it
function userCart(){
    try{
        $data = json_decode(trim(file_get_contents("php://input")),true);
        if(isUserValid($data)){
            getUserCart($data);
        }else{
            throw new Exception("Invalid User");
        }
    }
    catch(Exception $e){
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Request Failed";
        $resp->message = $e->getMessage();
        echo json_encode($error);
        return;
    }
}

function isUserValid($user){
    return isset($user['userID'])
}

function getUserCart($user){
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'SELECT * FROM'.CART.
            'WHERE UserID ='. $user['userID'] AND . 'CART' ;
    $sql = $dbConn->prepare($cmd);
    $sql->execute();
            //SELECT * FROM cart AS c
	//INNER JOIN cart_details AS cd ON cd.CartID = c.CartID;

}

# Sending back to client
echo "{}";
?>