<?php
// File: admin_panel/config/db.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wilkoservices";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Error: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>