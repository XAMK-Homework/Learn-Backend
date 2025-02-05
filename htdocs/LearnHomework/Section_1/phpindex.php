<?php
    function LoopTitle($title){
        for($i = 1; $i < 11; $i++){
            echo $title . $i . "<br>";
        }
    }
     $userInput = isset($_GET['userInput']) ? $_GET['userInput'] : "";
?>

<!DOCTYPE html>
    <html>
        <meta charset="UTF-8">
    <head>
        <title>Page Title</title>
    </head>
    <body>
        <h1>
            <?php LoopTitle("This is a title ") ?> 
        </h1>
        <p>This is a paragraph.</p>
        <p>User submitted: <?php echo $userInput;?></p>
    </body>
</html>