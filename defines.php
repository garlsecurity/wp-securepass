<?php

/**
 * SecurePass defines.
 *
 * @author          =undo= <info@wpxtre.me>
 * @copyright       Copyright (C) 2014 wpXtreme Inc. All Rights Reserved.
 * @date            2014-10-14
 * @version         1.0.0
 * @since           0.2.0
 *
 */

// Securepass main define
define( 'WP_SECUREPASS_VERSION', '0.2.0' );

// TODO Not use yet
define( 'WP_SECUREPASS_TEXTDOMAIN', 'wp-securepass' );

define( 'WP_SECUREPASS_URL', plugin_dir_url( __FILE__ ) );

// Useful define for assets folder
define( 'WP_SECUREPASS_ASSETS_URL', WP_SECUREPASS_URL . 'assets/' );
define( 'WP_SECUREPASS_ASSETS_CSS_URL', WP_SECUREPASS_ASSETS_URL . 'css/' );
define( 'WP_SECUREPASS_ASSETS_JAVASCRIPT_URL', WP_SECUREPASS_ASSETS_URL . 'js/' );