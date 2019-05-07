<!-- CS286-Geomapping and Location
     Eddie and Liam
     home.html -->

<html>
<head>
  <!-- CSS stylesheets -->
    <!-- For general styling -->
  <link rel="stylesheet" type="text/css" href="style.css">
    <!-- For the title font -->
  <link href='https://fonts.googleapis.com/css?family=Alfa Slab One' rel='stylesheet'>
    <!-- For the slide-out menu -->
  <link rel="stylesheet" href="sidebar_styling.css">

  <!-- Geocoding libraries -->
  <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
  <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAGPJCC0UuL1quW1tz93wX_bvON2Z7EmAA&callback=initMap"></script>

  <title>Ride Along</title>
  <meta name="viewport" content="initial-scale=1.0">
  <meta charset="utf-8">
</head>
  

<?php
  //GET UserID from cookie

  $conn = new mysqli('pippin.sewanee.edu', 'webUser', 'webLogPass', 'RideAlongUsers');

  if ($conn->connect_error) {
    die('Error : ('. $conn->connect_errno .') '. $conn->connect_error);
  }

  if(isset($_COOKIE["UserID"]))
  {
    $IDCookie = $_COOKIE["UserID"];
    $Name= $conn->query("SELECT Name FROM Users WHERE UserID LIKE '$IDCookie'")
                   ->fetch_object()->Name;
    echo '<h2 align="center">Welcome User '.$Name.'</h2>';
  }
  else{
    echo 'NO COOKIE';
  }



  //Create trip class, which will store database info in php objects
  //This will allow for parsing to JSON
  class Trip{
    //constructor - this will also handle parsing the start and end location strings
    //into separate start_lat, start_long, end_lat and end_long strings
    public function __construct($DriverID,$DriverName, $Start, $End){
      $this->Driver = $DriverID;
      $this->DriverName = $DriverName;
      //explode raw start string by the comma and space delimiter, after pulling off the start and
      //end parens
      $start_parsed = explode(", ", substr($Start, 1, strlen($Start) - 2)); 
      $end_parsed = explode(", ", substr($End, 1, strlen($End) - 2)); 
      $this->Start_Lat = $start_parsed[0];
      $this->Start_Long = $start_parsed[1];
      $this->End_Lat = $end_parsed[0];
      $this->End_Long = $end_parsed[1];
    }
  }
 
  //Create array to hold trips
  $trips = array();

  //Pull Trips from database
  $sql = "SELECT * FROM Trips";
  $result = mysqli_query($conn, $sql); // First parameter is just return of
                                       // "mysqli_connect()" function
  $nameQ = "SELECT Name FROM Trips, Users WHERE Users.UserID = Trips.UserID";
  $nameResult = mysqli_query($conn, $nameQ); 
  //create placeholders for params
  $user;
  $start_param;
  $end_param;

  $driver_names = array();

  while ($row = mysqli_fetch_assoc($nameResult)) {
    foreach($row as $field => $value){
      if($field == "Name"){
        array_push($driver_names, $value);
      }
    }
  }

  $index = 0;
  while ($row = mysqli_fetch_assoc($result)) {
    //row access
    foreach ($row as $field => $value) { 
    //column acces
    //Identify column type and asign value to proper var
      if($field == "UserID"){
        $user = $value;
      }
      if($field == "Start"){
        $start_param = $value;
      }
      if($field == "End"){
        $end_param = $value;
      }
    }
    if($user != null && $start_param != null && $end_param != null){
      array_push($trips, new Trip($user,$driver_names[$index++], $start_param, $end_param));
    }
  }
 // while ($row = mysqli_fetch_assoc($nameResult)) {
   // $driver_name = $row;
  //}
  

  //Convert to JSON 
  $trip_json = json_encode($trips);


  ?>

<script>
  // These variables are used to search at the end! 
  var startLocation;
  var endLocation;
  var dateTime;
  var dirIndex = 0;
  var map1;

  var directionsService = new google.maps.DirectionsService();
  var directionsDisplay;
  var directionsDisplays = [];
  //Populate directionsDisplay array.  Google sets a max of 10 directions to be displayed at once,
  //which is why we are hardcoding ten displays here
  for(var i = 0; i <= 10; i++){
    directionsDisplays[i] = new google.maps.DirectionsRenderer();
  }
  // Function to create map
  function initMap() {
        var latlng = new google.maps.LatLng(35.2031, -85.9211);
        var settings = {
          zoom: 17,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
                 
    map1 = new google.maps.Map(document.getElementById("map1"), settings);

    showTrips();
    directionsDisplay.setMap(map1);
  }
  // If flag is set to true, search for ride function will be unavailable
  var flag = true;
  function w3_open() {
    document.getElementById("mySidebar").style.display = "block";
  }
  function w3_close() {
    document.getElementById("mySidebar").style.display = "none";
  } 
  function open_start() {
    var prompt1 = window.prompt("Enter your starting location here:", "");
    if (prompt1 == null || prompt1 == "") {
      prompt1 = "No location was entered";
      startLocation = prompt1;
      flag = true;
    } else {
      startLocation = prompt1;
      flag = false;
    }
    document.getElementById("start").innerHTML = startLocation;
  }
  function open_end() {
    var prompt2 = window.prompt("Enter your end location here:", "");
    if (prompt2 == null || prompt2 == "") {
      prompt2 = "No location was entered";
      endLocation = prompt2;
      flag = true;
    } else {
      endLocation = prompt2;
      flag = false;
    }
    document.getElementById("end").innerHTML = endLocation;
  }
  function open_date() {
    var prompt3 = window.prompt("Enter your date and time in this format e.g 'December 17, 2019 03:24:00'", "");
    if (prompt3 == null || prompt3 == "") {
      prompt3 == "No date or time was entered";
      dateTime = prompt3;
      flag = true;
    } else {
      dateTime = prompt3;
      flag = false;
    } 
    var text = 'Your selected date & time is: ' + dateTime;
    document.getElementById("chosenDate").innerHTML = text;
  }
  function locateAddress(input) {
  geocoder = new google.maps.Geocoder();
  geocoder.geocode({ 'address': input }
  , function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      var msg = '<b>Found ' + results.length + ' addresses</b><br/>';
      if (results.length > 1) {
        msg += '<ol>';
        for (i = 0; i < results.length; i++) {
          msg += '<li><a href="javascript:goto' 
                  + results[i].geometry.location.toString() 
                  + '">' + results[i].formatted_address + '</a>'
                  + '</li>'
        }
        msg += '</ol>';
        //lkd, sigh
        // msg += '<div style="text-align:right; width:95%"><a href="javascript:hideMain()"><b>Close this dialog</b></a></div>';
        msg += '<a class=aCloser href="javascript:hideMain()">Close this dialog</a>';
        // Display the floating window's contents
        document.getElementById("main").innerHTML = msg;
        // Make the floating window visible
        document.getElementById("window").style.visibility = 'visible';
        // Set the height of the floating window so that it displays all addresses
        // The following statement uses jQuery to retrieve the height of certain 
        // elements on the page, since we have included the necessary jQuery 
        // scripts to make the data window draggable.
          /* argh height:92px;  world work fine EXCEPT author overwrites in his styling for both #main & #top. Sheesh!!! */
          // how if found out! 
          // document.getElementById("main").innerHTML += "<br>"+ $("#main").outerHeight()
          //                                              + ", "+ $("#top").outerHeight();
        document.getElementById("window").style.height = $("#main").outerHeight()
                                                         + $("#top").outerHeight() + 11;
        } 
        else {
          map.setCenter(results[0].geometry.location);
          map.setZoom(15);
        }
      }// end status OK
    }); // end geocoder.geocode function
  }// end locateAddress

  // This random color generator function was obtained from 
  //https://stackoverflow.com/questions/1484506  /random-color-generator, made by user Anatoliy
  function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++) {
      color += letters[Math.floor(Math.random() * 16)];
    } 
    return color;
  }

  function showDirections(start, end, name) {
    console.log("showDirections()")
    var request = {
      origin: start,
      destination: end,
      travelMode: google.maps.TravelMode.DRIVING
    };
    directionsService.route(request, function(result, status) {
      if (status == google.maps.DirectionsStatus.OK && dirIndex < directionsDisplays.length) {
        map1.fitBounds(result.routes[0].bounds);
        createPolyline(result, name);
      }

    dirIndex++;
    });

  }

  //Create polyline - creates polyline based on direction data, so that we can add our listener
  function createPolyline(directionResult, name) {
    var line = new google.maps.Polyline({
        path: directionResult.routes[0].overview_path,
        strokeColor: getRandomColor(),
        strokeOpacity: 0.5,
        strokeWeight: 4
    });
   
    line.addListener('click', function(){alert(name);});    

    line.setMap(map1);
    
  }
  //Pulls Trip information from the div created by php, then shows directions
  function showTrips(){
    //pull trip data
    var trip_json = document.getElementById("dom-target").textContent;
    var trip_data = JSON.parse(trip_json);
    //draw trip directions
    for(var i = 0; i < trip_data.length; i++){
      var trip = trip_data[i];
      var start = new google.maps.LatLng(trip.Start_Lat, trip.Start_Long); 
      var end = new google.maps.LatLng(trip.End_Lat, trip.End_Long); 
      showDirections(start, end, trip.DriverName);
    }

  }
 
</script>



<body>
  <!--For storage of trip data from php--!>
  <div id="dom-target" style="display: none;">
    <?php
      //Save json of trip data to div for access by JS
      echo htmlspecialchars($trip_json);
    ?>
  </div>
  <!-- Sidebar -->
  <div class="w3-sidebar w3-bar-block w3-border-right" style="display:none; width: 700px" id="mySidebar">
    <button onclick="w3_close()" class="w3-bar-item w3-large">Close &times;</button>
    <div id="ride">
        <h1 class="title1" style="font-family: 'Poppins', sans-serif; margin-top: 100px">Looking for a ride? Let's go!</h1>
          <button onclick="open_start()">Leaving from</button>
          <p id="start" style="font-style: italic"></p>
          <button onclick="open_end()">Going to</button>
          <p id="end" style="font-style: italic"></p>
          <hr>
          <button id="date" style="margin-top: 30px; font-style: italic" onclick="open_date()">Select your Date and Time</button>
          <p id="chosenDate"></p>
          <hr>
          <button class="button search">Click here to search for your ride</button>
    </div> 
  </div>

  <div class="topnav">
      <button class="w3-button w3-dark-grey w3-xlarge" onclick="w3_open()">☰</button>
    <div class="centerItems">
      <h5 style="font-size: 20px; font-family: 'Alfa Slab One'">RideAlong</h5>
    </div>
    <div class="rightItems">
      <a href="driver.php">Driver</a>
      <a class="active" href="passenger.php">Passenger</a>
    </div>
  </div>
  <div id="map1"></div>


</body>
</html>
