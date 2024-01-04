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
import '../bootstrap';
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

    navigator.geolocation.getCurrentPosition(success, error);

    function success(position) {
    	const location = document.getElementById("location");
//    	const apiKey = process.env.WEATHER_APIKEY;
    	const url = "https://api.weatherapi.com/v1/current.json";
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;
        console.log(latitude, longitude);
                        
        // get address by reverse geocoding

/*        let xmlhttp = new XMLHttpRequest();
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
    
        xmlhttp.open("GET", "https://mapquestapi.com/geocoding/v1/reverse?key=NEZuMk8AILPO3kN16urrDcHrfW0cddnE&location=" + latitude + "," + longitude, true );
        xmlhttp.send();*/
    
		// get weather forecast

        $.getJSON(
                url + "?key=e14352fca5204b7bbc5212630232704&q=" + latitude + "," + longitude + "&lang=fr",
                function (data) {
                    $("#temp").html("Température ext: " + data.current.temp_c + "°C");
                    $("#currently").html(data.current.condition.text);
                    $("#condition_icon").attr("src", data.current.condition.icon);
                    location.innerHTML = data.location.name + ", " + data.location.country;
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
	Tags.init("select", {allowClear: true, allowNew: true});
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