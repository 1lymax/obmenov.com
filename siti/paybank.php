<?php 
$dont_insert_client=1;
require_once('/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php');
require_once($serverroot.'function.php');
require_once($serverroot.'siti/class.php');
@ini_set ("display_errors", true);
echo "tt";

$select="select orders.id, fi.param, fi.param_value, fi.descr, fi.bonus, fi.userid, fi.fi_id 
						from orders, fi where orders.fi_inspect=0 and orders.id>".$GLOBALS['idmax_orders']." 
						and fi.bonus!=0 and (fi.degree=10 or fi.degree=11 or fi.degree=3 or fi.degree=2)  and
						( orders.email = fi.param_value or 
						  'Z' && orders.purse_z=fi.param_value or
						  'U' && orders.purse_u=fi.param_value or 
						  'R' && orders.purse_r=fi.param_value or 
						  'E' && orders.purse_e=fi.param_value or 
						  orders.purse_other=fi.param_value or 
						  orders.account=fi.param_value or 
						  orders.wmid=fi.param_value or 
						  orders.phone=fi.param_value or
						  orders.ip=fi.param_value
						)";
$query=_query2($select,"");
while ( $fi=$query->fetch_assoc() ) {
	print_r($fi);
	$select="select orders.status from orders where id=".$fi['id'];
	$query1=_query($select,"");
	$status=$query1->fetch_assoc();
	$update="update orders set orders.status='".$status['status'].
					"FI параметр: ".$fi['param'].
					", значение: ".$fi['param_value'].
					", описание: ".$fi['descr'].
					", добавил: ".$fi['userid'].
					", запись №: ".$fi['fi_id'].
					"', fi_inspect=2, droped=1 where orders.id=".$fi['id'];
	$query=_query($update,"");
			

}

// приват
$select="select orders.id as orderid, orders.summin, orders.currin, orders.summout, 
	orders.currout, orders.av_balance, orders.time, payment.id from orders, payment where orders.id=payment.orderid
	AND orders.ordered = 1 AND  payment.ordered=1 and 0=".get_setting("use_manual_p24_out_transfers")."
	AND	(orders.currout='P24UAH' OR orders.currout='P24USD')  and orders.droped=0
	AND	(orders.time +  INTERVAL 1440 MINUTE > NOW()) and needcheck=0
	AND payment.canceled=0 order by time"; // and left(orders.account,8)!='44058858' and orders.attach < 150
$query1=_query2($select, "siti/paybank.php 1");
//echo mysql_num_rows($query);

$clorder = new orders();


while ( $pays=$query1->fetch_assoc() ) {
	//print_r($pays);

	//print_r($pays);
	$check_summ=$clorder->check_order_summ($pays['orderid']);
	if ( $check_summ[1]!=$check_summ[2] ) {
		$select="update orders set status='".$check_summ[3]."', 
									droped=1								
					where id=".$pays['orderid'];
		//maildebugger($select);
		$query=_query($select,"");
		continue;
	}
	if ( $clorder->privat_blacklist($pays['orderid']) ) {continue;}
	$select="select * from orders where id=".$pays['orderid'];
	$query=_query($select,"");
	$row_orders=$query->fetch_assoc();
	$clorder->pay_pb($row_orders,$WM_amount_r);
		
	
	
	//die();	
	}
// wm	
	$select="select orders.id as orderid, orders.summin, orders.currin, orders.summout, orders.phone,
		orders.currout, orders.time, payment.id from orders, payment 
		WHERE orders.id=payment.orderid
		AND orders.ordered = 1 
		AND  payment.ordered=1 
		AND orders.currin!='SMS'
		AND orders.droped=0
		AND	left(orders.currout,2)='WM'
		AND	(orders.time +  INTERVAL 1440 MINUTE > NOW()) 
		AND payment.canceled=0
		AND orders.attach > 0.5
		order by time";
	$query1=_query2($select, "siti/paybank.php 1");
	while ( $pays=$query1->fetch_assoc() ) {
		$check_summ=$clorder->check_order_summ($pays['orderid']);
		if ( $check_summ[1]!=$check_summ[2] ) {
			$select="update orders set status='".$check_summ[3]."', 
									droped=1								
					where id=".$pays['orderid'];
			$query=_query($select,"");
			continue;
		}
		$clorder->pay_wm($pays['orderid']);
	}

// pm	
	$select="select orders.id as orderid, orders.summin, orders.currin, orders.summout, orders.phone, orders.purse_other,
		orders.currout, orders.time, payment.id from orders, payment 
		WHERE orders.id=payment.orderid
		AND orders.ordered = 1 
		AND payment.ordered=1 
		AND orders.droped=0
		AND	left(orders.currout,2)='PM' and 1=2
		AND	(orders.time +  INTERVAL 1440 MINUTE > NOW()) 
		AND payment.canceled=0 
		AND orders.attach > 0.3
		order by time"; // and LEFT(orders.currin,2)!=LEFT(orders.currout,2)
	$query1=_query2($select, "siti/paybank.php 1");
	while ( $pays=$query1->fetch_assoc() ) {
		$check_summ=$clorder->check_order_summ($pays['orderid']);
		if ( $check_summ[1]!=$check_summ[2] ) {
			$select="update orders set status='".$check_summ[3]."', 
									droped=1								
					where id=".$pays['orderid'];
			$query=_query($select,"");
			continue;
		}
		$clorder->pay_pm($pays['orderid']);
	}

// lr	
	$select="select orders.id as orderid, orders.summin, orders.currin, orders.summout, orders.phone, orders.purse_other,
		orders.currout, orders.time, payment.id from orders, payment 
		WHERE orders.id=payment.orderid
		AND orders.ordered = 1 
		AND payment.ordered=1 
		AND orders.droped=0
		AND	left(orders.currout,2)='LR' and 1=2
		AND	(orders.time +  INTERVAL 1440 MINUTE > NOW()) 
		AND payment.canceled=0
		AND orders.attach > 0.4
		order by time";
	$query1=_query2($select, "siti/paybank.php 1");
	while ( $pays=$query1->fetch_assoc() ) {
		$check_summ=$clorder->check_order_summ($pays['orderid']);
		if ( $check_summ[1]!=$check_summ[2] ) {
			$select="update orders set status='".$check_summ[3]."', 
									droped=1								
					where id=".$pays['orderid'];
			$query=_query($select,"");
			continue;
		}
		$clorder->pay_lr($pays['orderid']);
	}



// смс
$select="select orders.id as orderid, orders.summin, orders.currin, orders.summout, 
	orders.currout, orders.time, payment.id from orders, payment where orders.id=payment.orderid
	AND orders.ordered = 1 AND  payment.ordered=1  and orders.droped=0
	AND	orders.currin='SMS' and 1=2
	
	AND	(orders.time +  INTERVAL 1440 MINUTE > NOW()) and needcheck=0
	AND payment.canceled=0 order by time"; // AND payment.LMI_PAYER_PURSE=orders.phone
$query1=_query2($select, "siti/paybank.php 1");
while ( $pays=$query1->fetch_assoc() ) {
	$clorder->pay_wm($pays['orderid']);
}
	 //OR left(orders.currout,2)='WM')
	 
	
	
	
	// отправка емейл о принятии платежа
	$select="select orders.id as id, orders.summin, orders.currin, orders.summout, orders.email,
		orders.fname, orders.iname, orders.account, orders.oname,
		orders.discammount, orders.needcheck, orders.clid, orders.purse_r, orders.purse_e, orders.purse_z, orders.purse_u,
		orders.currout, orders.time, payment.id as paymentid,
		(select extname from currency where name=orders.currin) as extnamein, 
		(select extname from currency where name=orders.currout) as extnameout
		from orders, payment where orders.id=payment.orderid
		AND orders.ordered = 1 AND  payment.ordered=1 
		and orders.droped=0 and orders.email_pay_recieved=0 and orders.id >".$GLOBALS['idmax_orders']." 
		AND	(orders.time +  INTERVAL 5 MINUTE > NOW()) order by time";
	$query1=_query2($select, "siti/paybank.php 1");



while ( $row_order=$query1->fetch_assoc() ) {

	$clorder->email_pay_recieved($row_order);
	$update="update orders set email_pay_recieved=1 where id=".$row_order['id'];
	$query=_query($update,"paybank.php email_pay_recieved");
	$clorder->send_sms($row_order);
	
	
	
	//die();	
	}

?>
