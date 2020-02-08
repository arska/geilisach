<?
include("connect.inc.php");
include("auth.inc.php");

$extrahead = "<frameset rows=\"210,*\">
<frame src=\"chatinput.php\" name=\"input\" scrolling=\"auto\">
<frame src=\"chatoutput.php\" name=\"output\" scrolling=\"auto\" frameborder=\"0\">
</frameset>";

include("header.inc.php");
echo "Sorry, Sie brauchen einen Browser der Frames unterst&uuml;tzt um diesen Chat wie vorgesehen zu benutzen !<br><br>";
echo "Es ist aber m&ouml;glich den Chat (mit Einschr&auml;nkungen) auch mit 2 Browser-Fenstern zu verwenden:<br>";
echo "&Ouml;ffnen Sie dazu die <a href=\"chatinput.php\" target=\"_blank\">Chat-Eingabemaske</a> und die <a href=\"chatoutput.php\" target=\"_blank\">Chat-Ausgabe</a> in verschiedenen Browser-Fenstern.<br><br>";
echo "Viel Spass !";
include("footer.inc.php");
?>