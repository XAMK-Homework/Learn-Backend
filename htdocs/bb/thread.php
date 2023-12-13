<?php
include("include.php"); 

$threadID = 0;

    if ($_SERVER["REQUEST_METHOD"] == "GET") { // check that GET data was submitted

        $threadID = $_GET['id'];

        if (empty($threadID)) { // check that necessary values were submitted
            header("Location: /bb/main.php"); // redirect to main page in case of error
            exit;
        }
    } else {

        header("Location: /bb/main.php"); // redirect to main page in case of error
        exit;
    }
    include 'msgform.php';

    $usrQueryStr = "SELECT id FROM user WHERE username = '" . $_SESSION["username"] . "' LIMIT 1;";
    $usrData = $database->query($usrQueryStr); // execute query and store results 

    $count = $usrData->numRows();
    if ($count) { // check if a result was found
      $results = $usrData->fetchAll();
      $userID = $results[0]['id'];
    } else {
      $userID = NULL; // should not happen
    }

    // Fetch thread data
    // $threads = $database->query("SELECT * FROM thread WHERE id = ? AND hidden = false", $threadID)->fetchArray();
    $threads = $database->query("SELECT thread.*, user.username, user.id as author
                                FROM thread 
                                JOIN user ON thread.author = user.id
                                WHERE thread.id = ? AND hidden = false", $threadID)->fetchArray();
    if ($threads) {
        // Fetch message data, including user information
        $messages = $database->query("SELECT msg.*, user.username, user.id AS userid, replied_user.username AS replied_username
                                    FROM msg
                                    JOIN user ON msg.author = user.id
                                    LEFT JOIN msg AS replied_msg ON msg.replyto = replied_msg.id
                                    LEFT JOIN user AS replied_user ON replied_msg.author = replied_user.id
                                    WHERE msg.thread = ? AND msg.hidden = 0
                                    ORDER BY msg.created ASC", $threadID)->fetchAll();
    } else {
        echo "Thread not found or is hidden!";
        exit;
    }

    $tUsername = htmlspecialchars($threads['username']);
    $tDate = DateTime::createFromFormat('Y-m-d H:i:s', $threads['created']);
    $tFormattedDate = $tDate->format('M d, Y - H:i:s');

    // Check if the thread was made by the session user - check
    // Check if there's no messages under this thread from any other user than the session user
    // If both true, allow and show a delete button to delete the thread
    $deleteThreadStr = '';
    $onlySessionUserMessages = true;

    // Check if there are messages by other users than the session user
    foreach ($messages as $message) {
        if ($message['userid'] != $userID) {
            $onlySessionUserMessages = false;
            break; // No need to check further, one message from another user is enough
        }
    }
    
    // hiding the delete button from non session users (only visual as there's a proper user check in another script)
    if($threads['author'] == $userID && $onlySessionUserMessages || $isAdmin){
         $deleteThreadStr .= '<form style="margin-left: 10px;" action="/bb/delthread.php" method="POST">
                                <input type="hidden" name="thread_id" value="' . $threadID . '">
                                <input type="submit" value="Delete">
                              </form>';
    }

    $threadStr = "<div>
                    <span style='display: flex; align-items: center;'>
                        <h1>" . htmlspecialchars($threads['title']) . "</h1>
                        $deleteThreadStr
                    </span>
                    <p style='margin-top: 0;'>
                        Created by: <strong>$tUsername</strong> at $tFormattedDate
                    </p>
                </div><br>";

    $threadStr .= "<h2>Messages:</h2>";

    if ($messages) {
        foreach ($messages as $message) {

            $msgUsername = htmlspecialchars($message['username']);
            $msgTitle = htmlspecialchars($message['title']);
            $msgCreat = htmlspecialchars($message['created']);
            $msgContent = htmlspecialchars($message['content']);
            $msgReplyTo = '';
            // So that it wouldn't show errors if there's no replied_username field for messages without replied to
            if ($message['replied_username']){
                $msgReplyTo = htmlspecialchars($message['replied_username']);
            }

            $deleteMsgStr = "";
            $editMsgStr = "";
            if ($message['userid'] == $userID) {
                // generate "Edit"-button/form
                $editMsgStr = '<form style="margin-left: 10px;" action="/bb/edit.php" method="POST">
                                  <input type="hidden" name="msg" value="' . $message['id'] . '">
                                  <input type="hidden" name="thread_id" value="' . $threadID . '">
                                  <input type="submit" value="Edit">
                                 </form>';


                // generate "Delete"-button/form
                $deleteMsgStr = '<form style="margin-left: 10px;" action="/bb/delmsg.php" method="POST">
                                  <input type="hidden" name="msg" value="' . $message['id'] . '">
                                  <input type="hidden" name="thread_id" value="' . $threadID . '">
                                  <input type="submit" value="Delete">
                                 </form>';

                                
            }

            // Parse and format the date
            $mDate = DateTime::createFromFormat('Y-m-d H:i:s', $msgCreat);
            $mFormattedDate = $mDate->format('M d, Y - H:i:s');
            //  (2023.10.10 21:10:30) This is a title of this message. 
            //  Smoky: Hello World! Bla bla bla.
            //  To: Fireman
            $threadStr .= "
                        <div> 
                            <span style='display: flex; align-items: center;'>
                                (<em>$mFormattedDate</em>) 
                                <strong style='margin-left: 5px'>$msgTitle</strong>
                                $editMsgStr
                                $deleteMsgStr
                            </span>
                        <p style='margin-top: 5px;'><strong>$msgUsername:</strong> $msgContent";
        
        if ($msgReplyTo) {
            $threadStr .= "<br> To: $msgReplyTo";
        }

        $threadStr .= "</p></div><br>";
        }
    } else {
        $threadStr .= "<p>Empty thread</p>";
    }

    $form = new msgform();
    $formHTMLstr = $form->getMsgForm("/bb/newmsg.php", $threadID);
    ?>

    <!DOCTYPE html>
    <html>

    <head>
        <title>
            <?php echo htmlspecialchars($threads['title']); ?>
        </title>
    </head>

    <body>
        <div>
            <?php 
            echo printMenu();
            echo $threadStr; ?>
        </div>
        <div>
            <h3>Reply:</h3>
            <?php echo $formHTMLstr; ?>
        </div>

    </body>

    </html>

    <?php

?>