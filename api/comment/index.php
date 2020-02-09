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
define('USER_TABLE','user');
define('COMMENT_TABLE', 'comment');
define('IMAGE_TABLE','comment_image');

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
    $comment = array();
    //$comment = getComments();
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

        $cmd = 'SELECT * FROM '.COMMENT_TABLE.' AS C 
		INNER JOIN '.USER_TABLE.' AS UI ON C.UserID = UI.ID 
        WHERE C.ProductID ='.$data['productID'];
        
        $sql = $dbConn->prepare($cmd);
        $sql->execute();
        $final_data = array();
		$comments_data = $sql->fetchAll(PDO::FETCH_ASSOC);
		if($comments_data) {
			foreach($comments_data as $i => $temp) {
				$final_data[$i]['commentID'] = $temp['CommentID'];
				$final_data[$i]['userName'] = $temp['Username'];
				$final_data[$i]['firstName'] = $temp['FirstName'];
				$final_data[$i]['comment'] = $temp['Comment'];
	 
				$images = array();
				$cmd = 'SELECT * FROM '.IMAGE_TABLE.' WHERE CommentID = '.$temp['CommentID'];
				$sql = $dbConn->prepare($cmd);
				$sql->execute();
				$image_array = $sql->fetchAll(PDO::FETCH_ASSOC);
				foreach($image_array as $image_arr) {
				  $images[] = $image_arr['Path'];
				}			
				$final_data[$i]['images'] = $images;
			}
		}
		
        http_response_code(200);
        $resp = new stdClass(); 
		$resp->productID = $data['productID'];
		$resp->comments = $final_data;
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
function getImages($temp)
{
    $cmd = 'SELECT * FROM '.IMAGE_TABLE.' WHERE CommentID = '.$temp['CommentID'];
    $sql = $dbConn->prepare($cmd);
    $sql->execute();
    while ($image_arr = $sql->fetch(PDO::FETCH_ASSOC)) {
      $images[] = $image_arr['Path'];
    }	    
}
/*function getComments($data)
{
    if(!isset($_POST['productID']))
    {
        throw new Exception("could not find product id");
    }
    else
    {
        $database = new Database();
        $dbConn = $database->getConnection();
        $cmd = 'SELECT * FROM '.COMMENT_TABLE.' AS C 
        INNER JOIN '.IMAGE_TABLE.' AS CI 
        ON C.CommentID = CI.CommentID 
        WHERE C.ProductID ='.$data['productID'];
        
    }
}*/
# additional comment to a product
# echo '{}';
?>