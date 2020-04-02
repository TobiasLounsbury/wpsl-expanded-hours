jQuery( document ).ready( function( $ ) {

  //todo: make the open_now an optional thing.

  //todo: add the open now widget to the search form
  let widget = $('<div id="wpsl-result" class="magellan-input-group"><label>Open Now</label><input type="checkbox" name="open_now"  class="wpsl-hidden-accessible" id="wpsl-extended-hours-open-now"><label class="slider-v2" for="wpsl-extended-hours-open-now"></label> </div>');

  $("#wpsl-category").after(widget);

  //Add an event handler to add the open now flag to ajax search requests
  $( "#wpsl-wrap" ).on("filterAjaxData", function(event, data) {
    if ($("#wpsl-extended-hours-open-now").is(":checked")) {
      data.open_now = 1;
    }
  });

});