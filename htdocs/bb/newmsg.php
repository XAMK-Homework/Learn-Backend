<?php
// newmsg.php

/*
Toiminto:
B) Uuden keskustelun aloitus

- vastaanottaa sivun thread.php lomakkeelta msgform tiedot post-pyyntönä.
- luo ketjuun uuden viestin.
- ohjaa navigaation ketjun sivulle 

*/

session_start();
$id=0;
if(!isset($_SESSION["username"]))
{
	// user not logged in, redirect to index.php
	header("Location: index.php");
}else{
	/* TO DO: fetch all messages of this thread and display in order neatly  */
	if ($_SERVER["REQUEST_METHOD"] == "POST") { // check that POST data was submitted
	  
	  $title = $_POST['title'];
	  $msg = $_POST['msg'];
	  $threadID = $_POST['thread_id'];
	  
	  if (empty($title) || empty($msg) || empty($threadID)) { // check that necessary values were submitted
	    header("Location: main.php"); // redirect to main page in case of error
	  }elseif((empty($title) || empty($msg)) && $threadID) {
		header("Location: /bb/thread.php?id=".$threadID); // redirect to thread page if msg/title missing
	  }
	}else{
	  header("Location: main.php"); // redirect to main page in case of error
	}

	include("db.php");

    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbDatabase = 'bb';

    $database = new db($dbHost,$dbUser,$dbPass,$dbDatabase,'utf8'); // initilize database connection

    $usrQueryStr = "SELECT id FROM user WHERE username = '" . $_SESSION["username"] . "' LIMIT 1;";
    $usrData = $database->query($usrQueryStr); // execute query and store results 

    $count = $usrData->numRows();

    if($count){ // check if a result was found
    	$results = $usrData->fetchAll();
    	$userID = $results[0]['id'];
    	$date = date('Y-m-d H:i:s');

    	// *** Create new message to the  thread:
    	$insertMsgQuery = "INSERT INTO `msg` (`thread`, `created`, `title`, `content`, `author`) VALUES
						  ('$threadID', '$date', '$title', '$msg', '$userID') ";
    	$insertResults = $database->query($insertMsgQuery); // TODO: check success and create error message?

    	header("Location: /bb/thread.php?id=$threadID"); // redirect to thread page
    }
	else{
		header("Location: main.php"); // redirect to main page in case of error (user not found)
	}

}

?>