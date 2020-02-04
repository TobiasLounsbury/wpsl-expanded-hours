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