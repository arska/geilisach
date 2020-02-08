<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("admin.inc.php");


if ($_REQUEST['kill']) {
	
	$kill = addslashes($_REQUEST['kill']);
	
	$sql = "DELETE FROM party_data WHERE AID = '$kill';";
	if (!mysql_query($sql)) {
		$title = "Fehler";
		include("header.inc.php");
		echo "L&ouml;schen aus party_data misslang<br>";
		echo $sql;
		include("footer.inc.php");
		exit;
	}
	$sql = "DELETE FROM party_vote WHERE AID = '$kill';";
	if (!mysql_query($sql)) {
		$title = "Fehler";
		include("header.inc.php");
		echo "L&ouml;schen aus party_vote misslang<br>";
		echo $sql;
		include("footer.inc.php");
		exit;
	}

}


$title = "Event L&ouml;schen";
include("header.inc.php");

$sql = "SELECT AID, anlass, autor, anlasstime FROM party_data";
$result = mysql_query($sql);

echo "<table border=\"1\">\n";
echo "<tr><td>AID</td><td>Anlass</td><td>Autor UID</td><td>Datum</td><td></td></tr>";
echo "<tr></tr>";

while ($row = mysql_fetch_row($result)) {
	echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".dt2str($row[3])."</td><td><form><input type=\"submit\" name=\"kill\" value=\"".$row[0]."\"></form></td></tr>";
}

echo "</table>";

include("footer.inc.php");