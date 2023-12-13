<?php
// this script contains default init scripts used for every script to avoid multiple copies of boilerplate code

$database=NULL; // "global" database object initialized always and usable using the variable name $database where ever this is included

// init session and grab basic user info or redirect to login page
session_start();

if(!isset($_SESSION["username"]))
{
	// user not logged in,
	http_response_code(404);
  echo json_encode(['error' => "Not logged in"]);
}else{
	// init database connection in case user is logged in.
	include("../db.php");

    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbDatabase = 'bb';

    $database = new db($dbHost,$dbUser,$dbPass,$dbDatabase,'utf8'); // initilize database connection
}


$isAdmin = false; // global variable to denote admin user being logged in
$username = $_SESSION["username"];

$isAdminQuery = " SELECT isadmin FROM  `user` WHERE username LIKE '".$username."' AND isadmin = 1 LIMIT 1; ";
$usrData = $database->query($isAdminQuery);

$count = $usrData->numRows();
if($count){ // check if a result was found
  $isAdmin =true;
}


?>