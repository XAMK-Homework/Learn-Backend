<?php

include("include.php"); 

$uname = $_SESSION["username"];

if ($_SERVER["REQUEST_METHOD"] == "POST") { // check that POST data was submitted
  
  $msgID = $_POST['msgid'];
  $newTitle = $_POST['title'];
  $newContent = $_POST['msg'];
  $threadId = $_POST['thread_id'];
  
  if (empty($msgID) ) { // check that necessary values were submitted
    header("Location: main.php"); // redirect to main page in case of error   
	}

}else{
  header("Location: main.php"); // redirect to main page in case of error
}

$usrData = $database->query("UPDATE msg 
                              SET title = ?, content = ?, modified = ?
                              WHERE id = ? LIMIT 1;", $newTitle, $newContent, "CURRENT_TIMESTAMP()", $msgID);
    
if($usrData){ // check if a result was found

  // reaction?
}

header("Location: /bb/thread.php?id=" . urlencode($threadId));


?>