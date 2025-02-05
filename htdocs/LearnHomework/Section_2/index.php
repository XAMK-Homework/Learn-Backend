<?php
$jsonFileUsers = 'users.json';
$jsonFileCampaings = 'campaigns.json';
$dataUsers = file_get_contents($jsonFileUsers);
$dataCampaigns = file_get_contents($jsonFileCampaings);
$users = json_decode($dataUsers, true);
$campaigns = json_decode($dataCampaigns, true);

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method | $uri) {
    // Get Users
    case ($method == 'GET' && $uri == '/LearnHomework/Section_2/users'):
        header('Content-Type: application/json');
        echo json_encode($users, JSON_PRETTY_PRINT);
        break;
    // Get Users/5
    case ($method == 'GET' && preg_match('/\/LearnHomework\/Section_2\/users\/[1-9]/', $uri)):
        header('Content-Type: application/json');
        $id = basename($uri);
        if (!array_key_exists($id, $users)) {
            http_response_code(404);
            echo json_encode(['error' => 'user does not exist']);
            break;
        }
        echo json_encode([$id => $users[$id]], JSON_PRETTY_PRINT);
        break;
    // Post Users (adding users)
    case ($method == 'POST' && $uri == '/LearnHomework/Section_2/users'):
        header('Content-Type: application/json');
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $name = $requestBody['name'];
        if (empty($name)) {
            http_response_code(404);
            echo json_encode(['error' => 'Please add name of the user']);
            break;
        }
        $users[] = $name;
        file_put_contents($jsonFileUsers, json_encode($users, JSON_PRETTY_PRINT));
        echo json_encode(['message' => 'user added successfully']);
        break;
    // Put 1 User (adding 1 user)
    case ($method == 'PUT' && preg_match('/\/LearnHomework\/Section_2\/users\/[1-9]/', $uri)):
        header('Content-Type: application/json');
        $id = basename($uri);
        if (!array_key_exists($id, $users)) {
            http_response_code(404);
            echo json_encode(['error' => 'user does not exist']);
            break;
        }
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $name = $requestBody['name'];
        if (empty($name)) {
            http_response_code(404);
            echo json_encode(['error' => 'Please add name of the user']);
            break;
        }
        $users[$id] = $name;
        file_put_contents($jsonFileUsers, json_encode($users, JSON_PRETTY_PRINT));
        echo json_encode(['message' => 'user updated successfully']);
        break;
    // Delete 1 user
    case ($method == 'DELETE' && preg_match('/\/LearnHomework\/Section_2\/users\/[1-9]/', $uri)):
        header('Content-Type: application/json');
        $id = basename($uri);
        if (empty($users[$id])) {
            http_response_code(404);
            echo json_encode(['error' => 'user does not exist']);
            break;
        }
        unset($users[$id]);
        file_put_contents($jsonFileUsers, json_encode($users, JSON_PRETTY_PRINT));
        echo json_encode(['message' => 'user deleted successfully']);
        break;

    // ______________________________________________________________________________________________________________    
    // Get Campaigns
    case ($method == 'GET' && $uri == '/LearnHomework/Section_2/campaigns'):
        header('Content-Type: application/json');
        echo json_encode($campaigns, JSON_PRETTY_PRINT);
        break;
    // Get Campaign/5
    case ($method == 'GET' && preg_match('/\/LearnHomework\/Section_2\/campaigns\/[1-9]/', $uri)):
        header('Content-Type: application/json');
        $id = basename($uri);
        if (!array_key_exists($id, $campaigns)) {
            http_response_code(404);
            echo json_encode(['error' => 'campaign does not exist']);
            break;
        }
        echo json_encode([$id => $campaigns[$id]], JSON_PRETTY_PRINT);
        break;
    // Post Campaigns (adding campaigns)
    case ($method == 'POST' && $uri == '/LearnHomework/Section_2/campaigns'):
        header('Content-Type: application/json');
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $campaign = $requestBody['name'];
        if (empty($campaign)) {
            http_response_code(404);
            echo json_encode(['error' => 'Please add name of the campaign']);
            break;
        }
        $campaigns[] = $campaign;
        file_put_contents($jsonFileCampaings, json_encode($campaigns, JSON_PRETTY_PRINT));
        echo json_encode(['message' => 'campaign added successfully']);
        break;
    // Post 1 Campaign (adding 1 campaign)
    case ($method == 'PUT' && preg_match('/\/LearnHomework\/Section_2\/campaigns\/[1-9]/', $uri)):
        header('Content-Type: application/json');
        $id = basename($uri);
        if (!array_key_exists($id, $campaigns)) {
            http_response_code(404);
            echo json_encode(['error' => 'campaign does not exist']);
            break;
        }
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $campaign = $requestBody['name'];
        if (empty($campaign)) {
            http_response_code(404);
            echo json_encode(['error' => 'Please add name of the campaign']);
            break;
        }
        $campaigns[$id] = $campaign;
        file_put_contents($jsonFileCampaings, json_encode($campaigns, JSON_PRETTY_PRINT));
        echo json_encode(['message' => 'campaign updated successfully']);
        break;
    // Delete 1 campaign
    case ($method == 'DELETE' && preg_match('/\/LearnHomework\/Section_2\/campaigns\/[1-9]/', $uri)):
        header('Content-Type: application/json');
        $id = basename($uri);
        if (empty($campaigns[$id])) {
            http_response_code(404);
            echo json_encode(['error' => 'campaign does not exist']);
            break;
        }
        unset($campaigns[$id]);
        file_put_contents($jsonFileCampaings, json_encode($campaigns, JSON_PRETTY_PRINT));
        echo json_encode(['message' => 'campaign deleted successfully']);
        break;
    // Error with the search
    default:
        http_response_code(404);
        echo json_encode(['error' => "We cannot find what you're looking for."]);
        break;
}