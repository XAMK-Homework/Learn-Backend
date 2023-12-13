<?php
// sign.php
/*
F)  uuden käyttäjän luonti (syötetään tunnus, salasana ja muut henkilötiedot) Uuden käyttäjän luonnin tulee olla maailmalle avoin eikä kirjautumista voida edellyttää/tarkistaa. Tee se erillisenä sivunaan, jossa on lomake.  Kummallekin toiminnolle tarvitaan oma käsittelijänsä. 
*/
// sign.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Luo tunnus</title>
</head>
<body>
    <h1>Luo tunnus</h1>
    <form action="usercreate.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="password2">Password again:</label>
        <input type="password" id="password2" name="password2" required><br><br>

        <label for="firstname">Firstname:</label>
        <input type="text" id="firstname" name="firstname" ><br><br>

        <label for="lastname">Lastname:</label>
        <input type="text" id="lastname" name="lastname" ><br><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>


