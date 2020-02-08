<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("admin.inc.php");

if ($status != "god") {
	$title = "Admin only";
	include("header.inc.php");
	echo "Nur f&uuml;r Gods.. sorry !";
	include("footer.inc.php");
	exit;

}
if ($_REQUEST['kill']) {
	
	$kill = addslashes($_REQUEST['kill']);
	
	$messages = array();
	
	$sql = "DELETE FROM chat_user WHERE UID = $kill LIMIT 1;";
	if (mysql_query($sql)) array_push($messages,"In chat_user gekillt: ".mysql_affected_rows());
	else array_push($messages,"L&ouml;schen des users misslang");
		
	$sql = "DELETE FROM party_vote WHERE UID = $kill;";
	if (mysql_query($sql)) array_push($messages,"In party_vote gekillt: ".mysql_affected_rows());
	else array_push($messages,"Killen aus party_vote misslang");

	$sql = "DELETE FROM kolumne_anwesend WHERE UID = $kill;";
	if (mysql_query($sql)) array_push($messages,"In kolumne_anwesend gekillt: ".mysql_affected_rows());
	else array_push($messages,"Killen aus kolumne_anwesend misslang");

	$sql = "DELETE FROM chat_logins WHERE UID = $kill;";
	if (mysql_query($sql)) array_push($messages,"In chat_logins gekillt: ".mysql_affected_rows());
	else array_push($messages,"Killen aus chat_logins misslang");

}


$title = "User L&ouml;schen";
include("header.inc.php");

if (count($messages) > 0) {
	for ($x=0;$x < count($messages);$x++) echo $messages[$x]."<br>\n";
}

$sql = "SELECT UID, username, fullname, regdate, lastedit, lastaccess, lastip FROM chat_user WHERE UID > 0 ORDER BY UID ASC;";
$result = mysql_query($sql);

echo "<table border=\"1\">\n";
echo "<tr><td>UID</td><td>Username</td><td>Full Name</td><td>Registration Date</td><td>Last Edit</td><td>Last Access</td><td>Last IP</td><td></td></tr>";
echo "<tr></tr>";

while ($row = mysql_fetch_row($result)) {
	echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".dt2str($row[3])."</td><td>".dt2str($row[4])."</td><td>".ts2str($row[5])."</td><td><a target=\"_blank\" href=\"http://www.area51.dk/cgi/ipw.cgi?ip=".$row[6]."\">".$row[6]."</a></td><td><form><input type=\"submit\" name=\"kill\" value=\"".$row[0]."\"></form></td></tr>";
}

echo "</table>";

include("footer.inc.php");
