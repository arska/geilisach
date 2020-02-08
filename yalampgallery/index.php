<?
include("includes.inc.php");
$onlytestforadminbit = true;
include("admin.inc.php");

$perrow = 3;

$title = "Liste der Alben";
include("header.inc.php");

$sql = "SELECT * FROM album_info WHERE visible = 1 ORDER BY anlasstime DESC;";
$result = mysql_query($sql);

$numrows = mysql_num_rows($result);

echo "<table border=\"1\">";

echo "<tr><td>";
echo "Liste der Alben ($numrows Alben)";
echo "</td>";

if ($adminbit) {
	echo "<td align=\"right\"><a href=\"input.php\">Neues Bild hochladen</a></td>";
	echo "<td align=\"right\"><a href=\"editalbum.php?new=true\">Neues Album erstellen</a></td>";
}
if (!$_SESSION['UID']) echo "<td align=\"right\"><a href=\"../login.php?url=".urlencode($_SERVER['PHP_SELF'])."\">Login</a></td>";

echo "</tr>";

echo "<tr><td colspan=\"".($adminbit ? "3" : ((!$_SESSION['UID']) ? "2" : "1"))."\" align=\"center\">";
echo "<table border=\"1\">\n";

flush();

for ($i=0; $i< (ceil($numrows/$perrow)*$perrow); $i++) { // anzahl elemente in der matrix (inkl. der leeren elemente)
	if ($i%$perrow == 0) echo "<tr>\n";
	echo "<td align=\"center\" valign=\"middle\">\n";
	if ($i < $numrows) { // wenn es noch records gibt, füllen wir daten, sonst nicht
		$row = mysql_fetch_array($result);
		getimage($row['bild_ID'],"showalbum.php?id=".$row['ID'],200); // thumb-grösse
		echo "<br><a href=\"showalbum.php?id=".$row['ID']."\">".stripslashes($row['anlass'])." (".stripslashes($row['ort']).")</a>";
		echo "<br><a href=\"showalbum.php?id=".$row['ID']."\">".unix2shortstr(dt2unix($row['anlasstime']))."</a>";
	}
	echo "</td>\n";
	if ($i%$perrow == ($perrow - 1)) echo "</tr>\n";
	flush();
}

echo "</table>";
echo "</td></tr>";
echo "</table>\n";

flush();

echo "<!-- ";

$sql = "SHOW TABLE STATUS LIKE 'bilder_data';";
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
echo "Bilder-Data: ".round($row['Data_length']/pow(1024,2),3)." MiB";

echo " -->";

include("footer.inc.php");
?>