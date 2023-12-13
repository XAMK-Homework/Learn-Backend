<?php
include("include.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['username'];
    $pass = $_POST['password'];

    if (empty($name) || empty($pass)) {
        header("Location: /bb/index.php");
        exit; // Ensure that the script stops executing after a redirect
    }

    /* Checking user login */
    $queryStr = "SELECT * FROM user WHERE username = ? LIMIT 1;";
    $userData = $database->query($queryStr, $name);

    if ($userData->numRows()) {
        $user = $userData->fetchArray();
        // Check if the password matches and if the user is not banned
        if ($user['password'] == $pass && !$user['banned']) {
            $_SESSION["username"] = $name;
            header("Location: /bb/main.php");
        } else {
            // Handle wrong password or banned user
            header("Location: /bb/index.php?error=invalid_credentials_or_banned");
        }
    } else {
        header("Location: /bb/index.php?error=user_not_found");
    }
} else {
    header("Location: /bb/index.php");
}
exit;
?>