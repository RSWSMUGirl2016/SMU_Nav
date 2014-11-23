$(document).ready(function () {

    var marker;
    var map;
    var initialLocation;
    var directionsDisplay;
    var directionsService = new google.maps.DirectionsService();

    function initializeMap() {
        directionsDisplay = new google.maps.DirectionsRenderer();
        var mapOptions = {
            center: {lat: 32.8406452, lng: -96.7831393},
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById('mapWrapper'), mapOptions);
        directionsDisplay.setMap(map);
    }

    //Use the getCoordinats API call and push marker onto map
    $("#submitSearch").click(function () {
        event.preventDefault();
        var coordinatesInfo = {buildingName: $("#buildingName").val(), roomNumber: $("#roomNumber").val(), roomName: $("#roomName").val()};
        $.ajax({
            type: "POST",
            datatype: "json",
            data: coordinatesInfo,
            url: "api/index.php/getCoordinates",
            success: function (result) {
                $("#buildingName").val("");
                $("#roomName").val("");
                $("#roomNumber").val("");
                var json = JSON.parse(result);
                var x = json.x;
                var y = json.y;
                var z = json.z;
                var bounds = new google.maps.LatLngBounds();
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(x, -1*y),
                    title: "Testing!"
                });

                map.setCenter(marker.getPosition());
                marker.setMap(map);

                google.maps.event.addListener(marker, 'click', function () {
                    if(userId === undefined){
                        $("#favoritesHeading").hide();
                        $("#favorites_bttn").hide();
                    }
                    $("#marker_form").dialog("open");                    
                });

                $("#getDirections_bttn").click(function() {
                    marker.setMap(null);
                    event.preventDefault();
                    $("#marker_form").dialog("close");
                    if(navigator.geolocation) {
                        browserSupportFlag = true;
                        navigator.geolocation.getCurrentPosition(function(position) {
                        initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
                        
                        var start = initialLocation;
                        var end = marker.getPosition();
                        var request = {
                            origin: start,
                            destination: end,
                            travelMode: google.maps.TravelMode.WALKING
                        };
                        directionsService.route(request, function(response, status) {
                            if(status == google.maps.DirectionsStatus.OK) {
                                directionsDisplay.setDirections(response);
                            }
                        });

                    }, function() {
                        handleNoGeolocation(browserSupportFlag);
                    });

                  }
                });
                /*map.setCenter(marker.getPosition());
                marker.setMap(map);
                if(userId === undefined){
                    var contentString = '<div id="content">' +
                                        '<div id="siteNotice">' +
                                        '</div>' +
                                        '<div id="bodyContent">' +
                                        '<h6 id="getDirectionsHeading" class="firstHeading">Get Directions</h6>' +
                                        '<input id="getDirections_bttn" type="submit" class="button" value="Start">' +
                                        '</div>' +
                                        '</div>';
                } else {
                    var contentString = '<div id="content">' +
                                        '<div id="siteNotice">' +
                                        '</div>' +
                                        '<div id="bodyContent">' +
                                        '<h6 id="firstHeading" class="favoritesHeading">Add to Favorites</h6>' +
                                        '<input id="favorites_bttn" type="submit" class="button" value="Add">' +
                                        '<h6 id="getDirectionsHeading" class="firstHeading">Get Directions</h6>' +
                                        '<input id="getDirections_bttn" type="submit" class="button" value="Start">' +
                                        '</div>' +
                                        '</div>';
                }
                var infowindow = new google.maps.InfoWindow({
                    content: contentString
                });                    
                google.maps.event.addListener(marker, 'click', function () {
                    infowindow.open(map, marker);
                });
                $("#favorites_bttn").click(addFavorites);*/

                //$("#getDirections_bttn").click(function() {
                    //window.alert("Entered");
                    /*directionsService = new google.maps.DirectionsService();
                    var rendererOptions = {
                        map: map
                    }
                    directionsDisplay = new google.maps.DirectionsRenderer(rendererOtpions);
                    stepDisplay = new google.maps.InfoWindow();

                    var start = navigator.geolocation.getCurrentPosition(function (position) {
                                var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                    });
                    var end = marker.getPosition();
                    var request = {
                        origin: start,
                        destination: end,
                        travelmode: google.maps.TravelMode.WALKING
                    };
                    directionsService.route(request, function(response, status){
                        if (status == google.maps.DirectionsStatus.OK) {
                            var warnings = document.getElementById('warnings_panel');
                            warnings.innerHTML = '<b>' + response.routes[0].warnings + '</b>';
                            directionsDisplay.setDirections(response);
                            showSteps(response);
                        }
                    });*/
                //});        
            }
        });
    });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
        var pos = new google.maps.LatLng(position.coords.latitude,
                                         position.coords.longitude);

        var infowindow = new google.maps.InfoWindow({
            map: map,
            position: pos,
            content: 'My Location.'
        });

        map.setCenter(pos);
        }, function () {
            console.log("geolocation not working");
        });
    } else {
        // Browser doesn't support Geolocation
        console.log("You browser doesn't support geolocation");
    }


    google.maps.event.addDomListener(window, 'load', initializeMap);
    $("#menu_button").click(function () {
        if ($("#menuWrapper").attr("collapsed") === "true") {
            // Expand Menu
            toggleMenu('show');
        } else {
            // Collapse Menu
            toggleMenu('hide');
        }
    });

    //Registration Popup
    $("#register_form").dialog({
        autoOpen: false, height: 300, width: 350, modal: true, background: "blue"
    });
    $("#registration").click(function () {
        $("#register_form").dialog("open");
    });
    $("#cancel").click(function () {
        $("#register_form").dialog("close");
    });
    //Form Submit
    $("#login").click(login);
    $("#register").click(register);
    $("#logout").click(logout);

    //Marker Popup
    $("#marker_form").dialog({
        autoOpen: false, height: 300, width: 350, modal: true, background: "blue"
    });

    if(userId === undefined){
        $('#favorites').hide();
    } 

    getEvents();

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
            if (statusJson.status === "Failure") {
                window.alert("Incorrect Password or Email");
            }
            else {
                $("#login_form").css('display', 'none');
                $("#SignedIn").css('display', 'inline');
                $("#welcome").text("Welcome!!");
                $("#favorites").show();
                $("#favoritesHeading").show();
                $("#favorites_bttn").show();
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
                    $("#register_form").dialog("close");
                    $("#welcome").text("Welcome!!");
                    $("#favorites").show();
                    $("#favoritesHeading").show();
                    $("#favorites_bttn").show();
                    userId = statusJson.user_id;
                    email = statusJson.email;
                    firstName = statusJson.firstName;
                    lastName = statusJson.lastName;
                }});
        }
    });
}

function logout(event) {
    event.preventDefault();
    $.ajax({
        type: "POST",
        url: "api/index.php/logout",
        success: function (result) {
            $("#login_form").css('display', 'inline');
            $("#SignedIn").css('display', 'none');
        }
    });
}

function toggleMenu(action) {
    if (action == 'show') {
        $('#menuWrapper').show("slide", { direction: "left" }, 300);
        $("#menuWrapper").attr("collapsed", "false");

        // Shrink Map
        toggleMap('shrink');
    } else if (action == 'hide') {
        $('#menuWrapper').hide("slide", { direction: "left" }, 300);
        $("#menuWrapper").attr("collapsed", "true");

        // Grow Map
        toggleMap('grow');
        
    } else {
        console.log('Invalid parameter: toggleMenu(' + action + ')');
    }
}

function toggleMap(action) {
  if (action == 'grow') {
      $('#mapWrapper').animate({
        width: '98%'
      }, 300);
  } else if (action == 'shrink') {
      $('#mapWrapper').animate({
        width: '83%'
      }, 300);
  } else {
      console.log('Invalid parameter: toggleMap(' + action + ')');
  }
}


//Menu jquery and js
/*$(document).ready(function() {
 // Hide submenus
 $(".favs_title").click(function() {
 $(".favs_list").slideToggle(300);  $(this).toggleClass('close');
 });
 });  
 $(document).ready(function() {  // Hide submenus
 $(".recomms_title").click(function() {
 $(".recomms_list").slideToggle(300);
 $(this).toggleClass('close');
 });
 });*/


$(function() {
 $('#directionsWrapper').hide();
});

$(document).ready(function () {
// Hide submenus
    $("#print").click(function () {
        var prtTitle = document.getElementById("dir_title");
        var prtDirections = document.getElementsByName("printable");
        var print = '';
        var styleTitle = '<style>p{font-weight: bold;}</style>';
        print += styleTitle;
        print += prtTitle.innerHTML;
        for (var i = 0; i < prtDirections.length; i++) {
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
$(document).ready(function () {
    // Hide submenus
    $("#cancel_direcs").click(function () {
        $('#directionsWrapper').hide();
    });
});
function sendEmail(event) {
    event.preventDefault();
    var emailMssg = {"to": email,
        "html": "SMU NAV"};
    $.ajax({
        type: "POST",
        datatype: "json",
        data: emailMssg,
        url: "api/index.php/sendEmail",
        success: function (result) {
            window.alert("Email sent");
        }
    });
}

function getFavorites() {
    var Id = {"userId": userId};
    $.ajax({
        type: "POST",
        datatype: "json",
        data: Id,
        url: "api/index.php/getFavorites",
        success: function (result) {
        }
    });
}

function addFavorites() {
    window.alert("Adding");
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
        success: function (result) {

        }
    });
}

function getEvents() {
    $.ajax({
        type: "GET",
        datatype: "json",
        url: "api/index.php/getEvents",
        success: function (result) {
            var json = JSON.parse(result);
            var html = '';
            $.each(json, function(key, value) {
                //console.log(key, value, value.name);
                var coords = value.x+","+value.y+","+value.z;
                var rel = value.time+","+value.description;
                html += '<li><a href="" coords="'+coords+'" rel="'+rel+'">'+value.name+'</a></li>';
            });
            $(".events_list").append(html);
        }
    });
}
