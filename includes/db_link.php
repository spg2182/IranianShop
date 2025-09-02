<?php
$link = new mysqli("localhost", "root", "", "iranianshop");
if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
}
?>
