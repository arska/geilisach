<?
include("connect.inc.php");
include("../auth.inc.php"); // muss user sein.. naja.. uploaden darf jeder trotzdem nicht.. =)


$debug = 1;
$dir = "./upload/";

$title = "importing";
include("header.inc.php");

if (is_dir($dir)) {
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (filetype($dir . $file) == "file") {
					
				$tmpfile = ($dir . $file);
				if ($debug) echo "tempfile-path: $tmpfile<br>\n";
				$filesize = filesize($tmpfile) or die("couldn't stat $tmpfile !");
				$imgsize = getimagesize($tmpfile) or die("couldn't read $tmpfile !");
				
				if ($imgsize['mime']) $mime_type = $imgsize['mime'];
				else $mime_type = "image/jpeg";
				if ($debug) echo "Fileinfo: name = '".addslashes($file)."', hsize = '".$imgsize[0]."', vsize = '".$imgsize[1]."', size = '".addslashes($filesize)."', mime_type = '".addslashes($mime_type)."'<br>\n";
				
				$sql = "INSERT INTO bilder_info SET parent = 0, UID = '".$_SESSION['UID']."', name = '".addslashes($file)."', hsize = '".$imgsize[0]."', vsize = '".$imgsize[1]."', size = '".addslashes($filesize)."', mime_type = '".addslashes($mime_type)."', lastedit = NOW();";
				$result = mysql_query($sql);
				$file_id = mysql_insert_id();
				if ($debug && $result) echo "Info inserted @ ID: $file_id<br>\n";
				
				$path = "./generated/$file_id";
				if (strpos($mime_type,"gif")) $path .= ".gif";
				elseif (strpos($mime_type,"png")) $path .= ".png";
				else $path .= ".jpg"; // jpg ist default..
				
				if (copy($tmpfile,$path)) {
					if ($debug) echo "Moved $tempfile to $path<br>\n";
				} else {
					echo "COULDN'T COPY $tempfile to $path; is the destination writable ?? Leaving ID=$file_id fucked !<br>\n";
					return;
				}
				
				$sql = "UPDATE bilder_info SET parent = $file_id, path = '$path' WHERE ID = $file_id;";
				mysql_query($sql);
				if ($debug && $result) echo "parent & path updated @ ID: $file_id<br>\n";
				echo "All data inserted and file closed.<br>\n";
				if (@unlink($tmpfile)) echo "File deleted.<br>\n";
				else echo "File NOT deleted.<br>\n";
					
			} else echo "Not-file '$dir$file' ignored.<br>";
		}
	} else echo "could not open directory '$dir'";
} else echo "not a directory '$dir'";

closedir($dh);

include("footer.inc.php");

?>