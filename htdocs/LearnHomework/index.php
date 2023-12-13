<?php
$jsonFileUsers = 'users.json';
$jsonFileCourses = 'courses.json';
$dataUsers = file_get_contents($jsonFileUsers);
$dataCourses = file_get_contents($jsonFileCourses);
$users = json_decode($dataUsers, true);
$courses = json_decode($dataCourses, true);
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$apiPrefix = '/LearnHomework/index.php/api';
$apiUsers = '/users';
$apiCourses = '/courses';

$uriStartsWithApiPrefix = strpos($uri, $apiPrefix) === 0;

switch ($method) {
  case ('GET'):
    // Check if uri starts with the prefix
    if($uriStartsWithApiPrefix){ 
      // check if its users uri
      if (strpos($uri, $apiPrefix . $apiUsers) === 0) {
        // check if we're getting specific user
        if(preg_match('#'.$apiPrefix . $apiUsers.'/[1-9]+#', $uri)){
          header('Content-Type: application/json');
          $id = basename($uri);
          if (!array_key_exists($id, $users)) {
            http_response_code(404);
            echo json_encode(['error' => 'user does not exist']);
            break;
          }
          $responseData = [$id => $users[$id]];
          echo json_encode($responseData, JSON_PRETTY_PRINT);
          break;
        }
        // if it wasn't specific user, get the full user list
        header('Content-Type: application/json');
        echo json_encode($users, JSON_PRETTY_PRINT);
        break;
      } 
      // Check if its courses uri
      elseif (strpos($uri, $apiPrefix . $apiCourses) === 0) {
          // check if we're getting specific course
        if(preg_match('#'.$apiPrefix . $apiCourses.'/[1-9]+#', $uri)){
          header('Content-Type: application/json');
          $id = basename($uri);
          if (!array_key_exists($id, $courses)) {
            http_response_code(404);
            echo json_encode(['error' => 'course does not exist']);
            break;
          }
          $responseData = [$id => $courses[$id]];
          echo json_encode($responseData, JSON_PRETTY_PRINT);
          break;
        }
        // if it wasn't specific user, get the full user list
        header('Content-Type: application/json');
        echo json_encode($courses, JSON_PRETTY_PRINT);
        break;
      } 
    }
    break;

  case ('POST'):
    // Check if uri starts with the prefix
    if($uriStartsWithApiPrefix){
      // check if its users uri
      if(strpos($uri, $apiPrefix . $apiUsers) === 0){
        header('Content-Type: application/json');
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $name = $requestBody['name'];
        if (!isset($name)) {
          http_response_code(404);
          echo json_encode(['error' => 'Please add name of the user']);
        }
        $users[] = $name;
        $dataUsers = json_encode($users, JSON_PRETTY_PRINT);
        file_put_contents($jsonFileUsers, $dataUsers);
        echo json_encode(['message' => 'user added successfully']);
        break;
      }
      // check if its courses uri
      elseif(strpos($uri, $apiPrefix . $apiCourses) === 0){
        header('Content-Type: application/json');
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $course = $requestBody['course'];
        if (!isset($course)) {
          http_response_code(404);
          echo json_encode(['error' => 'Please add name of the course']);
        }
        $courses[] = $course;
        $dataCourses = json_encode($courses, JSON_PRETTY_PRINT);
        file_put_contents($jsonFileCourses, $dataCourses);
        echo json_encode(['message' => 'course added successfully']);
        break;
      }
    }
    break;

  case ('DELETE'):
    if(preg_match('#'.$apiPrefix . $apiUsers.'/[1-9]+#', $uri)){
      header('Content-Type: application/json');
      // get the id
      $id = basename($uri);
      if (!isset($users[$id])) {
        http_response_code(404);
        echo json_encode(['error' => 'user does not exist']);
        break;
      }
      unset($users[$id]);
      $data = json_encode($users, JSON_PRETTY_PRINT);
      file_put_contents($jsonFileUsers, $data);
      echo json_encode(['message' => 'user deleted successfully']);
      break;
    }
    elseif (preg_match('#'.$apiPrefix . $apiCourses.'/[1-9]+#', $uri)) {
      header('Content-Type: application/json');
      // get the id
      $id = basename($uri);
      if (!isset($courses[$id])) {
        http_response_code(404);
        echo json_encode(['error' => 'course does not exist']);
        break;
      }
      unset($courses[$id]);
      $data = json_encode($courses, JSON_PRETTY_PRINT);
      file_put_contents($jsonFileCourses, $data);
      echo json_encode(['message' => 'course deleted successfully']);
      break;
    }
    
  default:
    http_response_code(404);
    echo json_encode(['error' => "We cannot find what you're looking for."]);
    break;
}
?>