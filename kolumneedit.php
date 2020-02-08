<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("admin.inc.php");

if ($_REQUEST['kill']) {
	$sql = "DELETE FROM kolumne_anwesend WHERE KUID = ".addslashes($_REQUEST['kill'])." LIMIT 1;";
	mysql_query($sql);
}

$title = "Kolumne-Anwesende editieren";
include("header.inc.php");

if ($_REQUEST['kid']) {
	if ($_REQUEST['add']) {
		$sql = "INSERT INTO kolumne_anwesend (KID,UID) VALUES ('".addslashes($_REQUEST['kid'])."','".addslashes($_REQUEST['add'])."');";
		mysql_query($sql);
	}
	$sql = "SELECT anlass,andere,anlassdate FROM kolumne_data WHERE kolumne_data.KID = ".addslashes($_REQUEST['kid'])." LIMIT 1;";
	$result = mysql_query($sql);
	$kolumne = mysql_fetch_array($result);
	
	$sql = "SELECT kolumne_anwesend.KUID,chat_user.UID,chat_user.username FROM kolumne_anwesend,chat_user WHERE kolumne_anwesend.KID = ".addslashes($_REQUEST['kid'])." AND kolumne_anwesend.UID = chat_user.UID;";
	$result = mysql_query($sql);
	
	echo "Bei '".($kolumne['anlass'])."' am ".($kolumne['anlassdate'])." waren folgende Personen anwesend:<br>";
	echo "<form><input type=\"hidden\" name=\"kid\" value=\"".$_REQUEST['kid']."\">";
	echo "<table border=\"1\" cellpadding=\"10\">";
	echo "<tr><td>name</td><td>KILL!</td></tr>";
	while ($row = mysql_fetch_array($result)) {
		echo "<tr><td><a target=\"_blank\" href=\"userinfo.php?id=".$row['UID']."\">".$row['username']."</td><td><input type=\"submit\" name=\"kill\" value=\"".$row['KUID']."\"></td></tr>";
	}
	echo "</table>";
	echo "</form>";
	echo "..und dazu noch: ".($kolumne['andere']);
	
	echo "<form>";
	echo "<form><input type=\"hidden\" name=\"kid\" value=\"".$_REQUEST['kid']."\">";
	echo "F&uuml;ge User <select name=\"add\" size=\"1\"><option value=\"0\">--w&auml;hle einen User--</option>";
	$sql = "SELECT UID,username FROM chat_user ORDER BY username ASC;";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		echo "<option value=\"".$row['UID']."\">".htmlentities($row['username'])."</option>";
	}
	echo "</select> hinzu. <input type=\"submit\" value=\"Go!\">";
	echo "</form>";
	
} else {
	$sql = "SELECT * FROM kolumne_data ORDER BY anlassdate DESC;";
	$result = mysql_query($sql);
	echo "<form>";
	echo "<table border=\"1\" cellpadding=\"10\">";
	echo "<tr><td>Anlass</td><td>Titel</td><td>Datum</td><td>EDIT!</td></tr>";
	while ($row = mysql_fetch_array($result)) {
		echo "<tr><td>".stripslashes($row['anlass'])."</td><td>".stripslashes($row['titel'])."</td><td>".$row['anlassdate']."</td><td><input type=\"submit\" name=\"kid\" value=\"".$row['KID']."\"></td></tr>";
	}
	echo "</table>";
	echo "</form>";
	
	echo "<form>";
	echo "F&uuml;ge zu Anlass <select name=\"kid\" size=\"1\"><option value=\"0\">--w&auml;hle einen Anlass--</option>";
	$sql = "SELECT KID,anlass FROM kolumne_data ORDER BY anlassdate DESC;";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		echo "<option value=\"".$row['KID']."\">".stripslashes($row['anlass'])."</option>";
	}
	echo "</select> User <select name=\"add\" size=\"1\"><option value=\"0\">--w&auml;hle einen User--</option>";
	$sql = "SELECT UID,username FROM chat_user ORDER BY username ASC;";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		echo "<option value=\"".$row['UID']."\">".htmlentities($row['username'])."</option>";
	}
	echo "</select> hinzu. <input type=\"submit\" value=\"Go!\">";
	echo "</form>";

}
include("footer.inc.php");

?>