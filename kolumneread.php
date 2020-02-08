<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");

if (!$_REQUEST['id']) {
	$title = "KID?";
	include("header.inc.php");
	?>Welche Kolumne m&ouml;chtest du lesen ?<br>W&auml;hle eine von der <a href="kolumnelist.php">Kolumnenliste</a><?
	include("footer.inc.php");
	exit;
}

$kid = addslashes($_REQUEST['id']);

if ($_REQUEST['kommentar']) {
	$kommentar = $_REQUEST['kommentar'];
	$kommentar = trim($kommentar);
	$kommentar = htmlentities($kommentar);
	$kommentar = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a target=\"_blank\" href=\"\\0\">\\0</a>", $kommentar);
	$kommentar = preg_replace("/\&amp;([a-z0-9#]+);/i","\&\\1;", $kommentar);
	$kommentar = parsestyles($kommentar);
	$kommentar = addslashes($kommentar);
	$kommentar = substr($kommentar,0,255); 
	if (substr($kommentar,-1) == "\\") $kommentar = substr($kommentar,0,-1); // wenn das letzte Zeichen ein \ ist, muss es entfernt werden, da es sonst das SQL-' escapet.

	if ($kommentar) { // nicht leer
		$sql = "INSERT kolumne_comments (KID,UID,kommentar) VALUES ('$kid','".$_SESSION['UID']."','$kommentar');";
		$result = mysql_query($sql);
	}
	if (!$result) $failedflag = 1;
};

$sql = "SELECT kolumne_data.*,chat_user.username, kolumne_data.autor AS UID FROM kolumne_data LEFT JOIN chat_user ON kolumne_data.autor = chat_user.UID WHERE kolumne_data.KID = '$kid' LIMIT 1;";
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$row = array_map("stripslashes",$row);

$title = "Kolumne lesen";
include("header.inc.php");
?>
<table border="1" cellpadding="3">
<tr><td align="left"><a href="kolumnelist.php">Kolumnenliste</a></td><td align="right"><a href="kolumneadd.php">Kolumne schreiben</a></td></tr>
<tr><td colspan="2">
<table border="0" cellpadding="3">
<tr><td>Geschrieben von:</td><td><?=($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID']) ?></td></tr>
<tr><td>Geschrieben am:</td><td><?=ts2str($row['posttime']) ?></td></tr>
<tr><td>Der Anlass des Ausgangs:</td><td><?=$row['anlass'] ?></td></tr>
<tr><td>Das Datum des Ausgangs:</td><td><?=d2str($row['anlassdate']) ?></td></tr>
<tr><td>Dabei waren:</td><td>
<?
	$sql = "SELECT kolumne_anwesend.UID,chat_user.username FROM kolumne_anwesend LEFT JOIN chat_user ON kolumne_anwesend.UID = chat_user.UID WHERE kolumne_anwesend.KID = '$kid' ORDER BY username ASC;";
	$result = mysql_query($sql);
	
	$x = 0;
	while ($user = mysql_fetch_array($result)) echo ($x++ ? ", " : "").($user['username'] && $user['UID'] != 0 ? "<a href=\"userinfo.php?id={$user['UID']}\">".htmlentities($user['username'])."</a>" : "User#".$user['UID']);
	if ($row['andere']) echo ", ".$row['andere'];
?>
</td></tr>
<tr><td></td><td></td></tr>
<tr><td valign="top">Die Kolumne:</td><td><b><?=($row['titel']) ?></b><br><br><?=($row['kolumne']) ?></td></tr>
</table>
</td></tr>
<tr><td colspan="2">

Kommentare:<br>
<ul>
<?
$sql = "SELECT kolumne_comments.*,chat_user.username FROM kolumne_comments LEFT JOIN chat_user ON chat_user.UID = kolumne_comments.UID WHERE kolumne_comments.KID = '$kid' ORDER BY kolumne_comments.posttime ASC;";
$result = mysql_query($sql);
while ($row = mysql_fetch_array($result)) {
	if (substr(stripslashes($row['kommentar']),0,4) == "/me ") echo "<li> <i>".($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])." ".substr(stripslashes($row['kommentar']),4)."</i> <font size=\"-2\">(am ".ts2str($row['posttime']).")</font>\n";
	else echo "<li> ".stripslashes($row['kommentar'])." <font size=\"-2\">(von ".($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])." am ".ts2str($row['posttime']).")</font>\n";
}
?></ul><br>
Kommentar <font size="-2">(du bist eingeloggt als: <?=$_SESSION['username'] ?>)</font>: 
<form method="post" action="?id=<?=$kid ?>">
	<input type="text" name="kommentar"><input type="submit" value="Go!">
</form>

</td></tr>
</table>
<?
include("footer.inc.php");
if (!in_array($kid,$_SESSION['seenkolumnen'])) array_push($_SESSION['seenkolumnen'],$kid);

?>
