// This is my reusable database code that will be useful in all parts of my tool that use MySQL.

<?php

// My connection settings are stored here
$host = 'localhost'; // Running on localhost for this
$user = 'project_user'; // Username for MySQL
$pass = 'Pass1234'; // Password for project_user
$dbname = 'project_tool'; // This is the database name

// This is my database object for all future connections
$connection = new mysqli($host, $user, $pass, $dbname);

// If for whatever reason input is done incorrectly, an error will be printed out describing the problem. It will not continue running if this occurs, requiring a retry
if($connection->connect_error){
   die("Connection unable to establish: " . $connection->connect_error);
}
?>
