import $ from 'jquery';

const indieauthEndpoint = () => {
	if ($('#enable_indieauth_endpoint').is(':checked')) {
		$('#settings-indieauth-keys').show();
		$('#settings-indieauth-external').hide();
	} else {
		$('#settings-indieauth-keys').hide();
		$('#settings-indieauth-external').show();
	}
};
	
indieauthEndpoint();

$(document).ready(() => {
    $('#enable_indieauth_endpoint').change(indieauthEndpoint);
});
