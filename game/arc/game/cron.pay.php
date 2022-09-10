<?
//крон :)
		$dont_insert_client=1;
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php");
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/function.php");
		include '/var/www/webmoney_ma/data/www/obmenov.com/game/game/wm.class.php';

$wm = new wmbank;
$wm->checkCron();

//echo $_SERVER['DOCUMENT_ROOT'];
//mail('2nik@ua.fm','wmbanka','cron wmbanka');
//phpinfo();
?>