<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("admin.inc.php");

$id = addslashes($_REQUEST['id']);

if (!isset($_REQUEST['limit'])) $_REQUEST['limit'] = 20;

$title = "Anwesenheit/Logins";
include("header.inc.php");
echo "<form>";
echo "User: <select onChange=\"document.forms[0].submit()\" name=\"id\" size=\"1\"><option value=\"0\">--alle--</option>";
	$sql = "SELECT UID,username FROM chat_user WHERE UID > 0 ORDER BY username ASC;";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		echo "<option value=\"".$row['UID']."\"";
		if ($row['UID']==$id) echo " selected=\"selected\"";
		echo ">".htmlentities($row['username'])."</option>";
	}
	echo "</select>";
	echo ", Anzahl: <input type=\"text\" name=\"limit\" value=\"".(isset($_REQUEST['limit']) ? $_REQUEST['limit'] : "20")."\"> (0 f&uuml;r Alle) ";
echo "<input type=\"submit\" value=\"Go!\">";
echo "</form>";

if (isset($_REQUEST['id'])) {
	
	echo "<table border=\"1\">";
	echo "<tr><td>User</td><td>Dauer</td><td>in</td><td>out</td><td>host</td><td>ip</td><td>browser</td><td>method</td></tr>";
	
	$sql = "SELECT chat_logins.*,chat_user.username FROM chat_logins,chat_user WHERE chat_logins.UID = chat_user.UID ";
	if ($_REQUEST['id']) $sql .= "AND chat_logins.UID = '$id' ";
	$sql .= " ORDER BY chat_logins.intime DESC ";
	if ((integer)$_REQUEST['limit']) $sql .= "LIMIT ".(integer)$_REQUEST['limit'];
	$sql .= ";";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		echo "<tr><td>".$row['username']."</td><td>";
		
		if (substr($row['outtime'],0,3)) {
			$dauer = (dt2unix($row['outtime']) - dt2unix($row['intime']));
		
			if ($dauer >= 0) {
		
				if ($dauer > 3600*24) {
					echo str_pad((integer)($dauer / (3600*24)), 2, "0", STR_PAD_LEFT)."d ";
					$dauer = $dauer % (3600*24);
				}
				if ($dauer > 3600) {
					echo str_pad((integer)($dauer / 3600), 2, "0", STR_PAD_LEFT)."h ";
					$dauer = $dauer % 3600;
				}
				if ($dauer > 60) {
					echo str_pad((integer)($dauer / 60), 2, "0", STR_PAD_LEFT)."m ";
					$dauer = $dauer % 60;
				}
			
				if ((dt2unix($row['outtime']) - dt2unix($row['intime'])) < 600) echo str_pad($dauer, 2, "0", STR_PAD_LEFT)."s";
			} else echo "-";
		} else echo "-";
	
		echo "</td><td>".$row['intime']."</td><td>";
		if ((time() - dt2unix($row['outtime'])) <= 60) echo "<font color=\"red\">";
		echo "".$row['outtime']."";
		if ((time() - dt2unix($row['outtime'])) <= 60) echo "</font>";
		echo "</td><td><a target=\"_blank\" href=\"http://".$row['host']."\">".$row['host']."</a></td><td><a target=\"_blank\" href=\"http://www.area51.dk/cgi/ipw.cgi?ip=".$row['IP']."\">".$row['IP']."</a></td><td>".stripslashes($row['browser'])."</td><td>".$row['method']."</td></tr>";
	}
	echo "</table>";
}
	
include("footer.inc.php");
?>