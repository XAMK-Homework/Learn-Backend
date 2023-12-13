<?php
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Simple selector for choosing the right api entry based on uri/data user is accessing
// This is just for code separation, maintenance, simplifying code structre.
// This can also be helpful for first-level access limitation control based on user roles,
// or for inclusion of varying other pieces of code or choosing separate databases etc.
switch ($uri) {
    case ($uri == '/bb/api/users' || preg_match('/\/bb\/api\/users\/[1-9][0-9]*/', $uri)):
            include("userapi.php");
        break;
    case ($uri == '/bb/api/threads' || preg_match('/\/bb\/api\/threads\/[1-9][0-9]*/', $uri)):
            include("threadapi.php");
        break;
    case ($method == 'POST' && $uri == '/bb/api/login'):   // <-- ****** LOGIN ******
            include("api_login.php");
        break;
    default:
       http_response_code(404);
       echo json_encode(['error' => "We cannot find what you're looking for1."]);
       break;
   }

?>