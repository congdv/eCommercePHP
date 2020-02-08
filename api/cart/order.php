<?php
// Allow Get Request only
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

# Root Path
include('../../root.php');

include(HELPER_PATH."/authenticationHelper.php");

# Require Authentication first
$user = getAuthenticationUser();

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
if($verb = 'post'){
    try{
        
    }catch(Exception $e){
        http_response_code(400);
        $error = new stdClass();
        $error->error = "Failed to get order history";
        $error->message = $e->getMessage();
        echo json_encode($error);
        return;
    }

}else{
    throw new Exception("Invalid Cart Data");
}

?>