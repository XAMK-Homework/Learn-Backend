<?php
header("Content-Type: application/json");
include("include.php");

// ROUTING
switch ($endpoint) {
    case "users":
        handleUsers($method, $uri);
        break;
    case "rooms":
        handleRooms($method, $uri);
        break;
    case "rounds":
        handleRounds($method, $uri);
        break;
    case "messages":
        handleMessages($method, $uri);
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Invalid endpoint"]);
}

// HANDLERS
function handleUsers($method, $uri) {
    if ($method === "GET") {
        isset($uri[1]) ? getUser($uri[1]) : getUsers();
    } elseif ($method === "POST") {
        createUser();
    }
}

function handleRooms($method, $uri) {
    if ($method === "GET") {
        if (isset($uri[1])) {
            getRoom($uri[1]);
        } else {
            $status = $_GET['status'] ?? null;
            getRooms($status);
        }
    } elseif ($method === "POST") {
        createRoom();
    }
}

function handleRounds($method, $uri) {
    if ($method === "GET" && isset($uri[1])) {
        getRound($uri[1]);
    } elseif ($method === "POST") {
        createRound();
    }
}

function handleMessages($method, $uri) {
    if ($method === "GET" && isset($uri[1])) {
        getChatMessages($uri[1]);
    } elseif ($method === "POST") {
        sendMessage();
    }
}

// DATABASE OPERATIONS

function getUsers() {
    global $database;
    $result = $database->query("SELECT id, username, wins, losses FROM Users");
    echo json_encode($result->fetchAll());
}

function getUser($id) {
    global $database;
    $id = intval($id);
    $user = $database->query("SELECT id, username, wins, losses FROM Users WHERE id = ?", $id)->fetchArray();
    echo json_encode($user ?: []);
}

function createUser() {
    global $database;
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['username'])) {
        http_response_code(400);
        echo json_encode(["error" => "Username is required"]);
        return;
    }

    $username = trim($data['username']);
    $database->query("INSERT INTO Users (username, wins, losses) VALUES (?, 0, 0)", $username);
    echo json_encode(["message" => "User created"]);
}

function getRooms($status = null) {
    global $database;
    if ($status) {
        $rooms = $database->query("SELECT * FROM Rooms WHERE status = ?", $status)->fetchAll();
    } else {
        $rooms = $database->query("SELECT * FROM Rooms")->fetchAll();
    }
    echo json_encode($rooms);
}

function getRoom($id) {
    global $database;
    $id = intval($id);
    $room = $database->query("SELECT * FROM Rooms WHERE id = ?", $id)->fetchArray();
    echo json_encode($room ?: []);
}

function createRoom() {
    global $database;
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['host_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "host_id is required"]);
        return;
    }

    $host_id = intval($data['host_id']);
    $database->query("INSERT INTO Rooms (host_id, status, current_round) VALUES (?, 'waiting', 1)", $host_id);
    echo json_encode(["message" => "Room created"]);
}

function getRound($id) {
    global $database;
    $id = intval($id);
    $round = $database->query("SELECT * FROM Rounds WHERE id = ?", $id)->fetchArray();
    echo json_encode($round ?: []);
}

function createRound() {
    global $database;
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['room_id'], $data['word'], $data['clue_giver_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing data"]);
        return;
    }

    $database->query(
        "INSERT INTO Rounds (room_id, word, clue_giver_id, winner_id) VALUES (?, ?, ?, NULL)",
        intval($data['room_id']),
        $data['word'],
        intval($data['clue_giver_id'])
    );
    echo json_encode(["message" => "Round created"]);
}

function getChatMessages($room_id) {
    global $database;
    $room_id = intval($room_id);
    $messages = $database->query("
        SELECT Users.username, ChatMessages.message, ChatMessages.type, 
               ChatMessages.is_correct, ChatMessages.created_at
        FROM ChatMessages
        JOIN Users ON ChatMessages.sender_id = Users.id
        WHERE ChatMessages.room_id = ?
        ORDER BY ChatMessages.created_at ASC
    ", $room_id)->fetchAll();
    echo json_encode($messages);
}

function sendMessage() {
    global $database;
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['room_id'], $data['sender_id'], $data['message'], $data['type'], $data['is_correct'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing message data"]);
        return;
    }

    $database->query("
        INSERT INTO ChatMessages (room_id, sender_id, message, type, is_correct)
        VALUES (?, ?, ?, ?, ?)",
        intval($data['room_id']),
        intval($data['sender_id']),
        $data['message'],
        $data['type'],
        intval($data['is_correct'])
    );

    echo json_encode(["message" => "Message sent"]);
}