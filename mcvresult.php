<?php 
//$dont_insert_client=1;
require_once('Connections/ma.php');
//@ini_set ("display_errors", true);
//require_once($serverroot.'function.php');
//require_once($serverroot.'siti/_header.php');
require_once($serverroot.'siti/class.php');

/*if ( $_SERVER['HTTP_REFERER']!=get_setting("privat24_merchant_referer") {
	if ( $_SERVER['REMOTE_ADDR']!=get_setting("liqpay_merchant_ip") {
		_error("mcvresult bad ip","","");
		
	}
}*/
$resp="";
$insig=" ";
 
if ( isset($_POST['operation_xml']) && isset($_POST['signature']) ) {
	
	$resp = base64_decode($_POST['operation_xml']);
	$insig = $_POST['signature'];
	$response=simplexml_load_string($resp);
	//print_r($response);
	$merchant_id=$lq_merchant_id;
	$signature=$lq_signature;

	$resp_status=isset($response->status) ? $response->status : "";
	$resp_descr=isset($response->response_description) ? $response->response_description : "";
	
	$order = "SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.summout, orders.clid, orders.phone,
	  			orders.discammount, orders.partnerid, orders.attach, orders.email, orders.retid, orders.wmid,
				(select type from currency where name=orders.currin) as currintype,
				(select type from currency where name=orders.currout) as currouttype,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e	
				FROM orders WHERE orders.id=".$response->order_id;
				
	$row_order=_query2($order, 17);
	$row_order=$row_order->fetch_assoc();
	//header("Location: cabinet.php?clid=".$row_order['clid']."&oid=".$row_order['id']);

	$gensig = base64_encode(sha1($signature.$resp.$signature,1));
	if ($insig == $gensig)
	{
		//maildebugger($resp);
		$ordered=0;
		$canceled=0;
		if ($resp_status == 'success' || $resp_status == 'secure_success' )
		{
			$ordered=1;
			$canceled=0;
			//$select="update payment set canceled=1 where orderid=".$response->order_id;
			//$update=_query($select, "mcvresult.php 1");
			$select="select clid, id from orders where id=".$response->order_id;
			$query=_query($select, "mcvresult.php 2");
			$row_order=$query->fetch_assoc();

		}elseif ($resp_status == 'wait_secure' ) {
			$select="select clid, id from orders where id=".$response->order_id;
			$query=_query($select, "mcvresult.php 2");
			$row_order=$query->fetch_assoc();
			if ( isset($row_order['email']) ) {
				send_mail($row_order['email'], 'Уважаемый клиент!
Уведомляем вас об изменении статуса вашей заявки №: '.$row_order['id'].'
Ваша заявка получила статут "Верификация платежной карты".

Постоянный адрес с информацией о вашей заявке:
https://obmenov.com/cabinet.php?clid='.$row_order['clid'].'&oid='.$row_order['id'].'
---
С уважением, Обменов.ком', "Обменов.ком :: изменен статус заявки № ".$row_order['id'],$shop_email , $shop_name) ;
			}
		}elseif ($resp_status == 'failure' ) {

		}
		$select="update payment set LMI_SYS_TRANS_NO='".$response->transaction_id."',
							LMI_PAYER_WM='".$resp_status."',
							LMI_PAYER_PURSE='".str_replace("+","",$response->sender_phone)."',
							status='".$resp_descr."',
							ordered=".$ordered.",
							canceled=".$canceled."
							WHERE orderid='".$response->order_id."'";
		$update=_query($select, "mcvresult.php 1");
		header("Location: cabinet.php?clid=".$row_order['clid']."&oid=".$row_order['id']."&message=".$resp_status);
		die();
	
	
	}else{
	
	

	}


						

}elseif ( isset($_POST['payment']) && isset($_POST['signature']) ) { // переход от приват24
	if ( $_SERVER['REMOTE_ADDR']!=get_setting("privat24_merchant_referer") ) {
		$update="update orders set status=' invalid IP ".$_SERVER['REMOTE_ADDR']."=".get_setting("privat24_merchant_referer")."' where id=".$row_order['id'];
		$query=_query2($update, "result.php 45");
		_error("mcvresult bad ip privat24","","");
	}
	$merchant_num=0;
	if ( isset($_GET['oid']) ) {
		$select="select currin from orders where id=".(int)$_GET['oid'];
		$query=_query($select, "mcvresult.php 12");
		if ( $query->num_rows==1 ) {
			$row_order=$query->fetch_assoc();
			if ( $row_order['currin']=="P24UAH" ) {
				$merchant=$pb_uah_merchant_p;
				$merchant_num=$pb_uah_merchant;
				//$merchant=$pb_uah_merchant_p;
				//$merchant_num=$pb_uah_merchant;
			}elseif ( $row_order['currin']=="P24USD" ) {
				$merchant=$pb_usd_merchant_p;
				$merchant_num=$pb_usd_merchant;
			}
		}
	}
	if ( $merchant_num!=0 ) {

		$signature = sha1(md5($_POST['payment'].$merchant));
		
		if ( $signature==$_POST['signature'] ) {
			parse_str($_POST['payment'], $payrez);
			
			maildebugger(print_r($payrez,1));
			$select="select canceled from payment where orderid=".$payrez['order'];
			$query=_query($select, "cron_mcv.php 9");
			$row_ordered=$query->fetch_assoc();

			$order = "SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.summout, orders.clid,
	  			orders.discammount, orders.partnerid, orders.attach, orders.email, orders.wmid,  orders.fname, orders.iname, orders.account,
				(select type from currency where name=orders.currin) as currintype, orders.retid, orders.phone,
				(select type from currency where name=orders.currout) as currouttype,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e, orders.needcheck
				FROM orders WHERE orders.id=".$payrez['order'];
			$row_order=_query2($order, 17);
			$row_order=$row_order->fetch_assoc();
			$select="select ordered, canceled from payment where orderid=".$payrez['order'];
			//maildebugger(print_r($payrez));
			$query=_query($select,"mcvresult 13");
			$row_payment=$query->fetch_assoc();
			if ( $payrez['order']==$_GET['oid'] && $payrez['amt']==$row_order['summin']  ) {
			}else{
				$update="update orders set status='Изменена сумма сумма1=".$payrez['amt'].", сумма2=".$row_order['summin']. "' where id=".
																			$payrez['order'];
				$query=_query2($update, "result.php 45");
				_error("mcvresult bad summ or ordernum",$update,"");
				die();
			}
			
			if ( $row_payment['canceled']==1 ) { 
				//echo " "; die();
				//header("Location: cabinet.php?clid=".$row_order['clid']."&oid=".$row_order['id']);echo " ";die();
			}
			
			if ( $payrez['state']=="ok" && $row_payment['canceled']!=1 ) {
					$update="UPDATE payment SET payment.ordered=1,
						LMI_SYS_TRANS_NO='".$payrez['ref']."' where orderid=".$payrez['order'];
					$updateSQL=_query($update, "mcvresult.php 25");
					if ( $row_order['needcheck']==0 ) {
						$order = new orders();
						$order->pay_order($row_order);
						$order->partner_bonus($row_order);
						//$order->email_pay_recieved($row_order);
						
						$order->send_sms($row_order);
						
					}
			echo " ";
			}elseif ( $payrez['state']=="fail" ) {
				echo " ";
			}
			header("Location: cabinet.php?clid=".$row_order['clid']."&oid=".$row_order['id']);
			die();
		}
		
	}


}elseif ( isset($_POST['PAYMENT_ID']) && isset($_POST['PAYEE_ACCOUNT']) && isset($_POST['PAYMENT_AMOUNT']) ) { // Perfectmoney
	if ( $_SERVER['REMOTE_ADDR']!="77.109.141.170" ) {
		$update="update orders set status=' invalid IP ".$_SERVER['REMOTE_ADDR']."=".get_setting("privat24_merchant_referer")."' where id=".$row_order['id'];
		$query=_query2($update, "result.php 45");
		_error("mcvresult bad ip privat24","","");
	}
	/* Constant below contains md5-hashed alternate passhrase in upper case.
   You can generate it like this:
   strtoupper(md5('your_passphrase'));
   Where `your_passphrase' is Alternate Passphrase you entered
   in your PerfectMoney account. */
define('ALTERNATE_PHRASE_HASH',  '1092D7A32CDB31F4F1FC32B74FE50DBC');

// Path to directory to save logs. Make sure it has write permissions.
define('PATH_TO_LOG',  $serverroot.'siti/');

$string=
      $_POST['PAYMENT_ID'].':'.$_POST['PAYEE_ACCOUNT'].':'.
      $_POST['PAYMENT_AMOUNT'].':'.$_POST['PAYMENT_UNITS'].':'.
      $_POST['PAYMENT_BATCH_NUM'].':'.
      $_POST['PAYER_ACCOUNT'].':'.'72EAF6C0B16FF402C6ADA4358D5197F2'.':'.
      $_POST['TIMESTAMPGMT'];

$hash=strtoupper(md5($string));

if($hash==$_POST['V2_HASH']){ // proccessing payment if only hash is valid

   /* In section below you must implement comparing of data you recieved
   with data you sent. This means to check if $_POST['PAYMENT_AMOUNT'] is
   particular amount you billed to client and so on. */
   $order = "SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.summout, orders.clid,
	  			orders.discammount, orders.partnerid, orders.attach, orders.email, orders.purse_other, 
				(select type from currency where name=orders.currin) as currintype, orders.retid, orders.phone,
				(select type from currency where name=orders.currout) as currouttype,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e, orders.needcheck
				FROM orders WHERE orders.id=".$_POST['PAYMENT_ID'];
	$row_order=_query2($order, 17);
	$row_order=$row_order->fetch_assoc();
	$select="select ordered, canceled from payment where orderid=".$_POST['PAYMENT_ID'];
	$query=_query($select,"mcvresult 13");
	$row_payment=$query->fetch_assoc();

   if($_POST['PAYMENT_AMOUNT']==$row_order['summin'] && $_POST['PAYEE_ACCOUNT']==$GLOBALS['pm_'.strtolower(substr($row_order['currin'],2,3))] 
			&& $_POST['PAYMENT_UNITS']==substr($row_order['currin'],2,3) && $_SERVER['REMOTE_ADDR']==$GLOBALS['pm_ip']){

		$select="update payment set LMI_SYS_TRANS_NO='".$_POST['PAYMENT_BATCH_NUM']."',
							LMI_PAYER_WM='',
							LMI_PAYER_PURSE='".$_POST['PAYER_ACCOUNT']."',
							status='success',
							ordered=1,
							canceled=0
							WHERE orderid='".$_POST['PAYMENT_ID']."'";
		$update=_query($select, "mcvresult.php 1");
		//header("Location: cabinet.php?clid=".$row_order['clid']."&oid=".$row_order['id']."&message=success");

      // uncomment code below if you want to log successfull payments
      /* $f=fopen(PATH_TO_LOG."good.log", "ab+");
      fwrite($f, date("d.m.Y H:i")."; POST: ".serialize($_POST)."; STRING: $string; HASH: $hash\n");
      fclose($f); */

   }else{ // you can also save invalid payments for debug purposes

       $query="insert into badlog (type,ip,data) values ('fake data','".$_SERVER['REMOTE_ADDR']."','".serialize($_POST).";
STRING: $string; HASH: $hash')";
       $query=_query($query,"");
	   $select="update payment set LMI_SYS_TRANS_NO='".$_POST['PAYMENT_BATCH_NUM']."',
							LMI_PAYER_WM='fraud entry #".mysql_insert_id()."',
							LMI_PAYER_PURSE='".$_POST['PAYER_ACCOUNT']."',
							status='fake data',
							ordered=0,
							canceled=0
							WHERE orderid='".$_POST['PAYMENT_ID']."'";
		$update=_query($select, "mcvresult.php 1");

   }
}else{ // you can also save invalid payments for debug purposes

   $query="insert into badlog (type,ip,data) values ('bad hash','".$_SERVER['REMOTE_ADDR']."','".serialize($_POST)."; 
												STRING: $string; HASH: $hash')";
      $query=_query($query,"");
	  $select="update payment set LMI_SYS_TRANS_NO='".$_POST['PAYMENT_BATCH_NUM']."',
							LMI_PAYER_WM='fraud entry #".mysql_insert_id()."',
							LMI_PAYER_PURSE='".$_POST['PAYER_ACCOUNT']."',
							status='bad hash',
							ordered=0,
							canceled=0
							WHERE orderid='".$_POST['PAYMENT_ID']."'";
	   $update=_query($select, "mcvresult.php 1");
}
	
}elseif ( isset($_REQUEST["lr_paidto"]) && isset($_REQUEST["lr_store"]) && isset($_REQUEST["lr_encrypted"]) ) { // Liberty processing
	require_once($serverroot."siti/lib.lr.php");
	$lr=new lrWorker($GLOBALS['lr_id']);
	$string = 
  		$_REQUEST["lr_paidto"].":".
  		$_REQUEST["lr_paidby"].":".
 		 stripslashes($GLOBALS['lr_store']).":".
  		$_REQUEST["lr_amnt"].":".
  		$_REQUEST["lr_transfer"].":".
  		$_REQUEST["lr_currency"].":".
  		$GLOBALS['lr_sci'];
		$hash = strtoupper(hash('sha256', $string));
	$order = "SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.summout, orders.clid,
	  			orders.discammount, orders.partnerid, orders.attach, orders.email, orders.purse_other, 
				(select type from currency where name=orders.currin) as currintype, orders.retid, orders.phone,
				(select type from currency where name=orders.currout) as currouttype,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e, orders.needcheck
				FROM orders WHERE orders.id=".$_REQUEST['lr_merchant_ref'];
	$row_order=_query2($order, 17);
	$row_order=$row_order->fetch_assoc();
	$order=new orders();
	if ( $_REQUEST["lr_paidto"] == strtoupper($GLOBALS['lr_acc']) && stripslashes($_REQUEST["lr_store"]) == $GLOBALS['lr_store'] &&   
   					$_REQUEST["lr_encrypted"] == $hash && $row_order['summin']==$_REQUEST["lr_amnt"] 
					) {
		
		$hist=$order->lr_pay_exist($_REQUEST['lr_merchant_ref']);
		if ( isset($hist['RECEIPT']) ) {
			if (  isset($hist['PAGER']['PAGENUMBER']) && $hist['PAGER']['PAGENUMBER']==0 ) {
				$query="update payment set LMI_SYS_TRANS_NO='".$_REQUEST['lr_transfer']."',
							LMI_PAYER_WM='',
							LMI_PAYER_PURSE='".$_REQUEST['lr_paidby']."',
							status='success',
							ordered=1,
							canceled=0
							WHERE orderid='".$_REQUEST['lr_merchant_ref']."'";
				$result=_query($query, "mcvresult.php 1");
			}else{
				
				$lr->insert_badlog($_REQUEST['lr_merchant_ref'], $_REQUEST['lr_transfer'], $_REQUEST['lr_paidby']);
			}
		}
			  
	}else{
		$lr->insert_badlog($_REQUEST['lr_merchant_ref'], $_REQUEST['lr_transfer'], $_REQUEST['lr_paidby']);
	}

// Let's get all the data sent by LR and add it to our email.

}


?>
