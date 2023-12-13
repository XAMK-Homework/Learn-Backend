<?php //rolechange.php
include("include.php"); // <--- IMPORTANT!!! this file contains basic setup for our app's global features used on every page

if(!$isAdmin){ // Leave if logged user is not an admin
	header("Location: /bb/main.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { // check that GET data was submitted
    
    $userID = $_POST['userid'];
    $roleAction = $_POST['adminrole'];
    
    if (empty($userID) || empty($roleAction)) { // check that necessary values were submitted
        header("Location: /bb/main.php"); 
        exit;
    }

    $adminStatus = ($roleAction == "Upgrade") ? 1 : 0;

    if ($roleAction == "Downgrade") {
        $adminCountQuery = "SELECT COUNT(*) as admin_count FROM `user` WHERE isadmin = 1";
        $result = $database->query($adminCountQuery)->fetchArray();
        if ($result['admin_count'] <= 1) {
            // Redirect or show an error message, as this is the last admin
            header("Location: /bb/userlist.php");
            exit;
        }
    }

    $queryStr = "UPDATE `user` SET isadmin=? WHERE id=?";
    $usrData = $database->query($queryStr, $adminStatus, $userID);

    if ($database->affectedRows() == 0) {
        // Handle case where no rows are affected (e.g., user ID not found)
        // You can add some error handling here if needed
    }

    header("Location: /bb/userlist.php");
    exit;
}else{
    header("Location: /bb/main.php"); // redirect to main page in case of error
    exit;
}
?>
