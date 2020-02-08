<? // turnus.inc.php vers. 4  (mit DB)

include_once("connect.inc.php");
include_once("auth.inc.php");
include_once("functions2.inc.php");

$turnuszeit = 420;

// check, ob das bild noch aktuell ist
$sql = "SELECT bild_ID,(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(lastvote)) FROM turnus_bilder ORDER BY lastvote DESC LIMIT 1;"; // das zuletzt gevotete bild = das aktuelle bild
$result = mysql_query($sql);
$row = mysql_fetch_row($result);

if ($row[1] >= $turnuszeit) { // wenn das bild tatsächlich vor mehr als $turnuszeit zuletzt gevotet wurde = veraltetes bild
	$sql = "SELECT bild_id FROM turnus_bilder ORDER BY lastvote ASC;"; // liste der turnusfähigen bilder aktualisieren
	$result = mysql_query($sql);
	$turnusliste = Array();
	while ($bild_id = mysql_fetch_row($result)) array_push($turnusliste,$bild_id[0]);
	srand((double)microtime()*1000000);
	$bild_id = $turnusliste[rand(0,count($turnusliste)-(1+floor(count($turnusliste)*0.50)))]; // neues random-bild bestimmen, die letzten 50% nicht nochmal wählen
	$sql = "UPDATE turnus_bilder SET votes = votes + 1, lastvote = NOW() WHERE bild_id = '$bild_id' LIMIT 1;"; // dieses bild voten.. tadaaa
	mysql_query($sql);
}
else $bild_id = $row[0]; // wenn das bild doch noch aktuell war, nehmen wir natürlich das..

getimage($bild_id,"/yalampgallery/showpicture.php?id=$bild_id",200, "./yalampgallery/", "_blank");

?>

