<?

session_start();

include("connect.inc.php");
include("functions.inc.php");

$title = "Frequently Asked Questions";
include("header.inc.php");
?>
<center>
<table border="1"><tr><td>
Fragen, die schon &uuml;ber den Chat aufgetaucht sind:<br>
<ul>
<li><b>Wieviele User hat dieser Chat ?</b><br>Dieser Chat hat momentan <? 
$sql = "SELECT COUNT(UID) FROM chat_user;";
$result = mysql_query($sql);
$anz = mysql_fetch_row($result);
echo $anz[0];
?> User.<br>
<li><b>Wer sind die Admins des Chats ?</b><br>Die folgenden langj&auml;hrigen Geilisach-Members sind Admins: 
<?
	$sql = "SELECT UID,username FROM chat_user WHERE (status = 'admin' OR status = 'god') AND UID > 0 ORDER BY username ASC;";
	$result = mysql_query($sql);
	$i=0;
	while ($row = mysql_fetch_array($result)) {
		echo ($i++ ? ", " : "")."<a href=\"userinfo.php?id={$row['UID']}\">{$row['username']}</a>";
	}
	
?><br>
Admins helfen noch weniger erfahrenen Usern bei der Ben&uuml;tzung des Chat und sorgen f&uuml;r Ordnung. Dabei sind sie erm&auml;chtigt, st&ouml;rende User aus dem Chat zu kicken oder gegebenenfalls f&uuml;r eine bestimmte Zeit aus dem Chat zu verbannen, bis sie sich beruhigt haben.
<li><b>Wer ist Geilisach.ch-Member ?</b><br>
<?
	$sql = "SELECT UID,username FROM chat_user WHERE status != 'user' AND UID > 0 ORDER BY username ASC;";
	$result = mysql_query($sql);
	$i=0;
	while ($row = mysql_fetch_array($result)) {
		echo ($i++ ? ", " : "")."<a href=\"userinfo.php?id={$row['UID']}\">{$row['username']}</a>";
	}
	
?><br>
Geilisach.ch-Member tragen den Betrieb von Geilisach.ch finanziell und die Meisten sind sehr h&auml;ufig auf dem Chat. Member k&ouml;nnen von besonderen Member-Events usw. profitieren.
<!-- <li><b>Wie wird man Geilisach.ch-Member ?</b><br>
Einmal j&auml;hrlich k&ouml;nnen Memberships beantragt und verl&auml;ngert werden. Dies geschieht mittels eines besonderen Events, worin sich Member einschreiben k&ouml;nnen.
-->

<li><b>Wieviele User waren schon gleichzeitig im chat ?</b><br>
<?
$sql = "SELECT chat_logins.intime,chat_logins.outtime,chat_user.username FROM chat_logins,chat_user WHERE chat_logins.outtime != 0 AND chat_logins.UID = chat_user.UID ORDER BY chat_logins.intime ASC;";

$result = mysql_query($sql);

$outtimes = array();
$user = array();
$counter = 0;
$max = 0;

while ($row = mysql_fetch_array($result)) {
	while (count($outtimes) > 0 && reset($outtimes) <= $row['intime']) { // es hat logout-events, die aktueller sind, als der login-event von $row
		array_shift($outtimes); // das kleinste raus..
	}
	$outtimes[$row['username']] = $row['outtime'];
	
	asort($outtimes);
	
	if (count($outtimes) >= $max) {
		if (count($outtimes) > $max) $data = array(); // neuer rekord..
		
		$data[] = array(count($outtimes),$row['intime'],reset($outtimes),array_keys($outtimes));
		
		$max = count($outtimes);
	}
}
echo "Maximal waren bereits {$data[0][0]} User gleichzeitig im chat und zwar bisher ".count($data)." Mal: ";
for ($i=0; $i < count($data); $i++) {
	if ($i) echo ", ";
	echo "<br>von ".unix2str(dt2unix($data[$i][1]))." bis ".unix2str(dt2unix($data[$i][2]))." (";
	natcasesort($data[$i][3]);
	for ($j=0; $j<count($data[$i][3]); $j++) {
		echo ($j ? ", " : "").current($data[$i][3]);
		next($data[$i][3]);
	}
	echo ")";
}
echo ".<br>";
/*
?>
<li><b>Wieviel Zeit haben alle User bis jetzt total im Chat verbracht ?</b><br><?
$sql = "SELECT SUM(UNIX_TIMESTAMP(outtime)-UNIX_TIMESTAMP(intime)) FROM chat_logins WHERE outtime <> '0000-00-00 00:00:00';";
$subresult = mysql_query($sql);
$timespent = mysql_fetch_row($subresult);
echo "Totale Onlinezeit (seit dem 6.5.2003): <br>";
echo round($timespent[0])." Sekunden<br>";
echo round($timespent[0]/60,2)." Minuten<br>";
echo round($timespent[0]/3600,2)." Stunden<br>";
echo round($timespent[0]/(3600*24),2)." Tage<br>";
echo round($timespent[0]/(3600*24*7),2)." Wochen<br>";
echo round($timespent[0]/(3600*24*30),2)." Monate<br>";
echo round($timespent[0]/(3600*24*365.24),2)." Jahre<br>";
?>

<li><b>Wer sind die vielschreiber ?</b><br><?
$mytop = ($_SESSION['numlast'] ? $_SESSION['numlast'] : 5);

$sql = "SELECT chat_user.username,chat_data.UID,AVG(LENGTH(kommentar)) AS a FROM chat_data,chat_user WHERE chat_data.UID = chat_user.UID GROUP BY chat_data.UID ORDER BY a DESC LIMIT $mytop;";
$subresult = mysql_query($sql);

echo "<table>";
echo "<tr><td>User</td><td>Durchschnittliche Post-L&auml;nge</td></tr>";
while ($subrow = mysql_fetch_array($subresult)) {
	echo "<tr><td>".($subrow['username'] && $subrow['UID'] != 0 ? "<a target=\"_blank\" href=\"userinfo.php?id={$subrow['UID']}\">".htmlentities($subrow['username'])."</a>" : "User#".$subrow['UID'])."</td><td>".round($subrow['a'],2)." Buchstaben</td></tr>";
}

$sql = "SELECT chat_user.username,chat_data.UID,SUM(LENGTH(kommentar)) AS a FROM chat_data,chat_user WHERE chat_data.UID = chat_user.UID GROUP BY chat_data.UID ORDER BY a DESC LIMIT $mytop;";
$subresult = mysql_query($sql);

echo "<tr><td></td><td></td></tr>";
echo "<tr><td>User</td><td>Gesamte Schreibmenge</td></tr>";
while ($subrow = mysql_fetch_array($subresult)) {
	echo "<tr><td>".($subrow['username'] && $subrow['UID'] != 0 ? "<a target=\"_blank\" href=\"userinfo.php?id={$subrow['UID']}\">".htmlentities($subrow['username'])."</a>" : "User#".$subrow['UID'])."</td><td>";
	if ($subrow['a'] > 1024*1024) echo round($subrow['a']/1024/1024,2)." MB";
	elseif ($subrow['a'] > 1024) echo round($subrow['a']/1024,2)." KB";
	else echo $subrow['a']." Bytes";
	echo "</td></tr>";
}
echo "</table>";
*/
?>


<li><b>Muss ich mich jedes Mal einloggen ?</b><br>Nein, du kannst in den Voreinstellungen ein sog. "persistent cookie" erstellen, womit du automagisch eingeloggt wirst. Damit k&ouml;nnen aber auch alle anderen Personen, die denselben Computer benutzen, unter Deinem Namen auf den Chat. Diese Funktion eignet sich darum nur f&uuml;r deinen pers&ouml;nlichen Computer, zu dem nur Du Zugang hast.<br>
<li><b>Wie kann ich meinen Chat-text gestalten ?</b><br>Du hast folgende M&ouml;glichkeiten:<br><ul><li>Du kannst '/me ' vor deine Aussage schreiben, um etwas in der 3. Person zu sagen.<br>Die Zeile sieht dann so aus:<ul><? echo "<li> <i>".htmlentities(($_SESSION['username']) ? $_SESSION['username'] : "username")." blablabla</i> <font size=\"-2\">(am ".ts2str(date("YmdHis")).")</font>\n"; ?></ul>
anstatt (normal):<ul><? echo "<li> blablabla <font size=\"-2\">(von ".htmlentities(($_SESSION['username']) ? $_SESSION['username'] : "username")." am ".ts2str(date("YmdHis")).")</font>\n"; ?></ul>
<li>Du kannst einen Teil des Textes mit [u] und [/u] unterstrichen machen, zB: "[u]test[/u]" wird zu: "<u>test</u>".
<li>Du kannst einen Teil des Textes mit [i] und [/i] kursiv machen, zB: "[i]test[/i]" wird zu: "<i>test</i>".
<li>Du kannst einen Teil des Textes mit [b] und [/b] fett machen, zB: "[b]test[/b]" wird zu: "<b>test</b>".
<li>Um einen Link anklickbar zu machen, musst du einfach die vollst&auml;ndige Adresse inkl. http:// eingeben, zB: "http://www.arska.ch" wird zu "<a target="_blank" href="http://www.arska.ch">http://www.arska.ch</a>".
</ul>
<li><b>Was bedeuten die verschiedenen Abk&uuml;rzungen ?</b><br>
<ul>
<li><b>lol:</b> laughing out loud = laut lachen<br>
<li><b>dwsw:</b> du weisst schon was; ein synonym f&uuml;r lol<br>
<li><b>rofl:</b> rolling on the floor laughing = am Boden rollen vor lachen<br>
<li><b>brb:</b> be right back = bin gleich zur&uuml;ck<br>
<li><b>re:</b> lateinische Vorsilbe f&uuml;r "wieder", wird gebraucht als "wieder da"<br>
<li><b>giss:</b> gone in sixty seconds; wenn man den chat verl&auml;sst, wird man nach 60s nicht mehr angezeigt.<br>
<li><b>SB:</b> "Ausruf des Erstaunens mit 2 Buchstaben" im Kreuzwortr&auml;zel unserer Mitsch&uuml;lerin Therese L&uuml;thi (die den Weg in den Chat (noch) nicht gefunden hat..)<br>
<li><b>BTW:</b> By The Way = nebenbei..<br>
<li><b>JFYI:</b> Just For Your Information = zu deiner Information<br>
<li><b>RTFM:</b> Read The F***ing Manual = lies die Bedienungsanleitung !<br>
<li><b>FAQ:</b> Frequently Asked Questions = oft gefragte Fragen<br>
<li><b>fum?:</b> <u>F</u>it <u>u</u>nd <u>M</u>unter <u>?</u><br>

</ul>
Weitere h&auml;ufige Ausdr&uuml;cke:<br>
<ul>
<li><b>Slon:</b> Heisst Elefant auf Russisch und ist ein Universalwort. Es kann als Ersatz f&uuml;r "lol" dienen, als bezeichnung f&uuml;r Menschen oder auch einfach als F&uuml;llwort f&uuml;r Chatter mit gesteigertem Mitteilungsbed&uuml;rfnis...
<li><b>Schn&uuml;si:</b> Eine h&uuml;bsche Frau.
<li><b>D&uuml;si:</b> Eine sehr gutaussehende Frau, die Mann einfach anstarren muss. Die meisten D&uuml;sis kennen ihre Wirkung auf M&auml;nner genau - einige nutzen dies auch aus.
<li>Zwischen Schn&uuml;si und D&uuml;si gibt es nat&uuml;rlich auch &Uuml;bergangsformen, so zB schn&uuml;si-d&uuml;si.
<li><b>Hake:</b> Nicht-h&uuml;bsche Frau. Dieses Wort ist folgendermassen entstanden: Jemand hat bemerkt, dass jedes Schn&uuml;si oder D&uuml;si oft auch einen Haken habe, worauf jemand anders einwand dass es auch Frauen g&auml;be, die eben nur Haken seien.
<li><font size="-1">Dies sind keinesfalls diskriminierende Begriffe - weitere Terminologien d&uuml;rfen gerne eingef&uuml;hrt werden !</font>
</ul>
<li><b>Warum achten einige User auf ihre Anzahl lol's ?</b><br>
Nach einigen <a target="_blank" href="chatsearch.php?q=lol&mode=maxday">Kampf-lol-Sessions</a> wurde eingef&uuml;hrt, dass wenn ein User eine runde (200, 500, 1000,..) <a target="_blank" href="chatsearch.php?q=lol">Anzahl lol's</a> geschrieben hat, dieser eine Runde f&uuml;r alle an einem Geilisach.ch-Event anwesenden User ausgeben muss.
<li><b>Wer hat den Chat programmiert ?</b><br>
Arska bzw. seine Internetfirma <a target="_blank" href="http://www.arska.ch/">Arska Internet Services</a> hat den Chat entwickelt und programmiert.
<li><b>Welche Links f&uuml;hren zum Chat ?</b><br>
Alle fr&uuml;heren Orte des Chats besitzen eine Automatische Weiterleitung:<br>
<a target="_blank" href="http://www.mng.ch/~arska/chat/">http://www.mng.ch/~arska/chat/</a><br>
<a target="_blank" href="http://www.handrehabilitation.ch/arska/chat/">http://www.handrehabilitation.ch/arska/chat/</a><br>
<a target="_blank" href="http://www.arska.ch/chat/">http://www.arska.ch/chat/</a><br>

Die aktuellen Links zu Geilisach.ch:<br>
<a target="_blank" href="http://www.geilisach.ch/">http://www.geilisach.ch/</a><br>
<a target="_blank" href="http://www.geilisa.ch/">http://www.geilisa.ch/</a><br>
<a target="_blank" href="http://www.lol.ch/">http://www.lol.ch/</a><br>


<li><b>Warum schreiben einige User (fleisch) wirres Zeugs ?</b><br>
Das fragen wir uns auch manchmal... =)
</ul><br>
Sonst frage die Mods arska, airsicknessbag, stopfi und hirsch, sie helfen (meist) gerne.<br>
Weil die meisten unterdessen eine st&auml;ndige Internetverbindung besitzen und deshalb "immer im chat sind", kann es manchmal etwas dauern mit einer Antwort (wir nennen das liebevoll "laggen", "idlen" oder einfach "pennen").

</td></tr></table>
</center>
<?
include("footer.inc.php");
?>