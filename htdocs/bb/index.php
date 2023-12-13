<!DOCTYPE html>
<html>
<head>
    <title>Kirjaudu</title>
</head>
<body>
    <h1>Kirjaudu</h1>
    <form action="login.php" method="post">
        <label for="username">Name:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>