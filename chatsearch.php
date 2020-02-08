<?
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");


$title = "Chat durchsuchen";
include("header.inc.php");
?>
<table cellpadding="3" border="1">
<tr><td><form>Suche: <input type="text" name="q" value="<?=$_REQUEST['q'] ?>">, Von: <select name="autor" size="1"><option value="0">--egal--</option><?

$sql = "SELECT UID,username FROM chat_user WHERE UID > 0 ORDER BY username ASC;";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)) {
	?><option value="<?=$row[0] ?>"<? if ($row[0] == $_REQUEST['autor']) echo " selected"; ?>><?=$row[1] ?></option><? echo "\n";
} 

?></select>, <input type="checkbox" name="notcount" value="1" <? if ($_REQUEST['notcount']) echo " checked=\"checked\""; ?>> Anzeigen anstatt Z&auml;hlen
<br>
Suchmodus:<br>
<input type="radio" name="mode" value="string"<? if ($_REQUEST['mode'] == "string" || !$_REQUEST['mode']) echo " checked=\"checked\""; ?>> Genau diese Wortfolge soll vorkommen (string-vergleich)<br>
<input type="radio" name="mode" value="and"<? if ($_REQUEST['mode'] == "and") echo " checked=\"checked\""; ?>> Jedes Wort soll vorkommen (AND-vergleich)<br>
<input type="radio" name="mode" value="or"<? if ($_REQUEST['mode'] == "or") echo " checked=\"checked\""; ?>> Mindestens eines der W&ouml;rter soll vorkommen (OR-vergleich)<br>
<input type="radio" name="mode" value="genau"<? if ($_REQUEST['mode'] == "genau") echo " checked=\"checked\""; ?>> Exakte &uuml;bereinstimmung (string-vergleich)<br>
<input type="radio" name="mode" value="regex"<? if ($_REQUEST['mode'] == "regex") echo " checked=\"checked\""; ?>> Regular-Expression Suche (Wildcards % und _)<br>
<input type="radio" name="mode" value="maxday"<? if ($_REQUEST['mode'] == "maxday") echo " checked=\"checked\""; ?>> Tage mit maximaler Anzahl anzeigen<br>

<input type="submit" value="Go!"></form><!--<a href="javascript:window.open('chatcountwords.php','CountWordsWindow','width=250,height=400,resizable=yes');">Anzahl Vorkommen eines Wortes z&auml;hlen ?</a> -->


<?
if ($_REQUEST['q'] || $_REQUEST['autor']) {
	
	if ($_REQUEST['mode'] == "and" || $_REQUEST['mode'] == "or") { // wir müssen die query in ein array umwandeln
	
		$words = explode(" ",$_REQUEST['q']);
		
		$whereclause = ""; // init
		
		for($x=0;$x<count($words);$x++){
			$whereclause .= "chat_data.kommentar LIKE '%".addslashes(htmlentities($words[$x]))."%'";
			if (($_REQUEST['mode'] == "or") && (($x+1) < count($words))) $whereclause .= " OR ";
			if (($_REQUEST['mode'] == "and") && (($x+1) < count($words))) $whereclause .= " AND ";
		}
	} elseif ($_REQUEST['mode'] == "genau") {
		
		$query = addslashes(htmlentities($_REQUEST['q']));
		$whereclause = "chat_data.kommentar = '$query'";
	
	} elseif ($_REQUEST['mode'] == "regex") {
		
		$query = addslashes(htmlentities($_REQUEST['q']));
		$whereclause = "chat_data.kommentar LIKE '$query'";
	
	} else { // $_REQUEST['mode'] == "string": einfach addslashes und ab die post..
	
		$query = addslashes(htmlentities($_REQUEST['q']));
		$whereclause = "chat_data.kommentar LIKE '%$query%'";
	}
	
	if ($_REQUEST['autor']) $whereclause = "($whereclause) AND chat_data.UID = ".(integer)$_REQUEST['autor'];

	if ($_REQUEST['notcount']) $sql = "SELECT chat_user.username,chat_data.kommentar,chat_data.posttime,chat_data.PID FROM chat_data,chat_user WHERE chat_data.UID = chat_user.UID AND ($whereclause) ORDER BY chat_data.posttime DESC;";
	else $sql = "SELECT chat_user.UID, chat_user.username, count(chat_data.PID) AS anz FROM chat_data, chat_user WHERE chat_data.UID = chat_user.UID AND ($whereclause) GROUP BY chat_data.UID ORDER BY anz DESC;";
	
	if ($_REQUEST['mode'] == "maxday") {
		$sql = "SELECT COUNT(PID) AS num, LEFT(posttime, 8) FROM chat_data WHERE kommentar LIKE '%".addslashes(htmlentities($_REQUEST['q']))."%'";
		if ($_REQUEST['autor']) $sql .= " AND UID = '".addslashes($_REQUEST['autor'])."'";
		$sql .= " GROUP BY LEFT(posttime, 8) ORDER BY num DESC LIMIT 10;";
	}
	
	$result = mysql_query($sql);
	//echo "SQL: $sql<br>\n";
	if ($_REQUEST['mode'] == "maxday") {
		echo "<br><table border=\"0\" cellpadding=\"3\">\n";
		echo "<tr><td>Rang</td><td>Anzahl</td><td>Datum</td></tr>\n";
		$countstring = "";
		$rang = 1;
		while ($row = mysql_fetch_row($result)) {
			echo "<tr><td align=\"right\">".$rang++.".</td><td align=\"right\">".$row[0]."</td>";
			echo "<td>".shortts2str($row[1])."</td></tr>\n";
			$countstring .= shortts2str($row[1]).": ".$row[0].", ";
		}
		echo "</table>\n";
		echo "<br><input type=\"text\" value=\"".substr($countstring,0,-2)."\" size=\"30\">";
	} elseif ($_REQUEST['notcount']) {
		echo "<br>Gefundene Zeilen: ".mysql_num_rows($result);
	} else {
		echo "<br><table border=\"0\" cellpadding=\"3\">\n";
		echo "<tr><td>Rang</td><td>wer</td><td>wieviele</td><td><form method=\"post\"><input type=\"hidden\" name=\"notcount\" value=\"1\"><input type=\"hidden\" name=\"q\" value=\"{$_REQUEST['q']}\"><input type=\"hidden\" name=\"mode\" value=\"{$_REQUEST['mode']}\"><input type=\"submit\" value=\"Alle anzeigen\"></form></td></tr>\n";
		$countstring = "";
		$rang = 1;
		while ($row = mysql_fetch_row($result)) {
			echo "<tr><td align=\"right\"";
			if ($_SESSION['UID'] == $row[0]) echo " bgcolor=\"lightgreen\"";
			echo ">".$rang++.".<td";
			if ($_SESSION['UID'] == $row[0]) echo " bgcolor=\"lightgreen\"";
			echo "><a target=\"_blank\" href=\"userinfo.php?id=".$row[0]."\">".htmlentities(stripslashes($row[1]))."</a></td><td align=\"right\"";
			if ($_SESSION['UID'] == $row[0]) echo " bgcolor=\"lightgreen\"";
			echo ">";
			//if ($_REQUEST['q'] == "lol" && $row[0] == 1) echo "999"; // hehe
			echo $row[2];
			echo "</td><td align=\"right\"";
			if ($_SESSION['UID'] == $row[0]) echo " bgcolor=\"lightgreen\"";
			echo "><form method=\"post\"><input type=\"hidden\" name=\"notcount\" value=\"1\"><input type=\"hidden\" name=\"q\" value=\"{$_REQUEST['q']}\"><input type=\"hidden\" name=\"mode\" value=\"{$_REQUEST['mode']}\"><input type=\"hidden\" name=\"autor\" value=\"{$row[0]}\"><input type=\"submit\" value=\"anzeigen\"></form></td></tr>\n";
			$countstring .= stripslashes($row[1]).": ".$row[2].", ";
		}
		echo "</table>\n";
		echo "<br><input type=\"text\" value=\"".substr($countstring,0,-2)."\" size=\"30\">";
	}
	echo "<br></td></tr>\n";
	
	if ($_REQUEST['notcount']) {
		while ($row = mysql_fetch_row($result)) {
			echo "<tr><td align=\"left\"><table width=\"99%\"><tr><td>".stripslashes($row[1])." <font size=\"-2\">(von ".htmlentities(stripslashes($row[0]))." am ".ts2str($row[2]).")</font></td><td align=\"right\"><form action=\"keinstress.php#{$row[3]}\" method=\"get\"><input type=\"hidden\" name=\"goid\" value=\"{$row[3]}\"><input type=\"submit\" value=\"im Kontext zeigen\"></form></td></tr></table></td></tr>\n";
		}
	}
} else echo "</td></tr>";
?>

</table>
<?
include("footer.inc.php");
?>