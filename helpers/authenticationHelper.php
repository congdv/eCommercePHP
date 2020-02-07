<?php


# Constants 
include_once(HELPER_PATH.'/constants.php');

# Library
include_once(LIB_PATH.'/JWT.php');

# Database
include_once(CONFIG_PATH.'/database.php');

# Utils Helper
include_once(HELPER_PATH.'./utilsHelper.php');

define('TABLE', 'user');

# Get the user by token
function getAuthenticationUser() {
    $token = getTokenFromAuthorizationHeader();
    if($token == NULL) {
        return NULL;
    }
    try {
        $userID = JWT::decode($token,SECRET_KEY);
        return getUserByID($userID);
    } catch (exception $e) {
        return NULL;
    }
}

# Find the user by id
function getUserByID($userID)
{
    if(!isset($userID)) 
    {
        return NULL;
    }
    $database = new Database();
    $dbConn = $database->getConnection();

    $cmd = 'SELECT * FROM '.TABLE.' WHERE ID =:id';
    $sql = $dbConn->prepare($cmd);
    $sql->bindValue(':id',$userID);
    $sql->execute();
    $user = $sql->fetch(PDO::FETCH_ASSOC);
    return $user;
}

?>
