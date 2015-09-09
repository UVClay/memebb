<?php 
//just a template page so it is fine - clay
define('IN_MYBB', 1); 
require "./global.php";
if($mybb->user['uid'] < 1) error_no_permission();
$uploadHandler = 'http://' . $_SERVER['HTTP_HOST'] . $directory_self . '/flagupload.php';
$userid = $mybb->user['uid'];
$max_file_size = 512000;
$max_file_size_kbs = $max_file_size / 1024;
$directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
add_breadcrumb("Upload A Flag ON TO THE INTENRET?!@", "flagzone.php"); 
eval("\$html = \"".$templates->get("flagzone")."\";"); 
output_page($html);
?>
