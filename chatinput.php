<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
$onlytestforadminbit = true;
include("admin.inc.php");

// komentar schreiben
if ($_REQUEST['kommentar']) {
	$kommentar = $_REQUEST['kommentar'];
	$kommentar = substr($kommentar,0,2000);
	$kommentar = trim($kommentar);
	$kommentar = htmlentities($kommentar);
	$kommentar = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a target=\"_blank\" href=\"\\0\">\\0</a>", $kommentar);
	$kommentar = preg_replace("/\&amp;([a-z0-9#]+);/i","\&\\1;", $kommentar);
	$kommentar = parsestyles($kommentar);
	$kommentar = addslashes($kommentar);

	//$kommentar= "<li>" .htmlentities($kommentar)." <font size=\"-2\">(von ".htmlentities($_SESSION['username'])." am ".date("j.n.y, H:i").")</font>\n";
	if ($kommentar) { // nicht leer
		$sql = "INSERT chat_data (UID,kommentar) VALUES (".$_SESSION['UID'].",'$kommentar');";
		$result = mysql_query($sql);
	}
	if (!$result) $failedflag = 1;
};

// anzeigen

$title = "Chat-Input";
if ($_SESSION['inputfocus']) $extrahead = "<script type=\"text/javascript\">
<!--
	function inputfocus () { 
		document.inputform.kommentar.focus();
	}
//-->
</script>";
if ($_SESSION['inputfocus']) $extrabody = "onLoad=\"inputfocus()\" style=\"margin: 0; padding: 0;\"";
include("header.inc.php");

echo "<center>";
echo "<table width=\"100%\" border=\"0\"><tr>";

// logo
if ($_SESSION['inputlogo']) {
	include_once("functions2.inc.php");
	echo "<td align=\"center\" valign=\"middle\">";
	getimage(6056,"",200, "./yalampgallery/");
	echo "</td>";
}

echo "<td align=\"center\" valign=\"middle\">";
// input

?>
<? if ($failedflag) echo '<font color="red">Kommentar konnte nicht hinzugef&uuml;gt werden.</font><br>'; ?>
<table border="0">
<tr><td style="padding: 0; margin: 0;">
Kommentar <font size="-2">(du bist eingeloggt als: <?=htmlentities($_SESSION['username']) ?>)</font>: 
<form method="post" name="inputform" style="padding: 0; margin: 0;"><input type="text" name="kommentar" size="30" maxlength="2000"><input type="submit" value="Senden!"></form>
</td></tr>
<? 
if (count($_SESSION['inputbuttons']) > 0) { 
	echo "<tr><td align=\"center\" style=\"padding: 0; margin: 0\">";
	foreach ($_SESSION['inputbuttons'] as $button) echo "<form method=\"post\" style=\"float: left; padding: 0; margin: 0;\"><input type=\"submit\" name=\"kommentar\" value=\"$button\"></form>";
	echo "</td></tr>";
}
?>
</table>

[ <a target="_blank" href="userprefs.php<? if (!isset($_SESSION['zeilen'])) echo "?newuser=true"; ?>"><? if (!isset($_SESSION['zeilen'])) echo "<blink>"; ?>Voreinstellungen<? if (!isset($_SESSION['zeilen'])) echo "</blink>"; ?></a> 
| <a target="_blank" href="turnuspopup.php">Turnus Pop-Up</a> 
| <a target="_blank" href="/yalampgallery/index.php">Bilderalben</a> 
| <a target="_blank" href="userlist.php">Userliste</a> 
| <a target="_blank" href="last5.php">Last <?=$_SESSION['numlast'] ?></a> 
| <a target="_blank" href="keinstress.php">Nachlesen</a> 
| <a target="_blank" href="kolumnelist.php">Kolumnen</a> 
| <a target="_blank" href="partylist.php">Events</a> 
| <a target="_blank" href="chatsearch.php">Suchen</a> 
| <a target="_blank" href="faq.php">FAQ</a> 
| <a target="_top" href="logout.php">Logout</a> 
<? 
if ($adminbit) {Ê?>
| <a target="_blank" href="admin.php">Admin</a> 
<? } ?>
]
<?
echo "</td>";

// turnus
if ($_SESSION['turnus']) {
	echo "<td valign=\"middle\" align=\"right\">";
	include("turnus.inc.php");
	echo "</td>";
}
echo "</tr></table>";

echo "</center>";
include("footer.inc.php");

?>
