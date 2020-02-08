<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
$onlytestforadminbit = true;
include("admin.inc.php");

if (!$_REQUEST['id']) {
	$title = "AID?";
	include("header.inc.php");
	?>Welchen Event möchtest du editieren ?<br>W&auml;hle einen von der <a href="partylist.php">Eventliste</a><?
	include("footer.inc.php");
	exit;
}

$aid = addslashes($_REQUEST['id']);

$sql = "SELECT party_data.*,chat_user.username,party_data.autor AS UID  FROM party_data LEFT JOIN chat_user ON party_data.autor = chat_user.UID WHERE party_data.AID = '$aid' LIMIT 1;";
$result = mysql_query($sql);
$row = mysql_fetch_array($result);

if ($_REQUEST['go']) {
	$beschreibung = $_REQUEST['beschreibung'];
	//$beschreibung = substr($beschreibung,0,2000);
	$beschreibung = trim($beschreibung);
	$beschreibung = htmlentities($beschreibung);
	$beschreibung = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a target=\"_blank\" href=\"\\0\">\\0</a>", $beschreibung);
	$beschreibung = preg_replace("/\&amp;([a-z0-9#]+);/i","\&\\1;", $beschreibung);
	$beschreibung = parsestyles($beschreibung);
	$beschreibung = nl2br($beschreibung);
	$beschreibung = addslashes($beschreibung);
	if (!$beschreibung) array_push($errors,"Du solltest noch die beschreibung schreiben..");

	$homepage = htmlentities(trim($_REQUEST['homepage']));
	if ($homepage != "" && substr($homepage, 0, 7) != "http://") $homepage = "http://".$homepage; // wenn nicht leer -> http hinzufügen
	$homepage = addslashes($homepage);
	
	$anlass = addslashes(htmlentities(trim($_REQUEST['anlass'])));
	if ($anlass == "") {
		array_push($errors,"Wie Heisst der Event ?");
	}
	
	$ort = addslashes(htmlentities(trim($_REQUEST['ort'])));
	if ($ort == "") {
		array_push($errors,"Wo findet der Event statt ?");
	}
	
	$tag = (integer)($_REQUEST['tag']);
	$monat = (integer)($_REQUEST['monat']);
	$jahr = (integer)($_REQUEST['jahr']);
	if ($jahr < 100) $jahr = ($jahr + 2000);
	if (checkdate($monat,$tag,$jahr)) {
		$date = str_pad($jahr, 4, "0", STR_PAD_LEFT).str_pad($monat, 2, "0", STR_PAD_LEFT).str_pad($tag, 2, "0", STR_PAD_LEFT);
	} else {
		array_push($errors,"Das Datum '".str_pad($tag, 2, "0", STR_PAD_LEFT).".".str_pad($monat, 2, "0", STR_PAD_LEFT).".".str_pad($jahr, 4, "0", STR_PAD_LEFT)."' ist ung&uuml;ltig.");
	}

	$stunde = (integer)($_REQUEST['stunde']);
	$minute = (integer)($_REQUEST['minute']);


	if (($stunde >= 0) && ($stunde < 24) && ($minute >= 0) && ($minute < 60)) {
		$date .= str_pad($stunde, 2, "0", STR_PAD_LEFT).str_pad($minute, 2, "0", STR_PAD_LEFT)."00";
	} else {
		array_push($errors,"Die Uhrzeit '".str_pad($stunde, 2, "0", STR_PAD_LEFT).":".str_pad($minute, 2, "0", STR_PAD_LEFT)."' Uhr ist ung&uuml;ltig.");
	}
	
	if (($_SESSION['UID'] != $row['autor']) && (!$adminbit)) array_push($errors,"Du hast nicht die Berechtigung diesen Event upzudaten !");
	
	$autor = (integer)$_REQUEST['autor'];
	
	if (count($errors) < 1) {
		$sql = "UPDATE party_data SET anlass = '$anlass', ort = '$ort', beschreibung = '$beschreibung', anlasstime = $date, homepage = '$homepage', autor = $autor WHERE AID = '$aid' LIMIT 1;";
		if (mysql_query($sql)) {
			Header("Location: partyread.php?id=$aid"); // successfully added -> back to list
		}
		else array_push($errors,"party update failed (uh-oh)..");
	}

}

$title = "Event editieren";
include("header.inc.php");
?>
<form method="post" action="<?=$_SERVER['PHP_SELF'] ?>">
<table border="1" cellpadding="3">
<tr><td align="left"><a href="partylist.php">Eventliste</a></td></tr>
<tr><td>
<?
if (count($errors) > 0) {
	echo "<font color=\"red\">";
	for($x=0; $x<count($errors); $x++) {
		echo $errors[$x] . "<br>";
	}
	echo "</font>";
} elseif ($updated) echo "<font color=\"green\">Submitted !</font><br>";
?>
<table border="0" cellpadding="3">
<tr>
	<td>ID</td>
	<td><?=$aid ?><input type="hidden" name="id" value="<?=$aid ?>"></td>
</tr>
<tr>
	<td>Von:</td>
	<td><select name="autor">
<?
		$sql = "SELECT UID,username FROM chat_user WHERE UID > 0 ORDER BY username;";
		$result = mysql_query($sql);
		while ($userrow = mysql_fetch_array($result)) {
			echo "<option value=\"{$userrow['UID']}\"".($userrow['UID'] == $row['UID'] ? " selected=\"selected\"" : "").">{$userrow['username']}</option>\n";
		}
?>
	</select></td>
</tr>
<tr>
	<td>Anlass:</td>
	<td><input type="text" name="anlass" value="<?=($_REQUEST['anlass'] ? $_REQUEST['anlass'] : stripslashes($row['anlass'])) ?>" size="30"></td>
</tr>
<tr>
	<td>Ort:</td>
	<td><input type="text" name="ort" value="<?=($_REQUEST['ort'] ? $_REQUEST['ort'] : stripslashes($row['ort'])) ?>" size="30"></td>
</tr>
<tr>
	<td>Homepage:</td>
	<td><input type="text" name="homepage" value="<?=($_REQUEST['homepage'] ? $_REQUEST['homepage'] : stripslashes($row['homepage'])) ?>" size="30"></td>
</tr>
<tr>
	<td>Datum:</td>
	<td><input type="text" name="tag" value="<?=($_REQUEST['tag'] ? $_REQUEST['tag'] : substr($row['anlasstime'],8,2)) ?>" size="2">.<input type="text" name="monat" value="<?=($_REQUEST['monat'] ? $_REQUEST['monat'] : substr($row['anlasstime'],5,2)) ?>" size="2">.<input type="text" name="jahr" value="<?=($_REQUEST['jahr'] ? $_REQUEST['jahr'] : substr($row['anlasstime'],0,4)) ?>" size="4">, Zeit: <input type="text" size="2" name="stunde" value="<?=($_REQUEST['stunde'] ? $_REQUEST['stunde'] : substr($row['anlasstime'],11,2)) ?>">:<input type="text" size="2" name="minute" value="<?=($_REQUEST['minute'] ? $_REQUEST['minute'] : substr($row['anlasstime'],14,2)) ?>"></td>
</tr>
<tr>
	<td valign="top">Beschreibung:</td>
<?
	$search = array("<br />","<br>");
	$replace = array("","");
	
	$beschreibung = stripslashes($row['beschreibung']);
	$beschreibung = str_replace($search, $replace, $beschreibung);
	$beschreibung = preg_replace("|<a .*>(.+?)</a>|","$1",$beschreibung);
	$beschreibung = preg_replace("|<([/]?)([a-z]+)>|","[$1$2]",$beschreibung);
?>
	<td><textarea name="beschreibung" cols="50" rows="30" ><?=($_REQUEST['beschreibung'] ? $_REQUEST['beschreibung'] : $beschreibung) ?></textarea></td>
</tr>
<tr>
	<td></td>
	<td align="right"><input type="submit" name="go" value="Abschicken"></td>
</tr>
</table>
</td></tr>
</table>
</form>
<?
include("footer.inc.php");
?>