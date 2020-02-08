<?
include("secrets.inc.php")

if ($link = mysql_pconnect($dbhost,$dbuser,$dbpass)) {
	if (mysql_select_db($dbname, $link) == 0) {
		$title = "FEHLER: Datenbank nicht anw&auml;hlbar";
		include("header.inc.php");
		echo "Die Datenbank konnte nicht anw&auml;hlt werden.<br><br>Dies ist ein schwerwiegendes Problem - bitte ein <a href=\"mailto:arska@arska.ch\">Mail an den Admin</a>";
		include("footer.inc.php");
		exit;
	}
} else {
	$title = "FEHLER: Datenbankserver nicht erreichbar";
	include("header.inc.php");
	echo "Datenbankserver nicht erreichbar $php_errormsg.<br><br>";
	echo "Dies ist meist ein tempor&auml;res Problem - bitte etwas Geduld.";
	include("footer.inc.php");
	exit;
}
?>