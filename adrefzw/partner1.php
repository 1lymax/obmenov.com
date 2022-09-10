<?php require_once('../Connections/ma.php'); ?>
<?php require_once('../function.php');
//echo $serverroot.'adrefzw/top_partner.php';
require_once($serverroot.'adrefzw/top_partner.php');

if ( isset($_GET['pnik']) ) {
	$_SESSION['Partner_AuthUsername']=$_GET['pnik'];
}
if ( isset($_GET['scr']) ) {
	require_once($serverroot.htmlspecialchars($_GET['scr']));
	die();
}

?>