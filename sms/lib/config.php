<?php
### �������� ����������� ����� �������� �� ����, ���� ��� ���������� ###
print_r($_POST);
define("SERVICE", 10167); // sms:bank id		������������� ���:�����
define("URL", "http://service.smscoin.com/json/bank/".SERVICE."/"); 
$purse		= SERVICE;			  

// service secret code
// ��������� ��� �������
$secret_code = "sdkfjhvbdkfhbveirugwaesv";

// initializing variables
// �������������� ����������
$order_id	 = 1234;		   // operation id	   ������������� ��������
$clear_amount = 0;			  // billing algorithm  �������� �������� ���������
$description  = "demo payment"; // operation desc	 �������� ��������
$submit	   = "���������";	// submit label	   ������� �� ������ submit

?>
