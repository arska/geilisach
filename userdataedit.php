<? // userdataedit.php
include("connect.inc.php");
include("auth.inc.php");
include("functions.inc.php");
include("admin.inc.php");

if (!$_REQUEST['id'] || !$_REQUEST['column'] || !in_array(addslashes($_REQUEST['column']), array("username", "fullname", "herkunft", "email", "natel", "skype", "geburi", "beschreibung", "homepage", "ownpic", "disclose", "mf", "account", "status"))) {
	$title = "UID?";
	include("header.inc.php");
	echo "UID und/oder Column fehlt oder ist falsch. <a href=\"userinfoextra.php?id=".$_REQUEST['id']."\">Userinfo-Extra</a>";
	include("footer.inc.php");
	exit;
}
// else
$id = (integer)addslashes($_REQUEST['id']);
$column = addslashes($_REQUEST['column']);

if ($_REQUEST['savedata']) {
	if ($column == "disclose") {
		$data = addslashes(implode(",",$_REQUEST['data']));
	} else $data = addslashes($_REQUEST['data']);
	
	$sql = "UPDATE chat_user SET $column = '$data', lastaccess = lastaccess, lastedit = NOW() WHERE UID = $id;";
	if (mysql_query($sql)) {
		header("location: userinfoextra.php?id=$id");
	}
}

$title = "Edit Data";
include("header.inc.php");

$sql = "SELECT $column FROM chat_user WHERE UID = $id;";
$result = mysql_query($sql);
$data = mysql_fetch_row($result);
$data = $data[0];

echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
echo "<input type=\"hidden\" name=\"id\" value=\"$id\">";
echo "<input type=\"hidden\" name=\"column\" value=\"$column\">";

if ($column == "ownpic") {
	echo "<select name=\"data\"><option value=\"0\">--keines--</option>";
	
	$sql = "SELECT ID,name FROM bilder_info WHERE ID = parent ORDER BY ID ASC;";
	$bildresult = mysql_query($sql);
	while ($bildrow = mysql_fetch_row($bildresult)) {
		echo "<option value=\"{$bildrow[0]}\"";
		if ($bildrow[0] == $data) echo " selected=\"selected\"";
		echo ">{$bildrow[0]}: {$bildrow[1]}</option>";
	}
	echo "</select>";
} elseif ($column == "disclose") {
	echo "<select name=\"data[]\" size=\"4\" multiple=\"multiple\">";
	echo "<option value=\"fullname\"".(in_array("fullname",explode(",",$data)) ? " selected=\"selected\"" : "").">fullname</option>";
	echo "<option value=\"email\"".(in_array("email",explode(",",$data)) ? " selected=\"selected\"" : "").">email</option>";
	echo "<option value=\"natel\"".(in_array("natel",explode(",",$data)) ? " selected=\"selected\"" : "").">natel</option>";
	echo "<option value=\"herkunft\"".(in_array("herkunft",explode(",",$data)) ? " selected=\"selected\"" : "").">herkunft</option>";
	echo "</select>";
} elseif ($column == "mf") {
	echo "<select name=\"data\">";
	echo "<option value=\"m\"".($data == "m" ? " selected=\"selected\"" : "").">M</option>";
	echo "<option value=\"f\"".($data == "f" ? " selected=\"selected\"" : "").">F</option>";
	echo "</select>";
} elseif ($column == "status") {
	echo "<select name=\"data\">";
	echo "<option value=\"user\"".($data == "user" ? " selected=\"selected\"" : "").">user</option>";
	echo "<option value=\"member\"".($data == "member" ? " selected=\"selected\"" : "").">member</option>";
	if ($status == "god") echo "<option value=\"admin\"".($data == "admin" ? " selected=\"selected\"" : "").">admin</option>";
	if ($status == "god") echo "<option value=\"god\"".($data == "god" ? " selected=\"selected\"" : "").">god</option>";
	echo "</select>";
} elseif ($column == "account") {
	echo "<select name=\"data\">";
	echo "<option value=\"active\"".($data == "active" ? " selected=\"selected\"" : "").">active</option>";
	echo "<option value=\"pending\"".($data == "pending" ? " selected=\"selected\"" : "").">pending</option>";
	echo "</select>";
} else {
	echo $column.": <input type=\"text\" name=\"data\" value=\"".stripslashes($data)."\">";
}

echo "<input type=\"submit\" name=\"savedata\" value=\"Speichern!\">";

echo "</form>";

?>