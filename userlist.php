<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");

$title = "Liste der User";
include("header.inc.php");
?>
<center>
<table border="1"><tr><td>
<table border="0" cellspacing="" cellpadding="4">
<tr><td>Username:</td><td>War zuletzt online:</td><td align="right">Aufenhaltsdauer:</td></tr>
<tr></tr>
<?
$sql = "SELECT chat_user.username,chat_user.UID FROM chat_user WHERE chat_user.UID > 0 GROUP BY chat_user.UID ORDER BY chat_user.lastaccess DESC;";
$result = mysql_query($sql);
while ($row = mysql_fetch_array($result)) {
	
	$sql = "SELECT chat_logins.intime,chat_logins.outtime FROM chat_logins WHERE UID = '".$row['UID']."' ORDER BY chat_logins.intime DESC LIMIT 1;";
	$subresult = mysql_query($sql);
	$times= mysql_fetch_array($subresult);
	
	echo "<tr><td><a href=\"userinfo.php?id=".$row['UID']."\">".htmlentities($row['username'])."</a></td>";
	echo "<td>".dt2str($times['outtime'])."</td>";
	echo "<td align=\"right\">";

	if ((time() - dt2unix($times['outtime'])) <= 60) echo "<font color=\"red\">";
		
	if (substr($times['outtime'],0,3)) {
	
		$dauer = (dt2unix($times['outtime']) - dt2unix($times['intime']));
		
		if ($dauer >= 0) {
		
			if ($dauer > 3600*24) {
				echo str_pad((integer)($dauer / (3600*24)), 2, "0", STR_PAD_LEFT)."d ";
				$dauer = $dauer % (3600*24);
			}
			if ($dauer > 3600) {
				echo str_pad((integer)($dauer / 3600), 2, "0", STR_PAD_LEFT)."h ";
				$dauer = $dauer % 3600;
			}
			if ($dauer > 60) {
				echo str_pad((integer)($dauer / 60), 2, "0", STR_PAD_LEFT)."m ";
				$dauer = $dauer % 60;
			}
			
			if ((dt2unix($times['outtime']) - dt2unix($times['intime'])) < 600) echo str_pad($dauer, 2, "0", STR_PAD_LEFT)."s";
		} else echo "-";
	} else echo "-";
	
	if ((time() - dt2unix($times['outtime'])) <= 60) echo "</font>";
	
	echo "</td></tr>\n";
}
?>
</table>
</td></tr>
<tr><td><a href="geburiliste.php">Liste der Geburtstage</a></td></tr></table>
</center>
<?
include("footer.inc.php");
?>