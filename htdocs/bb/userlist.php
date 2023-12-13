<?php
// userlist.php - page for admin users to list and manage users; upgrading to admin, blocking access, modifying information

// modify database for storing admin level, e.g. by adding new column: 
// ALTER TABLE `user` ADD `isadmin` BOOLEAN NOT NULL DEFAULT FALSE AFTER `banned`; 

// step by step instructions:

// INITIALIZING PAGE / CHECKING ACCESS RIGHTS (only admins should be able to enter)

include("include.php");

// 0) include "include.php" to add and execute basic init perrocedures, and check login (username for session defined, see other source files)
// 1) based on the username in $_SESSION superglobal variable, do a database query to check if user is admin. Redirect using header() if not.

if(!$isAdmin){
	header("Location: main.php");
}

$outPutStr="";

// PREPARING DATA TO BE USED

// 2) for logged admins, create a SELECT query as a string that gets ALL USERS from database
// 3) execute query by using $database->query() -method (see examples from other source files)
// 4) check the succession by getting number of result rows and if necessary, redirect away upon error

$allUsersQuery = "SELECT * FROM `user` WHERE 1; ";

$usrData = $database->query($allUsersQuery);
$count = $usrData->numRows();
if(!$count){ // check if a result was found
  $outPutStr="<h1>Ei käyttäjiä!</h1>";
}else{

// DISPLAYING DATA AND ADDING USER MANAGEMENT FUNCTIONALITY

// 5) iterate through all result rows and generate e.g. a HTML table <tr><td>content</td><td>contentalso</td></tr> from the result rows
//    use numRows(), fetchAll() and foreach-loop, see other source files for example

	$results = $usrData->fetchAll();
	$outPutStr .= '<table>';
	$outPutStr .= "<tr>";
	$outPutStr .= "<th class='bordered-cell username'>"."Username"."</th>";
	$outPutStr .= "<th class='bordered-cell name'>"."Name"."</th>";
	$outPutStr .= "<th class='bordered-cell lastname'>"."Lastname"."</th>";
	$outPutStr .= "<th class='bordered-cell action'>"."Admin Status"."</th>";
	$outPutStr .= "<th class='bordered-cell action'>"."Ban?"."</th>";
	$outPutStr .= "<th class='bordered-cell action'>"."Edit User"."</th>";
	$outPutStr .= "</tr>";
	
	$rowCount = 0;
	foreach($results as $singleRes){
		$userName = $singleRes['username'];
		$userID = $singleRes['id'];
		$firstName = $singleRes['firstname'];
		$lastName = $singleRes['lastname'];
		$banned = $singleRes['banned'];
		$isUserAdmin = $singleRes['isadmin'];
		
		$rowClass = $rowCount % 2 == 0 ? 'white-row' : 'grey-row';
    	$rowCount++;

		$outPutStr .= "<tr class='".$rowClass."'>";

		$outPutStr .= '<td class="bordered-cell username">'.$userName.'</td>';
		$outPutStr .= '<td class="bordered-cell name">'.$firstName."</td>";
		$outPutStr .= '<td class="bordered-cell lastname">'.$lastName."</td>";

		$roleButtonText = $isUserAdmin ? "Downgrade" : "Upgrade";
    	$roleButton = '<form action="/bb/rolechange.php" method="POST">
						<input type="hidden" name="userid" value="'.$userID.'">
						<input type="submit" name="adminrole" value="'.$roleButtonText.'">
					   </form>';
		
		$banButtonText = $banned ? "Unban!" : "Ban!";
		$banButton = '<form action = "/bb/ban.php" method = "POST">
						<input type="hidden" name="userid" id="userid" value="'.$userID.'">
						<input type="submit" name="ban" value="'.$banButtonText.'">
					  </form>';
		
    	$editButton = '<form action="/bb/user.php" method="POST">
						 <input type="hidden" name="userid" value="'.$userID.'">
						 <input type="submit" name="edituser" value="Edit">
					   </form>';
    	
		$outPutStr .= '<td class="bordered-cell button">'.$roleButton.'</td>';
    	$outPutStr .= '<td class="bordered-cell button">'.$banButton.'</td>';
    	$outPutStr .= '<td class="bordered-cell button">'.$editButton.'</td>';

		$outPutStr .= "</tr>";
	}
	$outPutStr .= "</table>";
}

// UTILIZE NEW FEATURES IN OTHER FUNCTIONALITIES TOO

// 6c) also modify relevant other sources to take into account user ban and role:
//     - optional: allow admins to view also hidden messages and threads and reactivate them too (revert hiding, use similar mechanisms)
//         - show hidden content interleaved with visible one using e.g. color highlighting, or use completely separate list views for them


?>


<!DOCTYPE html>
<html>
<head>
    <title>Käyttäjät</title>
	<style>
	table {
        border-collapse: collapse;
        width: 60%;
    }
    th, .bordered-cell {
        border: 1px solid black;
        padding: 8px 10px;
        text-align: left;
    }
	th{
		background-color: #4CAF50;
        color: white;
	}
	.button{
		text-align: center;
	}
    th.username, td.username {
        width: 25%;
    }
    th.name, td.name {
        width: 20%;
    }
    th.lastname, td.lastname {
        width: 20%;
    }
    th.action, td.action {
        width: 12%;
    }
    input[type="submit"] {
        padding: 3px 8px;
        margin: 0;
        border: none;
        background-color: #31708f;
        color: white;
        cursor: pointer;
        font-size: 90%;
    }
    input[type="submit"]:hover {
        background-color: #245269;
    }
    .white-row {
        background-color: white;
    }
    .grey-row {
        background-color: #f2f2f2;
    }
	</style>
</head>
<body> <?php printMenu(); ?>
    <h1> Käyttäjät </h1>
    <div> <?php echo $outPutStr; ?></div>

</body>
</html>