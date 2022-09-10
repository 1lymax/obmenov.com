<?
header ('Content-type: text/html; charset=windows-1251');
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php");
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/function.php");
include 'game/wm.class.php';
$wm = new wmbank;
//print_r($_POST);


if(isset($_POST['LMI_HASH']))die($wm->checkHash($_POST));
if(isset($_POST['LMI_PREREQUEST']))die($wm->checkPostWm($_POST));
?>