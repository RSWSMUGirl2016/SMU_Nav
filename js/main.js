$(document).ready(function () {
	var map;
    function initializeMap() {
        var mapOptions = {
            center: {lat: 32.8406452, lng: -96.7831393},
            zoom: 15
        };

        map = new google.maps.Map(document.getElementById('mapWrapper'), mapOptions);
    }
	google.maps.event.addDomListener(window, 'load', initializeMap);

	$("#menu_button").click(function(){
        if($("#menuWrapper").attr("collapsed") == "true") {
            $("#menuWrapper").animate({
                'left': '-2em'
            }, function() {
                $("#menuWrapper").attr("collapsed", "false");
            });
        } else {
            $("#menuWrapper").animate({
                'left': '-16em'
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
    var signIn = document.getElementById("signInArea");
    var register = document.getElementById("registerArea");
    signIn.addEventListener('submit', login, false);
    register.addEventListener('submit', register, false);
});


function login(event) {
    event.preventDefault();
    var loginInfo = {email: $("#Email").val(), password: $("#Password").val()};
    $.ajax({
        type: "POST",
        url: "./api/loginUser",
        data: JSON.stringify(loginInfo),
        success: function (result) {
            $("#login_form").css('display', 'none');
            $("#SignedIn").css('display', 'inline');
        }
    });
}

function register(event) {
    event.preventDefault();
    var registerInfo = {firstname: $("#fname").val(), 
        lastname: $("#lname").val(),
        email: $("#email").val(),
        password: $("#password").val()};
    $.ajax({
        type: "POST",
        url: "api/createUserAccount",
        datatype: "json",
        data: JSON.stringify(registerInfo),
        success: function (result) {
            if (result === "error_email") {
                return;
            }
            $.ajax({
                type: "POST",
                url: "api/loginUser",
                data: {
                    email: $("#Email").val(),
                    password: $("#Password").val()
                },
                success: function (result) {
                    $("#login_form").css('display', 'none');
                    $("#SignedIn").css('display', 'inline');
                }
            });
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
