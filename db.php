<?php

$server="localhost";
$username="root";
$password="1234";
$database="spark";
$link = new mysqli($server, $username, $password, $database);

// Checks the connection
if($link->connect_error)
{

    // @mail($recipient, "Connection failed: ", $link->connect_error);

    die("Connection failed: " . $link->connect_error);

}

?>
