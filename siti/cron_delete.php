<?php require_once('/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php');

mysql_select_db($database_ma, $ma);
_query('SET character_set_database = cp1251',"");
_query('SET NAMES cp1251',"");

	$select="SELECT *
		FROM `clients`
		WHERE activated =0
		AND (
		date + INTERVAL 242000
		MINUTE < NOW( )
		)";
	$query=_query2($select,"cron_delete.php 1");
	
	while ($row_client=$query->fetch_assoc() ){
			
		
	}
	
	$select="SELECT *
		FROM `orders`
		WHERE ordered =0
		AND (
		time + INTERVAL 48 HOUR < NOW( )
		)";
	$query=_query2($select,"cron_delete.php 2");
	
	while ($row_order=$query->fetch_assoc() ){
		$select="delete from payment_out where payment=".$row_order['id']." AND partnerid !=302";
		$query1=_query2($select,"cron_delete.php 4");
		if ( mysql_affected_rows()==1 ) {
			$select="delete from payment where orderid=".$row_order['id'];
			$query1=_query2($select,"cron_delete.php 3");

		}
	}
	$select="DELETE
		FROM `orders`
		WHERE ordered =0 AND
		partnerid !=302
		AND (
		time + INTERVAL 48 HOUR < NOW()
		)";
	$query=_query2($select,"cron_delete.php 2");
	
	
	$select="SELECT *
		FROM `orders` , payment
		WHERE payment.ordered =0
		AND payment.canceled =0
		AND orders.ordered =1
		AND payment.orderid = orders.id
		AND (
		time + INTERVAL 48 HOUR < NOW( )
		)";
	$query=_query2($select,"cron_delete.php 2");
	
	while ($row_order=$query->fetch_assoc() ){
		$select="delete from payment where orderid=".$row_order['id'];
		$query1=_query2($select,"cron_delete.php 3");
		$select="delete from payment_out where payment=".$row_order['id'];
		$query1=_query2($select,"cron_delete.php 4");
		
	}
?>