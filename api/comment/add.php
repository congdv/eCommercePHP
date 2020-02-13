<?php 
// Allow POST Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# Root Path
include('../../root.php');
include(HELPER_PATH."/utilsHelper.php");
include(HELPER_PATH."/authenticationHelper.php");
include(HELPER_PATH."/responseHelper.php");

#define table and columns
define('COMMENT_TABLE','comment');
define('IMAGE_TABLE','comment_image');

# Require Authentication first
$token = getTokenFromAuthorizationHeader();
$user = getAuthenticationUser($token);
$userID = $user['ID'];
#echo $userID;
#die;

// Not found user from token
if(!$user) {
    http_response_code("401");
    $error = new stdClass();
    $error->error = "Forbidden Request";
    $error->message = "Request has invalid authentication credentials";
    echo json_encode($error);
    return;
}

# Authentication of User
$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == 'post') {
    $data = json_decode(trim(file_get_contents("php://input")), true);
    if(verifyPurchase($data)) {
        $comment_id = inserNewCommentToDB($data);
		if($comment_id) {
			addNewImages($data,$comment_id);
			http_response_code(200);
			$result['success'] = true;
			$result['message'] = "Comment added successfully";
			echo json_encode($result);
		} else {
			http_response_code(200);
			$result['success'] = false;
			$result['message'] = "Something went wrong!!";
			echo json_encode($result);
		}
	}
    else{
        http_response_code("401");
        $error = new stdClass();
        $error->error = "Forbidden add new comment";
        $error->message = "This user is not allowed to add comment(s). ";
        echo json_encode($error);
        return;
    }
} 
else {
    unknownEndpointsResponse();
}

function getUserID()
{
    $token = getTokenFromAuthorizationHeader();
    $user = getAuthenticationUser($token);
    $result = $user['ID'];
    return $result;    
}
#verify user returns true if user exists with product.
function verifyPurchase($data)
{
    try{
        $userID = getUserID();
        # getCartIDfromProduct($data['productID'],$userID);
        $database = new Database();
        $dbConn = $database->getConnection();
        $cmd = 'SELECT cart.UserID, cart_details.ProductID 
                FROM cart 
                INNER JOIN cart_details
                ON cart.CartID = cart_details.CartID 
                WHERE cart.UserID = :userID AND cart_details.ProductID = :productID AND cart.CartStatus = 1';
        $sql = $dbConn->prepare($cmd);
        $sql->bindValue(':userID', $userID);
        $sql->bindValue(':productID', $data['productID']);
        $sql->execute();
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if($result){
            return true;
        }else{
            return false;
        }
    }
    catch(Exception $e)
    {
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Failed to verify purchase";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }
}

function getCartIDfromProduct($productID) {
    $database = new Database();
    $dbConn = $database->getConnection();

    $cmd = "SELECT CartID FROM cart_details WHERE ProductID = :productID";
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':productID', $productID);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    print_r($result);

}

# comment function 
function inserNewCommentToDB($data)
{
    $userID = getUserID();
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'INSERT INTO ' . COMMENT_TABLE . ' (UserID,ProductID,Comment,Rating) ' . 
    ' VALUES (:userID, :productID, :comment, :rating) ';
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':userID', $userID);
    $sql->bindValue(':productID', $data['productID']);
    $sql->bindValue(':comment', $data['comment']);
    $sql->bindValue(':rating', $data['rating']);
    $sql->execute();
    #lastInsertID is inbuild functions of PHP which returns last inserted row.
	return $dbConn->lastInsertId();
}
# add images to comment function
function addNewImages($data,$comment_id)
{
    $database = new Database();
    $dbConn = $database->getConnection();
	if($data['images']) {
		foreach($data['images'] as $image) {
			$cmd = 'INSERT INTO ' . IMAGE_TABLE . ' (commentID,Path) ' . 
			' VALUES (:commentID, :path) ';
			$sql = $dbConn->prepare($cmd);
			$sql->bindValue(':commentID', $comment_id);
			$sql->bindValue(':path', $image);
			$sql->execute();
		}
	}
}
?>