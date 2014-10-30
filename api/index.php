<?php
require 'vendor/autoload.php';
$app = new \Slim\Slim();
$app->get('/getEvents', function ($name) {
    echo "Hello, $name";
});

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



$app->run();
?>
