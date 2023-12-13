<?php
include ("include.php");

if(!isset($_SESSION["username"]))
{
	// user not logged in, redirect to index.php
	header("Location: /bb/index.php");
}else{

include("msgform.php");


$threads = $database->query("SELECT thread.*, user.username
                             FROM thread 
                             JOIN user ON thread.author = user.id
                             WHERE hidden = false");

// Check if there's any threads
if($threads->numRows()){
    // if there's any threads, then define unordered bulleted list
    $threadStr = "<ul>";
    // print out all the threads into the list
    foreach($threads->fetchAll() as $thread){
        $tID = htmlspecialchars($thread['id']);
        $tCreat = htmlspecialchars($thread['created']);
        $tUsername = htmlspecialchars($thread['username']);
        $tTitle = htmlspecialchars($thread['title']);
        
        // Parse and format the date
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $tCreat);
        $formattedDate = $date->format('M d, Y - H:i:s');

        // - Hello world to everyone!
        //   Smoky, Oct 10, 2023 - 20:51:14
        $linkStr = "<li><p><a href='thread.php/thread/{$tID}'>{$tTitle}</a><br>";
        $threadStr .= $linkStr . "<font size='-1'>{$tUsername}, {$formattedDate}</font><p></li>";
    }
    $threadStr .= "</ul>";
}
else{
    echo "<h1>Keskusteluja ei ole</h1>";
}

$form = new msgform();
$formHTMLstr = $form->getMsgForm("newthread.php");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Keskustelut</title>
    <style>
    body {
        font-size: 16px;
    }
    p {
        font-size: 18px;
    }
</style>
</head>
<body>
    <?php printMenu(); ?>
    <h1>Keskustelut</h1>
    
    <?php 
    echo $threadStr;
    echo $formHTMLstr;

}// end of else block


?>
</body>
</html>