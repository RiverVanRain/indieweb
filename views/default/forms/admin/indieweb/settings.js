define(function(require) {
	var $ = require('jquery');
	var elgg = require('elgg');
	
	elgg.provide('elgg.settings');
	
	elgg.settings.indieauth_endpoint = function(elem) {
		if ($(elem).is(':checked')) {
			$('#settings-indieauth-keys').show();
			$('#settings-indieauth-external').hide();
		} else {
			$('#settings-indieauth-keys').hide();
			$('#settings-indieauth-external').show();
		}
	};
});
