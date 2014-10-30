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

function saltCost(){
    $timeTarget = 0.05;  
    $cost = 8;
    do {
        $cost++;
        $start = microtime(true);
        password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
        $end = microtime(true);
    } while (($end - $start) < $timeTarget);
    return $cost;
    
}
$app->post('/loginUser', function(){
    session_start();
    global $mysqli;
    $email = $_POST['email'];
    $password = $_POST['password'];
    $encryption = ['cost' => saltCost(), 'salt' => mcrypt_create_iv(25,MCRYPT_DEV_URANDOM)];
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
        $sql = "SELECT password FROM passwords p, idUser u WHERE p.idUser=u.idUser AND email='$email'";
                $stmt = $mysqli -> prepare($sql);
                $stmt -> bind_param('ss', $email);
        $passwordVal = $stmt -> fetch_assoc();
                $hashedPassword = password_hash($passwordVal, PASSWORD_BCRYPT, $encryption);
                
                //Potential Problems, Test to see
        $hashedPassword = $hashedPassword['password'];
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

});

$app->post('/getClasses', function() {

});

$app->post('/addClass', function() {

});



$app->run();
?>
