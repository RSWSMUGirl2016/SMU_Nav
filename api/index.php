<?php
require 'vendor/autoload.php';
$app = new \Slim\Slim();
$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});


$app->post('/createUserAccount', function () {
    $dummyJSON = array ('u_id'=>1);
    $fName = $_POST['fName'];
    $lName = $_POST['lName'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    echo json_encode($dummyJSON);
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

$app->run();
?>
