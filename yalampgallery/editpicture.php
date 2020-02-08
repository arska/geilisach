<?
include("includes.inc.php");
include("admin.inc.php");

$perrow = 3;

$id = addslashes((integer)trim($_REQUEST['id']));
if (!$id) {
	include("header.inc.php");
	echo "Keine ID angegeben.";
	if ($_REQUEST['album']) echo "<br>Zur&uuml;ck zum <a href=\"showalbum.php?id={$_REQUEST['album']}\">Album mit ID={$_REQUEST['album']}</a>";
	else echo "<br>Zur&uuml;ck zur <a href=\"index.php\">Album-Liste</a>";
	include("footer.inc.php");
	exit;
}

if ($_REQUEST['kill']) {
	$title = "Bist du sicher, dass du das Bild mit ID=$id l&ouml;schen willst ?";
	include("header.inc.php");
	echo "Bist du sicher, dass du das Bild mit ID=$id l&ouml;schen willst ?<br>";
	echo "<a href=\"{$_SERVER['PHP_SELF']}?album={$_REQUEST['album']}&id=$id\">NEIN, zur&uuml;ck</a>   -   <a href=\"{$_SERVER['PHP_SELF']}?album={$_REQUEST['album']}&id=$id&killsure={$_REQUEST['kill']}\">JA, weiter..</a>";
	include("footer.inc.php");
	exit;
}

if ($_REQUEST['killsure']) {
	$title = "Bild mit ID=$id gel&ouml;scht";
	include("header.inc.php");
	
	$sql = "SELECT ID,path FROM bilder_info WHERE parent = $id;";
	$result = mysql_query($sql);
	while ($thumb = mysql_fetch_array($result)) {
		if (!unlink($thumb['path'])) echo "Thumbnail mit ID=".((integer)trim($_REQUEST['removethumb']))." path={$thumb['path']} konnte nicht gel&ouml;scht werden.<br>";
		else echo "Thumbnail mit ID={$thumb['ID']} path={$thumb['path']} gel&ouml;scht.<br>";
	}
	
	$sql = "DELETE FROM bilder_info WHERE parent = $id;";
	if (mysql_query($sql)) echo "Album-info gel&ouml;scht<br>";
	else echo "Bilder-info konnte nicht gel&ouml;scht werden<br>";
	
	$sql = "DELETE FROM album_bilder WHERE bild_ID = $id;";
	if (mysql_query($sql)) echo "bild aus allen alben gel&ouml;scht<br>";
	else echo "bild konnte nicht aus allen alben gel&ouml;scht werden<br>";
	
	$sql = "DELETE FROM turnus_bilder WHERE bild_ID = $id;";
	if (mysql_query($sql)) echo "bild aus dem turnus gel&ouml;scht<br>";
	else echo "bild konnte nicht aus dem turnus gel&ouml;scht werden<br>";
	
	$sql = "DELETE FROM kommentare WHERE bild_ID = $id;";
	if (mysql_query($sql)) echo "alle kommentare zum bild gel&ouml;scht<br>";
	else echo "die kommentare zum bild konnten nicht gel&ouml;scht werden<br>";
	
	if ($_REQUEST['album']) echo "<br>Zur&uuml;ck zum <a href=\"showalbum.php?id={$_REQUEST['album']}\">Album mit ID={$_REQUEST['album']}</a>";
	else echo "<br>Zur&uuml;ck zur <a href=\"index.php\">Album-Liste</a>";
	
	include("footer.inc.php");
	exit;
}

if ($_REQUEST['name']) {
	$sql = "UPDATE bilder_info SET name = '".addslashes(htmlentities(trim($_REQUEST['name'])))."', beschreibung = '".addslashes(nl2br(htmlentities(trim($_REQUEST['beschreibung']))))."', lastedit = NOW() WHERE parent = $id;";
	$result = mysql_query($sql);
	if (!$result) echo "Name & Beschreibung konnten nicht gespeichert werden !<br>";
}

if ($_REQUEST['removefromalbum']) {
	$removefromalbum = (array)$_REQUEST['removefromalbum'];
	for ($i=0; $i < count($removefromalbum); $i++) {
		$sql = "DELETE FROM album_bilder WHERE bild_ID = $id AND album_ID = ".current($removefromalbum).";";
		if (!mysql_query($sql)) echo "Bild konnte nicht aus Album mit ID=".current($removefromalbum)." gel&ouml;scht werden.<br>";
		next($removefromalbum);
	}
}

if ($_REQUEST['addtoalbum']) {
	$sql = "INSERT INTO album_bilder SET bild_ID = $id, album_ID = ".((integer)trim($_REQUEST['addtoalbum'])).";";
	if (!mysql_query($sql)) echo "Bild konnte nicht zu Album mit ID=".((integer)trim($_REQUEST['addtoalbum']))." hinzugef&uuml;gt werden.<br>";
}

if ($_REQUEST['removethumb']) {
	$sql = "SELECT path FROM bilder_info WHERE ID = {$_REQUEST['removethumb']} LIMIT 1;";
	$result = mysql_query($sql);
	while ($thumb = mysql_fetch_array($result)) {
		if (!unlink($thumb['path'])) echo "Thumbnail mit ID=".((integer)trim($_REQUEST['removethumb']))." path={$thumb['path']} konnte nicht gel&ouml;scht werden.<br>";
	}
	$sql = "DELETE FROM bilder_info WHERE ID = ".((integer)trim($_REQUEST['removethumb'])).";";
	if (!mysql_query($sql)) echo "Thumbnail mit ID=".((integer)trim($_REQUEST['removethumb']))." konnte nicht gel&ouml;scht werden.<br>";
}



$sql = "SELECT * FROM bilder_info WHERE ID = $id;";
$albumresult = mysql_query($sql);

if (mysql_num_rows($albumresult) < 1) {
	include("header.inc.php");
	echo "Keine solche ID.";
	if ($_REQUEST['album']) echo "<br>Zur&uuml;ck zum <a href=\"showalbum.php?id={$_REQUEST['album']}\">Album mit ID={$_REQUEST['album']}</a>";
	else echo "<br>Zur&uuml;ck zur <a href=\"index.php\">Album-Liste</a>";
	include("footer.inc.php");
	exit;
}

$bild = mysql_fetch_array($albumresult);
$bild = array_map("stripslashes",$bild);

$title = "Bild ID=$id editieren";
include("header.inc.php");

echo "<script type=\"text/javascript\" language=\"JavaScript1.2\">
<!--
/**
 * Checks/unchecks all tables
 *
 * @param   string   the form name
 * @param   boolean  whether to check or to uncheck the element
 *
 * @return  boolean  always true
 */
function setCheckboxes(the_form, the_element, do_check)
{
    var elts      = (typeof(document.forms[the_form].elements[the_element]) != 'undefined') ? document.forms[the_form].elements[the_element] : '';
    var elts_cnt  = (typeof(elts.length) != 'undefined') ? elts.length : 0;

    if (elts_cnt) {
        for (var i = 0; i < elts_cnt; i++) {
            elts[i].checked = do_check;
        } // end for
    } else {
        elts.checked        = do_check;
    } // end if... else

    return true;
} // end of the 'setCheckboxes()' function
//-->
</script>";

echo "<form action=\"".$_SERVER['PHP_SELF']."?id=$id\" method=\"post\">";
echo "<table border=\"1\">\n";
echo "<tr>";
echo "<td>";
echo "<table>";
echo "<tr><td>ID</td><td>$id <a href=\"{$_SERVER['PHP_SELF']}?album=$album&id=$id&kill=true\">Dieses Bild l&ouml;schen</a></td></tr>";
echo "<tr><td>Masse</td><td>{$bild['hsize']}x{$bild['vsize']}</td></tr>";
echo "<tr><td>Gr&ouml;sse</td><td>".($bild['size'] > pow(1024,2) ? round($bild['size']/pow(1024,2),2)." MiB" : round($bild['size']/pow(1024,1),2)." KiB")."</td></tr>";
echo "<tr><td>Mime-Typ</td><td>{$bild['mime_type']}</td></tr>";
echo "<tr><td>Name</td><td><input type=\"text\" name=\"name\" value=\"".stripslashes($bild['name'])."\" size=\"30\"></td></tr>";
echo "<tr><td>Beschreibung</td><td><textarea name=\"beschreibung\" cols=\"30\" rows=\"4\">".str_replace(array("<br />","<br>","<br/>"),"",stripslashes($bild['beschreibung']))."</textarea></td></tr>";
echo "<tr><td></td><td><input type=\"submit\" value=\"Speichern!\"></td></tr>";
echo "</table>";
echo "</td>";
echo "<td>";
getimage($id,"showpicture.php?id=$id",200);
echo "</td>";
echo "</tr>";
echo "<tr><td colspan=\"2\">";
echo "Thumbnails: ";
$sql = "SELECT ID,hsize,vsize FROM bilder_info WHERE ID <> parent AND parent = $id ORDER BY hsize ASC;";
$thumbresult = mysql_query($sql);
if (mysql_num_rows($thumbresult) > 0) {
	while ($thumb = mysql_fetch_array($thumbresult)) echo "<a href=\"{$_SERVER['PHP_SELF']}?id=$id&removethumb={$thumb['ID']}\">({$thumb['hsize']}x{$thumb['vsize']})</a> ";
	echo "(klick um zu l&ouml;schen)";
} else echo "<i>keine Thumbnails</i>";
echo "</td></tr>";
echo "<tr><td colspan=\"2\">";

$sql = "SELECT album_info.* FROM album_info,album_bilder WHERE album_info.ID = album_bilder.album_ID AND album_bilder.bild_ID = $id ORDER BY album_info.anlasstime;";
$albumresult = mysql_query($sql);
if (mysql_num_rows($albumresult) > 0) {
	echo "<table>";
	while ($album = mysql_fetch_array($albumresult)) echo "<tr><td><input type=\"checkbox\" name=\"removefromalbum[]\" value=\"{$album['ID']}\"></td><td>{$album['anlass']} ({$album['anlasstime']}, {$album['ort']})</td></tr>";
	echo "<tr><td></td><td><input type=\"button\" onClick=\"setCheckboxes(0, 'removefromalbum[]', true)\" value=\"Alle ausw&auml;hlen\"> - <input type=\"button\" onClick=\"setCheckboxes(0, 'removefromalbum[]', false)\" value=\"Auswahl entfernen\"></td></tr>";
	echo "</table>";
	echo "<input type=\"submit\" value=\"Bild aus gew&auml;hlten Albums l&ouml;schen\"><br><br>";
}

$sql = "SELECT album_info.* FROM album_info LEFT JOIN album_bilder ON album_info.ID = album_bilder.album_ID AND album_bilder.bild_ID = $id WHERE album_bilder.bild_ID IS NULL ORDER BY album_info.anlasstime;";
$albumresult = mysql_query($sql);
if (mysql_num_rows($albumresult) > 0) {
	echo "<select name=\"addtoalbum\" size=\"1\">";
	echo "<option value=\"\">-- Album w&auml;hlen --";
	while ($album = mysql_fetch_array($albumresult)) echo "<option value=\"{$album['ID']}\">{$album['anlass']} ({$album['anlasstime']}, {$album['ort']})";
	echo "</select><br>";
	echo "<input type=\"submit\" value=\"Bild in gew&auml;hltes Album einf&uuml;gen\"><br>";
}

echo "</td></tr>";
echo "</table>";
echo "</form>";
include("footer.inc.php");
?>