<?php 
require_once('../Connections/ma.php'); require_once('../function.php');
if ( isset($_GET['clid']) ) {
	$select="select nikname from clients where clid='".$_GET['clid']."'";
	$query=_query($select, "adrefzw");
	$client=$query->fetch_assoc();
	$_SESSION['AuthUsername']=$client['nikname'];
	$_SESSION['authorized']=1;
	if ( isset($_GET['action']) && $_GET['action']=="cabinet" ) {
		header("Location: http://obmenov.biz/index.php");
	}
}


if ( isset($_GET['nik']) ) {
	$_SESSION['AuthUsername']=$_GET['nik'];
	$_SESSION['authorized']=1;
	
}
//echo $serverroot.'adrefzw/top_partner.php';
?>
<table align="center"><tr><td>
<form action="cabinet.php" method="get">
Ник: <input type="text" name="nik"/><input type="submit" />
</form>
</td></tr></table>
<?php
//require_once($serverroot.'adrefzw/top_cabinet.php');
//echo $serverroot.htmlspecialchars($_GET['scr']);

if ( isset($_GET['scr']) ) {
	require_once($serverroot.htmlspecialchars($_GET['scr']));
	die();
}

?>