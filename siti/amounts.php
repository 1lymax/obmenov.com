<?php
	//require_once("../Connections/ma.php");
	require_once($serverroot."siti/class.php"); 	
	require_once($serverroot."siti/_header.php");
	@ini_set ("display_errors", true);
	//$ma = mysqli_connect($hostname_ma, $username_ma, $password_ma, $database_ma);
_query('SET character_set_database = cp1251',"");
_query('SET NAMES cp1251',"");
	$amount=new amount();
	$WM_amount=$amount->get("amount");
	$WM_amount_r=$WM_amount[2];
	$WM_amount=$WM_amount[1];
	//print_r($WM_amount);
	//die();

?>