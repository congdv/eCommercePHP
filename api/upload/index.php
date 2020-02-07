<?php 
# header file
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# root path
include("../../root.php");

# authentication functions
include(HELPER_PATH."/utilsHelper.php");
include(HELPER_PATH."/authenticationHelper.php");

# assets path
define("ASSETS_PATH",'/assets');

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

$verb = strtolower($_SERVER['REQUEST_METHOD']);
if($verb == "post") {
    $targetDir = ASSETS_PATH."/images";
    $targetDir = $targetDir."/".date("Y/m/d");

    # Check directory is exists
    if(!file_exists($targetDir)) {
        mkdir($targetDir,077,true);
    }

    $targetFilePath = $targetDir."/". basename($_FILES["img"]["name"]);
    # Check file path is exists
    if(file_exists($targetFilePath)) {
        $pathFileType = strtolower(pathinfo($targetFilePath,PATHINFO_EXTENSION));
        $targetFilePath = $targetDir."/".uniqid("i").".".$pathFileType;
    }
    $success  = false;
    if(move_uploaded_file($_FILES["img"]["tmp_name"],ROOT_PATH."".$targetFilePath)) {
        $success = true;
    }

    if($success) {
        $resp = new stdClass();
        $resp->success = $success;
        $resp->path = $targetFilePath;
        http_response_code(200);
        echo json_encode($resp);
    } else {
        $resp = new stdClass();
        $resp->success = $success;
        $resp->message = "Your file was not uploaded";
        http_response_code(401);
        echo json_encode($resp);
    }
}
?>
