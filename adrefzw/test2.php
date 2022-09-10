<?php 
	require_once("../Connections/ma.php");		
	require_once($serverroot.'function.php');
	require_once($serverroot."siti/class.php");		

$row['id']=299332;
$order= new orders();
$row_order=$order->order_query($row['id']);

$predel[1]=99.99;
$predel[2]=999.99;
$predel[3]=4999.99;
$row_order['sumOut']=$row_order['summout']+$row_order['discammount'];
$_SESSION['WmLogin_WMID']=219391095990;
$GLOBALS['closed_exchange']=1;
print_r($_SESSION);
if ( $row_order['authorized'] || isset($_SESSION['WmLogin_WMID']) ) {
	
	$select="select id from clients where clid='".$row_order['clid']."'";
	$query=_query($select,"index.php 55");
	$clrow=$query->fetch_assoc();
	$clientid_predel=$clrow['id'];
	//echo $select;
}else{
	$clientid_predel=0;
}	
	if ( isset($money[$row_order['currin']][$row_order['currout']]['addon_value']) 
									&& $money[$row_order['currin']][$row_order['currout']]['addon_value']==1 ) {
		$addon_value=0;
	}else { 
		$addon_value=(($row_discount['total']-1)/100);
	}
	$predel1=0.001;
	$predel2=0.002;	
	$predel3=0.003;
	// первый предел
	$select="select value from addon_predel where currname1='".$row_order['currin']."' and currname2='".$row_order['currout']."'
			AND type=1 AND clientid=0 order by date desc";
	$query=_query($select, "specification.php predel_addon 1");
	if ( mysql_num_rows($query) != 0 ) {
		$predel1=$query->fetch_assoc();
		$predel1=$predel1['value']/100;
	}
	if ( $clientid_predel!=0 ) {
		$select="select value from addon_predel where currname1='".$row_order['currin']."' and currname2='".$row_order['currout']."'
			AND type=1 AND clientid=".$clientid_predel." order by date desc";
		$query=_query($select, "specification.php predel_addon 1");
		if ( mysql_num_rows($query)!=0 ) {
			$predel1=$query->fetch_assoc();
			$predel1=$predel1['value']/100;	
		}
	} // конец первый предел
	
	
	// второй предел
	$select="select value from addon_predel where currname1='".$row_order['currin']."' and currname2='".$row_order['currout']."'
			AND type=2 AND clientid=0 order by date desc";
	$query=_query($select, "specification.php predel_addon 1");
	if ( mysql_num_rows($query) != 0 ) {
		$predel2=$query->fetch_assoc();
		$predel2=$predel2['value']/100;
	}
	if ( $clientid_predel!=0 ) {
		$select="select value from addon_predel where currname1='".$row_order['currin']."'
			and currname2='".$row_order['currout']."'
			AND type=2 AND clientid=".$clientid_predel." order by date desc";
		$query=_query($select, "specification.php predel_addon 2");
		if ( mysql_num_rows($query)!=0 ) {
			$predel2=$query->fetch_assoc();
			$predel2=$predel2['value']/100;	
		}
	} // конец второй предел	
	
	
	
	// третий предел
	$select="select value from addon_predel where currname1='".$row_order['currin']."' and currname2='".$row_order['currout']."'
			AND type=3 AND clientid=0 order by date desc";
	$query=_query($select, "specification.php predel_addon 1");
	if ( mysql_num_rows($query) != 0 ) {
		$predel3=$query->fetch_assoc();
		$predel3=$predel3['value']/100;
	}
	if ( $clientid_predel!=0 ) {
		$select="select value from addon_predel where currname1='".$row_order['currin']."' 
			and currname2='".$row_order['currout']."'
			AND type=3 AND clientid=".$clientid_predel." order by date desc";
		$query=_query($select, "specification.php predel_addon 3");
		if ( mysql_num_rows($query)!=0 ) {
			$predel3=$query->fetch_assoc();
			$predel3=$predel3['value']/100;	
		}
	} // конец третий предел
	
	
	foreach ($money as $row1){
		foreach ($row1 as $row2){

			if ( $row2['curr1']==$row_order['currin'] && $row2['curr2']==$row_order['currout'] ){
 				$check=$row_order['summin']*$courses[$row_order['currin']]["USD"];//$row2['value'];
				
				if ($check<$predel[1]) {$row_discountammount=0;
				if ($check*1.001>$predel[1]){$row_discountammount=$predel1+0.0001;}}
				if ($check>$predel[1] && $check<$predel[2]) {$row_discountammount=$predel1;}
				if ($check>$predel[2] && $check<$predel[3]) {$row_discountammount=$predel2;}
				if ($check>$predel[3]) {$row_discountammount=$predel3;}
				$tuUSD=round($check,2);
					$TheorSumm=round($row_order['summin']*$courses[$row_order['currin']][$row_order['currout']]/($row2['value']-$row_discountammount)*(1+$addon_value),2);
			}
		}
}
if ( substr($in,0,3)=="MCV" ) { 
	$percent_for_courses=0.02; 
}
if ( $out=="KS" ) { 
	$percent_for_courses=0.031; 
}
if ( $TheorSumm > round($row_order['sumOut'],2)*(1+$percent_for_courses) || 
		$TheorSumm < round($row_order['sumOut'],2)/(1+$percent_for_courses) ) {
	 echo "Ошибка проверки суммы обмена: ".$TheorSumm. ' = '.htmlspecialchars(round($row_order['sumOut'],2));
}
		
?>



