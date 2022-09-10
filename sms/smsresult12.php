<?php
	require_once("../Connections/ma.php");
	require_once($serverroot."function.php");
	require_once($serverroot."siti/sms_config.php");
	require_once($serverroot."siti/class.php");
	// the function returns an MD5 of parameters passed
	// функция возвращает MD5 переданных ей параметров
	//maildebugger("sms_result@");
	if ( isset($_REQUEST['s_purse']) && isset($_REQUEST['s_order_id']) && isset($_REQUEST['s_sign_v2']) ) {
	}else{
		die();
	}
																						 
	function ref_sign() {
		$params = func_get_args();
		$prehash = implode("::", $params);
		return md5($prehash);
	}
	
	// filtering junk off acquired parameters
	// парсим полученные параметры на предмет мусора
	foreach($_REQUEST as $request_key => $request_value) { 
		$_REQUEST[$request_key] = substr(strip_tags(trim($request_value)), 0, 250);
	}
	
	// collecting required data
	// собираем необходимые данные
	$purse        = $_REQUEST["s_purse"];        // sms:bank id        идентификатор смс:банка
	$order_id     = $_REQUEST["s_order_id"];     // operation id       идентификатор операции
	$amount       = $_REQUEST["s_amount"];       // transaction sum    сумма транзакции
	$clear_amount = $_REQUEST["s_clear_amount"]; // billing algorithm  алгоритм подсчета стоимости
	$inv          = $_REQUEST["s_inv"];          // operation number   номер операции
	$phone        = $_REQUEST["s_phone"];        // phone number       номер телефона
	$sign         = $_REQUEST["s_sign_v2"];      // signature          подпись
	
	// making the reference signature
	// создаем эталонную подпись
	$reference = ref_sign($secret_code, $purse, $order_id, $amount, $clear_amount, $inv, $phone);
	
	// validating the signature
	// проверяем, верна ли подпись
	if($sign == $reference) {
		// success, proceeding
		$error['retval']=0;
		$select="select * from orders where id=".$order_id;
		$query=_query($select,"");
		$row_order=$query->fetch_assoc();
		$select="select type from currency where name='".$row_order['currout']."'";
		$query=_query($select,"");
		$row=$query->fetch_assoc();

		$summout=round($amount*$courses['USD'][$row['type']]/get_setting('sms_profit'),2);
		if ( $summout<$row_order['summout'] ) { // не совпадают суммы
			maildebugger($summout);
			$error['descr']=" Не совпадают суммы выплаты: по заявке- ".$row_order['summout'].", фактически- " .$summout;
			$error['retval']=10;
			$error['print']="Неправильные параметры заявки";
			
		}
		// обрабатываем полученные данные
		//mail('your mail','new sms','-->'.print_r($_REQUEST,true).' -->'.http_build_query($_POST));
		
		
		if ( $error['retval']!=0 ) {
			$update="update orders set status='".$error['descr']."', retval=".$error['retval']." where id=".$row_order['id'];
			$query=_query2($update, "result.php 45");
		}else{
			// все класс, обновляем базу и платим	
			$update="update payment set LMI_SYS_TRANS_NO=".$inv.", LMI_PAYER_PURSE='".$phone."', ordered=1 where orderid=".$order_id;
			$query=_query($update,"");
			$clorder = new orders();
			// партнерские
			$clorder->partner_bonus($row_order);
			//оповещение о принятии платежа
			//$clorder->email_pay_recieved($row_order);
			
			if ( substr($row_order['currout'],0,2) == "WM" ) {
				// pay_wm
				//$clorder->pay_wm($row_order);
				echo "paywm";
			}
		}
	} else {
		echo "неправильно составлен запрос";
		print_r($_REQUEST);
		// failure, reporting error
		// неправильно составлен запрос
	}
?>
