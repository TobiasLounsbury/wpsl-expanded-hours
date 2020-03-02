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
  $ampm = ($format == 24) ? "" : ($h > 12) ? " PM" : " AM";
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

  //todo: Add option for bolding today

  $output = "<table role='presentation' class='wpsl-opening-hours wpsl-expanded-hours'>";

  $day = strtotime(date("Y-m-d"));
  $i = 0;
  while($i < 7) {

    $dotw = date("w", $day);
    $date = date("Y-m-d", $day);

    $so = (array_key_exists($date, $hours)) ? "<del>" : "";
    $sc = (array_key_exists($date, $hours)) ? "</del>" : "";

    $output .= "<tr><td>$so". ucfirst(WPSLEH_DAY_LOOKUP[$dotw]) ."</td><td>";
    if(empty($hours[$dotw]['periods'])) {
      $output .= "{$so}Closed{$sc}";
    } else {
      foreach($hours[$dotw]['periods'] as $period) {
        $output .= "<time>$so" . wpsleh_convert_minutes_to_string($period['open'], $format) . " - " . wpsleh_convert_minutes_to_string($period['close'], $format) . "$sc</time>";
      }
    }
    $output .= "</td></tr>";


    if (array_key_exists($date, $hours)) {
      $output  .= "<tr><td>". $hours[$date]['label'] ."</td><td>";
      if(empty($hours[$date]['periods'])) {
        $output .= "Closed";
      } else {
        foreach($hours[$date]['periods'] as $period) {
          $output .= "<time>" . wpsleh_convert_minutes_to_string($period['open'], $format) . " - " . wpsleh_convert_minutes_to_string($period['close'], $format) . "</time>";
        }
      }
      $output .= "</td></tr>";
    }

    $day += 86400; //Add a day
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

  //todo: Handle timezones
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