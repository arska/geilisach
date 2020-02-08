<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");

$sql = "SELECT party_data.*,chat_user.username,UNIX_TIMESTAMP(party_data.anlasstime) AS anlasstime_unix,party_data.autor AS UID FROM party_data LEFT JOIN chat_user ON chat_user.UID = party_data.autor ORDER BY party_data.anlasstime DESC;";
$result = mysql_query($sql);

$title = "Eventliste";
include("header.inc.php");
?>

<table border="1" cellpadding="3">
<tr><td align="right"><a href="partyadd.php">Event hinzuf&uuml;gen</a></td></tr>
<tr><td>Events:<br>
<table border="0" cellpadding="3">
<tr><td>Anlass</td><td>Ort</td><td>Datum</td><td>Geschrieben von</td><td>Geschrieben am</td></tr>
<?

while ($row = mysql_fetch_array($result)) {
	
	if ($row['anlasstime_unix'] < time() && !$oldflag) {
		echo "<tr><td colspan=\"5\"><hr width=\"95%\" align=\"center\"></td></tr>";
		$oldflag = 1;
	}
	
	if (($row['anlasstime_unix'] - time()) < 24*3600 && ($row['anlasstime_unix'] - time()) > 0) $td = "<td bgcolor=\"#FF3333\">";
	else $td = "<td>";
	
	echo "<tr>$td";
	
	echo "<a href=\"partyread.php?id=".$row['AID']."\">".stripslashes($row['anlass'])."</a></td>$td";
	if ($row['homepage']) echo "<a href=\"".$row['homepage']."\" target=\"_blank\">";
	else echo "<a href=\"partyread.php?id=".$row['AID']."\">";
	echo stripslashes($row['ort'])."</a></td>$td<a href=\"partyread.php?id=".$row['AID']."\">".unix2str($row['anlasstime_unix'])."</a></td>$td".($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])."</td>$td<a href=\"partyread.php?id=".$row['AID']."\">".ts2str(stripslashes($row['posttime']))."</a></td></tr>";

} ?>
</table>
</td></tr>
</table>
<?
include("footer.inc.php");

// die letzten gesehenen events werden gespeichert (LIMIT sollte idealerweise max(mšglichkeiten auf last5), zZ 20)
$_SESSION['seenevents'] = array();
$sql = "SELECT AID FROM party_data ORDER BY posttime DESC LIMIT 20;";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)) array_push($_SESSION['seenevents'],$row[0]);

?>