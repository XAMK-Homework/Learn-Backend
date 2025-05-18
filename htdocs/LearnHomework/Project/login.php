<?php
include("include.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve username nad password from login fields
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check if there are empty login fields
    if (empty($username) || empty($password)) {
        header("Location: index.php?error=Täytä kaikki kentät");
        exit;
    }

    $result = $database->query("SELECT * FROM Users WHERE username = ?", $username)->fetchArray();

    // Check if user is found
    if (!$result) {
        header("Location: index.php?error=Käyttäjää ei löytynyt");
        exit;
    }

    // Check if password is correct
    if ($password !== $result['password']) {
        header("Location: index.php?error=Väärä salasana");
        exit;
    }

    // Check if user is suspended
    if (!empty($result['suspended']) && $result['suspended'] == 1) {
        header("Location: index.php?error=Käyttäjä on estetty");
        exit;
    }
    // Save session variables from login, and also check if the user is an admin
    $_SESSION['user_id'] = $result['id'];
    $_SESSION['username'] = $result['username'];
    $_SESSION['isadmin'] = $result['isadmin'];

    // Regenerate session id for security
    session_regenerate_id(true);
    // Head to the main page
    header("Location: main.php");
    exit;
}
// Head back to the login page
header("Location: index.php");
exit;
