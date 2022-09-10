<?php require_once('../Connections/ma.php');
    require_once($serverroot."function.php");
//RewriteEngine On
//RewriteRule ^([^/]*)/([^/]*)\.html$ /exchange/index.php?type=$1&exchange=$2 [L]
	
if ( isset($_GET['type']) && $_GET['type']=="exchange" ) {
	
	$uri=isset($_GET['exchange'])?$_GET['exchange']:"";
	//maildebugger($uri);
	//$uri=urldecode($uri);
	//maildebugger($uri);
	$curr=explode("-",$uri);
	$moneyIn=$curr[0];
	$moneyOut=$curr[1];
	//$moneyIn=iconv("utf-8","windows-1251",$curr[0]);
	//$moneyOut=iconv("utf-8","windows-1251",$curr[1]);
	$select="select extname, name, im from currency where extname='".$moneyIn."' and active=1";
	$query=_query($select,"exchange/".$moneyIn."-".$moneyOut." 1");
	$nameIn=$query->fetch_assoc();
	$moneyIn=$nameIn['name'];
	$select="select extname, name, im from currency where extname='".$moneyOut."' and active =1";
	$query=_query($select,"exchange/".$moneyIn."-".$moneyOut." 1");
	$nameOut=$query->fetch_assoc();
	$moneyOut=$nameOut['name'];
	if ( !isset($money[$moneyIn][$moneyOut]['value']) ){
		$only_mess="<h1 align='center'>Не выбрано направление обмена.</h1>
		<p align='center'>Выбрать направление обмена можно в <a href='".$siteroot."commission.php'>разделе тарифов и комиссий</a>.</p>";
	}
	
}else{
	$uri=$_SERVER['REQUEST_URI'];

	while (list($key, $val) = each($courses)) {

				while ( list($val1,$val2) = each($val) ) {
					
					if ( strpos($uri,$key."-".$val1)!=0 ) {
						/*	$moneyIn=substr($_SERVER['REQUEST_URI'],10,strpos($key."-".$val1,"-"));
							$moneyOut=substr($_SERVER['REQUEST_URI'],strpos($key."-".$val1,"-")+11, 
														strlen($val1));*/
						$moneyIn=mysqli_real_escape_string($ma,substr($key."-".$val1,0,strpos($key."-".$val1,"-")));
						$moneyOut=mysqli_real_escape_string($ma,substr($key."-".$val1,strpos($key."-".$val1,"-")+1));
						$select="select extname, im from currency where name='".$moneyIn."'";
						$query=_query($select,"exchange/".$moneyIn."-".$moneyOut." 1");
						$nameIn=$query->fetch_assoc();
						$select="select extname, im from currency where name='".$moneyOut."'";
						$query=_query($select,"exchange/".$moneyIn."-".$moneyOut." 1");
						$nameOut=$query->fetch_assoc();
						
						
					}
			   }
	}
}
reset($courses);
	//echo $moneyIn." - ".$moneyOut;
require_once($serverroot."siti/inc_exchange.php");
	?>