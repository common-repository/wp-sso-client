function callback_sso_server($data) {
	console.log($data);
	if (!$data.data.is_login) {
		return;
	}
	jQuery.ajax({
		url: wp_sso_client_urls.admin_ajax_url,
		method: 'POST',
		dataType: 'json',
		cache: false,
		data: {
			action: 'sso_token',
			data:   $data.data
		}
	})
	.done(function($rs) {
    
  })
  .fail(function() {
    
  })
  .always(function($rs) {
    if ($rs.reload == 1) {
			window.location.reload(true);
    } else if($rs.redirect != undefined) {
    	window.location
    }
  });
}