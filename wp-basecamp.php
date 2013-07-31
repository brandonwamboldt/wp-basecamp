<?php

/*
 * Plugin Name: Basecamp Integration
 * Author:      Brandon Wamboldt
 * Author URI:  http://brandonwamboldt.ca/
 * Version:     1.0
 * Description: Provides integration with 37signals Basecamp (oAuth login and user syncing)
 */

add_action('plugins_loaded', function() {
    require 'classes/admin.php';
    require 'classes/core.php';
    require 'lib/PHP-OAuth2/Client.php';
    require 'lib/PHP-OAuth2/GrantType/IGrantType.php';
    require 'lib/PHP-OAuth2/GrantType/AuthorizationCode.php';

    new WordPressBasecampAdmin(__FILE__);
    new WordPressBasecampCore(__FILE__);
});
