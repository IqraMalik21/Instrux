    <!-- JavaScript files-->

    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/popper.js/umd/popper.min.js"> </script>
    <script src="assets/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/grasp_mobile_progress_circle-1.0.0.min.js"></script>
    <script src="assets/vendor/jquery.cookie/jquery.cookie.js"> </script>
    <script src="assets/vendor/chart.js/Chart.min.js"></script>
    <script src="assets/vendor/jquery-validation/jquery.validate.min.js"></script>
    <script src="assets/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
    
    <!-- Main File-->
    <script src="assets/js/front.js"></script>
    <!-- Bootstrap Multi Select -->
    <script src="assets/js/bootstrap-multiselect.js"></script>

    <script type="text/javascript">
      $(window).on('load', function(){
        $('#loading').hide();
      });
    </script>

    <script type="text/javascript">

    $(document).ready(function() {
        $("#show_hide_password a").on('click', function(event) {
            event.preventDefault();
            if($('#show_hide_password input').attr("type") == "text"){
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass( "fa-eye-slash" );
                $('#show_hide_password i').removeClass( "fa-eye" );
            }else if($('#show_hide_password input').attr("type") == "password"){
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass( "fa-eye-slash" );
                $('#show_hide_password i').addClass( "fa-eye" );
            }
        });
    });

    </script>

    <script type="text/javascript">
      function roundTo(n, digits) {
					var negative = false;
					if (digits === undefined) {
							digits = 0;
					}
							if( n < 0) {
							negative = true;
						n = n * -1;
					}
					var multiplicator = Math.pow(10, digits);
					n = parseFloat((n * multiplicator).toFixed(11));
					n = (Math.round(n) / multiplicator).toFixed(2);
					if( negative ) {    
							n = (n * -1).toFixed(2);
					}
					return n;
			}



      function timeDurationSince(milliseconds) {

      var date = new Date();
      var timestamp = date.getTime();
      console.log("timestamp: "+timestamp);
      console.log("milliseconds: "+milliseconds);
      var seconds = (timestamp - milliseconds)/1000;
      var interval = Math.floor(seconds / 31536000);

      // console.log("milliseconds: "+milliseconds);
      // console.log("timestamp: "+timestamp);
      // console.log("seconds: "+seconds);
      // console.log("interval: "+interval);

      if (interval > 1) {
        return interval + " years";
      }
      interval = Math.floor(seconds / 2592000);
      if (interval > 1) {
        return interval + " months";
      }
      interval = Math.floor(seconds / 86400);
      if (interval > 1) {
        return interval + " days";
      }
      interval = Math.floor(seconds / 3600);
      if (interval > 1) {
        return interval + " hours";
      }
      interval = Math.floor(seconds / 60);
      if (interval > 1) {
        return interval + " minutes";
      }
      return Math.round(seconds,2) + " seconds";
      }

      function timeDurationSeconds(seconds) {

      var interval = Math.floor(seconds / 31536000);

      if (interval > 1) {
        return interval + " years";
      }
      interval = Math.floor(seconds / 2592000);
      if (interval > 1) {
        return interval + " months";
      }
      interval = Math.floor(seconds / 86400);
      if (interval > 1) {
        return interval + " days";
      }
      interval = Math.floor(seconds / 3600);
      if (interval > 1) {
        return interval + " hours";
      }
      interval = Math.floor(seconds / 60);
      if (interval > 1) {
        return interval + " minutes";
      }
      return Math.round(seconds,2) + " seconds";
      }

    function timeDuration(duration) {

      var seconds = duration/1000;

      var interval = Math.floor(seconds / 31536000);

      if (interval > 1) {
        return interval + " years";
      }
      interval = Math.floor(seconds / 2592000);
      if (interval > 1) {
        return interval + " months";
      }
      interval = Math.floor(seconds / 86400);
      if (interval > 1) {
        return interval + " days";
      }
      interval = Math.floor(seconds / 3600);
      if (interval > 1) {
        return interval + " hours";
      }
      interval = Math.floor(seconds / 60);
      if (interval > 1) {
        return interval + " minutes";
      }
      return Math.round(seconds,2) + " seconds";
    }

    function timeSince(date) {
      return Math.floor((new Date()/1000) - date);
    }
    function timeSinceText(date) {
      return timeDuration(timeSince(date));
    }

    function numberWithCommas(x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }

    </script>

  </body>
</html>