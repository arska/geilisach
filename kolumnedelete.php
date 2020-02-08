<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("admin.inc.php");

if ($_REQUEST['kill']) {
		
	$kill = addslashes($_REQUEST['kill']);
	
	$sql = "DELETE FROM kolumne_data WHERE KID = $kill;";
	if (!mysql_query($sql)) {
		$title = "Fehler";
		include("header.inc.php");
		echo "L&ouml;schen aus kolumne_data misslang";
		include("footer.inc.php");
		exit;
	}
	$sql = "DELETE FROM kolumne_anwesend WHERE KID = $kill;";
	if (!mysql_query($sql)) {
		$title = "Fehler";
		include("header.inc.php");
		echo "L&ouml;schen aus kolumne_anwesend misslang";
		include("footer.inc.php");
		exit;
	}

}


$title = "Kolumne L&ouml;schen";
include("header.inc.php");

$sql = "SELECT KID, anlass, autor, titel, anlassdate FROM kolumne_data";
$result = mysql_query($sql);

echo "<table border=\"1\">\n";
echo "<tr><td>KID</td><td>Anlass</td><td>Autor UID</td><td>titel</td><td>Datum</td><td></td></tr>";
echo "<tr></tr>";

while ($row = mysql_fetch_row($result)) {
	echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".dt2str($row[4])."</td><td><form><input type=\"submit\" name=\"kill\" value=\"".$row[0]."\"></form></td></tr>";
}

echo "</table>";

include("footer.inc.php");
