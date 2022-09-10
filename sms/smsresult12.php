<?php
	require_once("../Connections/ma.php");
	require_once($serverroot."function.php");
	require_once($serverroot."siti/sms_config.php");
	require_once($serverroot."siti/class.php");
	// the function returns an MD5 of parameters passed
	// ������� ���������� MD5 ���������� �� ����������
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
	// ������ ���������� ��������� �� ������� ������
	foreach($_REQUEST as $request_key => $request_value) { 
		$_REQUEST[$request_key] = substr(strip_tags(trim($request_value)), 0, 250);
	}
	
	// collecting required data
	// �������� ����������� ������
	$purse        = $_REQUEST["s_purse"];        // sms:bank id        ������������� ���:�����
	$order_id     = $_REQUEST["s_order_id"];     // operation id       ������������� ��������
	$amount       = $_REQUEST["s_amount"];       // transaction sum    ����� ����������
	$clear_amount = $_REQUEST["s_clear_amount"]; // billing algorithm  �������� �������� ���������
	$inv          = $_REQUEST["s_inv"];          // operation number   ����� ��������
	$phone        = $_REQUEST["s_phone"];        // phone number       ����� ��������
	$sign         = $_REQUEST["s_sign_v2"];      // signature          �������
	
	// making the reference signature
	// ������� ��������� �������
	$reference = ref_sign($secret_code, $purse, $order_id, $amount, $clear_amount, $inv, $phone);
	
	// validating the signature
	// ���������, ����� �� �������
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
		if ( $summout<$row_order['summout'] ) { // �� ��������� �����
			maildebugger($summout);
			$error['descr']=" �� ��������� ����� �������: �� ������- ".$row_order['summout'].", ����������- " .$summout;
			$error['retval']=10;
			$error['print']="������������ ��������� ������";
			
		}
		// ������������ ���������� ������
		//mail('your mail','new sms','-->'.print_r($_REQUEST,true).' -->'.http_build_query($_POST));
		
		
		if ( $error['retval']!=0 ) {
			$update="update orders set status='".$error['descr']."', retval=".$error['retval']." where id=".$row_order['id'];
			$query=_query2($update, "result.php 45");
		}else{
			// ��� �����, ��������� ���� � ������	
			$update="update payment set LMI_SYS_TRANS_NO=".$inv.", LMI_PAYER_PURSE='".$phone."', ordered=1 where orderid=".$order_id;
			$query=_query($update,"");
			$clorder = new orders();
			// �����������
			$clorder->partner_bonus($row_order);
			//���������� � �������� �������
			//$clorder->email_pay_recieved($row_order);
			
			if ( substr($row_order['currout'],0,2) == "WM" ) {
				// pay_wm
				//$clorder->pay_wm($row_order);
				echo "paywm";
			}
		}
	} else {
		echo "����������� ��������� ������";
		print_r($_REQUEST);
		// failure, reporting error
		// ����������� ��������� ������
	}
?>
