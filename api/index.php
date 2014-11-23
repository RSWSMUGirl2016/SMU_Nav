<?php
require 'vendor/autoload.php';
require 'SendGrid/vendor/autoload.php';
$app = new \Slim\Slim();
$mysqli = new mysqli("localhost", "root", "compassstudios", "mydb");
if ($mysqli->connect_errno)
    die("Connection failed: " . $mysqli->connect_error);


$app->get('/getEvents', function () {
    global $mysqli;
    $outputJSON = array();
    $classQuery = $mysqli->query("SELECT * FROM Event INNER JOIN Location ON Event.Location_idLocation = Location.idLocation INNER JOIN Coordinates ON Location.Coordinates_idCoordinates = Coordinates.idCoordinates");
    $counter = 0;
    while(true){
	$classOutput = array();
	$classList = $classQuery->fetch_assoc();
	if($classList === NULL)
	    break;
	$classOutput["name"] = $classList["name"];
	$classOutput["description"] = $classList["description"];
	$classOutput["time"] = $classList["eventDateTime"]; 
	$classOutput["x"] = $classList["x"]; 
	$classOutput["y"] = $classList["y"]; 
	$classOutput["z"] = $classList["z"]; 
	$outputJSON[$counter+=1] = $classOutput;
	}
    echo json_encode($outputJSON);
});

$app->get('/getBuildingNames', function () {
    $buildings = '{ "buildings": 
    [
"Annette Caldwell Simmons Hall",
"Armstrong Commons",
"Arnold Dining Commons",
"Beta Theta Pi",
"Binkley Parking Center",
"Blanton Student Services Building",
"Boaz Commons",
"Bridwell Library",
"Cafe 100",
"Camps: Conference Services",
"Carr Collins Hall",
"Caruth Hall",
"Chi Omega",
"Chick-fil-a",
"Clements Hall",
"Cockrell-McIntosh Commons",
"Collins Center (Crum Auditorium)",
"Conference Services",
"Copy Services",
"Crow Building",
"Crum Basketball Center",
"Crum Commons",
"Dallas Hall",
"Daniel II",
"Daniel Parking Center",
"Data Center",
"Dawson Service Center",
"Dedman Center for Lifetime Sports",
"Dedman Life Sciences Building",
"Delta Delta Delta",
"Delta Gamma",
"Dining Hall @ Umphrey Lee",
"Direct Mail",
"Einstein Bros. Bagels",
"Embrey Engineering Building",
"Expressway Tower",
"Fincher Building",
"Florence Hall",
"Fondren Library Center (DeGolyer Library)",
"Fondren Science Building",
"Ford Stadium",
"Future Development",
"Gamma Phi Beta",
"Gates Restaurant",
"George W. Bush Presidential Center",
"Giddy-Up Security Escort Service",
"Greer Garson Theatre",
"Hamon Arts Library",
"Harold Clark Simmons Hall",
"Hawk Hall",
"Health Center (Temporary Location)",
"Heroy Science Hall",
"Highland Park United Methodist Church",
"Hillcrest Manor",
"Hughes-Trigg Student Center (Centennial Hall)",
"Hyer Hall",
"ID Card Services",
"Images",
"Junkins Engineering Building",
"Kappa Alpha Order",
"Kappa Alpha Theta",
"Kappa Kappa Gamma",
"Kappa Sigma",
"Kathy Crow Commons",
"Kirby Hall"
    ]
     }';
    echo json_encode(json_decode($buildings, true));
});


$app->get('/getRoomNames', function () {
    $rooms = '{ "rooms": 
    [
"Janitor\'s Closet",
"Conference Room",
"Break Room",
"Stairs",
"Elevator",
"Systems of Engineering Office",
"Electrical Closet",
"Lyle Graduate School Office",
"Cullum Conference Room",
"EMIS office",
"Control Room",
"TA/RA",
"Stairs",
"I.D.F.",
"TA/RA",
"Chair Storage",
"Mark Fontenot Office",
"Tyler Moore Office",
"Don Evans Office",
"Suku Nair Office",
"Maryanne Anderson Office",
"Jeff Tian Office ",
"LiGuo Huang Office",
"Fred Chnag Office",
"Ira Greenber Office",
"Eric Larson Office",
"Todd Wright Office",
"David Matula Office",
"Frank Coyle Office",
"Daniel Engels Office",
"Doug Tucker Office",
"Merlin Wilkerson Office",
"Theodore Manikas Office",
"Jennifer Dworak Office",
"Mitch Thornton Office",
"Steve Szygenda Office",
"Lab",
"Lab",
"Offices",
"Janitor\'s Closet",
"Innovation Gym",
"Vester-Hughes",
"Mary Alice and Mark Sheperd Jr. Atrium",
"Hart Center",
"Hunt Institute for Eng. and Humanity"
    ]
     }';
    echo json_encode(json_decode($rooms, true));
});


$app->get('/getRoomNumbers', function () {
    $roomNumbers = '{ "roomNumbers": 
    [
314,
308,
312,
306,
320,
303,
301,
999,
999,
353,
347,
337,
369,
373,
302,
374,
372,
384,
383,
379,
406,
412,
441,
439,
435,
433,
431,
425,
421,
419,
549,
451,
455,
457,
459,
461,
467,
473,
477,
479,
481,
483,
485,
484,
472,
469,
400,
120,
116,
106,
136,
135,
147,
157,
156,
159,
161,
176,
183,
184,
266,
256,
999,
999,
239,
235,
233,
231,
221,
219,
999,
251,
255,
257,
259,
261,
267,
269,
271,
273,
279,
270,
268,
284,
272
    ]
     }';
    echo json_encode(json_decode($roomNumbers, true));
});



$app->post('/sendEmail', function (){

    $to = $_POST['to'];
    $html = $_POST['html'];
    //perhaps add destination field for subjects
    
    $sendgrid = new SendGrid("oxymo", "compassstudios", array("turn_off_ssl_verification" => false));

    $email = new SendGrid\Email();
    $email->setFrom('compassstudios@gmail.com')->
            setSubject('SMU Nav Directions')->
            setHtml($html)->
            addTo($to);

    $response1 = $sendgrid->send($email);
    //echo $response1;
    //$this->assertEquals("Bad username / password", $response->errors[0]);
    echo "Success probably!";
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
	    $classOutput["classTime"] = $classList["classTime"]; 
	    $classOutput["buildingName"] = $classList["buildingName"];
	    $classOutput["roomName"] = $classList["roomName"]; 
	    $classOutput["roomNumber"] = $classList["roomNumber"];
	    $classOutput["x"] = $classList["x"]; 
	    $classOutput["y"] = $classList["y"]; 
	    $classOutput["z"] = $classList["z"]; 
	    $outputJSON[$counter+=1] = $classOutput;
	}
    }
    echo json_encode($outputJSON);
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
	    $classOutput["z"] = $classList["z"]; //array_push($classOutput, array("z" => $classList["z"]));
	    $outputJSON[$counter+=1] = $classOutput;
	}
    echo json_encode($outputJSON);
    }
});

$app->run();
?>


