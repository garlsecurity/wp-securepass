<?php

/**
 * SecurePass main controller.
 *
 * @class           WPSecurePassController
 * @author          =undo= <info@wpxtre.me>
 * @copyright       Copyright (C) 2014 wpXtreme Inc. All Rights Reserved.
 * @date            2014-10-14
 * @version         1.0.0
 * @since           0.2.0
 *
 */
final class WPSecurePassController {

  /**
   * An instance of WPSecurePassOptions class.
   *
   * @var WPSecurePassOptions $_options
   */
  private $_options = null;

  /**
   * Return a singleton instance of WPSecurePassController class
   *
   * @return WPSecurePassController
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
   * Create an instance of WPSecurePassController class
   *
   * @return WPSecurePassController
   */
  public function __construct()
  {

    // Get options instance
    $this->_options = WPSecurePassOptions::init();

    // If master enable
    if( $this->_options->enable() ) {

      // Create the right callback for selected protocol
      $callback = array(
        'radius'  => array( $this, 'radius_authenticate' ),
        'restful' => array( $this, 'restful_authenticate' ),
      );

      // Get selected protocol
      $protocol = $this->_options->protocol();

      // Stability
      if( !isset( $callback[ $protocol ] ) ) {
        return;
      }

      // Filter the user to authenticate.
      add_filter( 'authenticate', $callback[ $protocol ], 10, 3 );
    }
  }

  /**
   * Filter the user to authenticate.
   *
   * If a non-null value is passed, the filter will effectively short-circuit
   * authentication, returning an error instead.
   *
   * This filter is used to authenticate an User by SecurePass RESTfull API.
   *
   * @since WP 2.8.0
   *
   * @param null|WP_User $user     User to authenticate.
   * @param string       $username User login.
   * @param string       $password User password
   */
  public function restful_authenticate( $user, $username, $password )
  {
    // Get instance of RESTFul API class
    $restful = WPSecurePassRestful::init();

    // Check API
    $continue = $restful->ping();

    // Stability
    if( false === $continue ) {
      return $user;
    }

    // Authentocate ?
    if( false === $this->canAuthenticateUser( $username ) ) {
      return $user;
    }

    // Authenticate via RESTful
    $user = $restful->auth( $username, $password );

    if( $user === false ) {
      $user = new WP_Error( 'denied', __( "<strong>ERROR</strong>: Invalid username and password." ) );
      remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
    }

    return $user;

  }


  /**
   * Filter the user to authenticate.
   *
   * If a non-null value is passed, the filter will effectively short-circuit
   * authentication, returning an error instead.
   *
   * This filter is used to authenticate an User by SecurePass RADIUS.
   *
   * @since WP 2.8.0
   *
   * @param null|WP_User $user     User to authenticate.
   * @param string       $username User login.
   * @param string       $password User password
   */
  public function radius_authenticate( $user, $username, $password )
  {
    /*
     * Set the default SecurePass radius server
     * We point to the Swiss datacenter here by default,
     * change radius_host to radius2.secure-pass.net to point to Milan
     * don't forget to change the secret accordingly
     */
    $radius_host   = $this->_options->radiusHost();
    $radius_secret = $this->_options->radiusSecret();

    // Stability
    if( empty( $radius_host ) || empty( $radius_secret ) ) {

      return $user;
    }

    // Authentocate ?
    if( false === $this->canAuthenticateUser( $username ) ) {
      return $user;
    }

    // Get info
    $user   = get_user_by( 'login', $username );
    $radius = new Radius( $radius_host, $radius_secret );

    // Check the password via RADIUS
    if( !$radius->AccessRequest( $username, $password ) ) {
      $user = new WP_Error( 'denied', __( "<strong>ERROR</strong>: Invalid username and password." ) );
      remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
    }

    return $user;
  }

  /**
   * Return TRUE if an user must be authenticate by SecurePass.
   * If no roles or users id are set in options this method return FALSE.
   *
   * @param string $username The user login.
   *
   * @return bool
   */
  public function canAuthenticateUser( $username )
  {
    // Get restrict roles
    $roles = $this->_options->listUserRoles();

    // Get restrict users id
    $users_id = $this->_options->listUsers();

    // If no options restrict found return FALSE
    if( empty( $roles ) && empty( $users_id ) ) {
      return false;
    }

    // Prepare found
    $found = false;

    // Get user
    $user = get_user_by( 'login', $username );

    // Stability
    if( !is_object( $user ) ) {
      return false;
    }

    // Check for roles
    if( !empty( $roles ) ) {
      foreach( $roles as $role ) {
        if( in_array( $role, $user->roles ) ) {
          $found = true;
          break;
        }
      }
    }

    // Find this user in roles
    if( true === $found ) {

      // White or Black list ?
      return $this->_options->isWhiteList();
    }

    // Search in the users id
    if( !empty( $users_id ) ) {
      $found = in_array( $user->ID, $users_id );
    }

    // Find in users id
    if( true === $found ) {

      // White or Black list ?
      return $this->_options->isWhiteList();
    }

    // If the user is not found, then return the right permission in accordion with White or Black list
    return ! $this->_options->isWhiteList();

  }

}