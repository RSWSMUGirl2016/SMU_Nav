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
        directionsDisplay.setPanel(document.getElementById('directions'));
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
                var json = JSON.parse(result);
                if (json === null) {
                    window.alert("Sorry the building doesn't exist in SMU or has not been mapped");
                }
                else {
                    var x = json.x;
                    var y = json.y;
                    var z = json.z;
                    var bounds = new google.maps.LatLngBounds();
                    marker = new google.maps.Marker({
                        position: new google.maps.LatLng(x, y),
                        title: "Testing!"
                    });

                    map.setCenter(marker.getPosition());
                    marker.setMap(map);

                    google.maps.event.addListener(marker, 'click', function () {
                        if (userId === undefined) {
                            $("#favoritesHeading").hide();
                            $("#favorites_bttn").hide();
                        }
                        $("#marker_form").dialog("open");
                    });

                    $("#favorites_bttn").click(function () {
                        event.preventDefault();
                        $("#marker_form").dialog("close");
                        //$("#favoritesHeading").hide();
                        //$("#favorites_bttn").hide();
                        var favoriteInfo = {userID: userId, building: $("#buildingName").val(), roomNumber: $("#roomNumber").val(), roomName: $("#roomName").val()};
                        $.ajax({
                            type: "POST",
                            datatype: "json",
                            data: favoriteInfo,
                            url: "api/index.php/addFavorite",
                            success: function (result) {
                                var statusJson = JSON.parse(result);
                                if (statusJson.Status === "Failure") {
                                    window.alert("Error");
                                }
                                else {
                                    window.alert("Successfully added class");
                                }
                            }
                        });
                    });

                    $("#getDirections_bttn").click(function () {
                        event.preventDefault();
                        $("#buildingName").val("");
                        $("#roomName").val("");
                        $("#roomNumber").val("");
                        marker.setMap(null);
                        $('#directionsWrapper').show();
                        $("#marker_form").dialog("close");
                        if (navigator.geolocation) {
                            browserSupportFlag = true;
                            navigator.geolocation.getCurrentPosition(function (position) {
                                initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

                                var start = initialLocation;
                                var end = marker.getPosition();
                                var request = {
                                    origin: start,
                                    destination: end,
                                    travelMode: google.maps.TravelMode.WALKING
                                };
                                directionsService.route(request, function (response, status) {
                                    if (status == google.maps.DirectionsStatus.OK) {
                                        directionsDisplay.setDirections(response);
                                    }
                                });
                                $("#cancel_direcs").click(function () {
                                    $('#directionsWrapper').hide();
                                    directionsDisplay.setMap(null);
                                    directionsDisplay.setPanel(null);
                                });

                            }, function () {
                                console.log("geolocation not working");
                            });

                        }

                    });
                }
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

    if (userId === undefined) {
        $('#favorites').hide();
    }

    $('#directionsWrapper').hide();



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
                getFavorites();
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
                    getFavorites();
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
        $('#menuWrapper').show("slide", {direction: "left"}, 300);
        $("#menuWrapper").attr("collapsed", "false");

        // Shrink Map
        toggleMap('shrink');
    } else if (action == 'hide') {
        $('#menuWrapper').hide("slide", {direction: "left"}, 300);
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

$(document).ready(function () {
// Hide submenus
    $("#print").click(function () {
        /*var prtTitle = document.getElementById("dir_title");
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
         WinPrint.close();*/
        //window.alert("Entered");
        var contents = document.getElementById("directions");
        var WinPrint = window.open('', '', 'letf=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
        WinPrint.document.write(contents.innerHTML);
        WinPrint.document.close();
        WinPrint.focus();
        WinPrint.print();
        WinPrint.close();
    });
    $("#emailButton").click(sendEmail);
});
$(document).ready(function () {
    // Hide submenus
    $("#cancel_direcs").click(function () {
        $('#directionsWrapper').hide();
    });
});
function sendEmail(event) {
    event.preventDefault();
    var directions = $("#directions").html();
    var emailMssg = {"to": email,
        "html": directions};
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
    var Id = {"userID": userId};
    $.ajax({
        type: "POST",
        datatype: "json",
        data: Id,
        url: "api/index.php/getFavorites",
        success: function (result) {
            //window.alert("Get favorites");
            var json = JSON.parse(result);
            var html = '';
            $.each(json, function (key, value) {
                if (value.Status === "Failure") {
                    window.alert("Incorrect Password or Email");
                } else if (value.Status === "Success") {

                } else {
                    //console.log(key, value);
                    var coords = value.x + "," + value.y + "," + value.z;
                    var rel = value.buildingName + "," + value.roomNumber;
                    html += '<li><a href="" coords="' + coords + '" rel="' + rel + '">' + value.roomName + '</a></li>';
                }
            });
            $(".favs_list").append(html);
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
            $.each(json, function (key, value) {
                var coords = value.x + "," + value.y + "," + value.z;
                var rel = value.time + "," + value.description;
                html += '<li><a href="" coords="' + coords + '" rel="' + rel + '">' + value.name + '</a></li>';
            });
            $(".events_list").append(html);
        }
    });
}
