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
define('COLUMNS', 'Id, Email');


$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'post') {
    registerNewUser();
} else {
    http_response_code("403");
    echo '{}';
}

# register new user
function registerNewUser() 
{
    $newID = 0;
    try {
        $data = json_decode(trim(file_get_contents("php://input")), true);
        if(isValidInsertNewUser($data)) {
            if(isNotExistedUser($data)) {
                insertUserToDB($data);

            } else {
                throw new Exception("Username is existed!!");
            }

        } else {
            throw new Exception("Invalid User Data");
        }
    }catch (Exception $e) {
        http_response_code(400);
        $error = new stdClass();
        $error->error = "failed register";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }
    http_response_code(200);

    $resp = new stdClass();
    $resp->message = "Successfully register";
    echo json_encode($resp);
}

# validation code for user object on insert
function isValidInsertNewUser($user)
{
    return isset($user['email']) &&
    isset($user['password']) &&
    isset($user['username']) &&
    filter_var($user['email'], FILTER_VALIDATE_EMAIL) &&
    strlen($user['password']) > 6;
}

function isNotExistedUser($data)
{
    $database = new Database();
    $dbConn = $database->getConnection();

    $cmd = 'SELECT * FROM '.TABLE.' WHERE Username =:username';
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':username',$data['username']);
    $sql->execute();
    $user = $sql->fetch(PDO::FETCH_ASSOC);
    $result =  empty($user) ? "true": "false";
    return empty($user);
}

# Insert user to database
function insertUserToDB($user) 
{
    //Create connection through Database class
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'INSERT INTO ' . TABLE . ' (Email, Password, Username, FirstName, LastName) ' .
        'VALUES (:email, :password, :username, :firstName, :lastName)';
    
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':email', $user['email']);
    $sql->bindValue(':password', password_hash($user['password'], PASSWORD_BCRYPT));
    $sql->bindValue(':firstName', isset($user['firstName']) ? $user['firstName'] : '');
    $sql->bindValue(':lastName', isset($user['lastName']) ? $user['lastName'] : '');
    $sql->bindValue(':username', $user['username']);
    $sql->execute();
}
?>