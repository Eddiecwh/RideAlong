<?php

function redirect($url) {
    ob_start();
    header('Location: '.$url);
    ob_end_flush();
    die();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $DriverID = $_POST["Name"];
  $VID = $_POST["DOB"];
  $Pickup = $_POST["Sex"];
  $Destination = $_POST["Pass"];
  $Time = ;

  $conn = new mysqli('pippin.sewanee.edu', 'webUser', 'webLogPass', 'RideAlongUsers');

  if ($conn->connect_error) {
    die('Error : ('. $conn->connect_errno .') '. $conn->connect_error);
  }
  else{
    echo "CONNECTED!<br>";
  }
  //Insert Owner
  $sql = "INSERT INTO Trips (Driver, VID, Pickup, Destination, Time) VALUES ('$DriverID', '$VID', '$Pickup', '$Time)";

  if ($conn->query($sql) === TRUE) {
    echo "-> Created Trip record...<br>";
    redirect("driver.html");
  } 
  else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();



?>
