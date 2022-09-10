<?php require_once('Connections/ma.php');
mysql_select_db($database_ma, $ma);
_query('SET character_set_database = cp1251',"");
_query('SET NAMES cp1251',"");
require_once("siti/amounts.php");
//die();	 
$query_course_usd = "SELECT course_usd.id, course_usd.`min`, course_usd.`max`, course_usd.`date` FROM course_usd ORDER BY course_usd.`date` desc";
$course_usd =  _query($query_course_usd, 'iframe.php 7');
$row_course_usd = $course_usd->fetch_assoc();
$totalRows_course_usd = $course_usd->num_rows;
//курс рубля
$query_course_rur = "SELECT course_rur.id, course_rur.`min`, course_rur.`max`, course_rur.`date` FROM course_rur ORDER BY course_rur.`date` desc";
$course_rur = _query($query_course_rur, 'iframe.php 8');
$row_course_rur = $course_rur->fetch_assoc();
$totalRows_course_rur = $course_rur->num_rows;

//курс евро
$query_course_eur = "SELECT course_eur.id, course_eur.`min`, course_eur.`max`, course_eur.`date` FROM course_eur ORDER BY course_eur.`date` desc";
$course_eur = _query($query_course_eur, 'iframe.php 9');
$row_course_eur = $course_eur->fetch_assoc();
$totalRows_course_eur = $course_eur->num_rows;



$courses['USD']['UAH']=$row_course_usd['min']+($row_course_usd['max']-$row_course_usd['min'])/2;
$courses['UAH']['USD']=1/$courses['USD']['UAH'];
$courses['RUR']['UAH']=$row_course_rur['min']+($row_course_rur['max']-$row_course_rur['min'])/2;
$courses['UAH']['RUR']=1/$courses['RUR']['UAH'];
$courses['EUR']['UAH']=$row_course_eur['min']+($row_course_eur['max']-$row_course_eur['min'])/2;
$courses['UAH']['EUR']=1/$courses['EUR']['UAH'];
$courses['USD']['RUR']=$courses['USD']['UAH']/$courses['RUR']['UAH'];
$courses['RUR']['USD']=1/$courses['USD']['RUR'];
$courses['EUR']['USD']=$courses['EUR']['UAH']/$courses['USD']['UAH'];
$courses['USD']['EUR']=1/$courses['EUR']['USD'];
$courses['EUR']['RUR']=$courses['EUR']['UAH']/$courses['RUR']['UAH'];
$courses['RUR']['EUR']=1/$courses['EUR']['RUR'];

//курс wmuwmz

$WMZWMU_base = get_course_from_db ("WMZ", "WMU");
$courses['WMZ']['WMU'] = get_course_with_addon ('WMZ', 'WMU');
$courses['WMU']['WMZ'] = 1/$courses['WMZ']['WMU'];

//курс wmrwmz
$WMZWMR_base = get_course_from_db ("WMZ", "WMR");
$courses['WMZ']['WMR'] = get_course_with_addon ('WMZ', 'WMR');
$courses['WMR']['WMZ'] = 1/$courses['WMZ']['WMR'];

//курс wmewmz
$WMEWMZ_base = get_course_from_db ("WME", "WMZ");
$courses['WME']['WMZ'] = get_course_with_addon ('WME', 'WMZ');
$courses['WMZ']['WME'] = 1/$courses['WME']['WMZ'];

//курс wmuwme
$WMEWMU_base = get_course_from_db ("WME", "WMU");
$courses['WME']['WMU'] = get_course_with_addon ('WME', 'WMU');
$courses['WMU']['WME'] = 1/$courses['WME']['WMU'];

//курс wmewmr
$WMEWMR_base = get_course_from_db ("WME", "WMR");
$courses['WME']['WMR'] = get_course_with_addon ('WME', 'WMR');
$courses['WMR']['WME'] = 1/$courses['WME']['WMR'];

//курс wmuwmr
$WMRWMU_base = get_course_from_db ("WMR", "WMU");
$courses['WMR']['WMU'] = get_course_with_addon ('WMR', 'WMU');
$courses['WMU']['WMR'] = 1/$courses['WMR']['WMU'];

// недостающие курсы для схожих направлений
		$select="select name,type from currency";
		$query1=_query($select, "");
		$query2=_query($select, "");
		
		while ( $row1=mysql_fetch_array($query1) ) {
			$query2=_query($select, "");
			while ( $row2=mysql_fetch_array($query2) ) {
				if ( !isset($courses[$row1['name']][$row2['name']]) ) {
					if ( !isset($courses[$row1['type']][$row2['type']]) ) {
						$courses[$row1['name']][$row2['name']]=1;
					}else{
						$courses[$row1['name']][$row2['name']]=$courses[$row1['type']][$row2['type']];
					}
				}
				if ( $courses[$row1['name']][$row2['name']]=='') {
						$courses[$row1['name']][$row2['name']]=1;			
				}
				//echo "courses['".$row1['name']."']['".$row2['name']."']".$courses[$row1['name']][$row2['name']]."<br />";
			}
		
		}



$query_addon = "SELECT addon.id, addon.currname1, addon.currname2, 
		(select type from currency where addon.currname1=currency.name) as type1, 
		(select type from currency where addon.currname2=currency.name) as type2, 
		addon.`value`, addon.`date` FROM addon where inactive=0 and clientid=0";
$addon = _query($query_addon, 'function.php 16');
$row_addon = $addon->fetch_assoc();
$money = array();

$query_currency = "SELECT currency.name, currency.extname, type FROM currency where active=1 and currency.server ".$urlid['curr']." ORDER BY name desc";
$currency = _query($query_currency, 'function.php 17');


do {
	$in=$row_addon['currname1']; $out=$row_addon['currname2'];
	if ( !isset ($courses[$in][$out]) ) {
	$course=$courses[$row_addon['type1']][$row_addon['type2']];
	} else {
		$course=$courses[$in][$out];
	}
/*if (!$row_addon['value']==NULL){

	switch ($in){  //вычисление уникальности обменной пары
	case 'WMU':    	$in1=1;    	break;
	case 'P24UAH':    	$in1=1;    	break;
	case 'NSMEP':    	$in1=1;    	break;
	case 'UAH':    	$in1=1;    	break;
	case 'WMZ':    	$in1=3;    	break;
	case 'USD':    	$in1=3;    	break;
	case 'P24USD':    	$in1=3;    	break;
	case 'WMR':	    $in1=5;	    break;
	case 'WME':	    $in1=9;	    break;	}
	switch ($out){
	case 'WMU':    	$in2=1.1;    	break;
	case 'P24UAH':    	$in2=1.1;    	break;
	case 'UAH':    	$in2=1.1;    	break;
	case 'NSMEP':    	$in2=1.1;    	break;
	case 'WMZ':    	$in2=3.3;    	break;
	case 'USD':    	$in2=3.3;    	break;
	case 'P24USD':    	$in2=3.3;    	break;
	case 'WMR':	    $in2=5.5;	    break;
	case 'WME':	    $in2=9.9;	    break;	}
	switch ($in1+$in2){
	case 4.1:   $course=$uah_usd;    	break;
	case 4.3:   $course=1/$uah_usd;    	break;
	case 8.5:   $course=$usd_rur;    	break;
	case 12.9:	$course=1/$usd_eur;	    break;
	case 10.9:	$course=1/$uah_eur;	    break;
	case 6.1:	$course=$uah_rur;		break;
	case 6.5:	$course=1/$uah_rur;		break;
	case 8.3:	$course=1/$usd_rur;		break;
	case 14.9:	$course=1/$rur_eur;		break;
	case 10.1:	$course=$uah_eur;		break;
	case 12.3:	$course=$usd_eur;		break;
	case 14.5:	$course=$rur_eur;		break;
	default: $course=1;}

} 
	if ( substr($in,0,2)==substr($out,0,2) ) {
		$course=round($courses[$in][$out],4);
	}*/
	$money[$in][$out]['curr1']=$row_addon['currname1'];
	$money[$in][$out]['curr2']=$row_addon['currname2'];
	$money[$in][$out]['value']=$row_addon['value'];
	$money[$in][$out]['value1']=$course/$row_addon['value'];
	$money[$in][$out]['date']=$row_addon['date'];
	if ($money[$in][$out]['value']==NULL){
		$money[$in][$out]['value']=$row_addon['value'];
		$money[$in][$out]['date']=$row_addon['date'];}
	else { //удаление старых дат
		if ($row_addon['date']>$money[$in][$out]['date']){ 
		$money[$in][$out]['date']=$row_addon['date'];
		$money[$in][$out]['value']=$row_addon['value'];
		$money[$in][$out]['value1']=$course/$row_addon['value'];
		}
	}
	if ($money[$in][$out]['value1']==NULL){
		$money[$in][$out]['value1']=$course/$row_addon['value'];
		$money[$in][$out]['date']=$row_addon['date'];}
	else { //удаление старых дат
		if ($row_addon['date']>$money[$in][$out]['date']){ 
		$money[$in][$out]['date']=$row_addon['date'];
		$money[$in][$out]['value1']=$course/$row_addon['value'];
		}
	}

} while ($row_addon = mysql_fetch_assoc($addon));

?>
<html>
<head>
<title><?=get_setting('site_title'.$urlid['site_curr2'])?> </title>
<META NAME="webmoney.attestation.label" CONTENT="webmoney attestation label#9A5BBD24-A808-463F-8C34-37900FD41FCC">
<script language="javascript">
d = document;
alertmess='';
predel=new Array;
courses= new Array;
predel[1]=99.99;
predel[2]=999.99;
predel[3]=4999.99;


	<?php	while (list($key, $val) = each($courses)) {
			echo "courses['".$key."']=new Array;";
			while ( list($val1,$val2) = each($val) ) {
			echo "courses['".$key."']['".$val1."']=".$val2.";" ;
		   }
		}


?>


var C=0;
var course=0;
var ii=0;
var i=0;
money=new Array;
help=new Array;
WM_amount_r=new Array;
<?php 
$select="select name from currency where active2=0 ";
$query=_query($select,"");
while ($row=$query->fetch_assoc() ) { ?>
money['<?=$row['name']?>']=new Array;
<?php }


//вывод массива в яваскрипт
foreach ($money as $row1){
	foreach ($row1 as $row2){
		echo "money['".$row2['curr1']."']['".$row2['curr2']."']=".$row2['value'].";";
	}
}
?>

money['WMZ']['WMZ']=0;
money['WMU']['WMU']=0;
money['WMR']['WMR']=0;
money['WME']['WME']=0;
money['USD']['USD']=0;
money['UAH']['UAH']=0;
money['P24USD']['P24USD']=0;
money['P24UAH']['P24UAH']=0;
money['MCVUAH']['MCVUAH']=0;
money['MCVUSD']['MCVUSD']=0;
money['MCVRUR']['MCVRUR']=0;

<?php	
	$query_currency = "SELECT currency.name, currency.extname FROM currency where active=1 and currency.server ".$urlid['curr']." ORDER BY name desc";
	$currency = _query($query_currency, 'iframe.php 5');

while ( $currency_row = $currency->fetch_assoc() ) {
?>
WM_amount_r['<?=$currency_row['name']; ?>']="<?=isset ($WM_amount_r[$currency_row['name']]) ? "Резерв: ". $WM_amount_r[$currency_row['name']]. " ".$currency_row['extname'] : "" ;?>";
help['<?=$currency_row['name']; ?>']="";

<?php } ?>
help[1]="";
help[2]="";
help[3]="";
help['CARDUAH']="";
help['P24UAH']='<img src="i/question.gif" width="11" height="11" hspace="0" vspace="0" border="0" title="Если сумма заявки не превышает указанный резерв, средства будут переведены Вам в течении 3-х минут. Безусловно Вы можете указывать бОльшую сумму на вывод, чем фактический резерв по выбранному направлению." />';
help['P24USD']=help['P24UAH'];
function CheckDiscount (val, curr, course)
{	//alert (val+' '+curr+' '+course);
	Range=val*course;
	//if (curr=='WMZ' || curr=='USD' || curr=='P24USD' ) {Range=val}
	//if (curr=='WME' || curr=='EUR') {Range=val/course}
	//if (curr=='WMU' || curr=='UAH' || curr=='P24UAH') {
	//Range=val*course;}
	//if (curr=='WMR') {Range=val/course}
	
	if (Range<predel[1]) {
		if (Range*1.001>predel[1]){return 0.0011}
		return 0}
	if (Range>predel[1] && Range<predel[2]) {
		if (Range*1.002>predel[2]){return 0.002}
		return 0.001}
	if (Range>predel[2] && Range<predel[3]) {
		if (Range*1.003>predel[3]){return 0.003}
		return 0.002}
	if (Range>predel[3]) {return 0.003;}
}

function sum2USD(num)
{
	mIn=d.$("moneyIn").value;
	mOut=d.$("moneyOut").value;
	if ((num.id=="SumIn") && (mIn=="WMU" || mIn=="UAH" || mIn=="NSMEP" || mIn=="P24UAH")) {return num.value/courses['USD']['UAH'];}
	if ((num.id=="SumIn") && (mIn=="WMR")) {return num.value/courses['USD']['RUR'];}	
	if ((num.id=="SumIn") && (mIn=="WME")) {return num.value/courses['USD']['EUR'];}	
	if ((num.id=="SumOut") && (mOut=="WMU" || mOut=="UAH" || mOut=="NSMEP" || mOut=="P24UAH")) {return num.value/courses['USD']['UAH'];}
	if ((num.id=="SumOut") && (mOut=="WMR")) {return num.value/courses['USD']['RUR'];}	
	if ((num.id=="SumOut") && (mOut=="WME")) {return num.value/courses['USD']['EUR'];}	
	return num.value;
}

function CheckSumm(num,course,currency){
	C1=d.$("moneyIn").value;
	C2=d.$("moneyOut").value;
	C=courses[d.$("moneyIn").value][d.$("moneyOut").value];

	if (num.id=='SumIn') {return num.value*(1+CheckDiscount(sum2USD(num),C, course))*C/course;}
	if (num.id=='SumOut') {return num.value/C*course/(1+CheckDiscount(sum2USD(num),C, course));}


 }
 
 
 function makechange() {
	if (d.$("moneyOut").value != 'WMZ' || d.$("moneyOut").value != 'WMU' || d.$("moneyOut").value != 'WMR' || d.$("moneyOut").value != 'WME') {
	if ( d.$("moneyIn").value == 'WMZ' && parseFloat(d.$("SumIn").value) < 1 ) {
     alert("Минимальная сумма обмена 1 WMZ");return(false);}
	if ( d.$("moneyIn").value == 'WMU' && parseFloat(d.$("SumIn").value) < 5 ) {
     alert("Минимальная сумма обмена 5 WMU");return(false);}
	if ( d.$("moneyIn").value == 'WMR' && parseFloat(d.$("SumIn").value) < 30 ) {
     alert("Минимальная сумма обмена 30 WMR");return(false);}
	if ( d.$("moneyIn").value == 'WME' && parseFloat(d.$("SumIn").value) < 1 ) {
     alert("Минимальная сумма обмена 1 WME");return(false);}
    if ( d.$("moneyIn").value == 'WMZ' && parseFloat(d.$("SumIn").value) > 10000 ) {
     alert("Для обмена более 10000 WMZ свяжитесь с администрацией. ");return(false);}
	if ( d.$("moneyIn").value == 'WMU' && parseFloat(d.$("SumIn").value) > 70000 ) {
     alert("Для обмена более 70000 WMU свяжитесь с администрацией.");return(false);}
	if ( d.$("moneyIn").value == 'WMR' && parseFloat(d.$("SumIn").value) > 150000 ) {
     alert("Для обмена более 150000 WMR свяжитесь с администрацией.");return(false);}
	if ( d.$("moneyIn").value == 'WME' && parseFloat(d.$("SumIn").value) > 10000 ) {
     alert("Для обмена более 10000 WME свяжитесь с администрацией.");return(false);}
	}
	
    if ( d.$("moneyOut").value == 'KS' && parseFloat(d.$("SumOut").value) < <?=get_setting('ks_in_min_value')?> ) {
     alert("Минимальное пополнение 100 грн.");return(false);}


	if (d.$("moneyOut").value != 'USD' || d.$("moneyOut").value != 'UAH' || d.$("moneyOut").value != 'P24UAH' || d.$("moneyOut").value != 'P24USD') {
	if ( (d.$("moneyIn").value == 'USD' || d.$("moneyIn").value == 'P24USD') && parseFloat(d.$("SumIn").value) < 50 ) {
     alert("Минимальная сумма обмена 50 USD");return(false);}
	if ( (d.$("moneyIn").value == 'UAH' || d.$("moneyIn").value == 'P24UAH') && parseFloat(d.$("SumIn").value) < 250 ) {
     alert("Минимальная сумма обмена 250 гривен");return(false);}
    if ( (d.$("moneyIn").value == 'USD' || d.$("moneyIn").value == 'P24USD') && parseFloat(d.$("SumIn").value) > 10000 ) {
     alert("Для обмена более 10000 USD свяжитесь с администрацией. ");return(false);}
	if ( (d.$("moneyIn").value == 'UAH' || d.$("moneyIn").value == 'P24UAH') && parseFloat(d.$("SumIn").value) > 70000 ) {
     alert("Для обмена более 70000 гривен свяжитесь с администрацией.");return(false);}
	}
	

	if (alert_mess=='') {
	document.exchange.submit();}
	else {alert(alert_mess);}
	};
</script>
<script type="text/javascript" src="fun.js"></script>
<script src="iframe.js" type="text/javascript"></script>


<script language="javascript">
function printcourse(){
	//if (C==1) {C3=''} else {C3=" * "+round3(C)}
	dis=CheckDiscount(sum2USD(document.forms.exchange.SumIn),C, course);
	d.$("prcourse").innerHTML= "<div align='center'><b>1 " + 
	<?php if ( isset($_GET['in']) ) {
		echo 'd.$("moneyIn2").value + ';
	}else{
		echo 'd.$("moneyIn").options[d.$("moneyIn").options.selectedIndex].text + ';
	} ?>
	"</b> " + " => " + " <b>"+ round3(C/(money[d.$("moneyIn").value][d.$("moneyOut").value]-dis))+ " "+ 
	<?php if ( isset($_GET['out']) ) {
		echo 'd.$("moneyOut2").value + ';
	}else{	
		echo 'd.$("moneyOut").options[d.$("moneyOut").options.selectedIndex].text + ';
	} ?>
	"</b></div><div align='center'><b>" + round3(1/(C/(money[d.$("moneyIn").value][d.$("moneyOut").value]-dis)))+ " " +
	<?php if ( isset($_GET['in']) ) {
		echo 'd.$("moneyIn2").value + ';
	}else{
		echo 'd.$("moneyIn").options[d.$("moneyIn").options.selectedIndex].text + ';
	} ?>
	"</b> " + " => " + " <b>"+  " 1 "+ 
	<?php if ( isset($_GET['out']) ) {
		echo 'd.$("moneyOut2").value + ';
	}else{	
		echo 'd.$("moneyOut").options[d.$("moneyOut").options.selectedIndex].text + ';
	} ?>
	"</b></div>";
	d.$("amount").innerHTML=WM_amount_r[d.$("moneyOut").value];
	d.$("help").innerHTML=help[d.$("moneyOut").value];
	}  

</script>

<link rel="icon" href="http://obmenov.com/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="http://obmenov.com/favicon.ico" type="image/x-icon">
<link href="iframe.css" rel="stylesheet" type="text/css">
<?php require_once("Connections/meta.php"); ?>
</head>
<body onLoad="start();">

 <!--Основная таблица-->

<form action="specification.php?<?=isset($_GET['pn']) ? "pn=".htmlspecialchars($_GET['pn']) : ""?>" method="post" name="exchange" id="exchange" target="_blank">

<table border="0" align="center" cellpadding="0" width="320" cellspacing="0">
<tr><td height="75"></td></tr>
<tr><td height="95" valign="top" colspan="2" align="center">
	Автоматический ввод/вывод в Приватбанк!
	<input name="SumIn" type="text" id="SumIn" onMouseOver="show_input(this)" onMouseOut="hide_input(this)" 
    onFocus="focus_input(this)" onBlur="blur_input(this)"
    onKeyUp="CheckInOutSumm(this);" value="<?php echo isset($_GET['sin']) ? htmlspecialchars($_GET['sin']): '0'; ?>" size="8" maxlength="7"><br>

    
    <select name="moneyIn" size="1" id="moneyIn" onChange="CheckInOutSumm(d.$('SumIn'));" class="input_select" onFocus="d.$('moneyIn').style.borderColor='#666666';"  onMouseOver="d.$('moneyIn').style.borderColor='#666666';" onMouseOut="d.$('moneyIn').style.borderColor='#7E8F94';">
<?php 
$query_currency = "SELECT currency.name, currency.extname, type3 FROM currency where active=1 and currency.server 
 ".$urlid['curr']." and name!='KS' ORDER BY type3 asc, name desc";
	$currency = _query($query_currency, 'index.php 5');

$t=0;
	$curtype[1]="Электронные валюты";
	$curtype[2]="Наличные валюты";
	$curtype[3]="Банковские переводы";
	$curtype[4]="Денежные переводы";
	$curtype[5]="Мобильные деньги";
	$curtype[6]="Форекс-брокеры";
	while ($row=mysql_fetch_assoc($currency)) { 
		if ( $t != $row['type3'] ) { 
			$t=$row['type3'];
			?>
    		<option value="<?=$t?>" style="color:#999; font-weight:bold"><?=$curtype[$t]?></option>
        	<?php 
		}
	?>
	<option <?=($row['name']=="WMR" ? "selected" : "")?> value="<?php echo $row['name']; ?>"><?php echo $row['extname']; ?></option>
<?php } ?>
    </option>
    </select>
    
<div id="needtohave" align="center" style="font-size:10px;">
	<span>С учетом комиссии должно быть +0,8%:</span>
		<span id="needIn" style="color:#F00">0</span>&nbsp;<span > (минимум)</span>
    </div></td></tr>
<tr><td colspan="2" align="center">
	<img src="i/butt_off.gif" alt="Обмен!" name="butt" width="120" height="48" hspace="0" vspace="0" border="0" 
    id="butt_on" onClick="makechange();" onMouseOver="this.src='i/butt_on.gif'" 
    onMouseOut="this.src='i/butt_off.gif'" value="Обмен!">
    </td></tr>
	<tr><td align="center" colspan="2" valign="top" height="155">
	
<?php $query_currency = "SELECT currency.name, currency.extname, 
												currency.type3 FROM currency  WHERE 1=1 " . ($urlid['site_curr2']==1 ?
												" and left(name,3)!='MCV' " : ""). "
													and name!='SMS'
												 AND active=1 ORDER BY type3 asc, extname desc";
												$currency = _query($query_currency, 'index.php 5'); ?>
    <select name="moneyOut" size="1" id="moneyOut" onChange="CheckInOutSumm(d.$('SumOut'));" class="input_select" onFocus="d.$('moneyOut').style.borderColor='#666666';"  onMouseOver="d.$('moneyOut').style.borderColor='#666666';" onMouseOut="d.$('moneyOut').style.borderColor='#7E8F94';">
<?php
$t=0;
	$curtype[1]="Электронные валюты";
	$curtype[2]="Наличные валюты";
	$curtype[3]="Банковские переводы";
	$curtype[4]="Денежные переводы";
	$curtype[5]="Мобильная связь";
	$curtype[6]="Форекс-брокеры";
	while ($row=$currency->fetch_assoc()) { 
		if ( $t != $row['type3'] ) { 
			$t=$row['type3'];
			?>
    		<option value="<?=$t?>" style="color:#999; font-weight:bold"><?=$curtype[$t]?></option>
        	<?php 
		}
	?>
	<option <?=($row['name']=="WMZ" ? "selected" : "")?> value="<?php echo $row['name']; ?>"><?php echo $row['extname']; ?></option>
<?php } ?>
    </option>
    </select><br>

    <input name="SumOut" type="text" id="SumOut" onKeyUp="CheckInOutSumm(this);" value="0"
	onMouseOver="show_input(this)" onMouseOut="hide_input(this)" 
    onFocus="focus_input(this)" onBlur="blur_input(this)" size="7" maxlength="8" /><br>
    <span id="amount" style="font-size:10px; font-family:Verdana, Geneva, sans-serif"></span> <span id="help" style="font-size:10px; font-family:Verdana, Geneva, sans-serif"></span><br>
  
    
    <input type="hidden" name="order" value="ok">
    <?php if ( isset($_GET['pn']) ) { ?>
    <input type="hidden" name="pn" value="<?=htmlspecialchars($_GET['pn']); ?>">
    <?php } ?>
	<div id="divprcourse" style="font-size:10px; font-family:Verdana, Geneva, sans-serif"><span id="prcourse"></span></div>
     	<div id="mess" style="font-size:10px; font-family:Verdana, Geneva, sans-serif">
		Операция с одинаковыми валютами <br>или направление обмена временно <br>не поддерживается.</div>
	</td></tr>
    <tr><td>
		<div align="center">&copy; Обменный пункт <a href="http://obmenov.com"><?=get_setting('site_title'.$urlid['site_curr2'])?></a></div>
	</td>
	</tr>
</table>
    </form>
  
  