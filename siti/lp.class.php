<?php 
require_once ($GLOBALS['serverroot']."siti/class.php");

class liqpay {
	function liqpay ($id) {
		$select="select * from merch where work=1 and id=".$id;
		$query=_query($select,"");
		$row=$query->fetch_assoc();
		$this->m=$row['merchant_id'];
		$this->p=$row['merchant'];
		$this->p_pay=$row['merchant2'];
		$this->number=$row['number'];
		
		
		
	}
	
	function inner_pay($acc,$amount, $currency) {
		$r['kind']='card';
		$r['merchant_id']=$this->m;
		$r['id']="inner".time();
		$r['acc']=$acc;
		$r['amount']=$amount;
		$r['currency']=$currency;
		$r['description']="Transfer to own card";
		$pay=$this->pay($r);
		
	}
	function out_pay($acc,$amount, $currency) {
		$r['kind']='card';
		$r['merchant_id']=$this->m;
		$r['id']="inner".time();
		$r['acc']=$acc;
		$r['amount']=$amount;
		$r['currency']=$currency;
		$r['description']="Transfer to card";
		$pay=$this->pay($r);
		
	}
	function order_pay ($row_order, $currency="") {
		$orders=new orders();
		if ( !is_array($row_order) ) $row_order=$orders->order_query($row_order);
		$response=$this->check_trans($row_order['id'],1);
		if ( $response->status=="success" ) {
			$response->to="";
			$response->transaction_id=$response->transaction->id;
			$response->amount=$response->transaction->amount;
			$response->currency=$response->transaction->currency;
			$response->order_id=$response->transaction->order_id;
			$this->trans_canceled($row_order,$response);
			return;
		}
		//die();
		$r['merchant_id']=$this->m;
		$query="select type, extname from currency where name='".$row_order['currout']."'";
		$r['kind']='phone';
		
		if ( substr($row_order['currout'],0,2)=='LQ' ) {
			$r['kind']="phone";
			$r['acc']=$row_order['phone'];
		}elseif ( substr($row_order['currout'],0,3)=='MCV' ){
			$r['kind']="card";
			$r['acc']=$row_order['account'];
		}elseif ( substr($row_order['currout'],0,3)=='P24' ){
			$r['kind']="card";
			$r['acc']=$row_order['account'];
		}else{
		//	return;
		}
		$result=_query($query,"");
		$row=mysql_fetch_assoc($result);
		$r['id']=$row_order['id'];
		if ( $currency=="" ) {
			$r['amount']=$row_order['summout']+$row_order['discammount'];
			$r['currency']=$row['type'];
		}else{
			$tab=strtolower(str_replace("RUB","RUR",$currency));
			$select="SELECT course_".$tab.".`min` as min FROM course_".$tab." 
						ORDER BY date desc";
						echo $select;
			$query=_query($select,"lp 45");
			$rate=$query->fetch_assoc();
			$r['amount']=round(($row_order['summout']+$row_order['discammount'])/$rate['min'],2);
			$r['currency']=$currency;
		}
	/*$r['description']='Order #'.$row_order['id'].'. '.$row_order['summin'].' '.$row_order['currintype'].' - '.$r['amount'].' '.
		  			$row_order['currout'];*/
		$r['description']=$row_order['id'];
		return $this->pay($r);
		
	}
	
	function pay($row, $row_order=0) {
		
		$str = '<request>
          <version>1.2</version>
          <action>send_money</action>
          <kind>'.$row['kind'].'</kind>
          <merchant_id>'.$row['merchant_id'].'</merchant_id>
          <order_id>'.$row['id'].'</order_id>
          <to>'.$row['acc'].'</to>
          <amount>'.$row['amount'].'</amount>
          <currency>'.$row['currency'].'</currency>
          <description>'.$row['description'].'</description>
        </request>';
		echo $str;
		//die();
		$xml=$this->do_request($str, 1);
		
		$result=simplexml_load_string($xml);
		if ( $this->signature($result, 1) ) { // проверка правильности сигнатуры
			
			$response = base64_decode($result->liqpay->operation_envelope->operation_xml);
			$response= simplexml_load_string($response);
			//print_r($response);
			if ( is_array($row_order) ) {
				return $this->trans_canceled($row_order,$response);
			}else{
				return $this->trans_canceled(0,$response);
			}
		}else{
			//badlog
			echo "badlog";
			print_r($result);
			
		}
		return $result;
		
	}
	
	function check_trans ($row_order,$full_return=0) {
		$orders=new orders();
		if ( !is_array($row_order) ) $row_order=$orders->order_query($row_order);
		$str = '<request>
     		<version>1.2</version>
     		<action>view_transaction</action>
      		<merchant_id>'.$this->m.'</merchant_id>
      		<transaction_id></transaction_id> 
     		<transaction_order_id>'.$row_order['id'].'</transaction_order_id>
			</request>';
		$xml=$this->do_request($str);
		$result=simplexml_load_string($xml);
		if ( $this->signature($result) ) {
			$response = base64_decode($result->liqpay->operation_envelope->operation_xml);
			$response = simplexml_load_string($response);
			if ( $full_return ) {
				return $response;
			}
			//echo "return status - ".$response->transaction->order_id." - ".$response->transaction->status;
			if ( $response->transaction->status=="success" ){
				echo "return true - ".$response->transaction->order_id;
				return true;
			}else{
				return false;
			}
		}else{
			return "bad siganture";
		}		
	}
	
	function balance() {
		$a=array();
		$str = '<request>
      	<version>1.2</version>
      	<action>view_balance</action>
      	<merchant_id>'.$this->m.'</merchant_id>
	  	</request>
	  	';
		$xml=$this->do_request($str);
		$result=simplexml_load_string($xml);
		//print_r($result);
		if ( $this->signature($result) ) {
			$response = base64_decode($result->liqpay->operation_envelope->operation_xml);
			$response = simplexml_load_string($response);
			//print_r($response);
		}
		//die();
		if ( isset($response) ) {
				$a['USD']=$response->USD;
				$a['UAH']=$response->UAH;
				$a['RUR']=$response->RUR;
				$a['EUR']=$response->EUR;
				/*foreach ($a as $k=>$v) {
					echo($k)."=".$v."<br />";
					$select="select * from amounts where val='LQ".strtoupper($k)."' and (time + INTERVAL 1 DAY > NOW()) order by time desc;";
					$query=_query2($select, "class.php liqpay balance");
					$row=$query->fetch_assoc();
					if ( $row['amount']!=$v ) {
						$insert="insert into amounts (val,amount) values ('LQ".strtoupper($k)."',".
										//$v.")";
					//echo $insert;
					//$query=_query($insert,"");
				}
			}*/
		
		}
		return $a;
	}
	function trans_canceled($row_order=0, $response=0) {
		if ( is_array($row_order) ) {
			$clid=$row_order['clid'];
		}else{
			$clid="";
		}
		if ( $response ) {
			if ( $response->status=="success" ) { // операция успешна
				$canceled=1;
				$retdesc=$response->transaction_id;
				$retval=0;
			}else{
				$canceled=0;
				$retdesc=$response->code.(isset($response->response_description)?" ".$response->response_description:"");
				$retval=1;
			}
			$select="select id from payment_out where payment='".$response->order_id."'";
			$query=_query($select,"76432");
			$partner_id['partner']="0";
			$select="select partner from partner_transfers where own_order_id=".(int)$response->order_id;
			$query2=_query($select,"34566");
			$partner_id=mysql_fetch_assoc($query2);
			if ( mysql_num_rows($query)!=0 ) {
				$partner_pay=mysql_num_rows($query2)!=0 ? "partnerid=".$partner_id['partner']."," : "0";	
				if ( $response->order_id!=0 ) {
					$select="update payment_out set 
								purse='".$response->to."',
								summ=".$response->amount.",
								val='".$response->currency."',
								clid='".$clid."',
								retdesc='Liqpay #$retdesc',
								retval=$retval,
								partnerid='".$partner_id['partner']."',
								payer='".$this->number."'
								where payment='".$response->order_id."'";
				}
			}else{
				$select="insert into payment_out (purse, summ, val, clid, retdesc, payment, payer, partnerid, retval) values ('".
											$response->to."',".
											$response->amount.", '".$response->currency."', '$clid','Liqpay #$retdesc',".
											$response->order_id.",'".$this->number."','".$partner_id['partner']."', $retval)";
			}
			$query=_query($select,"");
			//if ( is_array($row_order) ) {
			$select="update payment set canceled=".$canceled." where orderid='".$response->order_id."'";
			echo $select;
			$query=_query($select,"");
			//}/
			return $response;
		}
		
	}
	function signature ($res, $pay=0) {
		if ( $pay ){
			$pay=$this->p_pay;
		}else{
			$pay=$this->p;
		}
		if ( !isset($res->liqpay->operation_envelope) ) return false;
		$response = base64_decode($res->liqpay->operation_envelope->operation_xml);
		$insig = $res->liqpay->operation_envelope->signature;
		$gensig = base64_encode(sha1($pay.$response.$pay,1));
		//echo $insig."======".$gensig;
		if ( $insig==$gensig ) {
			//echo "true";
			return true;	
		}else{
			//echo "false";
			$query="insert into badlog (sender, type, ip, request, data) values ('liqpay', 'invalid signature',
												'".(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:"")."',
												'".print_r($_REQUEST,1)."',
												'response: ".print_r($response,1).", $insig ^^^ $gensig')";
			$result=_query($query,"");
			return false;
		}

	}
	function do_request ($str, $pay=0) {
		if ( $pay ){
			$pay=$this->p_pay;
		}else{
			$pay=$this->p;
		}
		
    	 $operation_xml = base64_encode($str);
    	 $signature = base64_encode(sha1($pay.$str.$pay, 1));
    	 $operation_envelop = '<operation_envelope>
                              <operation_xml>'.$operation_xml.'</operation_xml>
                              <signature>'.$signature.'</signature>
                         </operation_envelope>';
    	 $post = '<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                              <request>
                                   <liqpay>'.$operation_envelop.'</liqpay>
                              </request>';
     	$url = "https://www.liqpay.com/?do=api_xml";
     	$page = "/?do=api_xml";
     	$headers = array("POST ".$page." HTTP/1.0",
                         "Content-type: text/xml;charset=\"utf-8\"",
                         "Accept: text/xml",
                         "Content-length: ".strlen($post));
     	$ch = curl_init();
     	curl_setopt($ch, CURLOPT_URL, $url);
     	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     	//curl_setopt($ch, CURLOPT_CAINFO, $GLOBALS['serverroot']."siti/liqpay.crt");
     	//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
     	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
     	curl_setopt($ch, CURLOPT_POST, 1);
     	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
     	$result = curl_exec($ch);
     	
	 	if (curl_errno($ch) != 0)
			{
			print_r("lq error CURL: CODE:".curl_error($ch)." INFO:".curl_getinfo($ch, CURLINFO_HTTP_CODE));
		}
		curl_close($ch);
		libxml_use_internal_errors(true);
		$answer=simplexml_load_string($result);
		if($answer===false)return false;
		$answer=$answer->liqpay->operation_envelope->operation_xml;
		$answer=base64_decode($answer);
		$insert="insert into xml ( `iface`, `query`, `answer` ) values ('Liqpay', '".mysql_real_escape_string($str)."', 
																								'".mysql_real_escape_string($answer)."')";
		$query=_query2($insert, "lp.xml");
		return $result;
		
	}
	
	
}


?>