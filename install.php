<?php

function ipgpgeo_install() {
	//global $wpdb; 
	
	$check = time() .'482145457101419'. time();
	$url = 'http://www.ipgp.net/geo/request_key.php';
	$wordpressurl = get_site_url();
	$response = wp_remote_post( $url, array(
		'method' => 'POST',
		'timeout' => 7,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'body' => array( 'domain' => $wordpressurl, 'check' => $check ),
		'cookies' => array()
	    )
	);

	if ( is_wp_error( $response ) ) {
	   $error_message = $response->get_error_message();
	   	echo "Something went wrong: $error_message";
	} else {
		$api = json_decode($response[body]);
		delete_option('ipgpgeo_apikey');
		add_option( 'ipgpgeo_apikey', $api, '', 'no' );
	}
		
} 