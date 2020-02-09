<?php 
# Allow POST Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# ecommerce database connection
include('../../root.php');
include(HELPER_PATH."/utilsHelper.php");
include(HELPER_PATH."/authenticationHelper.php");

define('TABLE', 'user');

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

#cheking request method
$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'post') {
  updateUser($user);
} else {
    http_response_code("403");
    echo '{}';
}

# Main Update User function
function updateUser($user){
    try{
        $data = json_decode(trim(file_get_contents("php://input")), true);
        if(isValidUserData($data)) {
            if(isValidPassword($user, $data)) {
                updateUserToDatabase($user, $data);

            } else {
                throw new Exception("Wrong password!!");
            }
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
function isValidUserData($data)
{
    return isset($data['email']) &&
    isset($data['firstName']) &&
    isset($data['lastName']) &&
    isset($data['shippingAddress']) &&
    filter_var($data['email'], FILTER_VALIDATE_EMAIL) &&
    strlen($data['shippingAddress']) > 6;
}

#verify the password match
function isValidPassword($user,$data) {
    if(isset($user) && isset($data['password'])) {
        if(password_verify($data['password'],$user['Password'])) {
            return true;
        }
        return false;
    }

    return false;
}

# update user to database
# userID is provided by token for comparing ID in Update Query;
function updateUserToDatabase($user, $data)
{
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'UPDATE ' . TABLE . 
           ' SET Email = :email, FirstName = :firstName, LastName = :lastName , ShippingAddress = :shippingAddress' .
           ' WHERE ID = :id' ;
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':email', $data['email']);
    $sql->bindValue(':firstName', $data['firstName']);
    $sql->bindValue(':lastName', $data['lastName']);
    $sql->bindValue(':shippingAddress', $data['shippingAddress']);
    $sql->bindValue(':id', $user['ID']);
    $sql->execute();
   
}

?>