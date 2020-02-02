<?php

function getTokenFromAuthorizationHeader() {
    $token = null;
    $authenticationHeader = apache_request_headers();
    if(isset($authenticationHeader['Authorization'])) {
        $token =  $authenticationHeader['Authorization'];
        $token = substr($token,7);
    }
    return $token;
}

?>