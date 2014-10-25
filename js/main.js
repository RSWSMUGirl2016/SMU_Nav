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
});