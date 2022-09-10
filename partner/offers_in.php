<?php 

	require_once('../Connections/ma.php');
	$select="select orders.id as orderid, orders.clid as clid
					from orders, payment where orders.id=payment.orderid and orders.ordered=1 and payment.canceled=0
					and orders.currin in ('".implode("','",$GLOBALS['rbanks'])."')
					AND	(orders.time +  INTERVAL 1000 HOUR > NOW()) ";
	$query=_query2($select,"");
	while ( $row=$query->fetch_assoc() ) {
	
		echo "https://obmenov.com/partner/dsufgyhskdfh.php?oid=".$row['orderid']."&clid=".$row['clid']."\r\n";
	}
	
	
?>