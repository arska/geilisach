<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<html>
	<head>
		<title>YALAMPGallery - <?=($title) ?><? if ($_SESSION['username']) echo " - \"".$_SESSION['username']."\""; ?></title>
		<? if ($refreshme || $refreshurl) { ?><META HTTP-EQUIV=REFRESH CONTENT="<?=$_SESSION['refresh'] ?><? if ($refreshurl) echo "; url=$refreshurl"; ?>"><? } ?>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
		<link rel="icon" href="/favicon.ico" type="image/x-icon">
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"> 
		<link rel="bookmark" href="http://wwww.geilisach.ch/">
	</head>
	<body>
<? flush(); ?>