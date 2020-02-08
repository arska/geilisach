<?

if ($_REQUEST['timing']) $start = gettimeofday();

include("connect.inc.php");

$id = addslashes((integer)trim($_REQUEST['id']));
if ($id) {
	$sql ="SELECT *,UNIX_TIMESTAMP(lastedit) AS ts FROM bilder_info WHERE ID = $id;";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	
	if (!$result || !$row) {
		include("header.inc.php");
		echo "keine solche ID";
		include("footer.inc.php");
		exit;
	}
	
	// output the file 
	header("Content-type: {$row['mime_type']}");
	header("Content-length: {$row['size']}");
	header("Content-Disposition: attachment; filename={$row['name']}");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s",$row['ts'])." GMT");
	//header("Expires: ".gmdate("D, d M Y H:i:s",date()+30*24*3600)." GMT"); // + 1 Monat
	header("Cache-Control: max-age=10000000, s-maxage=1000000");

	flush();
	
	// get the file data 
	$sql = "SELECT data FROM bilder_data WHERE bild_ID = $id ORDER BY ID ASC;";
	$result = mysql_query($sql);
	
	// decode the fragments and recombine the file 
	while ($datarow = mysql_fetch_row($result)) {
		if ($row['datatype'] == "slashes") echo stripslashes($datarow[0]); 
		else echo base64_decode($datarow[0]); // default: base64_decode
		flush();
	}
	
	$sql = "UPDATE bilder_info SET hits = hits + 1, lastaccess = NOW() WHERE ID = $id;";
	mysql_query($sql);
	
}

if ($_REQUEST['timing']) {
	$end=gettimeofday();
	echo (float)($end['sec'] - $start['sec']) + ((float)($end['usec'] - $start['usec'])/1000000)." seconds passed.";

}

?>