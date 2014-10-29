$(document).ready(function() {
	var map;
	function initializeMap() {
		var mapOptions = {
   	    	center: { lat: 32.8406452, lng: -96.7831393},
			zoom: 15
    	};

		map = new google.maps.Map(document.getElementById('mapWrapper'), mapOptions);
	}

	google.maps.event.addDomListener(window, 'load', initializeMap);
        //Registration Popup
        $("#register_form").dialog({
            autoOpen:false, height:300, width:350, modal: true
        });
        $( "#registration").click(function() {
            $("#register_form" ).dialog( "open" );
        });
        $("#cancel").click(function() {
           $("#register_form" ).dialog( "close" ); 
        });
});


//function login(){
//    event.preventDefault();
//    $.ajax({
//            type: "POST",
//            url: "api/Login",
//            data: {
//                email: $("#Email").val(),
//                password: $("#Password").val()
//            },
//            
//    }
//}
//
//function register(){
//    event.preventDefault();
//    $.ajax(
//            {)
//}

//Hide these if user is not logged in
$(function() {
    $('#favorites, #recommended')
        .hide();
});


