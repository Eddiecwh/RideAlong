
<!-- CS286-Geomapping and Location
     Eddie and Liam
     home.html -->
<?php

function redirect($url) {
    ob_start();
    header('Location: '.$url);
    ob_end_flush();
    die();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $Name = $_POST["Name"];
  $DOB = $_POST["DOB"];
  $Sex = $_POST["Sex"];
  $Pass = $_POST["Pass"];

  $conn = new mysqli('pippin.sewanee.edu', 'webUser', 'webLogPass', 'RideAlongUsers');

  if ($conn->connect_error) {
    die('Error : ('. $conn->connect_errno .') '. $conn->connect_error);
  }
  else{
    echo "CONNECTED!<br>";
  }
  //Insert Owner
  $sql = "INSERT INTO Users (Name, DOB, Sex, Password) VALUES ('$Name', '$DOB', '$Sex', '$Pass')";

  if ($conn->query($sql) === TRUE) {
    echo "-> Created owner record...<br>";
    redirect("driver.php");
  } 
  else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
}


?>
