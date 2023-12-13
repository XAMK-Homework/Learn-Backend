<?php
// /bb/api/userapi.php - api entry for user related REST operations

include("api_include.php");

/**** BELOW ARE DECLARED THE ACTUAL FUNCTIONS STHAT IMPLEMENT VARIOUS OPERATIONS ON DATA   *****/

// this function returns data of individual user in JSON format (string)
function getUserDataJSON($userID)
{
	global $database;
	$userQuery = "SELECT * FROM `user` WHERE id = '".$userID."' LIMIT 1; ";
	$usrData = $database->query($userQuery);
	$count = $usrData->numRows();
	if(!$count){ // check if a result was found
	  http_response_code(404);
      echo json_encode(['error' => 'user does not exist']);
      exit();
	}else{
		$results = $usrData->fetchAll();
		return json_encode($results);
	}
}



// this function returns data of all users in JSON format (string)
function getAllUsersJSON()
{
	global $database;

	$allUsersQuery = "SELECT * FROM `user` WHERE 1; ";

	$usrData = $database->query($allUsersQuery);
	$count = $usrData->numRows();
	if(!$count){ // check if a result was found
	  http_response_code(404);
      echo json_encode(['error' => 'user does not exist']);
      exit();
	}else{
		$results = $usrData->fetchAll();
		return json_encode($results);
	}

}

// this function updates data of a user, defined by argument $userJSON in JSON format (string)
function updateUserData( $userJSON, $id)
{
	global $database;
	$requestBody = json_decode($userJSON, true);

	$username = $requestBody[0]['username'];
	$password = $requestBody[0]['password'];
	$firstname = $requestBody[0]['firstname'];
	$lastname = $requestBody[0]['lastname'];

	$queryStr = "UPDATE `user` SET username='".$username."',"
    ." password='".$password."',"
    ." firstname='".$firstname."',"
    ." lastname='".$lastname."'"
    ." WHERE id = '".$id."';";
    
	$usrData = $database->query($queryStr); // execute query and store results 

    $count = $usrData->affectedRows();

    if($count){ // check if a result was found
    	return true;
	}else{
		return false;
	}

}

// this function creates new user, defined by argument $userJSON in JSON format (string)
function createUser( $userJSON)
{
	global $database;
	$requestBody = json_decode($userJSON, true);
	/*[{"id":1,"username":"anton","password":"anton1","firstname":"Anton","lastname":"Yrj\u00f6nen","created":"2023-10-04 13:06:26","lastseen":null,"banned":0,"isadmin":0}]*/

	$username = $requestBody[0]['username'];
	$password = $requestBody[0]['password'];
	$firstname = $requestBody[0]['firstname'];
	$lastname = $requestBody[0]['lastname'];

	$queryStr = "INSERT INTO `user` (username, password, firstname, lastname) VALUES ('".$username."', '"
	.$password ."', '" .$firstname. "', ' " . $lastname. " ');" ;

	$database->query($queryStr); // execute query

	$id = $database->lastInsertID();
	if($id){
		return $id;
	}
	else{
		return false;
	}

}

// this function removes user (implemented as changing user banned for now)
function deleteUser($userID)
{
	global $database, $isAdmin;

	if(!$isAdmin) return false;

	$queryStr = "UPDATE `user` SET banned = TRUE WHERE id = '".$userID."';";
    
	$usrData = $database->query($queryStr); // execute query and store results 
    $count = $usrData->affectedRows();
    if($count){ // check if a result was found
    	return true;
	}else{
		return false;
	}
}


/**** BELOW A SWITCH-CASE STRUCTURE TO HANDLE HTTP ACTIONS AND TO TRIGGER CORRECT WORKER FUNCTIONS *****/

switch ($method | $uri) {
    // get all users
   case ($method == 'GET' && $uri == '/bb/api/users'):
       header('Content-Type: application/json');
       //echo json_encode($users, JSON_PRETTY_PRINT);
       echo getAllUsersJSON();
       break;
    // get single user by id
   case ($method == 'GET' && preg_match('/\/api\/users\/[1-9][0-9]*/', $uri)):
       header('Content-Type: application/json');
       $id = basename($uri); // basename — Returns trailing name component of path
       //echo json_encode(['note' => 'match for user id '.$id]);
       // fetch user information from database and send
       echo getUserDataJSON($id);
       break;
    // add new user
   case ($method == 'POST' && $uri == '/bb/api/users'):
       header('Content-Type: application/json');
   
       /*if (empty($id)) {
           http_response_code(404);
           echo json_encode(['error' => 'Please add id of the user']);
       }*/

       // add new user to database...
       $success = createUser(file_get_contents('php://input'));

       if($success) echo json_encode(['id' => $success]);
       else{
       	 echo json_encode(['error' => 'user addition failed']);
       }
       break;
    // update a user - TODO:
   case ($method == 'PUT' && preg_match('/\/api\/users\/[1-9][0-9]*/', $uri)):
   		$id = basename($uri);
   		// do the update...
   		header('Content-Type: application/json');
   		$success = updateUserData(file_get_contents('php://input'),$id);
   		if($success) echo json_encode(['message' => $success]);
       else{
       	 echo json_encode(['error' => 'user update failed']);
       }
   		break;
   case ($method == 'DELETE' && preg_match('/\/api\/users\/[1-9][0-9]*/', $uri)):
   		$id = basename($uri);
   		header('Content-Type: application/json');
   		$success = deleteUser($id);
   		if($success) echo json_encode(['message' => $success]);
       	else{
       	 	echo json_encode(['error' => 'user removal failed']);
       	}
   		break;
   default:
       http_response_code(404);
       echo json_encode(['error' => "We cannot find what you're looking for2."]);
       break;


}

?>