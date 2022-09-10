<?php 
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php");
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/function.php");
		include '/var/www/webmoney_ma/data/www/obmenov.com/game/game/wm.class.php';

$wm = new wmbank;
$wm->updateProjects();
?>
