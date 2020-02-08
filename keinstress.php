<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");

$maxanzahl = 500; // constant

$sql = "SELECT count(PID) FROM chat_data WHERE 1;";
$result = mysql_query($sql);
$temp = mysql_fetch_row($result);
$numrows = $temp[0];

if (!isset($_SESSION['anzahl'])) $_SESSION['anzahl'] = 200; //default
if ($_REQUEST['anzahl']) {
	$_SESSION['anzahl'] = (integer)addslashes(trim($_REQUEST['anzahl']));
	if ($_SESSION['anzahl'] > $maxanzahl) $_SESSION['anzahl'] = $maxanzahl; // nur begrenzt viele zeilen
	$_SESSION['anzahl'] = abs($_SESSION['anzahl']);
}

if (!isset($_SESSION['offset'])) $_SESSION['offset'] = 0; //default
if ($_REQUEST['offset'] || $_SESSION['offset']) {
	$_SESSION['offset'] = (integer)addslashes(trim($_REQUEST['offset']));
	if ($_SESSION['offset'] > ($numrows - 1)) $_SESSION['offset'] = $numrows - $_REQUEST['anzahl'];
	if ($_SESSION['offset'] < 0) $_SESSION['offset'] = 0;
}

if (!isset($_SESSION['desc'])) $_SESSION['desc'] = "false"; //default
if ($_REQUEST['desc']) $_SESSION['desc'] = $_REQUEST['desc'];

if ($_REQUEST['changedirection']) {
	$_SESSION['offset'] = $numrows - $_SESSION['offset']- $_SESSION['anzahl']; // neue position (von hinten her gerechnet)
	if ($_SESSION['desc'] == "true") $_SESSION['desc'] = "false";
	else $_SESSION['desc'] = "true";
}

if ($_REQUEST['gofirst']) {
	$_SESSION['offset'] = 0; // piece 'o cake..
}

if ($_REQUEST['golast']) {
	$_SESSION['offset'] = $numrows - (($numrows - ($_SESSION['offset'] % $_SESSION['anzahl'])) % $_SESSION['anzahl']);
}

if ($_REQUEST['goleft']) {
	$_SESSION['offset'] = $_SESSION['offset'] - $_SESSION['anzahl'];
	if ($_SESSION['offset'] < 0) $_SESSION['offset'] = 0;
}

if ($_REQUEST['goright']) {
	$_SESSION['offset'] = $_SESSION['offset'] + $_SESSION['anzahl'];
	if ($_SESSION['offset'] > ($numrows - 1)) $_SESSION['offset'] = $numrows - $_REQUEST['anzahl'];
}

if ($_REQUEST['gopage']) {
	if ($_REQUEST['gopage'] == 1) $_SESSION['offset'] = 0;
	else {
		if ($_SESSION['offset'] % $_SESSION['anzahl']) $_SESSION['offset'] = ($_SESSION['offset'] % $_SESSION['anzahl']) + ($_SESSION['anzahl'] * ($_REQUEST['gopage'] - 2));
		else $_SESSION['offset'] = ($_SESSION['anzahl'] * ($_REQUEST['gopage'] - 1));
	}
}

if ($_REQUEST['goid']) {
	$id = addslashes((integer)$_REQUEST['goid']);
	
	if ($_SESSION['desc'] == "true") $sign = ">";
	else $sign = "<";
	
	$sql = "SELECT count(a.pid) FROM chat_data a, chat_data b WHERE a.posttime $sign b.posttime AND b.pid = $id;";
	$result = mysql_query($sql);
	$position = mysql_fetch_row($result);
	$position = $position[0] + 1;
	if (($position - ($_SESSION['anzahl'] / 2)) <= 0) $_SESSION['offset'] = 0;
	elseif (($position + ($_SESSION['anzahl'] / 2)) > $numrows) $_SESSION['offset'] = $numrows - $_SESSION['anzahl'];
	else $_SESSION['offset'] = $position - ($_SESSION['anzahl'] / 2);
}

$title = "Der Ganze Chat";
include("header.inc.php");

buildsearchmenu();

echo "<ul>\n";

$sql = "SELECT chat_data.*,chat_user.username FROM chat_data LEFT JOIN chat_user ON chat_data.UID = chat_user.UID ORDER BY chat_data.posttime ";

if ($_SESSION['desc'] == "true") $sql .= "DESC ";
else $sql .= "ASC ";

$sql .= "LIMIT {$_SESSION['offset']},{$_SESSION['anzahl']};";

$result = mysql_query($sql);

while ($row = mysql_fetch_array($result)) {
	if ($id == $row['PID']) echo "<a name=\"{$row['PID']}\">";
	echo ($id == $row['PID'] ? "<font color=\"red\">" : ($_SESSION['UID'] == $row['UID'] ? "<font color=\"green\">" : ""));

	if (substr($row['kommentar'],0,4) == "/me ") echo "<li> <i>".($row['username'] && $row['UID'] != 0 ? "<a target=\"_blank\" href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])." ".substr(stripslashes($row['kommentar']),4)."</i> <font size=\"-2\">(am ".ts2str($row['posttime']).")</font>\n";
	else echo "<li> ".stripslashes($row['kommentar'])." <font size=\"-2\">(von ".($row['username'] && $row['UID'] != 0 ? "<a target=\"_blank\" href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])." am ".ts2str($row['posttime']).")</font>\n";

	if (($id == $row['PID']) || ($_SESSION['UID'] == $row['UID'])) echo "</font>";
	echo "\n";
	
}
echo "</ul>\n";

buildsearchmenu();

include("footer.inc.php");
?>