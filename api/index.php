<?php
require 'vendor/autoload.php';
$app = new \Slim\Slim();

$mysqli = new mysqli("localhost", "root", "SMUGirl2016", "mydb");
if ($mysqli->connect_errno)
    die("Connection failed: " . $mysqli->connect_error);

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

$app->post('/getCoordinates', function (){
    global $mysqli;
    $bName = $_POST['buildingName'];
    $rName = $_POST['roomName'];
    $rNum = $_POST['roomNumber'];

    if($rName!=null){    //getCoordinates by room name
        #echo "Its a wonderful day!";
        $firstQuery=$mysqli->query("SELECT x, y, z FROM Coordinates WHERE idCoordinates=
            (SELECT Coordinates_idCoordinates FROM Location WHERE roomName='$rName')");

        $firstResult=$firstQuery->fetch_assoc();
        echo json_encode($firstResult);
    }else if($bName!=null && $rName==null && $rNum==null){  //getCoordinates by building name
        $firstQuery=$mysqli->query("SELECT x, y, z FROM Coordinates INNER JOIN Location ON 
            Coordinates.idCoordinates= Location.Coordinates_idCoordinates WHERE buildingName='$bName' 
            AND roomName IS NULL AND roomNumber IS NULL");

        $firstResult=$firstQuery->fetch_assoc();
        echo json_encode($firstResult);
    }else if($bName!=null && $rName==null && $rNum!=null){  //getCoordinates by buildingName and roomNumber
        $firstQuery=$mysqli->query("SELECT x, y, z FROM Coordinates WHERE idCoordinates=
            (SELECT Coordinates_idCoordinates FROM Location WHERE buildingName='$bName' AND 
            roomNumber='$rNum')");

        $firstResult=$firstQuery->fetch_assoc();
        echo json_encode($firstResult);
    }
});



$app->post('/loginUser', function(){
    session_start();
    global $mysqli;
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    try {
    $sql = "SELECT idUser FROM User WHERE email=(?)";
    $stmt = $mysqli -> prepare($sql);
        $stmt -> bind_param('ss', $email);
    $username_test = $stmt -> fetch_assoc();

    if(($username_test === NULL)) {
        $JSONarray = array(
            'status'=>'Failure', 
            'user_id'=>NULL,
            'fName'=>NULL,
            'lName'=>NULL,
                        'email'=>NULL);
        return json_encode($JSONarray);
    }
    else{
        $sql = "SELECT password FROM User WHERE email='$email'";
        $stmt = $mysqli -> prepare($sql);
        $stmt -> bind_param('ss', $email);
        $passwordVal = $stmt -> fetch_assoc();
        
        $hashedPassword = $passwordVal['password'];
        if($hashedPassword === NULL) {
                $JSONarray = array(
                'status'=>'Failure', 
                'user_id'=>NULL,
                'fName'=>NULL,
                'lName'=>NULL,
                'email'=>NULL);
            return json_encode($JSONarray);
        } 
    
        else if($password === $hashedPassword) {                
            $_SESSION['loggedin'] = true;
            $query = "SELECT idUser FROM User WHERE email=(?)";
                        $stmt2 = $mysqli -> prepare($query);
                        $stmt2 -> bind_param('ss', $email);         
            $temp = $stmt2 -> fetch_assoc();    
            $_SESSION['userId'] = $temp['idUser'];
            $_SESSION['email'] = $email;    
            $statusFlg = 'Succeed';
    
            $components = "SELECT * FROM User WHERE email=(?)";
            $returnValue = $mysqli -> prepare($components);
                        $returnValue -> bind_param('ss', $email);
            $iteration = $returnValue -> fetch_assoc();
            $JSONarray = array(
                'status'=>$statusFlg,
                'user_id'=>$iteration['idUser'],
                'fName'=>$iteration['fName'],
                'lName'=>$iteration['lName'],
                'email'=>$iteration['email']);
            
            return json_encode($JSONarray); 
        } 
        //verifies password
        else {
            $JSONarray = array(
                'status'=>'Failure', 
                'user_id'=>NULL,
                'fName'=>NULL,
                'lName'=>NULL,
                'email'=>NULL);
            return json_encode($JSONarray);
        }
    }
    //returns null when password is wrong
        $mysqli = null;
    } catch(exception $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}'; 
    }
});

$app->post('/logout', function()  { 
    $_SESSION = array(); 
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
});

$app->post('/getCoordinates', function(){
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

$app->post('/getClasses', function() {
    global $mysqli;
    $userID = $_POST['userID'];
    $day = $_POST['day'];
    $outputJSON = array();
    if($userID === "" || $day === "")
	$outputJSON = array('Status'=>'Failure');
    else{
	array_push($outputJSON, array('Status'=>'Success'));
	$classQuery = $mysqli->query("SELECT * FROM Classes NATURAL JOIN Location NATURAL JOIN Coordinates WHERE User_idUser = $userID AND day = '$day'");
	$counter = 0;
	while(true){
	    $classOutput = array();
	    $classList = $classQuery->fetch_assoc();
	    if($classList === NULL)
		break;
	    $classOutput["classTime"] = $classList["classTime"]; //array("classTime" => $classList["classTime"]));
	    $classOutput["buildingName"] = $classList["buildingName"]; //array_push($classOutput, array("buildingName" => $classList["buildingName"]));
	    $classOutput["roomName"] = $classList["roomName"]; //array_push($classOutput, array("roomName" => $classList["roomName"]));
	    $classOutput["roomNumber"] = $classList["roomNumber"]; //array_push($classOutput, array("roomNumber" => $classList["roomNumber"]));
	    $classOutput["x"] = $classList["x"]; //array_push($classOutput, array("x" => $classList["x"]));
	    $classOutput["y"] = $classList["y"]; //array_push($classOutput, array("y" => $classList["y"]));
	    $classOutput["z"] = $classList["y"]; //array_push($classOutput, array("z" => $classList["z"]));
	    $outputJSON[$counter+=1] = $classOutput;
	}
    echo json_encode($outputJSON);
    }
});

$app->post('/addClass', function() {
    global $mysqli;
    $userID = $_POST['userID'];
    $time = $_POST['time'];
    $day = $_POST['day'];
    $building = $_POST['building'];
    $roomNumber = $_POST['roomNumber'];
    $roomName = $_POST['roomName'];
    if($building === "")
	$outputJSON = array('Status'=>'Failure');
    else if($roomNumber === "" && $roomName === ""){
	    $locationQuery = $mysqli->query("SELECT idLocation FROM Location WHERE buildingName = '$building' LIMIT 1");
	    $locationRow = $locationQuery->fetch_assoc();
	    if($locationRow === NULL)
		$outputJSON = array('Status'=>'Failure');
	    else{
		$location = $locationRow['idLocation'];
		$insertion = $mysqli->query("INSERT INTO Classes (User_idUser, classTime, day, Location_idLocation) VALUES ($userID, '$time', '$day', $location)");
		$outputJSON = array('Status'=>'Success');
		}
	}
	else if($roomNumber === ""){
	    $locationQuery = $mysqli->query("SELECT idLocation FROM Location WHERE buildingName = '$building' AND roomName '$roomName' LIMIT 1");
	    $locationRow = $locationQuery->fetch_assoc();
	    if($locationRow === NULL)
		$outputJSON = array('Status'=>'Failure');
	    else{
		$location = $locationRow['idLocation'];
		$insertion = $mysqli->query("INSERT INTO Classes (User_idUser, classTime, day, Location_idLocation) VALUES ($userID, '$time', '$day', $location)");
		$outputJSON = array('Status'=>'Success');
		}
	}
	else if($roomName === ""){
	    $locationQuery = $mysqli->query("SELECT idLocation FROM Location WHERE buildingName = '$building' AND roomNumber = '$roomNumber' LIMIT 1");
	    $locationRow = $locationQuery->fetch_assoc();
	    if($locationRow === NULL)
		$outputJSON = array('Status'=>'Failure');
	    else{
		$location = $locationRow['idLocation'];
		$insertion = $mysqli->query("INSERT INTO Classes (User_idUser, classTime, day, Location_idLocation) VALUES ($userID, '$time', '$day', $location)");
		$outputJSON = array('Status'=>'Success');
		}
	}
	else{
	    $locationQuery = $mysqli->query("SELECT idLocation FROM Location WHERE buildingName = '$building' AND roomName = '$roomName' AND roomNumber = '$roomNumber' LIMIT 1");
	    $locationRow = $locationQuery->fetch_assoc();
	    if($locationRow === NULL)
		$outputJSON = array('Status'=>'Failure');
	    else{
		$location = $locationRow['idLocation'];
		$insertion = $mysqli->query("INSERT INTO Classes (User_idUser, classTime, day, Location_idLocation) VALUES ($userID, '$time', '$day', $location)");
		$outputJSON = array('Status'=>'Success');
		}
	}
	    
	echo json_encode($outputJSON);

});



$app->run();
?>
