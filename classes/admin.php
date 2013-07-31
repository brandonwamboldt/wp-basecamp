<?php

/**
 * This class handles the admin UI for the plugin.
 *
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
class WordPressBasecampAdmin
{
  /**
   * Constructor.
   */
  public function __construct()
  {
    add_action('admin_menu', array($this, 'adminMenu'));
  }

  /**
   * Add the options menu page for this plugin.
   */
  public function adminMenu()
  {
    add_options_page('Basecamp Integration', 'Basecamp Integration', 'manage_options', 'wp-basecamp-options', array($this, 'optionsPage'));
  }

  /**
   * Render the options page.
   */
  public function optionsPage()
  {
    // If data was submitted, save it
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      update_option('wp:basecamp:client_id', $_POST['api_client_id']);
      update_option('wp:basecamp:client_secret', $_POST['api_client_secret']);
      update_option('wp:basecamp:auth_endpoint', $_POST['api_auth_endpoint']);
      update_option('wp:basecamp:token_endpoint', $_POST['api_token_endpoint']);
      update_option('wp:basecamp:organization_id', $_POST['organization_id']);
    }

    // Get the current settings
    $client_id       = get_option('wp:basecamp:client_id');
    $client_secret   = get_option('wp:basecamp:client_secret');
    $auth_endpoint   = get_option('wp:basecamp:auth_endpoint');
    $token_endpoint  = get_option('wp:basecamp:token_endpoint');
    $organization_id = get_option('wp:basecamp:organization_id');
    $redirect_uri    = wp_login_url();

    require __DIR__ . '/../views/admin/options.php';
  }
}
