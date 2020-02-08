<? // useractivate.php
session_start();
include("connect.inc.php");

$title = "Account-Aktivierung";
include("header.inc.php");

if ($_REQUEST['key']) {
	
	$key = addslashes($_REQUEST['key']);
	$sql = "SELECT * FROM chat_user WHERE cookie = '$key' AND cookie != '' AND UID > 0 AND account = 'pending' LIMIT 1;";
	$result = mysql_query($sql);
	if ($result && mysql_num_rows($result) > 0) {
		$user = mysql_fetch_array($result);
		
		$_SESSION['UID'] = $user['UID'];
		$_SESSION['username'] = $user['username'];
		
		// login
		$sql = "INSERT chat_logins SET UID = '".$_SESSION['UID']."', method = 'activate', ip = '".$_SERVER['REMOTE_ADDR']."', host = '".gethostbyaddr($_SERVER['REMOTE_ADDR'])."', browser = '".addslashes($_SERVER["HTTP_USER_AGENT"])."', intime = NOW(), outtime = NOW();";
		mysql_query($sql);
		
		// prep seenevents
		$sql = "SELECT AID FROM party_data WHERE 1";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_row($result)) $_SESSION['seenevents'][] = $row[0];
		
		// prep seenkolumnen
		$sql = "SELECT KID FROM kolumne_data WHERE 1";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_row($result)) $_SESSION['seenkolumnen'][] = $row[0];
		
		// save prefs
		$prefs = addslashes(session_encode());
		$sql = "UPDATE chat_user SET preferences = '$prefs', account = 'active' WHERE UID = '".$_SESSION['UID']."' LIMIT 1;";
		mysql_query($sql);
		
		echo "Account '".$_SESSION['username']."' (UID ".$_SESSION['UID'].") erfolgreich aktiviert. Willkommen !<br><br><a href=\"index.php\">Weiter zum Chat</a>";
		
	} else echo "Keinen solchen Aktivierungs-Code in der Datenbank.";
} else {
	echo "<form>Code: <input type=\"text\" name=\"key\" size=\"32\"> <input type=\"submit\" value=\"Senden\"></form>";
}
include("footer.inc.php");
?>