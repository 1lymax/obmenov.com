<?php require_once('../Connections/ma.php');
@ini_set ("display_errors", true);
//require_once('../function.php');
require_once('../siti/class.php');
require_once('../siti/_header.php');

$ip1="78.24.220.41"; // IP-����� ������� Fraud Inspector 
$ip2="82.146.43.7"; // IP-����� ������� Fraud Inspector 
$secretkey="adofjbieodubgwe9oiuhewjwe"; // ��� SecretKey, ������������� � ���������� FI 
// ��������� IP, � �������� ������ ������ 
if($_SERVER['REMOTE_ADDR']!=$ip1 && $_SERVER['REMOTE_ADDR']!=$ip2) { echo "������������������� ����� ".$_SERVER['REMOTE_ADDR']; exit; } // ��������� �������� XML-����� 
$xmlstr = file_get_contents('php://input'); $xmlstr=trim($xmlstr); // ���������, �������� �� ���� ���-������
if($xmlstr=="") { echo "������ �����"; exit; } // ��������� �������� XML-����� � ������� SimpleXML 
$xmlres = simplexml_load_string($xmlstr); // ��������� ���������� XML-������ 
if(!$xmlres) { echo "���������� XML"; exit; } // ��������� XML-����� �� ���������� 
$regn=$xmlres->regn; 
$reqtype=$xmlres->reqtype; 
$id=$xmlres->id; 
$user_id=$xmlres->user_id; 
$date_add=$xmlres->date_add;
$degree=$xmlres->degree;
$bonus=$xmlres->bonus; 
$desc=iconv("UTF-8", "CP1251", $xmlres->desc); 
$hash=$xmlres->hash; // ��������� ������ � ���������� ���������� � ������ �� ���������� ����������
$allvalues="";
foreach ($xmlres->param as $param) { 
	$pt=strval($param->ptype); 
	$pv=strval(iconv("UTF-8", "CP1251", $param->pvalue)); 
	// ��������� ������ �������� (����� �������������� ��� ������������ ����) 
	$allvalues.=$pv; 
	if($pt!="" && $pv!="") { 
		$param_types[]=$pt; $param_values[]=$pv; 
	} 
} 
// ��������� ��� 
$ourhash=strtolower(md5($regn.$id.$secretkey.$allvalues)); if($hash!=$ourhash) { echo "�������� ��� ".$hash; exit; } // ���� �� � ������� - ��������� ������ �������� 
if($reqtype=="newentry") { 
// ������ ������ � �� //.... //.... 
	while (list($k, $v) = each($param_types)) {
		$query="insert into fi (fi_id, userid,date,degree,bonus,descr,param,param_value)
				values ($id,$user_id,'$date_add',$degree,'$bonus','$desc','".
				$v."','".
				$param_values[$k]."'"
				.")";
		$query=_query($query,"");
	}
echo "OK"; }
elseif($reqtype=="updentry") { 
// ��������� ������ � �� //.... //.... 
	maildebugger(print_r($param_types,1).print_r($param_values,1));
	while (list($k, $v) = each($param_types)) {

		$query="update fi set date='$date_add',degree='$degree',bonus='$bonus',descr='', droped=0,
		param_value='".$param_values[$k]."' where fi_id=$id and param='$v'";
		$query=_query($query,"");
	}
echo "OK"; } elseif($reqtype=="delentry") { 
// ������� ������ �� �� //.... //.... 
	$query="update fi set droped=1 where fi_id=$id";
		$query=_query($query,"");
echo "OK"; } 
else { echo "�������� �������� reqtype"; } 
?>