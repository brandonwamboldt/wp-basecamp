<?php

use WordPress\Basecamp\OAuth;

/**
 * This class handles the actual oAuth authentication and integration features
 * of the plugin.
 *
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
class WordPressBasecampCore
{
  /**
   * Constructor.
   */
  public function __construct()
  {
    add_action('wp_loaded', array($this, 'doOAuthLogin'));
  }

  /**
   * If we're on the WordPress login, do OAuth2 login via Basecamp.
   */
  public function doOAuthLogin()
  {
    if (!function_exists('login_header')) {
      return;
    }

    // Is the user in our organization?
    $in_organization = false;
    $api_href        = '';

    // OAuth2 Parameters
    $client_id       = get_option('wp:basecamp:client_id');
    $client_secret   = get_option('wp:basecamp:client_secret');
    $auth_endpoint   = get_option('wp:basecamp:auth_endpoint');
    $token_endpoint  = get_option('wp:basecamp:token_endpoint');
    $organization_id = get_option('wp:basecamp:organization_id');
    $redirect_uri    = wp_login_url();

    // OAuth2 client
    $client = new OAuth2\Client($client_id, $client_secret);

    // Check authentication
    if (!isset($_GET['code'])) {
      wp_redirect($client->getAuthenticationUrl($auth_endpoint, $redirect_uri, array('type' => 'web_server')));
      exit;
    } else {
      // Token request parameters
      $params   = array(
        'code'          => $_GET['code'],
        'redirect_uri'  => $redirect_uri,
        'type'          => 'web_server',
        'client_secret' => $client_secret
      );

      // Request an authorization token
      $response = $client->getAccessToken($token_endpoint, 'authorization_code', $params);

      // If the access token failed, try again
      if ($response['code'] == 400) {
        wp_redirect($client->getAuthenticationUrl($auth_endpoint, $redirect_uri, array('type' => 'web_server')));
        exit;
      }

      // Set access token
      $client->setAccessToken($response['result']['access_token']);

      // Get authorization info
      $response = $client->fetch('https://launchpad.37signals.com/authorization.json');

      // Get the user's email
      $user_email = $response['result']['identity']['email_address'];
      $first_name = $response['result']['identity']['first_name'];
      $last_name  = $response['result']['identity']['last_name'];
      $full_name  = $first_name . ' ' . $last_name;

      // Is the user a member of our organization?
      foreach ($response['result']['accounts'] as $account) {
        if ($account['id'] == $organization_id) {
          $in_organization = true;
          $api_href        = $account['href'];
          break;
        }
      }

      if (!$in_organization) {
        wp_redirect(site_url('?not_in_organization=true'));
        exit;
      }

      // Does the user exist
      $user = get_user_by('email', $user_email);

      if ($user) {
        // Log the user in
        wp_set_auth_cookie($user->ID, true);
        wp_redirect(site_url());
        exit;
      } else {
        // Get more info about the user
        $response = $client->fetch($api_href . '/people/me.json', array(), 'GET', array(
          'User-Agent' => sprintf('%s (%s)', get_bloginfo('name'), get_bloginfo('admin_email'))
        ));

        // Create a new user
        $user_id = wp_create_user($user_email, uniqid(), $user_email);

        // Set the user's information
        wp_update_user(array(
          'ID'           => $user_id,
          'first_name'   => $first_name,
          'last_name'    => $last_name,
          'display_name' => $full_name,
          'nickname'     => $full_name,
          'role'         => $response['result']['admin'] == 1 ? 'administrator' : 'subscriber'
        ));

        // Log the user in
        wp_set_auth_cookie($user_id, true);
        wp_redirect(site_url());
        exit;
      }
    }
  }
}
