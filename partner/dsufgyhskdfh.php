<?php require_once('../Connections/ma.php');
$bank['ACRUB']="Альфабанк";
$bank['TBRUB']="Телебанк";
$bank['RUSSTRUB']="Русский Стандарт";
$bank['SVBRUB']="Связной Банк";
if ( isset($_GET['oid']) && isset($_GET['clid']) ) {
	$select="select fname, iname, oname, account, summin, currin, summout, discammount, currout, inn
					from orders where id=".(int)$_GET['oid']." and clid='".mysql_real_escape_string($_GET['clid'])."'";
	$query=_query($select,"");
	$row=$query->fetch_assoc();
	echo $row['fname']." ".$row['iname']." ".$row['oname']."\r\n";
	echo $row['account']."\r\n";
	if ( in_array($row['currin'],$GLOBALS['rbanks'])  ) {
		echo $row['summin']."\r\n";
		echo $bank[$row['currin']]."\r\n";
		echo "Ввод";
	}
	if ( in_array($row['currout'],$GLOBALS['rbanks']) ) {
		if ( $row['currout']=="TBRUB" ) {
			echo $row['inn']."\r\n";
		}
		echo ($row['summout']+$row['discammount'])."\r\n";
		echo $bank[$row['currout']]."\r\n";
		echo "Вывод";	
		
	}
}



?>