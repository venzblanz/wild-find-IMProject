<?php
    $connection = new mysqli('localhost', 'root', 'bc_db')

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
?>