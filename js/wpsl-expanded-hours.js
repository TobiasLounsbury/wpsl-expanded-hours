jQuery( document ).ready( function( $ ) {

  //Make the open_now an optional thing based on the setting
  if(WPSLEH.settings.wpsleh_enable_open_now) {

    //add the open now widget to the search form
    let widget = $('<div id="wpsl-result" class="magellan-input-group"><label>Open Now</label><input type="checkbox" name="open_now"  class="wpsl-hidden-accessible" id="wpsl-extended-hours-open-now"><label class="slider-v2" for="wpsl-extended-hours-open-now"></label> </div>');

    //Use the selector from the settings form
    let $target = $(WPSLEH.settings.wpsleh_open_now_widget_target);

    if(!$target) {
      $("#wpsl-category");
    }

    if($target) {
      $target.after(widget);
    } else {
      $("#wpsl-search-wrap").append(widget);
    }
  }

  //Add an event handler to add the open now flag to ajax search requests
  $( "#wpsl-wrap" ).on("filterAjaxData", function(event, data) {
    if ($("#wpsl-extended-hours-open-now").is(":checked")) {
      data.open_now = 1;
    }
  });

});