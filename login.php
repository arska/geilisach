<?

include("connect.inc.php");
include("functions.inc.php");

session_start(); // sonst in auth.inc.php

// persistent cookie auth:
if ($_COOKIE['persistentlogin']) {
	$tempcookie = addslashes(trim($_COOKIE['persistentlogin']));
	$sql = "SELECT UID,username,preferences FROM chat_user WHERE cookie = '$tempcookie' AND cookie != '' AND UID > 0 AND account = 'active' LIMIT 1;";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) == 1) { // 1 resultat -> cookie g&uuml;ltig und eindeutig
		$row = mysql_fetch_row($result); // gibt array mit 3 Elementen, $row[0] ist UID, $row[1] ist username, $row[2] sind die session_encode()'d preferences
		//weiterleiten an $url oder erfolgreiche meldung anzeigen
		// jetzt prefs laden
		if ($row[2]) session_decode(stripslashes($row[2]));
		else include("userdefaults.inc.php");
		$_SESSION['UID'] = $row[0];
		$_SESSION['username'] = $row[1];
		$prefsloaded = 1;
		$authmethod = "persistentcookie";

	} else {
		// cookie ung&uuml;ltig
		$invalidcookie = 1; // warnung sp&auml;ter anzeigen..
	}
}
if ($_REQUEST['username'] && !$prefsloaded) { // username durch formular und $prefsloaded noch nicht schon oben gesetzt
	$username = addslashes(trim($_REQUEST['username']));
	$password = addslashes(trim($_REQUEST['password']));
	
	$sql = "SELECT UID,username,preferences FROM chat_user WHERE username = '$username' AND password = '$password' AND UID > 0 AND account = 'active' LIMIT 1;";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) == 1) { // 1 resultat -> username-password-kombination g&uuml;ltig und eindeutig
		$row = mysql_fetch_row($result); // gibt array mit 3 Elementen, $row[0] ist UID, $row[1] ist username, $row[2] sind die session_encode()'d preferences
		//weiterleiten an $url oder erfolgreiche meldung anzeigen
		// jetzt prefs laden
		if ($row[2]) session_decode(stripslashes($row[2]));
		else include("userdefaults.inc.php");
		$_SESSION['UID'] = $row[0];
		$_SESSION['username'] = $row[1];
		$prefsloaded = 1;
		$authmethod = "login";

	} else {
		// auth ung&uuml;ltig
		$invalidauth = 1; // warnung sp&auml;ter anzeigen..
	}
}

if ($_SESSION['UID']) $banneduid = $_SESSION['UID'];
elseif ((integer)$_REQUEST['kickban']) $banneduid = (integer)$_REQUEST['kickban'];
else $banneduid = 0; // keine uid -> kann nicht gebannt sein.

if ($banneduid) {
	$sql = "SELECT chat_user.username,chat_kickban.*,UNIX_TIMESTAMP(chat_kickban.banto) AS ban FROM chat_kickban,chat_user WHERE chat_user.UID = chat_kickban.UID AND chat_kickban.UID = $banneduid AND (UNIX_TIMESTAMP(chat_kickban.banto) >= UNIX_TIMESTAMP() OR chat_kickban.banto = 0) ORDER BY (CASE WHEN chat_kickban.banto = 0 THEN 1 ELSE 0 END) LIMIT 1;";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) { // uh-oh, der benutzer ist gebannt..
		$ban = mysql_fetch_array($result);
		$prefsloaded = false;
		include("logout.inc.php");
		$bannedstring = "Du, {$ban['username']}, wurdest am ".unix2str(ts2unix($ban['banfrom']))." ";
		if ($ban['banto'] == 0) $bannedstring .= "bis auf weiteres";
		else $bannedstring .= "bis zum ".unix2str(dt2unix($ban['banto']))."";
		$bannedstring .= " gebannt mit der Begr&uuml;ndung: {$ban['grund']}.<br> Der Admin ist erreichbar unter <a href=\"mailto:admin@geilisach.ch\">admin@geilisach.ch</a>";
	} // sonst nichts tun und den user weiterleiten
}

//weiterleitung
	//echo "-".urldecode($_REQUEST['url']);
	if (!trim($_REQUEST['url']) || substr(urldecode(trim($_REQUEST['url'])),0,4) == "http") $url = "index.php"; // default oder mšchtegern-hijack.. wuahaha.. hehe.. lol.. *g*
	else $url = trim($_REQUEST['url']);

if ($prefsloaded) {
	
	$sql = "INSERT chat_logins SET UID = '".$_SESSION['UID']."', method = '$authmethod', ip = '".$_SERVER['REMOTE_ADDR']."', host = '".gethostbyaddr($_SERVER['REMOTE_ADDR'])."', browser = '".addslashes($_SERVER["HTTP_USER_AGENT"])."', intime = NOW(), outtime = NOW();";
	mysql_query($sql);
			
	?><html><head><META HTTP-EQUIV=REFRESH CONTENT="0; url=<?=urldecode($url) ?>"></head><body><?
	?>Du bist authentifiziert, <?=$_SESSION['username'] ?> (UID <?=$_SESSION['UID'] ?>), es geht gleich <a href="<?=urldecode($url) ?>">weiter..</a></body></html><?
			
} else {
	$title = "Login";
	include("header.inc.php");
?>
<center>
<form method="post" action="">
<input type="hidden" name="url" value="<?=htmlspecialchars($_REQUEST['url']) ?>">
<table border="1" cellpadding="4">
<tr><td align="center" rowspan="2" width="50%">
<img src="/yalampgallery/generated/6056.jpg" align="top">
<h1>Willkommen zu Geilisach.ch !</h1>
</td>
<td align="center">
<?=($bannedstring ? "<font color=\"red\">".$bannedstring."</font><br>" : "") ?>
<?=($invalidcookie ? "<font color=\"red\">"."Ung&uuml;ltiges persistent-cookie gesetzt ! Bitte nach dem Login in den Voreinstellungen korrigieren !"."</font><br>" : "") ?>
<?=($invalidauth ? "<font color=\"red\">"."Falsche Username - Passwort Kombination oder der Account wurde noch nicht aktiviert !"."</font><br>" : "") ?>
<? if ($_SESSION['UID']) { ?>
Hallo <?=$_SESSION['username'] ?>, du bist schon authentifiziert ! Du kannst auch direkt <a href="<?=urldecode($url) ?>">weiter gehen</a>.<br>
</td></tr>
<tr><td align="center">
<? } ?>
<table border="0">
<tr><td>Username:</td><td><input type="text" name="username" value="<? if ($_SESSION['username']) echo $_SESSION['username']; else echo $_REQUEST['username'] ?>"></td></tr>
<tr><td>Passwort:</td><td><input type="password" name="password" value="<?=htmlspecialchars($_REQUEST['password']) ?>"></td></tr>
<tr><td></td><td><input type="submit" name="gologin" value="Login"></td></tr>
<tr><td></td><td><a href="useradd.php">Noch gar nicht registriert ?</a></td></tr>
<tr><td></td><td><a href="userlostpw.php">Passwort vergessen ?</a></td></tr>
</table>
</td></tr>
<tr><td align="left" colspan="2">
<b>Wer sind wir ?</b><br>
Wir sind ein paar Kollegen ('airsicknessbag', 'arska', 'Hirsch' und 'Stopfi'), die zusammen diese Homepage gebastelt haben und regelm&auml;ssig ben&uuml;tzen.<br>
Wir sind zusammen in die <a target="_blank" href="http://www.mng.ch/">Mittelschule</a> gegangen, haben aber unterdessen die Matur hinter uns gebracht und studieren an der <a target="_blank" href="http://www.ethz.ch/">ETH Z&uuml;rich</a> und der <a target="_blank" href="http://www.unisg.ch/">Universit&auml;t St. Gallen</a>.<br>
<br>
<b>Was ist diese Seite ?</b><br>
Diese Seite ist ein Chat, ein Partykalender und eine Bilder- und Kolumnengalerie.<br>
Nach dem einloggen landest du im Chat, wo du auch gleich siehst wer sonst gerade noch online ist. Von dort gelangst du auf die verschiedenen anderen Bereiche.<br>
<br>
<b>Warum registrieren ?</b><br>
Weil sich die meisten User auch im "real life" kennen, m&ouml;chte man wissen mit wem man chattet.<br>
<br>
Dazu gibt es noch die <a href="faq.php">FAQ</a>.<br>
<br>
<b>Viel Spass w&uuml;nscht das Geilisach.ch - Team ('airsicknessbag', 'arska', 'Hirsch' und 'Stopfi')</b>
</td></tr>
<tr><td align="center"  colspan="2">
	<table border="0" width="99%" cellspacing="10">
		<tr>
			<td align="center">
				Hosted & Programmed by<br><a href="http://www.arska.ch/" target="_blank">Arska.ch Internet Services</a>
			</td>
			<td>
				<a href="http://validator.w3.org/check/referer"><img border="0" src="/bilder/valid-html401.gif" alt="Valid HTML 4.01!" <? $st = @getimagesize("../bilder/valid-html401.gif"); echo $st[3]; ?>></a> 
			</td>
			<td>
				<a href="http://www.php.net/"><img border="0" src="/bilder/php-small-trans-light.gif" alt="Powered by PHP" <? $st = @getimagesize("../bilder/php-small-trans-light.gif"); echo $st[3]; ?>></a> 
			</td>
			<td>
				<a href="http://www.mysql.com/"><img border="0" src="/bilder/poweredbymysql.gif" alt="Powered by MySQL" <? $st = @getimagesize("../bilder/poweredbymysql.gif"); echo $st[3]; ?>></a> 
			</td>
		</tr>
	</table>
</td></tr>
</table>
</form>
</center>
<?
	include("footer.inc.php");
}
?>
