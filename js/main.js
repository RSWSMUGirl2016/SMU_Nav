$(document).ready(function () {
	
    var marker;
    var map;
    function initializeMap() {
        var mapOptions = {
            center: {lat: 32.8406452, lng: -96.7831393},
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        map = new google.maps.Map(document.getElementById('mapWrapper'), mapOptions);

        var buildingName;
        var roomName;
        var roomNumber;

    }
	google.maps.event.addDomListener(window, 'load', initializeMap);

	$("#menu_button").click(function(){
        if($("#menuWrapper").attr("collapsed") === "true") {
            $("#menuWrapper").animate({
                'left': '-2em'
            }, function() {
                $("#menuWrapper").attr("collapsed", "false");
            });
        } else {
            $("#menuWrapper").animate({
                'left': '-20em'
            }, function() {
                $("#menuWrapper").attr("collapsed", "true");
            });
        }
    });
	
    //Registration Popup
    $("#register_form").dialog({
        autoOpen:false, height:300, width:350, modal: true, background:"blue"
    });
    $( "#registration").click(function() {
        $("#register_form" ).dialog( "open" );
    });
    $("#cancel").click(function() {
       $("#register_form" ).dialog( "close" ); 
    });

    //Form Submit
    $("#login").click(login);
    $("#register").click(register);
    $("#logout").click(logout);
});

var userId;
var email;
var firstName;
var lastName;

function login(event) {
    event.preventDefault();
    var loginInfo = {email: $("#SignInEmail").val(), password: $("#SignInPassword").val()};
    $.ajax({
        type: "POST",
        url: "./api/index.php/loginUser",
        datatype: "json",
        data: loginInfo,
        success: function (result) {
            var statusJson = JSON.parse(result);
            if(statusJson.status === "Failure"){
                window.alert("Incorrect Password or Email");
            }
            else{
                $("#login_form").css('display', 'none');
                $("#SignedIn").css('display', 'inline');
                $("#welcome").text("Welcome!!");
                userId = statusJson.user_id;
                email = statusJson.email;
                firstName = statusJson.firstName;
                lastName = statusJson.lastName;
            }
        }
    });
}

function register(event) {
    event.preventDefault();
    var registerInfo = {"fName": $("#fName").val(), 
        "lName": $("#lName").val(),
        "email": $("#Email").val(),
        "password": $("#Password").val()};
    
    console.log(registerInfo);
    $.ajax({
        type: "POST",
        url: "api/index.php/createUserAccount",
        datatype: "json",
        data: registerInfo,
        success: function (result) {
            var loginInfo = {"email": $("#Email").val(),
                "password": $("#Password").val()}; 
            console.log(loginInfo);
            $.ajax({
                type: "POST",
                url: "api/index.php/loginUser",
                datatype: "json",
                data: loginInfo,
                success: function (result) {
                    $("#login_form").css('display', 'none');
                    $("#SignedIn").css('display', 'inline');
                    $("#register_form" ).dialog( "close" );
                    $("#welcome").text("Welcome!!");
                    userId = statusJson.user_id;
                    email = statusJson.email;
                    firstName = statusJson.firstName;
                    lastName = statusJson.lastName;
                }
            });
        }
    });
            
}

function logout(event){
    event.preventDefault();
    $.ajax({
       type: "POST",
       url: "api/index.php/logout",
       success: function(result){
           $("#login_form").css('display', 'inline');
           $("#SignedIn").css('display', 'none');
       }
    });
    
}


//Menu jquery and js
//Hide these if user is not logged in
/*$(function() {
    $('#favorites, #recommended')
        .hide();
});*/

/*$(function() {
    $('#dir_title', '#directions')
        .hide();
});*/

/*$(document).ready(function(){
  $('.favs_list').hide();
  $('.recomms_list').hide();
});

$(document).ready(function() {
    // Hide submenus
    $(".favs_title").click(function() {
        $(".favs_list").slideToggle(300);
        $(this).toggleClass('close');
    });
});

$(document).ready(function() {
    // Hide submenus
    $(".recomms_title").click(function() {
        $(".recomms_list").slideToggle(300);
        $(this).toggleClass('close');
    });
});*/

$(document).ready(function() {
    // Hide submenus
    $("#print").click(function() {
        var prtTitle = document.getElementById("dir_title");
        var prtDirections = document.getElementsByName("printable");
        var print = '';
        var styleTitle = '<style>p{font-weight: bold;}</style>';
        print += styleTitle;
        print += prtTitle.innerHTML;
        for(var i = 0; i < prtDirections.length; i++){
            print += prtDirections[i].innerHTML;
            print += '<br></br>';
        }
        console.log(styleTitle);
        console.log(print);
        var WinPrint = window.open('', '', 'letf=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
        WinPrint.document.write(print);
        WinPrint.document.close();
        WinPrint.focus();
        WinPrint.print();
        WinPrint.close();
    });
    $("#emailButton").click(sendEmail);
    $("#favorites_bttn").click(addFavorites);
});

$(document).ready(function() {
    // Hide submenus
    $("#cancel_direcs").click(function() {
        $('#directionsWrapper').hide();
    });
});

function sendEmail(event){
    event.preventDefault();
    var emailMssg = {"to": email, 
        "html":"SMU NAV"};
    $.ajax({
       type: "POST",
       datatype:"json",
       data: emailMssg,
       url: "api/index.php/sendEmail",
       success: function(result){
           window.alert("Email sent");
       }
    });
    
}

function getFavorites(){
    var Id = {"userId": userId}; 
    $.ajax({
       type: "POST",
       datatype: "json",
       data: Id,
       url: "api/index.php/getFavorites",
       success: function(result){           
       }
    });
}

function addFavorites(){
    var favoriteInfo;
    //var favoriteInfo = {"userId": userId,
        //"building": ,
        //"roomNumber": ,
        //"roomName":};
    $.ajax({
        type: "POST",
        datatype: "json",
        data: favoriteInfo,
        url: "api/index.php/addFavorites",
        sucess: function(result){
            
        }
    });
}
