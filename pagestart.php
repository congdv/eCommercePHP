<!---
    Author: Rick Kozak
    Email: Rpkozak@conestogac.on.ca
--->
<?php
# this bit of code will execute immediately in every PHP script that it is added to
# it initializes the PDO interface
try {
    $GLOBALS['db'] = new PDO('mysql:dbname=attendance; host=localhost;', 'root');
    $GLOBALS['db']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
}

# start or continue this user's session
session_start();

# utility function to validate a date
# ISO-8601 format date is expected default
# in JavaScript, new Date().toISOString() will return this format
function isValidDate($date, $format = DateTimeInterface::ISO8601)
{
    # we only care about the first 19 characters (no fractions of a second or timezone)
    $jsDate = substr($date, 0, 19);
    # append back the timezone (UTC) for conversion purposes
    $d = DateTime::createFromFormat($format, $jsDate . 'Z');

    # only pay attention to the first 19 characters (up to but not including the timezone)
    return $d && substr($d->format($format), 0, 19) == $jsDate;
}

# utility function to check if this session is associated with a valid user
function isLoggedIn(){
    return true;
    //return isset($_SESSION['UserId']) && $_SESSION['UserId'] > 0;
}

# utility function to determine the ip/port of the server
function getIpPort(){
    $ipConfigOut = shell_exec("ipConfig");
    $ipConfigLines = explode("\n", $ipConfigOut);
    foreach($ipConfigLines as $line){
        if (strpos($line, 'IPv4')){
            $ipAddress = trim(substr(strstr($line, ':'), 1));
            break;
        }
    }
    return $ipAddress . ':' . $_SERVER['SERVER_PORT'];
}