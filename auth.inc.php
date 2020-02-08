<?

session_start();

if (!$_SESSION['UID']) {
	$url = urlencode($_SERVER['PHP_SELF']);
	
	if ($_REQUEST['q']) {
		$url .= urlencode("?q=".$_REQUEST['q']);
	}
	
	if ($_REQUEST['id']) {
		$url .= urlencode("?id=".$_REQUEST['id']);
	}
	
	header("Pragma: no-cache");
	header("Cache-Control: no-cache");
	header("Location: /login.php?url=$url");
	
	$title = "Auth";
	$refreshurl = "login.php?url=$url";
	include("header.inc.php");
	echo "Du bist (noch) nicht authenticated, es geht gleich <a href=\"/login.php?url=$url\">weiter..</a>";
	include("footer.inc.php");
	exit;
}

$sql = "SELECT *,UNIX_TIMESTAMP(banto) AS ban FROM chat_kickban WHERE UID = {$_SESSION['UID']} AND (UNIX_TIMESTAMP(chat_kickban.banto) >= UNIX_TIMESTAMP() OR banto = 0);";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) { // uh-oh, der benutzer ist gebannt...
	$kickeduid = $_SESSION['UID'];
	header("Location: login.php?kickban=$kickeduid");
	include("logout.inc.php");
	include("header.inc.php");
	echo "Du wurdest aus dem Chat gebannt. <a target=\"_top\" href=\"login.php?kickban=$kickeduid\">Weiter..</a>";
	include("footer.inc.php");
	exit;
} // sonst gehts weiter.. 

?>
