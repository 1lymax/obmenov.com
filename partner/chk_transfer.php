<?php 
	require_once('../Connections/ma.php');
	@ini_set ("display_errors", true);
	error_reporting(0);
	require_once($serverroot.'siti/partner/class.php');
	
	$xmlstr = file_get_contents('php://input'); 
	$xmlstr=trim($xmlstr); // Проверяем, получено ли хоть что-нибудь
	if($xmlstr=="") { echo "Пустой вызов"; exit; }
	$xmlres = simplexml_load_string($xmlstr); // Проверяем валидность XML-пакета 
	if(!$xmlres) { echo "Невалидный XML"; exit; }
	//print_r($xmlres);
	$transfer['reqn']=isset($xmlres->reqn)?mysql_real_escape_string((string)$xmlres->reqn) : "";
	$transfer['type']=isset($xmlres->type)?(string)$xmlres->type : "";
	$transfer['partner']=isset($xmlres->partner)?(int)$xmlres->partner : 0; 
	$transfer['order_id']=isset($xmlres->order_id)?(string)$xmlres->order_id : ""; 
	$transfer['id']=isset($xmlres->id)?(string)$xmlres->id : "";
	$inhash=isset($xmlres->hash)?$xmlres->hash:"";
	
	
	//print_r($transfer);	
	$pnclass=new partner();
	$select="select * from partner where id=".$transfer['partner'];
	$query=_query($select,"");
	$pn=$query->fetch_assoc();
	$ourhash=strtolower(md5($transfer['reqn'].$transfer['type'].$transfer['partner'].
										$transfer['order_id'].$transfer['id'].$pn['secret']));
	$answer="";
	if ( mysql_num_rows($query)!=0 ) {
		if ( $_SERVER['REMOTE_ADDR']==$pn['server_ip'] ) {
			if ( $inhash==$ourhash) {
				$order_id=(strlen(trim($transfer['order_id']))==0? "" : " payment.LMI_SYS_TRANS_NO='".(int)$transfer['order_id']."' and ");
				$id=(strlen(trim($transfer['id']))==0? "" : " payment.orderid=".(int)$transfer['id']." and ");
				$select="select partner_transfers.own_order_id as id, orders.summout as summout, 
								orders.currout as currout, payment.canceled as status, payment.LMI_SYS_TRANS_NO as order_id 
							from partner_transfers, payment, orders 
							where partner_transfers.own_order_id=payment.orderid 
								and partner_transfers.own_order_id = orders.id
								and partner_transfers.partner=".$transfer['partner']." and".
											$order_id.$id." 1=1 order by partner_transfers.id desc";
				$query=_query($select,"");
				$acc=$query->fetch_assoc();
				$select="select * from payment_out where payment=".(isset($acc['id'])?$acc['id']:"0 and 1=2");
				$query=_query($select,"");
				$payment_out=$query->fetch_assoc();
				$answer.="<id>".(isset($acc['id'])?$acc['id']:$transfer['id'])."</id>\r\n";
				$answer.="<order_id>".(isset($acc['order_id'])?$acc['order_id']:$transfer['order_id'])."</order_id>\r\n";
				$answer.="<account>".(isset($payment_out['purse'])?$payment_out['purse']:"")."</account>\r\n";
				$answer.="<summ>".(isset($acc['summout'])?$acc['summout']:"")."</summ>\r\n";
				$answer.="<currency>".(isset($acc['currout'])?$acc['currout']:"")."</currency>\r\n";
				$answer.="<ref>".(isset($payment_out['retdesc'])?$payment_out['retdesc']:"")."</ref>\r\n";
				$answer.="<status>".(isset($acc['status'])?$acc['status']:"0")."</status>\r\n";

			}else{
				$response[0]['message']="Проверка подписи не прошла";
				$response[0]['state']=12;
			}
		}else{
			$response[0]['message']="Невалидный IP-адрес отправителя: ".$_SERVER['REMOTE_ADDR'];
			$response[0]['state']=13;
		}
	}else{
		$response[0]['message']="Доступ запрещен";
		$response[0]['state']=17;
	}
	$response= "<response>\r\n<reqn>".$transfer['reqn']."</reqn>\r\n<state>".
	(isset($response[0]['state'])?$response[0]['state']:"1")."</state>\r\n<desc>".
	(isset($response[0]['message'])?$response[0]['message']:"")."</desc>\r\n".
	$answer."</response>";
	$pnclass->write_log((string)$xmlstr, $response, $transfer['partner'],$transfer['type']);
	echo $response;
	
/*	
запрос состояния заявки на перевод
url - https://obmenov.com/partner/chk_transfer.php
hash - reqn.type.secret.partner.order_id.id
id - номер транзакции на стороне obmenov.com
order_id - номер заявки на стороне партнера
если id и order_id не задан, возвращает информацию по последней заявке

<request>
   <reqn>586420</reqn>
   <partner>1982</partner>
   <type>chk_transfer</type>
   <order_id>44</order_id>
   <id></id>
   <hash></hash>
</request>

------------------

reqn - повторяет reqn в запросе данных, INT
state - статус запроса, INT
// 1 - успешно, 
// 0 - не успешно, 
// 12 - подпись проверку не прошла, 
// 13- ip-адрес, 
desc - расширенное описание статуса. STRING

account - банк карта/счет, 16 цифр
ref- референс в приват24
status- статус платежа, 1- успешно, 0- не успешно
<response>
<reqn>6260470</reqn>
<state>1</state>
<desc></desc>
<id>162787</id>
<order_id>44</order_id>
<account>1234567890123456</account>
<ref>aA4XQ2509011251234</ref>
<status>0</status>
</response>
*/
	
?>


