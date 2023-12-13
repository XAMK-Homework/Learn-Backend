<?php //upduser.php
include("include.php"); // <--- IMPORTANT!!! this file contains basic setup for our app's global features used on every page
	
$uname = $_SESSION["username"];

if ($_SERVER["REQUEST_METHOD"] == "POST") { // check that GET data was submitted
	
	$newUsername = $_POST['username'];
	$newPass1 = $_POST['password1'];
	$newPass2 = $_POST['password2'];
	$newFname = $_POST['firstname'];
	$newLname = $_POST['lastname'];

	if (empty($newUsername) || strcmp($newPass1, $newPass2) !== 0 || strlen($newPass1) < 4) { // check that necessary values were submitted
		// Optional: send an error that the password is too short
		header("Location: /bb/user.php"); // redirect to back in case of an error
		exit;
	}

	// Fetch the logged-in user's ID
    $loggedInUsername = $_SESSION["username"];
    $userQuery = "SELECT id FROM user WHERE username = '" . $loggedInUsername . "' LIMIT 1;";
    $loggedInUserData = $database->query($userQuery);
    $loggedInUser = $loggedInUserData->fetchArray();
    $loggedInUserID = $loggedInUser['id'];

	// Determine which user to update
    $editUserID = ($isAdmin && isset($_POST['userid'])) ? $_POST['userid'] : $loggedInUserID;

    // Update query using prepared statements
    $queryStr = "UPDATE `user` SET username=?, password=?, firstname=?, lastname=? WHERE id=?";
    $database->query($queryStr, $newUsername, $newPass2, $newFname, $newLname, $editUserID);


	if ($database->affectedRows() == 0) {
        // Optional: Add error handling if no rows were affected
    }

	header("Location: main.php");
	exit;
}else{
	header("Location: main.php"); // redirect to main page in case of error
	exit;
  }
?>