<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
$onlytestforadminbit = true;
include("admin.inc.php");

if (!$_REQUEST['id']) {
	$title = "AID?";
	include("header.inc.php");
	?>&Uuml;ber welchen Event mšchtest du Infos ?<br>W&auml;hle einen von der <a href="partylist.php">Eventliste</a><?
	include("footer.inc.php");
	exit;
}

$aid = addslashes($_REQUEST['id']);

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
		$sql = "INSERT party_comments (AID,UID,kommentar) VALUES ('$aid','".$_SESSION['UID']."','$kommentar');";
		$result = mysql_query($sql);
	}
}

if ($_REQUEST['vote']) {
	if ($_REQUEST['vote'] == "1") $vote = 1;
	elseif ($_REQUEST['vote'] == "-1") $vote = -1;
	else {
		$title = "vote?";
		include("header.inc.php");
		echo "Ung&uuml;ltige voteeingabe !";
		include("footer.inc.php");
		exit;
	}
	$sql = "SELECT AVID FROM party_vote WHERE UID = '".$_SESSION['UID']."' AND AID = '$aid';";
	$result = mysql_query($sql);
	if (mysql_num_rows($result)) $sql = "UPDATE party_vote SET vote = '$vote' WHERE UID = '".$_SESSION['UID']."' AND AID = '$aid' LIMIT 1;";
	else $sql = "INSERT party_vote SET vote = '$vote', UID = '".$_SESSION['UID']."', AID = '$aid';";
	//echo $sql;
	$voteresult = mysql_query($sql);
}

$sql = "SELECT party_data.*,chat_user.username,party_data.autor AS UID FROM party_data LEFT JOIN chat_user ON party_data.autor = chat_user.UID WHERE party_data.AID = '$aid' LIMIT 1;";
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$row = array_map("stripslashes",$row);

$title = "Event Infos";
include("header.inc.php");
?>
<table border="1" cellpadding="3">
<tr><td align="left"><a href="partylist.php">Eventliste</a></td><? if ($_SESSION['UID'] == $row['autor'] || $adminbit) { ?><td align="center"><a href="partyedit.php?id=<? echo $aid ?>">Event editieren</a></td><? } ?><td align="right"><a href="partyadd.php">Event hinzuf&uuml;gen</a></td></tr>
<tr><td colspan="<? if ($_SESSION['UID'] == $row['autor'] || $_SESSION['UID'] == 1) echo "3"; else echo "2"; ?>">
<table border="0" cellpadding="3">
<tr><td>Geschrieben von:</td><td><?=($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID']) ?></td></tr>
<tr><td>Geschrieben am:</td><td><?=ts2str($row['posttime']) ?></td></tr>
<tr><td>Was</td><td><?=$row['anlass'] ?></td></tr>
<tr><td>Wo</td><td><? if ($row['homepage']) echo "<a href=\"".$row['homepage']."\" target=\"_blank\">"; ?><?=stripslashes($row['ort']) ?><? if ($row['homepage']) echo "</a>"; ?></td></tr>
<tr><td>Wann</td><td><?=unix2str(dt2unix($row['anlasstime'])) ?></td></tr>
<tr><td valign="top">Was</td><td><?=stripslashes($row['beschreibung']) ?></td></tr>
<tr><td valign="top">Abstimmung</td><td>
<table>
<?
$sql = "SELECT chat_user.UID,chat_user.username,party_vote.vote FROM chat_user,party_vote WHERE chat_user.UID = party_vote.UID AND party_vote.AID = $aid ORDER BY chat_user.username ASC;";
$listresult = mysql_query($sql);
while ($user = mysql_fetch_row($listresult)) {
	$uid2name[$user[0]] = $user[1];
	$uid2vote[$user[0]] = $user[2];
}

if (!$uid2name[$_SESSION['UID']]) {
	$sql = "SELECT chat_user.UID,chat_user.username FROM chat_user WHERE UID = {$_SESSION['UID']};";
	$listresult = mysql_query($sql);
	$user = mysql_fetch_row($listresult);
	
	$uid2name[$user[0]] = $user[1];
	$uid2vote[$user[0]] = 0;
	
	natcasesort($uid2name);
}

for ($i=0; $i< count($uid2name); $i++) {
	$user = each($uid2name);
	echo "<tr><td align=\"right\"><a href=\"userinfo.php?id=".$user[0]."\">".$user[1]."</a> findet:</td><td align=\"left\">";
	
	if ($uid2vote[$user[0]] < 0) echo "<img src=\"/bilder/off.gif\">";// schlecht !";
	elseif ($uid2vote[$user[0]] > 0) echo "<img src=\"/bilder/on.gif\">";// gut !";
	else echo "<img src=\"/bilder/sb.gif\">";// (noch) nichts.";
	
	if ($_SESSION['UID'] == $user[0]) {
		echo " vote: <a href=\"".$_SERVER['PHP_SELF']."?id=$aid&vote=1\">gut</a> | <a href=\"".$_SERVER['PHP_SELF']."?id=$aid&vote=-1\">schlecht</a>";
		if ($voteresult) echo " (done)";
	}
	
	echo "</td></tr>";
}
echo "</table>";
$sql = "SELECT SUM(vote) FROM party_vote WHERE AID = '$aid';";
$result = mysql_query($sql);
$row = mysql_fetch_row($result);
echo "Alle Stimmen: ";
if ($row[0] < 0) echo "<img src=\"/bilder/off.gif\">";// schlecht !";
elseif ($row[0] > 0) echo "<img src=\"/bilder/on.gif\">";// gut !";
else echo "<img src=\"/bilder/sb.gif\">";// (noch) nichts.";

?>
</td></tr>
</table>
</td></tr>
<tr><td colspan="<?=($_SESSION['UID'] == $row['autor'] || $adminbit ? "3" : "2") ?>">

Kommentare:<br>
<ul>
<?
$sql = "SELECT party_comments.*,chat_user.username FROM party_comments LEFT JOIN chat_user ON chat_user.UID = party_comments.UID WHERE party_comments.AID = '$aid' ORDER BY party_comments.posttime ASC;";
$result = mysql_query($sql);
while ($row = mysql_fetch_array($result)) {
	if (substr(stripslashes($row['kommentar']),0,4) == "/me ") echo "<li> <i>".($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])." ".substr(stripslashes($row['kommentar']),4)."</i> <font size=\"-2\">(am ".ts2str($row['posttime']).")</font>\n";
	else echo "<li> ".stripslashes($row['kommentar'])." <font size=\"-2\">(von ".($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])." am ".ts2str($row['posttime']).")</font>\n";
}
?></ul><br>
Kommentar <font size="-2">(du bist eingeloggt als: <?=$_SESSION['username'] ?>)</font>: 
<form method="post" action="?id=<?=$aid ?>">
	<input type="text" name="kommentar"><input type="submit" value="Go!">
</form>

</td></tr>

</table>
<?
include("footer.inc.php");

if (!isset($_SESSION['seenevents'])) $_SESSION['seenevents'] = Array();
if (!in_array($aid,$_SESSION['seenevents'])) array_push($_SESSION['seenevents'],$aid);

?>
