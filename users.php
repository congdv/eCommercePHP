<!---
    Author: Rick Kozak
    Email: Rpkozak@conestogac.on.ca
--->
<?php
include "pagestart.php";
include "handlers.php";

define('TABLE', 'users');
define('COLUMNS', 'Id, Email');

$verb = strtolower($_SERVER['REQUEST_METHOD']);

if (isLoggedIn()) {
    if ($verb == 'get') {
        handleGet(TABLE, COLUMNS);
    } else if ($verb == 'post') {
        handlePost('isValidInsert', 'insert');
    } else if ($verb == 'put') {
        handlePut('isValidUpdate', 'update');
    } else if ($verb == 'delete') {
        handleDelete(TABLE);
    }
} else {
    echo '{}';
}

# validation code for user object on insert
function isValidInsert($user)
{
    return isset($user['Email']) &&
    isset($user['Password']) &&
    filter_var($user['Email'], FILTER_VALIDATE_EMAIL) &&
    strlen($user['Password']) > 6;
}

# validation code for user object on update
function isValidUpdate($user, $id)
{
    return isValidInsert($user) && is_numeric($id) && $id > 0;
}

# DB insert for user
function insert($user)
{
    $cmd = 'INSERT INTO ' . TABLE . ' (Email, Password) ' .
        'VALUES (:email, :password)';
    $sql = $GLOBALS['db']->prepare($cmd);
    $sql->bindValue(':email', $user['Email']);
    $sql->bindValue(':password', password_hash($user['Password'], PASSWORD_BCRYPT));
    $sql->execute();
}

# DB update for user
function update($user, $id)
{
    # update the record
    $cmd = 'UPDATE ' . TABLE .
        ' SET Email = :email, Password = :password ' .
        'WHERE ID = :id';
    $sql = $GLOBALS['db']->prepare($cmd);
    $sql->bindValue(':email', $user['Email']);
    $sql->bindValue(':password', password_hash($user['Password'], PASSWORD_BCRYPT));
    $sql->bindValue(':id', $id);
    # execute returns true if the update worked, so we don't actually have to test
    # to see if the record exists before attempting an update.
    return $sql->execute();
}
