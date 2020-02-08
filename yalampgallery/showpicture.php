<?
include("includes.inc.php");
$onlytestforadminbit = true;
include("admin.inc.php");

$id = addslashes((integer)trim($_REQUEST['id']));
if (!$id) {
	include("header.inc.php");
	echo "Keine ID angegeben.";
	if ($_REQUEST['album']) echo "<br>Zur&uuml;ck zum <a href=\"showalbum.php?id={$_REQUEST['album']}\">Album mit ID={$_REQUEST['album']}</a>";
	else echo "<br>Zur&uuml;ck zur <a href=\"index.php\">Album-Liste</a>";
	include("footer.inc.php");
	exit;
}

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
		$sql = "INSERT INTO kommentare SET bild_ID = '$id', UID = '{$_SESSION['UID']}', kommentar = '$kommentar';";
		if (!mysql_query($sql))  array_push($errors,"Kommentar konnte nicht eingef&uuml;gt werden.");
	}
}

if ($_REQUEST['turnusvote'] == "aktivieren") {
	$sql = "SELECT * FROM turnus_bilder WHERE bild_ID = $id;";
	$subresult = mysql_query($sql);
	if (!mysql_num_rows($subresult)) { // wenn's nicht schon im turnus ist (damit keine duplicates entstehen)
		$sql = "INSERT INTO turnus_bilder SET bild_ID = $id, lastvote = 0;";
		if (!mysql_query($sql))  array_push($errors,"Bild konnte nicht im Turnus aktiviert werden.");
	}
}

if ($_REQUEST['turnusvote'] == "deaktivieren") {
	$sql = "DELETE FROM turnus_bilder WHERE bild_ID = $id;";
	if (!mysql_query($sql))  array_push($errors,"Bild konnte nicht im Turnus deaktiviert werden.");
}

$sql = "SELECT * FROM bilder_info WHERE ID = $id;";
$result = mysql_query($sql);
$bild = mysql_fetch_array($result);
$bild = array_map("stripslashes",$bild);

if (mysql_num_rows($result) < 1) {
	include("header.inc.php");
	echo "Keine solche ID.";
	if ($_REQUEST['album']) echo "<br>Zur&uuml;ck zum <a href=\"showalbum.php?id={$_REQUEST['album']}\">Album mit ID={$_REQUEST['album']}</a>";
	else echo "<br>Zur&uuml;ck zur <a href=\"index.php\">Album-Liste</a>";
	include("footer.inc.php");
	exit;
}

if (!$_REQUEST['album']) {
	$sql = "SELECT album_ID FROM album_bilder WHERE bild_ID = $id;";
	$subresult = mysql_query($sql);
	if (mysql_num_rows($subresult) > 0) { // wenn ein album gefunden wurde..
		$subrow = mysql_fetch_row($subresult); // wir nehmen nur das erste album..
		$album = $subrow[0];
		$albumsql = "AND album_bilder.album_ID = ".$subrow[0];
	} else {
		$album = 0;
		$albumsql = "";
	}
} else {
	$album = addslashes((integer)$_REQUEST['album']);
	$albumsql = "AND album_bilder.album_ID = ".$album;
}

$title = "Bild '{$bild['name']}'";
include("header.inc.php");
if (count($errors) > 0) print_r($errors);
echo "<table border=\"1\" cellpadding=\"3\">";
echo "<tr><td align=\"left\" colspan=\"".($adminbit ? "1" : "2")."\">Bild '{$bild['name']}':</td>".($adminbit ? "<td align=\"right\"><a href=\"editpicture.php?album=$album&id=$id\">Bild editieren</a></td>" : "")."</tr>";
echo "<tr><td colspan=\"2\" align=\"center\">";
getimage($id,$bild['path'],640);
echo "</td></tr>";

echo "<tr><td colspan=\"2\" align=\"center\">";
echo "<table border=\"0\" width=\"99%\">";
echo "<tr>";

echo "<td width=\"33%\" align=\"left\">";
$sql = "SELECT bilder_info.ID FROM bilder_info,album_bilder WHERE bilder_info.ID = album_bilder.bild_ID AND bilder_info.name < '".addslashes($bild['name'])."' $albumsql ORDER BY bilder_info.name DESC LIMIT 1;";
$subresult = mysql_query($sql);
if (mysql_num_rows($subresult) > 0) {
	$subbild = mysql_fetch_row($subresult);
	echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"GET\"><input type=\"hidden\" name=\"album\" value=\"$album\"><input type=\"hidden\" name=\"id\" value=\"{$subbild[0]}\"><input type=\"submit\" value=\"<-\"></form>";
}
echo "</td>";
echo "<td align=\"center\">";
echo "<form action=\"showalbum.php\" method=\"GET\"><input type=\"hidden\" name=\"id\" value=\"$album\"><input type=\"submit\" value=\"up\"></form>";
echo "</td>";

echo "<td width=\"33%\" align=\"right\">";
$sql = "SELECT bilder_info.ID FROM bilder_info,album_bilder WHERE bilder_info.ID = album_bilder.bild_ID AND bilder_info.name > '".addslashes($bild['name'])."' $albumsql ORDER BY bilder_info.name ASC LIMIT 1;";
$subresult = mysql_query($sql);
if (mysql_num_rows($subresult) > 0) {
	$subbild = mysql_fetch_row($subresult);
	echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"GET\"><input type=\"hidden\" name=\"album\" value=\"$album\"><input type=\"hidden\" name=\"id\" value=\"{$subbild[0]}\"><input type=\"submit\" value=\"->\"></form>";
}
echo "</td>";

echo "</tr>";
echo "</table>";
echo "</td></tr>";

echo "<tr><td colspan=\"2\">Beschreibung:<br>";
echo $bild['beschreibung'];
echo "</td></tr>";

echo "<tr><td colspan=\"1\">Kommentare:<br>";

$sql = "SELECT kommentare.*,chat_user.username FROM kommentare LEFT JOIN chat_user ON kommentare.UID = chat_user.UID WHERE kommentare.bild_ID = $id ORDER BY kommentare.posttime ASC;";
$kommentresult = mysql_query($sql);

echo "<ul>";
while ($kommentar = mysql_fetch_array($kommentresult)) echo "<li> ".stripslashes($kommentar['kommentar'])." <font size=\"-2\">(von ".($kommentar['username'] && $kommentar['UID'] != 0 ? "<a href=\"userinfo.php?id={$kommentar['UID']}\">".htmlentities($kommentar['username'])."</a>" : "User#".$kommentar['UID'])." am ".ts2str($kommentar['posttime']).")</font>\n";
echo "</ul>";

echo "<br><br>";

if ($_SESSION['UID']) {
	echo "Kommentar <font size=\"-2\">(du bist eingeloggt als: {$_SESSION['username']})</font>: ";
	echo "<form action=\"{$_SERVER['PHP_SELF']}?id=$id\" method=\"post\"><input type=\"text\" name=\"kommentar\"><input type=\"submit\" value=\"Speichern!\"></form>";
} else {
	echo "Du musst dich <a href=\"../login.php?url=".urlencode($_SERVER['PHP_SELF']."?album=$album&id=$id")."\">anmelden</a>, um Kommentare zu schreiben.";
}

echo "</td>";
echo "<td>Bilder-turnus<br>";
	$sql = "SELECT * FROM turnus_bilder WHERE bild_ID = $id;";
	$subresult = mysql_query($sql);
	if (mysql_num_rows($subresult)) { // es hat einen eintrag
		echo "Das bild ist im Bilder-Turnus.";
		$turnusflag = true;
	} else {
		echo "Das bild ist nicht im Bilder-Turnus.";
		$turnusflag = false;
	}
if ($adminbit) {
	echo "<form action=\"{$_SERVER['PHP_SELF']}?id=$id\" method=\"post\"><input type=\"submit\" name=\"turnusvote\" value=\"".($turnusflag ? "deaktivieren" : "aktivieren")."\"></form>";
} else {
	echo "<br>Du musst dich <a href=\"../login.php?url=".urlencode($_SERVER['PHP_SELF']."?album=$album&id=$id")."\">anmelden</a> und admin sein, um ein bild in den / aus dem Turnus zu w&auml;hlen.";
}

echo "</td>";
echo "</tr>";
echo "</table>";
include("footer.inc.php");

?>