<?php

include("include.php");

$threadID = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") { // check that POST data was submitted
        
        $threadID = $_POST['thread_id'];
        
        if (empty($threadID)) { // check that necessary values were submitted
        header("Location: main.php"); // redirect to main page in case of error
        exit;

        } else {
            $usrQueryStr = "SELECT id FROM user WHERE username = ? LIMIT 1;";
            $usrData = $database->query($usrQueryStr, $_SESSION["username"]); // execute query and store results 

            if($usrData->numRows()){ // check if a result was found
                $results = $usrData->fetchAll();
                $userID = $results[0]['id'];
            }else{
                header("Location: /bb/main.php");
                exit;
            }

            // Retrieve all messages of the thread
            $messageQueryStr = "SELECT author FROM msg WHERE thread = ?";
            $messages = $database->query($messageQueryStr, $threadID)->fetchAll();
            
            // Check if all messages are by the current user
            $onlySessionUserMessages = true;
            foreach ($messages as $message) {
                if ($message['author'] != $userID) {
                    $onlySessionUserMessages = false;
                    break;
                }
            }
            
            if ($isAdmin) {
                // Admin can delete any thread
                $delQueryStr = "UPDATE `thread` SET hidden = 1 WHERE id = ?;";
                $delData = $database->query($delQueryStr, $threadID);
            } else if ($onlySessionUserMessages){
                // Regular user can delete only their thread without other users' messages
                $delQueryStr = "UPDATE `thread` SET hidden = 1 WHERE id = ? AND author = ?;";
                $delData = $database->query($delQueryStr, $threadID, $userID);
            }
            else{
                $_SESSION['error_message'] = 'You do not have permission to delete this thread.';
                header("Location: main.php");
                exit;
            }

            $count = $delData->numRows();
                
                if($count > 0){ // check if a result was found, meaning the thread was successfully hidden
                    $_SESSION['success_message'] = 'Thread successfully deleted.';
                } else {
                    // If no rows were affected, then the thread wasn't deleted - perhaps it didn't exist or the user was not authorized
                    $_SESSION['error_message'] = 'Failed to delete the thread. It may have already been deleted or you do not have permissions.';
                }
            header("Location: main.php");
            exit;
        }
    }else{
        header("Location: main.php"); // redirect to main page in case of error
        exit;
    }
?>