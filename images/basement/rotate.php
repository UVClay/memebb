<?php
//$imgs = glob("*.{jpg,png,gif}", GLOB_BRACE);
//$img = array_rand($imgs, 1);

//wawawewa

if (!defined("IN_MYBB"))
{
      header("Location: http://www.kickinrad.tv/404.shtml");
      die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}


//define('IN_MYBB', 1);

$dir = 'images/basement/';
$exts = 'jpg,jpeg,png,gif';
$imgs = glob($dir . '*.{' . $exts . '}', GLOB_BRACE);
$img = $imgs[array_rand($imgs)];

echo '<img class="flagarea" src="' . $img .'" />';
?>

