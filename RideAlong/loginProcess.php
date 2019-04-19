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
  if($Pass == $UserPass){
    redirect("driver.html");
  }
  else{
    echo "INVALID USERNAME OR PASSWORD";
  }

  $conn->close();
}


?>
