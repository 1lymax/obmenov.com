<?php 
	require_once('../Connections/ma.php');
	@ini_set ("display_errors", true);
	require_once($serverroot.'siti/partner/class.php');
	error_reporting(0);
	$xmlstr = file_get_contents('php://input'); 
	$xmlstr=trim($xmlstr); // Проверяем, получено ли хоть что-нибудь
	if($xmlstr=="") { echo "Пустой вызов"; exit; }
	$xmlres = simplexml_load_string($xmlstr); // Проверяем валидность XML-пакета 
	if(!$xmlres) { echo "Невалидный XML"; exit; }
	//print_r($xmlres);
	$transfer['reqn']=isset($xmlres->reqn)?mysql_real_escape_string((string)$xmlres->reqn) : "";
	$transfer['type']=isset($xmlres->type)?(string)$xmlres->type : "";
	$transfer['partner']=isset($xmlres->partner)?(int)$xmlres->partner : 0; 
	$inhash=isset($xmlres->hash)?$xmlres->hash:"";
	
	
	//print_r($transfer);	
	$pnclass=new partner();
	$select="select * from partner where id=".$transfer['partner'];
	$query=_query($select,"");
	$pn=$query->fetch_assoc();
	$ourhash=strtolower(md5($transfer['reqn'].$transfer['type'].$transfer['partner'].$pn['secret']));
	$balance="<balance>\r\n";
	if ( mysql_num_rows($query)!=0 ) {
		if ( $_SERVER['REMOTE_ADDR']==$pn['server_ip'] ) {
			if ( $inhash==$ourhash) {
				$amount=new amount();
				$WM_amount=$amount->get("amount");
				$amount_reserve=$WM_amount[2];
				$WM_amount=$WM_amount[1];
				require_once($GLOBALS['serverroot']."siti/courses.php");
				$courses=$GLOBALS['courses'];
				$amount_reserve['P24UAH']= min($amount_reserve["P24UAH"],
							($pnclass->balance($pn['id'])+$pn['limit'])*$courses['USD']['UAH']*0.994);
				$amount_reserve['P24USD']= min($amount_reserve["P24USD"],$pnclass->balance($pn['id'])+$pn['limit']);
				$balance.="<P24USD>".$amount_reserve['P24USD']."</P24USD>\r\n";
				$balance.="<P24UAH>".round($amount_reserve['P24UAH'],2)."</P24UAH>\r\n";
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
	$balance.="</balance>\r\n";
	$response= "<response>\r\n<reqn>".$transfer['reqn']."</reqn>\r\n<state>".
	(isset($response[0]['state'])?$response[0]['state']:"1")."</state>\r\n<desc>".
	(isset($response[0]['message'])?$response[0]['message']:"")."</desc>\r\n".
	$balance."</response>";
	$pnclass->write_log((string)$xmlstr, $response, $transfer['partner'],$transfer['type']);
	echo $response;
	/*
запрос информации о доступном балансе
url - https://obmenov.com/partner/balance.php
hash - reqn.type.secret.partner
<request>
   <reqn>9944100</reqn>                  
   <partner>1982</partner>
   <type>balance</type>
   <hash></hash>
</request>

-----------

reqn - повторяет reqn в запросе данных, INT
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

<response>
<reqn>9944100</reqn>
<state>0</state>
   <desc></desc>
<balance>
<P24USD>649.38</P24USD>
<P24UAH>5196.14</P24UAH>
</balance>
</response>

*/
?>


