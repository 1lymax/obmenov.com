<?
// $Id: result.php,v 1.11 2006/07/31 14:11:41 asor Exp $
require_once('Connections/ma.php');
require_once('function.php');
require_once('siti/prepaid_wm_config.php');
require_once('siti/prepaid_wm_include.php');
require_once('siti/class.php');


if( isset($_POST['LMI_PREREQUEST']) && $_POST['LMI_PREREQUEST'] == 1){ # Prerequest
    if( isset($_POST['LMI_PAYMENT_NO']) 
	&& preg_match('/^\d+$/',$_POST['LMI_PAYMENT_NO']) == 1  # Payment inner id
        && isset($_POST['RND']) && preg_match('/^[a-z0-9]{8}$/',$_POST['RND'],$match) == 1){ # step 3
	# Request from database re payment with such id
	$query = "SELECT prepaid_payment.id, prepaid_payment.disc_amount2,item, price, unit FROM prepaid_payment, items, item_name WHERE ".
	"prepaid_payment.id =".$_POST['LMI_PAYMENT_NO']." AND prepaid_payment.item = items.id AND items.itemid=item_name.id
	AND prepaid_payment.state='I' AND items.state='Y' ".
	"AND RND='".$_POST['RND']."' AND((reserved IS NULL) OR (reserved + INTERVAL 4 MINUTE < NOW()));";
	$result = _query2($query, 4);
	$rows = mysql_num_rows($result);
	if ( $rows != 1 ) {
		echo "Произошла внутренняя ошибка. Попробуйте осуществить покупку позже.";
	    _error('Item not found', 5, $query);
	} else { # If no payment or items found
 		$pay = mysql_fetch_array($result);
		# перевод валюты
		$currency="WM".substr($_POST['LMI_PAYEE_PURSE'],0,1);
		if ( $pay['unit']==$currency && $pay['unit']=='WMU' ) {
			$percent_addon=1;
		}
		$unit_price = round($pay['price']*$courses[$pay['unit']][$currency]*$percent_addon-$pay['disc_amount2'],2);
		
		//if ( $pay['unit']=="WMU" && ($currency=="WMZ" || $currency=="WME" || $currency=="WMR") ){
		//	$unit_price = round($pay['price']*$courses[$pay['unit']][$currency]*$percent_addon,2);
			//maildebugger($pay['unit']. $pay['price']." ".$courses[$pay['unit']][$currency]);
		//}

	    mysql_free_result($result);
	    if( $_POST['LMI_PAYMENT_NO'] == $pay['id'] # Check if payment id, purse number and ammount correspond with each other 
             && $_POST['LMI_PAYEE_PURSE'] == $WM_SHOP_PURSE['WM'.substr(htmlspecialchars($_POST['LMI_PAYEE_PURSE']),0,1)]
             && round($_POST['LMI_PAYMENT_AMOUNT'],1) == round($unit_price,1)){ # step 5
                # reserve
		$query = "UPDATE items SET reserved=CURRENT_TIMESTAMP() WHERE id=".$pay['item'].";";
		$result = _query($query, 6);
		if(mysql_affected_rows() != 1){
		    _error('Item not reserved', 7, $query);
		} else {
		    # Update payment  as _reserved_ 
		    $query = "UPDATE prepaid_payment SET state='R', timestamp=CURRENT_TIMESTAMP() WHERE id=".$pay['id'].";";
		    $result = _query($query, 8);
		    if(mysql_affected_rows() != 1){
			_error('Payment not updated', 9);
                    } else {	
			echo 'YES'; # if everything is ok and items are reserved,  give ok to transaction
		    };
		};
     } else { # step 5
        //_error('Inconsistent parameters price='.$unit_price, 5, '');
				maildebugger("Prepaid step=5 ".round($_POST['LMI_PAYMENT_AMOUNT'],1)." ".round($unit_price,1)." ".print_r($pay,1)." ".$pay['unit']." ".$currency." ".print_r($courses,1)." ".$courses[$pay['unit']][$currency]);
     };
}
} else { # step 3
    _error('Inconsistent parameters', 3, '');
}
}
else{ #  Payment notification
    if( isset($_POST['LMI_PAYMENT_NO']) # Check payment id
	&&  preg_match('/^\d+$/',$_POST['LMI_PAYMENT_NO']) == 1 
	&& isset($_POST['RND']) && preg_match('/^[a-z0-9]{8}$/',$_POST['RND'],$match) == 1){ # Check ticket, step 11
	# Query form database about payment with such id
	$query = "SELECT prepaid_payment.id, prepaid_payment.item, items.number, items.content, prepaid_payment.disc_amount2,
					price, unit, email, name FROM prepaid_payment, item_name, items WHERE ".
	"prepaid_payment.id =".$_POST['LMI_PAYMENT_NO']." AND prepaid_payment.item = items.id AND items.itemid = item_name.id AND 
	prepaid_payment.state='R' AND items.state='Y' ".
	"AND RND = '".$_POST['RND']."' AND ((reserved IS NULL) OR (reserved + INTERVAL 4 MINUTE > NOW()));";
	$result = _query2($query, 12);
	$rows = mysql_num_rows($result);
	if ( $rows != 1 ) {
	        _error('Payment not found', 13, $query);
	    } else { # If payment or items were not found,
	    $pay = mysql_fetch_array($result);
		
		# перевод валюты
		
		
		$currency="WM".substr($_POST['LMI_PAYEE_PURSE'],0,1);
		$shop=new shop();
		//$unit_price=$shop->get_price($pay['unit'],$pay['price']);
		//$unit_price=$unit_price[$currency];
		if ( $pay['unit']==$currency && $pay['unit']=='WMU' ) {
			$percent_addon=1;
		}
		$unit_price = $pay['price']*$courses[$pay['unit']][$currency]*$percent_addon-$pay['disc_amount2'];
		
		/*$unit_price = round($pay['price']*$courses[$currency][$pay['unit']],2);
		
		if ( $pay['unit']=="WMU" && ($currency=="WMZ" || $currency=="WMR" || $currency=="WME" ) ){
			$unit_price = round($pay['price']*$courses[$pay['unit']][$currency]*$percent_addon,2);	
		}*/
	
	    mysql_free_result($result);
	    # Create check string
			$unit_price=trim(sprintf ("%9.2f",$unit_price));
    	    $chkstring =  strtoupper(md5($WM_SHOP_PURSE['WM'.substr($_POST['LMI_PAYEE_PURSE'],0,1)].($unit_price-0.01).$pay['id'].
		    $_POST['LMI_MODE'].$_POST['LMI_SYS_INVS_NO'].$_POST['LMI_SYS_TRANS_NO'].$_POST['LMI_SYS_TRANS_DATE'].
	            $LMI_SECRET_KEY.$_POST['LMI_PAYER_PURSE'].$_POST['LMI_PAYER_WM']));
			$chkstring1 =  strtoupper(md5($WM_SHOP_PURSE['WM'.substr($_POST['LMI_PAYEE_PURSE'],0,1)].$unit_price.$pay['id'].
		    $_POST['LMI_MODE'].$_POST['LMI_SYS_INVS_NO'].$_POST['LMI_SYS_TRANS_NO'].$_POST['LMI_SYS_TRANS_DATE'].
	            $LMI_SECRET_KEY.$_POST['LMI_PAYER_PURSE'].$_POST['LMI_PAYER_WM']));
			$chkstring2 =  strtoupper(md5($WM_SHOP_PURSE['WM'.substr($_POST['LMI_PAYEE_PURSE'],0,1)].($unit_price+0.01).$pay['id'].
		    $_POST['LMI_MODE'].$_POST['LMI_SYS_INVS_NO'].$_POST['LMI_SYS_TRANS_NO'].$_POST['LMI_SYS_TRANS_DATE'].
	            $LMI_SECRET_KEY.$_POST['LMI_PAYER_PURSE'].$_POST['LMI_PAYER_WM']));
				
	    if ( $LMI_HASH_METHOD == 'MD5' ) {
	    	$md5sum = strtoupper(md5($chkstring));
		$hash_check = ($_POST['LMI_HASH'] == $chkstring);
		$hash_check1 = ($_POST['LMI_HASH'] == $chkstring1);
		$hash_check2 = ($_POST['LMI_HASH'] == $chkstring2);
		
	    } else {
		_error('Config parameter LMI_HASH_METHOD incorrect!', 14, $chkstring);
	    };

	    
		if(    $_POST['LMI_PAYMENT_NO'] == $pay['id'] # Check if payment id, purse number and amount correspond
		&& $_POST['LMI_PAYEE_PURSE'] == $WM_SHOP_PURSE['WM'.substr($_POST['LMI_PAYEE_PURSE'],0,1)]
		&& round($_POST['LMI_PAYMENT_AMOUNT'],1) == round($unit_price,1)
		&& $_POST['LMI_MODE'] == $LMI_MODE
		&& ( $hash_check || $hash_check1 || $hash_check2 ) ) {  # checksum is correct, step 15
		    # if everything is ok, payment receives status: Paid, item receives status: Sold,
		    # enter payment and customer data into database
			$client_query="SELECT clients.id FROM clients WHERE clid='".$_POST['clid']."'";
			$client_query=_query($client_query, "prepaid_result.php 20");
			$client_query = mysql_fetch_assoc($client_query);
		    $query = "UPDATE prepaid_payment SET state='S', timestamp=CURRENT_TIMESTAMP(), ".
			     "LMI_SYS_INVS_NO='".$_POST['LMI_SYS_INVS_NO']."', ".
			     "LMI_SYS_TRANS_NO='".$_POST['LMI_SYS_TRANS_NO']."', ".
			     "LMI_SYS_TRANS_DATE='".$_POST['LMI_SYS_TRANS_DATE']."', ".
			     "LMI_PAYER_PURSE='".$_POST['LMI_PAYER_PURSE']."', ".			     
			     "LMI_PAYER_WM='".$_POST['LMI_PAYER_WM']."', ".
				 "LMI_PAYMENT_AMOUNT='".$_POST['LMI_PAYMENT_AMOUNT']."', ".
				 "email='".(isset($_POST['email']) ? $_POST['email'] : "")."', ".
				 "payway='wm' WHERE id=".$pay['id'].";";
		    $result = _query($query, 16);
		    if(mysql_affected_rows() == 1){
			$query = 'UPDATE items SET state="N" WHERE id='.$pay['item'].';';
			$result = _query($query, 17);
			
			
			# send customer a link to receive purchased items
			$ouremail = $shop_email;
			$url = 'http://obmenov.com'.
			substr($_SERVER['REQUEST_URI'],0,strlen($_SERVER['REQUEST_URI'])-strlen('prepaid_result.php')).
			'giveout.php'.
			'?wmid='.$_POST['LMI_PAYER_WM'].'&id='.$_POST['LMI_PAYMENT_NO'].'&rnd='.$_POST['RND'];
			$message = "\nВы купили : <".$pay['name'].">.\n".
			"Код ваучера: ".$pay['number'].".\n".
			"Секретный код: ".$pay['content'].".\n";
			$addheader ="From: $ouremail\r\nReply-To: $ouremail\r\nContent-Type: text/plain; charset='windows-1251'"; 
			if (isset($_POST['email']))mail($_POST['email'], 'Покупка в магазине Обменов.ком', $message, $addheader);
			require_once("siti/_header.php");
			$response = $wmxi->X6(
				htmlspecialchars($_POST['LMI_PAYER_WM']),                            
				"Покупка в магазине Обменов.ком",                           
				trim("\nПокупка в магазине Обменов.ком.\n".$message."\n_______________________________________________________________\nДанное сообщение сформировано автоматически и не требует ответа"));
				$structure = $parser->Parse($response, DOC_ENCODING);
				$transformed = $parser->Reindex($structure, true);
				if ( @$transformed["w3s.response"]["retval"]!=0 ){
					_error("Ошибка интерфейса Х6: Код: ".@$transformed["w3s.response"]["retval"].
							" Описание: ".@$transformed["w3s.response"]["retdesc"] , "prepaid_result.php X6", "");	
				 }
			
					
		    }
	    } else { # step 15
		_error("Inconsistent parameters ". print_r($_POST,1)." ".$md5sum." ".$_POST['LMI_PAYMENT_NO']." ".$pay['id']." ".$_POST['LMI_PAYEE_PURSE']." ".$WM_SHOP_PURSE['WM'.substr($_POST['LMI_PAYEE_PURSE'],0,1)]." ".$_POST['LMI_PAYMENT_AMOUNT']." ".$unit_price." ".$_POST['LMI_MODE']." ".$LMI_MODE." ".$hash_check. "checkstring=".$chkstring, 15, "");
	    };
	}
    } //else { # step 11			_error('Inconsistent parameters', 11, "");		}
}


if ( isset($_POST['signature']) ) {
require_once("siti/_header.php");			
function parseTag($rs, $tag) {            
   $rs = str_replace("\n", "", str_replace("\r", "", $rs));
   $tags = '<'.$tag.'>';
   $tage = '</'.$tag;
   $start = strpos($rs, $tags)+strlen($tags);
   $end = strpos($rs, $tage);
   return substr($rs, $start, ($end-$start)); 
 }
			maildebugger($_POST['signature']." operation = ".$_POST['operation_xml']);
			$resp = base64_decode($_POST['operation_xml']);
			echo $resp;
$payrez = array();
$payrez['order_id'] = parseTag($resp, 'order_id');
$payrez['status'] = parseTag($resp, 'status');
//$payrez['response_description'] = parseTag($resp, 'request');
$payrez['transaction_id'] = parseTag($resp, 'transaction_id');
$payrez['pay_details'] = parseTag($resp, 'pay_details');
$payrez['pay_way'] = parseTag($resp, 'pay_way');
$payrez['amount'] = parseTag($resp, 'amount');
$order_id = $payrez['order_id'];
$status = $payrez['status'];
$payrez['sender_phone']=parseTag($resp, 'sender_phone');
$transaction_id = $payrez['transaction_id'];
$pay_details = $payrez['pay_details'];
$pay_way = $payrez['pay_way'];

			$insig = $_POST['signature'];
			//print_r($transformed);
			$merchant_id=$pb_merchant_id;

			$signature=$pb_signature;

			$gensig = base64_encode(sha1($signature.$resp.$signature,1));

			if ($insig == $gensig)
			{	//echo "Signuture ok<br />";
				//echo $payrez['status'];
				if ($payrez['status'] == 'failure' )
				{
					
				}elseif($status == 'success'){
					print_r($payrez)."<br />";
					$query = "SELECT prepaid_payment.id, item, price, unit FROM prepaid_payment, items, item_name WHERE ".
					"prepaid_payment.id =".substr($payrez['order_id'],7,10)." AND prepaid_payment.item = items.id 
					AND items.itemid=item_name.id
					AND prepaid_payment.state='I'  ".
					"AND RND='".$_POST['rnd']."'";//AND items.state='Y' AND((reserved IS NULL) OR (reserved + INTERVAL 2 MINUTE < NOW()));";
					//echo $query;
					$result = _query2($query, 4);
					$rows = mysql_num_rows($result);
					if ( $rows != 1 ) {
				   		_error('Item not found', 5, $query);
					} else { # If no payment or items found
 						$pay = mysql_fetch_array($result);
					}
					$query = "UPDATE prepaid_payment SET state='S', timestamp=CURRENT_TIMESTAMP(), ".
			    	 "LMI_SYS_TRANS_NO='".$payrez['transaction_id']."', ".
					 "LMI_PAYER_PURSE='".$payrez['sender_phone']."', ".
					 "LMI_PAYMENT_AMOUNT='".$payrez['amount']."', ".
					 "email='".$_POST['email']."', ".
					 "payway='".$payrez['pay_way']."' WHERE id=".$pay['id'].";";
					 echo "<br />".$query;
		 		   $result = _query($query, 16);
		 		   if(mysql_affected_rows() == 1){
					$query = 'UPDATE items SET state="N" WHERE id='.$pay['item'].';';
					$result = _query($query, 17);
					$ouremail = $shop_email;
					$message = "\nВы купили : <".$pay['name'].">.\n".
					"Код ваучера: ".$pay['number'].".\n".
					"Секретный код: ".$pay['content'].".\n";
					$addheader ="From: $ouremail\r\nReply-To: $ouremail\r\nContent-Type: text/plain; charset='windows-1251'"; 
					mail($_POST['email'], 'Покупка в магазине Обменов.ком', $message, $addheader);
				   }
				}elseif($status=='wait_secure'){
					$query = "SELECT prepaid_payment.id, item, price, unit FROM prepaid_payment, items, item_name WHERE ".
					"prepaid_payment.id =".substr($_POST['order_id'],7,10)." AND prepaid_payment.item = items.id 
					AND items.itemid=item_name.id
					AND prepaid_payment.state='I' AND items.state='Y' ".
					"AND RND='".$_POST['RND']."' AND((reserved IS NULL) OR (reserved + INTERVAL 2 MINUTE < NOW()));";
					$result = _query2($query, 4);
					$rows = mysql_num_rows($result);
					if ( $rows != 1 ) {
				   		_error('Item not found', 5, $query);
					} else { # If no payment or items found
 						$pay = mysql_fetch_array($result);
					}
					$query = "UPDATE prepaid_payment SET state='S', payway='wait_secure', timestamp=CURRENT_TIMESTAMP(), ".
			    	 "LMI_SYS_TRANS_NO='".$_POST['transaction_id']."', ".
					 "LMI_PAYER_PURSE='".$payrez['sender_phone']."', ".
					 "LMI_PAYMENT_AMOUNT='".$_POST['amount']."', ".
					 "email='".$_POST['email']."', ".
					 "payway='".$_POST['pay_way']."' WHERE id=".$pay['id'].";";
		 		   $result = _query($query, 16);

				}
				maildebugger(print_r($payrez,1));
				//print_r($payrez);
			}else{

			}
			
}
?>
