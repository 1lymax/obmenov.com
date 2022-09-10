<?php

		require_once("/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php");
		$dont_insert_client=1;
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/function.php");
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/siti/_header.php");
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/siti/class.php");
@ini_set ("display_errors", true);

		$amount=new amount();
		$amount->update();

?>