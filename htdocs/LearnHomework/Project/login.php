<?php
include("include.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header("Location: index.php?error=Täytä kaikki kentät");
        exit;
    }

    $result = $database->query("SELECT * FROM Users WHERE username = ?", $username)->fetchArray();

    if (!$result) {
        header("Location: index.php?error=Käyttäjää ei löytynyt");
        exit;
    }

    if ($password !== $result['password']) {
        header("Location: index.php?error=Väärä salasana");
        exit;
    }

    $_SESSION['user_id'] = $result['id'];
    $_SESSION['username'] = $result['username'];

    header("Location: /LearnHomework/Project/api.php");
    exit;
}
header("Location: /LearnHomework/Project/index.php");
exit;
