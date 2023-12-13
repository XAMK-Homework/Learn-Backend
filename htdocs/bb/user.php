<?php
// user.php
// maintain user information; list current and modify personal information
// use form 

include("include.php"); // <--- IMPORTANT!!! this file contains basic setup for our app's global features used on every page

$loggedInUsername = $_SESSION["username"];
$userQuery = "SELECT id FROM user WHERE username = '" . $loggedInUsername . "' LIMIT 1;";
$loggedInUserData = $database->query($userQuery);
$loggedInUser = $loggedInUserData->fetchArray();
$loggedInUserID = $loggedInUser['id'];

if ($isAdmin && isset($_SESSION['editUserID']) && empty($_POST['userid'])) {
    $editUserID = $_SESSION['editUserID'];
}
else if ($isAdmin && isset($_POST['userid'])) {
    $editUserID = $_POST['userid'];
    $_SESSION['editUserID'] = $editUserID;  // Store the user ID being edited in the session
} else {
    $editUserID = $loggedInUserID;
    unset($_SESSION['editUserID']);  // Clear any previous editUserID in session
}

$isSelfEdit = $editUserID == $loggedInUserID;
$pageTitle = $isSelfEdit ? "Omat tiedot" : "Muokkaa Käyttäjää";
$headerTitle = $isSelfEdit ? "Päivitä tietosi" : "Päivitä Käyttäjän Tiedot";

$queryStr = "SELECT * FROM user WHERE id = '" . $editUserID . "' LIMIT 1;";
$userData = $database->query($queryStr);

if($userData->numRows()){ // check if a result was found
    $results = $userData->fetchAll();
    foreach($results as $singleRes){
        $uname = $singleRes['username'];
        $fname = $singleRes['firstname'];
        $lname = $singleRes['lastname'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle; ?></title>
</head>
<body>
	<?php printMenu(); ?>
    <h1><?php echo $headerTitle; ?></h1>
    <form action="upduser.php" method="post">
        <?php if ($isAdmin && $editUserID != $loggedInUserID): ?>
            <input type="hidden" name="userid" value="<?php echo $editUserID; ?>">
        <?php endif; ?>
        <label for="username">Name:</label>
        <input type="text" id="username" name="username" required value="<?php echo $uname; ?>"><br><br>

        <label for="password1">Password:</label>
        <input type="password" id="password1" name="password1" required value=""><br><br>

        <label for="password2">Password again:</label>
        <input type="password" id="password2" name="password2" required value="" ><br><br>

        <label for="firstname">First name:</label>
        <input type="text" id="firstname" name="firstname" value="<?php echo $fname; ?>" ><br><br>

        <label for="lastname">Last name:</label>
        <input type="text" id="lastname" name="lastname" value="<?php echo $lname; ?>" ><br><br>

        <input type="submit" value="Submit">
    </form>

</body>
</html>