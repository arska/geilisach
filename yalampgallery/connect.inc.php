<?
include("../secrets.inc.php")

if ($link = mysql_pconnect($dbhost,$dbuser,$dbpass)) {
	if (mysql_select_db($dbname, $link) == 0) {
		echo "Datenbank $dbname link $link nicht selektierbar";
		exit;
	}
} else {
	echo "Datenbankserver nicht erreichbar $php_errormsg";
	exit;
}
?>