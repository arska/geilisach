<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
$onlytestforadminbit = true;
include("admin.inc.php");

include("userdefaults.inc.php");


// Online-strings
// 
$timeout = 60;
$sincetime = date("YmdHis",time()-$timeout);
$sql = "SELECT UID,username,invisible FROM chat_user WHERE (lastaccess > $sincetime OR UID = {$_SESSION['UID']})";
if (!$adminbit) $sql .= " AND invisible = 0";
$sql .= " ORDER BY username ASC;";
$result = mysql_query($sql);

$usersonlinestring .= "In den letzten {$timeout}s war";
if (!isset($_SESSION['knownusers'])) $_SESSION['knownusers'] = Array();
$nowusers = array();

if (mysql_num_rows($result)==0) $usersonlinestring .=  " niemand online.";
elseif (mysql_num_rows($result)==1) {
	$user = mysql_fetch_array($result);
	if ($user['UID'] == $_SESSION['UID']) {
		$usersonlinestring .=  "st nur du, <a target=\"_blank\" href=\"userinfo.php?id=".$user['UID']."\">".($user['invisible'] ? "<i>" : "").htmlentities($user['username']).($user['invisible'] ? "</i>" : "")."</a>, online.";
	} else {
		$usersonlinestring .=  " nur <a target=\"_blank\" href=\"userinfo.php?id=".$user['UID']."\">".($user['invisible'] ? "<i>" : "").htmlentities($user['username']).($user['invisible'] ? "</i>" : "")."</a> online.";
	}
} else {
	$usersonlinestring .=  "en ".mysql_num_rows($result)." User online:<br>";
	
	$x = 0;
	while ($user = mysql_fetch_array($result)){
		if (!in_array($user['UID'],$_SESSION['knownusers'])) $usersonlinestring .=  "<b>"; // wenn noch nicht known user
		$usersonlinestring .=  "<a target=\"_blank\" href=\"userinfo.php?id=".$user['UID']."\">".($user['invisible'] ? "<i>" : "").htmlentities($user['username']).($user['invisible'] ? "</i>" : "")."</a>";
		if (!in_array($user['UID'],$_SESSION['knownusers'])) $usersonlinestring .=  "</b>";
		array_push($nowusers, $user['UID']);
		if ($x < (mysql_num_rows($result) - 1)) $usersonlinestring .=  ", "; // nicht letztes element
		if ($_SESSION['watchusers'] && !in_array($user['UID'],$_SESSION['knownusers'])) $extrabody = "bgcolor=\"red\"";
		$x++;
	}
}
$_SESSION['knownusers'] = $nowusers;

$title = "Chat-Output";
$refreshme = true;
include("header.inc.php");

// reload-saver
//

if ($_SESSION['reloadsaver']) echo "<script language=\"JavaScript\"> setTimeout(\"location.reload()\",".($_SESSION['refresh']*3*1000)."); setTimeout(\"location.reload()\",".($_SESSION['refresh']*5*1000)."); setTimeout(\"location.reload()\",".($_SESSION['refresh']*8*1000)."); setTimeout(\"location.reload()\",".($_SESSION['refresh']*13*1000)."); setTimeout(\"location.reload()\",".($_SESSION['refresh']*21*1000)."); </script>"; // 3/5/8/13/21 für 3/5/8/13/21-fache zeit, 1000 für mikrosek -> sek
echo "<center><table width=\"99%\"><tr><td align=\"left\"><font size=\"-1\">";

echo $usersonlinestring;

echo "</font></td><td align=\"center\"><font size=\"-1\">";

// Geburtstage
//

$sql = "SELECT UID,username,geburi FROM chat_user WHERE geburi LIKE '%-".date("m-d")."' OR geburi LIKE '%-".date("m-d",time()+24*3600)."' ORDER BY RIGHT(geburi, 5), username ASC;";
$result = mysql_query($sql);
//echo "<br>\n".$sql."<br>\n";
//echo mysql_num_rows($result)."<br>\n";

if (mysql_num_rows($result)) { // es hat jemand heute oder morgen geburtstag
	
	while ($row = mysql_fetch_row($result)) {
		
		echo "<a target=\"_blank\" href=\"userinfo.php?id={$row[0]}\">".htmlentities($row[1])."</a> hat ";
		if (substr($row[2],5,5) == date("m-d")) echo "heute";
		else echo "morgen";
		echo " Geburtstag (".round((time()-d2unix($row[2]))/(365.24*24*3600)).").<br>";
	}
	
} /* else { // kein geburtstag heute / morgen - MOTD   // MOTD wurde abgewählt bzw deaktiviert...

	$sql = "SELECT id,lastvote FROM chat_motd ORDER BY lastvote DESC LIMIT 1;";
	$result = mysql_query($sql);
	$row = mysql_fetch_row($result);
	
	if ($row[1] < (date("Ymd")."000000")) {  // veraltet => neues zufälliges wählen
		$sql = "SELECT id FROM chat_motd ORDER BY lastvote ASC;"; // liste der motd aktualisieren
		$result = mysql_query($sql);
		$motdlist = Array();
		while ($motdid = mysql_fetch_row($result)) array_push($motdlist,$motdid[0]);
		srand((double)microtime()*1000000);
		$motdid = $motdlist[rand(0,count($motdlist)-(1+floor(count($turnusliste)*0.50)))]; // neuen random-motd bestimmen, letzte 50% nicht wiederwählen
		$sql = "UPDATE chat_motd SET lastvote = NOW() WHERE id = '$motdid' LIMIT 1;"; // dieses bild voten.. tadaaa
		mysql_query($sql);
		
	} else $motdid = $row[0]; // es war nicht veraltet => bestehendes verwenden
	
	$sql = "SELECT message,autor FROM chat_motd WHERE ID = '$motdid' LIMIT 1;";
	$result = mysql_query($sql);
	$message = mysql_fetch_row($result);
	echo stripslashes($message[0])."</font><br><font size=\"-2\">(".stripslashes($message[1]).")</font>";
} */


// neue kolumnen
//

if (!isset($_SESSION['seenkolumnen'])) $_SESSION['seenkolumnen'] = Array();

$sql = "SELECT kid,titel FROM kolumne_data ORDER BY posttime DESC LIMIT {$_SESSION['numlast']};";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)) {
	$kolumnen[$row[0]] = $row[1];
}

for ($i=0; $i<count($kolumnen); $i++) {
	if (!in_array(key($kolumnen),$_SESSION['seenkolumnen'])) {
		if (!$newkolumnenflag) { // we have a winner here !
			echo "<br>Es hat neue Kolumnen: ";
			$newkolumnenflag = true;
		}
		if (!$firstkolumnenflag) { // die weiteren neuen nach dem ersten bekommen noch ein ", " vorgehängt..
			$firstkolumnenflag = true;
		} else echo ", ";
		
		echo "'<a target=\"_blank\" href=\"kolumneread.php?id=".key($kolumnen)."\">".stripslashes(current($kolumnen))."</a>'";
	}
	next($kolumnen);
}

// neue events
//

if (!isset($_SESSION['seenevents'])) $_SESSION['seenevents'] = Array();

$sql = "SELECT aid,anlass FROM party_data ORDER BY posttime DESC LIMIT {$_SESSION['numlast']};";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)) {
	$events[$row[0]] = $row[1];
}

for ($i=0; $i<count($events); $i++) {
	if (!in_array(key($events),$_SESSION['seenevents'])) {
		if (!$neweventflag) { // we have a winner here !
			echo "<br>Es hat neue Events: ";
			$neweventflag = true;
		}
		if (!$firsteventflag) { // die weiteren neuen nach dem ersten bekommen noch ein ", " vorgehängt..
			$firsteventflag = true;
		} else echo ", ";
		
		echo "'<a target=\"_blank\" href=\"partyread.php?id=".key($events)."\">".stripslashes(current($events))."</a>'";
	}
	next($events);
}



echo "</font></td><td align=\"right\"><font size=\"-1\">".unix2str(time())."</font></td></tr></table></center>";

// chat-output
//

?>
<ul>
<?

$sql = "SELECT chat_data.*,chat_user.username FROM chat_data LEFT JOIN chat_user ON chat_data.UID = chat_user.UID ORDER BY chat_data.PID DESC LIMIT ".$_SESSION['zeilen'].";";
$result = mysql_query($sql);
while ($row = mysql_fetch_array($result)) {
	if (substr($row['kommentar'],0,4) == "/me ") echo "<li> <i>".($row['username'] && $row['UID'] != 0 ? "<a target=\"_blank\" href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])." ".substr(stripslashes($row['kommentar']),4)."</i> <font size=\"-2\">(am ".ts2str($row['posttime']).")</font>\n";
	else echo "<li> ".stripslashes($row['kommentar'])." <font size=\"-2\">(von ".($row['username'] && $row['UID'] != 0 ? "<a target=\"_blank\" href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])." am ".ts2str($row['posttime']).")</font>\n";
}

echo "</ul>";

include("footer.inc.php");

// lastaccess updaten (legacy)
//

$sql = "UPDATE chat_user SET lastaccess = NOW(), lastip = '".$_SERVER['REMOTE_ADDR']."', preferences = '".addslashes(session_encode())."' WHERE UID = '".addslashes($_SESSION['UID'])."' LIMIT 1;";
if (!mysql_query($sql)) echo "old lastaccess failed\n";

// lastaccess updaten
//

$sql = "SELECT ID FROM chat_logins WHERE UID = '".addslashes($_SESSION['UID'])."' ORDER BY intime DESC LIMIT 1;";
$result = mysql_query($sql);
$lastloginid = mysql_fetch_row($result);
$sql = "UPDATE chat_logins SET outtime = NOW() WHERE ID = '".addslashes($lastloginid[0])."' LIMIT 1;";
if (!mysql_query($sql)) echo "new lastaccess failed\n"
?>