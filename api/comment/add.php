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
    #print_r ($data);
    if(verifyPurchase($data)) {
		$comment_id = addComment($data);
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
        $error->error = "Forbidden Request";
        $error->message = "This user is not allowed to add comment(s). ";
        echo json_encode($error);
        return;
    }
} 
else {
    http_response_code("403");
    echo '{}';
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
        $database = new Database();
        $dbConn = $database->getConnection();
        $cmd = 'SELECT C.UserID, CD.ProductID 
                FROM cart AS C 
                INNER JOIN cart_details AS CD 
                ON C.CartID = CD.CartID 
                WHERE C.UserID = :userID';
        $sql = $dbConn->prepare($cmd);
        $sql->bindValue(':userID', $userID);
        $sql->execute();
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        #print_r($result); 
        #die();
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

# comment function 
function addComment($data)
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
# DO NOT REMOVE THESE FUNCTIONS.
# fetch commentId from comment-table
/* function fetchCommentID($data)
{
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'SELECT CommentID FROM '. COMMENT_TABLE .
           ' WHERE UserID = :userID AND ProductID = :productID';
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':userID',$data['userID']);
    $sql->bindValue(':productID',$data['productID']);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    return $result;
} */

# fetch comment data of product id
/*function verifyData($data)
{
    try{
        $database = new Database();
        $dbConn = $database->getConnection();
		
        $cmd = 'SELECT * FROM '.COMMENT_TABLE.' WHERE ProductID =' :productID' and UserID=' :userID;
        $sql = $dbConn->prepare($cmd);
        $sql->bindValue(':productID',$data['userID']);
        $sql->bindValue(':userID',$data['userID']);
		$sql->execute();
		$comment_data=$sql->fetchAll(PDO::FETCH_ASSOC);
        
        #print_r($comment_data); 
        #die();
        
        $final_data = array();
		if($comment_data) {
			foreach($comment_data as $i => $comment) {
				$final_data[$i]['userID'] = $comment['UserID'];
				$final_data[$i]['productID'] = $comment['ProductID'];
				$final_data[$i]['comment'] = $comment['Comment'];
				$final_data[$i]['rating'] = $comment['Rating'];
				
				$images = array();
				$cmd = 'SELECT * FROM '.IMAGE_TABLE.' WHERE CommentID = ' :commentID';
                $sql = $dbConn->prepare($cmd);
                $sql->bindValue(':commentID',$comment['CommentID']);
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
		$resp->comments = $final_data;
        echo json_encode($resp);
    }
    catch(Exception $e)
    {
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Data not found";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }
}*/
?>