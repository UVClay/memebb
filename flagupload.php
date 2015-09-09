<?php
/***************
krad flag upload preprocessor
v final
sides - adjusted uid handling
clay - fuck you it fucking works now suck my dick
clay - fixed upload bug not handling non-image images properly
clay - gutted some template code and integrated mybb shit
***************/

define('IN_MYBB', 1);
require_once("global.php");
global $db, $mybb;

// first let's set some variables

session_start();

if ($mybb->user['usergroup'] == "1" || empty($mybb->user["uid"]))
	die("you're not logged in");

// make a note of the current working directory, relative to root.
$directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);

// make a note of the directory that will recieve the uploaded files
$uploadsDirectory = 'images/basement/';

// make a note of the location of the upload form in case we need it
$uploadForm = 'http://' . $_SERVER['HTTP_HOST'] . $directory_self . 'flagzone.php';
// make a note of the location of the success page
$uploadSuccess = 'http://' . $_SERVER['HTTP_HOST'] . $directory_self . 'flagisborn.php';

// name of the fieldname used for the file in the HTML form
$fieldname = 'file';

$errors = array(1 => 'php.ini max file size exceeded', 
                2 => 'html form max file size exceeded', 
                3 => 'file upload was only partial', 
                4 => 'no file was attached'); 


// check the upload form was actually submitted else print form
isset($_POST['submit'])
	or error('the upload form is neaded', $uploadForm);

// check for standard uploading errors
($_FILES[$fieldname]['error'] == 0)
	or error($errors[$_FILES[$fieldname]['error']], $uploadForm);
	
// check that the file we are working on really was an HTTP upload
is_uploaded_file($_FILES[$fieldname]['tmp_name'])
	or error('not an HTTP upload', $uploadForm);
	
// validation... since this is an image upload script we 
// should run a check to make sure the upload is an image
$info = getimagesize($_FILES[$fieldname]['tmp_name'])
	or error('only image uploads are allowed', $uploadForm);
$imagewidth = $info[0];
$imageheight = $info[1];
	
if($imagewidth != 500) 
{
die("You're flag is the wrong size, buddy.  Resize it to 500x192");
}

if($imageheight !=192)
{
die("You're flag is the wrong size, buddy.  Resize it to 500x192");
}


// make a unique filename for the uploaded file and check it is 
// not taken... if it is keep trying until we find a vacant one

$mime = mime_content_type($_FILES[$fieldname]['tmp_name']);

switch($mime){
case "image/gif":
$ext = ".gif";
break;

case "image/jpeg":
$ext = ".jpg";
break;

case "image/pjpeg":
$ext = ".jpg";
break;

case "image/png":
$ext = ".png";
break;

default:
echo "You didn't upload a valid image.  Please don't do it again.";
}

$now = md5_file($_FILES[$fieldname]['tmp_name']);
$finalname = $uploadsDirectory.$now.$ext;
if(file_exists($finalname))
{
	die('This flag has already been uploaded you big idiot!');
}

$dbnameflagthing = $now.$ext;
// now let's move the file to its final and allocate it with the new filename
move_uploaded_file($_FILES[$fieldname]['tmp_name'], $finalname)
	or error('receiving directory insufficient permission', $uploadForm);
	
// If you got this far, everything has worked and the file has been successfully saved.
// We are now going to redirect the client to the success page.
$ts = time();
$flagstats = array(
	"filename" => $db->escape_string($dbnameflagthing),
	"upload_uid" => $mybb->user["uid"],
	"upload_date" => $db->escape_string($ts));
	
$db->insert_query('flags', $flagstats);


#file_put_contents($uploadFilename.".txt",$_POST["uid"]);

$_SESSION['image'] = $finalname;

header('Location: ' . $uploadSuccess);

?>

