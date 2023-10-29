import { Controller } from '@hotwired/stimulus';
import '../js/jsmediatags.min.js';

export default class extends Controller {
	static targets = ['name', 'output'];
    connect() {
	    
	    let media = document.getElementById("media-source");    
	    let source = media.getAttribute("src");
	    let image = document.getElementById("mediatags-image");
	    
	    let mediatags = require("../js/jsmediatags.min.js");
	    
		mediatags.read(location.origin + source, {
		  onSuccess: function(tag) {
		    console.log(tag);
		    const { data, format } = tag.tags.picture;
			let base64String = "";
			for (var i = 0; i < data.length; i++) {
  			base64String += String.fromCharCode(data[i]);
			}
			
			image.setAttribute("src", `data:${data.format};base64,${window.btoa(base64String)}`);
			document.getElementById("mediatags").innerHTML = tag.tags.artist + " - " + tag.tags.title + " | " + tag.tags.year;
		  },
		  onError: function(error) {
		    console.log(':(', error.type, error.info);
		  }
		});		
		
    }
}
