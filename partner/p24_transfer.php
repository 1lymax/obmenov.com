<?php 
	require_once('../Connections/ma.php');
	@ini_set ("display_errors", true);
	require_once($serverroot.'siti/partner/class.php');
	//error_reporting(0);
	$xmlstr = file_get_contents('php://input'); 
	$xmlstr=trim($xmlstr); // Проверяем, получено ли хоть что-нибудь
	if($xmlstr=="") { echo "Пустой вызов"; exit; }
	$xmlres = simplexml_load_string($xmlstr); // Проверяем валидность XML-пакета 
	if(!$xmlres) { echo "Невалидный XML"; exit; }
	//print_r($xmlres);
	$transfer['reqn']=isset($xmlres->reqn)?mysql_real_escape_string((string)$xmlres->reqn) : 0;
	$transfer['test']=isset($xmlres->testmode)?(int)$xmlres->testmode: 1;
	$transfer['type']=isset($xmlres->type)?(string)$xmlres->type : "";
	$transfer['partner']=isset($xmlres->partner)?(int)$xmlres->partner : 0; 
	$transfer['order_id']=isset($xmlres->transfer->order_id)?(int)$xmlres->transfer->order_id : 0; 
	$transfer['fname']=isset($xmlres->transfer->fname)?(string)$xmlres->transfer->fname : ""; 
	$transfer['iname']=isset($xmlres->transfer->iname)?(string)$xmlres->transfer->iname : "";
	$transfer['account']=isset($xmlres->transfer->account)?(string)$xmlres->transfer->account : "";
	$transfer['summ']=isset($xmlres->transfer->summ)?number_format((float)$xmlres->transfer->summ,2,".","") : 0;
	$transfer['currency']=isset($xmlres->transfer->currency)?(string)$xmlres->transfer->currency : "";
	$transfer['needcheck']=isset($xmlres->transfer->needcheck)?(int)$xmlres->transfer->needcheck : 1; 
	$inhash=isset($xmlres->hash)?$xmlres->hash : "";
	
	//print_r($transfer);	
	$pnclass=new partner();
	$select="select * from partner where id=".$transfer['partner'];
	$query=_query($select,"");
	$pn=$query->fetch_assoc();
	$ourhash=strtolower(md5($transfer['reqn'].$transfer['type'].$transfer['partner'].
													$transfer['order_id'].$transfer['account'].$transfer['summ'].
													$transfer['currency'].$transfer['needcheck'].$pn['secret']));
	if ( mysql_num_rows($query)!=0 ) {
		if ( $_SERVER['REMOTE_ADDR']==$pn['server_ip'] ) {
			if ( $inhash==$ourhash) {
				//print_r($transfer);
				//die();
				if ( $pn['p24_transfer']==0) {
					$response[0]['message']="Переводы Приват24 не разрешены";
					$response[0]['state']=15;
				
				}else{
					//echo "Продолжаем";
					$transfer['fname']=mysql_real_escape_string(iconv("utf-8", "windows-1251",trim($transfer['fname'])));
					$transfer['iname']=mysql_real_escape_string(iconv("utf-8", "windows-1251",trim($transfer['iname'])));
					$transfer['account']=mysql_real_escape_string(iconv("utf-8", "windows-1251",trim($transfer['account'])));
					$row=$query->fetch_assoc();
					$select="select * from partner_transfers where order_id=".$transfer['order_id']." and partner=".$transfer['partner'];
					$query=_query($select,"");
					if ( mysql_num_rows($query)!=0 ){
						$response[0]['message']="order_id #".$transfer['order_id']." уже есть в базе платежей";
						$response[0]['state']=14;
					}else{
						$amount=new amount();
						$WM_amount=$amount->get("amount");
						$amount_reserve=$WM_amount[2];
						$WM_amount=$WM_amount[1];
						require_once($GLOBALS['serverroot']."siti/courses.php");
						$courses=$GLOBALS['courses'];
						$transfer['av_balance']= min($amount_reserve[$transfer['type'].$transfer['currency']],
									($pnclass->balance($pn['id'])+$pn['limit'])*$courses['USD'][$transfer['currency']]*0.994);
						$transfer['course']=$courses['USD'][$transfer['currency']];
						$transfer=$pnclass->update_transfer($transfer);
						
						if ( $transfer['needcheck']==1 || $transfer['needcheck']==2 ) {
							$response[0]['message']="Требуется проверка принадлежности карты";
							$response[0]['state']=0;
						}else{
							$order=new orders();
							$response=$order->pay_pb($transfer['id'],$transfer['av_balance'],$transfer['test']);
						}
						
					}
				}
			}else{
				$response[0]['message']="Проверка подписи не прошла";
				$response[0]['state']=12;
				//die();
			}
		}else{
			$response[0]['message']="Невалидный IP-адрес отправителя: ".$_SERVER['REMOTE_ADDR'];
			$response[0]['state']=13;
		}
	}else{
		$response[0]['message']="Доступ запрещен";
		$response[0]['state']=17;
	}
	$response= "<response>
	<reqn>".(isset($transfer['reqn'])?$transfer['reqn']:"")."</reqn>
	<id>".(isset($response[0]['id'])?$response[0]['id']:"")."</id>
	<state>".(isset($response[0]['state'])?$response[0]['state']:"0")."</state>
	<desc>".(isset($response[0]['message'])?$response[0]['message']:"")."</desc>
	<ref>".(isset($response[0]['ref'])?$response[0]['ref']:"")."</ref>
	<summ>".(isset($transfer['summ'])?$transfer['summ']:"0")."</summ>
	<order_id>".(isset($transfer['order_id'])?$transfer['order_id']:"0")."</order_id>
	<currency>".(isset($transfer['currency'])?$transfer['currency']:"")."</currency>
	<account>".(isset($transfer['account'])?$transfer['account']:"")."</account>
	<code>".(isset($response[0]['code'])?$response[0]['code']:"")."</code>
	<md5>".$ourhash."</md5>
</response>";
	$pnclass->write_log((string)$xmlstr, $response, $transfer['partner'],$transfer['type']);
	echo $response;
/*
url - https://obmenov.com/partner/p24_transfer.php	
account - банк карта/счет, 16 цифр
fname - фамилия в utf-8
iname - имя  в utf-8
summ - сумма платежа
currency - валюта платежа
needcheck - 0/1 нужна ли проверка принадлежности ФИО банковской карте
hash - объединение строк reqn.type.secret.partner.order_id.account.summ.currency.needcheck
<request>
   <testmode>0</testmode>
   <reqn>7040820</reqn>
   <partner>1982</partner>
   <type>P24</type>
   <transfer>
       <order_id>46</order_id>
       <fname>Р»С‹СЃРѕРіРѕСЂРѕРІ</fname>
       <iname>РјР°РєСЃ</iname>
       <account>6762462602779495</account>
       <summ>5</summ>
       <currency>UAH</currency>
       <needcheck>0</needcheck>
   </transfer>
   <hash></hash>
</request>

----------------------
reqn - повторяет reqn в запросе данных, INT
type - P24
id - номер транзакции на стороне obmenov.com, INT
state - статус запроса, INT
// 1 - успешно, 
// 0 - не успешно, 
// 12 - подпись проверку не прошла, 
// 13- ip-адрес, 
// 14- запрос по заявке партнера уже выполнялся, 
// 15- переводы не разрешены
// 20- недостаточно баланса для завершения операции. в desc сообщается доступный баланс
desc - расширенное описание статуса. STRING
ref - референс платежа в приват24. STRING
code - код ответа привата. INT
<response>
   <reqn>7040820</reqn>
   <id>164068</id>
   <state>1</state>
   <desc></desc>
   <ref>aA4YA2509011383862</ref>
   <code></code>
</response>

*/

?>


