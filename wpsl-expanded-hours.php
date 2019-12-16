<?php
/**
 * @package   WPSL_Expanded_Hours
 * @author    Tobias Lounsbury <TobiasLounsbury@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/TobiasLounsbury/wpsl-coauthors
 * @copyright 2019 Tobias Lounsbury
 *
 * @wordpress-plugin
 * Plugin Name:       Store Locator: Expanded Hours
 * Plugin URI:        https://github.com/TobiasLounsbury/wpsl-expanded-hours
 * Description:       Adds additional flexibility to open hours including searchable by "Open Now" Special Holiday flexibility and flexible display order
 * Version:           1.0.0
 * Author:            Tobias Lounsbury
 * Author URI:        http://TobiasLounsbury.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * Text Domain:       wpsl-ca
 * GitHub Plugin URI: https://github.com/TobiasLounsbury/wpsl-expanded-hours
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

//Define the current version number
define( 'WPSL_EXPANDED_HOURS_VERSION', '1.0.0' );


//Register Activation/deactivation hooks
register_activation_hook( __FILE__, 'wpsl_expanded_hours_plugin_activate' );
register_deactivation_hook( __FILE__, 'wpsl_expanded_hours_plugin_deactivate' );

/**
 *
 */
function wpsl_expanded_hours_plugin_activate() {

}


/**
 *
 */
function wpsl_expanded_hours_plugin_deactivate() {

}
