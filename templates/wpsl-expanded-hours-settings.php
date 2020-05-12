<div class='wpsleh-settings-form'>


  <?php require_once(__DIR__."/../wpsl-expanded-hours-utils.php"); ?>

  <?php //mainlib_magellan_acf_admin_enqueue_scripts(); ?>

  <?php
  wpsleh_render_messages();
  ?>


    <form method="post" action="options.php">
        <div class="postbox-container">
            <div class="metabox-holder">
                <div class="postbox">
                    <h3 class="hndle"><span>Expanded Hours Settings</span></h3>
                    <div class="inside">
                        <?php settings_fields( 'wpsl_expanded_hours_option_group' ); ?>
                        <p>
                            <label for="wpsleh_bold_today"><strong>Bold the Hours for Today:</strong></label>
                            <input type="checkbox" id="wpsleh_bold_today" name="wpsleh_bold_today" value="1" <?php echo (get_option('wpsleh_bold_today') == 1) ? "checked" : ""; ?> />
                        </p>
                        <hr>
                        <p>
                            <label for="wpsleh_enable_open_now"><strong>Enable open now search widget:</strong></label>
                            <input type="checkbox" id="wpsleh_enable_open_now" name="wpsleh_enable_open_now" value="1" <?php echo (get_option('wpsleh_enable_open_now') == 1) ? "checked" : ""; ?> />
                        </p>
                        <p>
                            <label for="wpsleh_open_now_widget_target"><strong>Where to Place the Open Now Widget:</strong></label>
                        </p>
                        <p>
                            <input type="text" id="wpsleh_open_now_widget_target" name="wpsleh_open_now_widget_target" value="<?php echo get_option('wpsleh_open_now_widget_target', '#wpsl-category'); ?>" />
                        </p>
                        <?php  submit_button(); ?>
                    </div>
                </div>
            </div>
        </div>
    </form>



  <div style="clear: both">
    <div class="postbox-container">
      <div class="metabox-holder">
        <div class="postbox">
          <h3 class="hndle"><span>Utilities</span></h3>
          <div class="inside">

            <form style="clear: both" method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
              <input type="hidden" name="action" value="wpsleh_all_data_export" />
              <p><label ><strong>Export all expanded hours data to json</strong></label></p>
              <p><label for="wpsleh_export_pretty">Output Formatting:</label></p>
              <p>
                <select name="wpsleh_export_pretty" >
                  <option value="0">Inline</option>
                  <option value="1">Pretty Printed</option>
                </select>
              </p>
              <?php  submit_button("Export All Hours Data"); ?>
            </form>

            <p>&nbsp;</p>
            <p><hr style="clear: both" /></p>

            <form style="clear: both" method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
              <input type="hidden" name="action" value="wpsleh_import_from_old_data" />
              <p><label ><strong>Import Hours Data From Store Locator:</strong></label></p>
              <?php  submit_button("Import Hours"); ?>
            </form>

            <p>&nbsp;</p>
            <p><hr style="clear: both" /></p>

            <form style="clear: both" method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
              <input type="hidden" name="action" value="wpsleh_save_to_old_data" />
              <p><label ><strong>Export Hours Data Back to Store Locator:</strong></label></p>
              <?php  submit_button("Export Hours"); ?>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>


  <form style="clear: both" method="post" action="<?php echo admin_url( 'admin.php' ); ?>" enctype="multipart/form-data">
    <input type="hidden" name="action" value="wpsleh_all_locations_import_data" />
    <div class="postbox-container">
      <div class="metabox-holder">
        <div class="postbox">
          <h3 class="hndle"><span>Import Hours Data</span></h3>
          <div class="inside">
            <p>
              <label for="wpsleh_import_json_input"><strong>Json Data File:</strong></label>
            </p>
            <p>
              <input type="file" id="wpsleh_import_json_input" name="wpsleh_import_json_input" />
            </p>
            <?php  submit_button("Import Data"); ?>
          </div>
        </div>
      </div>
    </div>
  </form>

</div>