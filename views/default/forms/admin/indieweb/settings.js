define(function(require) {
	var $ = require('jquery');
	
	indieauthEndpoint = function() {
		if ($('#enable_indieauth_endpoint').is(':checked')) {
			$('#settings-indieauth-keys').show();
			$('#settings-indieauth-external').hide();
		} else {
			$('#settings-indieauth-keys').hide();
			$('#settings-indieauth-external').show();
		}
	};
	
	indieauthEndpoint();
});
