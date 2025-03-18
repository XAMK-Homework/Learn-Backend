<?php
header("Content-Type: application/json");
require_once "db.php";

$method = $_SERVER['REQUEST_METHOD'];

$basePath = "/LearnHomework/Project/";
$fullPath = str_replace($basePath, "", $_SERVER['REQUEST_URI']);
$parts = explode("?", $fullPath); // Split path and query parameters
$uri = explode("/", trim($parts[0], "/"));

$endpoint = $uri[0] ?? ""; // First part of the URL

switch ($endpoint) {
    case "users":
        if ($method == "GET") {
            if (isset($uri[1])) {
                getUser($uri[1]); // Get user by ID
            } else {
                getUsers(); // Get all users
            }
        } elseif ($method == "POST") {
            createUser();
        }
        break;

    case "rooms":
        if ($method == "GET") {
            if (isset($uri[1])) {
                getRoom($uri[1]); // Get room details
            } else {
                $status = $_GET['status'] ?? null; // Get "status" from query parameters, if provided
                getRooms($status); // Fetch rooms with optional status filter
            }
        } elseif ($method == "POST") {
            createRoom();
        }
        break;

    case "rounds":
        if ($method == "GET" && isset($uri[1])) {
            getRound($uri[1]); // Get round details
        } elseif ($method == "POST") {
            createRound();
        }
        break;

    case "messages":
        if ($method == "GET" && isset($uri[1])) {
            getChatMessages($uri[1]); // Get messages for a specific room
        } else if ($method == "POST"){
            sendMessage();
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Invalid endpoint"]);
}

function getUsers() {
    global $conn;
    $result = $conn->query("SELECT * FROM Users");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function getUser($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
}

function createUser() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("INSERT INTO Users (username, wins, losses) VALUES (?, 0, 0)");
    $stmt->bind_param("s", $data['username']);
    $stmt->execute();
    echo json_encode(["message" => "User created"]);
}

function getRooms($status = null) {
    global $conn;
    
    if ($status) {
        $stmt = $conn->prepare("SELECT * FROM Rooms WHERE status = ?");
        $stmt->bind_param("s", $status);
    } else {
        $stmt = $conn->prepare("SELECT * FROM Rooms");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}


function getRoom($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
}

function createRoom() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("INSERT INTO Rooms (host_id, status, current_round) VALUES (?, 'waiting', 1)");
    $stmt->bind_param("i", $data['host_id']);
    $stmt->execute();
    echo json_encode(["message" => "Room created"]);
}

function getRound($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Rounds WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
}

function createRound() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("INSERT INTO Rounds (room_id, word, clue_giver_id, winner_id) VALUES (?, ?, ?, NULL)");
    $stmt->bind_param("isi", $data['room_id'], $data['word'], $data['clue_giver_id']);
    $stmt->execute();
    echo json_encode(["message" => "Round created"]);
}

function getChatMessages($room_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT Users.username, ChatMessages.message, ChatMessages.type, ChatMessages.is_correct, ChatMessages.created_at 
                            FROM ChatMessages 
                            JOIN Users ON ChatMessages.sender_id = Users.id 
                            WHERE ChatMessages.room_id = ? 
                            ORDER BY ChatMessages.created_at ASC");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function sendMessage() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);
    
    $stmt = $conn->prepare("INSERT INTO ChatMessages (room_id, sender_id, message, type, is_correct) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissi", $data['room_id'], $data['sender_id'], $data['message'], $data['type'], $data['is_correct']);
    $stmt->execute();
    
    echo json_encode(["message" => "Message sent"]);
}
?>
