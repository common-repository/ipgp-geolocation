=== Plugin Name ===
Contributors: thedark
Donate link: http://www.ipgp.net
Tags: geolocation, plugin, shortcode, widget, json, javascript, html, redirect, country
Requires at least: 2.7
Tested up to: 6.1.1
Stable tag: 1.0.7
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Want to show different content based on user location, or to redirect certain users to another url ? 

== Description ==

Using IPGP Geolocation, you can redirect users in specific countries to another URL. You can add different redirect rules that applies to different countries and different URLs.

After installation, go to IPGP Geolocation menu in your wordpress administration panel. There you can select which countries you want to apply the redirection for, and set a redirection URL. Add the rule and it is done.

To get the user location, the data is send to IPGP Geolocation API, located at: http://www.ipgp.net/geo/country.php . The API is free for fair usage. There is no private data sent to any external server. The data exchanged between the plugin and the API are: your visitor IP Address without other details about the visitor, and API key to be identified on the server and your website URL. The data sent back by the API contains the visitor country code, country name. 

When the plugin is activated, an API key is requested from the API website. The data sent to the API servers contains the current time, and a public number to make sure that the API key is requested by the plugin. The data sent back contains the API key, a secret key for authentication and the number of queries allowed for fair usage. Fair usage consist in 20,000 queries per month. This number is subject to change based on the load on our servers. 



== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin to the `/wp-content/plugins/` directory or download it directly from your administration panel.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to IPGP Geolocation menu in your wordpress administration panel to add rewrite rules
4. Select the countries from the drop-down list and click add. You can do this for multiple countries.
5. Add the URL where you want visitors from that country to be redirected and click to add rule.
6. Now, visitors coming from that country will be redirected to your specified URL


== Frequently Asked Questions ==

= How the plugin works ? = 

The plugin use the visitor IP Address to get Geolocation data from an external server. 

== Upgrade Notice ==

== Screenshots ==

== Changelog ==

= 1.0.7 =
* Tested and updated

= 1.0.6.4 =
* Fixed warning on redirection rules display

= 1.0.6.2 =
* Tested and updated to work with WordPress 5.7.2

= 1.0.6.2 =
* Tested and updated to work with WordPress 5.4.2

= 1.0.6.1 =
* Tested and updated to work with WordPress 5.3.2

= 1.0.6 =
* Fixed apikey generation issues and cleared a notice appearing

= 1.0.5 =
* Fixed few errors causing notices

= 1.0.4 =
* Fixed issue with url encoding and cleared some notices

= 1.0.3 =
* Fixed textdomain function bug

= 1.0.2 =
* Sorted country list

= 1.0.1 =
* If only one country is set, there is no need to click "add" button
* If no redirection rules are set, then the plugin does nothing on page load

= 1.0 =
* This is the first version of the plugin