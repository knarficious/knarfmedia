/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../styles/app.scss';

// start the Stimulus application
//import './bootstrap';
import 'jquery';
import '@popperjs/core/dist/umd/popper.min.js';
// loads the Bootstrap jQuery plugins
//import 'bootstrap-sass/assets/javascripts/bootstrap/transition.js';
//import 'bootstrap-sass/assets/javascripts/bootstrap/alert.js';
//import 'bootstrap-sass/assets/javascripts/bootstrap/collapse.js';
//import 'bootstrap-sass/assets/javascripts/bootstrap/dropdown.js';
//import 'bootstrap-sass/assets/javascripts/bootstrap/modal.js';
//import 'bootstrap-sass/assets/javascripts/bootstrap.min.js';
import 'bootstrap';

$(function() {
	$("#weather-button").click(function(){
		var x = document.getElementById("weather-widget");
		var y = document.getElementById("weather-button");
		if (x.style.display === "none") {
		    x.style.display = "block";
			y.style.display = "none";
		} else {
		    x.style.display = "none";
		}

                    var process = document.getElementById("process");
                    var location = document.getElementById("location");
                    var apiKey = "da6ae541c95e65f571c4274c3588cb5f";
                    var url = "https://api.darksky.net/forecast/";
            
                    navigator.geolocation.getCurrentPosition(success, error);
            
                    function success(position) {
                        var latitude = position.coords.latitude;
                        var longitude = position.coords.longitude;
                        console.log(latitude, longitude);
                        
                // get address by reverse geocoding
    
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if (this.readyState === 4 && this.status === 200) {
                        
                        var myObj = JSON.parse(this.responseText);
                        
                        for (var i = 0; i < myObj.results.length; i++)
                        {
                            var results = myObj.results[i];
                    
                            for (var iter = 0; iter < results.locations.length; iter++)
                            {
                                var area = results.locations[iter];
                    
                                location.innerHTML = area.adminArea5;
                            }
                        }                    
                    };
                };
            
                xmlhttp.open("GET", "https://open.mapquestapi.com/geocoding/v1/reverse?key=4gNCAu0aZMSWHvaUaV56xvD846pUXFix&location=" + latitude + "," + longitude, true );
                xmlhttp.send();
            
    		// get weather forecast
    
                $.getJSON(
                        url + apiKey + "/" + latitude + "," + longitude + "?lang=fr&units=auto&callback=?",
                        function (data) {
                            $("#temp").html("Température ext: " + data.currently.temperature + "°C");
                            $("#currently").html(data.currently.summary);
                            $("#minutely").html(data.minutely.summary);
                        }
                );
            }
    
            function error(err) {
                process.innerHTML = "Impossible de récupérer votre position";
                console.warn(`ERROR(${err.code}): ${err.message}`);
            }
	})


        //process.innerHTML = "Locating...";
    }) ;
    	function myFunction(x) {
//        const image = document.getElementById("weather-image");
//        const content = document.getElementById("weather-content");
      if (x.matches) { // If media query matches
        $("#weather-image").addClass("col-xs-4");
        $("#weather-content").addClass("col-xs-8");
      }
      else {
        $("#weather-image").removeClass("col-xs-4");
        $("#weather-content").removeClass("col-xs-8");  
      }
    };
