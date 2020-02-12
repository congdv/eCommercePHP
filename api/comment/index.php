<?php 
# Allow GET Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# Root Path
include('../../root.php');
include(CONFIG_PATH."/database.php");
include(HELPER_PATH."/utilsHelper.php");
include(HELPER_PATH."/authenticationHelper.php");
include(HELPER_PATH."/responseHelper.php");

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
    try {
        if(getProductID()){
            $productID = $_GET['productID'];
            $comments = getCommentsFromDBOf($productID);
    
            $resp = new stdClass();
            $resp->productID = $productID;
            $resp->comments = $comments;
            echo json_encode($resp);
        }
        else{
            throw new Exception("Invalid Product ID");
        }
    }catch(Exception $e) {
        errorResponse("Invalid Data",$e->getMessage());
    }
    

} 
else {
    unknownEndpointsResponse();
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
function getCommentsFromDBOf($productID)
{
    try{
        $database = new Database();
        $dbConn = $database->getConnection();

        $cmd = 'SELECT * FROM comment WHERE ProductID = :productID';

        $sql = $dbConn->prepare($cmd);
        $sql->bindValue(':productID',$productID);
        $sql->execute();
        $comments = $sql->fetchAll(PDO::FETCH_ASSOC);
        $commentsResp = array();
        foreach($comments as $comment) {;
            $commentObject =  array(
                'commentID' => $comment['CommentID'],
                'comment' => $comment['Comment'],
                'rating' => $comment['Rating'],
                'images' => getImagesOf($comment['CommentID']));
            array_push($commentsResp,$commentObject);

        }
        return $commentsResp;
    }
    catch(Exception $e){
        throw new Exception("Failed to load comments about product");
    }
}

function getImagesOf($commentID) {
    $database = new Database();
    $dbConn = $database->getConnection();
    $cmd = 'SELECT * FROM '.IMAGE_TABLE.' WHERE CommentID = :commentID';
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':commentID',$commentID);
    $sql->execute();

    $images = $sql->fetchAll(PDO::FETCH_ASSOC);
    $imageList = array();
    foreach($images as $image) {
        array_push($imageList,$image['Path']);
    }			
    return $imageList;
}
?>