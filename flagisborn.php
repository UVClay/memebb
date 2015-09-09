<?php 
// yet another template generator - clay
define('IN_MYBB', 1); require "./global.php";
session_start();
$image = $_SESSION['image'];
add_breadcrumb("ON THIS DAY A FLAG IS BIRTHED", "flagisborn.php"); 
eval("\$html = \"".$templates->get("flagisborn")."\";"); 
output_page($html);
?>
