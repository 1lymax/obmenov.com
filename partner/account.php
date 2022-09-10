<?php 
	require_once('../Connections/ma.php');
	@ini_set ("display_errors", true);
	error_reporting(0);
	require_once($serverroot.'siti/partner/class.php');
	
	$xmlstr = file_get_contents('php://input'); 
	$xmlstr=trim($xmlstr); // ���������, �������� �� ���� ���-������
	if($xmlstr=="") { echo "������ �����"; exit; }
	$xmlres = simplexml_load_string($xmlstr); // ��������� ���������� XML-������ 
	if(!$xmlres) { echo "���������� XML"; exit; }
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
			$response[0]['message']="�������� ������� �� ������";
			$response[0]['state']=12;
		}
	}else{
		$response[0]['message']="���������� IP-����� �����������: ".$_SERVER['REMOTE_ADDR'];
		$response[0]['state']=13;
	}
	$response= "<response>\r\n<reqn>".$transfer['reqn']."</reqn>\r\n<state>".
	(isset($response[0]['state'])?$response[0]['state']:"1")."</state>\r\n<desc>".
	(isset($response[0]['message'])?$response[0]['message']:"")."</desc>\r\n".
	$answer."</response>";
	$pnclass->write_log((string)$xmlstr, $response, $transfer['partner'],$transfer['type']);
	echo $response;

/*
���������� ��������
partner=1982 // Changer.ru
reqn=��������� �����, �������� microtime()*10000000

��� ��� ���������� �����:
1. IP-����� ������� �������.
2. secret - ��������� �����, ����� �����������


*/

/* 
�������� �������������� ��� ���������� �����
url - https://obmenov.com/partner/account.php	
hash - reqn.type.secret.partner.fname.iname.account

<request>
   <reqn>2651590</reqn>
   <partner>1982</partner>
   <type>account</type>
   <fname>Тросюк</fname>
   <iname>Сергей</iname>
   <account>6762462039228264</account>
   <hash></hash>
</request>

---------------

reqn - ��������� reqn � ������� ������, INT
state - ������ �������, INT
// 1 - �������, 
// 0 - �� �������, 
// 12 - ������� �������� �� ������, 
// 13- ip-�����, 
// 14- ������ �� ������ �������� ��� ����������, 
// 15- �������� �� ���������
// 20- ������������ ������� ��� ���������� ��������. � desc ���������� ��������� ������
desc - ����������� �������� �������. STRING
fname - �������. STRING
iname - ���. STRING
account - ����. 16 ����
verified - 0 - �� ��������, 1 - ��������, 2 - �� �������������

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


