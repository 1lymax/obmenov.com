<?php 
require_once('Connections/ma.php');

require_once($serverroot."function.php");
require_once($serverroot."siti/class.php");



if( isset($_POST['LMI_PREREQUEST']) && $_POST['LMI_PREREQUEST'] == 1){ # Prerequest
    if( isset($_POST['LMI_PAYMENT_NO']) 
	&& preg_match('/^\d+$/',$_POST['LMI_PAYMENT_NO']) == 1 && isset($_POST['oid'])) { # Payment inner id
    //    && isset($_POST['RND']) && preg_match('/^[A-Z0-9]{8}$/',$_POST['RND'],$match) == 1){ # step 3
	# Request from database re payment with such id
	$query = "SELECT payment.id FROM payment WHERE ".
	"payment.id =".$_POST['LMI_PAYMENT_NO']." AND payment.orderid = ".$_POST['oid'];
	$result = _query($query, 4);
	$rows = $result->num_rows;
	$order = "SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.summout, orders.clid,
	  			orders.discammount, orders.wmid, orders.descr, orders.retid,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e	
				FROM orders WHERE orders.id=".$_POST['oid']." AND (
				time + INTERVAL 25
				MINUTE > NOW())";
	$row_order=_query2($order, 17);
	$order_numrows=$row_order->num_rows;
	$row_order=$row_order->fetch_assoc();
		if ( $order_numrows != 1 ) {
		echo 'Превышено время для оформления заказа. Начните процедуру сначала.';
	    _error('time exceeded or order not found', 5, '');
		}
	if ( $rows != 1 ) {
	    _error('Item not found', 5, '');
	} else { # If no payment or items found
	    $pay = $result->fetch_array();
	    if( $_POST['LMI_PAYMENT_NO'] == $pay['id'] # Check if payment id, purse number and ammount correspond with each other 
             && $_POST['LMI_PAYEE_PURSE'] == $shop_wm_purse[substr($row_order['currin'],2,1)]
             && $_POST['LMI_PAYMENT_AMOUNT'] == $row_order['summin']
			 //&& $_POST['LMI_PAYER_WM'] == $row_order['wmid']
			 //&& $_POST['LMI_PAYER_PURSE'] == $_POST['purseType'].trim(strtr($_POST['Purse'], "URZEurze", "        "))
			 )
			 { # step 5
			 	if ( $_POST['LMI_PAYER_WM'] != $row_order['wmid'] && strlen($row_order['wmid'])!=0 ) {
					$error['descr']=$error['print']="WMID-плательщик ".$_POST['LMI_PAYER_WM'].
						" отличается от WMID, указанного в заявке (".$row_order['wmid'].")";
					$error['retval']=5;
				}
				if ( isset($_POST['PurseOut']) ) {
					$purseOut=strtoupper($_POST['purseTypeOut']).trim(strtr($_POST['PurseOut'], "URZEurze", "        "));
					$response = $wmxi->X8($_POST['LMI_PAYER_WM'],$purseOut);
					$structure = $parser->Parse($response, DOC_ENCODING);
					$transformed = $parser->Reindex($structure, true);
					//print_r($transformed);
					if ( $transformed['w3s.response']['retval'] == 1 ){
						if ( $transformed['w3s.response']['testwmpurse']['purse']=='' ) {
							$error['descr']=$error['print']= 'Кошелек получателя '.$purseOut.
								' должен принадлежать WM-идентификатору '.$_POST['LMI_PAYER_WM'];
							$error['retval']=2;
						}
					}else{
						$error['descr']=$error['print']='Заявка не может быть проведена по указанным реквизитам';
						$error['retval']=3;
					}
				} 
				if ( trim($row_order['descr'].($row_order['retid']!="" ? " [rd: ".$row_order['retid']."]" : "" )) !=
																	trim($_POST['LMI_PAYMENT_DESC']) ) {
					$error['descr']="Изменено примечание ".$_POST['LMI_PAYMENT_DESC'];
					$error['retval']=4;
					$error['print']= "Изменены параметры платежа. Платеж не может быть проведен. ";
				}
				/*if ( substr($row_order['currout'],0,2) == "WM" && 
				( ($row_order['summout']+$row_order['discammount']) > $WM_amount_r[$row_order['currout']]) ) {
					$error['descr']="Не хватает резерва по заявке ".$WM_amount_r[$row_order['currout']];
					$error['print']="Для завершения обмена сервису не хватает резерва выбранного направления. 
					Возможно произошло резервирование средств по параллельно оформленным заявкам. 
					Попробуйте оформить заявку через 10 мин или обратитесь в техподдержку 
					(http://obmenov.com/contacts.php).";
					$error['retval']=5;

				}*/
				if ( 1==2 ) {//isIPIn($_SERVER['REMOTE_ADDR'],$wmNets[1],"255.255.255.0") ||
					 //isIPIn($_SERVER['REMOTE_ADDR'],$wmNets[2],"255.255.255.0") ||
					 //isIPIn($_SERVER['REMOTE_ADDR'],$wmNets[3],"255.255.255.0") ||
					// isIPIn($_SERVER['REMOTE_ADDR'],$wmNets[4],"255.255.255.0") ) {
					
					$error['descr']="ip:".$_SERVER['REMOTE_ADDR'];
					$error['retval']=10;
					$error['print']="Неправильные параметры заявки p:";
					
				}
				$order=new orders();
				$check_summ=$order->check_order_summ($row_order['id']);
				if ( $check_summ[1]!=$check_summ[2] ) {
					$error['descr']="Изменена сумма сумма1=".$check_summ[1].", сумма2=".$check_summ[2];
					$error['retval']=34;
					$error['print']= "Изменены параметры платежа. Платеж не может быть проведен. ";
				}
				if ( !isset($error) ) {
					$error['retval']=1;
					$update="update orders set retval=1 where id=".$row_order['id'];
					$query=_query2($update, "result.php 45");
					echo "YES";

					
				}else{
					$update="update orders set status='".$error['descr']."', retval=".$error['retval']." where id=".$row_order['id'];
					$query=_query2($update, "result.php 45");
					echo $error['print'];
				}
		}else { # step 5
        _error('Inconsistent parameters '.print_r($_POST, 1), 5, '');
     };
}
} else { # step 3
    _error('Inconsistent parameters', 3, print_r($_REQUEST,1));
};
}else{ #  Payment notification
    if( isset($_POST['LMI_PAYMENT_NO']) # Check payment id
	
	&&  preg_match('/^\d+$/',$_POST['LMI_PAYMENT_NO']) == 1 ){ 
	# Check ticket, step 11  //&& isset($_POST['RND']) && preg_match('/^[A-Z0-9]{8}$/',$_POST['RND'],$match) == 1
	# Query form database about payment with such id
	$query = "SELECT payment.id, payment.orderid, payment.rnd FROM payment WHERE ".
	"payment.id = ".$_POST['LMI_PAYMENT_NO'];
	$result = _query($query, 12);
	$rows = $result->num_rows;
	$pay=$result->fetch_assoc();
		$order = "SELECT orders.id, orders.summin, orders.currin, orders.summout, orders.currout, orders.clid,
  				orders.discammount, orders.attach, orders.partnerid,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e				
				FROM orders WHERE orders.id=".$_POST['oid'].";";
	$row_order=_query($order, 19);
	$row_order=$row_order->fetch_assoc();
	$numrows_order = $result->num_rows;
	if ( $rows != 1 && numrows_order !=1 ) {
	        _error('Payment not found', 13, '');
	    } else { # If payment or items were not found,
	    # Create check string
    	    $chkstring =  $shop_wm_purse[substr($row_order['currin'],2,1)].number_format($row_order['summin'], 2, '.', '').$pay['id'].
		    $_POST['LMI_MODE'].$_POST['LMI_SYS_INVS_NO'].$_POST['LMI_SYS_TRANS_NO'].$_POST['LMI_SYS_TRANS_DATE'].
	            $LMI_SECRET_KEY.$_POST['LMI_PAYER_PURSE'].$_POST['LMI_PAYER_WM'];
	    if ( $LMI_HASH_METHOD == 'MD5' ) {
	    	$md5sum = strtoupper(md5($chkstring));
		$hash_check = ($_POST['LMI_HASH'] == $md5sum);
	    } else {
		_error('Config parameter LMI_HASH_METHOD incorrect!', 14, '');
	    };	  
	    if(    $_POST['LMI_PAYMENT_NO'] == $pay['id'] # Check if payment id, purse number and amount correspond
		&& isset($_POST["LMI_SYS_TRANS_NO"])
		&& $_POST['LMI_PAYEE_PURSE'] == $shop_wm_purse[substr($row_order['currin'],2,1)] 
		&& $_POST['LMI_PAYMENT_AMOUNT'] == $row_order['summin']
		&& $_POST['LMI_MODE'] == $LMI_MODE
		) {  # checksum is correct, step 15
		    # if everything is ok, payment receives status: Paid, item receives status: Sold,
		    # enter payment and customer data into database
		    $query = "UPDATE payment SET timestamp=CURRENT_TIMESTAMP(), ".
			     "LMI_SYS_INVS_NO='".$_POST['LMI_SYS_INVS_NO']."', ".
			     "LMI_SYS_TRANS_NO='".$_POST['LMI_SYS_TRANS_NO']."', ".
			     "LMI_SYS_TRANS_DATE='".$_POST['LMI_SYS_TRANS_DATE']."', ".
			     "LMI_PAYER_PURSE='".$_POST['LMI_PAYER_PURSE']."', ".			     
			     "LMI_PAYER_WM='".$_POST['LMI_PAYER_WM']."', ".
				 "ordered=1 ".
			     "WHERE id=".$pay['id'].";";
		    $result = _query($query, 16);
			

			
			// заполнение базы недостающим
			if ( isset($_POST['Purse']) ) {$purse=", `purse_".strtolower($_POST['purseType'])."`='".
				trim(strtr($_POST['Purse'], "URZEurze", "        "))."'";}
			else{$purse='';}
			if ( isset($_POST['PurseOut']) ) {$purseOut=", `purse_".strtolower($_POST['purseTypeOut'])."`='".
				trim(strtr($_POST['PurseOut'], "URZEurze", "        "))."'";}
			else{$purseOut='';}

			$order = "SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.summout, orders.clid,
	  			orders.discammount, orders.partnerid, orders.attach, orders.needcheck, orders.email,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e,	orders.retid,
				(select type from currency where name=orders.currin) as currintype,
				(select type from currency where name=orders.currout) as currouttype, orders.needcheck
				FROM orders WHERE orders.id=".$row_order['id'];
			$row_order=_query2($order, 17);
			$row_order=$row_order->fetch_assoc();
			
			
			$clorder = new orders();
			// партнерские
			$clorder->partner_bonus($row_order);
			//оповещение о принятии платежа
			//$clorder->email_pay_recieved($row_order);
			
			if ( substr($row_order['currout'],0,2) == "WM" ) {
				// pay_wm
				$clorder->pay_wm($row_order);
			} else { // секция обмена на наличные
			}	
			
			//$clorder->send_sms($row_order);
		} else { # step 15
			@mail("support@obmenov.com", "15", print_r($_POST,1)." hash=".$hash_check." " .$pay['id']. " ".
				$shop_wm_purse[substr($row_order['currin'],2,1)]. " ".
				$row_order['summin']." ". $LMI_MODE,'');
			_error('Inconsistent parameters', 15, '');
	    };
	}
    } else { # step 11
	_error('Inconsistent parameters', 11, '');
    };
}
?>