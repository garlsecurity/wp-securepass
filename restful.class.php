<?php

/**
 * Description
 *
 * @class           WPSecurePassRestful
 * @author          =undo= <info@wpxtre.me>
 * @copyright       Copyright (C) 2014 wpXtreme Inc. All Rights Reserved.
 * @date            2014-10-15
 * @version         1.0.0
 *
 */
final class WPSecurePassRestful {

  // Default end point api address.
  const API_ENDPOINT = 'https://beta.secure-pass.net/';

  // Entry and version
  const API_VERSION = 'api/v1/';

  // Timeout connection request
  const CONNECTION_TIMEOUT = 45;

  // The User Agent request
  const USER_AGENT = 'wpSecurePass/';

  /**
   * An instance of WPSecurePassOptions class.
   *
   * @var WPSecurePassOptions $_options
   */
  private $_options;

  /**
   * Return a singleton instance of WPSecurePassRestful class
   *
   * @return WPSecurePassRestful
   */
  public static function init()
  {
    static $instance = null;
    if( is_null( $instance ) ) {
      $instance = new self();
    }

    return $instance;
  }

  /**
   * Create an instance of WPSecurePassRestful class
   *
   * @return WPSecurePassRestful
   */
  public function __construct()
  {
    // Get options instance
    $this->_options = WPSecurePassOptions::init();
  }

  /**
   * Do a request to the wpXtreme Server.
   *
   * @param string $route    Optional. Route. Example `/`
   * @param array  $raw_body Optional. Params will be convert in jSON
   * @param string $verb     Optional. Verb of request. Default is 'GET'
   *
   * @return array|bool
   */
  public function request( $route = '', $raw_body = array(), $verb = 'POST' )
  {

    // Stability
    $continue = $this->isRESTFulAvailable();

    // Exit if RESTFul is not properly set.
    if( empty( $continue ) ) {
      return false;
    }

    // Prepare array for request
    $args = array(
      'method'      => $verb,
      'timeout'     => self::CONNECTION_TIMEOUT,
      'redirection' => 5,
      'httpversion' => '1.0',
      'user-agent'  => self::USER_AGENT . WP_SECUREPASS_VERSION,
      'blocking'    => true,
      'headers'     => array(
        'X-SecurePass-App-ID'     => $this->_options->restfulApplicationID(),
        'X-SecurePass-App-Secret' => $this->_options->restfulSecret(),
      ),
      'cookies'     => array(),
      'body'        => $raw_body,
      'compress'    => false,
      'decompress'  => false,
      'sslverify'   => true,
    );

    // Build the endpoint API
    $endpoint = trailingslashit( sprintf( '%s%s', trailingslashit( $this->_options->restfulEndPoint() ) . self::API_VERSION, $route ) );

    // Do request
    $response = wp_remote_request( $endpoint, $args );

    // Dead connection
    if( 200 != wp_remote_retrieve_response_code( $response ) ) {
      return false;
    }

    // Get body
    $body = wp_remote_retrieve_body( $response );

    return json_decode( $body );
  }

  /**
   * Return TRUE if all options RESTFul are setting, otherwise FALSE.
   *
   * @return bool
   */
  public function isRESTFulAvailable()
  {
    // Stability
    $endpoint = $this->_options->restfulEndPoint();
    $app_id   = $this->_options->restfulApplicationID();
    $secret   = $this->_options->restfulSecret();
    $domain   = $this->_options->restfulDomain();

    if( empty( $endpoint ) || empty( $app_id ) || empty( $secret ) || empty( $domain ) ) {
      return false;
    }

    return true;
  }

  // -------------------------------------------------------------------------------------------------------------------
  // API (map)
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * Return the 'ping' to API or FALSE on error.
   *
   * eg:
   *
   *    object(stdClass)#307 (4) {
   *      ["ip_version"]=> int(4)
   *      ["ip"]=> string(13) "198.211.98.85"
   *      ["errorMsg"]=> string(0) ""
   *      ["rc"]=> int(0)
   *    }
   *
   * @return array|bool
   */
  public function ping()
  {
    return $this->request( 'ping' );
  }

  /**
   * Authenticate a given user and return a WP_User if successfully, otherwise FALSE.
   *
   * @param string $username SercurePass user id
   * @param string $password SecurePass OTP + Password
   *
   * @return bool|WP_User
   */
  public function auth( $username, $password )
  {
    // Prepare result
    $user = false;

    // Prepare additional arguments with USERNAME@domain
    $args = array(
      'USERNAME' => sprintf( '%s@%s', $username, $this->_options->restfulDomain() ),
      'SECRET'   => $password
    );

    // Authenticate
    $result = $this->request( 'users/auth', $args );

    if( false === $result ) {
      return $user;
    }

    // Check jSON
    if( is_object( $result ) && empty( $result->errorMsg ) ) {

      // Check authenticate
      if( false !== $result->authenticated ) {

        // Get user
        $user = get_user_by( 'login', $username );
      }
    }

    return $user;
  }
}