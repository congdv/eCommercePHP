<?php
function unknownEndpointsResponse() {
    http_response_code(403);
    $resp = new stdClass();
    $resp->error = "Invalid";
    $resp->message = "Unknown Endpoint";
    echo json_encode($resp);
}

function invalidAuthenticationResponse() {
    http_response_code("401");
    $error = new stdClass();
    $error->error = "Forbidden Request";
    $error->message = "Request has invalid authentication credentials";
    echo json_encode($error);
}

function errorResponse($error, $message) {
    http_response_code(401);
    $resp = new stdClass();
    $resp->error = $error;
    $resp->message = $message;
    echo json_encode($resp);
}

function succesResponse($success, $message, $data) {
    http_response_code(200);
    $resp = new stdClass();
    $resp->success = $success;
    $resp->message = $message;
    if($data) {
        $resp->data = $data;
    }
    echo json_encode($resp);
}
?>