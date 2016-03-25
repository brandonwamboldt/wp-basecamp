<?php

/*
 * Plugin Name: Basecamp Integration
 * Author:      Brandon Wamboldt, Justin Sternberg
 * Author URI:  http://brandonwamboldt.ca/
 * Version:     1.0
 * Description: Provides integration with 37signals Basecamp (oAuth login and user syncing)
 */

define( 'WP_BASECAMP_AUTH_ENDPOINT', 'https://launchpad.37signals.com/authorization/new' );
define( 'WP_BASECAMP_TOKEN_ENDPOINT', 'https://launchpad.37signals.com/authorization/token' );
define( 'WP_BASECAMP_AUTH_INFO_ENDPOINT', 'https://launchpad.37signals.com/authorization.json' );

add_action('plugins_loaded', function() {
    require 'classes/admin.php';
    require 'classes/core.php';
    require 'lib/PHP-OAuth2/Client.php';
    require 'lib/PHP-OAuth2/GrantType/IGrantType.php';
    require 'lib/PHP-OAuth2/GrantType/AuthorizationCode.php';

    new WordPressBasecampAdmin(__FILE__);
    new WordPressBasecampCore(__FILE__);
});
