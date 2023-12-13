<?php
// api_login.php
session_start(); // start PHP session to remember user login info across page loads

 // collect value of input field
$requestBody=NULL;
$JSONdata = file_get_contents('php://input');
if(isset($JSONdata)){
  $requestBody = json_decode($JSONdata, true);
}else{
    http_response_code(404);
    echo json_encode(['error' => "Malformed request, no input"]);
    exit();
  }

$name=NULL;
$pass=NULL;
if(isset($requestBody)){
  if(isset($requestBody[0])){
    $name = $requestBody[0]['username'];
    $pass = $requestBody[0]['password'];
  }else{
    http_response_code(404);
    echo json_encode(['error' => "Malformed request, no entry for login data"]);
    exit();
  }
}else{
    http_response_code(404);
    echo json_encode(['error' => "Malformed request, no data available"]);
    echo ($JSONdata);
    exit();
}  
 if (empty($name) || empty($pass)) { // check that necessary values were submitted
    http_response_code(404);
    echo json_encode(['error' => "We cannot find what you're looking for."]);
    exit();
 } 

/* OK to proceed checking usert login  */

include("../db.php");

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; 
$dbDatabase = 'bb';

$database = new db($dbHost,$dbUser,$dbPass,$dbDatabase,'utf8'); // initilize database connection

$queryStr = "SELECT * FROM user WHERE username = '" . $name . "' AND password LIKE '" . $pass . "' LIMIT 1; ";
// string above constructed: "SELECT * FROM user WHERE username = 'anton' AND password LIKE 'anton1' LIMIT 1; "

//echo $queryStr;
//exit();

$userData = $database->query($queryStr); // execute query and store results in $db object userData

if($userData->numRows()){ // check if a result was found
  // user found - SUCCESS!
  //echo "WELCOME $name";

  $_SESSION["username"] = $name; // store credentials for future page loads...
  $_SESSION["password"] = $pass;
  header('Content-Type: application/json');
  echo json_encode(['message' => 'success']);

}else{
  // failed attempt
  header('Content-Type: application/json');
  echo json_encode(['message' => 'fail']);
}


?>