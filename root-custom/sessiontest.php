<?php

session_start();

echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";

$path = session_save_path();

echo "Directory Exists: ";
var_dump(is_dir($path));

echo "<br>Writable: ";
var_dump(is_writable($path));

$_SESSION['test'] = time();

echo "<br>Session ID: " . session_id();
