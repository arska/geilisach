<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("admin.inc.php");

if ($_REQUEST['kill']) {
	$sql = "DELETE FROM party_comments WHERE CID = ".addslashes($_REQUEST['kill'])." LIMIT 1;";
	mysql_query($sql);
}
$title = "Event-Kommentare l&ouml;schen";
include("header.inc.php");

echo "<form>";
echo "Filter Anlass: <select name=\"aid\" onChange=\"document.forms[0].submit()\" size=\"1\"><option value=\"0\">--w&auml;hle einen Anlass--</option>";
	$sql = "SELECT AID,anlass FROM party_data ORDER BY anlasstime DESC;";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		echo "<option value=\"".$row['AID']."\"";
		if ($_REQUEST['aid'] == $row['AID']) echo " selected=\"selected\"";
		echo ">".stripslashes($row['anlass'])."</option>";
	}
	echo "</select><input type=\"submit\" value=\"Go!\">";
echo "</form>";

$sql = "SELECT party_comments.*, party_data.anlass, chat_user.username FROM party_data, party_comments LEFT JOIN chat_user ON party_comments.UID = chat_user.UID WHERE party_comments.AID = party_data.AID";
if ($_REQUEST['aid']) $sql .= " AND party_comments.AID = ".addslashes($_REQUEST['aid']);
$sql .= " ORDER BY party_comments.posttime ASC;";

$result = mysql_query($sql);

echo "<form>";
echo "<table border=\"1\">";
echo "<tr><td>Anlass</td><td>User</td><td>Kommentar</td><td>Datum</td><td>KILL!</td></tr>";
while ($row = mysql_fetch_array($result)) {
	echo "<tr><td>".stripslashes($row['anlass'])."</td><td>".($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])."</td><td>".stripslashes($row['kommentar'])."</td><td>".ts2str($row['posttime'])."</td><td><input type=\"submit\" name=\"kill\" value=\"".stripslashes($row['CID'])."\"></td></tr>";
}
echo "</table>";
echo "</form>";

include("footer.inc.php");

?>