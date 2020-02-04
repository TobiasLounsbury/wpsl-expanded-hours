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
define("WPSLEH_DAY_LOOKUP", [
    0 => "sunday",
    1 => "monday",
    2 => "tuesday",
    3 => "wednesday",
    4 => "thursday",
    5 => "friday",
    6 => "saturday",
    "sunday"    => 0,
    "monday"    => 1,
    "tuesday"   => 2,
    "wednesday" => 3,
    "thursday"  => 4,
    "friday"    => 5,
    "saturday"  => 6,
]);

//Register Activation/deactivation hooks
register_activation_hook( __FILE__, 'wpsl_expanded_hours_plugin_activate' );
register_deactivation_hook( __FILE__, 'wpsl_expanded_hours_plugin_deactivate' );

add_filter( 'wpsl_meta_box_fields', 'wpsl_expanded_hours_wpsl_custom_meta_box_fields' );
add_filter( 'wpsl_metabox_expanded_hours_input', 'wpsl_expanded_hours_metabox_input' );
add_filter( 'wpsl_store_meta', 'wpsl_expanded_hours_custom_store_meta', 10, 2 );

add_action( 'admin_enqueue_scripts', 'wpsl_expanded_hours_enqueue_scripts');

add_shortcode( 'wpsl_hours', 'wpsl_expanded_hours_show_opening_hours');

/**
 * Handle Activation tasks
 */
function wpsl_expanded_hours_plugin_activate() {
  require_once("wpsl-expanded-hours-install.php");
  wpsleh_import_from_old_hours();
}


/**
 * Handle Deactivation Tasks
 */
function wpsl_expanded_hours_plugin_deactivate() {
  require_once("wpsl-expanded-hours-install.php");
  wpsleh_export_to_old_hours();
}


/**
 * Function used to create new metadata fields for locations
 *
 * @param $meta
 * @return mixed
 */
function wpsl_expanded_hours_wpsl_custom_meta_box_fields($meta) {
  //todo: allow a config where an admin can define if we remove the old hours or not.
  unset($meta['Opening Hours']['hours']);
  $meta['Opening Hours']['expanded_hours'] = array("label" => "Hours", "type" => "expanded_hours");
  return $meta;
}


/**
 * Generate the new Expanded Hours form.
 *
 * @param $args
 */
function wpsl_expanded_hours_metabox_input($args) {
  require_once("wpsl-expanded-hours-admin.php");
  wpsleh_build_expanded_hours_admin_form($args);
}


/**
 * Add scripts for handling the admin side of the expanded hours form.
 */
function wpsl_expanded_hours_enqueue_scripts() {
  wp_enqueue_script( 'wpsl-expanded-hours-admin-js', plugins_url( '/js/wpsl-expanded-hours-admin.js', __FILE__ ), array( 'jquery' ), WPSL_EXPANDED_HOURS_VERSION, true );
  wp_enqueue_style('wpsl-expanded-hours-admin-css', plugins_url( '/css/wpsl-expanded-hours.css', __FILE__ ));
}


/**
 * Adds Custom Expanded hours metadata to store locations
 * before output
 *
 * @param $store_meta
 * @param $store_id
 * @return mixed
 */
function wpsl_expanded_hours_custom_store_meta($store_meta, $store_id) {
  require_once("wpsl-expanded-hours-utils.php");
  $store_meta['expanded_hours'] = get_post_meta($store_id, "wpsl_expanded_hours", true);
  $expanded_hours = json_decode($store_meta['expanded_hours'], true);
  $store_meta['hours'] = wpsleh_render_hours($expanded_hours);
  return $store_meta;
}

function wpsl_expanded_hours_show_opening_hours($atts) {
  wp_enqueue_style("wpsl_expanded_hours",  plugin_dir_url(__FILE__)."/css/wpsl-expanded-hours.css");

  require_once("wpsl-expanded-hours-utils.php");
  global $wpsl_settings, $post;

  // If the hours are set to hidden on the settings page, then respect that and don't continue.
  if ( $wpsl_settings['hide_hours'] ) {
    return;
  }

  $hide_closed = apply_filters( 'wpsl_hide_closed_hours', false );

  $atts = wpsl_bool_check( shortcode_atts( apply_filters( 'wpsl_hour_shortcode_defaults', array(
      'id'          => '',
      'hide_closed' => $hide_closed
  ) ), $atts ) );

  if ( get_post_type() == 'wpsl_stores' ) {
    if ( empty( $atts['id'] ) ) {
      if ( isset( $post->ID ) ) {
        $atts['id'] = $post->ID;
      } else {
        return;
      }
    }
  } else if ( empty( $atts['id'] ) ) {
    return __( 'If you use the [wpsl_hours] shortcode outside a store page you need to set the ID attribute.', 'wpsl' );
  }

  $opening_hours = get_post_meta( $atts['id'], 'wpsl_expanded_hours' );
  try {
    $opening_hours = json_decode($opening_hours[0], true);
  } catch (exception $e) {
    return;
  }

  if ( $opening_hours ) {
    $output = wpsleh_render_hours($opening_hours);
    return $output;
  }
}