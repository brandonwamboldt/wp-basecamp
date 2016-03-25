<?php

/**
 * This class handles the actual oAuth authentication and integration features
 * of the plugin.
 *
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
class WordPressBasecampCore {
  /**
   * Constructor.
   */
  public function __construct() {
    add_action( 'init', array( $this, 'register_session' ) );
    add_action( 'login_init', array( $this, 'login_init' ) );
    add_filter( 'login_redirect', array( $this, 'redirect_after_login' ), 20, 3 );

    if ( isset( $_GET['code'] ) ) {
      add_filter( 'authenticate', array( $this, 'doOAuthLogin' ), 30, 3 );
    }
  }

  /**
   * Add the hook that starts the SESSION. SESSION will be used to store redirects.
   */
  function register_session() {
    if ( ( function_exists( 'session_status' ) && PHP_SESSION_ACTIVE !== session_status() ) || ! session_id() ) {
      session_start();
    }
  }

  /**
   * Setup login form or redirect user.
   */
  public function login_init() {
    if (
      // If requesting to not auto-redirect, then add a link to the login form.
      ! apply_filters( 'wp_basecamp_auto_redirect_login', true )
      // If we're coming back from basecamp w/ the code, don't redirect
      || isset( $_GET['code'] )
      // If we're in the middle of logging in traditionally, don't redirect
      || ! empty( $_POST )
    ) {
      // Add the link to the sign-in page
      return add_action( 'login_form', array( $this, 'printLoginLink' ) );
    }

    $args = array_filter( $_GET );

    // wanting to login (and redirect param is set), then send them to basecamp.
    if ( empty( $args ) || ( 1 === count( $args ) && isset( $args['redirect_to'] ) ) ) {
      $this->redirect_to_auth_url();
    }

    // If logging out...
    if ( isset( $args['loggedout'] ) && 'true' === $args['loggedout'] ) {

      // We'll redirect to the requested location, or back to the homepage
      $redirect = isset( $args['redirect_to'] )
        ? esc_url_raw( $args['redirect_to'] )
        : site_url();

      wp_redirect( $redirect );
      exit;
    }

    // Ok, not logging out, but we shouldn't auto-redirect them, so add the login form link.
    add_action( 'login_form', array( $this, 'printLoginLink' ) );
  }

  /**
   * Redirect user back to original location, if we have it.
   */
  public function redirect_after_login( $redirect_to, $requested_redirect_to, $user ) {
    if ( is_a( $user, 'WP_User' ) && isset( $_SESSION['redirect_to'] ) ) {
      $redirect_to = esc_url_raw( $_SESSION['redirect_to'] );
      // Remove chances of residual redirects when logging in.
      unset( $_SESSION['redirect_to'] );
    }

    return $redirect_to;
  }

  /**
   * If we have a response back from basecamp, attempt to log the user in
   * via Basecamp OAuth2.
   */
  public function doOAuthLogin( $user, $username, $password ) {
    // Don't re-authenticate if already authenticated
    if ( is_a( $user, 'WP_User' ) ) {
      return $user;
    }

    $client = $this->get_client();

    // No oauth client? Don't bother.
    if ( ! $client ) {
      return $user;
    }

    // Request an authorization token
    $response = $client->getAccessToken(
      $this->get_token_endpoint(),
      'authorization_code',
      // Token request parameters
      array(
        'code'          => $_GET['code'],
        'redirect_uri'  => wp_login_url(),
        'type'          => 'web_server',
        'client_secret' => get_option( 'wp:basecamp:client_secret' )
      )
    );

    // If the access token failed, try again
    if ($response['code'] == 400) {
      wp_redirect( $this->get_auth_url() );
      exit;
    }

    $organization_id = get_option( 'wp:basecamp:organization_id' );

    // Set access token
    $client->setAccessToken($response['result']['access_token']);

    // Get authorization info
    $response = $client->fetch( WP_BASECAMP_AUTH_INFO_ENDPOINT );

    // Get the user's email
    $user_email = $response['result']['identity']['email_address'];
    $first_name = $response['result']['identity']['first_name'];
    $last_name  = $response['result']['identity']['last_name'];
    $full_name  = $first_name . ' ' . $last_name;

    // The default WP user level for Basecamp users logging in who are NOT members of your Basecamp org.
    // Use add_filter( 'wp_basecamp_default_user_level', '__return_false' ); to disable non-organization users.
    $user_level = apply_filters( 'wp_basecamp_default_user_level', 'subscriber' );

    // The default WP user level for members of your Basecamp org.
    $bc_user_level = apply_filters( 'wp_basecamp_default_organization_user_level', 'contributor' );

    // The default WP user level for admins of your Basecamp org.
    $bc_admin_level = apply_filters( 'wp_basecamp_user_level_organization_admin', 'administrator' );

    $in_organization = false;
    $api_href        = '';
    // Is the user in our organization?
    foreach ($response['result']['accounts'] as $account) {
      if ($account['id'] == $organization_id) {
        $in_organization = true;
        $api_href        = $account['href'];
        $user_level      = $bc_user_level;
        break;
      }
    }

    if ( ! $in_organization && ! $user_level ) {
      return new WP_Error( 'wp_basecamp:not_in_org', 'Sorry, you are not in the correct Basecamp organization.' );
    }

    // Does the user exist
    $user = get_user_by( 'email', $user_email );

    // If we have a user, log them in
    if ( ! empty( $user ) && is_a( $user, 'WP_User' ) ) {
      return $user;
    }

    // Get more info about the user
    $response = $client->fetch( $api_href . '/people/me.json', array(), 'GET', array(
      'User-Agent' => sprintf('%s (%s)', get_bloginfo('name'), get_bloginfo('admin_email') )
    ));

    // Create a new user
    // Setup the minimum required user data
    $userdata = array(
      'user_login'   => wp_slash( $user_email ),
      'user_email'   => wp_slash( $user_email ),
      'user_pass'    => wp_generate_password( 20, true ),
      'first_name'   => $first_name,
      'last_name'    => $last_name,
      'nickname'     => $full_name,
      'display_name' => $full_name,
      'role'         => 1 == $response['result']['admin'] ? $bc_admin_level : $user_level,
    );

    $new_user_id = wp_insert_user( $userdata );

    if ( is_wp_error( $new_user_id ) ) {
      return $new_user_id;
    }

    // Log the user in
    return new WP_User( $new_user_id );
  }

  /**
   * If we're on the WordPress login screen, add a Basecamp login link.
   */
  public function printLoginLink() {
    if ( $client = $this->get_client() ) {

      $login_url = $this->get_auth_url();

      require __DIR__ . '/../views/login/button.php';
    }
  }

  public function get_client() {
    static $client = null;
    if ( null !== $client ) {
      return $client;
    }

    // OAuth2 Parameters
    $client_id     = get_option( 'wp:basecamp:client_id' );
    $client_secret = get_option( 'wp:basecamp:client_secret' );

    // No OAuth parameters? bail.
    if ( ! $client_id || ! $client_secret ) {
      return false;
    }

    // OAuth2 client
    return new OAuth2\Client($client_id, $client_secret);
  }

  public function redirect_to_auth_url() {
    // Ok, we're clear, let's auto-redirect them.
    if ( $client = $this->get_client() ) {
      wp_redirect( $this->get_auth_url() );
      exit;
    }
  }

  public function get_auth_url() {
    if ( isset( $_GET['redirect_to'] ) && $_GET['redirect_to'] ) {
      $_SESSION['redirect_to'] = esc_url( $_GET['redirect_to'] );
    } else {
      unset( $_SESSION['redirect_to'] );
    }

    return $this->get_client()->getAuthenticationUrl(
      $this->get_auth_endpoint(),
      wp_login_url(),
      array('type' => 'web_server')
    );
  }

  public function get_auth_endpoint() {
    return get_option( 'wp:basecamp:auth_endpoint', WP_BASECAMP_AUTH_ENDPOINT );
  }

  public function get_token_endpoint() {
    return get_option( 'wp:basecamp:token_endpoint', WP_BASECAMP_TOKEN_ENDPOINT );
  }

}
