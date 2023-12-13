<?php
include("include.php"); 

$msgid =NULL;
if ($_SERVER["REQUEST_METHOD"] == "POST") { // check that POST data was submitted
        
  $msgid = $_POST['msg'];
  $thread_id = $_POST['thread_id'];
 
  if ( empty($msgid) ) { // check that necessary values were submitted
    header("Location: main.php"); // redirect to main page in case of error
  }
}else{
  header("Location: main.php"); // redirect to main page in case of error
}

include("msgform.php");
$form = new msgform();
$formHTMLstr = $form->getMsgForm("editmsg.php", $thread_id, $msgid);


?>
<!DOCTYPE html>
<html>
<head>
    <title>Keskustelut</title>
</head>
<body> <?php printMenu(); ?>
    <h1>Edit</h1>
    
    <?php 

    echo $formHTMLstr;

?>


</body>
</html>