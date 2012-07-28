
function resetposts() {
	if(confirm('Are you sure you want to turn all of your events back into posts?')) {
		return true;
	} else {
		return false;
	}
	
}

function ventReady() {
	jQuery('#resetpostsbutton').click(resetposts);
}

jQuery(document).ready(ventReady);