<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("admin.inc.php");

$title = "Kick&Ban";
include("header.inc.php");

if ($_REQUEST['kickid'] && $_REQUEST['bantime'] && $_REQUEST['grund']) {
	$uid = (integer)$_REQUEST['kickid'];
	if ($_REQUEST['bantime'] == "ever") $banto = 0;
	else $banto = "FROM_UNIXTIME(".(time()+(integer)$_REQUEST['bantime']).")";
	$grund = addslashes(htmlentities($_REQUEST['grund']));
	$sql = "INSERT INTO chat_kickban SET UID = $uid, grund = '$grund', banto = $banto, banfrom = NOW();";
	if (!mysql_query($sql)) echo "Kickban konnte nicht  hinzugef&uuml;gt werden !<br>";
}

if ((integer)$_REQUEST['killkick']) {
	$killkick = (integer)$_REQUEST['killkick'];
	$sql = "DELETE FROM chat_kickban WHERE ID = $killkick LIMIT 1;";
	if (!mysql_query($sql)) echo "Kickban konnte nicht  gel&ouml;scht werden !<br>";
}

$kickid = (integer)$_REQUEST['kickid'];

echo "Kick:<br>";
echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"post\">";
echo "<select name=\"kickid\" size=\"1\">";
echo "<option value=\"0\">-- User W&auml;hlen --</option>";
$sql = "SELECT UID,username FROM chat_user WHERE UID > 0 AND status != 'god' ORDER BY username ASC;";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)) echo "<option value=\"{$row[0]}\"".($kickid == $row[0] ? " selected=\"selected\"" : "").">{$row[1]}</option>";
echo "</select>";
echo " f&uuml;r <select name=\"bantime\" size=\"1\">";
echo "<option value=\"0\">-- Zeit W&auml;hlen --</option>";
echo "<option value=\"60\">60s</option>";
echo "<option value=\"120\">2min</option>";
echo "<option value=\"".(5*60)."\">5min</option>";
echo "<option value=\"".(10*60)."\">10min</option>";
echo "<option value=\"".(30*60)."\">30min</option>";
echo "<option value=\"".(60*60)."\">60min</option>";
echo "<option value=\"".(2*60*60)."\">2h</option>";
echo "<option value=\"".(6*60*60)."\">6h</option>";
echo "<option value=\"".(12*60*60)."\">12h</option>";
echo "<option value=\"".(24*60*60)."\">24h</option>";
echo "<option value=\"".(2*24*60*60)."\">2d</option>";
echo "<option value=\"".(3*24*60*60)."\">3d</option>";
echo "<option value=\"".(4*24*60*60)."\">4d</option>";
echo "<option value=\"".(5*24*60*60)."\">5d</option>";
echo "<option value=\"".(6*24*60*60)."\">6d</option>";
echo "<option value=\"".(7*24*60*60)."\">7d</option>";
echo "<option value=\"".(14*24*60*60)."\">14d</option>";
echo "<option value=\"".(30*24*60*60)."\">30d</option>";
echo "<option value=\"ever\">forever</option>";
echo "</select>";
echo " mit Begr&uuml;ndung: <input type=\"text\" name=\"grund\">";
echo " <input type=\"submit\" value=\"Kick!\">";
echo "</form>";

echo "<br><br>";
echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"post\">";
echo "Liste:<br>";
$sql = "SELECT chat_kickban.*, chat_user.username FROM chat_kickban LEFT JOIN chat_user ON chat_kickban.UID = chat_user.UID ORDER BY chat_kickban.ID DESC;";
$result = mysql_query($sql);

echo "<table border=\"1\" cellpadding=\"2\">";
echo "<tr><td>Username</td><td>Ban from</td><td>Ban to</td><td>Grund</td><td>Kill</td></tr>";
echo "<tr></tr>";

while ($row = mysql_fetch_array($result)) {
	echo "<tr><td>".($row['username'] && $row['UID'] != 0 ? "<a target=\"_blank\" href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])."</td><td>".(dt2unix($row['banto']) >= time() ? "<font color=\"red\">" : "").unix2str(ts2unix($row['banfrom'])).(dt2unix($row['banto']) >= time() ? "</font>" : "")."</td><td>".(dt2unix($row['banto']) >= time() ? "<font color=\"red\">" : "").($row['banto'] == 0 ? "forever" : unix2str(dt2unix($row['banto']))).(dt2unix($row['banto']) >= time() ? "</font>" : "")."</td><td>{$row['grund']}</td><td><input type=\"submit\" name=\"killkick\" value=\"{$row['ID']}\"></td></tr>";
}

echo "</table>";
echo "</form>";
include("footer.inc.php");

?>