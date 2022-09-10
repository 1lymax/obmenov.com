<?php 
$dont_insert_client=1;

require_once('/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php');
_query('SET character_set_database = cp1251',"");
_query('SET NAMES cp1251',"");
@ini_set ("display_errors", true);
//require_once('/var/www/webmoney_ma/data/www/obmenov.com/function.php');
require_once('/var/www/webmoney_ma/data/www/obmenov.com/siti/_header.php');
require_once('/var/www/webmoney_ma/data/www/obmenov.com/siti/p24api.php');
require_once('/var/www/webmoney_ma/data/www/obmenov.com/siti/class.php');


$select="SELECT orders.id AS orderid, orders.summin, orders.currin, orders.summout, orders.currout, payment.LMI_PAYER_WM,
					orders.time, orders.id as id, payment.LMI_SYS_TRANS_NO 
					FROM orders, payment 
					WHERE orders.id = payment.orderid AND orders.ordered =1
					
					AND ( left(orders.currin,3) = 'MCV' ) 
					AND ( orders.time + INTERVAL 15 day > NOW( ) ) AND payment.ordered=0 and payment.canceled =0";
$query1=_query2($select, "siti/paybank.php 1");//AND orders.phone=payment.LMI_PAYER_PURSE   and LMI_PAYER_WM!='failure'


//echo mysql_num_rows($query1);
while ( $pays=$query1->fetch_assoc() ) {
	require_once($GLOBALS['serverroot']."siti/lp.class.php");
	$liqpay= new liqpay($GLOBALS['lq_id']);
	$response=$liqpay->check_trans($pays,1);
	libxml_use_internal_errors(true);
	if($response===false)die();
	if(!$response)die();
	maildebugger($response);
	$payrez = array();
	//if ( !isset($response->code) )continue;
	$payrez['code'] = $response->code;
	$payrez['status'] = $response->status;
	$payrez['response_description'] = $response->response_description;
	$payrez['transaction_id'] = $response->transaction_id;
	//$payrez['pay_details'] = parseTag($resp, 'pay_details');
	//$payrez['pay_way'] = parseTag($resp, 'pay_way');
	//$payrez['amount'] = parseTag($resp, 'amount');
	$payrez['order_id']=$response->transaction_order_id;
	//maildebugger($payrez);
	$status = isset($response->transaction->status) ? $response->transaction->status : 'failure';
	$row="select * from payment where orderid=".$payrez['order_id'];
	$row=_query($row,"cron_mcv.php 18");
	echo $status." - ".$response->transaction_order_id;
	if ( $row->num_rows==0 ) {
		$select="insert into payment (orderid, LMI_PAYER_WM) VALUES ('".$response->transaction_order_id."',
							'".$status."')";
					$query=_query($select, "cron_mcv.php 19");	
	}
	//$query=_query($select, "cron_mcv.php3");
	
	if ($status == 'failure' )
	{
		$payrez['ordered']=0;	
	}elseif($status == 'success' or $status == 'secure_success' ){
		$select="update payment set ordered=1, LMI_PAYER_WM='".$status."' where orderid=".$response->transaction_order_id;
		$query=_query($select, "cron_mcv.php 19");	
		$select="select canceled from payment where orderid=".$response->transaction_order_id;
		$query=_query($select, "cron_mcv.php 9");
		$row_ordered=$query->fetch_assoc();
		//maildebugger($resp." cron_mcv");
		if ( $row_ordered['canceled']==1 ) { continue; }
	
		$order = "SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.summout, orders.clid,
	  			orders.discammount, orders.partnerid, orders.attach, orders.email, orders.retid,
				(select type from currency where name=orders.currin) as currintype, 
				(select type from currency where name=orders.currout) as currouttype,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e, orders.needcheck	
				FROM orders WHERE orders.id=".$payrez['order_id'];
		$row_order=_query2($order, 17);
		$row_order=$row_order->fetch_assoc();
		
		if  ( substr($row_order['currout'],0,2)=="WM" ) {
			$order = new orders();
			$order->pay_order($row_order);
			$order->partner_bonus($row_order);
			//$order->email_pay_recieved($row_order);
			$order->send_sms($row_order);
		}
				
		$payrez['ordered']=1;
		
	}elseif($status=='wait_secure'){
		$payrez['ordered']=0;
		$select="update payment set timestamp=CURRENT_TIMESTAMP() where orderid=".$payrez['order_id'];
		$query=_query($select, "cron_mcv.php 4");
	}
	if ( $status!="") {
		$select="update payment set LMI_PAYER_WM='".$status."',
							status='',
							ordered=".$payrez['ordered']."
							WHERE orderid=".$payrez['order_id']." AND 
							LMI_SYS_TRANS_NO='".$payrez['transaction_id']."'";
		$query=_query($select, "cron_mcv.php 4");
	}
}
	$select="select orders.id as id, orders.summin FROM orders, payment 
					WHERE orders.id = payment.orderid AND orders.ordered =1
					AND orders.droped!=1
					AND ( left(orders.currin,2) = 'LR' ) 
					AND ( orders.time + INTERVAL 600 MINUTE > NOW( ) ) AND payment.canceled =0";
	$query=_query2($select, "cronmcv.php");
	//echo mysql_num_rows($query);
	if ( $query->num_rows!=0) {
		require_once($GLOBALS['serverroot']."siti/lib.lr.php");
		$lr=new lrWorker($GLOBALS['lr_id']);
	}
	while ( $row=$query->fetch_assoc() ) {
		$order=new orders();
		$hist=$order->lr_pay_exist($row['id']);
		if ( isset($hist['RECEIPT']) ) {
			if ( isset($hist['PAGER']['PAGENUMBER']) && $hist['PAGER']['PAGENUMBER']==0 
							&& $hist['RECEIPT'][0]['AMOUNT']==$row['summin']
							&& (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']==$GLOBALS['lr_merchant_ip'] : 1 ) ) {
				echo "good";
				print_r($hist);
				$query1="update payment set LMI_SYS_TRANS_NO='".$hist['RECEIPT']['0']['TRANSFERID']."',
							LMI_PAYER_WM='".mysql_real_escape_string($hist['RECEIPT']['0']['PAYERNAME'])."',
							LMI_PAYER_PURSE='".$hist['RECEIPT']['0']['PAYER']."',
							status='success',
							ordered=1
							WHERE orderid='".$row['id']."'";
			$result=_query($query1, "mcvresult.php 1");
			
			}else{
				echo "badlog";
				print_r($hist);
				$lr->insert_badlog($row['id']);

			}
		}
		
	}
	
	
	//print_r(*/
?>