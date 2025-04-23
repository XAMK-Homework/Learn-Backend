<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Kirjaudu</title>
</head>
<body>
    <h1>Kirjaudu</h1>
    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;">Virhe: <?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="login.php" method="post">
        <label for="username">Nimi:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Salasana:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Kirjaudu sisään</button>
    </form>
</body>
</html>