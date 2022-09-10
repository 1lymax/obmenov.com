<?php 
$nameIn['extname'] = isset($nameIn['extname']) ? $nameIn['extname'] : "";
$nameOut['extname'] = isset($nameOut['extname']) ? $nameOut['extname'] : "";
$imageIn['im'] = isset($nameIn['im']) ? $nameIn['im'] : "";
$imageOut['im'] = isset($nameOut['im']) ? $nameOut['im'] : "";

$valOut="";
if ( strlen($nameIn['extname'])==0 && strlen($nameOut['extname'])=="" ) {
	$only_mess="<h1 align='center'>Не выбрано направление обмена.</h1>
	<p align='center'>Выбрать направление обмена можно в <a href='".$siteroot."commission.php'>разделе тарифов и комиссий</a>.</p>";	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Моментальный обмен <?=isset($nameIn['extname']) ? $nameIn['extname'] : ""?> на <?=isset($nameOut['extname']) ? $nameOut['extname'] : ""?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
        <link rel="shortcut icon" href="<?=$siteroot?>favicon.ico"/>
		<meta name="description" content="Сервис по моментальному обмену <?=$nameIn['extname']?> на <?=$nameOut['extname']?> и других валют. Низкие комиссии. Очень удобная программа накопительных скидок. Ввод средств от 0%, вывод от 2%" />
		<meta name="keywords" content="<?php require_once($serverroot."siti/inc_meta_words.php"); ?>" />
		<link rel="stylesheet" href="<?=$siteroot?>style.css" type="text/css" media="screen" />
		<!--[if lte IE 7]><link rel="stylesheet" href="<?=$siteroot?>ie.css" type="text/css" media="screen" /><![endif]-->
        <style>
		<?php
       	if ( isset($_SESSION['AuthUsername']) ) {
		echo '.wrapper {background: url("'.$siteroot.'i/wrapper'.$urlid['site_ext'].'-auth.jpg") center 0 no-repeat;}';
		}else{
		echo '.wrapper {background: url("'.$siteroot.'i/wrapper'.$urlid['site_ext'].'.jpg") center 0 no-repeat;}';
		}
		?>
		</style>
        
	</head>
    <body onLoad="CheckInOutSumm(d.$('SummIn'));">

<script language="javascript">
var d;
d=document;
var pred=new Array();
var foc= new Array();
foc['SummIn']=0;
foc['SummOut']=0;
var alert_mess='';

function setneedIn() {

	 d.$("needIn").value = Math.round((d.$('SummIn').value*1.008+0.01)*100)/100;

}

function setTotal() {
	//alert (d.$("discount").value);
	//alert (d.$("SummOut").value);
	 d.$("total").value = round2(parseFloat(d.$("discount").value) + parseFloat(d.$('SummOut').value)); 
	 
}
function setDiscount () {
<?php if ( $num_row_client == 1 )    { ?>
	if ( typeof pred[d.$("moneyIn").value][d.$("moneyOut").value]=="undefined") {
		d.$("discount").value=round2(d.$("SummOut").value*<?=round(($row_discount['total']-1)/100,5)?>);
	}
	<?php } ?>
}
function setSummInFromNeedIn () {
	d.$("SummIn").value = Math.round((d.$('needIn').value/1.008)*100)/100;
}
function setSummOutFromTotal () {
	d.$("SummOut").value = round2(d.$("total").value/(1+parseInt(<?=round(($row_discount['total']-1)/100,5)?>)));
	d.$("discount").value=round2(d.$("SummOut").value*<?=round(($row_discount['total']-1)/100,5)?>);
}

function CheckInOutSumm (num){
 clear_num(num);
 if(CheckCurr()==1) {
 course=money[d.$("moneyIn").value][d.$("moneyOut").value];
  C=courses[d.$("moneyIn").value][d.$("moneyOut").value];
  if (num.id=='SummIn')  {d.$("SummOut").value=round2(CheckSumm(num,course,d.$("moneyIn").value));}
  if (num.id=='SummOut')  {d.$("SummIn").value=round2(CheckSumm(num,course,d.$("moneyOut").value));}


	
  if (isNaN(d.$("SummIn").value) || isNaN(d.$("SummOut").value) ) 
  {d.$("SummIn").value=0;d.$("SummOut").value=0;
  }
 }
  
  else{
	if (num.id=='SummOut')  {d.$("SummIn").value=0;}
  	if (num.id=='SummIn')  {d.$("SummOut").value=0;}
  }
 
 
setDiscount();
printcourse();
 
 }


function CheckCurr ()
{
	//d.$("bank").innerHTML='';
	var ii=d.$("moneyIn").value;
	var oo=d.$("moneyOut").value;
	var alert_mess='';
	if ((ii == oo) ||
             ((ii == "USD") ||(ii == "EUR") ||(ii == "UAH") ||(ii == "P24UAH"||(ii == "P24USD")))&&
             ((oo == "USD")||(oo == "EUR")||(oo == "UAH")||(oo == "P24UAH")||(oo == "P24USD")))
             {alert_mess='Недопустимая операция с одинаковыми или наличными валютами либо направление обмена не поддерживается!';
			 return 0;
	
	}
	return 1;
 
}
 
 function round2(val){
         return Math.round((val+0.0000001)*100)/100;
 }
function round3(val){
         return Math.round((val+0.0000001)*1000)/1000;
 }
 
function ShowRow(id, show) {
  if(d.$(id) != null) {
    if(show) d.$(id).style.display = '';
    else d.$(id).style.display = 'none';
  }
  else alert("id == null as ShowRow parameter");
}

function start(){
	//ShowRow('mess',!mess);
    //ShowRow('needtohave',needtohave);
	ShowRow('divprcourse',divprcourse);
	
	printcourse();
	CheckInOutSumm(d.$('SummIn'));
}
//end my
var t = new Array ();
var predel=new Array();
var courses=new Array();

predel[1]=99.99;
predel[2]=999.99;
predel[3]=4999.99;

	<?php	
	if ( isset($_SESSION['AuthUsername']) ) {
		$select="select id from clients where nikname='".$_SESSION['AuthUsername']."'";
		$query=_query($select,"index.php 55");
		$clrow=$query->fetch_assoc();
		$clientid_predel=$clrow['id'];
	}else{
		$clientid_predel=0;
	}
	if ( isset($moneyIn) && isset($moneyOut) ) {
		$key=$moneyIn;
		$val1=$moneyOut;
		$val2=$courses[$key][$val1];
		echo "courses['".$key."']=new Array ();";
				echo "courses['".$key."']['".$val1."']=".$val2.";" ;
				echo "pred['".$key."']=new Array ();";
				echo "pred['".$key."']['".$val1."']=new Array ();";
						for ( $i = 1; $i <= 3; $i++ ) {
							$select="select value from addon_predel where currname1='".$key."' and currname2='".$val1."'
								AND type=".$i." AND clientid=".$clientid_predel." order by date desc";
							$query=_query($select, "index.php 56");
							$row_predel=$query->fetch_assoc();
							$base_value=$row_predel['value'];
							$clientid_numrows=$query->num_rows;
							if ( $query->num_rows==0 && $clientid_predel!=0 ) {	
								$select="select value from addon_predel where currname1='".$key."' and currname2='".$val1."'
										AND type=".$i." AND clientid=0 order by date desc";
								$query=_query($select, "index.php predel_addon 1");
								$row_predel=$query->fetch_assoc();
								$base_value=$row_predel['value'];
							}
						if ( $query->num_rows>0 ) {
							if ( $i==1 ) {
								echo "pred['".$key."']=new Array;";
								echo "pred['".$key."']['".$val1."']=new Array ();";
								if ( $clientid_numrows>0 ) {
									echo "pred['".$key."']['".$val1."']['cl']=1;" ;
								}
							}							
							echo "pred['".$key."']['".$val1."'][".$i."]=".($base_value/100).";" ;
							$personal=1;
						}else{
							echo "pred['".$key."']['".$val1."'][".$i."]=".($i/1000).";" ;
							$personal=0;
						}
					}			

	
?>
var C=0;
var course=0;
var ii=0;
var i=0;
var money=new Array ();
var help=new Array ();
var WM_amount_r=new Array ();
var summs = new Array ();
summs['min']=new Array();summs['max']=new Array();
summs['min']['In']=new Array();summs['min']['Out']=new Array();
summs['max']['In']=new Array();summs['max']['Out']=new Array();
<?php 

//вывод массива в яваскрипт

if ( isset($money[$moneyIn][$moneyOut]['value'])) {
	echo "money['".$moneyIn."']=new Array ();";
	echo "money['".$moneyIn."']['".$moneyOut."']=".$money[$moneyIn][$moneyOut]['value'].";";
}
//maildebugger($money);

?>
courses['USD']=new Array ();
courses['USD']['UAH']=<?=$courses['USD']['UAH']?>;
courses['USD']['EUR']=<?=$courses['USD']['EUR']?>;
courses['USD']['RUR']=<?=$courses['USD']['RUR']?>;

<?php 
	$select="select direction, orders_predel.name, orders_predel.type, orders_predel.value from orders_predel, currency where orders_predel.name=currency.name ".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] );
	$query=_query($select,"");
	while ( $summs=$query->fetch_assoc() ) { ?>
summs['<?=$summs['type']?>']['<?=$summs['direction']?>']['<?=$summs['name']?>']=<?=$summs['value']?>;
<?php } ?>


<?php	
	$query_currency = "SELECT currency.name, currency.extname, currency.type3 FROM currency 
	WHERE active=1 
	".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] )."
	ORDER BY name desc";
	$currency = _query($query_currency, 'index.php 5');

while ( $currency_row = $currency->fetch_assoc() ) {
?>
WM_amount_r['<?=$currency_row['name']; ?>']="<?=isset ($WM_amount_r[$currency_row['name']]) ? $WM_amount_r[$currency_row['name']] : "" ;?>";
help['<?=$currency_row['name']; ?>']="";
<?php } 

	
	$query_currency = "SELECT currency.name, currency.extname, currency.type3 FROM currency 
	WHERE active=1 
	".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "
					and (currency.server ".$urlid['curr'].(isset ($_SESSION['AuthUsername'])? "or 1)":")") )." "."
	ORDER BY name desc";
	$currency = _query($query_currency, 'index.php 5');
?>
WM_amount_r['UAH']=7000.00;
WM_amount_r['USD']=1300.00;
WM_amount_r[1]="";
WM_amount_r[2]="";
WM_amount_r[3]="";
help[1]="";
help[2]="";
help[3]="";
help['CARDUAH']="";
help['P24UAH']='<img src="<?=$siteroot?>i/question.gif" width="11" height="11" hspace="0" vspace="0" border="0" title="Если сумма заявки не превышает указанный резерв, средства будут переведены Вам в течении 3-х минут. Безусловно Вы можете указывать бОльшую сумму на вывод, чем фактический резерв по выбранному направлению." />';
help['P24USD']=help['P24UAH'];

function CheckDiscount (val, curr, course) {	
	var Range=val*course;
	if ( pred[d.$("moneyIn").value][d.$("moneyOut").value]==undefined )	{
		//alert(";;;");
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
	}else{
		t[1]=pred[d.$("moneyIn").value][d.$("moneyOut").value][1];
		t[2]=pred[d.$("moneyIn").value][d.$("moneyOut").value][2];
		t[3]=pred[d.$("moneyIn").value][d.$("moneyOut").value][3];
		
		if (Range<predel[1]) {
			if (Range*(1+t[1])>predel[1]){return t[1]+0.0001}
			return 0}
		if (Range>predel[1] && Range<predel[2]) {
		if (Range*(1+t[2])>predel[2]){return t[2]}
			return t[1]}
		if (Range>predel[2] && Range<predel[3]) {
			if (Range*(1+t[3])>predel[3]){return t[3]}
			return t[2]}
		if (Range>predel[3]) {return t[3];}
	}
}

function sum2USD(num) {
	var mIn=d.$("moneyIn").value;
	var mOut=d.$("moneyOut").value;
	if ((num.id=="SummIn") && (mIn=="WMU" || mIn=="UAH" || mIn=="P24UAH")) {return num.value/courses['USD']['UAH'];}
	if ((num.id=="SummIn") && (mIn=="WMR" || mIn=="ACRUB" || mIn=="RUSSTRUB" || mIn=="TBRUB" || mIn=="SVBRUB")) {return num.value/courses['USD']['RUR'];}	
	if ((num.id=="SummIn") && (mIn=="WME")) {return num.value/courses['USD']['EUR'];}	
	if ((num.id=="SummOut") && (mOut=="WMU" || mOut=="UAH" || mOut=="P24UAH")) {return num.value/courses['USD']['UAH'];}
	if ((num.id=="SummOut") && (mOut=="WMR")) {return num.value/courses['USD']['RUR'];}	
	if ((num.id=="SummOut") && (mOut=="WME")) {return num.value/courses['USD']['EUR'];}	
	return num.value;
}
function CheckSumm(num,course,currency){
	var C1=d.$("moneyIn").value;
	var C2=d.$("moneyOut").value;
	var C=courses[d.$("moneyIn").value][d.$("moneyOut").value];
	//alert (C);
	if (num.id=='SummIn') {return num.value*(1+CheckDiscount(sum2USD(num),C, course))*C/course;}
	if (num.id=='SummOut') {return num.value/C*course/(1+CheckDiscount(sum2USD(num),C, course));}


 }
d.$ = function ( obj_id ) {
	var obj;
    if( document.all )
    {
        obj = document.all[obj_id];
    }
    else if( document.getElementById )
    {
        obj = document.getElementById(obj_id);
    }
    else if( document.getElementsByName )
    {
        obj = document.getElementsByName(obj_id);
    }
    return obj;
}

function rel(){
	document.getElementById('kap').src = '/kcaptcha/?' + Math.random()*10000000000;
	}

function clear_num(num){
  var rg = new RegExp('[^1234567890\.,]','gi');
  var rg2 = new RegExp('[,]','gi');
  if(num.value.match(rg)){
    num.value = num.value.replace(rg,'');
	}
	num.value = num.value.replace(rg2,'.');
}

function show_input(a){
  if (navigator.userAgent.indexOf('Opera') >= 0) {
    a.className="inputhoveropera"
  }else{
    a.className="inputhover"
  };
}
function hide_input(a){
  if(foc[a.id] == 0){
    a.className="";
  }
}
function focus_input(a){
  foc[a.id]=1;
}
function blur_input(a){
  foc[a.id]=0;
  hide_input(a);
}
 
 function makechange() {
	 if ( parseFloat(d.$("SummIn").value) < summs['min']['In'][d.$("moneyIn").value] ) {
     alert("Минимальная сумма обмена "+summs['min']['In'][d.$("moneyIn").value]+" "+d.$("moneyIn2").value);return(false);}
	 if ( parseFloat(d.$("SummOut").value) < summs['min']['Out'][d.$("moneyOut").value] ) {
     alert("Минимальная сумма обмена "+summs['min']['Out'][d.$("moneyOut").value]+" "+d.$("moneyOut2").value);return(false);}
	 if ( parseFloat(d.$("SummIn").value) > summs['max']['In'][d.$("moneyIn").value] ) {
     alert("Максимальная сумма обмена "+summs['max']['In'][d.$("moneyIn").value]+" "+d.$("moneyIn2").value);return(false);}
	 if ( parseFloat(d.$("SummOut").value) > summs['max']['Out'][d.$("moneyOut").value] ) {
     alert("Максимальная сумма обмена "+summs['max']['Out'][d.$("moneyOut").value]+" "+d.$("moneyOut2").value);return(false);}

	document.forms.exchange.action="<?=$siteroot?>specification.php";
	document.exchange.submit();
};

function printcourse(){
	//if (C==1) {C3=''} else {C3=" * "+round3(C)}
	var dis=CheckDiscount(sum2USD(document.forms.exchange.SummIn),C, course);
	//alert(C);
	d.$("prcourse").innerHTML= "<div align='center'><b>1 " + 
	<?php echo 'd.$("moneyIn2").value + ';?>
	"</b> " + " >> " + " <b>"+ round3(C/(money[d.$("moneyIn").value][d.$("moneyOut").value]-dis))+ " "+ 
	<?php echo 'd.$("moneyOut2").value + ';?>
	"</b></div><div align='center'><b>" + round3(1/(C/(money[d.$("moneyIn").value][d.$("moneyOut").value]-dis)))+ " " +
	<?php echo 'd.$("moneyIn2").value + '; ?>
	"</b> " + " >> " + " <b>"+  " 1 "+ 
	<?php echo 'd.$("moneyOut2").value + ';?>
	"</b></div>";
	
		d.$("amount").innerHTML=WM_amount_r[d.$("moneyOut").value];
		d.$("help").innerHTML=help[d.$("moneyOut").value];
d.$("discount").value=round2(d.$("SummOut").value*<?=round(($row_discount['total']-1)/100,5)?>);
	}  
<?php } ?>
</script>
	    <div class="wrapper">
            <div class="wrapper-inn">

                <?php require_once($serverroot."siti/inc_top.php");?>

                <div class="middle clear">

                    <!-- Start left column -->
                    <? require_once($serverroot."siti/inc_left.php");?>
                    <!-- End left column -->

                    <!-- Start central column -->
                    <div class="c-col">
                    <?php if ( $urlid['site_curr2']==1 ) { ?>
                   <h1>Уважаемые клиенты!<br />
При оформлении заявки, обращайте внимание на доступный резерв результирующей валюты.</h1>
<p>При недоступном резерве время выполнения заявки может увеличиваться.</p>
<div class="otzyv"></div>
<h1>Скоро! Ввод/вывод через российские банки: <br />
Альфабанк, Связной, ВТБ и Русский Стандарт!</h1>
<p>Следите за нашими новостями!</p>
<div class="otzyv"></div>
                    <?php } 
					if ( isset($only_mess) ) {
						echo $only_mess; }
					$valIn=''; $valIn1='';
					if ( isset($moneyIn) && isset($moneyOut) && !isset($only_mess) ) {

?>

<form method="post" name="exchange" id="exchange" onsubmit="makechange();">

<table width="500" border="0" align="center" cellpadding="2">
<tr><td rowspan="13" width="30"><td height="40" colspan="3" align="center" valign="bottom">
    <?php 	while ($row=$currency->fetch_assoc()) { 
				if ( $row['name'] == $moneyIn ) { 
	?>
			<input type="hidden" name="moneyIn" value="<?=htmlspecialchars($moneyIn)?>" id="moneyIn" />
			<input type="hidden" name="moneyIn2" id="moneyIn2" value="<?=$row['extname']?>" />
			<h1>Обмен <?=$row['extname']?> >>
			<? $valIn1=$row['name'];
				$valIn=$row['extname']; 
				}
			}

	
			$query_currency = "SELECT currency.name, currency.extname, currency.type3 FROM currency 
			WHERE 1=1 ".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] )."
			ORDER BY name desc";
			//WHERE active=1 
			//ORDER BY name desc";
			$currency = _query($query_currency, 'index.php 5');
			//$row=mysql_fetch_assoc($currency);

		while ($row=$currency->fetch_assoc()) {
			if ( $row['name'] == $moneyOut ) { ?>
            
		<?=str_replace("MasterCard/","",$row['extname'])?></h1>
		<input type="hidden" name="moneyOut" value="<?=htmlspecialchars($moneyOut)?>" id="moneyOut" />
		<input type="hidden" name="moneyOut2" id="moneyOut2" value="<?=$row['extname']?>" />
		<?php $valOut=str_replace("MasterCard/","",$row['extname']);
			}
		}	
	?></strong>
</td></tr>
<tr><td></td><td colspan="2" align="right">
<?php if ( $moneyOut!='KS' ) { ?>
<a href="<?=$siteroot?>exchange/<?=urlencode($nameOut['extname']."-".$nameIn['extname']);?>.html">Обратное направление <br />
<?=$valOut?> >> <?=$valIn?></a>
<?php } ?>
</td></tr>
<tr><td colspan="3" align="center" height="60" valign="middle">	
<div id="divprcourse"><span id="prcourse"></span></div></td></tr>

<tr><td align="right" height="25" valign="middle" class="cabinet-text">
	Вы заплатите</td><td width="130" align="right" class="form-normal2"> <input name="SummIn" id="SummIn" type="text" 
    onKeyUp="CheckInOutSumm(this);setneedIn();setDiscount();setTotal();" value="0" /></td>
    <td class="cabinet-text"> <img src="<?=$siteroot?>i/logos/<?=$imageIn['im']?>"/> <?=$valIn?>		
	</td></tr>
<?php if ( substr($valIn1,0,2)=="WM" ) { 
 ?>
	<tr><td></td><td align="right" height="25" valign="middle" class="form-normal2"><div id="needtohave" align="right">
	<input id="needIn" type="text" onKeyUp=" clear_num(this);setSummInFromNeedIn();CheckInOutSumm(d.$('SummIn'));setDiscount();setTotal();" value="0" /> 
    </div>
</td><td class="cabinet-text">c учетом комиссии <br />
Webmoney 0,8%</td></tr>
<?php } 
else {?>
<input id="needIn" type="hidden" value="0"  />
<?php } ?>

	<tr><td align="right" height="25" valign="middle" class="cabinet-text">
 
    Вы получите</td><td align="right" class="form-normal2" height="25"> <input name="SummOut" id="SummOut" type="text" onKeyUp="CheckInOutSumm(this);setneedIn();setDiscount();setTotal();" value="0"/></td><td class="cabinet-text"> <img src="<?=$siteroot?>i/logos/<?=$imageOut['im']?>"/> <?=$valOut?>
    </td></tr>
    <tr><td align="right" height="25" valign="middle" width="140" class="cabinet-text">Всего доступно</td><td align="right"><strong><span id="amount" class="cabinet-text"></span> <span id="help" class="cabinet-text"></span></strong>	
 	</td><td class="cabinet-text"> <img src="<?=$siteroot?>i/logos/<?=$imageOut['im']?>"/> <strong><?=$valOut?></strong></td></tr>
    
    <?php if ( isset($personal) && $personal==0 ) {?>
    <tr><td align="right" height="25" valign="middle" class="cabinet-text"> Дополнительная<br /> скидка (<?=($row_discount['total']*100-100)?>%) <strong><a href="<?=$siteroot?>register.php" title="Пройдите несложную процедуру регистрации и сразу получите скидку 2%!">>>></a></strong></td>
    <td align="right" class="form-normal2">
    <input name="discount" type="text" id="discount" 
    class="comm_in" readonly="true" value="0" />
</td><td class="cabinet-text"> <img src="<?=$siteroot?>i/logos/<?=$imageOut['im']?>"/> <?=$valOut?>
	</td>
	</tr>
    <?php }else{ ?>
    <input name="discount" type="hidden" id="discount" value="0" />
    <?php } ?>
        <tr><td align="right" height="25" valign="middle" class="cabinet-text"> <strong>Итого</strong></td>
        <td align="right" class="form-normal2">
    <input name="total" type="text" id="total" 
    value="0"  onkeyup="clear_num(this);setSummOutFromTotal();CheckInOutSumm(d.$('SummOut'));setneedIn();setDiscount();" />
</td><td class="cabinet-text"> <img src="<?=$siteroot?>i/logos/<?=$imageOut['im']?>"/> <?=$valOut?>
	</td>
	</tr>
 <tr><td></td><td align="left" colspan="2">Всю остальную информацию (номера кошельков, платежные и контактные реквизиты) Вам будет предложено ввести на следующем этапе.</td></tr>   
 <tr><td></td><td align="left"><?php /*<input name="" type="image" src="<?=$siteroot?>i/empty.gif" width="1" height="1"  style="border:none" />*/ ?>
 <input type="button" class="button1" value="Продолжить" onClick="makechange();">
   </td></tr>
 <tr><td></td><td align="left" colspan="2"><a href="<?=$siteroot?>commission.php">Остальные направления обменов>></a></td></tr>      
</table>
    <input type="hidden" name="order" value="ok">
    </form>
 <?php } ?>   
                      
                        
                    </div>
                    <!-- End central column -->

                    <!-- Start right column -->
                    <?php require_once($serverroot."siti/inc_right.php");?>
                    <!-- End right column -->

                </div>

                <?php require_once($serverroot."siti/inc_footer.php"); ?>

            </div>
	    </div>

	</body>
</html>