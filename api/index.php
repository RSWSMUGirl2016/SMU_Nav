<?php
require 'vendor/autoload.php';
$app = new \Slim\Slim();
$mysqli = new mysqli("localhost", "root", "compassstudios", "mydb");
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
    $stmt -> bind_param('s', $email);
    $stmt -> execute();
    $username_test = $stmt -> fetch();
    if(($username_test === NULL)) {
        $JSONarray = array(
            'status'=>'Failure', 
            'user_id'=>NULL,
            'fName'=>NULL,
            'lName'=>NULL,
            'email'=>NULL);
        echo json_encode($JSONarray);
        return;
    }
    else{
        $stmt->close();

        $sql = "SELECT password FROM User WHERE email=(?)";
        $stmt1 = $mysqli -> prepare($sql);
        $stmt1 -> bind_param('s', $email);
        $stmt1 -> execute();
        
        $stmt1->bind_result($passwordVal);
        $stmt1 -> fetch();
       
        if($passwordVal === NULL) {
            $JSONarray = array(
            'status'=>'Failure', 
            'user_id'=>NULL,
            'fName'=>NULL,
            'lName'=>NULL,
            'email'=>NULL);
            echo json_encode($JSONarray);
            return;
        } 
    
        else if($password === $passwordVal) { 
            $stmt1->close();              
            $_SESSION['loggedin'] = true;
            $query = "SELECT idUser FROM User WHERE email=(?)";
            $stmt2 = $mysqli -> prepare($query);
            $stmt2 -> bind_param('s', $email);
            $stmt2 -> execute();
            $stmt2->bind_result($temp);         
            $stmt2 -> fetch();    
            $_SESSION['userId'] = $temp;
            $_SESSION['email'] = $email;    
            $statusFlg = 'Succeed';
            $stmt2->close();

            $components = "SELECT * FROM User WHERE email='$email'";
            $returnValue = $mysqli -> query($components);
            $iteration = $returnValue -> fetch_assoc();
            $JSONarray = array(
                'status'=>$statusFlg,
                'user_id'=>$iteration['idUser'],
                'firstName'=>$iteration['firstName'],
                'lastName'=>$iteration['lastName'],
                'email'=>$iteration['email']);
            echo json_encode($JSONarray);
            return;
        } 
        //verifies password
        else {
            $JSONarray = array(
                'status'=>'Failure', 
                'user_id'=>NULL,
                'fName'=>NULL,
                'lName'=>NULL,
                'email'=>NULL);
            echo json_encode($JSONarray);
            return;
        }
    }
    //returns null when password is wrong
        $mysqli = null;
    } catch(exception $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}'; 
    }
    echo "Finish5";
});
$app->post('/logout', function()  { 
    session_start();
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
$app->post('/createUserAccount', function(){
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
	$classQuery = $mysqli->query("SELECT * FROM Classes INNER JOIN Location ON Classes.Location_idLocation = Location.idLocation INNER JOIN Coordinates ON Location.Coordinates_idCoordinates = Coordinates.idCoordinates WHERE User_idUser = $userID AND day = '$day'");
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

$app->post('/addFavorite', function() {
    global $mysqli;
    $userID = $_POST['userID'];
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
		$insertion = $mysqli->query("INSERT INTO Favorites (User_idUser, Location_idLocation) VALUES ($userID, $location)");
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
		$insertion = $mysqli->query("INSERT INTO  Favorites (User_idUser, Location_idLocation) VALUES ($userID, $location)");
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
		$insertion = $mysqli->query("INSERT INTO  Favorites (User_idUser, Location_idLocation) VALUES ($userID, $location)");
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
		$insertion = $mysqli->query("INSERT INTO  Favorites (User_idUser, Location_idLocation) VALUES ($userID, $location)");
		$outputJSON = array('Status'=>'Success');
		}
	}
	    
	echo json_encode($outputJSON);
    
});


$app->post('/getFavorites', function() {
    global $mysqli;
    $userID = $_POST['userID'];
    $outputJSON = array();
    if($userID === "")
	$outputJSON = array('Status'=>'Failure');
    else{
	array_push($outputJSON, array('Status'=>'Success'));
	$classQuery = $mysqli->query("SELECT * FROM Favorites INNER JOIN Location ON Favorites.Location_idLocation = Location.idLocation INNER JOIN Coordinates ON Location.Coordinates_idCoordinates = Coordinates.idCoordinates WHERE User_idUser = $userID");
	$counter = 0;
	while(true){
	    $classOutput = array();
	    $classList = $classQuery->fetch_assoc();
	    if($classList === NULL)
		break;
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

$app->run();
?>


