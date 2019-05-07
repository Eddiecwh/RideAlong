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
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAGPJCC0UuL1quW1tz93wX_bvON2Z7EmAA"></script>

  <!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAGPJCC0UuL1quW1tz93wX_bvON2Z7EmAA"></script>-->

  <title>Ride Along</title>
  <meta name="viewport" content="initial-scale=1.0">
  <meta charset="utf-8">
</head>

<script>
  var directionsDisplay;
  var directionsService = new google.maps.DirectionsService();
  var start,end;
  var map;

  // Function to create map
  function initMap() {
        directionsDisplay = new google.maps.DirectionsRenderer();
        var iniLat = 35.2031;
        var iniLon = -85.9211;

        var latlng = new google.maps.LatLng(iniLat, iniLon);
        var settings = {
          zoom: 17,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          zoomControl: true, 
          scaleControl: true, 
          streetViewControl: false, 
          overviewMapControl: false
        };
                 
    map = new google.maps.Map(document.getElementById("map1"), settings);

    google.maps.event.addListener(
          map, 'bounds_changed', function() {
                           drawCrosshair()
    });

    directionsDisplay.setMap(map);
  }

  function w3_open() {
    document.getElementById("mySidebar").style.display = "block";
  }

  function w3_close() {
    document.getElementById("mySidebar").style.display = "none";
  } 

  // crosshair cursor support variables
  var crossHair = true;
  var centerHLine;
  var centerVLine;

  function drawCrosshair() {
        if (centerHLine != null)
            centerHLine.setMap(null);
        if (centerVLine != null)
            centerVLine.setMap(null);

        var bounds = map.getBounds();
        var sw = bounds.getSouthWest();
        var ne = bounds.getNorthEast();
        var minLat = sw.lat(); var maxLat = ne.lat();
        var minLon = sw.lng(); var maxLon = ne.lng();
        var mapCenter = map.getCenter();
        var ctrLat = mapCenter.lat(); var ctrLon = mapCenter.lng();
        var centerCoordinatesV = [new google.maps.LatLng(minLat, ctrLon),
                                new google.maps.LatLng(maxLat, ctrLon)];
        var centerCoordinatesH = [new google.maps.LatLng(ctrLat, minLon),
                                new google.maps.LatLng(ctrLat, maxLon)];
        centerVLine = new google.maps.Polyline({
            path: centerCoordinatesV,
            strokeColor: "#FF0000", strokeOpacity: 1.0, strokeWeight: 0.75
        });
        centerHLine = new google.maps.Polyline({
            path: centerCoordinatesH,
            strokeColor: "#FF0000", strokeOpacity: 1.0, strokeWeight: 0.75
        });
        centerVLine.setMap(map);
        centerHLine.setMap(map);
  }
  
  function setOrigin() {
    console.log("setOrigin()");
    start=map.getCenter();
    document.cookie="start=" + start;
    document.getElementById('origin').value = start;
  }

  function setDestination() {
    console.log("setDestination()");
    end=map.getCenter();
    document.cookie="end=" + end;
    document.getElementById('destination').value = end
  }

   function showDirections() {
    console.log("showDirections()")
    var request = {
      origin: start,
      destination: end,
      travelMode: google.maps.TravelMode.DRIVING
    };
  
    directionsService.route(request, function(result, status) {
      if (status == google.maps.DirectionsStatus.OK) {
        directionsDisplay.setDirections(result);
        w3_close();
      }
    });
  }

</script>

<body onload="initMap();">

  <!--Identify User
  Uses cookie set by login script to identify the current user 
  -->

  <?php
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

  //ADD TRIP
  //Gets trip start and end from cookies, then inserts to Trips table


  if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['addTrip']))
  {
    $validTrip = true;

    if(isset($_COOKIE["start"]))
    {
      $StartCookie = $_COOKIE["start"];
    }
    else{
      $validTrip = false;
    }
    if(isset($_COOKIE["end"]))
    {
      $EndCookie = $_COOKIE["end"];
    }
    else{
      $validTrip = false;
    }
    if(!isset($_COOKIE["UserID"])){
      $validTip = false;
    }
    //Insert Trip record
    if($validTrip){
      $sql = "INSERT INTO Trips (UserID, Start, End) VALUES ('$IDCookie', '$StartCookie', '$EndCookie')";    

      if ($conn->query($sql) === TRUE) {
        echo "-> Created trip record...<br>";
      }
      else {
        echo "Error: " . $sql . "<br>" . $conn->error;
      }
    }
  }
  ?>


  <!-- Sidebar -->
  <div id="mySidebar" class="w3-sidebar w3-bar-block w3-border-right" style="display:none">
    <button onclick="w3_close()" class="w3-bar-item w3-large">Close &times;</button>
    <div id="label"> 
      <button onclick="setOrigin()">Set Origin</button>           
        <input id="origin" value="Enter your starting point here">

        <br>

        <button onclick='setDestination()'>Set Destination</button> 
        <input id="destination" value="Enter your destination here">

       <br>
       <button id="showme" action="" onclick='showDirections()'>Show directions</button>
       <br>
       <form action="driver.php" method="post">
         <button name="addTrip">Add Trip</button> 
       </form>
    </div>
  </div>

  <div class="topnav">
      <button class="w3-button w3-dark-grey w3-xlarge" onclick="w3_open()">☰</button>
    <div class="centerItems">
      <h5 style="font-size: 20px; font-family: 'Alfa Slab One'">RideAlong</h5>
    </div>
    <div class="rightItems">
      <a class="active" href="driver.html">Driver</a>
      <a href="passenger.php">Passenger</a>
    </div>
  </div>
  <div id="map1"></div> <!-- Div for map -->

 <!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAGPJCC0UuL1quW1tz93wX_bvON2Z7EmAA&callback=initMap"
    async defer></script>-->

</body>
</html>

        
