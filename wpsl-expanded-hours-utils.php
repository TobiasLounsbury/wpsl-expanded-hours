<?php

/**
 * Helper function that takes a number of minutes and converts
 * it into a standard time string
 *
 * e.g. 600 => 10:00 AM
 *
 * @param $minutes
 * @param $format
 * @return string
 */
function wpsleh_convert_minutes_to_string($minutes, $format) {
  $m = $minutes % 60;
  $m = ($m == 0) ? "00" : $m;
  $h = floor( $minutes / 60);
  $ampm = ($format == 24) ? "" : ($h >= 12) ? " PM" : " AM";
  $h = (($format == 12) && ($h > 12)) ? $h - 12 : $h;
  $h = (($format == 12) && ($h == 0)) ? 12 : $h;
  return "{$h}:{$m}{$ampm}";
}


/**
 * Pre-Renders the hours as a block of html.
 *
 * @param $hours
 * @return string
 */
function wpsleh_render_hours($hours) {

  $format = (array_key_exists("config", $hours) && array_key_exists("format", $hours['config'])) ? $hours['config']['format'] : 12;

  //Add option for bolding today
  $todayClass = (get_option('wpsleh_bold_today') == "1") ? "wpsleh-today wpsleh-today-bold" : "wpsleh-today";

  $output = "<table role='presentation' class='wpsl-opening-hours wpsl-expanded-hours'>";

  //Set Timezone
  wpsleh_set_timezone($hours);
  $day = strtotime(date("Y-m-d"));
  $i = 0;
  while($i < 7) {

    $dotw = date("w", $day);
    $date = date("Y-m-d", $day);
    $special = (array_key_exists($date, $hours));
    $data = (array_key_exists($date, $hours)) ? $hours[$date] : $hours[$dotw];
    $today = (date("w") == $dotw) ? $todayClass : "";

    $output .= "<tr class='wpsleh-day-row {$today}'><td>". ucfirst(WPSLEH_DAY_LOOKUP[$dotw]) ."</td><td>";


    if(empty($data['periods'])) {
      $output .= "Closed";
    } else {
      foreach($data['periods'] as $period) {
        $output .= "<time>" . wpsleh_convert_minutes_to_string($period['open'], $format) . " - " . wpsleh_convert_minutes_to_string($period['close'], $format) . "</time>";
      }
    }
    $output .= "</td></tr>";

    if ($special) {
      $output  .= "<tr class='wpsleh-special-hours-row {$today}'><td>". $hours[$date]['label'] ."</td><td>(Special Hours)</td></tr>";
    }

    // Add a day while also allowing for daylight savings.
    $day = strtotime( date("Y-m-", $day) . ( intval(date("d",$day)) + 1) );
    $i++; //increment the index
  }

  $output .= "</table>";
  return $output;
}


/**
 * Returns a boolean indicating if the requested location is currently
 * open based on their expanded hours
 *
 * @param $store array|int Either a store object or a post id
 * @return boolean indicating if the location is currently open
 */
function wpsleh_store_is_open($store) {

  try {

    //If we can't load the data return false;
    if (is_int($store)) {
      $store = array("expanded_hours" => json_decode(get_post_meta($store, "wpsl_expanded_hours", true), true));
    }

    //If we don't have data, return false;
    if(!array_key_exists("expanded_hours", $store)) {
      return false;
    }
  } catch (Exception $e) {
    return false;
  }

    //Set the Timezone
    wpsleh_set_timezone($store);


  $custom = date("Y-m-d");
  $dotw = (array_key_exists($custom, $store['expanded_hours'])) ? $custom : date("w");
  $periods = $store['expanded_hours'][$dotw]['periods'];
  if (!empty($periods)) {
    $minutes = intval(date("G")) * 60 + (intval(date("i")));
    foreach ($periods as $period) {
      //todo: Walk through the hours and check to see if the store is open
      if($period['open'] <= $minutes && $period['close'] >= $minutes) {
        return true;
      }
    }
  }

  return false;
}


function wpsleh_set_timezone($store_meta = []) {
    //Handle timezones: We can just set the "default" timezone based on the store data
    $timezone = get_option("wpsleh_default_timezone", WPSLEH_DEFAULT_SETTINGS['wpsleh_default_timezone']);
    if(array_key_exists("expanded_hours", $store_meta)) {
        if (array_key_exists("timezone", $store_meta['expanded_hours'])) {
            $timezone = $store_meta['expanded_hours']['timezone'];
        }
        if (array_key_exists("config", $store_meta['expanded_hours']) && array_key_exists("timezone", $store_meta['expanded_hours']['config'])) {
            $timezone = $store_meta['expanded_hours']['config']['timezone'];
        }
    }
    date_default_timezone_set($timezone);
}

function wpsleh_add_error($msg) {
  wpsleh_add_message($msg, "error");
}

function wpsleh_add_warning($msg) {
  wpsleh_add_message($msg, "warning");
}

function wpsleh_add_success($msg) {
  wpsleh_add_message($msg, "success");
}

function wpsleh_add_message($msg, $class) {
  if(!array_key_exists("wpsleh_messages", $GLOBALS)) {
    $GLOBALS['wpsleh_messages'] = array();
  }
  $GLOBALS['wpsleh_messages'][] = array("text" => $msg, "class" => $class);
}

function wpsleh_clear_messages() {
  $_GLOBAL['wpsleh_messages'] = array();
}

function wpsleh_render_messages() {
  if(array_key_exists("wpsleh_messages", $GLOBALS)) {
    foreach ($GLOBALS['wpsleh_messages'] as $msg) {
      echo "<div class='notice notice-${msg['class']} is-dismissible'><p><strong>${msg['text']}</strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
    }
    wpsleh_clear_messages();
  }
}

