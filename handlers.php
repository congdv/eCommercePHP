<!---
    Author: Rick Kozak
    Email: Rpkozak@conestogac.on.ca
--->
<?php

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

# GET /<tableName>.php
# returns JSON student objects list
function handleGet($tableName, $columns = '*', $join = '', $where = '')
{
    try {
        # extract the id portion of the URL
        if (isset($_SERVER['PATH_INFO'])) {
            $id = substr($_SERVER['PATH_INFO'], 1);
        }

        # were we provided with an  id?
        if (isset($id) && is_numeric($id)) {
            $cmd = "SELECT $columns FROM $tableName " . 
                   $join .
                   ' WHERE ID = :id';
            if (strlen($where) > 0)
                $cmd = $cmd . ' AND ' . $where;
            $sql = $GLOBALS['db']->prepare($cmd);
            $sql->bindValue(':id', $id);
        } else {
            $cmd = "SELECT $columns FROM $tableName " . $join;
            if (strlen($where) > 0)
                $cmd = $cmd . ' WHERE ' . $where;
            $sql = $GLOBALS['db']->prepare($cmd);
        }
        $sql->execute();
    } catch (Exception $e) {
    }

    echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
}

# DELETE /<tableName>.php/5
# last part of URL to be ID of record to delete
# no body
# returns JSON format boolean. True if successful, false if not
function handleDelete($tableName)
{
    $isSuccess = false;

    try {
        # extract the id portion of the URL
        $id = substr($_SERVER['PATH_INFO'], 1);

        # validate the received data
        if (is_numeric($id)) {
            # delete the record
            $cmd = "DELETE FROM $tableName " .
                'WHERE ID = :id';
            $sql = $GLOBALS['db']->prepare($cmd);
            $sql->bindValue(':id', $id);
            # execute returns true if the update worked, so we don't actually have to test
            # to see if the record exists before attempting a delete
            $isSuccess = $sql->execute();
        }
    } catch (Exception $e) {
    }

    $resp = new stdClass();
    $resp->success = $isSuccess;

    echo json_encode($resp);
}

# POST /<tableName>.php
# body to contain a valid JSON student object
# returns JSON format ID of new record, or zero if fail
function handlePost($isValid, $save)
{
    $newId = 0;

    try {
        $post = trim(file_get_contents("php://input"));
        $data = json_decode($post, true);

        if ($isValid($data)) {
            $save($data);
            $newId = $GLOBALS['db']->lastInsertId();
        }
    } catch (Exception $e) {
    }

    $resp = new stdClass();
    $resp->id = $newId;

    echo json_encode($resp);
}

# PUT /<tableName>.php/5
# last part of URL to be ID of record to update
# body to contain valid JSON student object
# returns JSON format boolean. True if successful, false if not
function handlePut($isValid, $update)
{
    $isSuccess = false;

    try {
        # extract the id portion of the URL
        $id = substr($_SERVER['PATH_INFO'], 1);

        # get the JSON format body and decode it
        $put = trim(file_get_contents("php://input"));
        $data = json_decode($put, true);

        # validate the received data
        if ($isValid($data, $id)) {
            $isSuccess = $update($data, $id);
        }
    } catch (Exception $e) {
    }

    $resp = new stdClass();
    $resp->success = $isSuccess;

    echo json_encode($resp);
}
