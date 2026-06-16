function reportWindowSize(ghIframe) {
	if(window.innerWidth <= 808) {
		ghIframe.setAttribute("height", "400"); 
	} else {
		ghIframe.setAttribute("height", "200"); 
	}
}
const url = 'https://greenheadreport.com';
//const url = 'https://greenhead.local';
const locations = document.currentScript.dataset.locations;
const size = document.currentScript.dataset.size;
var ghEmbed = document.createElement('iframe');
ghEmbed.setAttribute("frameBorder", "0"); 
if(size == 'small_card') {
	ghEmbed.setAttribute("width", "300"); 
	ghEmbed.setAttribute("height", "160"); 
} else if(size == 'large_card') {
	ghEmbed.setAttribute("width", "300"); 
	ghEmbed.setAttribute("height", "400"); 
} if(size == 'full_width') {
	ghEmbed.setAttribute("width", "100%"); 
	ghEmbed.setAttribute("height", "200"); 
}
ghEmbed.setAttribute('src', url + '/iframe/?embed-location='+encodeURIComponent(locations)+'&size='+size+'&embed-url='+encodeURIComponent(window.location.origin)+'&embed-path='+encodeURIComponent(window.location.pathname));
window.addEventListener('DOMContentLoaded', function() {
	var target = document.getElementById('greenhead-report');
	target.appendChild(ghEmbed);
	if(size == 'full_width') {
		reportWindowSize(ghEmbed);
	}
});
window.onresize = function(event) {
	if(size == 'full_width') {
		reportWindowSize(ghEmbed)
	}
};