<?php

function ipgpgeo_geoaction() { 


	$activecountries = get_option('ipgpgeo_georules');
	if(empty($activecountries)) return;	
	
	
	$api = get_option('ipgpgeo_apikey');
	$domain = get_site_url();

	//$check = time() .'482145457101419'. time();
	$url = 'http://www.ipgp.net/geo/country.php';
	$wordpressurl = get_site_url();
	$secret = md5($api->secret);
	$response = wp_remote_post( $url, array(
		'method' => 'POST',
		'timeout' => 5,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'body' => array( 'apikey' => $api->apikey, 'secret' => $secret, 'ipaddress' => $_SERVER['REMOTE_ADDR'], 'domain' => $domain ),
		'cookies' => array()
	    )
	);
	
	

	if ( is_wp_error( $response ) ) {
	   $error_message = $response->get_error_message();
	   	echo "Something went wrong: $error_message";
	} else {
		$result = json_decode($response['body']);
		//print_r($response);
		
		$activecountries = get_option('ipgpgeo_georules');
		foreach($activecountries as $ac) {
			$codes = explode(',', $ac->activecountries);
			foreach($codes as $code) {  
				if($result->countrycode == $code) {					
					wp_redirect( html_entity_decode($ac->redirecturl ));
					exit;				
				} 
			}
		}		
		
	}



}