<?php 
$dont_insert_client=1;
require_once('/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php');
require_once('/var/www/webmoney_ma/data/www/obmenov.com/function.php');
require_once('/var/www/webmoney_ma/data/www/obmenov.com/siti/amounts.php');


$query_addon = "SELECT addon.id, addon.value, addon.date, addon.inactive, max( if( addon.currname1 =  currency.name, currency.extname, NULL ) ) AS currin_ext, max( if( addon.currname2 = currency.name, currency.extname, NULL ) ) AS currout_ext, addon.currname1, addon.currname2, addon.exchtype, addon.manual
FROM addon
INNER JOIN currency ON addon.currname1 = currency.name
OR addon.currname2 = currency.name
WHERE clientid =0 and onsite_visible=0 and inactive=0
GROUP BY addon.id
ORDER BY addon.exchtype ASC";
$addon = _query($query_addon,"commision.php 1");
$row_addon = mysql_fetch_assoc($addon);

$query_currency = "SELECT currency.name, currency.server, currency.currency_id FROM currency
	ORDER BY name desc";
$currency = _query($query_currency, 'function.php 17');
while ($row=mysql_fetch_assoc($currency)) {
	$site_curr[$row['name']]=$row['server'];
	$id[$row['name']]=$row['currency_id'];
}

do {
	$in=$row_addon['currname1']; $out=$row_addon['currname2'];
if (!$row_addon['value']==NULL){

	$course=$courses[$in][$out];

	$money1[$in.$out]['curr1']=$row_addon['currname1'];
	$money1[$in.$out]['curr2']=$row_addon['currname2'];
	$money1[$in.$out]['curr1_ext']=$row_addon['currin_ext'];
	$money1[$in.$out]['curr2_ext']=$row_addon['currout_ext'];
	$money1[$in.$out]['exchtype']=$row_addon['exchtype'];
	$money1[$in.$out]['inactive']=$row_addon['inactive'];
	$money1[$in.$out]['manual']=$row_addon['manual'];
	$money1[$in.$out]['value']=$row_addon['value'];
	$money1[$in.$out]['value1']=$course/$row_addon['value'];
	$money1[$in.$out]['date']=$row_addon['date'];
	if ($money1[$in.$out]['value']==NULL){
		$money1[$in.$out]['value']=$row_addon['value'];
		$money1[$in.$out]['date']=$row_addon['date'];}
	else { //удаление старых дат
		if ($row_addon['date']>$money1[$in.$out]['date']){ 
		$money1[$in.$out]['date']=$row_addon['date'];
		$money1[$in.$out]['value']=$row_addon['value'];
		$money1[$in.$out]['value1']=$course/$row_addon['value'];
		}
	}
	if ($money1[$in.$out]['value1']==NULL){
		$money1[$in.$out]['value1']=$course/$row_addon['value'];
		$money1[$in.$out]['date']=$row_addon['date'];}
	else { //удаление старых дат
		if ($row_addon['date']>$money1[$in.$out]['date']){ 
		$money1[$in.$out]['date']=$row_addon['date'];
		$money1[$in.$out]['value1']=$course/$row_addon['value'];
		}
	}
}

} while ($row_addon = mysql_fetch_assoc($addon));

array_sort($money1, 'exchtype', 'curr1');
print_r($money1);
	require_once("/var/www/webmoney_ma/data/www/obmenov.com/siti/_header.php");
	require_once("/var/www/webmoney_ma/data/www/obmenov.com/siti/amounts.php");
	$WM_amount_r["UAH"]=10000;
	$WM_amount_r["USD"]=2000;
	$WM_amount_r['CARDUAH']=5000;

 //конец вывод резервов   

$fp = fopen('/var/www/webmoney_ma/data/www/obmenov.com/current_state.txt', 'w');
$fp2 = fopen('/var/www/webmoney_ma/data/www/obmenov.com/current_state2.txt', 'w');
$fp3 = fopen('/var/www/webmoney_ma/data/www/obmenov.com/estandards.txt', 'w');
$fp4 = fopen('/var/www/webmoney_ma/data/www/obmenov.com/estandards2.txt', 'w');
$fp5 = fopen('/var/www/webmoney_ma/data/www/obmenov.com/current_state_xml.txt', 'w');

$fp_biz = fopen('/var/www/webmoney_ma/data/www/obmenov.com/_current_state.txt', 'w');
$fp2_biz = fopen('/var/www/webmoney_ma/data/www/obmenov.com/_current_state2.txt', 'w');
$fp3_biz = fopen('/var/www/webmoney_ma/data/www/obmenov.com/_estandards.txt', 'w');
$fp4_biz = fopen('/var/www/webmoney_ma/data/www/obmenov.com/_estandards2.txt', 'w');
$fp5_biz = fopen('/var/www/webmoney_ma/data/www/obmenov.com/_current_state_xml.txt', 'w');

//print_r ($site_curr);
$WM_amount_r["CARDUSD"]=$WM_amount_r["MCVUSD"];
$WM_amount_r["CARDUAH"]=$WM_amount_r["MCVUAH"];
$WM_amount_r["CARDEUR"]=$WM_amount_r["MCVEUR"];
$WM_amount_r["CARDRUB"]=$WM_amount_r["MCVRUR"];

fwrite($fp5, "<rates>\n");
fwrite($fp5_biz, "<rates>\n");

foreach ($money1 as $row2){
if ( substr($row2['curr1'],0,3)=="MCV" )$row2['curr1']=str_replace("MCV","CARD",$row2['curr1']);
if ( substr($row2['curr2'],0,3)=="MCV" )$row2['curr2']=str_replace("MCV","CARD",$row2['curr2']);
if ( $row2['curr1']=="CARDRUR" )$row2['curr1']="CARDRUB";
if ( $row2['curr2']=="CARDRUR" )$row2['curr2']="CARDRUB";

if ( in_array($site_curr[$row2['curr1']],array(1)) || in_array($site_curr[$row2['curr2']],array(1)) ){
	fwrite($fp, $id[$row2['curr1']].", ".$id[$row2['curr2']].", ".$row2['value1'].", ".round($WM_amount_r[$row2['curr2']],2).";\n");
	fwrite($fp2, $id[$row2['curr1']].",".$id[$row2['curr2']].",".(1/$row2['value1']).",".round($WM_amount_r[$row2['curr2']],2).";\n"); 
	fwrite($fp3, $row2['curr1'].";".$row2['curr2'].";1;".$row2['value1'].";".round($WM_amount_r[$row2['curr2']],2)."\n");
	fwrite($fp4, $row2['curr1']." -> ".$row2['curr2'].": rate=".$row2['value1'].", reserve=".round($WM_amount_r[$row2['curr2']],2)."\n");
	fwrite($fp5, "<item>\n<from>".$row2['curr1']."</from>\n<to>".$row2['curr2']."</to>\n<in>1</in>\n<out>".$row2['value1']."</out>\n<amount>".round($WM_amount_r[$row2['curr2']],2)."</amount>\n<param>".(($row2['inactive']=="1" || $row2['manual']=="1")?"manual":"")."</param>\n</item>\n");
	
	//fwrite($fp_biz, $id[$row2['curr1']].", ".$id[$row2['curr2']].", ".$row2['value1'].", ".round($WM_amount_r[$row2['curr2']],2).";\n");
	//fwrite($fp2_biz, $id[$row2['curr1']].",".$id[$row2['curr2']].",".(1/$row2['value1']).",".round($WM_amount_r[$row2['curr2']],2).";\n"); 
	//fwrite($fp3_biz, $row2['curr1'].";".$row2['curr2'].";1;".$row2['value1'].";".round($WM_amount_r[$row2['curr2']],2)."\n");
	//fwrite($fp4_biz, $row2['curr1']." -> ".$row2['curr2'].": rate=".$row2['value1'].", reserve=".round($WM_amount_r[$row2['curr2']],2)."\n");
}elseif ( in_array($site_curr[$row2['curr1']],array(2)) || in_array($site_curr[$row2['curr2']],array(2)) ){
	//fwrite($fp, $id[$row2['curr1']].", ".$id[$row2['curr2']].", ".$row2['value1'].", ".round($WM_amount_r[$row2['curr2']],2).";\n");
	//fwrite($fp2, $id[$row2['curr1']].",".$id[$row2['curr2']].",".(1/$row2['value1']).",".round($WM_amount_r[$row2['curr2']],2).";\n"); 
	//fwrite($fp3, $row2['curr1'].";".$row2['curr2'].";1;".$row2['value1'].";".round($WM_amount_r[$row2['curr2']],2)."\n");
	//fwrite($fp4, $row2['curr1']." -> ".$row2['curr2'].": rate=".$row2['value1'].", reserve=".round($WM_amount_r[$row2['curr2']],2)."\n");
	
	fwrite($fp_biz, $id[$row2['curr1']].", ".$id[$row2['curr2']].", ".$row2['value1'].", ".round($WM_amount_r[$row2['curr2']],2).";\n");
	fwrite($fp2_biz, $id[$row2['curr1']].",".$id[$row2['curr2']].",".(1/$row2['value1']).",".round($WM_amount_r[$row2['curr2']],2).";\n"); 
	fwrite($fp3_biz, $row2['curr1'].";".$row2['curr2'].";1;".$row2['value1'].";".round($WM_amount_r[$row2['curr2']],2)."\n");
	fwrite($fp4_biz, $row2['curr1']." -> ".$row2['curr2'].": rate=".$row2['value1'].", reserve=".round($WM_amount_r[$row2['curr2']],2)."\n");
	fwrite($fp5_biz, "<item>\n<from>".$row2['curr1']."</from>\n<to>".$row2['curr2']."</to>\n<in>1</in>\n<out>".$row2['value1']."</out>\n<amount>".round($WM_amount_r[$row2['curr2']],2)."</amount>\n<param>".(($row2['inactive']=="1" || $row2['manual']=="1")?"manual":"")."</param>\n</item>\n");
	//echo $row2['curr1']." -> ".$row2['curr2'].": rate=".$row2['value1'].", reserve=".round($WM_amount_r[$row2['curr2']],2)."\n";
}


}//echo "hello";
fwrite($fp5,"</rates>");
fwrite($fp5_biz,"</rates>");

fclose($fp);
fclose($fp2);
fclose($fp3);
fclose($fp4);
fclose($fp5);

fclose($fp_biz);
fclose($fp2_biz);
fclose($fp3_biz);
fclose($fp4_biz);
fclose($fp5_biz);

//prepaid
$select="SELECT item_name.id AS item_id, item_name.name, item_name.price, item_name.unit, COUNT(items.itemid) AS total, (SELECT COUNT(items.itemid) FROM items WHERE items.state='Y' AND items.itemid=item_name.id GROUP by items.itemid) as amount FROM items, item_name WHERE items.itemid=item_name.id GROUP by items.itemid ORDER BY item_name.name";
$query=_query($select,"");
$fp4 = fopen('/var/www/webmoney_ma/data/www/obmenov.com/prepaid_amount.txt', 'w');
while ( $item=$query->fetch_assoc() ) {
	if ( $item['unit']=="WMU" ) {
		$wmzprice=(round($item['price']/$courses['WMZ']['WMU']*1.008,2));
		$wmrprice=(round($item['price']/$courses['WMR']['WMU'],2));
		$wmuprice=$item['price'];
		$wmeprice=round($item['price']/$courses['WME']['WMU'],2);
							   
	}
	if ( $item['unit']=="WMZ" ) {
		$wmzprice=$item['price'];
		$wmrprice=(round($item['price']*$courses['WMZ']['WMR'],2));
		$wmuprice=(round($item['price']*$courses['WMZ']['WMU'],2));
		$wmeprice=(round($item['price']/$courses['WME']['WMZ'],2));
	}
	fwrite($fp4, $item['name'].",".$item['amount'].",".$wmzprice.",".$wmrprice.",".$wmuprice.",".$wmeprice.";\n");
// 	echo $item['name'].",".$item['amount'].",".$wmzprice.",".$wmrprice.",".$wmuprice.",".$wmeprice."<br />";
}
fclose($fp4);


?>