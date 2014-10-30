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



$app->post('/createUserAccount', function () {
    $dummyJSON = array ('u_id'=>1);
    $fName = $_POST['fName'];
    $lName = $_POST['lName'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    echo json_encode($dummyJSON);
});



$app->run();
?>
