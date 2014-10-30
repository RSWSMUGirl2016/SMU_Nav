<?php
require 'vendor/autoload.php';
$app = new \Slim\Slim();
$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});

$mysqli = new mysqli("localhost", "root", "compassstudios", "mydb");
if ($mysqli->connect_errno)
    die("Connection failed: " . $mysqli->connect_error);

$app->post('/createUserAccount', function () {
    global $mysqli;
    $fName = $_POST['fName'];
    $lName = $_POST['lName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    if($fName === "" || $lName === "" || $email === "" || $password === "")
	$outputJSON = array ('u_id'=>-2);
    else{
	$dupCheck = $mysqli->query("SELECT email FROM User WHERE email = '$email' LIMIT 1");
	$checkResults = $dupCheck->fetch_assoc();
	    if(!($checkResults === NULL))
		$outputJSON = array ('u_id'=>-1);
	    else{
			$prevUser = $mysqli->query("SELECT idUser FROM User ORDER BY idUser DESC LIMIT 1");
			$row = $prevUser->fetch_assoc();
			if($row === NULL){
			    $outputJSON = array ('u_id'=>1);
			    $insertion = $mysqli->query("INSERT INTO User (idUser, firstName, lastName, email) VALUES (1, '$fName', '$lName', '$email')");
			    $passInsertion = $mysqli->query("INSERT INTO Passwords VALUES (1, '$password')");
			}
			else{
			    $newID = $row['idUser']+1;
			    $outputJSON = array ('u_id'=>$newID);
			    $insertion = $mysqli->query("INSERT INTO User (idUser, firstName, lastName, email) VALUES ($newID, '$fName', '$lName', '$email')");
			    $passInsertion = $mysqli->query("INSERT INTO Passwords VALUES ($newID, '$password')");
		    }
                }
            }
	
	echo json_encode($outputJSON);
});



$app->run();
?>
