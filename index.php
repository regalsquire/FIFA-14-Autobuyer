<?php
require "conector.php";


$datos = array(
            "username" => "xx@xx.com",
            "password" => "xxxxxx",
            "platform" => "ps3", // 360, pc
            "hash" => "xxxx",  // answer in hash
            );
$connector = new Connector($datos);
$con = $connector->Connect();



echo "NUCLEUS ID: ";
echo $con["nucleusId"];

echo "<br><br>";

echo "USER ACCOUNTS:";
 print_r($con["userAccounts"]);

echo "<br><br>";

 echo " SID: ";
 print_r($con["sessionId"]);

 echo "<br><br>";

 echo " TOKEN: ";
  print_r($con["phishingToken"]);

echo "<br><br>";

echo "COOKIES: ";
  print_r($con["cookies"]);

 
?>
