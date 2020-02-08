<?
include("connect.inc.php");
include("functions.inc.php");

$sql = "SELECT party_data.*,UNIX_TIMESTAMP(party_data.anlasstime) AS anlasstime_unix FROM party_data WHERE party_data.anlasstime > NOW() ORDER BY party_data.aid ASC;";
$result = mysql_query($sql);

?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Arska.net//Geilisach.ch Calendar//1.0
CALSCALE:GREGORIAN
X-WR-CALNAME:Geilisach.ch
<?
while ($row = mysql_fetch_array($result)) {
	echo "BEGIN:VEVENT\n";
	echo "DTSTART;TZID=Europe/Zurich:".strftime("%Y%m%dT%H%M%S",$row['anlasstime_unix'])."\n";
	echo "DTEND;TZID=Europe/Zurich:".strftime("%Y%m%dT%H%M%S",$row['anlasstime_unix'] + 3600)."\n";
	echo "SUMMARY:".stripslashes($row['anlass'])."\n";
	echo "UID:{$row['UID']}@geilisach.ch\n";
	echo "SEQUENCE:0\n";
	echo "DTSTAMP:".strftime("%Y%m%dT%H%M%S",ts2unix($row['posttime']))."\n";
	echo "URL:{$row['homepage']}\n";
	echo "LOCATION:{$row['ort']}\n";
	echo "DESCRIPTION:".str_replace("\n","",str_replace("\r","",str_replace("<br />","",$row['beschreibung'])))."\n";
	echo "CLASS:PUBLIC\n";
	echo "END:VEVENT\n";
} ?>
END:VCALENDAR
