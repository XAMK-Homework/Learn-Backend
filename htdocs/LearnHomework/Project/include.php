<?php
// This script contains default init scripts used for every script to avoid multiple copies of boilerplate code
// init session and grab basic user info or redirect to login page
session_start();

// set cache-control header to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);

// set error reporting on for all messages
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Allow unauthenticated access only to index.php and login.php
$publicPages = [
    '/LearnHomework/Project/index.php',
    '/LearnHomework/Project/login.php'
];

$currentPath = $_SERVER['PHP_SELF'];

if (!in_array($currentPath, $publicPages)) {
    if (!isset($_SESSION["user_id"])) {
        header("Location: /LearnHomework/Project/index.php");
        exit;
    }
}

// If logged in, include the database connection script
include("db.php");

// Database connection details (can be customized as needed)
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbDatabase = 'guesstheword';

// Initialize the database connection
$database = new db($dbHost, $dbUser, $dbPass, $dbDatabase, 'utf8');

$method = $_SERVER['REQUEST_METHOD'];
$basePath = "/LearnHomework/Project/";
/*$requestUri = str_replace($basePath, "", $_SERVER['REQUEST_URI']);
$pathParts = explode("?", $requestUri);
$uri = explode("/", trim($pathParts[0], "/"));

$endpoint = $uri[0] ?? "";*/

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path if it exists
if (strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}

$uri = explode("/", trim($requestPath, "/"));
$endpoint = $uri[0] ?? "";

// This function can be used to print a navigation menu on all pages
function printMenu() {
    echo '<div style="width: 350px; text-align: center" class="topnav">
    <a style="margin: 0 15px 0 15px" class="active" href="index.php">Login</a>
    <a style="margin: 0 15px 0 15px" href="main.php">Main</a>
    </div>';
}

function isAdmin() {
    return isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1;
}
$username = $_SESSION['username'];
?>
