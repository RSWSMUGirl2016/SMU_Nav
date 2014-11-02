$(document).ready(function () {
	var map;
    function initializeMap() {
        var mapOptions = {
            center: {lat: 32.8406452, lng: -96.7831393},
            zoom: 15
        };

        map = new google.maps.Map(document.getElementById('mapWrapper'), mapOptions);

        var markers = [];

          // Create the search box and link it to the UI element.
          var input = /** @type {HTMLInputElement} */(
              document.getElementById('map-input'));
          map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

          var searchBox = new google.maps.places.SearchBox(
            /** @type {HTMLInputElement} */(input));

          // Listen for the event fired when the user selects an item from the
          // pick list. Retrieve the matching places for that item.
          google.maps.event.addListener(searchBox, 'places_changed', function() {
            var places = searchBox.getPlaces();

            if (places.length == 0) {
              return;
            }
            for (var i = 0, marker; marker = markers[i]; i++) {
              marker.setMap(null);
            }

            // For each place, get the icon, place name, and location.
            markers = [];
            var bounds = new google.maps.LatLngBounds();
            for (var i = 0, place; place = places[i]; i++) {
              var image = {
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(25, 25)
              };

              // Create a marker for each place.
              var marker = new google.maps.Marker({
                map: map,
                icon: image,
                title: place.name,
                position: place.geometry.location
              });

              markers.push(marker);

            }

            map.fitBounds(bounds);
          });

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
    $("#login").click(login);
    $("#register").click(register);
});


function login(event) {
    event.preventDefault();
    var loginInfo = {email: $("#SignInEmail").val(), password: $("#SignInPassword").val()};
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
});

$(document).ready(function() {
    // Hide submenus
    $("#cancel_direcs").click(function() {
        $('#directionsWrapper').hide();
    });
});
