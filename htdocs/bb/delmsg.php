<?php
// delmsg.php - oman viestin poisto / kohta C)
include("include.php");
$msgId=0;

if ($_SERVER["REQUEST_METHOD"] == "POST") { // check that POST data was submitted
	  $msgId = $_POST['msg'];
	  $threadId = $_POST['thread_id'];

	  if (empty($msgId)) { // check that necessary values were submitted
	    header("Location: main.php"); // redirect to main page in case of error
		exit;

	} else {
		$delQueryStr = "UPDATE `msg` SET hidden=1 WHERE id = ?;";
		$result = $database->query($delQueryStr, $msgId);

		// Redirect back to the thread after deletion
		header("Location: /bb/thread.php?id=" . urlencode($threadId));
		exit;
	}
} else {
    header("Location: main.php"); // redirect to main page in case of error
    exit;
}
?>