<?
include("includes.inc.php");
include("admin.inc.php");

$debug = 1;

if ($_REQUEST['goedit']) $debug = 0; // or else no redirect...

$title = "Bild hochladen";
if (!$_REQUEST['goedit']) include("header.inc.php");

if ($_FILES) { // ein file..
	if ($debug) echo "File uploaded<br>\n";
	
	$tmpfile = $_FILES['userfile']['tmp_name'];
	if ($debug) echo "tempfile-path: $tmpfile<br>\n";
	$imgsize = getimagesize($tmpfile);
	
	
	
	if ($imgsize['mime']) $mime_type = $imgsize['mime'];
	else $mime_type = $_FILES['userfile']['type'];
	if ($debug) echo "Fileinfo: name = '".addslashes($_FILES['userfile']['name'])."', hsize = '".$imgsize[0]."', vsize = '".$imgsize[1]."', size = '".addslashes($_FILES['userfile']['size'])."', mime_type = '".addslashes($mime_type)."'<br>\n";
	
	$sql = "INSERT INTO bilder_info SET parent = 0, UID = '".$_SESSION['UID']."', name = '".addslashes($_FILES['userfile']['name'])."', hsize = '".$imgsize[0]."', vsize = '".$imgsize[1]."', size = '".addslashes($_FILES['userfile']['size'])."', mime_type = '".addslashes($mime_type)."', lastedit = NOW();";
	$result = mysql_query($sql);
	$file_id = mysql_insert_id();
	if ($debug && $result) echo "Info inserted @ ID: $file_id<br>\n";
	
	$path = "./generated/$file_id";
	if (strpos($mime_type,"gif")) $path .= ".gif";
	elseif (strpos($mime_type,"png")) $path .= ".png";
	else $path .= ".jpg"; // jpg ist default..
	
	if (move_uploaded_file($tmpfile,$path)) {
		if ($debug) echo "Moved $tempfile to $path<br>\n";
	} else {
		echo "COULDN'T MOVE $tempfile to $path; is the destination writable ?? Leaving ID=$file_id fucked !<br>\n";
		return;
	}
	
	$sql = "UPDATE bilder_info SET parent = $file_id, path = '$path' WHERE ID = $file_id;";
	mysql_query($sql);
	if ($debug && $result) echo "parent & path updated @ ID: $file_id<br>\n";
		
	if ($_REQUEST['goedit']) header("Location: editpicture.php?id=$file_id");

} // kein file
	
if (!$_REQUEST['goedit']) {
	echo "<form enctype=\"multipart/form-data\" action=\"{$_SERVER['PHP_SELF']}\" method=\"POST\">";
	echo "<input type=\"file\" name=\"userfile\"><br>";
	echo "<input type=\"checkbox\" name=\"goedit\" value=\"true\"> Go to edit page<br>";
	echo "<input type=\"submit\">";
	echo "</form>";
	include("footer.inc.php");
}
?>