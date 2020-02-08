<?
include("includes.inc.php");
include("admin.inc.php");

$perrow = 3;

if ($_REQUEST['new']) {
	$sql = "INSERT INTO album_info SET created = NOW(), autor = {$_SESSION['UID']};";
	mysql_query($sql);
	$id = mysql_insert_id();
} else $id = addslashes((integer)trim($_REQUEST['id']));
if (!$id) {
	include("header.inc.php");
	echo "Keine ID angegeben.";
	echo "<br>Zur&uuml;ck zur <a href=\"index.php\">Album-Liste</a>";
	include("footer.inc.php");
	exit;
}


if ($_REQUEST['kill']) {
	$title = "Bist du sicher, dass du das Album mit ID=$id l&ouml;schen willst ?";
	include("header.inc.php");
	echo "Bist du sicher, dass du das Album mit ID=$id l&ouml;schen willst ?<br>";
	echo "<a href=\"{$_SERVER['PHP_SELF']}?id=$id\">NEIN, zur&uuml;ck</a>   -   <a href=\"{$_SERVER['PHP_SELF']}?id=$id&killsure={$_REQUEST['kill']}\">JA, weiter..</a>";
	include("footer.inc.php");
	exit;
}

if ($_REQUEST['killsure']) {
	$title = "Album mit ID=$id gel&ouml;scht";
	include("header.inc.php");
	$sql = "DELETE FROM album_info WHERE ID = $id;";
	if (mysql_query($sql)) echo "Album-info gel&ouml;scht<br>";
	else echo "Album-info konnte nicht gel&ouml;scht werden<br>";
	$sql = "DELETE FROM album_bilder WHERE album_ID = $id;";
	if (mysql_query($sql)) echo "Album-bilder gel&ouml;scht<br>";
	else echo "Album-bilder konnten nicht gel&ouml;scht werden<br>";
	echo "<br>Zur&uuml;ck zur <a href=\"index.php\">Album-Liste</a>";
	include("footer.inc.php");
	exit;
}

if ($_REQUEST['anlass']) {
	
	if (!($anlass = addslashes(htmlentities($_REQUEST['anlass'])))) {
		$unvollstandig = 1;
		echo "Einen Anlassnamen angeben !<br>";
	}
	if (!($ort = addslashes(htmlentities($_REQUEST['ort'])))) {
		$unvollstandig = 1;
		echo "Einen Anlassort angeben !<br>";
	}
	$homepage = addslashes(htmlentities($_REQUEST['homepage']));
	$beschreibung = addslashes(nl2br(htmlentities(trim($_REQUEST['beschreibung']))));
	$bild_ID = addslashes((integer)($_REQUEST['bild_ID']));
	if ($_REQUEST['visible'] == "true") $visible = 1;
	else $visible = 0;
	
	
	$tag = (integer)($_REQUEST['tag']);
	$monat = (integer)($_REQUEST['monat']);
	$jahr = (integer)($_REQUEST['jahr']);
	$stunde = (integer)($_REQUEST['stunde']);
	$minute = (integer)($_REQUEST['minute']);
	
	$jahr = (integer)$jahr;
	if ($jahr) {
		if ($jahr < 100) $jahr = ($jahr + 2000);
	} else {
		$datewrong = 1;
		$unvollstandig = 1;
	}
	
	$monat = (integer)$monat;
	if ($monat && $monat > 0 && $monat < 13) {
		
	} else {
		$datewrong = 1;
		$unvollstandig = 1;
	}
	
	$tag = (integer)$tag;
	if ($tag && $tag > 0 && $tag < 32) {
		
	} else {
		$datewrong = 1;
		$unvollstandig = 1;
	}
	
	$stunde = (integer)$stunde;
	$minute = (integer)$minute;
	if (($stunde || $stunde == 0) && ($minute || $minute == 0) && $stunde < 24 && $minute < 60) {
		
	} else {
		$timewrong = 1;
		$unvollstandig = 1;
	}
	
	if (!$unvollstandig) {
		$date = str_pad($jahr, 4, "0", STR_PAD_LEFT).str_pad($monat, 2, "0", STR_PAD_LEFT).str_pad($tag, 2, "0", STR_PAD_LEFT).str_pad($stunde, 2, "0", STR_PAD_LEFT).str_pad($minute, 2, "0", STR_PAD_LEFT)."00";

		$sql = "UPDATE album_info SET anlass = '$anlass', ort = '$ort', homepage = '$homepage', beschreibung = '$beschreibung', anlasstime = '$date', bild_ID = '$bild_ID', visible = $visible, autor = {$_SESSION['UID']}, lastedit = NOW() WHERE ID = $id;";
		$result = mysql_query($sql);
		if (!$result) echo "Infos konnten nicht gespeichert werden !<br>";
	}
}

if ($_REQUEST['removepicture']) {
	$removepicture = (array)$_REQUEST['removepicture'];
	for ($i=0; $i < count($removepicture); $i++) {
		$sql = "DELETE FROM album_bilder WHERE album_ID = $id AND bild_ID = ".current($removepicture).";";
		if (!mysql_query($sql)) echo "Bild mit ID=".current($removepicture)." konnte nicht aus dem Album gel&ouml;scht werden.<br>";
		next($removepicture);
	}
}

if ($_REQUEST['addpicture']) {
	$addpicture = (array)$_REQUEST['addpicture'];
	for ($i=0; $i < count($addpicture); $i++) {
		$sql = "INSERT INTO album_bilder SET album_ID = $id, bild_ID = ".current($addpicture).";";
		if (!mysql_query($sql)) echo "Bild mit ID=".current($addpicture)." konnte nicht zum Album hinzugef&uuml;gt werden.<br>";
		next($addpicture);
	}
}

$sql = "SELECT * FROM album_info WHERE ID = $id;";
$albumresult = mysql_query($sql);
$album = mysql_fetch_array($albumresult);
$album = array_map("stripslashes",$album);

if (mysql_num_rows($albumresult) < 1) {
	include("header.inc.php");
	echo "Keine solche ID.";
	echo "<br>Zur&uuml;ck zur <a href=\"index.php\">Album-Liste</a>";
	include("footer.inc.php");
	exit;
}


$title = "Album ID=$id editieren";
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

echo "<form action=\"".$_SERVER['PHP_SELF']."?id={$album['ID']}\" method=\"post\">";
echo "<table border=\"1\">\n";
echo "<tr><td align=\"left\"><a href=\"index.php\">Liste der Alben</a></td>";
echo "<td align=\"center\"><a href=\"input.php\">Neues Bild hochladen</a></td>";
echo "<td align=\"right\"><a href=\"editalbum.php?new=true\">Neues Album erstellen</a></td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan=\"2\">";
echo "<table>";
echo "<tr><td>ID</td><td>{$album['ID']} <a href=\"{$_SERVER['PHP_SELF']}?id=$id&kill=true\">Dieses Album l&ouml;schen</a></td></tr>";
echo "<tr><td>Anlass</td><td><input type=\"text\" name=\"anlass\" value=\"".($_REQUEST['anlass'] ? stripslashes($_REQUEST['anlass']) : stripslashes($album['anlass']))."\" size=\"30\"></td></tr>";
echo "<tr><td>Ort</td><td><input type=\"text\" name=\"ort\" value=\"".($_REQUEST['ort'] ? stripslashes($_REQUEST['ort']) : stripslashes($album['ort']))."\" size=\"30\"></td></tr>";
echo "<tr><td>Homepage</td><td><input type=\"text\" name=\"homepage\" value=\"".($_REQUEST['homepage'] ? stripslashes($_REQUEST['homepage']) : stripslashes($album['homepage']))."\" size=\"30\"></td></tr>";
echo "<tr><td>Bild</td><td><select name=\"bild_ID\" size=\"1\">\n<option value=\"\">-- Ein bild w&auml;hlen --";

$sql = "SELECT bilder_info.ID,bilder_info.name FROM bilder_info,album_bilder WHERE album_bilder.bild_ID = bilder_info.ID AND album_bilder.album_ID = $id ORDER BY bilder_info.name;";
$bildresult = mysql_query($sql);
while ($bild = mysql_fetch_array($bildresult)) echo "<option value=\"{$bild['ID']}\"".($bild['ID'] == (integer)$album['bild_ID'] ? " selected=\"selected\"" : "").">{$bild['ID']}: {$bild['name']}</option>\n";
echo "</select></td></tr>";

echo "<tr><td>Sichtbarkeit</td><td><input type=\"radio\" name=\"visible\" value=\"true\"".($album['visible'] ? " checked=\"checked\"" : "")."> sichtbar<br><input type=\"radio\" name=\"visible\" value=\"false\"".($album['visible'] ? "" : " checked=\"checked\"")."> unsichtbar</td></tr>";
echo "<tr><td>Beschreibung</td><td><textarea name=\"beschreibung\" cols=\"30\" rows=\"4\">".($_REQUEST['beschreibung'] ? stripslashes($_REQUEST['beschreibung']) : str_replace(array("<br />","<br>","<br/>"),"",stripslashes($album['beschreibung'])))."</textarea></td></tr>";
echo "<tr><td>".($datewrong ? "<font color=\"red\">" : "")."Datum:".($datewrong ? "</font>" : "")."</td><td><input type=\"text\" name=\"tag\" value=\"".($_REQUEST['tag'] ? $_REQUEST['tag'] : substr($album['anlasstime'],8,2))."\" size=\"2\">.<input type=\"text\" name=\"monat\" value=\"".($_REQUEST['monat'] ? $_REQUEST['monat'] : substr($album['anlasstime'],5,2))."\" size=\"2\">.<input type=\"text\" name=\"jahr\" value=\"".($_REQUEST['jahr'] ? $_REQUEST['jahr'] : substr($album['anlasstime'],0,4))."\" size=\"4\">, ".($timewrong ? "<font color=\"red\">" : "")."Zeit:".($timewrong ? "</font>" : "")." <input type=\"text\" size=\"2\" name=\"stunde\" value=\"".($_REQUEST['stunde'] ? $_REQUEST['stunde'] : substr($album['anlasstime'],11,2))."\">:<input type=\"text\" size=\"2\" name=\"minute\" value=\"".($_REQUEST['minute'] ? $_REQUEST['minute'] : substr($album['anlasstime'],14,2))."\"></td></tr>";

echo "<tr><td></td><td><input type=\"submit\" value=\"Speichern!\"></td></tr>";
echo "</table>";
echo "</td>";
echo "<td>";
getimage($album['bild_ID'],"showpicture.php?id=".$album['bild_ID'],200);
echo "</td>";
echo "</tr>";
echo "<tr><td colspan=\"3\">";

$sql = "SELECT bilder_info.* FROM bilder_info,album_bilder WHERE bilder_info.ID = album_bilder.bild_ID AND album_bilder.album_ID = $id ORDER BY bilder_info.name;";
$bildresult = mysql_query($sql);
if (mysql_num_rows($bildresult) > 0) {
	echo "<table>";
	while ($bild = mysql_fetch_array($bildresult)) echo "<tr><td><input type=\"checkbox\" name=\"removepicture[]\" value=\"{$bild['ID']}\"></td><td>{$bild['ID']}: {$bild['name']}</td></tr>";
	echo "<tr><td></td><td><input type=\"button\" onClick=\"setCheckboxes(0, 'removepicture[]', true)\" value=\"Alle ausw&auml;hlen\"> - <input type=\"button\" onClick=\"setCheckboxes(0, 'removepicture[]', false)\" value=\"Auswahl entfernen\"></td></tr>";
	echo "</table>";
	echo "<input type=\"submit\" value=\"Ausgew&auml;hlte Bilder aus diesem Album l&ouml;schen\"><br><br>";
}


$sql = "SELECT bilder_info.* FROM bilder_info LEFT JOIN album_bilder ON bilder_info.ID = album_bilder.bild_ID WHERE album_bilder.bild_ID IS NULL AND bilder_info.parent = bilder_info.ID ORDER BY bilder_info.name;";
$bildresult = mysql_query($sql);
if (mysql_num_rows($bildresult) > 0) {
	echo "<table>";
	while ($bild = mysql_fetch_array($bildresult)) echo "<tr><td><input type=\"checkbox\" name=\"addpicture[]\" value=\"{$bild['ID']}\"></td><td><a href=\"showpicture.php?id={$bild['ID']}\">{$bild['ID']}: {$bild['name']}</a></td></tr>";
	echo "<tr><td></td><td><input type=\"button\" onClick=\"setCheckboxes(0, 'addpicture[]', true)\" value=\"Alle ausw&auml;hlen\"> - <input type=\"button\" onClick=\"setCheckboxes(0, 'addpicture[]', false)\" value=\"Auswahl entfernen\"></td></tr>";
	echo "</table>";
	echo "<input type=\"submit\" value=\"Ausgew&auml;hlte Bilder in dieses Album einf&uuml;gen\"><br><br>";
}


$sql = "SELECT bilder_info.* FROM bilder_info,album_bilder WHERE bilder_info.ID = album_bilder.bild_ID AND bilder_info.parent = bilder_info.ID AND album_bilder.album_ID <> $id GROUP BY bilder_info.parent ORDER BY bilder_info.name;";
$bildresult = mysql_query($sql);
if (mysql_num_rows($bildresult) > 0) {
	echo "<select name=\"addpicture[]\" size=\"3\" multiple=\"multiple\">";
	echo "<option value=\"\">-- Bild(er) w&auml;hlen --";
	while ($bild = mysql_fetch_array($bildresult)) echo "<option value=\"{$bild['ID']}\">{$bild['ID']}: {$bild['name']}</option>";
	echo "</select><br>";
	echo "<input type=\"submit\" value=\"Ausgew&auml;hlte Bilder in dieses Album einf&uuml;gen\"><br>";
}

echo "</td></tr>";
echo "</table>";
echo "</form>";
include("footer.inc.php");
?>
