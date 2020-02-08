<?
include_once("connect.inc.php");

$_SESSION['knownusers'] = array();

$sql = "UPDATE chat_user SET preferences = '".addslashes(session_encode())."' WHERE UID = '".addslashes($_SESSION['UID'])."' LIMIT 1;";
mysql_query($sql);

setcookie(session_name() ,"",0,"/"); 

session_unset(); 

$_SESSION = array();

session_destroy();
?>