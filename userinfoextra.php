<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("functions2.inc.php");
include("admin.inc.php");

if (!$_REQUEST['id']) {
	$title = "UID?";
	include("header.inc.php");
	echo "<form><select name=\"id\" size=\"1\" onChange=\"document.forms[0].submit()\"><option value=\"0\">--w&auml;hle einen User--</option>";
	$sql = "SELECT UID,username FROM chat_user WHERE UID > 0 ORDER BY username ASC;";
	$subresult = mysql_query($sql);
	while ($subrow = mysql_fetch_array($subresult)) {
		echo "<option value=\"".$subrow['UID']."\">".htmlentities($subrow['username'])."</option>";
	}
	echo "</select> <input type=\"submit\" value=\"Go!\"></form>";
	include("footer.inc.php");
	exit;
}
else $id = addslashes($_REQUEST['id']);

$sql = "SELECT * FROM chat_user WHERE UID = '$id' AND UID > 0 LIMIT 1;";
$row = mysql_fetch_array(mysql_query($sql));

$title = "Infos &uuml;ber '".htmlentities($row['username'])."'";
include("header.inc.php");
	echo "<form><select name=\"id\" size=\"1\" onChange=\"document.forms[0].submit()\"><option value=\"0\">--w&auml;hle einen User--</option>";
	$sql = "SELECT UID,username FROM chat_user WHERE UID > 0 ORDER BY username ASC;";
	$subresult = mysql_query($sql);
	while ($subrow = mysql_fetch_array($subresult)) {
		echo "<option value=\"".$subrow['UID']."\"".($subrow['UID'] == $id ? " selected=\"selected\"" : "").">".htmlentities($subrow['username'])."</option>";
	}
	echo "</select> <input type=\"submit\" value=\"Go!\"></form>";
	
	$sql = "SELECT * FROM chat_logins WHERE UID = {$row['UID']} ORDER BY intime DESC LIMIT 1;";
	$subresult = mysql_query($sql);
	$lastlogin = mysql_fetch_array($subresult);
	
?>
<table border="1" cellpadding="4">
<tr><td>
	<table border="0" cellpadding="2">
		<tr>
			<td>ID</td>
			<td><?=($row['UID']) ?></td>
		</tr>
		<tr>
			<td>Username</td>
			<td><?=htmlentities($row['username']) ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=username">Edit</a></td>
		</tr>
		<tr>
			<td>M/F</td>
			<td><?=($row['mf']) ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=mf">Edit</a></td>
		</tr>
		<tr>
			<td>Fullname</td>
			<td><?=(in_array("fullname",explode(",",$row['disclose'])) ? "" : "<i>").($row['fullname']).(in_array("fullname",explode(",",$row['disclose'])) ? "" : "</i>") ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=fullname">Edit</a></td>
		</tr>
		<tr>
			<td>Herkunft</td>
			<td><?=(in_array("herkunft",explode(",",$row['disclose'])) ? "" : "<i>").($row['herkunft']).(in_array("herkunft",explode(",",$row['disclose'])) ? "" : "</i>") ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=herkunft">Edit</a></td>
		</tr>
		<tr>
			<td>Email</td>
			<td><?=(in_array("email",explode(",",$row['disclose'])) ? "" : "<i>")."<a href=\"mailto:".htmlentities(stripslashes($row['email']))."\">".htmlentities(stripslashes($row['email']))."</a>".(in_array("email",explode(",",$row['disclose'])) ? "" : "</i>") ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=email">Edit</a></td>
		</tr>
		<tr>
			<td>Natel</td>
			<td><?=($_SESSION['UID'] == 1 ? "<a href=\"mailto:".str_replace(" ","",str_replace("-","",str_replace("+","00",str_replace("+41","0",str_replace("/","",$row['natel'])))))."@sms.switch.ch\" target=\"_blank\">" : "") ?><?=(in_array("natel",explode(",",$row['disclose'])) ? "" : "<i>").htmlentities($row['natel']).(in_array("natel",explode(",",$row['disclose'])) ? "" : "</i>") ?><?=($_SESSION['UID'] == 1 ? "</a>" : "") ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=natel">Edit</a></td>
		</tr>
		<tr>
			<td>Disclose</td>
			<td><?=$row['disclose'] ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=disclose">Edit</a></td>
		</tr>
		<tr>
			<td>Skype</td>
			<td><? if (stripslashes($row['skype'])) { ?><a href="skype://<?=stripslashes($row['skype']) ?>"><img src="http://goodies.skype.com/graphics/skypeme_btn_small_white.gif" border=0></a><? } else echo "<i>kein skype</i>";?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=skype">Edit</a></td>

		</tr>

		<tr>
			<td>Geburtstag</td>
			<td><? if ((integer)substr($row['geburi'],0,4)) echo d2str($row['geburi'])." (Alter: ".round((time()-d2unix($row['geburi']))/(365.24*24*3600),2)."y)"; else echo "<i>noch nicht angegeben</i>"; ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=geburi">Edit</a></td>
		</tr>
		<tr>
			<td>Beschreibung</td>
			<td><?=stripslashes($row['beschreibung']) ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=beschreibung">Edit</a></td>
		</tr>
		<tr>
			<td>Homepage</td>
			<td><a target="_blank" href="<?=stripslashes($row['homepage']) ?>"><?=stripslashes($row['homepage']) ?></a></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=homepage">Edit</a></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td></td><td></td>
		</tr>
		<tr>
			<td>User-Status</td>
			<td><?=$row['status'] ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=status">Edit</a></td>
		</tr>
		<tr>
			<td>Account-Status</td>
			<td><?=$row['account'] ?></td>
			<td><a href="userdataedit.php?id=<?=$id ?>&column=account">Edit</a></td>
		</tr>
		<tr>
			<td>Registriert am</td>
			<td><?=htmlentities(dt2str($row['regdate'])) ?></td>
		</tr>
		<tr>
			<td>Zuletzt hier am</td>
			<td><?=($lastlogin['method'] != "login" && $lastlogin['method'] != "persistentcookie" ? "<font color=\"red\">".htmlentities(dt2str($lastlogin['outtime']))." ({$lastlogin['method']})</font>" : htmlentities(dt2str($lastlogin['outtime']))) ?></td>
			<td></td>
		</tr>
		<tr>
			<td>Zuletzt hier von:</td>
			<td><?=htmlentities($lastlogin['IP']) ?></td>
			<td><a href="userlogins.php?id=<?=$id ?>">Logins</a></td>
		</tr>
		<tr>
			<td></td>
			<td><?=htmlentities($lastlogin['host']) ?></td>
			<td><a href="kickban.php?kickid=<?=$id ?>">Kick/ban</a></td>
		</tr>
		<tr>
			<td align="top">User mit &auml;hnlicher IP:</td>
			<td>
<?
				// unsere IP ist in $lastlogin['IP']
								
				$basesql = "SELECT chat_logins.*,chat_user.username,MAX(chat_logins.intime) AS lasttime FROM chat_logins LEFT JOIN chat_user ON chat_logins.UID = chat_user.UID WHERE chat_logins.UID != {$row['UID']} AND ";
				$basesql2 = " GROUP BY chat_logins.UID ORDER BY chat_user.username ASC;";
				$sql = $basesql."chat_logins.IP = '{$lastlogin['IP']}'".$basesql2;
				$result = mysql_query($sql);
				
				$i = 0;
				
				while (mysql_num_rows($result) < 1 && $i < 7) { // wir haben noch nichts gefunden AND sind noch nicht ins unendliche abgeDAUt..
					$i++;
					$sql = $basesql."chat_logins.IP LIKE '".substr($lastlogin['IP'],0,-$i)."%'".$basesql2;
					$result = mysql_query($sql);
				}
				// wir haben jetzt ein gŸltiges $result oder (wenn wir mehr als n=7 mal geloopt sind) eben nicht
				if ($result) {
					echo "<table cellspacing=\"2\">";
					echo "<tr><td>User</td><td>IP</td><td>Zuletzt am</td></tr>\n";
					while ($userrow = mysql_fetch_array($result)) {
						echo "<tr><td>".($userrow['username'] && $userrow['UID'] != 0 ? "<a href=\"userinfo.php?id={$userrow['UID']}\">".htmlentities($userrow['username'])."</a>" : "User#".$userrow['UID'])."</td><td>".("<b>".substr($userrow['IP'],0,strlen($lastlogin['IP'])-$i)."</b>").(substr($userrow['IP'],strlen($lastlogin['IP'])-$i))."</td><td>".dt2str($userrow['lasttime'])."</td></tr>\n";
					}
					echo "</table>";
				} else {
					echo "<i>(keine &auml;hnlichen IPs)</i>";
				}
?>
			</td>
			<td></td>
		</tr>
		<tr>
			<td valign="top">Onlinezeit (seit dem 6.5.2003)</td>
			<td>
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
		</td>
		<td></td>
	</tr>
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
		<tr>
		<td>Durchschnittliche Aktivit&auml;t</td>
		<td><?=($timespent[0] ? round(($posts[0] / ($timespent[0] / 3600)),2) : 0)." Beitr&auml;ge / Stunde" ?></td>
		<td></td>
	</tr>		
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
<br><a href="userdataedit.php?id=<?=$id ?>&column=ownpic">Edit</a></td>
</td>
</tr>
</table>

<?
include("footer.inc.php");