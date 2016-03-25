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

    // If we're attempting to save our basecamp settings
    if ( ! empty( $_POST ) && isset( $_GET['page'] ) && 'wp-basecamp-options' === $_GET['page'] ) {
      add_action( 'admin_init', array( $this, 'save_fields' ) );
    }
  }

  /**
   * Add the options menu page for this plugin.
   */
  public function adminMenu()
  {
    add_options_page( 'Basecamp Integration', 'Basecamp Integration', 'manage_options', 'wp-basecamp-options', array( $this, 'optionsPage' ) );
  }

  /**
   * Render the options page.
   */
  public function optionsPage()
  {
    // Get the current settings
    $client_id       = get_option( 'wp:basecamp:client_id' );
    $client_secret   = get_option( 'wp:basecamp:client_secret' );
    $auth_endpoint   = get_option( 'wp:basecamp:auth_endpoint', 'https://launchpad.37signals.com/authorization/new' );
    $token_endpoint  = get_option( 'wp:basecamp:token_endpoint', 'https://launchpad.37signals.com/authorization/token' );
    $organization_id = get_option( 'wp:basecamp:organization_id' );
    $redirect_uri    = wp_login_url();

    require __DIR__ . '/../views/admin/options.php';
  }

  /**
   * Save the Basecamp settings
   */
  public function save_fields() {
    // Make sure we passed nonce check
    if ( check_admin_referer( 'save-basecamp-settings', 'save-basecamp-settings' ) ) {
      $fields_to_save = array(
        'wp:basecamp:client_id' => 'api_client_id',
        'wp:basecamp:client_secret' => 'api_client_secret',
        'wp:basecamp:auth_endpoint' => 'api_auth_endpoint',
        'wp:basecamp:token_endpoint' => 'api_token_endpoint',
        'wp:basecamp:organization_id' => 'organization_id',
      );
      foreach ( $fields_to_save as $option_key => $post_key ) {
        // If data was submitted, save it
        update_option( $option_key, sanitize_text_field( $_POST[ $post_key ] ) );
      }
    }
  }

}
