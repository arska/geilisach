<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("admin.inc.php");

if ($_REQUEST['kill']) {
	$sql = "DELETE FROM kolumne_comments WHERE CID = ".addslashes($_REQUEST['kill'])." LIMIT 1;";
	mysql_query($sql);
}
$title = "Kolumnen-Kommentare l&ouml;schen";
include("header.inc.php");

echo "<form>";
echo "Filter Anlass: <select name=\"kid\" onChange=\"document.forms[0].submit()\" size=\"1\"><option value=\"0\">--w&auml;hle einen Anlass--</option>";
	$sql = "SELECT KID,anlass FROM kolumne_data ORDER BY anlassdate DESC;";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		echo "<option value=\"".$row['KID']."\"";
		if ($_REQUEST['kid'] == $row['KID']) echo " selected=\"selected\"";
		echo ">".stripslashes($row['anlass'])."</option>";
	}
	echo "</select><input type=\"submit\" value=\"Go!\">";
echo "</form>";

$sql = "SELECT kolumne_comments.*, kolumne_data.anlass, chat_user.username FROM kolumne_data, kolumne_comments LEFT JOIN chat_user ON kolumne_comments.UID = chat_user.UID WHERE kolumne_comments.KID = kolumne_data.KID";
if ($_REQUEST['kid']) $sql .= " AND kolumne_comments.KID = ".addslashes($_REQUEST['kid']);
$sql .= " ORDER BY kolumne_comments.posttime ASC;";

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