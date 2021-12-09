import '../styles/admin.scss';
//import '@popperjs/core';
import * as tempusDominus from '@eonasdan/tempus-dominus';
import 'typeahead.js';
import Bloodhound from "bloodhound-js";
import Tags from './tags.js';


$(function() {
	
	// Tempus Dominus datetimepicker initializization
	// https://getdatepicker.com/6/examples/
	new tempusDominus.TempusDominus(document.getElementById('post_publishedAt'), {
		hooks: {
      inputFormat: (context, date) => {
      return date.toISOString();
      }
    }

	});

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
