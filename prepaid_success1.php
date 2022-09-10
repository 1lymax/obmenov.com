<?
// $Id: success.php,v 1.6 2006/07/31 14:11:41 asor Exp $
require_once('Connections/ma.php');
require_once('function.php');
require_once('prepaid_wm_config.php');
require_once('prepaid_wm_include.php');
$message="";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<script language="javascript" type="text/javascript">
//<![CDATA[
var cot_loc0=(window.location.protocol == "https:")? "https://secure.comodo.net/trustlogo/javascript/cot.js" :
"http://www.trustlogo.com/trustlogo/javascript/cot.js";
document.writeln('<scr' + 'ipt language="JavaScript" src="'+cot_loc0+'" type="text\/javascript">' + '<\/scr' + 'ipt>');
//]]>
</script>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: ПИН-коды</title>
<link href="wm.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="fun.js"></script>

</head>

<a href="http://www.instantssl.com" id="comodoTL">Trusted SSL Certificate</a>
<script language="JavaScript" type="text/javascript">
COT("https://obmenov.com/images/cornertrust.gif", "SC2", "none");
</script>
<body>
<?php
require_once("top_left.php")
?>


<?php

if(isset($_POST['LMI_PAYMENT_NO']) && preg_match('/^\d+$/',$_POST['LMI_PAYMENT_NO']) == 1){ # Payment ref. number
        # select payment with ref. number
        $query = "SELECT prepaid_payment.id, prepaid_payment.item, prepaid_payment.state, name, LMI_SYS_INVS_NO, 
		LMI_SYS_TRANS_NO, LMI_SYS_TRANS_DATE, RND FROM prepaid_payment, item_name, items 
		WHERE prepaid_payment.id =".(int)$_POST['LMI_PAYMENT_NO']. "  
		AND prepaid_payment.item=items.id AND items.itemid=item_name.id";


		$result = _query($query, "prepaid_success.php 1");
        $rows = mysql_num_rows($result);
        if ( $rows == 1 ) { # If payment is found, and actually paid
            $pay = mysql_fetch_array($result);
            mysql_free_result($result);
    	    if(    $_POST['LMI_SYS_INVS_NO'] == $pay['LMI_SYS_INVS_NO']
		&& $_POST['LMI_SYS_TRANS_NO'] == $pay['LMI_SYS_TRANS_NO']
		&& $_POST['LMI_SYS_TRANS_DATE'] == $pay['LMI_SYS_TRANS_DATE']
		&& $_POST['RND'] == $pay['RND'] ) {
		    # select item 
		    $query = "SELECT content, name, url, rules, number FROM items, item_name WHERE items.id=".$pay['item']." AND state='N' 
			AND items.itemid=item_name.id;";
		    $result = _query($query, "prepaid_success.php 2");
		    $rows = mysql_num_rows($result);
		    if ( $rows == 1 ) { # item found
			$item = mysql_fetch_array($result);
			mysql_free_result($result);
			# update state to "delivered" to customer
			$query = "UPDATE prepaid_payment SET state='G', timestamp=CURRENT_TIMESTAMP() WHERE id=".$pay['id'].";";
			$result = _query($query, "prepaid_success.php 3"); 
			if(mysql_affected_rows() != 1){ die("Payment table UPDATE failed!");};
		    };
		}
	}
?>
    

	<table align="center" width="450" border="0"><tr><td valign="middle" id="head_small" height="30">
          
          </td></tr>
          <tr><td width="450">

<span id="head">Вы приобрели:</span><br /><br />

<?php   echo '<img src="http://obmenov.com/images/'.$item['url'].'_logo.gif"><br />'; ?>
<b>Ваучер:</b> <? echo $item['name'] ?><br />
<b>Номер карты:</b> <? echo $item['number'] ?><br />
<b>Код пополнения:</b> <? echo $item['content'] ?></p><br />
<b>Инструкции:</b><br>
<?php echo $item['rules']; ?>

</td></tr></table>

<?php
} else if ( isset($_POST['signature']) && isset($_POST['operation_xml']) ) { // оплата liqpay
					
			function parseTag($rs, $tag) {            
   				$rs = str_replace("\n", "", str_replace("\r", "", $rs));
    			$tags = '<'.$tag.'>';
   				$tage = '</'.$tag;
   				$start = strpos($rs, $tags)+strlen($tags);
		    	$end = strpos($rs, $tage);
		    	return substr($rs, $start, ($end-$start)); 
			 }

			$resp = base64_decode($_POST['operation_xml']);

			$insig = $_POST['signature'];

			$merchant_id=$pb_merchant_id;

			$signature=$pb_signature;

$payrez = array();
$payrez['order_id'] = parseTag($resp, 'order_id');
$payrez['status'] = parseTag($resp, 'status');
$payrez['response_description'] = parseTag($resp, 'response_description');
$payrez['transaction_id'] = parseTag($resp, 'transaction_id');
$payrez['pay_details'] = parseTag($resp, 'pay_details');
$payrez['pay_way'] = parseTag($resp, 'pay_way');
$payrez['amount'] = parseTag($resp, 'amount');
$order_id = $payrez['order_id'];
$status = $payrez['status'];
$response_description = $payrez['response_description'];
$payrez['sender_phone']=parseTag($resp, 'sender_phone');
$transaction_id = $payrez['transaction_id'];
$pay_details = $payrez['pay_details'];
$pay_way = $payrez['pay_way'];

			$gensig = base64_encode(sha1($signature.$resp.$signature,1));

			if ($insig == $gensig)
			{
				if ($payrez['status'] == 'failure' )
				{
					$message="Платеж не выполнен. Вы можете пройти процедуру покупки снова";	
				}elseif($payrez['status'] == 'secure'){

					$query = "SELECT prepaid_payment.id, prepaid_payment.item, prepaid_payment.state, 
					name, LMI_SYS_INVS_NO, LMI_SYS_TRANS_NO, LMI_SYS_TRANS_DATE, RND 
					FROM prepaid_payment, item_name, items	
					WHERE prepaid_payment.id =".substr($payrez['order_id'],7,10)."
					AND prepaid_payment.item=items.id AND items.itemid=item_name.id";


					$result = _query($query, "prepaid_success.php 1");
      				$rows = mysql_num_rows($result);
       				if ( $rows == 1 ) { # If payment is found, and actually paid
    			  		$pay = mysql_fetch_array($result);
          				mysql_free_result($result);
    			    if(  $pay['transaction_id'] == $pay['transaction_id']
						&& $payrez['sender_phone'] == $pay['sender_phone']
						&& $payrez['rnd'] == $pay['RND'] ) {
		    # select item 
		   			 $query = "SELECT content, name, url, rules, number 
					 FROM items, item_name WHERE items.id=".$pay['item']." AND state='N' 
					 AND items.itemid=item_name.id;";
		 			$result = _query($query, "prepaid_success.php 2");
		 			$rows = mysql_num_rows($result);
				    if ( $rows == 1 ) { # item found
						$item = mysql_fetch_array($result);
						mysql_free_result($result);
			# update state to "delivered" to customer
						$query = "UPDATE prepaid_payment SET state='G', payway='card' timestamp=CURRENT_TIMESTAMP() WHERE id=".$pay['id'].";";
						$result = _query($query, "prepaid_success.php 3");
						$message='Платеж прошел успешно. Свою покупку Вы можете увидеть в разделе "Мои покупки"';
					}}
					}
				}elseif($status=='wait_secure'){
						$message='Идет проверка платежа. Статус платежа и данные покупки Вы можете уточнить в разделе "Мои покупки"';
				}
				//maildebugger(print_r($payrez,1));
				echo $message;
				//print_r($payrez);
			}else{

}


?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: ПИН-коды</title>
<link href="wm.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="fun.js"></script>

</head>


<body>
<?php
require_once("top_left.php");
?>

	<table align="center" width="450" border="0"><tr><td valign="middle" id="head_small" height="30">
          
          </td></tr>
          <tr><td width="450">
          Ошибка платежа. Обратитесь в <a href="contacts.php">службу поддержки</a>
          </td></tr></table>
 <?php }
 echo $message;
require_once("bottom_right.php");

?>        
