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

//Add a custom "metabox" that will ed up being rendered on the admin side as the
//expanded hours interface
add_filter( 'wpsl_meta_box_fields', 'wpsl_expanded_hours_wpsl_custom_meta_box_fields' );

//Register a custom input type handler that will render the admin interface
add_filter( 'wpsl_metabox_expanded_hours_input', 'wpsl_expanded_hours_metabox_input' );

//Add custom metadata to the output front and back ends.
add_filter( 'wpsl_store_meta', 'wpsl_expanded_hours_custom_store_meta', 10, 2 );
add_filter( 'wpsl_frontend_meta_fields', 'wpsl_expanded_hours_frontend_meta_fields', 10, 2);

//Add a tab for custom settings
add_filter( 'wpsl_settings_tab', 'wpsl_expanded_hours_add_custom_settings_tab');
add_filter( 'wpsl_settings_section', 'wpsl_expanded_hours_render_custom_settings_tab');

//Add a filter to remove results that aren't open when the openNow flag is set
add_filter( 'wpsl_store_data', 'wpsl_expanded_hours_filter_store_data');

//Add JS to the front-end
add_action( 'admin_enqueue_scripts', 'wpsl_expanded_hours_enqueue_scripts');

//Override the wpsl_hours shortcode so it can be rendered by expanded hours
add_shortcode( 'wpsl_hours', 'wpsl_expanded_hours_show_opening_hours');

add_filter( 'wp_footer', 'wpsl_expanded_hours_add_scripts');

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
  $store_meta['expanded_hours'] = json_decode(get_post_meta($store_id, "wpsl_expanded_hours", true), true);
  $store_meta['hours'] = wpsleh_render_hours($store_meta['expanded_hours']);
  return $store_meta;
}


/**
 * Add custom expanded hours metadata so it is available on the
 * front end.
 *
 * @param $store_meta
 * @return mixed
 */
function wpsl_expanded_hours_frontend_meta_fields($store_meta) {
  $store_meta['wpsl_expanded_hours'] = array("name" => 'expanded_hours');
  return $store_meta;
}

function wpsl_expanded_hours_add_custom_settings_tab($tabs) {
  $tabs['expanded_hours'] = "Expanded Hours";
  return $tabs;
}

function wpsl_expanded_hours_render_custom_settings_tab($tab) {
  if ($tab == "expanded_hours") {
    require_once("wpsl-expanded-hours-admin.php");
    wpsleh_build_expanded_hours_settings_form();
  }
}

/**
 * Add css and js to front-end
 */
function wpsl_expanded_hours_add_scripts() {
  wp_enqueue_script("wpsl_expanded_hours_js", plugin_dir_url(__FILE__)."js/wpsl-expanded-hours.js");
  wp_enqueue_style("wpsl_expanded_hours_css",  plugin_dir_url(__FILE__)."css/wpsl-expanded-hours.css");
}


/**
 * Filter the store data being returned for open now status
 *
 * @param $storeData
 * @return array
 */
function wpsl_expanded_hours_filter_store_data($storeData) {
  //only filter the results IF the open now flag is sent
  if(array_key_exists("open_now", $_REQUEST) && $_REQUEST['open_now'] == "1") {
    require_once("wpsl-expanded-hours-utils.php");
    //Calling array values as the easiest way to re-index the array
    //because the WPSL front-end can't handle an array that isn't sequentially indexed.
    $storeData = array_values(array_filter($storeData, wpsleh_store_is_open));
  }
  return $storeData;
}


/**
 *
 *
 * @param $atts
 * @return string|void
 */
function wpsl_expanded_hours_show_opening_hours($atts) {

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