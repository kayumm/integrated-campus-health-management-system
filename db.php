<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "ichms");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>