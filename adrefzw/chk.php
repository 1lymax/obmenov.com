<?php 
require_once('../Connections/ma.php');
@ini_set ("display_errors", true);
require_once($serverroot.'siti/partner/class.php');
$pnclass=new partner();
$reqn=$pnclass->reqID(); // ���������� �������������� ����� INT
$secret="gfhdsfognsdfisngvidfsn"; // ������� �����
$partner="299"; // ����� ��������

if ( isset ($_GET['transfer']) && $_GET['transfer']=="1" ) {
							   

$type="P24"; // ��� ��������
$testmode=1; // �������� ����� 1/0
$order_id=135; // ����� ������ �� ������� �������� INT
$fname=iconv("windows-1251", "utf-8","���������"); // ������� � utf-8
$iname=iconv("windows-1251", "utf-8","������");  // ��� � utf-8
$summ=number_format(5,2,".",""); // ����� �������  FLOAT(6,2)
$account="5457092075049258"; // ���� � �������, 16����
$currency="UAH"; // ������ ������� UAH/USD
$needcheck=1;  // 1 - ��������� �������� ������������ ��� ���������� �����, 0 - ��� ��
$hash=strtolower(md5($reqn.$type.$partner.$order_id.$account.$summ.$currency.$needcheck.$secret));

$t= $pnclass->request("<request>
	<testmode>".$testmode."</testmode>
	<reqn>".$reqn."</reqn>
	<partner>".$partner."</partner>
	<type>".$type."</type>
	<transfer>
    	<order_id>".$order_id."</order_id>
    	<fname>".$fname."</fname>
        <iname>".$iname."</iname>
        <account>".$account."</account>
        <summ>".$summ."</summ>
        <currency>".$currency."</currency>
        <needcheck>".$needcheck."</needcheck>
	</transfer>
    <hash>".$hash."</hash>
</request>","https://obmenov.com/partner/p24_transfer.php");


print_r($t);
}
if ( isset($_GET['balance']) && $_GET['balance']==1 ) {
$type="balance"; // ��� �������

$hash=strtolower(md5($reqn.$type.$partner.$secret));

$t= $pnclass->request("<request>				  
	<reqn>".$reqn."</reqn>				  
	<partner>".$partner."</partner>
	<type>".$type."</type>
    <hash>".$hash."</hash>
</request>","https://obmenov.com/partner/balance.php");

print_r($t);

}

if ( isset($_GET['account']) && $_GET['account']==1 ) {
$type="account"; // ��� �������
$fname=iconv("windows-1251", "utf-8","������"); // ������� � utf-8
$iname=iconv("windows-1251", "utf-8","������"); // ��� � utf-8
$account=iconv("windows-1251", "utf-8","6762462039228264"); // ����� �����/�����, 16����
$hash=strtolower(md5($reqn.$type.$partner.$fname.$iname.$account.$secret));

$t= $pnclass->request("<request>
	<reqn>".$reqn."</reqn>
	<partner>".$partner."</partner>
	<type>".$type."</type>
	<fname>".$fname."</fname>
	<iname>".$iname."</iname>
	<account>".$account."</account>
    <hash>".$hash."</hash>
</request>","https://obmenov.com/partner/account.php");

print_r($t);

}

if ( isset($_GET['chk_transfer']) && $_GET['chk_transfer']==1 ) {
$type="chk_transfer"; // ��� �������
$order_id="1"; // ����� ������ �� ������� ��������
$id=""; // ����� ���������� �� ������� obmenov.com
$hash=strtolower(md5($reqn.$type.$partner.$order_id.$id.$secret));

$t= $pnclass->request("<request>
	<reqn>".$reqn."</reqn>
	<partner>".$partner."</partner>
	<type>".$type."</type>
	<order_id>".$order_id."</order_id>
	<id>".$id."</id>
    <hash>".$hash."</hash>
</request>","https://obmenov.com/partner/chk_transfer.php");


print_r($t);

}


?>
