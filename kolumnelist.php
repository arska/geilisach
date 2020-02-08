<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");

$sql = "SELECT kolumne_data.*,chat_user.username,kolumne_data.autor AS UID FROM kolumne_data LEFT JOIN chat_user ON chat_user.UID = kolumne_data.autor ORDER BY kolumne_data.posttime DESC;";
$result = mysql_query($sql);

$title = "Kolumnenliste";
include("header.inc.php");
?>

<table border="1" cellpadding="3">
<tr><td align="right"><a href="kolumneadd.php">Kolumne schreiben</a></td></tr>
<tr><td>Kolumnen:<br>
<table border="0" cellpadding="3">
<tr><td>Titel</td><td>Anlass</td><td>Datum</td><td>Geschrieben von</td><td>Geschrieben am</td></tr>
<?
while ($row = mysql_fetch_array($result)) {
?>
	<tr><td><a href="kolumneread.php?id=<?=$row['KID'] ?>"><?=stripslashes($row['titel']) ?></a></td><td><a href="kolumneread.php?id=<?=$row['KID'] ?>"><?=stripslashes($row['anlass']) ?></a></td><td><a href="kolumneread.php?id=<?=$row['KID'] ?>"><?=d2str($row['anlassdate']) ?></a></td><td><?=($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID']) ?></td><td><a href="kolumneread.php?id=<?=$row['KID'] ?>"><?=ts2str(stripslashes($row['posttime'])) ?></a></td></tr>
<? 
}
?>
</table>
</td></tr>
</table>
<?
include("footer.inc.php");

// die letzten gesehenen kolumnen werden gespeichert (LIMIT sollte idealerweise max(mšglichkeiten auf last5), zZ 20)
$_SESSION['seenkolumnen'] = array();
$sql = "SELECT KID FROM kolumne_data ORDER BY posttime DESC LIMIT 20;";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)) array_push($_SESSION['seenkolumnen'],$row[0]);

?>