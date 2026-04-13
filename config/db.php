<?php
// File: admin_panel/config/db.php


$servername = "localhost";
$username = "u479142652_willko_dbuser";
$password = "Rajbhai@101";
$dbname = "u479142652_willko_dbname";



$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Error: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>