<?
include("includes.inc.php");
$onlytestforadminbit = true;
include("admin.inc.php");

$id = addslashes((integer)trim($_REQUEST['id']));
if (!$id) {
	include("header.inc.php");
	echo "Keine ID angegeben. <a href=\"index.php\">Zur&uuml;ck</a>.";
	include("footer.inc.php");
	exit;
}

$perrow = 3;
$errors = array();

if ($_REQUEST['kommentar'] && $_SESSION['UID']) {
	$kommentar = $_REQUEST['kommentar'];
	$kommentar = trim($kommentar);
	$kommentar = htmlentities($kommentar);
	$kommentar = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a target=\"_blank\" href=\"\\0\">\\0</a>", $kommentar);
	$kommentar = preg_replace("/\&amp;([a-z0-9#]+);/i","\&\\1;", $kommentar);
	$kommentar = parsestyles($kommentar);
	$kommentar = addslashes($kommentar);
	$kommentar = substr($kommentar,0,255); 
	if (substr($kommentar,-1) == "\\") $kommentar = substr($kommentar,0,-1); // wenn das letzte Zeichen ein \ ist, muss es entfernt werden, da es sonst das SQL-' escapet.
	
	if ($kommentar) {
		$sql = "INSERT INTO kommentare SET album_ID = '$id', UID = '{$_SESSION['UID']}', kommentar = '$kommentar';";
		if (!mysql_query($sql)) array_push($errors,"Kommentar konnte nicht eingef&uuml;gt werden.");
	}
}

$sql = "SELECT * FROM album_info WHERE ID = $id;";
$albumresult = mysql_query($sql);
$album = mysql_fetch_array($albumresult);
$album = array_map("stripslashes",$album);

if (mysql_num_rows($albumresult) < 1) {
	include("header.inc.php");
	echo "Keine solche ID. <a href=\"index.php\">Zur&uuml;ck</a>.";
	include("footer.inc.php");
	exit;
}


$sql = "SELECT bilder_info.* FROM bilder_info,album_bilder WHERE album_bilder.album_ID = $id AND album_bilder.bild_ID = bilder_info.ID ORDER BY bilder_info.name ASC;";
$result = mysql_query($sql);

$numrows = mysql_num_rows($result);

$title = "Album '{$album['anlass']}' ({$album['ort']})";
include("header.inc.php");
if (count($errors) > 0) print_r($errors);
echo "<table border=\"1\" cellpadding=\"3\">";
echo "<tr><td align=\"left\" colspan=\"".($adminbit ? "1" : "2")."\">Bilder von '{$album['anlass']}' am ".unix2str(dt2unix($album['anlasstime']))." in '{$album['ort']}': ($numrows Bilder)</td><td><a href=\"index.php\">Liste der Alben</a></td>".($adminbit ? "<td align=\"right\"><a href=\"editalbum.php?id=$id\">Album editieren</a></td>" : "")."</tr>";
echo "<tr><td colspan=\"3\" align=\"center\">";
echo "<table border=\"0\">\n";

flush();

for ($i=0; $i< (ceil($numrows/$perrow)*$perrow); $i++) { // anzahl elemente in der matrix (inkl. der leeren elemente)
	if ($i%$perrow == 0) echo "<tr>\n";
	echo "<td align=\"center\" valign=\"middle\">";
	if ($i < $numrows) { // wenn es noch records gibt, fŸllen wir daten, sonst nicht
		$row = mysql_fetch_array($result);
		getimage($row['ID'],"showpicture.php?album=$id&id=".$row['ID'],200); // thumb-gršsse
		echo "<br><a href=\"showpicture.php?album=$id&id=".$row['ID']."\">".$row['name']."</a>";
	}
	echo "</td>\n";
	if ($i%$perrow == ($perrow - 1)) echo "</tr>\n";
	flush();
}

echo "</table>";
echo "</td></tr>";
echo "<tr><td colspan=\"3\" align=\"center\">";
echo "<table border=\"0\" width=\"99%\">";
echo "<tr>";

echo "<td width=\"33%\" align=\"left\">";
$sql = "SELECT ID FROM album_info WHERE anlass > '".addslashes($album['anlass'])."' AND visible = 1 ORDER BY anlass ASC LIMIT 1;";
$subresult = mysql_query($sql);
if (mysql_num_rows($subresult) > 0) {
	$subbild = mysql_fetch_row($subresult);
	echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"GET\"><input type=\"hidden\" name=\"id\" value=\"{$subbild[0]}\"><input type=\"submit\" value=\"<-\"></form>";
}
echo "</td>";
echo "<td align=\"center\">";
echo "<form action=\"index.php\" method=\"GET\"><input type=\"submit\" value=\"up\"></form>";
echo "</td>";

echo "<td width=\"33%\" align=\"right\">";
$sql = "SELECT ID FROM album_info WHERE anlass < '".addslashes($album['anlass'])."' AND visible = 1 ORDER BY anlass DESC LIMIT 1;";
$subresult = mysql_query($sql);
if (mysql_num_rows($subresult) > 0) {
	$subbild = mysql_fetch_row($subresult);
	echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"GET\"><input type=\"hidden\" name=\"id\" value=\"{$subbild[0]}\"><input type=\"submit\" value=\"->\"></form>";
}
echo "</td>";

echo "</tr>";
echo "</table>";
echo "</td></tr>";

echo "<tr><td colspan=\"3\">Beschreibung:<br>";
echo $album['beschreibung'];
echo "</td></tr>";
echo "<tr><td colspan=\"3\">Kommentare:<br>";

$sql = "SELECT kommentare.*,chat_user.username FROM kommentare LEFT JOIN chat_user ON kommentare.UID = chat_user.UID WHERE kommentare.album_ID = $id ORDER BY kommentare.posttime ASC;";
$kommentresult = mysql_query($sql);

echo "<ul>";
while ($kommentar = mysql_fetch_array($kommentresult)) echo "<li> ".stripslashes($kommentar['kommentar'])." <font size=\"-2\">(von ".($kommentar['username'] && $kommentar['UID'] != 0 ? "<a href=\"userinfo.php?id={$kommentar['UID']}\">".htmlentities($kommentar['username'])."</a>" : "User#".$kommentar['UID'])." am ".ts2str($kommentar['posttime']).")</font>\n";
echo "</ul>";

echo "<br><br>";

if ($_SESSION['UID']) {
	echo "Kommentar <font size=\"-2\">(du bist eingeloggt als: {$_SESSION['username']})</font>: ";
	echo "<form action=\"{$_SERVER['PHP_SELF']}?id=$id\" method=\"post\"><input type=\"text\" name=\"kommentar\"><input type=\"submit\" value=\"Speichern!\"></form>";
} else {
	echo "Du musst dich <a href=\"../login.php?url=".urlencode($_SERVER['PHP_SELF']."?id=$id")."\">anmelden</a>, um Kommentare zu schreiben.";
}

echo "</td></tr>";
echo "</table>";

include("footer.inc.php");

$sql = "UPDATE album_info SET hits = hits + 1, lastaccess = NOW();";
mysql_query($sql);

?>