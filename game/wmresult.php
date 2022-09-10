<?
header ('Content-type: text/html; charset=windows-1251');
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php");
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/function.php");
include 'game/wm.class.php';
$wm = new wmbank;
//print_r($_POST);
@ini_set ("display_errors", true);
if ( isset($_POST)){
	//maildebugger($_POST);
}
if(isset($_POST['payment']) && isset($_POST['signature']))die($wm->checkp24($_POST));
if(isset($_POST['operation_xml']) && isset($_POST['signature']))die($wm->checkmcv($_POST));
if(isset($_POST['LMI_HASH']))die($wm->checkHash($_POST));
if(isset($_POST['LMI_PREREQUEST']))die($wm->checkPostWm($_POST));
?>