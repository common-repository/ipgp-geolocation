<?php
/*
Plugin Name: IPGP Geolocation
Plugin URI: http://www.ipgp.net
Description: Show different content based on user location, or to redirect certain users to another url 
Author: Lucian Apostol
Version: 1.0.7
Author URI: http://www.ipgp.net
*/

defined( 'ABSPATH' ) or die( 'You need wordpress to load this file!' );

include(plugin_dir_path(__FILE__) . 'lib/georule.php');
include(plugin_dir_path(__FILE__) . 'install.php');
include(plugin_dir_path(__FILE__) . 'engine.php');

/* Register hooks and filters */

add_action('admin_menu', 'ipgpgeo_mainmenu');
add_action('admin_init', 'ipgpgeo_actions');
add_action( 'plugins_loaded', 'ipgpgeo_textdomain' );
add_action('wp_print_scripts', 'ipgpgeo_loadjs');
add_action( 'wp', 'ipgpgeo_geoaction' );

register_activation_hook(__FILE__,'ipgpgeo_install');

/* End registering hooks and filters */

function ipgpgeo_textdomain() {
  load_plugin_textdomain('ipgpgeo-textdomain', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}

function ipgpgeo_mainmenu() {
	$icon_url = 'dashicons-location';
	$position = null;
	add_menu_page( 'IPGP Geolocation', 'IPGP Geolocation', 'publish_pages', 'ipgpgeo_topmenu', 'ipgpgeo_topmenupage', $icon_url, $position );	
}


function ipgpgeo_topmenupage() {
	
	$countries = ipgpgeo_countrylist();
	//$activecountries = json_decode(get_option('ipgpgeo_georules'));
	$activecountries = get_option('ipgpgeo_georules');
	//print_r($activecountries);
	
	$api = get_option('ipgpgeo_apikey');
	//print_r($api);
		
?>

	<div class="wrap">
		<h2>IPGP Geolocation</h2>
		<br />
		<?php
			if(!$api || !is_object($api) || !$api->apikey) {
				echo 'There was a problem creating your API key, please try to deactivate and activate plugin again, if it is still not working <a href="https://www.ipgp.net/about/">contact us</a> for more info';	
			}		
		?>
		<br />
		<h3>Add a new redirection rule</h3>
		<form method="post" action="">
			<?php wp_nonce_field( 'ipgpgeo_addrule' ); ?>
			<input type="hidden" name="ipgpgeo_addrule" value="yes" />
			<br />Select countries to redirect:<br />
			<select name="ipgpgeo_allcountries" id="ipgpgeo_allcountries">
				<?php 	
				foreach($countries as $code => $country) {
					echo '<option value="'. $code .'">'. $country .'</option>
					';				
				}
				?>			
			</select>
			<input type="hidden" value="" name="ipgpgeo_activecountries" id="ipgpgeo_activecountries" />
			<a id="ipgpgeo_addcountry" class="button button-primary" href="javascript:;" >Add >></a>	<br /><br />
			<div id="ipgpgeo_activecountriesdiv"></div>		
			<br />
			
			Redirect selected countries to this URL: 
			<br />
			<input type="text" name="ipgpgeo_redirecturl" id="ipgpgeo_redirecturl" />
			
		

		 	<?php submit_button('Add Redirection Rule'); ?>
		</form>
		<br /><br />
		<h3>Redirection rules</h3>
		<br />
		<table class="widefat fixed" > 
		<thead>
			<tr>
				<th class="check-column"></th>
				<th>Countries</th>
				<th>Redirection URL</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="check-column"></th>
				<th>Countries</th>
				<th>Redirection URL</th>
				<th>Actions</th>
			</tr>
		</tfoot>		
		<tbody>
		<?php
		if(isset($activecountries) && is_array($activecountries)) foreach($activecountries as $ac) {
			$codes = explode(',', $ac->activecountries);
			echo '<tr>';
			echo '<td></td>';		
			echo '<td>';
			foreach($codes as $code) { 
				echo $countries[$code] .', '; 
			}
			echo '</td>';
			echo '<td>'. $ac->redirecturl .'</td>';
			echo '<td><form onsubmit="return confirm(\'Are you sure you want to delete this rule?\');" action="" method="post"><input type="hidden" name="ipgpgeo_deleterule" value="'. $ac->activecountries .'" /><input type="submit" value="Delete" class="button-primary" /></form></td>';
			echo '</tr>';
		
		}
		
		
		?>
		</tbody>
		</table>
		
	</div>
	
	
	
<?php


}



function ipgpgeo_actions() {
	
	$addrule = filter_input(INPUT_POST, 'ipgpgeo_addrule', FILTER_SANITIZE_SPECIAL_CHARS);
	$redirecturl = filter_input(INPUT_POST, 'ipgpgeo_redirecturl', FILTER_SANITIZE_SPECIAL_CHARS);
	$activecountries = filter_input(INPUT_POST, 'ipgpgeo_activecountries', FILTER_SANITIZE_SPECIAL_CHARS);
	
	$allcountries = filter_input(INPUT_POST, 'ipgpgeo_allcountries', FILTER_SANITIZE_SPECIAL_CHARS);
	if(!$activecountries) $activecountries = $allcountries;
	
	$deleterule = filter_input(INPUT_POST, 'ipgpgeo_deleterule', FILTER_SANITIZE_SPECIAL_CHARS);
		
	
	
	if($addrule && $redirecturl && $activecountries) {
		check_admin_referer( 'ipgpgeo_addrule' );
		
		$georule = new Georule($activecountries,$redirecturl);
	
		$georules = get_option('ipgpgeo_georules');
		if($georules) {
			//$newgeorules = json_decode($georules);	
			$newgeorules = $georules;			
		}
		else {
			$newgeorules = array();		
		}
		
		
		$newgeorules[] = $georule;
		
		delete_option('ipgpgeo_georules');
		//add_option( 'ipgpgeo_georules', json_encode($newgeorules), '', 'no' );
		add_option( 'ipgpgeo_georules', $newgeorules, '', 'no' );
		
		
		
	
	}
	
	//Delete a rule
	if($deleterule) {
		
		$georules = get_option('ipgpgeo_georules');
		if($georules) {
			//$newgeorules = json_decode($georules);	
			foreach($georules as $key => $georule) {
				if($georule->activecountries == $deleterule)	{
					unset($georules[$key]);				
				}		
			}		
			delete_option('ipgpgeo_georules');
			add_option( 'ipgpgeo_georules', $georules, '', 'no' );
		}
		else {
			//false	
		}			

	
	}


}


//Load javascript files for both backend and frontend
function ipgpgeo_loadjs() {
	
      //Load javascript file
		wp_enqueue_script( "ipgpgeo_mainjs", plugin_dir_url( __FILE__ ) . 'ipgp-geolocation.js', array( 'jquery' ) );
     
}



function ipgpgeo_countrylist() {

	$countries = array( 
		'RW' => 'Rwanda',
		'SO' => 'Somalia',
		'YE' => 'Yemen',
		'IQ' => 'Iraq',
		'SA' => 'Saudi Arabia',
		'IR' => 'Iran',
		'CY' => 'Cyprus',
		'TZ' => 'Tanzania',
		'SY' => 'Syria',
		'AM' => 'Armenia',
		'KE' => 'Kenya',
		'CD' => 'Congo',
		'DJ' => 'Djibouti',
		'UG' => 'Uganda',
		'CF' => 'Central African Republic',
		'SC' => 'Seychelles',
		'JO' => 'Hashemite Kingdom of Jordan',
		'LB' => 'Lebanon',
		'KW' => 'Kuwait',
		'OM' => 'Oman',
		'QA' => 'Qatar',
		'BH' => 'Bahrain',
		'AE' => 'United Arab Emirates',
		'IL' => 'Israel',
		'TR' => 'Turkey',
		'ET' => 'Ethiopia',
		'ER' => 'Eritrea',
		'EG' => 'Egypt',
		'SD' => 'Sudan',
		'GR' => 'Greece',
		'BI' => 'Burundi',
		'EE' => 'Estonia',
		'LV' => 'Latvia',
		'AZ' => 'Azerbaijan',
		'LT' => 'Republic of Lithuania',
		'SJ' => 'Svalbard and Jan Mayen',
		'GE' => 'Georgia',
		'MD' => 'Republic of Moldova',
		'BY' => 'Belarus',
		'FI' => 'Finland',
		'AX' => 'Åland',
		'UA' => 'Ukraine',
		'MK' => 'Macedonia',
		'HU' => 'Hungary',
		'BG' => 'Bulgaria',
		'AL' => 'Albania',
		'PL' => 'Poland',
		'RO' => 'Romania',
		'XK' => 'Kosovo',
		'ZW' => 'Zimbabwe',
		'ZM' => 'Zambia',
		'KM' => 'Comoros',
		'MW' => 'Malawi',
		'LS' => 'Lesotho',
		'BW' => 'Botswana',
		'MU' => 'Mauritius',
		'SZ' => 'Swaziland',
		'RE' => 'Réunion',
		'ZA' => 'South Africa',
		'YT' => 'Mayotte',
		'MZ' => 'Mozambique',
		'MG' => 'Madagascar',
		'AF' => 'Afghanistan',
		'PK' => 'Pakistan',
		'BD' => 'Bangladesh',
		'TM' => 'Turkmenistan',
		'TJ' => 'Tajikistan',
		'LK' => 'Sri Lanka',
		'BT' => 'Bhutan',
		'IN' => 'India',
		'MV' => 'Maldives',
		'IO' => 'British Indian Ocean Territory',
		'NP' => 'Nepal',
		'MM' => 'Myanmar [Burma]',
		'UZ' => 'Uzbekistan',
		'KZ' => 'Kazakhstan',
		'KG' => 'Kyrgyzstan',
		'TF' => 'French Southern Territories',
		'CC' => 'Cocos [Keeling] Islands',
		'PW' => 'Palau',
		'VN' => 'Vietnam',
		'TH' => 'Thailand',
		'ID' => 'Indonesia',
		'LA' => 'Laos',
		'TW' => 'Taiwan',
		'PH' => 'Philippines',
		'MY' => 'Malaysia',
		'CN' => 'China',
		'HK' => 'Hong Kong',
		'BN' => 'Brunei',
		'MO' => 'Macao',
		'KH' => 'Cambodia',
		'KR' => 'Republic of Korea',
		'JP' => 'Japan',
		'KP' => 'North Korea',
		'SG' => 'Singapore',
		'CK' => 'Cook Islands',
		'TL' => 'East Timor',
		'RU' => 'Russia',
		'MN' => 'Mongolia',
		'AU' => 'Australia',
		'CX' => 'Christmas Island',
		'MH' => 'Marshall Islands',
		'FM' => 'Federated States of Micronesia',
		'PG' => 'Papua New Guinea',
		'SB' => 'Solomon Islands',
		'TV' => 'Tuvalu',
		'NR' => 'Nauru',
		'VU' => 'Vanuatu',
		'NC' => 'New Caledonia',
		'NF' => 'Norfolk Island',
		'NZ' => 'New Zealand',
		'FJ' => 'Fiji',
		'LY' => 'Libya',
		'CM' => 'Cameroon',
		'SN' => 'Senegal',
		'CG' => 'Republic of the Congo',
		'PT' => 'Portugal',
		'LR' => 'Liberia',
		'CI' => 'Ivory Coast',
		'GH' => 'Ghana',
		'GQ' => 'Equatorial Guinea',
		'NG' => 'Nigeria',
		'BF' => 'Burkina Faso',
		'TG' => 'Togo',
		'GW' => 'Guinea-Bissau',
		'MR' => 'Mauritania',
		'BJ' => 'Benin',
		'GA' => 'Gabon',
		'SL' => 'Sierra Leone',
		'ST' => 'São Tomé and Príncipe',
		'GI' => 'Gibraltar',
		'GM' => 'Gambia',
		'GN' => 'Guinea',
		'TD' => 'Chad',
		'NE' => 'Niger',
		'ML' => 'Mali',
		'TN' => 'Tunisia',
		'ES' => 'Spain',
		'MA' => 'Morocco',
		'MT' => 'Malta',
		'DZ' => 'Algeria',
		'FO' => 'Faroe Islands',
		'DK' => 'Denmark',
		'IS' => 'Iceland',
		'GB' => 'United Kingdom',
		'CH' => 'Switzerland',
		'SE' => 'Sweden',
		'NL' => 'Netherlands',
		'AT' => 'Austria',
		'BE' => 'Belgium',
		'DE' => 'Germany',
		'LU' => 'Luxembourg',
		'IE' => 'Ireland',
		'MC' => 'Monaco',
		'FR' => 'France',
		'AD' => 'Andorra',
		'LI' => 'Liechtenstein',
		'JE' => 'Jersey',
		'IM' => 'Isle of Man',
		'GG' => 'Guernsey',
		'SK' => 'Slovak Republic',
		'CZ' => 'Czech Republic',
		'NO' => 'Norway',
		'VA' => 'Vatican City',
		'SM' => 'San Marino',
		'IT' => 'Italy',
		'SI' => 'Slovenia',
		'ME' => 'Montenegro',
		'HR' => 'Croatia',
		'BA' => 'Bosnia and Herzegovina',
		'AO' => 'Angola',
		'NA' => 'Namibia',
		'SH' => 'Saint Helena',
		'BB' => 'Barbados',
		'CV' => 'Cape Verde',
		'GY' => 'Guyana',
		'GF' => 'French Guiana',
		'SR' => 'Suriname',
		'PM' => 'Saint Pierre and Miquelon',
		'GL' => 'Greenland',
		'PY' => 'Paraguay',
		'UY' => 'Uruguay',
		'BR' => 'Brazil',
		'FK' => 'Falkland Islands',
		'GS' => 'South Georgia and the South Sandwich Islands',
		'JM' => 'Jamaica',
		'DO' => 'Dominican Republic',
		'CU' => 'Cuba',
		'MQ' => 'Martinique',
		'BS' => 'Bahamas',
		'BM' => 'Bermuda',
		'AI' => 'Anguilla',
		'TT' => 'Trinidad and Tobago',
		'KN' => 'Saint Kitts and Nevis',
		'DM' => 'Dominica',
		'AG' => 'Antigua and Barbuda',
		'LC' => 'Saint Lucia',
		'TC' => 'Turks and Caicos Islands',
		'AW' => 'Aruba',
		'VG' => 'British Virgin Islands',
		'VC' => 'Saint Vincent and the Grenadines',
		'MS' => 'Montserrat',
		'MF' => 'Saint Martin',
		'BL' => 'Saint-Barthélemy',
		'GP' => 'Guadeloupe',
		'GD' => 'Grenada',
		'KY' => 'Cayman Islands',
		'BZ' => 'Belize',
		'SV' => 'El Salvador',
		'GT' => 'Guatemala',
		'HN' => 'Honduras',
		'NI' => 'Nicaragua',
		'CR' => 'Costa Rica',
		'VE' => 'Venezuela',
		'EC' => 'Ecuador',
		'CO' => 'Colombia',
		'PA' => 'Panama',
		'HT' => 'Haiti',
		'AR' => 'Argentina',
		'CL' => 'Chile',
		'BO' => 'Bolivia',
		'PE' => 'Peru',
		'MX' => 'Mexico',
		'PF' => 'French Polynesia',
		'PN' => 'Pitcairn Islands',
		'KI' => 'Kiribati',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'WF' => 'Wallis and Futuna',
		'WS' => 'Samoa',
		'NU' => 'Niue',
		'MP' => 'Northern Mariana Islands',
		'GU' => 'Guam',
		'PR' => 'Puerto Rico',
		'VI' => 'U.S. Virgin Islands',
		'UM' => 'U.S. Minor Outlying Islands',
		'AS' => 'American Samoa',
		'CA' => 'Canada',
		'US' => 'United States',
		'PS' => 'Palestine',
		'RS' => 'Serbia',
		'AQ' => 'Antarctica',
		'SX' => 'Sint Maarten',
		'CW' => 'Curaçao',
		'BQ' => 'Bonaire, Sint Eustatius, and Saba',
		'SS' => 'South Sudan',	
	);
	
	asort($countries);

	return $countries;



}