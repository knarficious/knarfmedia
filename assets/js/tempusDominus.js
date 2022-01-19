import * as tempusDominus from '@eonasdan/tempus-dominus';

$(function() {
	
	// Tempus Dominus datetimepicker initializization
	// https://getdatepicker.com/6/examples/
	new tempusDominus.TempusDominus(document.getElementById('post_publishedAt'), {
		hooks: {
      		inputFormat: (context, date) => {
      			return date.toISOString()
      		}
    	}
	})
})