<?php require_once('Connections/ma.php');
//if ( !isset($_SERVER['HTTPS']) ) {header("Location: https://obmenov.com/index.php?".$_SERVER['QUERY_STRING']); }

require_once($serverroot."siti/class.php");

$urlid['index']="47025bb6-5500-48af-8654-9c9000ff2f76";
mysql_select_db($database_ma, $ma);
if (!isset($_SESSION)) {
  session_start();
}
$pn=", 299";
$partnerid=299;
if ( isset($_COOKIE['pn']) ) {
	$pn=", ".htmlspecialchars($_COOKIE['pn']);
	$partnerid=htmlspecialchars($_COOKIE['pn']);
	}
$clientid = ( isset($_COOKIE['clid']) ) ? htmlspecialchars($_COOKIE['clid']) : '' ;

if ( isset($_GET['pn']) ) {
	setcookie("pn",htmlspecialchars($_GET['pn']),time()+60*60*24*60);
	$partnerid=" ,".htmlspecialchars($_GET['pn']);
	$pn=htmlspecialchars($_GET['pn']);
	}
if ( (isset($_REQUEST['utm_source']) && $_REQUEST['utm_source']=='begun') || strpos($_SERVER['QUERY_STRING'], "openstat")!=0 ) {
	setcookie ("pn",342,time()+60*60*24*60);
	$pn=" , 342";
	$partnerid=342;
}

if ( !isset($_GET['pn']) && !isset($_COOKIE['pn']) ){
	$pn=", 299";
	$partnerid=299;
	setcookie ("pn",299,time()+60*60*24*60);
}



$referer= isset ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
$user_agent= isset ($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
if ( strpos($referer, "webmoney.ru/rus/cooperation/exchange/onlinexchange")!=0 ) {
	setcookie ("pn",355,time()+60*60*24*60);
	$pn=" , 355";
	$partnerid=355;
}	
if ( strpos($referer, "forum.searchengines.ru")!=0 ) {
	setcookie ("pn",492,time()+60*60*24*60);
	$pn=" , 492";
	$partnerid=492;
}
if ( strpos($referer, "owebmoney.ru")!=0 ) {
	setcookie ("pn",1116,time()+60*60*24*60);
	$pn=" , 1116";
	$partnerid=1116;
}


if ( isset($_GET['pn']) ) {
	setcookie("pn",htmlspecialchars($_GET['pn']),time()+60*60*24*60);
	$clid= isset($_COOKIE['clid']) ? htmlspecialchars($_COOKIE['clid']) : session_id();
	$_SESSION['ref']=$referer;
	$pn=" , ".(int)$_GET['pn'];
	$partnerid=(int)$_GET['pn'];

	}
	
//if (!isset($_COOKIE['clid']) && !isset($_SESSION['clid']) ){
	date_default_timezone_set('Europe/Helsinki');
//	$check_client="SELECT id FROM clients WHERE clients.clid='".session_id()."';";
//	$check_client_sql=_query($check_client, "index.php 1.5");
//	$numrows_check_client=mysql_num_rows($check_client_sql);
//	if ( $numrows_check_client !=1 ) {
//		$clientid = sprintf("INSERT INTO clients (clid, date, partnerid) VALUES (%s, %s,  ".intval($partnerid).")",
//							GetSQLValueString(session_id(),"text"),
//							GetSQLValueString(date("Y-m-d H:i:s"), "date"));
//
//		_query($clientid, 'index.php 2');
//	}
//	if ( isset($_COOKIE['PHPSESSID']) ){
//	setcookie("clid",htmlspecialchars($_COOKIE['PHPSESSID']),time()+60*60*24*60);}
//}
require_once($serverroot.'function.php');
require_once($serverroot."siti/amounts.php");
$message=' ';
if ( isset($_GET['success']) ) {$message= "<div id='head_small'><strong>Спасибо за использование <br>
услуг нашего сервиса.<br></strong></div>";}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title'.$urlid['site_curr2'])?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
        <meta name="w1-verification" content="150449552204" />
		<meta name="description" content="<?php require_once($serverroot."siti/inc_meta_descr.php"); ?>" />
		<meta name="keywords" content="<?php require_once($serverroot."siti/inc_meta_words.php"); ?>" />
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
        <link rel="shortcut icon" href="<?=$siteroot?>i/favico.ico"/>
		<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
		<!--[if lte IE 7]><link rel="stylesheet" href="ie.css" type="text/css" media="screen" /><![endif]-->
        <style>
		<?php
       	if ( isset($_SESSION['AuthUsername']) ) {
		echo '.wrapper {background: url("i/wrapper'.$urlid['site_ext'].'-auth.jpg") center 0 no-repeat;}';
		}else{
		echo '.wrapper {background: url("i/wrapper'.$urlid['site_ext'].'.jpg") center 0 no-repeat;}';
		}
		?>
		</style>
        <script type="text/javascript" src="fun.js"></script>
		<script src="_main.js" type="text/javascript"></script>
        <script language="javascript">
d = document;
var alertmess='';
var predel=new Array ();
var pred=new Array ();
var courses=new Array ();


predel[1]=99.99;
predel[2]=999.99;
predel[3]=4999.99;

	<?php	
		$query_currency = "SELECT currency.name, currency.server FROM currency
			ORDER BY name desc";
		$currency = _query($query_currency, 'function.php 17');
		while ($row=$currency->fetch_assoc()) {
			$site_curr[$row['name']]=$row['server'];
		}
	
			if ( isset($_SESSION['AuthUsername']) ) {
				$clientid_predel=$_SESSION['clid_num'];
			}else{
				$clientid_predel=0;
			}
			while (list($key, $val) = each($courses)) {
				if ( (isset($site_curr[$key]) && 
						!in_array($site_curr[$key],$urlid['site_curr'])) && !$closed_exchange && $urlid['site_curr2']!=$closed_exchange_site )continue; 
				echo "courses['".$key."']=new Array ();";
				echo "pred['".$key."']=new Array ();";
				while ( list($val1,$val2) = each($val) ) {
					if ( (isset($site_curr[$val1]) && 
						!in_array($site_curr[$val1],$urlid['site_curr']))  && !$closed_exchange 
						&& $urlid['site_curr2']!=$closed_exchange_site )continue;
					echo "courses['".$key."']['".$val1."']=".$val2.";" ;
					for ( $i = 1; $i <= 3; $i++ ) {
							$select="select value from addon_predel where currname1='".$key."' and currname2='".$val1."'
												AND type=".$i." AND clientid=".$clientid_predel." order by date desc";
							$query=_query($select, "index.php 56");
							$row_predel=$query->fetch_assoc();
							$base_value=$row_predel['value'];
							if ( $clientid_predel!=0 ) {
								$clientid_numrows=$query->num_rows;
							}else { 
								$clientid_numrows=0;
							}
							// 
							if ( $query->num_rows==0 && $clientid_predel!=0 ) {	
								$select="select value from addon_predel where currname1='".$key."' and currname2='".$val1."'
										AND type=".$i." AND clientid=0 order by date desc";
								$query=_query($select, "index.php predel_addon 1");
								$row_predel=$query->fetch_assoc();
								$base_value=$row_predel['value'];
							}
							if ( $query->num_rows>0 ) {
								if ( $i==1 ) {
									echo "pred['".$key."']['".$val1."']=new Array ();";
									if ( $clientid_numrows>0 ) {
										echo "pred['".$key."']['".$val1."']['cl']=1;" ;
									}
								}							
								echo "pred['".$key."']['".$val1."'][".$i."]=".($base_value/100).";" ;
						//}else{
							//echo "predel['".$key."']['".$val1."'][".$i."]=".($i/1000).";" ;
							}
					}
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
	$select="select direction, orders_predel.name, orders_predel.type, orders_predel.value from orders_predel, currency where orders_predel.name=currency.name ".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] );
	$query=_query($select,"");
	while ( $summs=$query->fetch_assoc() ) { ?>
summs['<?=$summs['type']?>']['<?=$summs['direction']?>']['<?=$summs['name']?>']=<?=$summs['value']?>;
<?php } ?>
	
	
<?php 
$select="select name from currency where active2=0 ";//.($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] );
$query=_query($select,"");
while ($row=$query->fetch_assoc() ) { ?>
money['<?=$row['name']?>']=new Array;
<?php }

//вывод массива в яваскрипт
foreach ($money as $row1){
	foreach ($row1 as $row2){
		if ( in_array($row2['curr1']."-".$row2['curr2'],$closed_directions) ) {
		}else{
			if ( isset($site_curr[$row2['curr1']]) &&
						( !in_array($site_curr[$row2['curr1']],$urlid['site_curr']) ||
						  !in_array($site_curr[$row2['curr2']],$urlid['site_curr']) 
						 ) && !$closed_exchange && $urlid['site_curr2']!=$closed_exchange_site
			)continue;
			if ( !$closed_exchange && $row2['inactive']==1 ) continue;
		}
		echo "money['".$row2['curr1']."']['".$row2['curr2']."']=".$row2['value'].";";
	}
}

$select="select name from currency where active2=0 ".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] );
$query=_query($select,"");
while ($row=$query->fetch_assoc() ) { ?>
money['<?=$row['name']?>']['<?=$row['name']?>']=0;
<?php }


	$query_currency = "SELECT currency.name, currency.extname, currency.type3 FROM currency 
	
	WHERE active=1 ".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] )." ORDER BY name desc";//	ORDER BY name desc";
	$currency = _query($query_currency, 'index.php 5');

while ( $currency_row = $currency->fetch_assoc() ) {
?>
WM_amount_r['<?=$currency_row['name']; ?>']="<?=isset ($WM_amount_r[$currency_row['name']]) ? "Резерв: ". $WM_amount_r[$currency_row['name']]. " ".$currency_row['extname'] : "" ;?>";
help['<?=$currency_row['name']; ?>']="";
<?php } 

	$query_currency = "SELECT currency.name, currency.extname, currency.type3 FROM currency 
	
	WHERE active=1 ".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] )." ORDER BY name desc";//ORDER BY name desc";	
	$currency = _query($query_currency, 'index.php 5');

?>
var t = new Array ();
WM_amount_r[1]="";
WM_amount_r[2]="";
WM_amount_r[3]="";
help[1]="";
help[2]="";
help[3]="";
help['MCVUAH']="";
help['P24UAH']='<img src="i/question.gif" width="11" height="11" hspace="0" vspace="0" border="0" title="Если сумма заявки не превышает указанный резерв, средства будут переведены Вам в течении 3-х минут. Безусловно Вы можете указывать бОльшую сумму на вывод, чем фактический резерв по выбранному направлению." />';

help['P24USD']=help['P24UAH'];

function CheckDiscount (val, curr, course)
{	
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

function sum2USD(num)
{
	mIn=d.$("moneyIn").value;
	mOut=d.$("moneyOut").value;
	if ((num.id=="SumIn") && (mIn=="WMU" || mIn=="UAH" || mIn=="MCVUAH" || mIn=="P24UAH")) {return num.value/courses['USD']['UAH'];}
	if ((num.id=="SumIn") && (mIn=="WMR" || mIn=="ACRUB" || mIn=="RUSSTRUB" || mIn=="TBRUB" || mIn=="SVBRUB")) {return num.value/courses['USD']['RUR'];}	
	if ((num.id=="SumIn") && (mIn=="WME")) {return num.value/courses['USD']['EUR'];}	
	if ((num.id=="SumOut") && (mOut=="WMU" || mOut=="UAH" || mOut=="P24UAH")) {return num.value/courses['USD']['UAH'];}
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
	if ( parseFloat(d.$("SumIn").value) < summs['min']['In'][d.$("moneyIn").value] ) {
     alert("Минимальная сумма обмена "+summs['min']['In'][d.$("moneyIn").value]+" "+d.$("moneyIn").options[d.$("moneyIn").options.selectedIndex].text);return(false);}
	 if ( parseFloat(d.$("SumOut").value) < summs['min']['Out'][d.$("moneyOut").value] ) {
     alert("Минимальная сумма обмена "+summs['min']['Out'][d.$("moneyOut").value]+" "+d.$("moneyOut").options[d.$("moneyOut").options.selectedIndex].text);return(false);}
	 if ( parseFloat(d.$("SumIn").value) > summs['max']['In'][d.$("moneyIn").value] ) {
     alert("Максимальная сумма обмена "+summs['max']['In'][d.$("moneyIn").value]+" "+d.$("moneyIn").options[d.$("moneyIn").options.selectedIndex].text);return(false);}
	 if ( parseFloat(d.$("SumOut").value) > summs['max']['Out'][d.$("moneyOut").value] ) {
     alert("Максимальная сумма обмена "+summs['max']['Out'][d.$("moneyOut").value]+" "+d.$("moneyOut").options[d.$("moneyOut").options.selectedIndex].text);return(false);}

	if ( money[d.$("moneyIn").value][d.$("moneyOut").value]==undefined )	 {
		alert("Направление обмена не поддерживается");

	}else{
		document.forms.exchange.action="specification.php";
		document.forms.exchange.submit();
	}

}

function printcourse(){
	//if (C==1) {C3=''} else {C3=" * "+round3(C)}
	dis=CheckDiscount(sum2USD(document.forms.exchange.SumIn),C, course);
	d.$("prcourse").innerHTML= "<div align='left'><b>1 " + 
	<?php if ( isset($_GET['in']) ) {
		echo 'd.$("moneyIn2").value + ';
	}else{
		echo 'd.$("moneyIn").options[d.$("moneyIn").options.selectedIndex].text + ';
	} ?>
	"</b> " + " > " + " <b>"+ round3(C/(money[d.$("moneyIn").value][d.$("moneyOut").value]-dis))+ " "+ 
	<?php if ( isset($_GET['out']) ) {
		echo 'd.$("moneyOut2").value + ';
	}else{	
		echo 'd.$("moneyOut").options[d.$("moneyOut").options.selectedIndex].text + ';
	} ?>
	"</b></div><div align='left'><b>" + round3(1/(C/(money[d.$("moneyIn").value][d.$("moneyOut").value]-dis)))+ " " +
	<?php if ( isset($_GET['in']) ) {
		echo 'd.$("moneyIn2").value + ';
	}else{
		echo 'd.$("moneyIn").options[d.$("moneyIn").options.selectedIndex].text + ';
	} ?>
	"</b> " + " > " + " <b>"+  " 1 "+ 
	<?php if ( isset($_GET['out']) ) {
		echo 'd.$("moneyOut2").value + ';
	}else{	
		echo 'd.$("moneyOut").options[d.$("moneyOut").options.selectedIndex].text + ';
	} ?>
	"</b></div>";
	if ( money[d.$("moneyIn").value][d.$("moneyOut").value]==undefined ) {
	 d.$("prcourse").innerHTML= "<br /><br /><strong>Направление обмена не поддерживается</strong>";
	// nothing
 	}
	
	<?php if ( $num_row_client == 1 )    { ?>
	//alert(d.$("moneyIn").value+" "+d.$("moneyOut").value+" "+pred[d.$("moneyIn").value][d.$("moneyOut").value]['cl']);
	if ( typeof pred[d.$("moneyIn").value][d.$("moneyOut").value]=="undefined" || 
				pred[d.$("moneyIn").value][d.$("moneyOut").value]['cl']!=1) {
		d.$("discount").innerHTML="<div align='left'>Дополнительная скидка: <b>" + round2(d.$("SumOut").value*<?=round(($discount['total']-1)/100,5) ?>)  + " " +
		<?php if ( isset($_GET['out']) ) {
			echo ' d.$("moneyOut2").value';
		}else{	
			echo ' d.$("moneyOut").options[d.$("moneyOut").options.selectedIndex].text';
		} ?>
	+"</b></div>";
	}else { 
		d.$("discount").innerHTML="В этом направлении действует Ваша <strong>персональная скидка</strong>, которая уже учитывается в результирующей сумме."}
	<?php } ?>
		d.$("amount").innerHTML=WM_amount_r[d.$("moneyOut").value];
		d.$("help").innerHTML=help[d.$("moneyOut").value];
	}  
function start(){
	//ShowRow('mess',!mess);
    //ShowRow('needtohave',needtohave);
	//ShowRow('divprcourse',divprcourse);
	
	printcourse();
	CheckInOutSumm(d.$('SumIn'));
	<?php if ( isset($_GET['message']) ) {
		$alert_message['wrong_summ']='Не указана сумма. Укажите, пожалуйста, сумму.';
		$alert_message['max_limit']='Превышен лимит ввода средств по данному направлению для вашего идентификатора.';
		if ( isset($alert_message[$_GET['message']]) ) {
		
		?>
    alert("<?=$alert_message[$_GET['message']]?>");
    <?php }} ?>
}
</script>
        
	</head>
	<body onload="start();">
	    <div class="wrapper">
            <div class="wrapper-inn">

                <?php require_once("siti/inc_top.php");?>

                <div class="middle clear">

                    <!-- Start left column -->
                    <? require_once("siti/inc_left.php");?>
                    <!-- End left column -->

                    <!-- Start central column -->
                    <div class="c-col">
                        <div class="change">
                        	<?php /*?><h1>Обменов.ком - мгновенный ввод/вывод Webmoney по всей Украине.</h1><?php */?> 
                            <?php /*?><h1>Уважаемые коллеги и друзья! От всей души поздравляем Вас с Новым Годом!</h1>
                            <p>Всегда кажется, что с наступлением Нового года проводится некая черта, за которой мы оставляем все, что было, - и хорошее, и плохое, и ждем от нового года только хорошего. Ну так вот, :) хотим пожелать Вам, чтобы осталось за этой чертой только плохое, хорошее надо прихватить с собой в новый год,... реализации того, чего не удалось воплотить в жизнь. Финансовой радости. Просто неперестающей прухи :) Здоровья, само собой - куда же без него! <br />
<p align="right">С уважением, ваш Обменов.ком</p>
<p align="left">p.s.: Банковский ввод и вывод в праздничные дни будет работать в обычном режиме.</p></p><?php */?>
<?php /*?><h1>Ввод/вывод электронных валют в Киеве от 2000$ в эквиваленте.</h1>
<p>За подробностями обращайтесь в <a href="<?=$siteroot?>contacts.php">техподдержку сервиса</a>.<br />
<a href="<?=$siteroot?>commission.php">Тарифы и комиссия</a> на эти и другие виды обменов.</p>
<div class="otzyv"></div><?php */?>
<?php /*?><h1>Уважаемые клиенты!<br />
На текущий момент на наши сервера предпринята массированная DDoS-атака. </h1>
<p>Возможны перебои и медленная работа сервиса. Спасибо за понимание.</p>
<div class="otzyv"></div><?php */?>
<h1>Уважаемые клиенты!<br />
При оформлении заявки, обращайте внимание на доступный резерв результирующей валюты.</h1>
<p>При недостаточном резерве время выполнения заявки может увеличиваться.</p>
<div class="otzyv"></div>
<?php /*?><h1>Скоро!<br />
Беспроцентное пополнение счетов азиатского брокера Instaforex!</h1>
<p>Уже скоро!</p><?php */?>
<div class="otzyv"></div>
<?php if ( $urlid['site_curr2']==1 ) { ?>
<h1>Скоро! Ввод и вывод WM с помощью российских банков!<br />
Альфабанк, Связной, ВТБ, Русский Стандарт.</h1>
<p>В самое ближайшее время планируется расширение услуг нашего сервиса! Планируется подключение наиболее крупных и восстребованных российских банков для ввода/вывода средств: Альфабанк, Связной, ВТБ и Русский Стандарт. Следите за нашими новостями!</p>
<div class="otzyv"></div>
<?php /*?><h1>Ввод средств от 1000 WMZ - без комиссии, от 10000 WMZ - с нашей доплатой!</h1>
<p>За подробностями обращайтесь в <a href="<?=$siteroot?>contacts.php">техподдержку сервиса</a>.</p>
<div class="otzyv"></div><?php */?>
<h1>Obmenov.biz - сервис по обмену, вводу и выводу других электронных валют.</h1>
<p>Для сервиса доступны новые экспортные файлы курсов валют. Ссылки находятся в <a href="<?=$siteroot?>partner_rates.php">кабинете партнера</a>.<br />
<a href="http://obmenov.biz">Перейти на сайт Obmenov.biz</a></p>
 <?php }elseif ( $urlid['site_curr2']==2 ) { ?> 
<h1>Obmenov.biz - ввод, вывод LibertyReserve, PerfectMoney.</h1>
 <div class="otzyv"></div>
 <h1>Скоро! Ввод и вывод Perfectmoney и LibertyReserve с помощью российских банков!</h1>
<p>В самое ближайшее время планируется расширение услуг нашего сервиса! Планируется подключение наиболее крупных и восстребованных российских банков для ввода/вывода средств: Альфабанк, Связной, ВТБ и Русский Стандарт. Следите за нашими новостями!</p>
<div class="otzyv"></div>
<?php /*?><h1><?php /\Ввод средств от 1000 Liberty USD - без комиссии, от 5000 - с нашей доплатой 1%!<br />
Ввод средств от 1000 PerfectMoney USD - с нашей доплатой 1%, от 5000 - с нашей доплатой 2%!</h1>
<p>За подробностями обращайтесь в <a href="<?=$siteroot?>contacts.php">техподдержку сервиса</a>.</p>
<div class="otzyv"></div><?php */?>
 <h1>Обменов.ком - сервис по обмену, вводу и выводу остальных валют.</h1>
<a href="https://obmenov.com">Перейти на сайт Обменов.ком</a></p>
  <?php } ?>
  <div class="otzyv"></div>

                            <div class="change-form">
                          <form method="post" name="exchange" id="exchange">
                                    <fieldset>
                                        <div class="form-line1 clear">
                                            <div class="change-from">
                                                <input type="text" onfocus="if(this.value=='0') this.value='';" 
                                                	onblur="if(this.value=='') this.value='0';" value="0"  
                                                    onKeyUp="CheckInOutSumm(this);"
                                                    name="SumIn" id="SumIn"/>
                                <?php $query_currency = "SELECT currency.name, currency.extname, currency.type3 FROM currency 
										WHERE active=1 ".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] )."  and name!='KS' ".(!$closed_exchange ? ""/*" and left(currency.name,2)!='LP' "*/: "")."
										ORDER BY type3 asc, extname desc";
									$currency = _query($query_currency, 'index.php 4');
									
		
							$select = "SELECT count(orders.id) as count, orders.currin, orders.currout,
								(select extname from currency where name=orders.currin) as _currin, 
								(select extname from currency where name=orders.currout) as _currout FROM orders, payment, currency
								WHERE payment.orderid = orders.id
								AND payment.canceled =1
								AND orders.ordered =1
								AND (
									orders.currin = currency.name
									OR orders.currout = currency.name
								)
								".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] )
								 ." and orders.clid='".(isset($_SESSION['clid']) ? $_SESSION['clid'] : $clid)."'
								group by currin, currout 
								order by count desc limit 0, 1";
							
							$query=_query($select,"inc_right.php");
							$popular=$query->fetch_assoc();
							if ($query->num_rows==0) {
								if ( $urlid['site_curr2']==1 ) {
									$popular['currin']="WMR";
									$popular['currout']="WMZ";
								}else{
									$popular['currin']="LRUSD";
									$popular['currout']="P24USD";
								}
								
							}
									?>
                                                    
                                                <select onChange="CheckInOutSumm(d.$('SumIn'));" name="moneyIn" id="moneyIn"><?php 
													$t=0;
													$curtype[1]="Электронные валюты";
													$curtype[2]="Наличные валюты";
													$curtype[3]="Банковские переводы";
													$curtype[4]="Другое";
													$curtype[5]="Мобильная связь";
													$curtype[6]="Форекс-брокеры";
													while ($row=mysql_fetch_assoc($currency)) { 
													if ( $t != $row['type3'] ) { 
														$t=$row['type3'];?><option value="<?=$t?>" style="color:#999; font-weight:bold"><?=$curtype[$t]?></option><?php } ?><option <?=($row['name']==$popular['currin'] ? "selected" : "")?> value="<?=$row['name']; ?>"><?=$row['extname']; ?></option><?php } ?></option></select>
                                            </div>
                                            <p id="needtohave">На вашем кошельке <strong>должно быть +0,8%: 
                                            <span id="needIn">0</span> (минимум)</strong></p>
                                        </div>
                                        <div class="form-line2 clear">
                                            <div class="change-to">
                                            <?php $query_currency = "SELECT currency.name, currency.extname, 
												currency.type3 FROM currency  WHERE 1=1 " . ($urlid['site_curr2']==1 ?
												" and left(name,3)!='MCV' " : ""). "
													and name!='SMS'
												 AND active=1 ".(!$closed_exchange ? ""/*" and left(currency.name,2)!='LP' "*/: "")."
											".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "and currency.server ".$urlid['curr'] )."
												ORDER BY type3 asc, extname desc";
												$currency = _query($query_currency, 'index.php 5'); ?>
                                                <select onChange="CheckInOutSumm(d.$('SumOut'));" name="moneyOut" id="moneyOut">
                                                <?php $t=0;

											while ($row=$currency-> fetch_assoc()) { 
												if ( $t != $row['type3'] ) { 
													$t=$row['type3'];?>
    										<option value="<?=$t?>" style="color:#999; font-weight:bold"><?=$curtype[$t]?></option>
        									<?php } ?><option <?=($row['name']==$popular['currout'] ? "selected" : "")?> value="<?=$row['name']; ?>"><?=$row['extname']; ?></option><?php } ?></select>
                                                <input type="text" name="SumOut" id="SumOut" 
                                                onKeyUp="CheckInOutSumm(this);"
                                                onfocus="if(this.value=='0') this.value='';" 
                                                onblur="if(this.value=='') this.value='0';" value="0" />
                                                <p align="left"><span id="amount"></span> <span id="help"></span>
												<span id="prcourse"></span>
												<?php if ( $num_row_client == 1 )  { ?> 
    										<br /><span id="discount"></span>
											<?php } ?></p>
                                            </div>
										 
                                           <table align="right"><tr><td valign="top">
                                           <p id="bank" align="right" style="color:#FFF;"></p>
    	<p id="mess" align="right" style="color:#FFF; ">
		Операция с одинаковыми валютами <br>или направление обмена временно <br>не поддерживается.</p>
        									</td><td width="10"></td></tr></table>
                                            
                                        </div>
                                        <input type="button" value="" class="change-submit" onclick="makechange();" />
                                        <input type="hidden" name="order" value="ok" />
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        <p align="right" style="margin-bottom:10px"><strong>Не нашли нужной валюты или направления обмена? Обратитесь в нашу <a href="<?=$siteroot?>contacts.php">техподержку!</a></strong></p>
                        <div class="otzyv"></div>
                          <h1>Прямое пополнение <img src="<?=$siteroot?>i/logos/ks.png" width="17" height="17" hspace="0" vspace="0" border="0"  />Киевстар с большими скидками! <?=($urlid['site_curr2']==1) ? "Теперь и за Webmoney!" : "" ?></h1>
<p>Выберите свой способ прямого пополнения Киевстар <a href="<?=$siteroot?>commission.php">по ссылке</a>. <br />
Скидки при регистрации также действуют на этом направлении обмена.</p> 
<div class="otzyv"></div>

                        <div class="intro">
                            <h1>Новости <?=get_setting('site_title_sht'.$urlid['site_curr2'])?></h1>
                            <?php $select="select * from news where site ".$urlid['curr']." order by id desc";
								$query=_query($select,"");
										?>
                                
							
                            <table cellspacing="5">
                            <?php while ( $row=$query->fetch_assoc() ) { ?>
                            <tr><td width="10"></td><td>
                            <span class="otzyv-date"><?=$row['date']?></span></td>
                            <td width="10"></td><td> <span class="otzyv-name"><?=$row['head']?></span>
                            
                            <p><?=$row['text']?></p>
                            </td></tr>
                            <?php } ?>
                            </table>
                           <br />

                            <ul>
                            	<?php if ($urlid['site_curr2']==1) {?>
                                <li>Сервис находится в <a href="http://megastock.ru/Resources.aspx?gid=19" target="_blank">первой 20-ке рейтинга</a> обменных пунктов каталога Мегасток. Общее число обменных пунктов в рейтинге более 700.</li>
                                <?php } ?>
                                <li>Автоматический, мгновенный ввод/вывод через систему Приват24.</li>
                                <li>Продажа электронных валют посредством карт VISA и MasterCard.</li>
                                <li>При обмене сумм более 100, 1000 и 5000 долл. в эквиваленте, Вы получаете оптовую скидку, величина которой зависит от направления обмена.</li>
                                <li>Самые выгодные курсы валют на онлайн-обмены.</li>
                                <li>Автоматический, мгновенный способ обмена электронных валют.</li>
                                <li>Партнерская программа для владельцев сайтов, которая позволяет зарабатывать при минимуме затрат и усилий. </li>
                                <li>Все зарегистрированные пользователи автоматически становятся участниками накопительной дисконтной программы и получают скидку на комиссию сервиса Обменов.ком.</li>
                            </ul>
                        </div>
                    </div>
                    <!-- End central column -->

                    <!-- Start right column -->
                    <?php require_once("siti/inc_right.php");?>
                    <!-- End right column -->

                </div>

                <?php require_once("siti/inc_footer.php"); ?>

            </div>
	    </div>

	</body>
</html>
test