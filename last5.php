<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");

if ((integer)$_REQUEST['numlast']) $_SESSION['numlast'] = (integer)$_REQUEST['numlast'];

$numlast = (integer)$_SESSION['numlast'];

$title = "Die letzten $numlast";
include("header.inc.php");
?>
<table border="1" cellpadding="4">
<tr><td>
	<form>
		<select name="numlast" size="1" onChange="document.forms[0].submit()">
<?
			$options = array(3,5,7,10,12,15,20);
			for ($i=0; $i < count($options); $i++) echo "<option".($options[$i] == $numlast ? " selected=\"selected\"": "").">{$options[$i]}</option>"
?>
		</select>
	</form>
</td></tr>

<tr><td>
	<table border="0" cellpadding="2">
		<tr><td><?=$numlast ?> neuesten Alben</td></tr>
		<tr></tr>
		<?
		$sql = "SELECT album_info.*,chat_user.username FROM album_info LEFT JOIN chat_user ON album_info.autor = chat_user.UID ORDER BY album_info.created DESC LIMIT $numlast;";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
		?><tr><td><a href="yalampgallery/showalbum.php?id=<?=$row['ID'] ?>"><?=stripslashes($row['anlass']); ?></a>, <font size="-2">(von '<?=($row['username'] && $row['autor'] != 0 ? "<a href=\"userinfo.php?id={$row['autor']}\">".htmlentities($row['username'])."</a>" : "User#".$row['autor']) ?>' am <?=dt2str($row['created']) ?>)</font>
		<? } ?>
	</table>
</td></tr>
<tr><td>
	<table border="0" cellpadding="2">
		<tr><td><?=$numlast ?> neuesten Albenkommentare</td></tr>
		<tr></tr>
		<?
		$sql = "SELECT kommentare.*, chat_user.username, album_info.anlass FROM kommentare LEFT JOIN chat_user ON kommentare.UID = chat_user.UID LEFT JOIN album_info ON kommentare.album_ID = album_info.ID WHERE kommentare.album_ID != 0 ORDER BY kommentare.posttime DESC LIMIT $numlast;";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
		?><tr><td>'<a href="<?=($row['bild_ID'] ? "yalampgallery/showpicture.php?id=".$row['bild_ID'] : "yalampgallery/showalbum.php?id=".$row['album_ID']) ?>"><?=$row['kommentar'] ?></a>', <font size="-2">(von '<?=($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID']) ?>' zu <a href="<?=($row['bild_ID'] ? "yalampgallery/showpicture.php?id=".$row['bild_ID'] : "yalampgallery/showalbum.php?id=".$row['album_ID']) ?>"><?=stripslashes($row['anlass']) ?></a> am <?=ts2str($row['posttime']) ?>)</font>
		<? } ?>
	</table>
</td></tr>

<tr><td>
	<table border="0" cellpadding="2">
		<tr><td><?=$numlast ?> neuesten Bilder</td></tr>
		<tr></tr>
		<?
		$sql = "SELECT bilder_info.*,chat_user.username FROM bilder_info LEFT JOIN chat_user ON bilder_info.UID = chat_user.UID WHERE bilder_info.ID = bilder_info.parent ORDER BY bilder_info.lastedit DESC LIMIT $numlast;";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
		?><tr><td><a href="yalampgallery/showpicture.php?id=<?=$row['ID'] ?>"><?=stripslashes($row['name']); ?></a>, <font size="-2">(von '<?=($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID']) ?>' am <?=dt2str($row['lastedit']) ?>)</font>
		<? } ?>
	</table>
</td></tr>
<tr><td>
	<table border="0" cellpadding="2">
		<tr><td><?=$numlast ?> neuesten Bilderkommentare</td></tr>
		<tr></tr>
		<?
		$sql = "SELECT kommentare.*, chat_user.username, bilder_info.name FROM kommentare LEFT JOIN chat_user ON kommentare.UID = chat_user.UID LEFT JOIN bilder_info ON kommentare.bild_ID = bilder_info.ID WHERE kommentare.bild_ID != 0 ORDER BY kommentare.posttime DESC LIMIT $numlast;";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
		?><tr><td>'<a href="<?=($row['bild_ID'] ? "yalampgallery/showpicture.php?id=".$row['bild_ID'] : "yalampgallery/showalbum.php?id=".$row['album_ID']) ?>"><?=$row['kommentar'] ?></a>', <font size="-2">(von '<?=($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID']) ?>' zu <a href="<?=($row['bild_ID'] ? "yalampgallery/showpicture.php?id=".$row['bild_ID'] : "yalampgallery/showalbum.php?id=".$row['album_ID']) ?>"><?=stripslashes($row['name']) ?></a> am <?=ts2str($row['posttime']) ?>)</font>
		<? } ?>
	</table>
</td></tr>

<tr><td>
	<table border="0" cellpadding="2">
		<tr><td><?=$numlast ?> neuesten Events</td></tr>
		<tr></tr>
		<?
		$sql = "SELECT party_data.*,chat_user.username,party_data.autor AS UID FROM party_data LEFT JOIN chat_user ON chat_user.UID = party_data.autor ORDER BY party_data.posttime DESC LIMIT $numlast;";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
		?><tr><td>'<a href="partyread.php?id=<?=$row['AID'] ?>"><?=(stripslashes($row['anlass'])) ?></a>' (<? if ($row['homepage']) echo "<a href=\"".$row['homepage']."\" target=\"_blank\">"; ?><?=(stripslashes($row['ort'])) ?><? if ($row['homepage']) echo "</a>"; ?>, am <?=dt2str($row['anlasstime']) ?>), <font size="-2">(hinzugef&uuml;gt von '<?=($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID']) ?>' am <?=ts2str($row['posttime']) ?>)</font>
		<? } ?>
	</table>
</td></tr>
<tr><td>
	<table border="0" cellpadding="2">
		<tr><td><?=$numlast ?> neuesten Eventkommentare</td></tr>
		<tr></tr>
<?
		$sql = "SELECT party_comments.*,chat_user.username,party_data.anlass FROM party_data,party_comments LEFT JOIN chat_user ON party_comments.UID = chat_user.UID WHERE party_data.AID = party_comments.AID ORDER BY party_comments.posttime DESC LIMIT $numlast;";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
			echo "<tr><td><a href=\"partyread.php?id={$row['AID']}\">".stripslashes($row['kommentar'])."</a>, <font size=\"-2\">(von '".($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID'])."' zu <a href=\"partyread.php?id={$row['AID']}\">".stripslashes($row['anlass'])."</a> am ".ts2str($row['posttime'])."</font>";
		}
?>
	</table>
</td></tr>

<tr><td>
	<table border="0" cellpadding="2">
		<tr><td><?=$numlast ?> neuesten Kolumnen</td></tr>
		<tr></tr>
		<?
		$sql = "SELECT kolumne_data.*,chat_user.username,kolumne_data.autor AS UID FROM kolumne_data LEFT JOIN chat_user ON chat_user.UID = kolumne_data.autor ORDER BY kolumne_data.posttime DESC LIMIT $numlast;";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
		?><tr><td>'<a href="kolumneread.php?id=<?=$row['KID'] ?>"><?=(stripslashes($row['titel'])) ?></a>' (<?=(stripslashes($row['anlass'])) ?>), <font size="-2">(geschrieben von '<?=($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID']) ?>' am <?=ts2str($row['posttime']) ?>)</font>
		<? } ?>
	</table>
</td></tr>
<tr><td>
	<table border="0" cellpadding="2">
		<tr><td><?=$numlast ?> neuesten Kolumnenkommentare</td></tr>
		<tr></tr>
		<?
		$sql = "SELECT kolumne_comments.*,chat_user.username,kolumne_data.anlass,kolumne_data.titel FROM kolumne_comments,kolumne_data,chat_user WHERE kolumne_comments.UID = chat_user.UID AND kolumne_data.KID = kolumne_comments.KID ORDER BY kolumne_comments.posttime DESC LIMIT $numlast;";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
		?><tr><td><a href="kolumneread.php?id=<?=$row['KID'] ?>"><?=stripslashes($row['kommentar']) ?></a>, <font size="-2">(von '<?=($row['username'] && $row['UID'] != 0 ? "<a href=\"userinfo.php?id={$row['UID']}\">".htmlentities($row['username'])."</a>" : "User#".$row['UID']) ?>' zu <a href="kolumneread.php?id=<?=$row['KID'] ?>"><?=stripslashes($row['titel']) ?> (<?=stripslashes($row['anlass']) ?>)</a> am <?=ts2str($row['posttime']) ?>)</font>
		<? } ?>
	</table>
</td></tr>

<tr><td>
	<table border="0" cellpadding="2">
		<tr><td><?=$numlast ?> neuesten Benutzer</td></tr>
		<tr></tr>
		<?
		$sql = "SELECT UID,username,regdate FROM chat_user WHERE UID > 0 ORDER BY regdate DESC LIMIT $numlast;";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)) {
		?><tr><td>'<a href="userinfo.php?id=<?=$row['UID'] ?>"><?=htmlentities($row['username']) ?></a>', <font size="-2">(registriert am <?=dt2str($row['regdate']) ?>)</font>
		<? } ?>
	</table>
</td></tr>

</table>
<?
include("footer.inc.php");
?>