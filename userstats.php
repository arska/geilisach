<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("admin.inc.php");

$title = "Accesszeit Statistiken";
include("header.inc.php");
echo "Anzuzeigen:<br>";
echo "<form><input type=\"checkbox\" name=\"stunde\" value=\"Tageszeit\"";
if ($_REQUEST['stunde']) echo " checked=\"checked\"";
echo "> Tageszeit<br><input type=\"checkbox\" name=\"monat\" value=\"Tag des Monats\"";
if ($_REQUEST['monat']) echo " checked=\"checked\"";
echo "> Tag des Monats<br><input type=\"checkbox\" name=\"tag\" value=\"Wochentag\"";
if ($_REQUEST['tag']) echo " checked=\"checked\"";
echo "> Wochentag<br><input type=\"submit\" value=\"Go!\"></form><br><br>";
$sql = "SELECT SUM(UNIX_TIMESTAMP(outtime)-UNIX_TIMESTAMP(intime)) FROM chat_logins WHERE outtime <> '0000-00-00 00:00:00';";
$subresult = mysql_query($sql);
$timespent = mysql_fetch_row($subresult);
echo "Totale onlinezeit (seit 6.5.2003):<br>";
echo round($timespent[0])." Sekunden<br>";
echo round($timespent[0]/60,2)." Minuten<br>";
echo round($timespent[0]/3600,2)." Stunden<br>";
echo round($timespent[0]/(3600*24),2)." Tage<br>";
echo round($timespent[0]/(3600*24*7),2)." Wochen<br>";
echo round($timespent[0]/(3600*24*30),2)." Monate<br>";
echo round($timespent[0]/(3600*24*365.24),2)." Jahre<br>";
echo "<br><br>";

if ($_REQUEST['stunde']) {
	echo "Stunde:<br>";
	echo "<table border=\"1\">";
	echo "<tr><td>user</td>";
	for ($x=0;$x<24;$x++) echo "<td>".str_pad($x,2,"0",STR_PAD_LEFT).":xx</td>";
	echo "<td>alle</td>";
	echo "</tr>";
	
	$sql = "SELECT UID,username FROM chat_user WHERE UID > 0;";
	$result = mysql_query($sql);
	while ($user = mysql_fetch_array($result)) {
		echo "<tr><td><a href=\"userinfo.php?id=".$user['UID']."\">".htmlentities($user['username'])."</a></td>";
		for ($x=0;$x<24;$x++) {
			$sql = "SELECT SUM(60*60 - (CASE WHEN HOUR(intime)=$x THEN MINUTE(intime)*60+SECOND(intime) ELSE 0 END) - (CASE WHEN HOUR(outtime)=$x THEN (60*60-MINUTE(outtime)*60-SECOND(outtime)) ELSE 0 END)) FROM chat_logins WHERE ($x BETWEEN HOUR(intime) AND HOUR(outtime) OR (HOUR(outtime) < HOUR(intime) AND ($x <= HOUR(outtime) OR $x >= HOUR(intime)))) AND outtime <> '0000-00-00 00:00:00' AND UID = '".$user['UID']."';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
		}
			$sql = "SELECT SUM(UNIX_TIMESTAMP(outtime)-UNIX_TIMESTAMP(intime)) FROM chat_logins WHERE outtime <> '0000-00-00 00:00:00' AND UID = '".$user['UID']."';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
			
		echo "</tr>";
		
	}
	
		echo "<tr><td>alle</td>";
		for ($x=0;$x<24;$x++) {
			$sql = "SELECT SUM(60*60 - (CASE WHEN HOUR(intime)=$x THEN MINUTE(intime)*60+SECOND(intime) ELSE 0 END) - (CASE WHEN HOUR(outtime)=$x THEN (60*60-MINUTE(outtime)*60-SECOND(outtime)) ELSE 0 END)) FROM chat_logins WHERE ($x BETWEEN HOUR(intime) AND HOUR(outtime) OR (HOUR(outtime) < HOUR(intime) AND ($x <= HOUR(outtime) OR $x >= HOUR(intime)))) AND outtime <> '0000-00-00 00:00:00';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
		}
			$sql = "SELECT SUM(UNIX_TIMESTAMP(outtime)-UNIX_TIMESTAMP(intime)) FROM chat_logins WHERE outtime <> '0000-00-00 00:00:00';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
	
		echo "</tr>";
	
	
	echo "</table>";
	
	echo "<br><br>";
}
if ($_REQUEST['monat']) {
	echo "Tag des Monats:";
	echo "<table border=\"1\">";
	echo "<tr><td>user</td>";
	for ($x=1;$x<32;$x++) echo "<td>".$x."</td>";
	echo "</tr>";
	
	$sql = "SELECT UID,username FROM chat_user WHERE UID > 0;";
	$result = mysql_query($sql);
	while ($user = mysql_fetch_array($result)) {
		echo "<tr><td><a href=\"userinfo.php?id=".$user['UID']."\">".htmlentities($user['username'])."</a></td>";
		for ($x=1;$x<32;$x++) {
			$sql = "SELECT SUM(24*60*60 - (CASE WHEN DAYOFMONTH(intime)=$x THEN HOUR(intime)*60*60+MINUTE(intime)*60+SECOND(intime) ELSE 0 END) - (CASE WHEN DAYOFMONTH(outtime)=$x THEN (24*60*60-HOUR(outtime)*60*60-MINUTE(outtime)*60-SECOND(outtime)) ELSE 0 END)) FROM chat_logins WHERE ($x BETWEEN DAYOFMONTH(intime) AND DAYOFMONTH(outtime) OR (DAYOFMONTH(outtime) < DAYOFMONTH(intime) AND ($x <= DAYOFMONTH(outtime) OR $x >= DAYOFMONTH(intime)))) AND outtime <> '0000-00-00 00:00:00' AND UID = '".$user['UID']."';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
		}
		$sql = "SELECT SUM(UNIX_TIMESTAMP(outtime)-UNIX_TIMESTAMP(intime)) FROM chat_logins WHERE outtime <> '0000-00-00 00:00:00' AND UID = '".$user['UID']."';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
		echo "</tr>";
		
	}
	
		echo "<tr><td>alle</td>";
		for ($x=1;$x<32;$x++) {
			$sql = "SELECT SUM(24*60*60 - (CASE WHEN DAYOFMONTH(intime)=$x THEN HOUR(intime)*60*60+MINUTE(intime)*60+SECOND(intime) ELSE 0 END) - (CASE WHEN DAYOFMONTH(outtime)=$x THEN (24*60*60-HOUR(outtime)*60*60-MINUTE(outtime)*60-SECOND(outtime)) ELSE 0 END)) FROM chat_logins WHERE ($x BETWEEN DAYOFMONTH(intime) AND DAYOFMONTH(outtime) OR (DAYOFMONTH(outtime) < DAYOFMONTH(intime) AND ($x <= DAYOFMONTH(outtime) OR $x >= DAYOFMONTH(intime)))) AND outtime <> '0000-00-00 00:00:00';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
		}
		$sql = "SELECT SUM(UNIX_TIMESTAMP(outtime)-UNIX_TIMESTAMP(intime)) FROM chat_logins WHERE outtime <> '0000-00-00 00:00:00';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
		echo "</tr>";
	
	
	echo "</table>";
	echo "<br><br>";
}

if ($_REQUEST['tag']) {
	echo "Wochentag:";
	echo "<table border=\"1\">";
	echo "<tr><td>user</td>";
	$wochentage = array('mo','di','mi','do','fr','sa','so');
	for ($x=0;$x<7;$x++) echo "<td>".$wochentage[$x]."</td>";
	echo "</tr>";
	
	$sql = "SELECT UID,username FROM chat_user WHERE UID > 0;";
	$result = mysql_query($sql);
	while ($user = mysql_fetch_array($result)) {
		echo "<tr><td><a href=\"userinfo.php?id=".$user['UID']."\">".htmlentities($user['username'])."</a></td>";
		for ($x=0;$x<7;$x++) {
			$sql = "SELECT SUM(24*60*60 - (CASE WHEN WEEKDAY(intime)=$x THEN HOUR(intime)*60*60+MINUTE(intime)*60+SECOND(intime) ELSE 0 END) - (CASE WHEN WEEKDAY(outtime)=$x THEN (24*60*60-HOUR(outtime)*60*60-MINUTE(outtime)*60-SECOND(outtime)) ELSE 0 END)) FROM chat_logins WHERE ($x BETWEEN WEEKDAY(intime) AND WEEKDAY(outtime) OR (WEEKDAY(outtime) < WEEKDAY(intime) AND ($x <= WEEKDAY(outtime) OR $x >= WEEKDAY(intime)))) AND outtime <> '0000-00-00 00:00:00' AND UID = '".$user['UID']."';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
		}
		$sql = "SELECT SUM(UNIX_TIMESTAMP(outtime)-UNIX_TIMESTAMP(intime)) FROM chat_logins WHERE outtime <> '0000-00-00 00:00:00' AND UID = '".$user['UID']."';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
		echo "</tr>";
		
	}
	
		echo "<tr><td>alle</td>";
		for ($x=0;$x<7;$x++) {
			$sql = "SELECT SUM(24*60*60 - (CASE WHEN WEEKDAY(intime)=$x THEN HOUR(intime)*60*60+MINUTE(intime)*60+SECOND(intime) ELSE 0 END) - (CASE WHEN WEEKDAY(outtime)=$x THEN (24*60*60-HOUR(outtime)*60*60-MINUTE(outtime)*60-SECOND(outtime)) ELSE 0 END)) FROM chat_logins WHERE ($x BETWEEN WEEKDAY(intime) AND WEEKDAY(outtime) OR (WEEKDAY(outtime) < WEEKDAY(intime) AND ($x <= WEEKDAY(outtime) OR $x >= WEEKDAY(intime)))) AND outtime <> '0000-00-00 00:00:00';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
		}
		$sql = "SELECT SUM(UNIX_TIMESTAMP(outtime)-UNIX_TIMESTAMP(intime)) FROM chat_logins WHERE outtime <> '0000-00-00 00:00:00';";
			$subresult = mysql_query($sql);
			$timespent = mysql_fetch_row($subresult);
			echo "<td>";
			if ($timespent[0] > 0) echo (floor(($timespent[0]/60)*10)/10)."m";
			else echo "0";
			echo "</td>";
		echo "</tr>";
	
	
	echo "</table>";
}

include("footer.inc.php");
?>