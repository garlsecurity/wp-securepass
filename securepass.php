<?php
/*
Plugin Name: SecurePass authentication
Plugin URI: https://github.com/gpaterno/wp-securepass
Description:  Authenticates Wordpress usernames against SecurePass
Version: 0.1
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
 

// Load the radius class
require_once('radius.class.php');

// Add authentication filter
add_filter('authenticate', 'sp_authenticate', 10, 3);

// The core authentication function
function sp_authenticate( $user, $username, $password ){
     /* 
      * Set the default SecurePass radius server
      * We point to the Swiss datacenter here by default, 
      * change radius_host to radius2.secure-pass.net to point to Milan 
      * don't forget to change the secret accordingly
      */
     $radius_host = 'radius1.secure-pass.net';
     $radius_secret = 'CHANGEME';     // <-- DON'T FORGET TO CHANGE IT!!!!!

     // Get info
     $user = get_userdatabylogin( $username ); 
     $radius = new Radius($radius_host, $radius_secret);
 
     // Check the password via RADIUS
     if (! $radius->AccessRequest($username, $password)) {
        $user = new WP_Error( 'denied', __("<strong>ERROR</strong>: Invalid username and password.") );
        remove_action('authenticate', 'wp_authenticate_username_password', 20);
     }
 
     return $user;
}

?>
