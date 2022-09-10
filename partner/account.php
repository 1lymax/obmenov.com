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
	$transfer['fname']=isset($xmlres->fname)?(string)$xmlres->fname : ""; 
	$transfer['iname']=isset($xmlres->iname)?(string)$xmlres->iname :"";
	$transfer['account']=isset($xmlres->account)?(string)$xmlres->account : "";
	$inhash=isset($xmlres->hash)?$xmlres->hash:"";
	
	
	//print_r($transfer);	
	$pnclass=new partner();
	$select="select * from partner where id=".$transfer['partner'];
	$query=_query($select,"");
	$pn=$query->fetch_assoc();
	$ourhash=strtolower(md5($transfer['reqn'].$transfer['type'].$transfer['partner'].
											$transfer['fname'].$transfer['iname'].$transfer['account'].$pn['secret']));
	$answer="";
	if ( $_SERVER['REMOTE_ADDR']==$pn['server_ip'] ) {
		if ( $inhash==$ourhash) {
			$transfer['fname']=mysql_real_escape_string(iconv("utf-8", "windows-1251",trim($transfer['fname'])));
			$transfer['iname']=mysql_real_escape_string(iconv("utf-8", "windows-1251",trim($transfer['iname'])));
			$transfer['account']=mysql_real_escape_string(iconv("utf-8", "windows-1251",trim($transfer['account'])));
			$select="select * from bank_accounts where 
								fname='".$transfer['fname']."' and
								iname='".$transfer['iname']."' and
								account='".$transfer['account']."'";
			$query=_query($select,"");
			$acc=$query->fetch_assoc();
			$answer.="<fname>".$transfer['fname']."</fname>\r\n";
			$answer.="<iname>".$transfer['iname']."</iname>\r\n";
			$answer.="<account>".$transfer['account']."</account>\r\n";
			if ( mysql_num_rows($query)==0 ) {
				$answer.="<verified>0</verified>\r\n";	
			}else{
				$answer.="<verified>".$acc['verified']."</verified>\r\n";
			}
		}else{
			$response[0]['message']="Проверка подписи не прошла";
			$response[0]['state']=12;
		}
	}else{
		$response[0]['message']="Невалидный IP-адрес отправителя: ".$_SERVER['REMOTE_ADDR'];
		$response[0]['state']=13;
	}
	$response= "<response>\r\n<reqn>".$transfer['reqn']."</reqn>\r\n<state>".
	(isset($response[0]['state'])?$response[0]['state']:"1")."</state>\r\n<desc>".
	(isset($response[0]['message'])?$response[0]['message']:"")."</desc>\r\n".
	$answer."</response>";
	$pnclass->write_log((string)$xmlstr, $response, $transfer['partner'],$transfer['type']);
	echo $response;

/*
постоянные величины
partner=1982 // Changer.ru
reqn=случайное число, например microtime()*10000000

для нас необходимо знать:
1. IP-адрес клиента запроса.
2. secret - секретное слово, нужно согласовать


*/

/* 
проверка принадлежности ФИО банковской карте
url - https://obmenov.com/partner/account.php	
hash - reqn.type.secret.partner.fname.iname.account

<request>
   <reqn>2651590</reqn>
   <partner>1982</partner>
   <type>account</type>
   <fname>РўСЂРѕСЃСЋРє</fname>
   <iname>РЎРµСЂРіРµР№</iname>
   <account>6762462039228264</account>
   <hash></hash>
</request>

---------------

reqn - повторяет reqn в запросе данных, INT
state - статус запроса, INT
// 1 - успешно, 
// 0 - не успешно, 
// 12 - подпись проверку не прошла, 
// 13- ip-адрес, 
// 14- запрос по заявке партнера уже выполнялся, 
// 15- переводы не разрешены
// 20- недостаточно баланса для завершения операции. в desc сообщается доступный баланс
desc - расширенное описание статуса. STRING
fname - фамилия. STRING
iname - имя. STRING
account - счет. 16 цифр
verified - 0 - не проверен, 1 - проверен, 2 - не соответствует

<response>
<reqn></reqn>
<state>1</state>
<desc></desc>
<fname></fname>
<iname></iname>
<account></account>
<verified></verified>
</response>

*/

?>


