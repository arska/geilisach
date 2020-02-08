<?
include("connect.inc.php");
include("auth.inc.php");
$onlytestforadminbit = true;
include("admin.inc.php");

if ($_SESSION['autoextra'] && $adminbit) {
	header("Location: userinfoextra.php?id=".$_REQUEST['id']);
	exit;
}

include("functions.inc.php");
include("functions2.inc.php");


if (!$_REQUEST['id']) {
	$title = "UID?";
	include("header.inc.php");
	?>Und &uuml;ber wen m&ouml;chtest du Infos ?<br>W&auml;hle einen User von der <a href="userlist.php">Userliste</a><?
	include("footer.inc.php");
	exit;
}
else $id = addslashes($_REQUEST['id']);

$sql = "SELECT * FROM chat_user WHERE UID = '$id' AND UID > 0 LIMIT 1;";
$row = mysql_fetch_array(mysql_query($sql));

$row = array_map("stripslashes",$row);

$title = "Infos &uuml;ber '".htmlentities($row['username'])."'";
include("header.inc.php");

	echo "<form><select name=\"id\" size=\"1\" onChange=\"document.forms[0].submit()\"><option value=\"0\">--w&auml;hle einen User--</option>";
	$sql = "SELECT UID,username FROM chat_user WHERE UID > 0 ORDER BY username ASC;";
	$subresult = mysql_query($sql);
	while ($subrow = mysql_fetch_array($subresult)) {
		echo "<option value=\"".$subrow['UID']."\"".($subrow['UID'] == $id ? " selected=\"selected\"" : "").">".htmlentities($subrow['username'])."</option>";
	}
	echo "</select> <input type=\"submit\" value=\"Go!\"></form>";
?>
<table border="1" cellpadding="4">
<? if ($adminbit || $id == $_SESSION['UID']) echo "<tr><td>".($id == $_SESSION['UID'] ? "<a href=\"userprefs.php\">Deine Daten editieren</a>" : "")."</td><td align=\"right\">".($adminbit ? "<a href=\"userinfoextra.php?id=$id\">Userinfo-Extra</a>" : "")."</td></tr>"; ?>
<tr><td>
	<table border="0" cellpadding="2">
		<tr><td>Username</td><td><?=htmlentities($row['username']) ?> <?="<img src=\"bilder/".($row['mf']=="m" ? "male.gif" : "female.gif")."\">" ?></td></tr>
		<tr><td>Name</td><td><? if (in_array("fullname",explode(",",$row['disclose']))) echo (stripslashes($row['fullname'])); else echo "<i>".htmlentities($row['username'])." m&ouml;chte dies nicht bekanntgeben.</i>"; ?></td></tr>
		<tr><td>Herkunft</td><td><? if (in_array("herkunft",explode(",",$row['disclose']))) echo (stripslashes($row['herkunft'])); elseif (!$row['herkunft']) echo "<i>noch nicht angegeben</i>"; else echo "<i>".htmlentities($row['username'])." m&ouml;chte dies nicht bekanntgeben.</i>"; ?></td></tr>
		<tr><td>Email</td><td><? if (in_array("email",explode(",",$row['disclose']))) echo "<a href=\"mailto:".htmlentities(stripslashes($row['email']))."\">".htmlentities(stripslashes($row['email']))."</a>"; else echo "<i>".htmlentities($row['username'])." m&ouml;chte dies nicht bekanntgeben.</i>"; ?></td></tr>
		<tr><td>Natel</td><td><? if (in_array("natel",explode(",",$row['disclose']))) echo htmlentities(stripslashes($row['natel'])); else echo "<i>".htmlentities($row['username'])." m&ouml;chte dies nicht bekanntgeben.</i>"; ?></td></tr>
		<tr><td>Skype</td><td><? if (stripslashes($row['skype'])) { ?><a href="skype://<?=stripslashes($row['skype']) ?>"><img src="http://goodies.skype.com/graphics/skypeme_btn_small_white.gif" border=0></a><? } else echo "<i>kein skype</i>";?></td></tr>
		<tr><td>Geburtstag</td><td><? if ((integer)substr($row['geburi'],0,4)) echo d2str($row['geburi'])." (Alter: ".round((time()-d2unix($row['geburi']))/(365.24*24*3600),2)."y)"; else echo "<i>noch nicht angegeben</i>"; ?></td></tr>
		<tr><td>Beschreibung</td><td><? if ($row['beschreibung']) echo (stripslashes($row['beschreibung'])); else echo "<i>keine angegeben</i>"; ?></td></tr>
		<tr><td>Homepage</td><td><? if ($row['homepage']) echo "<a target=\"_blank\" href=\"".(stripslashes($row['homepage']))."\">".(stripslashes($row['homepage']))."</a>"; else echo "<i>keine angegeben</i>"; ?></td></tr>
		
		<tr><td>&nbsp;</td><td></td><td></td></tr>
		<tr><td>User-Status</td><td><?=$row['status'] ?></td></tr>
		<tr><td>Account-Status</td><td><?=$row['account'] ?></td></tr>
		<tr><td>Registriert am</td><td><?=htmlentities(dt2str($row['regdate'])) ?></td></tr>
		<tr><td>Zuletzt hier am</td><td><?=htmlentities(ts2str($row['lastaccess'])) ?></td></tr>
		<tr><td valign="top">Onlinezeit (seit dem 6.5.2003)</td><td>
<?	
	$sql = "SELECT SUM(UNIX_TIMESTAMP(outtime)-UNIX_TIMESTAMP(intime)) FROM chat_logins WHERE outtime <> '0000-00-00 00:00:00' AND UID = $id;";
	$subresult = mysql_query($sql);
	$timespent = mysql_fetch_row($subresult);
	echo round($timespent[0])." Sekunden<br>";
	echo round($timespent[0]/60,2)." Minuten<br>";
	echo round($timespent[0]/3600,2)." Stunden<br>";
	echo round($timespent[0]/(3600*24),2)." Tage<br>";
	echo round($timespent[0]/(3600*24*7),2)." Wochen<br>";
	echo round($timespent[0]/(3600*24*30),2)." Monate<br>";
	echo round($timespent[0]/(3600*24*365.24),2)." Jahre<br>";
?>
		</td></tr>
		<tr><td>Anzahl Beitr&auml;ge</td><td>
<?
	$sql = "SELECT count(PID) FROM chat_data WHERE UID = $id;";
	$subresult = mysql_query($sql);
	$posts = mysql_fetch_row($subresult);
	echo $posts[0];
?>
		</td></tr>
	<tr>
		<td>Anzahl Beitr&auml;ge mit Lols</td>

		<td><?
	$sql = "SELECT count(PID) FROM chat_data WHERE UID = $id AND kommentar LIKE '%lol%';";
	$subresult = mysql_query($sql);
	$lols = mysql_fetch_row($subresult);
	echo $lols[0]." (".($posts[0] ? round($lols[0] / ($posts[0] / 100),2) : 0)."%)";
		?></td>
		<td></td>
	</tr>

		<tr><td>Durchschnittliche Aktivit&auml;t</td><td>
<?=round(($posts[0] / ($timespent[0] / 3600)),2)." Beitr&auml;ge / Stunde" ?>
		</td></tr>
	
	<tr>
		<td>Durchschnittliche Post-L&auml;nge</td>

		<td><?
	$sql = "SELECT AVG(LENGTH(kommentar)) FROM chat_data WHERE UID = $id;";
	$subresult = mysql_query($sql);
	$schwanzlaenge = mysql_fetch_row($subresult);
	echo round($schwanzlaenge[0],2)." Buchstaben";
		?></td>
		<td></td>
	</tr>
	<tr>
		<td>Gesamte Post-Datenmenge</td>

		<td><?
	$sql = "SELECT SUM(LENGTH(kommentar)) FROM chat_data WHERE UID = $id;";
	$subresult = mysql_query($sql);
	$schwanzlaenge = mysql_fetch_row($subresult);
	if ($schwanzlaenge[0] > 1024*1024) echo round($schwanzlaenge[0]/1024/1024,2)." MB";
	elseif ($schwanzlaenge[0] > 1024) echo round($schwanzlaenge[0]/1024,2)." KB";
	else echo ($schwanzlaenge[0] ? $schwanzlaenge[0] : 0)." Bytes";
	
	
		?></td>
		<td></td>
	</tr>		

	</table>
</td>
<td>
<?
if ($row['ownpic']) {
	getimage($row['ownpic'],"/yalampgallery/showpicture.php?id=".$row['ownpic'],200, "./yalampgallery/", "_blank");
} else echo "<i>Kein Bild angegeben</i>";
?>
</td>
</tr>
</table>

<?
include("footer.inc.php");