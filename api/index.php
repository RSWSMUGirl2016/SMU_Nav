<?php
require 'vendor/autoload.php';
$app = new \Slim\Slim();
$app->get('/getEvents', function () {
   $dummyData = '{
        "1": {
            "name": "event_name",
            "location": [
                3.1234,
                4.1234
            ],
            "description": "Event description",
            "eventDateTime": "9999-12-31 23:59:59"
        },
        "2": {
            "name": "event_name",
            "location": [
                3.1234,
                4.1234
            ],
            "description": "Event description",
            "eventDateTime": "9999-12-31 23:59:59"
        }
    }'; 

    echo json_encode(json_decode($dummyData, true));
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
			    $insertion = $mysqli->query("INSERT INTO User (idUser, firstName, lastName, email, password) VALUES (1, '$fName', '$lName', '$email', '$password')");
			}
			else{
			    $newID = $row['idUser']+1;
			    $outputJSON = array ('u_id'=>$newID);
			    $insertion = $mysqli->query("INSERT INTO User (idUser, firstName, lastName, email, password) VALUES ($newID, '$fName', '$lName', '$email', '$password')");
		    }
                }
            }
	
	echo json_encode($outputJSON);
});



$app->run();
?>
