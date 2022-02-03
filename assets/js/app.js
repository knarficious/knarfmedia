/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../styles/app.scss';
import '../styles/admin.scss';
// start the Stimulus application
//import './bootstrap';
import 'jquery';
import '@popperjs/core/dist/umd/popper.min.js';
import 'typeahead.js';
import Tags from 'bootstrap5-tags';
import 'bootstrap';

// Weather forecast widget
//
$("#weather-button").on("click", function(){
	$("#weather-widget").show();
	$("#weather-button").hide();	

	const process = $("#process");

    navigator.geolocation.getCurrentPosition(success, error);

    function success(position) {
    	const location = document.getElementById("location");
    	const apiKey = "da6ae541c95e65f571c4274c3588cb5f";
    	const url = "https://api.darksky.net/forecast/";
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;
        console.log(latitude, longitude);
                        
        // get address by reverse geocoding

        let xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                
                let myObj = JSON.parse(this.responseText);
                
                for (let i = 0; i < myObj.results.length; i++)
                {
                    let results = myObj.results[i];
            
                    for (let iter = 0; iter < results.locations.length; iter++)
                    {
                        let area = results.locations[iter];
            
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

$(function() {
    // Easy Tags Input Component For Bootstrap 5/4 initialization
    // https://www.cssscript.com/tags-input-bootstrap-5/
	Tags.init();
});

// Handling the modal confirmation message.
$(document).on('submit', 'form[data-confirmation]', function (event) {
    var $form = $(this),
        $confirm = $('#confirmationModal');

    if ($confirm.data('result') !== 'yes') {
        //cancel submit event
        event.preventDefault();

        $confirm
            .off('click', '#btnYes')
            .on('click', '#btnYes', function () {
                $confirm.data('result', 'yes');
                $form.find('input[type="submit"]').attr('disabled', 'disabled');
                $form.submit();
            }).modal('show');
    }
});