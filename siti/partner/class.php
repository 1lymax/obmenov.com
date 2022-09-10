<?php 

class partner {
	function update_transfer ($transfer) { // возвращает id новой записи в partner_transfer
		
		$select="select * from partner where id=".$transfer['partner'];
		$query=_query($select,"");
		$row=$query->fetch_assoc();
		$update="insert into partner_transfers 
									(type, partner, order_id, fname, iname, account, summ, currency, regn, test) values
									('".mysql_real_escape_string($transfer['type'])."',".$transfer['partner'].",".$transfer['order_id'].",
									'".mysql_real_escape_string($transfer['fname'])."','".
									mysql_real_escape_string($transfer['iname'])."','".
									mysql_real_escape_string($transfer['account'])."','".$transfer['summ']."',
									'".mysql_real_escape_string($transfer['currency'])."','".$transfer['reqn']."',".$transfer['test'].")";
		$query=_query($update,"");
		$transfer['id']=mysql_insert_id();
		$select="select verified from bank_accounts where fname='".mysql_real_escape_string($transfer['fname'])."'
							and iname='".mysql_real_escape_string($transfer['iname'])."' 
							and account=".mysql_real_escape_string($transfer['account'])."";
		$query=_query($select,"");
		$bank_acc=$query->fetch_assoc();
		if ( $bank_acc['verified']==0 )$needcheck=1;
		if ( $bank_acc['verified']==1 )$needcheck=0;
		if ( $bank_acc['verified']==2 )$needcheck=2;
		$update = "INSERT INTO orders (summin, currin, summout, currout, fname, iname,
							date, time, account, clid, ip, av_balance, needcheck, ordered, attach, droped) 
										 VALUES (
													 ".$transfer['summ'].",
													 '".mysql_real_escape_string($transfer['type'].$transfer['currency'])."',
													 ".$transfer['summ'].",
													 '".mysql_real_escape_string($transfer['type'].$transfer['currency'])."',
													 '".mysql_real_escape_string($transfer['fname'])."',
													 '".mysql_real_escape_string($transfer['iname'])."',
													 NOW(), NOW(),
													 '".mysql_real_escape_string($transfer['account'])."',
													 '".$row['clid']."',
													 '".$_SERVER['REMOTE_ADDR']."',
													 ".$transfer['av_balance'].",
													 ".$needcheck.", 1,
													 ".$transfer['summ']/$transfer['course'].",".$transfer['test']."
													 );";
		$query=_query($update, "");
		$order_id=mysql_insert_id();
		$update="update partner_transfers set own_order_id=".$order_id." WHERE id=".$transfer['id'];
		$query=_query($update,"");
		$update="insert into payment (orderid, ordered, LMI_SYS_TRANS_NO, LMI_PAYER_PURSE) values (
														".$order_id.",1, ".$transfer['order_id'].", 'Партнер ".
														$row['nikname']." ".$row['id']."')";
		$query=_query($update, "");
		$transfer['needcheck']=$needcheck;
		$transfer['id']=$order_id;
		return $transfer;
		

	}
	
	function balance ($partner_id) {
		$full_balance = _query("SELECT SUM(bonus) as sum FROM partner_bonus  
			WHERE partnerid=".$partner_id, "");
		$full_balance = mysql_fetch_assoc($full_balance);
		$full_balance = $full_balance["sum"];
		
		$query="SELECT SUM(orders.attach) AS total_payed FROM orders, payment_out, payment
							WHERE orders.id=payment_out.payment and orders.id=payment.orderid and payment_out.partnerid=".$partner_id."
		AND payment.ordered=1
		GROUP by payment_out.partnerid";
		$total_payed=_query($query, "");
		$total_payed=mysql_fetch_assoc($total_payed);
		
		return $full_balance-$total_payed['total_payed'];
	}
	
	function request ($xmlstr, $url) { // транспортная ф-ция
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlstr); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$res=curl_exec($ch);
		return $res;
	}
	function reqID() {
		return microtime()*10000000;
	}
	function write_log($request, $response, $partner, $type) {
		$select="insert into xml_partner (partner,type, request, response, ip, time) values (".
										"".(int)$partner.",".
										"'".mysql_real_escape_string($type)."',".
										"'".$request."',".
										"'".mysql_real_escape_string($response)."',".
										"'".$_SERVER['REMOTE_ADDR']."',".
										"NOW()".
										")";
		$query=_query2($select,"");
	}
}


?>