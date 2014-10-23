<?php

/**
 * Manage the model and view of Securepass options.
 *
 * @class           WPSecurePassOptions
 * @author          =undo= <info@wpxtre.me>
 * @copyright       Copyright (C) 2014 wpXtreme Inc. All Rights Reserved.
 * @date            2014-10-14
 * @version         1.0.0
 * @since           0.2.0
 *
 */
final class WPSecurePassOptions {

  // WordPress capability to display the menu
  const MENU_CAPABILITY = 'manage_options';

  // Options group
  const OPTION_GROUP = 'wp_securepass_option_group';

  // Options name
  const OPTION_NAME = 'wp_securepass_options';

  // Menu id / hook suffix
  const MENU_ID = 'wp-securepass';

  // Fields
  const FIELD_SECUREPASS_ENABLE = 'wpsp_field_securepass_enable';
  const FIELD_PROTOCOL          = 'wpsp_field_protocol';
  const FIELD_RADIUS_HOST       = 'wpsp_field_radius_host';
  const FIELD_RADIUS_SECRET     = 'wpsp_field_radius_secret';

  const FIELD_RESTFUL_ENDPOINT = 'wpsp_field_restful_enpoint';
  const FIELD_RESTFUL_APPID    = 'wpsp_field_restful_appid';
  const FIELD_RESTFUL_SECRET   = 'wpsp_field_restful_secret';
  const FIELD_RESTFUL_DOMAIN   = 'wpsp_field_restful_domain';

  const FIELD_LIST            = 'wpsp_field_list';
  const FIELD_LIST_USER_ROLES = 'wpsp_field_list_user_roles';
  const FIELD_LIST_USERS      = 'wpsp_field_list_users';

  /**
   * The (database) options.
   *
   * @var bool $_options
   */
  private $_options = false;

  /**
   * Return a singleton instance of WPSecurePassOptions class
   *
   * @return WPSecurePassOptions
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
   * Create an instance of WPSecurePassOptions class
   *
   * @return WPSecurePassOptions
   */
  public function __construct()
  {
    // Filter a specific option before its value is (maybe) serialized and updated.
    add_filter( 'pre_update_option_' . self::OPTION_NAME, array( $this, 'pre_update_option' ), 10, 2 );

    // Fires before the administration menu loads in the admin.
    add_action( 'admin_menu', array( $this, 'admin_menu' ) );

    // Fires as an admin screen or script is being initialized.
    add_action( 'admin_init', array( $this, 'admin_init' ) );

    // Fires when styles are printed for a specific admin page based on $hook_suffix.
    add_action( 'admin_print_styles-settings_page_' . self::MENU_ID, array( $this, 'admin_print_styles' ) );

    // Fires in <head> for a specific admin page based on $hook_suffix.
    add_action( 'admin_head-settings_page_' . self::MENU_ID, array( $this, 'admin_head' ) );

    // Get options
    $this->_options = get_option( self::OPTION_NAME );

    // If options doesn't exists
    if( empty( $this->_options ) ) {
      update_option( self::OPTION_NAME, $this->_defaults() );
    }

    // Check if enable
    if( $this->enable() ) {

      // Print admin screen notices.
      add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

  }

  /**
   * Filter a specific option before its value is (maybe) serialized and updated.
   *
   * The dynamic portion of the hook name, $option, refers to the option name.
   *
   * @since WP 2.6.0
   *
   * @param mixed $value     The new, unserialized option value.
   * @param mixed $old_value The old option value.
   */
  public function pre_update_option( $value, $old_value )
  {
    if( isset( $_POST[ 'wp-securepass-reset-to-default' ] ) ) {
      $value = $this->_defaults();
    }
    else {
      // Remove any duplicate for users list id.
      if( isset( $value[ self::FIELD_LIST_USERS ] ) && !empty( $value[ self::FIELD_LIST_USERS ] ) ) {
        $value[ self::FIELD_LIST_USERS ] = array_filter( array_unique( $value[ self::FIELD_LIST_USERS ] ) );
      }
    }

    return $value;
  }

  /**
   * Fires when styles are printed for a specific admin page based on $hook_suffix.
   */
  public function admin_print_styles()
  {
    // Load your own styles
    wp_enqueue_style( 'wpsp-options', WP_SECUREPASS_ASSETS_CSS_URL . 'wpsp-options.css', false, WP_SECUREPASS_VERSION );

    // Used to show/hide right child
    $h3_index    = ( 'radius' == $this->protocol() ) ? 10 : 8;
    $table_index = ( 'radius' == $this->protocol() ) ? 11 : 9;

    // Print custom inline style to shoe/hide the selected option (protocol) view
    ?>
    <style type="text/css">
      form#wp-securepass-options h3:nth-child(<?php echo $h3_index ?>),
      form#wp-securepass-options table:nth-child(<?php echo $table_index ?>)
      {
        display : none;
      }
    </style>
  <?php
  }

  /**
   * Fires in <head> for a specific admin page based on $hook_suffix.
   */
  public function admin_head()
  {
    // Load your own scripts
    wp_enqueue_script( 'wpsp-options', WP_SECUREPASS_ASSETS_JAVASCRIPT_URL . 'wpsp-options.js', array( 'jquery' ), WP_SECUREPASS_VERSION, true );
  }

  // -------------------------------------------------------------------------------------------------------------------
  // COMODITY METHODS TO GET OPTIONS
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * Return TRUE if SecurePass ois enable, FALSE otherwise.
   *
   * @return bool
   */
  public function enable()
  {
    return isset( $this->_options[ self::FIELD_SECUREPASS_ENABLE ] ) ? ( 'on' == $this->_options[ self::FIELD_SECUREPASS_ENABLE ] ) : false;
  }

  /**
   * Return the protocol: 'radius' or 'restful'
   *
   * @return string
   */
  public function protocol()
  {
    return $this->_options[ self::FIELD_PROTOCOL ];
  }

  /**
   * Return the Radius Host.
   *
   * @return string
   */
  public function radiusHost()
  {
    return $this->_options[ self::FIELD_RADIUS_HOST ];
  }

  /**
   * Return the Radius Secret.
   *
   * @return string
   */
  public function radiusSecret()
  {
    return $this->_options[ self::FIELD_RADIUS_SECRET ];
  }

  /**
   * Return the RESTFul Endpoint.
   *
   * @return string
   */
  public function restfulEndPoint()
  {
    return $this->_options[ self::FIELD_RESTFUL_ENDPOINT ];
  }

  /**
   * Return the RESTFul Application ID.
   *
   * @return string
   */
  public function restfulApplicationID()
  {
    return $this->_options[ self::FIELD_RESTFUL_APPID ];
  }

  /**
   * Return the RESTFul Secret.
   *
   * @return string
   */
  public function restfulSecret()
  {
    return $this->_options[ self::FIELD_RESTFUL_SECRET ];
  }

  /**
   * Return the RESTFul Domain.
   *
   * @return string
   */
  public function restfulDomain()
  {
    return $this->_options[ self::FIELD_RESTFUL_DOMAIN ];
  }

  /**
   * Return TRUE if the list of user roles and users must be used as white list instead black list.
   *
   * @return bool
   */
  public function isWhiteList()
  {
    return isset( $this->_options[ self::FIELD_LIST ] ) ? ( 'white' == $this->_options[ self::FIELD_LIST ] ) : false;
  }

  /**
   * Return the list of user roles.
   *
   * @return string
   */
  public function listUserRoles()
  {
    return isset( $this->_options[ self::FIELD_LIST_USER_ROLES ] ) ? $this->_options[ self::FIELD_LIST_USER_ROLES ] : array();
  }

  /**
   * Return the list of users id select in lists.
   *
   * @return array
   */
  public function listUsers()
  {
    return isset( $this->_options[ self::FIELD_LIST_USERS ] ) ? $this->_options[ self::FIELD_LIST_USERS ] : array();
  }

  // -------------------------------------------------------------------------------------------------------------------
  // OTHERS HOOKS
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * Print admin screen notices.
   *
   * @since WP 3.1.0
   */
  public function admin_notices()
  {
    // Check option for radius
    if( 'radius' == $this->_options[ self::FIELD_PROTOCOL ] ) {
      if( empty( $this->_options[ self::FIELD_RADIUS_HOST ] ) ) {
        echo '<div class="update-nag">' . __( 'Warning! Radius Host is empty' ) . '</div>';
      }

      if( empty( $this->_options[ self::FIELD_RADIUS_SECRET ] ) ) {
        echo '<div class="update-nag">' . __( 'Warning! Radius Secret is empty' ) . '</div>';
      }
    }

    // TODO Check option for RESTFul
  }

  /**
   * Return a key value pairs array with default securepass options.
   *
   * @return array
   */
  private function _defaults()
  {
    $defaults = array(
      self::FIELD_SECUREPASS_ENABLE => 'off',
      self::FIELD_PROTOCOL          => 'radius',
      self::FIELD_RADIUS_HOST       => 'radius1.secure-pass.net',
      self::FIELD_RADIUS_SECRET     => 'CHANGEME',
      self::FIELD_RESTFUL_ENDPOINT  => WPSecurePassRestful::API_ENDPOINT,
      self::FIELD_RESTFUL_APPID     => '',
      self::FIELD_RESTFUL_SECRET    => '',
      self::FIELD_RESTFUL_DOMAIN    => '',
      self::FIELD_LIST              => 'white',
      self::FIELD_LIST_USER_ROLES   => array(),
      self::FIELD_LIST_USERS        => array(),
    );

    return $defaults;
  }

  /**
   * Fires before the administration menu loads in the admin.
   *
   * @since WP 1.5.0
   *
   * @param string $context Empty context.
   */
  public function admin_menu()
  {
    add_options_page( 'WP-SecurePass', 'WP-SecurePass', self::MENU_CAPABILITY, self::MENU_ID, array( $this, 'display' ) );
  }

  /**
   * Fires as an admin screen or script is being initialized.
   *
   * Note, this does not just run on user-facing admin screens.
   * It runs on admin-ajax.php and admin-post.php as well.
   *
   * This is roughly analgous to the more general 'init' hook, which fires earlier.
   *
   * @since WP 2.5.0
   */
  public function admin_init()
  {
    // Register settings
    register_setting( self::OPTION_GROUP, self::OPTION_NAME );

    // SECTION - GENERAL
    add_settings_section(
      'wpsp_general_section',
      __( 'General', WP_SECUREPASS_TEXTDOMAIN ),
      //array( $this, 'wpsp_general_section' ),
      false,
      self::OPTION_GROUP
    );

    // ENABLE
    add_settings_field(
      self::FIELD_SECUREPASS_ENABLE,
      __( 'Enable', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'input_checkbox' ),
      self::OPTION_GROUP,
      'wpsp_general_section',
      array( self::FIELD_SECUREPASS_ENABLE )
    );

    // PROTOCOL
    add_settings_field(
      self::FIELD_PROTOCOL,
      __( 'Protocol', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'select' ),
      self::OPTION_GROUP,
      'wpsp_general_section',
      array(
        self::FIELD_PROTOCOL,
        array(
          'radius'  => __( 'RADIUS' ),
          'restful' => __( 'RESTful APIs' )
        )
      )
    );

    // SECTION - RADIUS
    add_settings_section(
      'wpsp_radius_section',
      __( 'RADIUS', WP_SECUREPASS_TEXTDOMAIN ),
      false,
      //array( $this, 'section' ),
      self::OPTION_GROUP
    );

    // RADIUS HOST
    add_settings_field(
      self::FIELD_RADIUS_HOST,
      __( 'Radius HOST', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'input_text' ),
      self::OPTION_GROUP,
      'wpsp_radius_section',
      array( self::FIELD_RADIUS_HOST, __( 'eg: radius1.secure-pass.net' ) )
    );

    // RADIUS SECRET
    add_settings_field(
      self::FIELD_RADIUS_SECRET,
      __( 'Radius SECRET', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'input_text' ),
      self::OPTION_GROUP,
      'wpsp_radius_section',
      array( self::FIELD_RADIUS_SECRET )
    );

    // SECTION - RESTFUL
    add_settings_section(
      'wpsp_restful_section',
      __( 'RESTFul API', WP_SECUREPASS_TEXTDOMAIN ),
      false,
      //array( $this, 'wpsp_restful_section' ),
      self::OPTION_GROUP
    );

    // RESTFUL STATUS
    add_settings_field(
      '',
      __( 'Status', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'restful_status' ),
      self::OPTION_GROUP,
      'wpsp_restful_section'
    );

    // RESTFUL ENDPOINT API
    add_settings_field(
      self::FIELD_RESTFUL_ENDPOINT,
      __( 'End Point API', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'input_text' ),
      self::OPTION_GROUP,
      'wpsp_restful_section',
      array( self::FIELD_RESTFUL_ENDPOINT, __( 'eg: ' ) . WPSecurePassRestful::API_ENDPOINT )
    );

    // RESTFUL APPLICATION ID
    add_settings_field(
      self::FIELD_RESTFUL_APPID,
      __( 'Application ID', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'input_text' ),
      self::OPTION_GROUP,
      'wpsp_restful_section',
      array( self::FIELD_RESTFUL_APPID )
    );

    // RESTFUL SECRET
    add_settings_field(
      self::FIELD_RESTFUL_SECRET,
      __( 'Secret', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'input_text' ),
      self::OPTION_GROUP,
      'wpsp_restful_section',
      array( self::FIELD_RESTFUL_SECRET )
    );

    // RESTFUL DOMAIN
    add_settings_field(
      self::FIELD_RESTFUL_DOMAIN,
      __( 'Domain', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'input_text' ),
      self::OPTION_GROUP,
      'wpsp_restful_section',
      array( self::FIELD_RESTFUL_DOMAIN, __( 'eg: mydomain.com' ) )
    );

    // SECTION - BLACKLIST
    add_settings_section(
      'wpsp_lists_section',
      __( 'Lists', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'wpsp_lists_section' ),
      self::OPTION_GROUP
    );

    // SWICTH TO WHITE/BLACK LIST
    add_settings_field(
      self::FIELD_LIST,
      __( 'Use list as', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'select' ),
      self::OPTION_GROUP,
      'wpsp_lists_section',
      array(
        self::FIELD_LIST,
        array(
          'white' => __( 'White List' ),
          'black' => __( 'Black List' )
        )
      )
    );

    // USER ROLES
    add_settings_field(
      self::FIELD_LIST_USER_ROLES,
      __( 'User Roles', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'input_user_roles_checkboxes' ),
      self::OPTION_GROUP,
      'wpsp_lists_section',
      array( self::FIELD_LIST_USER_ROLES )
    );

    // USERS
    add_settings_field(
      self::FIELD_LIST_USERS,
      __( 'Users', WP_SECUREPASS_TEXTDOMAIN ),
      array( $this, 'select_users' ),
      self::OPTION_GROUP,
      'wpsp_lists_section'
    );

  }

  /**
   * Not used at this moment
   */
  public function wpsp_general_section()
  {
    echo __( 'This section description', WP_SECUREPASS_TEXTDOMAIN );
  }

  /**
   * Additional information for RESTFul section.
   */
  public function restful_status()
  {
    // Check restful params
    $restful_status = WPSecurePassRestful::init()->ping();

    if( empty( $restful_status ) ) : ?>

      <div id="wpsp_restful_section" class="error">
        <p><?php _e( 'RESTFul API not available at this moment. Please check your settings.' ) ?></p>
      </div>

    <?php elseif( is_object( $restful_status ) ) : ?>

      <?php if( empty( $restful_status->errorMsg ) ) : ?>

        <div id="wpsp_restful_section" class="updated">
          <p><?php _e( 'RESTFul API works properly.' ) ?></p>
        </div>

      <?php else : ?>

        <div id="wpsp_restful_section" class="error">
          <p><?php _e( 'RESTFul API Error:' ) . $restful_status->errorMsg ?></p>
        </div>

      <?php endif; ?>

    <?php endif;
  }

  /**
   * Additional information for Blacklist section.
   */
  public function wpsp_lists_section()
  {
    echo __( 'The list of users and roles below, will be used to determine who can (whitelist) or cannot (blacklist) authenticate with Secure Pass protocol.', WP_SECUREPASS_TEXTDOMAIN );
  }

  // -------------------------------------------------------------------------------------------------------------------
  // UI
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * Display a generic input text field.
   *
   * @param array $args   Optional. Addition arguments
   *                      [
   *                      'field name',
   *                      'place holder',      // Optional
   *                      ]
   */
  public function input_text( $args = array() )
  {
    // Extract
    @list( $field_name, $place_holder ) = $args;
    ?>
    <input type="text"
           name="<?php echo self::OPTION_NAME ?>[<?php echo $field_name ?>]"
           size="32"
           placeholder="<?php echo is_null( $place_holder ) ? '' : $place_holder ?>"
           value="<?php echo $this->_options[ $field_name ]; ?>"/>
  <?php
  }

  /**
   * Display a generic input checkbox field.
   *
   * @param array $args   Optional. Addition arguments
   *                      [
   *                      'field name',
   *                      ]
   */
  public function input_checkbox( $args = array() )
  {
    // Extract
    @list( $field_name, $append ) = $args;
    ?>
    <input type="checkbox"
           name="<?php echo self::OPTION_NAME ?>[<?php echo $field_name ?>]"
           value="on"
      <?php isset( $this->_options[ $field_name ] ) ? checked( 'on', $this->_options[ $field_name ] ) : '' ?> />
    <?php echo !is_null( $append ) ? $append : '' ?>
  <?php
  }

  /**
   * Display a set of checkboxes to select user roles.
   *
   * @param array $args   Optional. Addition arguments
   *                      [
   *                      'field name',
   *                      ]
   */
  public function input_user_roles_checkboxes( $args = array() )
  {
    // Extract
    @list( $field_name ) = $args;

    // Get the available user roles
    $roles = new WP_Roles();

    // Loop into the roles
    foreach( $roles->role_names as $key => $value ) : ?>

      <label class="clearfix">
        <input type="checkbox"
               name="<?php echo self::OPTION_NAME ?>[<?php echo $field_name ?>][]"
               value="<?php echo $key ?>"
          <?php if( isset( $this->_options[ $field_name ] ) && !is_null( $this->_options[ $field_name ] ) ) checked( true, in_array( $key, $this->_options[ $field_name ] ) ) ?> />
        <?php echo $value ?>
      </label>
    <?php endforeach;
  }

  /**
   * Display a generic select (combo) field.
   *
   * @param array $args   Optional. Addition arguments
   *                      [
   *                      'field name',
   *                      [ 'options', ... ]
   *                      ]
   */
  public function select( $args = array() )
  {
    // Extract
    @list( $field_name, $options ) = $args;
    ?>
    <select id="<?php echo $field_name ?>" name="<?php echo self::OPTION_NAME ?>[<?php echo $field_name ?>]">
      <?php foreach( $options as $value => $text ) : ?>
        <option <?php selected( $value, $this->_options[ $field_name ] ) ?>
          value="<?php echo $value ?>"><?php echo $text ?></option>
      <?php endforeach; ?>
    </select>
  <?php
  }

  /**
   * Display an input text and select list for choose a list of users.
   */
  public function select_users()
  {
    // Query for all users
    $query = array(
      'exclude' => array()
    );

    // Get all registered users
    $all_user_query = get_users( $query );
    ?>

    <label class="clearfix">
      <select id="temp-users">
        <?php foreach( $all_user_query as $user ) : ?>
          <option value="<?php echo $user->ID ?>"><?php echo $user->display_name ?> (<?php echo $user->user_login ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <button class="button button-primary"
              id="wpsp-button-add"><?php _e( 'Add' ) ?>
      </button>
    </label>
    <?php

    // Empty list
    $selected_user_query = array();

    // Get users
    $users_id = $this->_options[ self::FIELD_LIST_USERS ];

    // Get user only if any id exist
    if( !empty( $users_id ) ) {

      // Prepare query for users
      $query = array(
        'include' => empty( $users_id ) ? array() : $users_id
      );

      // Get selected users via WP_User_Query
      $selected_user_query = get_users( $query );
    }

    ?>

    <label class="clearfix">
      <select id="<?php echo self::FIELD_LIST_USERS ?>"
              size="10"
              multiple="multiple"
              name="<?php echo self::OPTION_NAME ?>[<?php echo self::FIELD_LIST_USERS ?>][]">
        <?php foreach( $selected_user_query as $user ) : ?>
          <option value="<?php echo $user->ID ?>"><?php echo $user->display_name ?> (<?php echo $user->user_login ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <button class="button button-secondary"
              id="wpsp-button-remove"><?php _e( 'Remove' ) ?>
      </button>
      <br/>
      <button class="button button-secondary"
              id="wpsp-button-remove-all"><?php _e( 'Remove All' ) ?>
      </button>
    </label>

  <?php
  }

  /**
   * Init the options view and display.
   */
  public function display()
  {
    ?>
    <form id="wp-securepass-options" action="options.php" method="post">

      <h2>WP-SecurePass</h2>

      <?php
      settings_fields( self::OPTION_GROUP );
      do_settings_sections( self::OPTION_GROUP );
      ?>
      <p class="submit">
        <input name="submit"
               id="submit"
               class="button button-primary alignleft"
               value="<?php _e( 'Save Changes' ) ?>"
               type="submit"/>

        <button name="wp-securepass-reset-to-default"
                class="button button-secondary alignright"
                value="wp-securepass-reset-to-default">
          <?php _e( 'Reset to default', WP_SECUREPASS_TEXTDOMAIN ) ?>
        </button>
      </p>
    </form>
  <?php
  }

}
