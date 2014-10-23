<?php
/*
Plugin Name: SecurePass authentication
Plugin URI: https://github.com/gpaterno/wp-securepass
Description:  Authenticates Wordpress usernames against SecurePass
Version: 0.2.0
Author: Giuseppe Paterno' (gpaterno@gpaterno.com)
Author URI: http://www.gpaterno.com/
*/

/*
 * Few extra comments here: this plugin is far from being
 * perfect, but it seems to work for basic operations.
 * Feel free to contribute to it.
 *
 * This software is released under GPLv2.
 * Please note that this software comes with NO WARRANTIES!!!!!
 * Although is known to work, use it at YOUR OWN RISK.
 * Full GPLv2 license is on:
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * Neither the author(s), SecurePass or GARL Sagl is responsible 
 * for this software.
 *
 * A known limitation is the failure of a datacenter.
 * The used radius PHP class don't handle it, instead it reports
 * "authentication failure" on timeout. You might wish to consider
 * modifying the sp_authenticate() function below to requery a
 * secondary datacenter in case of authentication failure.
 * 
 * BEFORE activating this plugin, make sure that you created a
 * user in wordpress that has the same name in SecurePass and
 * has full administrative powers. This because admin will be
 * no longer checked locally. In case you won't be able to login
 * any more, a workaround is moving the securepass plugin directory
 * to another directory name, ex: "mv securepass securepass.old".
 */

// Avoid directly access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get plugin path
define( 'WP_SECUREPASS_PATH', trailingslashit( dirname( __FILE__) ) );

// Load defines
require_once( WP_SECUREPASS_PATH . 'defines.php' );

// Load the radius class
require_once( WP_SECUREPASS_PATH . 'radius.class.php' );

// Load the RESTFul api controller class
require_once( WP_SECUREPASS_PATH . 'restful.class.php' );

// Load the securepass options class
require_once( WP_SECUREPASS_PATH . 'options.class.php' );

// Load the securepass controller class
require_once( WP_SECUREPASS_PATH . 'securepass.class.php' );

// Init options
WPSecurePassOptions::init();

// Init (main) controller
WPSecurePassController::init();