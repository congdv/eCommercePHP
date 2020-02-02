<?php 
// Allow POST Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# Root Path
include('../../root.php');
include(CONFIG_PATH."/database.php");
include(HELPER_PATH."/utilsHelper.php");
include(HELPER_PATH."/authenticationHelper.php");

#define table and columns
define('COMMENT_TABLE', 'comment');
define('IMAGE_TABLE','comment_image');

#define('COLUMNS', 'CommentID,UserID,ProductID,Comment,Rating');

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

# checking authentication of user
$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'post') {
    $data = json_decode(trim(file_get_contents("php://input")), true);
    #print_r($data);
    getCommentsFromDB($data);
} 
else {
    http_response_code("403");
    echo '{}';
}

#  get existing comments from table 
function getCommentsFromDB($data)
{
    try{
        $database = new Database();
        $dbConn = $database->getConnection();

        $cmd = 'SELECT * FROM '.COMMENT_TABLE.' AS C INNER JOIN '.IMAGE_TABLE.' AS CI ON C.CommentID = CI.CommentID WHERE C.ProductID ='.$data['ProductID'];

        #$cmd = 'SELECT * FROM '.COMMENT_TABLE.' WHERE ProductID='.$data['ProductID'];

        $sql = $dbConn->prepare($cmd);
        $sql->execute();
        $temp=$sql->fetch(PDO::FETCH_ASSOC);  
        print_r($temp);
        http_response_code(200);
        $resp = new stdClass(); 
        $resp->message = "data retrived from db";
        echo json_encode($resp);
    }
    catch(Exception $e){
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Failed to load comments about product";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }
}
# Add comment to a product

echo "\n {end of code}";
?>