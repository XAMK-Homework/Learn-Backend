<?php
// ban.php - käyttäjän bannaus / kohta 6)
include("include.php");

if(!$isAdmin){ // Leave if logged user is not an admin
	header("Location: /bb/main.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { // check that POST data was submitted
	  $userID = $_POST['userid'];
      $banAction = $_POST['ban'];

	  if (empty($userID) || empty($banAction)) { // check that necessary values were submitted
	    header("Location: main.php"); // redirect to main page in case of error
		exit;

	} else {
        $banStatus = ($banAction == "Ban!") ? 1 : 0;

		$delQueryStr = "UPDATE `user` SET banned = ? WHERE id = ?;";
		$result = $database->query($delQueryStr, $banStatus, $userID);

		header("Location: /bb/userlist.php");
		exit;
	}
} else {
    header("Location: main.php"); // redirect to main page in case of error
    exit;
}
?>