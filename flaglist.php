<?php
/*****************
krad flag list
version im notupdating this fuck you
clay, sides, ice

sides - redid the whole fucking script basically and deletion shit
sides - added filtering and actually fixed sorting
sides - fixed sorting
sides - added sorting
ice - fixed the page number shit
clay - whatever it works fuck all of you
******************/


define('IN_MYBB', 1);
require "global.php";
add_breadcrumb("Viewing Flags", "flagslist.php");
global $db;

// real quick flag permission check
function realQuickFlagPermissionCheck($action, $user, $flagFilename = null, $flag = null) {
    global $db;
  switch($action) {
    case "restore":
    case "delete":
        if ($user["usergroup"] === 1)
            return true;
        else if ($flag !== null)
            return $user["uid"] === $flag["upload_uid"];
        else if ($flagFilename !== null) {
            die('a');
            $result = $db->fetch_array($db->query("SELECT upload_uid FROM ".TABLE_PREFIX."flags WHERE filename = \"$flagFilename\""));
            return $result && $user["uid"] === $result["upload_uid"];
        }
        else
            return false;
    case "permdelete":
      return $user["usergroup"] === 1;
    default:
      return false;
  }
}
 
// Config
// * * * * * * * * *
$dir = '/images/basement/';
$flagsperpage = 20;
$pageinglength = 5;
$messages = array();


// Flag actions
// * * * * * * * * *
if (isset($_GET["delete"])) {
    $delete = $db->escape_string(trim($_GET["delete"]));
  if (!empty($delete) && realQuickFlagPermissionCheck("delete", $mybb->user, $delete)) {
    $db->query("
      ALTER ".TABLE_PREFIX."flags
      SET enabled = 0
      WHERE filename = \"$delete\"
    ");
    $messages[] = "Sayonara $delete";
  } else {
    $messages[] = "You don't have permission to delete that flag";
  }
} else if (isset($_GET["permdelete"])) {
    $permdelete = $db->escape_string(trim($_GET["permdelete"]));
  if (!empty($permdelete) && realQuickFlagPermissionCheck("permdelete", $mybb->user, $permdelete)) {
    // todo whoa this unlink won't work need root dir of server probably in $mybb somewhere
    //if (unlink($dir.$permdelete)) {
    //  $db->query("
    //    DELETE FROM ".TABLE_PREFIX."flags
    //    WHERE filename = \"$permdelete\"
    //  ");
    //  $messages[] = "Rest in peace $permdelete, you will (won't) be missed";
    //} else {*/
      $messages[] = "Something went wrong trying to delete the flag";
    //}
  } else {
    $messages[] = "You don't have permission to delete that flag";
  }
} else if (isset($_GET["restore"])) {
    $restore = $db->escape_string(trim($_GET["restore"]));
    if (!empty($restore) && realQuickFlagPermissionCheck("restore", $mybb->user, $restore)) {
        $db->query("
            ALTER ".TABLE_PREFIX."flags
            SET enabled = 1
            WHERE filename = \"$restore\"
        ");
        $messages[] = "We can rebuild $restore we have the technology";
    } else {
        $messages[] = "You don't have permission to restore that flag";
    }
}
 


// Display flags
// * * * * * * * * *
$desc = isset($_GET["desc"]) && $_GET["desc"] !== "0" ? 1 : 0;
$pagenumber = isset($_GET["page"]) ? intval(trim($_GET["page"])) : 0;
$filter = isset($_GET["filter"]) ? $_GET["filter"] : null;
$displaynumber = $pagenumber + 1;
$offset = $pagenumber * $flagsperpage;
$end = $offset + $flagsperpage;
 
// Begin the filtering procedure. This is an important, and professional, aspect of the code; it filters users.
if (!empty($filter)) {
  $filter = $db->escape_string(str_replace(" ", "+", $filter));
  $subtractThese = str_replace("-", "\",\"", trim(preg_replace('/-{2,}/', '-', preg_replace('/(^[^-]|\+)[^-+]*/', '', $filter)), " \t\n\r\0\x0B+-"));
  $addThese = str_replace("+", "\",\"", trim(preg_replace('/\+{2,}/', '+', preg_replace('/-[^-+]*/', '', $filter)), " \t\n\r\0\x0B+-"));
}
 
$condition = "(enabled='1'".(!empty($subtractThese) ? "
        AND ".(preg_match('/^\d+"?/', $subtractThese) ? "u.uid" : "u.username")." NOT IN (\"".$subtractThese."\")" : "").(!empty($addThese) ? "
        AND ".(preg_match('/^\d+"?/', $addThese) ? "u.uid" : "u.username")." IN (\"".$addThese."\")" : "").")";

$total = $db->fetch_array($db->query("
  SELECT COUNT(*) as total
  FROM ".TABLE_PREFIX."flags AS f
  INNER JOIN ".TABLE_PREFIX."users AS u ON (u.uid = f.upload_uid)
  WHERE $condition
"))["total"];
 
 
// PAGEING STUFF HERE
 
 
$prevpage = $pagenumber - 1;
$nextpage = $pagenumber + 1;
 
$total_pages = floor($total / $flagsperpage);
 
// hte good shit goes here breh!
 
//ice is a lazy fucker
//$total_pages++;
 
 $pageing = $desc ? "<a href=\"?page=$pagenumber&desc=0&filter=$filter\">&#9650;</a>" : "<a href=\"flaglist.php?page=$pagenumber&desc&filter=$filter\">&#9660;</a>&nbsp;";
 
if ($pagenumber > 0)
  $pageing .= "<a href=\"flaglist.php?page=0&desc=$desc&filter=$filter\">« First</a> <a href=\"flaglist.php?page=$prevpage&desc=$desc&filter=$filter\">« Previous</a>";
for ($i = max($pagenumber - $pageinglength, 0), $l = $pagenumber; $i < $l; ++$i)
  $pageing .= "&nbsp;<a href=\"flaglist.php?page=$i&desc=$desc&filter=$filter\">$i</a>";
 
$pageing .= "&nbsp;<strong>$pagenumber</strong>&nbsp;";
 
for ($i = $pagenumber + 1, $l = min($i + $pageinglength, $total_pages + 1); $i < $l; ++$i)
  $pageing .= "<a href=\"flaglist.php?page=$i&desc=$desc&filter=$filter\">$i</a>&nbsp;";
if ($total > $end + 1)
  $pageing .= "<a href=\"flaglist.php?page=$nextpage&desc=$desc&filter=$filter\">Next »</a> <a href=\"flaglist.php?page=$total_pages&desc=$desc&filter=$filter\">Last »</a>";
       
//hahahahahah
//$total_pages--;
 
// end the good shit that will definitely break the forums
 
 
 
//END PAGEING
 
$query = $db->query("
     SELECT f.*, u.username
     FROM ".TABLE_PREFIX."flags AS f
     INNER JOIN ".TABLE_PREFIX."users AS u ON (u.uid = f.upload_uid)
     WHERE $condition
     ORDER BY f.upload_date ".($desc ? "DESC" : "ASC")."
     LIMIT {$flagsperpage}
     OFFSET {$offset}
");
 
 if (empty($messages))
    $messages = "";
else
    $messages = "<p>".implode("</p><p>", $messages)."</p>";

$flaglist = '<table style="width:60%;table-layout:fixed;">';
 
$displayedflags = 0;
while ($flag = $db->fetch_array($query)) {
                if($displayedflags % 2 == 0):
                        $flaglist .= "<tr>";
                endif;
        $flaglist .= "<td><a href=\"$dir{$flag['filename']}\" target=\"_blank\"><img style=\"max-width:100%;\" src=\"$dir{$flag['filename']}\"></a><br />
     Uploaded by: <a href=\"http://kickinrad.tv/member.php?action=profile&uid={$flag['upload_uid']}\">{$flag['username']}</a><br />
     Uploaded on: ".my_date($mybb->settings["dateformat"], $flag["upload_date"])."<br />
     [ <a href=\"flaglist.php?page=0&desc=$desc&filter={$flag['upload_uid']}\" title=\"Show all flags by this user\">U</a>".(realQuickFlagPermissionCheck("delete", $mybb->user, null, $flag) ?
      " <a href=\"flaglist.php?page=$pagenumber&desc=$desc&filter=$filter&delete={$flag['filename']}\" title=\"Delete this flag\">D</a>" : "")." ]
     <br /><br /></td>";
                if($displayedflags % 2 == 1):
                        $flaglist .= "</tr>";
                endif;
        $displayedflags++;
}
if($displayedflags % 2 == 1):
        $flaglist .= '</tr>';
endif;
$flaglist .= '</table>';
 
        eval("\$html = \"".$templates->get("flagslist")."\";");
        output_page($html);
?>
