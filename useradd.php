<?
session_start();
include("connect.inc.php");

echo "Registrerungen f&uuml;r den Moment deaktiviert.";
exit;

// der grösste Teil aus userprefs.php.. ausser dass alle daten nicht aus der DB kommen sondern geschrieben werden..
$messages = array();
$errors = array();

if ($_REQUEST['createuser']) {
	$username = addslashes(trim($_REQUEST['username']));

	if (!$username) {
		array_push($errors,"Keinen Usernamen angegeben.");
	}

	$sql = "SELECT UID FROM chat_user WHERE username = '$username' LIMIT 1;";
	$result = mysql_query($sql);
	$row = mysql_fetch_row($result);
	if (!(mysql_num_rows($result) == 0)) {
		array_push($errors,"Es gibt bereits einen anderen User mit Namen '$username', bitte einen anderen w&auml;hlen.");
	}

	$disclose = "";
	if ($_REQUEST['fullnamedisclose']) $disclose .= "fullname,";
	if ($_REQUEST['herkunftdisclose']) $disclose .= "herkunft,";
	if ($_REQUEST['emaildisclose']) $disclose .= "email,";
	if ($_REQUEST['nateldisclose']) $disclose .= "natel,";

	$gebjahr = (integer)$_REQUEST['gebjahr'];
	$gebmon = (integer)$_REQUEST['gebmon'];
	$gebtag = (integer)$_REQUEST['gebtag'];

	if ($gebjahr == 0) array_push($errors,"Bitte Geburtsjahr eingeben.");
	elseif ($gebjahr < 100) $gebjahr = (integer)$_REQUEST['gebjahr'] + 1900; // hehe, ist halt für unsere Generation


	if (checkdate($gebmon,$gebtag,$gebjahr)) {
		$geburi = str_pad($gebjahr, 4, "0", STR_PAD_LEFT).str_pad($gebmon, 2, "0", STR_PAD_LEFT).str_pad($gebtag, 2, "0", STR_PAD_LEFT);
	} else {
		array_push($errors,"Das Datum '".str_pad($gebtag, 2, "0", STR_PAD_LEFT).".".str_pad($gebmon, 2, "0", STR_PAD_LEFT).".".str_pad($gebjahr, 4, "0", STR_PAD_LEFT)."' ist ung&uuml;ltig.");
	}

	if ($_REQUEST['mf']) {
		if ($_REQUEST['mf'] == "m") $mf = "m";
		else $mf = "f";
	} else array_push($errors,"Du hast nicht angegeben, ob du Mann oder Frau bist.");

	$fullname = addslashes(htmlentities(trim($_REQUEST['fullname'])));

	$herkunft = addslashes(htmlentities(trim($_REQUEST['herkunft'])));

	$password = addslashes(trim($_REQUEST['password']));

	if (strlen($password) < 3) array_push($errors,"Bitte ein Passwort wählen, das mindestens 3 Zeichen enthält (Leerschläge am Anfang/Ende werden entfernt).");

	$email = addslashes(trim($_REQUEST['email']));
	if (!preg_match("/[a-z_-]+\@.+\.[a-z]{2,4}/i",$email)) array_push($errors,"Die Email-Adresse scheint nicht gültig zu sein.");

	$natel = addslashes(trim($_REQUEST['natel']));

	$beschreibung = addslashes(nl2br(htmlentities(trim($_REQUEST['beschreibung']))));

	if (trim($_REQUEST['homepage']) && trim($_REQUEST['homepage']) != "http://") $homepage = addslashes((trim($_REQUEST['homepage'])));
	else $homepage = "";

	if (count($errors) < 1) { // keine fehler
		// aktivierungs-code:
		$cookie = md5($username.$fullname.$email.$_REQUEST["PHPSESSID"]);
		$sql = "INSERT INTO chat_user (username,fullname,herkunft,password,email,natel,geburi,beschreibung,homepage,disclose,mf,account,cookie,regdate,lastaccess) VALUES ('$username','$fullname','$herkunft','$password','$email','$natel','$geburi','$beschreibung','$homepage','$disclose','$mf','pending','$cookie',NOW(),0);";
		$result = mysql_query($sql);
		if ($result) { // konnte eingefügt werden..
			$uid = mysql_insert_id();

			// first login
			$sql = "INSERT chat_logins SET UID = '".$uid."', method = 'registration', ip = '".$_SERVER['REMOTE_ADDR']."', host = '".gethostbyaddr($_SERVER['REMOTE_ADDR'])."', browser = '".addslashes($_SERVER["HTTP_USER_AGENT"])."', intime = NOW(), outtime = NOW();";
			mysql_query($sql);

			$from = "Geilisach.ch-Chat <admin@geilisach.ch>";

			// mail mit cookie-code
			$message = "Geilisach.ch-Chat - Registrierung für ".$username.":\n\n";
			$message .= "Willkommen auf Geilsach.ch !\n";
			$message .= "Damit dein Account aktiviert werden kann, klicke auf folgenden Link:\n";
			$message .= "<http://www.geilisach.ch/useractivate.php?key=$cookie>\n\n";
			$message .= "Falls der Link nicht funktionieren sollte, gehe auf <http://www.geilisach.ch/useractivate.php> und tippe folgenden Code ein:\n";
			$message .= "$cookie \n\n";
			$message .= "Viel Spass im Geilisach.ch-Chat wünscht dir\n-Arska (Aarno Aukia)\n";

			$recipient = "\"".html_entity_decode($fullname)."\" <".$email.">";
			$subject = "Geilisach.ch-Chat Registrierung";

			$header = "";
			$header .= "Date: ". date('r'). " \r\n";
			$header .= "Return-Path: <admin@geilisach.ch>\r\n";
			$header .= "From: $from \r\n";
			$header .= "Sender: $from \r\n";
			$header .= "Reply-To: $from \r\n";
			$header .= "Organization: Geilisach.ch \r\n";
			$header .= "X-Sender: $from \r\n";
			$header .= "X-Priority: 3 \r\n";
			$header .= "Return-Path: <admin@geilisach.ch>\n";
			$header .= "X-From: Geilisach.ch-Chat Registration\n";
			$header .= "X-Mailer: PHP/".phpversion()."\n";
			$header .= "MIME-Version: 1.0\n";
			$header .= "Content-Type: text/plain;\n";
			$header .= "	charset=\"iso-8859-1\"\n";
			$header .= "Content-Transfer-Encoding: 8bit\n";

			if (mail($recipient,$subject,$message,$header)) {
				$title = "Aktivierung geschickt";
				include("header.inc.php");
				echo "Vielen Dank f&uuml;r deine Anmeldung '$username'. Das Mail mit dem Aktivierungscode wurde an '$email' geschickt.<br>Du musst den code auf <a href=\"useractivate.php\">dieser Seite</a> eingeben.";
				include("footer.inc.php");
				exit;
			} else {
				$title = "Aktivierung fehlgeschlagen";
				include("header.inc.php");
				echo "Das Mail mit dem Aktivierungscode konnte nicht an '$email' geschickt werden. Bitte melde dich nochmals an (diesmal mit der richtigen Email-Adresse) und melde dich beim admin@geilisach.ch, damit er die misslungene Anmeldung l&ouml;schen kann.";
				include("footer.inc.php");
				exit;
			}

		} else array_push($errors,"Daten konnten nicht in die Datenbank geschrieben werden. Sorry. Vielleicht ist das ein temporäres Problem. Wenn nicht, informiere bitte den Admin@geilisach.ch.");
	} else array_push($errors,"Daten wurden wegen genannten Fehlern noch nicht gespeichert.");
// workarea
}

$title = "Anmeldung f&uuml;r neue User";
include("header.inc.php");

echo "<center>";
echo "<form method=\"post\" action=\"{$_SERVER['PHP_SELF']}\">";

if (count($errors) > 0) {
	for ($i=0; $i<count($errors); $i++) echo "<font color=\"red\">{$errors[$i]}</font><br>";
}
if (count($messages) > 0) {
	for ($i=0; $i<count($messages); $i++) echo $messages[$i]."<br>";
}

?>
<table width="75%" border="1"><tr><td>
<table border="0" cellspacing="0" cellpadding="4">
<tr>
	<td>Username</td>
	<td><input type="text" name="username" value="<?=$username ?>"></td>
	<td>Der gew&uuml;nschte Chat-Name</td>
</tr>
<tr>
	<td>Passwort</td>
	<td><input type="password" name="password" value="<?=$password ?>"></td>
	<td>W&auml;hle ein Passwort. Mindestens 3 Zeichen, keine Leerschl&auml;ge am Anfang oder Ende.</td>
</tr>
<tr>
	<td>Vorname Nachname</td>
	<td><input type="text" name="fullname" value="<?=$fullname ?>"><br><input type="checkbox" name="fullnamedisclose" value="1" <? if (in_array("fullname",explode(",",$disclose)) || !isset($disclose)) echo " checked=\"checked\"" ?>>F&uuml;r alle sichtbar</td>
	<td>Deinen (richtigen) Namen</td>
</tr>
<tr>
	<td>Herkunft</td>
	<td><input type="text" name="herkunft" value="<?=$herkunft ?>"><br><input type="checkbox" name="herkunftdisclose" value="1" <? if (in_array("herkunft",explode(",",$disclose)) || !isset($disclose)) echo " checked=\"checked\"" ?>>F&uuml;r alle sichtbar</td>
	<td>Wo kommst du her ?</td>
</tr>
<tr>
	<td>Emailadresse</td>
	<td><input type="text" name="email" value="<?=$email ?>"><br><input type="checkbox" name="emaildisclose" value="1" <? if (in_array("email",explode(",",$disclose)) || !isset($disclose)) echo " checked=\"checked\"" ?>>F&uuml;r alle sichtbar</td>
	<td>Email-Adresse, an die das <b>Aktivierungs-Mail</b> geschickt wird. <b>Muss G&uuml;ltig sein</b> !</td>
</tr>
<tr>
	<td>Natel-Nr.</td>
	<td><input type="text" name="natel" value="<?=$natel ?>"><br><input type="checkbox" name="nateldisclose" value="1" <? if (in_array("natel",explode(",",$disclose)) || !isset($disclose)) echo " checked=\"checked\"" ?>>F&uuml;r alle sichtbar</td>
	<td></td>
</tr>
<tr>
	<td>Geburtstag</td>
	<td><input type="text" name="gebtag" value="<?=substr($geburi,8,2) ?>" size="2">.<input type="text" name="gebmon" value="<?=substr($geburi,5,2) ?>" size="2">.<input type="text" name="gebjahr" value="<?=substr($geburi,0,4) ?>" size="4"></td>
	<td>Damit dir die anderen User dann grosse Geschenke schenken k&ouml;nnen =)</td>
</tr>
<tr>
	<td>M/F</td>
	<td><input type="radio" name="mf" value="m" <?=($mf == "m" ? "checked=\"checked\"" : "") ?>> Mann<br><input type="radio" name="mf" value="f" <?=($mf == "f" ? "checked=\"checked\"" : "") ?>> Frau</td>
	<td></td>
</tr>
<tr>
	<td>Beschreibung</td>
	<td><textarea name="beschreibung" rows="4" cols="30"><?=stripslashes(str_replace("<br />","",$beschreibung)) ?></textarea></td>
	<td>Eine kleine Beschreibung wer du bist, woher man dich kennt usw..</td>
</tr>
<tr>
	<td>Homepage</td>
	<td><input type="text" name="homepage" value="<? if ($homepage) echo $homepage; else echo "http://";?>"></td>
	<td>Wenn du eine Homepage hast, machen wir einen Link darauf.</td>
</tr>

<tr><td></td><td align="right"><input type="submit" name="createuser" value="Anmelden"></td><td></td></tr>
</table>
</td></tr></table>
</form>

<?
	include("footer.inc.php");
?>
