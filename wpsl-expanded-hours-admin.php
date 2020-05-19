<?php

/**
 * Render the admin interface for editing expanded hours.
 *
 * @param $args
 */
function wpsleh_build_expanded_hours_admin_form($args) {
  global $post;

  //Fetch the data
  $data = get_post_meta( $post->ID, 'wpsl_' . $args['key'], true );

  wpsl_expanded_hours_export_settings();

  echo "<input id='wpsl-". esc_attr( $args['key'] ) ."' type='hidden' name='wpsl[". esc_attr( $args['key'] ) ."]' value='". esc_attr( $data ) ."' />";
  ?>
    <div class='wpsleh-admin-form' data-target="wpsl-<?php echo esc_attr( $args['key'] ); ?>">

        <p class="wpsl-hours-dropdown">
            <label for="wpsl-editor-hour-input">Hour format:</label>
            <select class="wpsleh-config-format wpsleh-data-config-save" data-key="format">
                <option value="12">12 Hours</option>
                <option value="24">24 Hours</option>
            </select>
        </p>

        <table class="wpsleh-store-hours">
            <tbody>
            <tr>
                <th>Days</th>
                <th>Opening Periods</th>
                <th></th>
            </tr>

            <tr class="wpsleh-row-template">
                <td class="wpsleh-opening-day"></td>
                <td class="wpsleh-hours">
                    <p class="wpsleh-store-closed">Closed</p>

                </td>
                <td>
                    <div class="wpsleh-add-period wpsl-icon-plus-circled"></div>
                </td>
            </tr>

            <tr class='wpsleh-row-seperator'> <td colspan='3'><div class="wpsleh-add-custom-row wpsl-icon-plus-circled"></div> <strong>Custom Dates</strong></td> </tr>

            </tbody>
        </table>
        <div class="wpsleh-custom-day-template">
            <div class="wpsleh-remove-custom-row wpsl-icon-cancel-circled ib"></div>
            <div class="ib">Date: <input type="date" class="wpsleh-row-day wpsleh-row-day-custom" /></div>
            <div class="ib"><input placeholder="Event Label" class="wpsleh-data-day-save wpsleh-day-label" data-key="label" /></div>
        </div>
        <div class="wpsleh-period-template">
            <select class="wpsleh-open-hour wpsleh-data-period-save" data-key='open'>
              <?php
              $i = 0;
              while ($i < 1440) {
                  echo "<option class='wpsleh-hour-option' value='{$i}'>{$i}</option>\n";
                  $i += 15;
              }
              ?>
            </select>
            <span> - </span>
            <select class="wpsleh-close-hour wpsleh-data-period-save" data-key='close'>
              <?php
              $i = 0;
              while ($i < 1440) {
                echo "<option class='wpsleh-hour-option' value='{$i}'>{$i}</option>\n";
                $i += 15;
              }
              ?>
            </select>
            <div class="wpsleh-remove-period wpsl-icon-cancel-circled"></div>
        </div>
    </div>
  <?php
}
