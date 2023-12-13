<?php
// usercreate.php - get post data from form and insert new user into database, redirect to login page
include("include.php");

$username ;
$password ;
$password2;
$firstname;
$lastname;

if ($_SERVER["REQUEST_METHOD"] == "POST") { // check that POST data was submitted
  // collect value of input field
  $username = $_POST['username'];
  $password = $_POST['password'];
  $password2 = $_POST['password2'];
  $firstname = $_POST['firstname'];
  $lastname = $_POST['lastname'];
  
  if (empty($username) || empty($password) || strcmp( $password, $password2 )) { // check that necessary values were submitted
    header("Location: sign.php"); // redirect to login form in case of error
  } 
}else{
  header("Location: index.php"); // redirect to login form in case of error
}

// create database query:
$queryStr = "INSERT INTO `user` (username, password, firstname, lastname) VALUES ('".$username."', '"
	.$password ."', '" .$firstname. "', ' " . $lastname. " ');"     ;

$database->query($queryStr); // execute query

$id = $database->lastInsertID();
if($id) header("Location: index.php");
else header("Location: sign.php");
?>