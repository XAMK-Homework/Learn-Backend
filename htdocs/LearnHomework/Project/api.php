<?php
header("Content-Type: application/json");
include("include.php");

// ROUTING: Delegate requests to endpoint-specific handlers
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
    } elseif ($method === "PUT" && isset($uri[1])) {
        updateUser($uri[1]);
    } elseif ($method === "DELETE" && isset($uri[1])) {
        deleteUser($uri[1]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
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
        if (isset($uri[1]) && $uri[1] === "join") {
            joinRoom();
        } else {
            createRoom();
        }
    } elseif ($method === "PUT" && isset($uri[1])) {
        updateRoom($uri[1]);
    } elseif ($method === "DELETE" && isset($uri[1])) {
        deleteRoom($uri[1]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
}

function handleRounds($method, $uri) {
    if ($method === "GET" && isset($uri[1])) {
        getRound($uri[1]);
    } elseif ($method === "POST") {
        createRound();
    } elseif ($method === "PUT" && isset($uri[1])) {
        updateRound($uri[1]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
}

function handleMessages($method, $uri) {
    if ($method === "GET" && isset($uri[1])) {
        getChatMessages($uri[1]);
    } elseif ($method === "POST") {
        sendMessage();
    } elseif ($method === "DELETE" && isset($uri[1])) {
        deleteMessage($uri[1]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
}

// DATABASE OPERATIONS

// USERS
function getUsers() {
    global $database;
    $result = $database->query("SELECT id, username, wins, losses FROM users");
    echo json_encode($result->fetchAll());
}

function getUser($id) {
    global $database;
    $id = intval($id);
    $user = $database->query("SELECT id, username, wins, losses FROM users WHERE id = ?", $id)->fetchArray();
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
    $database->query("INSERT INTO users (username, wins, losses) VALUES (?, 0, 0)", $username);
    echo json_encode(["message" => "User created"]);
}

function updateUser($id) {
    global $database;

    if (!isset($_SESSION['user_id']) || ($_SESSION['user_id'] != $id && !isAdmin())) {
        http_response_code(403);
        echo json_encode(["error" => "You can only update your own profile"]);
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $fields = [];
    $values = [];

    if (isset($data['username'])) {
        $fields[] = "username = ?";
        $values[] = $data['username'];
    }

    if (isset($data['password'])) {
        $fields[] = "password = ?";
        $values[] = $data['password'];
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields to update"]);
        return;
    }

    $values[] = intval($id); // Last: user ID
    $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $database->query($query, ...$values);

    echo json_encode(["message" => "User updated"]);
}

function deleteUser($id) {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden: Admins only"]);
        return;
    }
    global $database;
    $id = intval($id);
    $database->query("DELETE FROM users WHERE id = ?", $id);
    echo json_encode(["message" => "User deleted"]);
}

// ROOMS

function getRooms($status = null) {
    global $database;
    if ($status) {
        $rooms = $database->query("SELECT * FROM rooms WHERE status = ?", $status)->fetchAll();
    } else {
        $rooms = $database->query("SELECT * FROM rooms")->fetchAll();
    }
    echo json_encode($rooms);
}

function getRoom($id) {
    global $database;
    $id = intval($id);
    $room = $database->query("SELECT * FROM rooms WHERE id = ?", $id)->fetchArray();
    echo json_encode($room ?: []);
}

function createRoom() {
    global $database;
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['room_name'])) {
        http_response_code(400);
        echo json_encode(["error" => "room name is required"]);
        return;
    }

    $host_id = intval($_SESSION['user_id']);
    $room_name = trim($data['room_name']);

    $database->query(
        "INSERT INTO rooms (room_name, host_id, status, current_round) VALUES (?, ?, 'waiting', 1)",
        $room_name, $host_id
    );
    $room_id = $database->lastInsertId();
    echo json_encode([
        "message" => "Joined room", 
        "id" => $room_id,
        "room_name" => $room_name,
        "host_id" => $host_id,
        "status" => "waiting",
        "current_round" => 1
    ]);
}

function joinRoom() {
    global $database;
    $data = json_decode(file_get_contents("php://input"), true);

    // Check user login
    $user_id = intval($_SESSION['user_id']);
    $room_id = intval($data["room_id"]);

    // Check if room exists
    $room = $database->query("SELECT * FROM rooms WHERE id = ?", $room_id)->fetchArray();
    if (!$room) {
        http_response_code(404);
        echo json_encode(["error" => "Room not found"]);
        return;
    }

    // Check if user is already in the room
    $exists = $database->query(
        "SELECT * FROM roomplayers WHERE room_id = ? AND user_id = ?",
        $room_id, $user_id
    )->fetchArray();

    if ($exists) {
        http_response_code(409); // Conflict
        echo json_encode(["error" => "User already in the room"]);
        return;
    }

    // Remove user from any other rooms
    $database->query("DELETE FROM roomplayers WHERE user_id = ?", $user_id);

    // Insert user into roomplayers
    $database->query(
        "INSERT INTO roomplayers (room_id, user_id, joined_at) VALUES (?, ?, NOW())",
        $room_id, $user_id
    );

    echo json_encode(["message" => "Joined room", "room_id" => $room_id, "user_id" => $user_id]);
}

function updateRoom($id) {
    global $database;
    $user_id = $_SESSION['user_id'] ?? null;
    $room = $database->query("SELECT host_id FROM rooms WHERE id = ?", intval($id))->fetchArray();

    if (!$room) {
        http_response_code(404);
        echo json_encode(["error" => "Room not found"]);
        return;
    }

    if ($room['host_id'] != $user_id && !isAdmin()) {
        http_response_code(403);
        echo json_encode(["error" => "Only the host or an admin can update this room"]);
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $fields = [];
    $values = [];
    
    if (isset($data['room_name'])) {
        $fields[] = "room_name = ?";
        $values[] = $data['room_name'];
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields to update"]);
        return;
    }

    $values[] = intval($id); // Last: room ID
    $query = "UPDATE rooms SET " . implode(", ", $fields) . " WHERE id = ?";
    $database->query($query, ...$values);

    echo json_encode(["message" => "Room updated"]);
}

function deleteRoom($id) {
    global $database;
    $user_id = $_SESSION['user_id'];
    $id = intval($id);

    if (!$user_id) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        return;
    }

    // Get the room to check host_id
    $room = $database->query("SELECT host_id FROM rooms WHERE id = ?", $id)->fetchArray();

    if (!$room) {
        http_response_code(404);
        echo json_encode(["error" => "Room not found"]);
        return;
    }

    // Allow if user is admin OR user is host of the room
    if (!isAdmin() && $room['host_id'] != $user_id) {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden: You can only delete your own rooms"]);
        return;
    }

    // Delete the room
    $database->query("DELETE FROM rooms WHERE id = ?", $id);
    
    // Also delete related roomplayers, rounds, chat messages, etc. to keep DB clean
    $database->query("DELETE FROM roomplayers WHERE room_id = ?", $id);
    $database->query("DELETE FROM rounds WHERE room_id = ?", $id);
    $database->query("DELETE FROM chatmessages WHERE room_id = ?", $id);

    echo json_encode(["message" => "Room deleted"]);
}

// ROUNDS

function getRound($id) {
    global $database;
    $id = intval($id);
    $round = $database->query("SELECT * FROM rounds WHERE id = ?", $id)->fetchArray();
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
        "INSERT INTO rounds (room_id, word, clue_giver_id, winner_id) VALUES (?, ?, ?, NULL)",
        intval($data['room_id']),
        $data['word'],
        intval($data['clue_giver_id'])
    );
    echo json_encode(["message" => "Round created"]);
}

function updateRound($id) {
    global $database;
    $data = json_decode(file_get_contents("php://input"), true);
    
    $fields = [];
    $values = [];
    
    if (isset($data['word'])) {
        $fields[] = "word = ?";
        $values[] = $data['word'];
    }

    if (isset($data['clue_giver_id'])) {
        $fields[] = "clue_giver_id = ?";
        $values[] = $data['clue_giver_id'];
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields to update"]);
        return;
    }
    
    $values[] = intval($id); // Last: round ID
    $query = "UPDATE rounds SET " . implode(", ", $fields) . " WHERE id = ?";
    $database->query($query, ...$values);

    echo json_encode(["message" => "Room updated"]);
}

// CHAT MESSAGES

function getChatMessages($room_id) {
    global $database;
    $room_id = intval($room_id);
    $messages = $database->query("
        SELECT users.username, ChatMessages.message, ChatMessages.type, 
               ChatMessages.is_correct, ChatMessages.created_at
        FROM ChatMessages
        JOIN users ON ChatMessages.sender_id = users.id
        WHERE ChatMessages.room_id = ?
        ORDER BY ChatMessages.created_at ASC
    ", $room_id)->fetchAll();
    echo json_encode($messages);
}

function sendMessage() {
    global $database;
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['room_id'], $data['message'], $data['type'], $data['is_correct'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing message data"]);
        return;
    }

    $database->query("
        INSERT INTO ChatMessages (room_id, sender_id, message, type, is_correct)
        VALUES (?, ?, ?, ?, ?)",
        intval($data['room_id']),
        intval($_SESSION['user_id']),
        $data['message'],
        $data['type'],
        intval($data['is_correct'])
    );

    echo json_encode(["message" => "Message sent"]);
}

function deleteMessage($id) {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden: Admins only"]);
        return;
    }

    global $database;
    $id = intval($id);
    $database->query("DELETE FROM chatmessages WHERE id = ?", $id);
    echo json_encode(["message" => "Message deleted"]);
}