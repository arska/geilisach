<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");

$errors = array();

if ($_REQUEST['go']) {
	$beschreibung = $_REQUEST['beschreibung'];
	//$beschreibung = substr($beschreibung,0,2000);
	$beschreibung = trim($beschreibung);
	$beschreibung = htmlentities($beschreibung);
	$beschreibung = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a target=\"_blank\" href=\"\\0\">\\0</a>", $beschreibung);
	$beschreibung = preg_replace("/\&amp;([a-z0-9#]+);/i","\&$1;", $beschreibung);
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
	
	$autor = $_SESSION['UID'];

	if (count($errors) < 1) {
		$sql = "INSERT party_data SET anlass = '$anlass', ort = '$ort', beschreibung = '$beschreibung', autor = '$autor', anlasstime = $date, homepage = '$homepage';";
		if (mysql_query($sql)) {
			$id = mysql_insert_id();
			Header("Location: partyread.php?id=$id"); // successfully added -> back to list
		}
		else array_push($errors,"party write failed (uh-oh)..");
	}

}

$title = "Event hinzuf&uuml;gen";
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
}
?>
<table border="0" cellpadding="3">
<tr><td>Anlass:</td><td><input type="text" name="anlass" value="<?=$_REQUEST['anlass'] ?>" size="30"></td></tr>
<tr><td>Ort:</td><td><input type="text" name="ort" value="<?=$_REQUEST['ort'] ?>" size="30"></td></tr>
<tr><td>Homepage:</td><td><input type="text" name="homepage" value="<?=$_REQUEST['homepage'] ?>" size="30"></td></tr>
<tr><td>Datum:</td><td><input type="text" name="tag" value="<?=$_REQUEST['tag'] ?>" size="2">.<input type="text" name="monat" value="<?=$_REQUEST['monat'] ?>" size="2">.<input type="text" name="jahr" value="<? if ($_REQUEST['jahr']) echo $_REQUEST['jahr']; else echo strftime("%Y"); ?>" size="4">, <? if ($timewrong) { ?><font color="red"><? } ?>Zeit:<? if ($timewrong) { ?></font><? } ?> <input type="text" size="2" name="stunde" value="<?=$_REQUEST['stunde'] ?>">:<input type="text" size="2" name="minute" value="<? if ($_REQUEST['minute']) echo $_REQUEST['minute']; else echo "00"; ?>"></td></tr>
<tr><td valign="top">Beschreibung:</td><td><textarea name="beschreibung" cols="50" rows="30" ><?=$_REQUEST['beschreibung'] ?></textarea></td></tr>
<tr><td></td><td align="right"><input type="submit" name="go" value="Abschicken"></td></tr>
</table>
</td></tr>
</table>
</form>
<?
include("footer.inc.php");
?>