<?php 
// Allow POST Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# Root Path
include('../../root.php');
include(CONFIG_PATH.'/database.php');
include(LIB_PATH.'/JWT.php');
include(HELPER_PATH.'/constants.php');

# Constants

define('TABLE', 'user');



$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'post') {
   login();
} else {
    http_response_code("403");
    echo '{}';
}

function login() 
{ 
    $isLoggedIn = false;
    # data from client
    $data = json_decode(trim(file_get_contents("php://input")), true);
    $user = getUser($data);

    #verify password 
    if(isValidPassword($user, $data)) {
        http_response_code(200);
        $resp = new stdClass();
        $resp->token = JWT::encode($user['ID'],SECRET_KEY);
        $resp->firstName = $user['FirstName'];
        $resp->lastName = $user['LastName'];
        $resp->username = $user['Username'];
        echo json_encode($resp);
    } else {
        http_response_code(401);
        $resp = new stdClass();
        $resp->error = "failed login";
        $resp->message = "Invalid username and password";
        echo json_encode($resp);
    }

}

# Find the user in datbase by username and password
function getUser($data)
{
    if(!isset($data) || !isset($data['username'])) 
    {
        return NULL;
    }
    $database = new Database();
    $dbConn = $database->getConnection();

    $cmd = 'SELECT * FROM '.TABLE.' WHERE Username =:username';
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':username',$data['username']);
    $sql->execute();
    $user = $sql->fetch(PDO::FETCH_ASSOC);
    return $user;
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
?>