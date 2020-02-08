<? // functions.inc.php, vers. 5, requires connect.inc.php

function whoisonline($timeout=60) {
// removed and integrated to chatoutput.php for more functionality
}

function ts2str($ts) { // timestamp format '20021229044918'
	return dt2str($ts);
	//return substr($ts,6,2).".".substr($ts,4,2).".".substr($ts,0,4).", ".substr($ts,8,2).":".substr($ts,10,2)/*.":".substr($ts,12,2)*/;
	
}

function shortts2str($ts) { // timestamp format '20021229044918'
	return d2str($ts);
	//return substr($ts,6,2).".".substr($ts,4,2).".".substr($ts,0,4);
	
}

function ts2unix($ts) { // timestamp format '20021229044918'
	
	return dt2unix($ts);
	//return mktime(substr($ts,8,2), substr($ts,10,2), substr($ts,12,2), substr($ts,4,2), substr($ts,6,2), substr($ts,0,4));
	
}

function dt2str($dt) { // datetime format '0000-00-00 00:00:00'
	
	return substr($dt,8,2).".".substr($dt,5,2).".".substr($dt,0,4).", ".substr($dt,11,2).":".substr($dt,14,2)/*.":".substr($dt,17,2)*/;
	
}

function dt2unix($dt) { // datetime format '0000-00-00 00:00:00'
	
	return mktime(substr($dt,11,2), substr($dt,14,2), substr($dt,17,2), substr($dt,5,2), substr($dt,8,2), substr($dt,0,4));
	
}


function d2str($d) { // date format '0000-00-00'
	
	return substr($d,8,2).".".substr($d,5,2).".".substr($d,0,4);
	
}

function d2unix($d) { // date format '0000-00-00'
	
	return mktime(0, 0, 0, substr($d,5,2), substr($d,8,2), substr($d,0,4));
	
}

function unix2str($unix) {
	
	$wochentage = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa");

	return $wochentage[(integer)date("w",$unix)] .", ". date("d.m.Y, H:i",$unix);

}

function unix2shortstr($unix) {
	
	$wochentage = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa");
	return $wochentage[(integer)date("w",$unix)] .", ". date("d.m.Y",$unix);

}

function movefile ($oldfile, $destfile) {
	$errors = array();
	if (!file_exists($destfile)) {
		if (copy($oldfile, $destfile)) {
			if (!unlink($oldfile)) array_push($errors,"Couldn't delete '$oldfile' after copying it to '$newfile'.");
		} else {
			array_push($errors,"Couldn't copy '$oldfile' to '$newfile'.");
		}
	} else array_push($errors,"'$destfile' exists already, so I can't overwrite it. Rename the to be moved or delete the existing file.");
	return $errors;
}

function buildsearchmenu() {
	
	global $maxanzahl;
	global $numrows;
	
	// begin function
	if ($_SESSION['offset'] % $_SESSION['anzahl']) $maxpages++;
	$maxpages += ceil(($numrows - ($_SESSION['offset'] % $_SESSION['anzahl']))/$_SESSION['anzahl']);
	
	$thispage = ceil(($_SESSION['offset']/$_SESSION['anzahl'])+1);
	
	echo "<center><form action=\"{$_SERVER['PHP_SELF']}\" method=\"get\">";
	echo "<input type=\"hidden\" name=\"desc\" value=\"";
	if ($_SESSION['desc'] == "true") echo "true";
	else echo "false";
	echo "\">";
	echo "Von Zeile Nr. <input type=\"text\" name=\"offset\" value=\"{$_SESSION['offset']}\" size=\"6\"> (max. ".($numrows - 1).") zeige <input type=\"text\" name=\"anzahl\" value=\"{$_SESSION['anzahl']}\" size=\"3\"> (max {$maxanzahl}) <input type=\"submit\" name=\"gonowhere\" value=\"OK\">. &Auml;ndere Leserichtung in <input type=\"submit\" name=\"changedirection\" value=\"";
	if ($_SESSION['desc'] == "true") echo "von oben nach unten";
	else echo "von unten nach oben";
	echo "\">.<br>";
	if ($thispage != 1) echo "<input type=\"submit\" name=\"gofirst\" value=\"|<-\"><input type=\"submit\" name=\"goleft\" value=\"<<\"> ";
	
	
	if ($thispage == $maxpages) $korrekturfaktor = -2;
	elseif ($thispage == ($maxpages - 1)) $korrekturfaktor = -1;
	elseif ($thispage == 2) $korrekturfaktor = 1;
	elseif ($thispage == 1) $korrekturfaktor = 2;
	else $korrekturfaktor = 0;
	
	for ($i=-2; $i < 3; $i++) {
		if (($i + $korrekturfaktor + $thispage) > 0) {
			if (($i + $korrekturfaktor + $thispage) == 1) { // wir haben die untere seitengrenze erreicht - seite 1 ist am anfang der liste
				echo "[ ";
			} else {
				if ($i == -2) echo "..";
				echo " | ";
			}
			if (($i + $korrekturfaktor + $thispage) == $thispage) { // wir schreiben die aktuelle seite als zahl und nocht als button
				echo $thispage;
			} else {
				echo "<input type=\"submit\" name=\"gopage\" value=\"".($i + $korrekturfaktor + $thispage)."\">";
			}
			
			if (($i + $korrekturfaktor + $thispage) == $maxpages) { // wir haben die obere seitengrenze erreicht - seite $maxpages ist am ende der liste
				echo " ]";
			} else {
				if ($i == 2) echo " | ..";
			}
		} // kein else - wenn wir von oben herunterzŠhlen und schon unten ankommen, hat es weniger als 5 seiten -> auch nicht mehr anzeigen
	}
	if ($thispage != $maxpages) echo "<input type=\"submit\" name=\"goright\" value=\">>\"><input type=\"submit\" name=\"golast\" value=\"->|\">";
	echo "</form></center>";
	
	//end function
}

function parsestyles($text) {
	$text2 = preg_replace("'\[([uib]?)\](.*?)\[/\\1\]'","<\\1>\\2</\\1>",$text);
	if ($text == $text2) return $text; // everything replaced
	else return parsestyles($text2); // maybe more to replace..
}

?>
