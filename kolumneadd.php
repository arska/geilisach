<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");

$errors = array();

if ($_REQUEST['go']) {
	$kolumne = $_REQUEST['kolumne'];
	$kolumne = trim($kolumne);
	$kolumne = htmlentities($kolumne);
//	$kolumne = htmlspecialchars($kolumne);
	echo "3".$kolumne;
	$kolumne = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a target=\"_blank\" href=\"\\0\">\\0</a>", $kolumne);
	echo "4".$kolumne;
	$kolumne = preg_replace("/\&amp;([a-z0-9#]+);/i","\&\\1;", $kolumne);
	echo "5".$kolumne;
	$kolumne = parsestyles($kolumne);
	echo "6".$kolumne;
	$kolumne = nl2br($kolumne);
	echo "7".$kolumne;
	$kolumne = addslashes($kolumne);
	echo "8".$kolumne;
	if (!$kolumne) array_push($errors,"Du solltest noch die kolumne schreiben..");
		
	$anwesende = (array)$_REQUEST['anwesende'];
	$andere = addslashes(htmlentities(trim($_REQUEST['andere'])));	
	if (count($anwesende) < 2 && $andere == "") {
		array_push($errors,"Du wirst wohl kaum alleine gewesen sein ?");
	}
	
	$anlass = addslashes(htmlentities(trim($_REQUEST['anlass'])));
	if ($anlass == "") {
		array_push($errors,"Welchen Anlass beschreibt diese Kolumne ?");
	}
	
	$titel = addslashes(htmlentities(trim($_REQUEST['titel'])));
	if ($titel == "") {
		array_push($errors,"Was ist der Titel deiner Kolumne ?");
	}
	
	$tag = (integer)($_REQUEST['tag']);
	$monat = (integer)($_REQUEST['monat']);
	$jahr = (integer)($_REQUEST['jahr']);
	if (checkdate($monat,$tag,$jahr)) {
		$date = str_pad($jahr, 4, "0", STR_PAD_LEFT).str_pad($monat, 2, "0", STR_PAD_LEFT).str_pad($tag, 2, "0", STR_PAD_LEFT);
	} else {
		array_push($errors,"Das Datum '".str_pad($tag, 2, "0", STR_PAD_LEFT).".".str_pad($monat, 2, "0", STR_PAD_LEFT).".".str_pad($jahr, 4, "0", STR_PAD_LEFT)."' ist ung&uuml;ltig.");
	}
	
	$autor = $_SESSION['UID'];
	
	if (count($errors) < 1) {
	
		$sql = "INSERT kolumne_data SET anlass = '$anlass', andere = '$andere', titel = '$titel', kolumne = '$kolumne', autor = '$autor', anlassdate = $date;";
		if (!mysql_query($sql)) array_push($errors,"kolumne write failed");
		$kid = mysql_insert_id();
		
		for($x=0; $x<count($anwesende); $x++) {
			$sql = "INSERT kolumne_anwesend SET UID = '".$anwesende[$x]."', KID = '$kid';";
			if (!mysql_query($sql)) array_push($errors,"anwesender user UID '".$anwesende[$x]."', (nr. $x im array \$anwesende) failed");			
		}
		Header("Location: kolumnelist.php");
	}
	
}

$title = "Kolumne schreiben";
include("header.inc.php");
?>
<form method="post" action="<?=$_SERVER['PHP_SELF'] ?>">
<table border="1" cellpadding="3">
<tr><td align="left"><a href="kolumnelist.php">Kolumnenliste</a></td></tr>
<tr><td>
<?
if (count($errors) > 0) {
	echo "<font color=\"red\">";
	for($i=0; $i<count($errors); $i++) {
		echo $errors[$i] . "<br>";
	}
	echo "</font>";
}
?>
<table border="0" cellpadding="3">
<tr><td>Anlass:</td><td><input type="text" name="anlass" value="<?=$_REQUEST['anlass'] ?>" size="30">
<? /* oder 
<select name="aid" size="1"><option value="0">--w&auml;hle ein Event aus--</option><?

$sql = "SELECT AID,anlass FROM party_data ORDER BY anlasstime DESC;";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)) {
	echo "<option value=\"".$row[0]."\">".$row[1]."</option>\n";
} 

echo "</select>"; */ ?></td></tr>

<tr><td valign="top">Anwesende:</td><td><table><?

$sql = "SELECT UID,username FROM chat_user WHERE UID > 0 ORDER BY username ASC;";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)) {
	?><tr><td><input type="checkbox" name="anwesende[]" value="<?=$row[0] ?>"<? if (in_array($row[0],(array)$_REQUEST['anwesende']) || $row[0] == $_SESSION['UID']) echo " checked=\"checked\""; ?>> <?=stripslashes($row[1]) ?></td></tr><? echo "\n";
} 

?>
<tr><td>Andere: <input type="text" name="andere" value="<?=$_REQUEST['andere'] ?>" size="30"></td></tr>
</table></td></tr>
<tr><td>Titel:</td><td><input type="text" name="titel" value="<?=$_REQUEST['titel'] ?>" size="30"></td></tr>
<tr><td>Datum:</td><td><input type="text" name="tag" value="<?=($_REQUEST['tag'] ? $_REQUEST['tag'] : strftime('%d')) ?>" size="2">.<input type="text" name="monat" value="<?=($_REQUEST['monat'] ? $_REQUEST['monat'] : strftime('%m')) ?>" size="2">.<input type="text" name="jahr" value="<?=($_REQUEST['jahr'] ? $_REQUEST['jahr'] : strftime('%Y')) ?>" size="4"></td></tr>
<tr><td valign="top">Kolumne:</td><td><textarea name="kolumne" cols="50" rows="30" ><?=trim($_REQUEST['kolumne']) ?></textarea></td></tr>
<tr><td></td><td align="right"><input type="submit" name="go" value="Abschicken"></td></tr>
</table>
</td></tr>
</table>
</form>
<?
include("footer.inc.php");
?>