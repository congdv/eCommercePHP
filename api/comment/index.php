<?php 
# Allow GET Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
#header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Methods: GET");
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
define('USER_TABLE','user');

# Require Authentication first
$token = getTokenFromAuthorizationHeader();
$user = getAuthenticationUser($token);

# checking GET method
$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'get') {
    $data = json_decode(trim(file_get_contents("php://input")), true);
    print_r($data);
    #$comment = array();
    #$comment = getComments();
    if(getProductID()){
        $productID = $_GET['productID'];
        getCommentsFromDB($productID);
    }
    else{
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Failed Get ProductID from Given URL";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }

} 
else {
    http_response_code("403");
    echo '{}';
}

function getProductID()
{
    #check ID is valid
    if(!isset($_GET['productID'])){
      return false;
    }
    else{
      return true;
    }
}
#  get existing comments from table 
function getCommentsFromDB($productID)
{
    try{
        $database = new Database();
        $dbConn = $database->getConnection();

        $cmd = 'SELECT * FROM '.COMMENT_TABLE.' AS C 
        INNER JOIN '.USER_TABLE.' AS UI 
        ON C.UserID = UI.ID 
        WHERE C.ProductID = :productID';

        $sql = $dbConn->prepare($cmd);
        $sql->bindValue(':productID',$productID);
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
                $cmd = 'SELECT * FROM '.IMAGE_TABLE.' WHERE CommentID = :commentID';
                $sql = $dbConn->prepare($cmd);
                $sql->bindValue(':commentID',$temp['CommentID']);
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
		$resp->productID = $productID;
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
?>