<?php

require_once("wpsl-expanded-hours-utils.php");


/**
 *
 */
function wpsleh_first_install() {

  //Import from old data
  wpsleh_import_from_old_hours();

  //Convert the Default hours
  wpsleh_convert_default_hours();

  //Set the flag so we don't run this again.
  update_option("wpsleh_installed", true);

}


/**
 * Handle Converting the old format hours into the new
 * format and saving them for each location.
 */
function wpsleh_import_from_old_hours() {
  //format example: {0: [...], 1:[], 0209: [], mmdd: []}
  //Each day entry contents: {open: 510, close: 1080} - 8:30AM to 6PM
  //number of minutes since midnight: hours * 60 + minutes

  //Fetch all of our location posts
  $locations = get_posts(['post_type' => 'wpsl_stores', 'numberposts' => -1]);

  //Loop through all the locations
  foreach($locations as $location) {

    //This shouldn't replace all the new data, if there are existing
    //custom entries we should only be "importing" the old weekly data and not
    //overwrite custom entries.
    $newData = json_decode(get_post_meta($location->ID, "wpsl_expanded_hours", true), true);
    if(!$newData) {
      $newData = [];
    }

    //Make sure that we have a config entry
    $newData['config'] = (array_key_exists("config", $newData)) ? $newData['config'] : [];

    //Fetch the old Hours data
    $oldData = get_post_meta($location->ID, "wpsl_hours", true);

    //Store what format was being used so we can display our data in the same format
    $newData['config']['format'] = (preg_match('/(AM|PM)/i', serialize($oldData)) === 1) ? 12 : 24;

    //Loop through each Day
    foreach($oldData as $day => $hours) {
      //Example of what old data looks like '9:45 AM,7:00 PM'
      //or '09:45,19:00' if the listing was in 24 Hour format

      //Initialize the day as an empty array
      $newData[WPSLEH_DAY_LOOKUP[$day]] = array("periods" => []);

      //Loop through each entry for this day
      foreach ($hours as $hourString) {
        //Split the listing string on the comma into open and close times
        list($oldOpen, $oldClose) = explode(",", $hourString);

        //Parse each time into the new number format
        $newData[WPSLEH_DAY_LOOKUP[$day]]['periods'][] = [
            "open" => wpsleh_parse_old_time_string($oldOpen),
            "close" => wpsleh_parse_old_time_string($oldClose)
        ];
      }
    }
    update_post_meta($location->ID, "wpsl_expanded_hours", json_encode($newData));
  }

}


/**
 * Handle converting from our new extended hours format back to
 * the standard WPSL text format
 *
 */
function wpsleh_export_to_old_hours() {

  //Fetch all of our location posts
  $locations = get_posts(['post_type' => 'wpsl_stores', 'numberposts' => -1]);

  //Loop through all the locations
  foreach($locations as $location) {
    $oldHours = array();
    $expandedData = json_decode(get_post_meta($location->ID, "wpsl_expanded_hours", true), true);

    if($expandedData) {
      $format = $expandedData['config']['format'];
      foreach(range(0,6) as $dotw) {
        $oldHours[WPSLEH_DAY_LOOKUP[$dotw]] = array();

        if(array_key_exists("periods", $expandedData[$dotw]) && !empty($expandedData[$dotw]['periods'])) {
          foreach($expandedData[$dotw]['periods'] as $period) {
            $oldHours[WPSLEH_DAY_LOOKUP[$dotw]][] = wpsleh_convert_minutes_to_string($period['open'], $format). ", ". wpsleh_convert_minutes_to_string($period['close'], $format);
          }
        }
      }

      update_post_meta($location->ID, "wpsl_hours", $oldHours);
    }
  }
}


/**
 * Helper function that parses a single time string and
 * convert it into a number of minutes.
 *
 * @param $hourString
 * @return int
 */
function wpsleh_parse_old_time_string($hourString) {
  //Split based on the space between the time and the AM/PM
  //If in 24 hour format $ampm is null
  list($time, $ampm) = explode(" ", $hourString);

  //Split the time into hours and minutes
  list($hours, $minutes) = explode(":", $time);
  //Convert the hours into minutes and add the hours
  $numVal = (intval($hours) * 60) + intval($minutes);

  //Add 12 hours worth of minutes if the time was in the PM
  return ($ampm == "PM") ? 720 + $numVal : $numVal;
}


/**
 * Export all expanded hours data to a json download
 */
function wpsleh_export_all_store_data($pretty = false) {
  $file_name = 'store-locator-expanded-hour-export-' . date('Ymd' ) . '.json';

  // Set the download headers for the CSV file.
  header( 'Content-Type: application/json; charset=utf-8' );
  header( 'Content-Disposition: attachment; filename=' . $file_name . '' );

  //Open the stream for writing
  $output = fopen( 'php://output', 'w' );

  //Fetch all the location posts.
  $locations = get_posts(['post_type' => 'wpsl_stores', 'numberposts' => -1]);

  //data that will get json encoded and output
  $outputData = array();

  //Loop through the locations and build a data object before we encode and output
  foreach($locations as $location) {
    //Fetch the custom hours data
    $hourString = get_post_meta($location->ID, "wpsl_expanded_hours", true);
    $hours = json_decode($hourString, true);
    //Add to output object
    $outputData[] = array("id" => $location->ID, "data" => $hours);
  }

  //Output the data
  if($pretty) {
    fputs($output, json_encode($outputData, JSON_PRETTY_PRINT));
  } else {
    fputs($output, json_encode($outputData));
  }

  //Close the stream
  fclose( $output );
  exit();
}


/**
 * @param $json
 */
function wpsleh_import_all_store_data($json) {
  try {
    $data = json_decode($json, true);
    if($data) {
      foreach ($data as $store) {
        //If the id provided matches a post of type store_locator
        $post = get_post($store['id']);
        if ($post && $post->post_type == "wpsl_stores") {
          update_post_meta($post->ID, "wpsl_expanded_hours", json_encode($store['data']));
        }
      }
      wpsleh_add_success("Data imported Successfully");
    } else {

      wpsleh_add_error("Error reading json file: ". json_last_error_msg());
    }
  } catch (Exception $e) {
    //Failed, set a UI error
    wpsleh_add_error("Error reading json file: ". $e->getMessage());
  }
}


/**
 *
 */
function wpsleh_convert_default_hours() {

}