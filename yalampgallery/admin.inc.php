<?
//if ($_SESSION['UID'] == 1) { //hehe, arska only.. // veraltet, jetzt mit adminbit in DB (requires connect.inc.php !)

if ($_SESSION['UID']) {
	$sql = "SELECT status FROM chat_user WHERE UID = ".$_SESSION['UID'].";";
	$result = mysql_query($sql);
	
	$status = mysql_fetch_row($result);
	$status = $status[0];
} else { // nicht eingeloggt -> kein admin..
	$status = "user";
}

if ($status != "admin" && $status != "god") {
	if ($onlytestforadminbit) $adminbit = false;
	else {
		$title = "Admin only";
		include("header.inc.php");
		echo "Nur f&uuml;r Admins.. sorry !";
		include("footer.inc.php");
		exit;
	}
} else $adminbit = true;


?>