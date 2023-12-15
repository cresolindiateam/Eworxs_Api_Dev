<?php
 
function connect_db() {
    // $server = 'localhost'; // this may be an ip address instead
    // $user = 'eworxs';
    // $pass = 'Dgoud123!@#';
    // $database = 'EworxsDB'; // name of your database
    //   // Create connection

      $server = 'localhost'; // this may be an ip address instead
  $user = 'deveworxs';
  $pass = 'nx]~0-(BGJ{^';
  $database = 'EworxsDevDB';

    // $server = '50.62.209.18:3306'; // this may be an ip address instead
    // $user = 'eworxs';
    // $pass = 'eworxs123!@#';
    // $database = 'EworxsDB'; // name of your database
    $connection = new mysqli($server, $user, $pass, $database);
    return $connection;
}
?>