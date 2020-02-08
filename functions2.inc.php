<?
function makethumb ($id, $s, $h=0, $v=0, $addpath = "", $debug=0) { // aufzurufen mit makethumb(ID, 640) oder makethumb(ID, 0, 640, 480)
	include_once("connect.inc.php");
	
	if (!function_exists("imagecreatefromjpeg")) return false; // kein GD -> kein business
	
	if (!$s && (!$h || !$v)) {
		if ($debug) echo "Size=$s, H=$h, v=$v\n";
		return false;
	}
	
	if ($id) {
		$id = addslashes((integer)$id);
		$sql = "SELECT * FROM bilder_info WHERE ID = '$id';";
		$result = mysql_query($sql);
		
		if (!$result) {
			if ($debug) echo "Kein erstes result -> bild-id gib's nicht\n";
			return false;
		}
		
		$bild_info = mysql_fetch_array($result);

		if ($bild_info['mime_type'] == "image/jpeg")
		{
			if (($orig = imagecreatefromjpeg($addpath.$bild_info['path'])) == false) {
				echo "[Couldn't create thumb from jpg ID=$id]";
				return false;
			}
		}
		elseif ($bild_info['mime_type'] == "image/gif")
		{
			if (($orig = imagecreatefromgif($addpath.$bild_info['path'])) == false) {
				echo "[Couldn't create thumb from gif ID=$id]";
				return false;
			}
		}
		elseif ($bild_info['mime_type'] == "image/png")
		{
			if (($orig = imagecreatefrompng($addpath.$bild_info['path'])) == false) {
				echo "[Couldn't create thumb from png ID=$id]";
				return false;
			}
		} else {
			echo "[mime-typ {$bild_info['mime_type']} von bild ID=$id nicht erkannt]";
			return false;
		}
	
		
		if ($s) {
			if ($bild_info['hsize'] >= $bild_info['vsize']) { // querformat
				$hor = (integer)$s;
				$ver = $bild_info['vsize']*$s/$bild_info['hsize'];
			} else { // hochformat
				$hor = $bild_info['hsize']*$s/$bild_info['vsize'];
				$ver = (integer)$s;
			}
		} else {
			$hor = $h;
			$ver = $v;
		}
		
		$thumb = imagecreatetruecolor($hor, $ver);
		
		if (@imagecopyresampled($thumb, $orig, 0,0,0,0,$hor, $ver, $bild_info['hsize'], $bild_info['vsize'])); // GD2
		elseif (@ImageCopyResampleBicubic($thumb, $orig, 0,0,0,0,$hor, $ver, $bild_info['hsize'], $bild_info['vsize'])); // GD1/2 ?
		elseif (@imagecopyresized($thumb, $orig, 0,0,0,0,$hor, $ver, $bild_info['hsize'], $bild_info['vsize'])); // GD1
		else return false;
		
		$sql = "INSERT INTO bilder_info SET name = '".addslashes(stripslashes($bild_info['name']))."', hsize = '$hor', vsize = '$ver', parent = '$id', mime_type = 'image/jpeg', lastedit = NOW();";
		$result = mysql_query($sql);
		$thumbid = mysql_insert_id();
		
		$thumbpath = "./generated/$thumbid.jpg"; // hard-coded.. may need some adjusting.. 
		
		if (!imagejpeg($thumb,$addpath.$thumbpath)) { // mime-type ist im sql hard-coded
			echo "[Couldn't save Thumb]";
			$sql = "DELETE FROM bilder_info WHERE ID = $thumbid LIMIT 1;";
			mysql_query($sql);
			return false;
		}
		
		imagedestroy($orig);
		imagedestroy($thumb);
		
		$thumbsize = filesize($thumbpath);
		
		$sql = "UPDATE bilder_info SET size = '$thumbsize', path = '$thumbpath' WHERE ID = $thumbid LIMIT 1;";
		mysql_query($sql);
		
		return $thumbid;
	}
}

function ImageCopyResampleBicubic (&$dimg, &$simg, $dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh){
	ImagePaletteCopy ($dimg, $simg );
	$rX = $sw / $dw ;
	$rY = $sh / $dh ;
	$nY = 0;
	for( $y =$dy ; $y <$dh ; $y ++){
		$oY = $nY ;
		$nY = round (( $y + 1)* $rY );
		$nX = 0;
		for( $x =$dx ; $x <$dw ; $x ++){
			$oX = $nX ;
			$nX = round (( $x + 1)* $rX );
			$r = $g = $b = $a = 0;
			for( $i =$nY ;-- $i >= $oY ;){
				for( $j =$nX ;-- $j >= $oX ;){
					$c = ImageColorsForIndex ($simg, ImageColorAt ($simg, $j, $i));
					$r += $c['red'];
					$g += $c['green'];
					$b += $c['blue'];
					$a ++;
				}
			}
			ImageSetPixel ($dimg, $x, $y, ImageColorClosest($dimg, $r/$a, $g/$a, $b/$a));
		}
	}
}

function getimage ($id, $linkto=0, $s=0, $addpath="", $linktarget="", $debug = 0) { // $linkto=0 -> no link; $s=0 -> originalgrösse; $addpath: path zu output.php; $linktarget: target="" für $linkto
	if ($s) $criteria = "(parent = $id AND parent = ID AND ((hsize <= $s AND vsize < $s) OR (vsize <= $s AND hsize < $s))) OR (parent = $id AND parent != ID AND ((hsize = $s AND vsize < $s) OR (vsize = $s AND hsize < $s)))"; // spezielle grösse angegeben, (original-bild, das kleiner ist als $s) OR (thumb, das die exakte grösse hat)
	else $criteria = "parent = $id AND parent = ID"; // original-bild
	
	$sql = "SELECT ID,name,path,hsize,vsize FROM bilder_info WHERE $criteria ORDER BY hsize DESC LIMIT 1;";
	$result = mysql_query($sql);
	
	if (mysql_num_rows($result) < 1) { // keine solche grösse vorhanden
		$sql = "SELECT ID FROM bilder_info WHERE parent = $id AND parent = ID"; // gibts das bild mit $id überhaupt ?
		$result = mysql_query($sql);
		if (mysql_num_rows($result) < 1) { // das bild mit $id gibt's gar nicht !
			echo "[no Picture ID $id]";
			return;
		} else {
			$thumbid = makethumb($id, $s, 0, 0, $addpath, $debug); // macht ein bild mit den richtigen grössen
			if (!$thumbid) { // makethumb error -> browser-resized
				$browserresize = true;
				$sql = "SELECT ID,name,path,hsize,vsize FROM bilder_info WHERE (parent = $id AND (hsize > $s OR vsize > $s)) ORDER BY hsize ASC LIMIT 1;";
				$result = mysql_query($sql);
			} else {
				$sql = "SELECT ID,name,path,hsize,vsize FROM bilder_info WHERE ID = $thumbid;";
				$result = mysql_query($sql);
			}
		}
	}
	
	$row = mysql_fetch_array($result);
		
	if ($linkto) echo "<a href=\"$linkto\"".($linktarget ? " target=\"$linktarget\"" : "").">";
	echo "<img src=\"$addpath".$row['path']."\" alt=\"".stripslashes($row['name'])."\" width=\"".($browserresize ? $s*$row['hsize']/max($row['hsize'],$row['vsize']) : $row['hsize'])."\" height=\"".($browserresize ? $s*$row['vsize']/max($row['hsize'],$row['vsize']) : $row['vsize'])."\" border=\"0\">";
	if ($linkto) echo "</a>";
}

?>