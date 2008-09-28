$(document).ready(function() {
	if (!$.browser.msie && !$.browser.opera) {
		window.setTimeout(function() { $('#intro').fadeTo(3000, 0.4); }, 3000);
	
		$("#intro").hover(function() {
			$(this).stop();
			$(this).fadeTo("fast", 1);
		},function() {
			$(this).stop();
			$(this).fadeTo("fast", 0.4);
		});
	};
});