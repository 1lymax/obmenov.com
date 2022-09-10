<?php 

	require_once('../Connections/ma.php');
	$select="select orders.id as orderid, orders.clid as clid
					from orders, payment where orders.id=payment.orderid and payment.ordered=1 and payment.canceled=0
							and orders.currout in ('".implode("','",$GLOBALS['rbanks'])."')";
	$query=_query($select,"");
	while ( $row=$query->fetch_assoc() ) {
	
		echo "https://obmenov.com/partner/dsufgyhskdfh.php?oid=".$row['orderid']."&clid=".$row['clid']."\r\n";
	}
	
	
?>