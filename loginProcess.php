
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
  $Pass = $_POST["Pass"];
  $UserID = $_POST["UserID"];

  $conn = new mysqli('pippin.sewanee.edu', 'webUser', 'webLogPass', 'RideAlongUsers');

  if ($conn->connect_error) {
    die('Error : ('. $conn->connect_errno .') '. $conn->connect_error);
  }
  else{
    echo "CONNECTED!<br>";
  }
  //Fetch Password of User with Name
  $UserPass = $conn->query("SELECT Password FROM Users WHERE Name LIKE '$Name'")
                   ->fetch_object()->Password;
  $UserID= $conn->query("SELECT UserID FROM Users WHERE Name LIKE '$Name'")
                   ->fetch_object()->UserID;
  if($Pass == $UserPass){
    setcookie("UserID", $UserID, time()+3600);
    //redirect("driver.php");
    header("location: driver.php");
  }
  else{
    echo "INVALID USERNAME OR PASSWORD";
  }

  $conn->close();
}


?>
