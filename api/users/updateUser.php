<?php 
# Allow POST Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# ecommerce database connection
include "../../config/database.php";  
define('TABLE', 'user');

#users can not change ID and Username 
define('COLUMNS', 'Email,Password,Firstname,Lastname,ShippingAddress');

#  Checking Authentication of User first
$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'post') {
  updateUser();
} else {
    http_response_code("403");
    echo '{}';
}

# Main Update User function
function updateUser(){
    try{
        $data = json_decode(trim(file_get_contents("php://input")), true);
        if(isValidUserUpdate($data)) {
            updateUserToDatabase($data);
        } else {
            throw new Exception("Invalid User Data");
        }
        http_response_code(200);
        $resp = new stdClass();
        $resp->message = "Successfully register";
        echo json_encode($resp);
    }catch(Exception $e)
    {
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Failed Update";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }
}

# Validation code update request
function isValidUserUpdate($user)
{
    return isset($user['email']) &&
    isset($user['password']) &&
    isset($user['firstName']) &&
    isset($user['lastName']) &&
    isset($user['shippingAddress']) &&
    filter_var($user['email'], FILTER_VALIDATE_EMAIL) &&
    strlen($user['password']) > 6;
    strlen($user['shippingAddress']) > 8;
}

#update user to database
# userID is provided by token for comparing ID in Update Query;
function updateUserToDatabase($user)
{
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'UPDATE' . TABLE . 
           ' SET Email = :email, Password = :password, FirstName = :firstName, LastName = :lastName , ShippingAddress = :shippingAddress' .
           'WHERE ID = :id' ;
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':email', $user['email']);
    $sql->bindValue(':password', password_hash($user['password'], PASSWORD_BCRYPT));
    $sql->bindValue(':firstName', isset($user['firstName']) ? $user['firstName'] : '');
    $sql->bindValue(':lastName', isset($user['lastName']) ? $user['lastName'] : '');
    $sql->bindValue(':shippingAddress', $user['shippingAddress']);
    $sql->bindValue(':id', $user['id']);
    $sql->execute();
   
}

?>