jQuery( document ).ready( function( $ ) {

  //Days of the Week
  const dotw = {0: "sunday", 1: "monday", 2: "tuesday", 3: "wednesday", 4: "thursday", 5: "friday", 6: "saturday"};

  //Cache the calculated strings so they only need to be made once per page load
  let cache24 = {};

  /**
   * Convert minutes to a 24 hour formatted string
   *
   * @param minutes
   * @return string
   */
  let mto24 = (minutes) => {
    if (!cache24.hasOwnProperty(minutes)) {
      let m = minutes % 60;
      m = (m == 0) ? "00" : m;
      let h = Math.floor(minutes / 60);
      h = (h < 10) ? "0" + h : h;
      cache24[minutes] = h + ":" + m;
    }
    return cache24[minutes];
  };


  //Cache the calculated strings so they only need to be made once per page load
  let cache12 = {};

  /**
   * Convert minutes to a 12 hour formatted string
   *
   * @param minutes
   * @return string
   */
  let mto12 = (minutes) => {
    if (!cache12.hasOwnProperty(minutes)) {
      let h = Math.floor(minutes / 60);
      let m = minutes % 60;
      m = (m == 0) ? "00" : m;
      let ampm = (h >= 12) ? "PM" : "AM";
      h = (h > 12) ? h - 12 : h;
      h = (h == 0) ? 12 : h;
      cache12[minutes] =  h + ":" + m + " " + ampm;
    }
    return cache12[minutes];
  };
  

  /**
   * Re-serialize the form data into a json string and stuff it into the target
   * hidden input.
   *
   * @param $form
   * @param $target
   */
  let reserialize = ($form, $target) => {

    //empty object for our data to be stored in before being serialized.
    let data = {"config": {}};

    //Serialize the config variables
    $form.find(".wpsleh-data-config-save").each(function() {
      data.config[$(this).data("key")] = $(this).val();
    });

    //Loop through each Row.
    $form.find(".wpsleh-row").each(function() {
      let day = $(this).find(".wpsleh-row-day").val();

      //Initialize each "row" with an entry in data so that
      //if no periods are found it registers as closed.
      data[day] = {"periods": []};

      //Grab the custom data for each day (such as title)
      $(this).find(".wpsleh-data-day-save").each(function() {
        data[day][$(this).data("key")] = $(this).val();
      });

      //Loop through all the periods for this day
      $(this).find(".wpsleh-period").each(function() {
        let period = {};
        //Loop through all inputs that have the data-save class and use their
        //data-key tag to store them in the period before it is added to the day
        $(this).find(".wpsleh-data-period-save").each(function() {
          period[$(this).data("key")] = $(this).val();
        });
        data[day].periods.push(period);
      });
    });


    //Serialize and save our data object
    $target.val( JSON.stringify(data) );
  };



  /**
   *
   *
   * @param data
   * @param $day
   */
  let loadPeriods = (data, $day) => {
    if(data.length > 0) {
      let $hours = $day.find(".wpsleh-hours");
      for(var p in data) {
        let $period = $(".wpsleh-period-template").clone();
        $period.removeClass("wpsleh-period-template").addClass("wpsleh-period");
        $period.find(".wpsleh-open-hour").val(data[p].open);
        $period.find(".wpsleh-close-hour").val(data[p].close);
        $hours.append($period);
      }
    } else {
      $day.find(".wpsleh-store-closed").show();
    }
  };



  /**
   * Take action for each expanded hours form we find on the page
   */
  $(".wpsleh-admin-form").each(function() {
    //Get our data into a usable format
    let $target = $("#" + $(this).data("target"));
    let data;
    try {
      data =  JSON.parse($target.val());
    } catch (e) {
      data = {"config": {"format": 12}, 0: [], 1: [], 2: [], 3: [], 4: [], 5: [], 6: []};
    }

    //set all of the configs
    for(var slug in data.config) {
      $(".wpsleh-config-" + slug).val( data.config[slug]);
    }


    let $template = $(".wpsleh-row-template");
    //console.log(data);

    //Construct the Days of the Week form
    for (var i in dotw) {
      let $day = $template.clone();
      $day.removeClass("wpsleh-row-template").addClass("wpsleh-row wpsleh-row-" + dotw[i]);
      $day.find(".wpsleh-opening-day").html(dotw[i].charAt(0).toUpperCase() + dotw[i].substring(1));
      $day.find(".wpsleh-opening-day").append("<input type='hidden' class='wpsleh-row-day' value='" + i + "' />");

      if(data.hasOwnProperty(i) && data[i].hasOwnProperty("periods")) {
        loadPeriods(data[i].periods, $day);
      } else {
        loadPeriods({}, $day);
      }

      $(this).find(".wpsleh-row-seperator").before($day);
    }



    //Handle the custom entries
    for (var day in data) {
      if(!dotw.hasOwnProperty(day) && day !== "config") {

        //Clone the row template
        let $day = $(".wpsleh-row-template").clone();
        $day.removeClass("wpsleh-row-template").addClass("wpsleh-row wpsleh-row-custom");
        $day.find(".wpsleh-opening-day").html( $(".wpsleh-custom-day-template").clone() );
        $day.find(".wpsleh-custom-day-template").removeClass("wpsleh-custom-day-template").addClass("wpsleh-custom-day");

        //Set the day
        $day.find(".wpsleh-row-day").val( day );

        //Set any custom data for the day, like a label
        for(key in data[day]) {
          if(data[day].hasOwnProperty(key) && key !== "periods") {
            $day.find(".wpsleh-day-" + key).val( data[day][key] );
          }
        }


        //Load the period data
        loadPeriods(data[day].periods, $day);

        //Add it to the table
        $(this).find("tbody").append($day);
      }
    }



    /**
     * Generic click handler, on the "form" use class
     * filtering for various behaviors
     */
    $(this).click(function(e) {

      /**
       * Handle clicks for new open periods
       */
      if($(e.target).hasClass("wpsleh-add-period")) {
        let $obj = $(this).find(".wpsleh-period-template").clone();
        $obj.removeClass("wpsleh-period-template").addClass("wpsleh-period");
        $(e.target).closest(".wpsleh-row").find(".wpsleh-hours").append($obj).find(".wpsleh-store-closed").hide();
        //recalculate serialized data
        reserialize($(this), $target);
      }


      /**
       * Handle clicks for adding custom day rows
       */
      if($(e.target).hasClass("wpsleh-add-custom-row")) {
        //Clone the row template
        let $day = $(".wpsleh-row-template").clone();
        $day.removeClass("wpsleh-row-template").addClass("wpsleh-row wpsleh-row-custom");
        $day.find(".wpsleh-opening-day").html( $(".wpsleh-custom-day-template").clone() );
        $day.find(".wpsleh-custom-day-template").removeClass("wpsleh-custom-day-template").addClass("wpsleh-custom-day");
        $day.find(".wpsleh-store-closed").show();
        //Add it to the table
        $(this).find("tbody").append($day);
      }



      /**
       * Handle clicks for removing periods
       */
      if($(e.target).hasClass("wpsleh-remove-period")) {
        let $row = $(e.target).closest(".wpsleh-row");
        $(e.target).closest(".wpsleh-period").remove();
        //show the closed sign if there are no more open periods
        if($row.find(".wpsleh-period").length === 0) {
          $row.find(".wpsleh-store-closed").show();
        }
        //recalculate serialized data
        reserialize($(this), $target);
      }


      /**
       * Handle click for removing whole custom days
       */
      if($(e.target).hasClass("wpsleh-remove-custom-row")) {
        let $row = $(e.target).closest(".wpsleh-row").remove();
        reserialize($(this), $target);
      }

    }); //End of click handler


    /**
     * Handle when/when not to reserialize.
     */
    $(this).change(function(e) {

      /**
       * Change the display format for hours
       */
      if($(e.target).hasClass("wpsleh-config-format")) {
        //change between 12/24 hour format
        if($(e.target).val() == 12) {
          $(this).find(".wpsleh-hour-option").each(function() {
            $(this).html( mto12( $(this).attr("value") ) );
          });
        } else {
          $(this).find(".wpsleh-hour-option").each(function() {
            $(this).html( mto24( $(this).attr("value") ) );
          });
        }
      }


      //Stuff on the form has changed, so re-serialize the data
      reserialize($(this), $target);
    }); // End of change handler

  });

  //Trigger an update of the hours on page load
  //So they are all in the correct format
  $(".wpsleh-config-format").change();

});