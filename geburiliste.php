<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");

$title = "Geburiliste";
include("header.inc.php");

$sql = "SELECT UID,username,geburi FROM chat_user WHERE LEFT(geburi,4) <> '0000' ORDER BY RIGHT(geburi,5) ASC;";
$result = mysql_query($sql);

echo "<table>";

while ($row = mysql_fetch_row($result)) {
	echo "<tr>";
	echo "<td>";
	echo "<a href=\"userinfo.php?id={$row[0]}\">{$row[1]}</a>";
	echo "</td>";
	echo "<td>";
	if (substr($row[2],5,5) < strftime("%m-%d")) { // geburi dieses Jahr schon vorbei ? -> nächstes jahr wieder
		$jahr = (integer)strftime("%Y") + 1;
	} else {
		$jahr = (integer)strftime("%Y");
	}
	echo unix2shortstr(d2unix($jahr."-".substr($row[2],5,5)));
	echo " (".($jahr-substr($row[2],0,4)).")";
	;
	echo "</td>";
	echo "</tr>";
}

echo "</table>";
include("footer.inc.php");
?>