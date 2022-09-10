<?php require_once('Connections/ma.php');
require_once('function.php');
require_once( 'game/game/wm.class.php');	

$gamechoose['title']="";
$gamechoose['url']="";
$gamechoose['currency']="";


if ( isset($_GET['game']) ) {
	$select="select * from gamedealer_projects where active=0 and projectid=".(int)$_GET['game'];
	$query=_query($select,"gamepay.php 1");
	if ( mysql_num_rows($query)==1 ) {
		$gamechoose=$query->fetch_assoc();
	}
}

$wmbank = new wmbank;
	//$wmbank->updateProjects(); 066-694-6142
 //print_r($_POST);
  if(isset($_POST['ajax']) && isset($_POST['nick']) && isset($_POST['projectid'])){

	if($_POST['getamount']<1)die($wmbank->json(array('status'=>-1,'desc'=>'Извините, сумма должна составлять 1 игровую валюту, как минимум')));
     $result = $wmbank->json($wmbank->checkLogin($_POST['nick'],$_POST['projectid']));
     die(iconv('utf-8','windows-1251',$result));
  }


$projectlist = $wmbank->projectlist(); //print_R($projectlist)	
		
			?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?php if ( strlen($gamechoose['title'])>0 ) { 
echo $gamechoose['title']." (".$gamechoose['url'].")";?> :: Игровая валюта <?=$gamechoose['currency']?> за Webmoney, VISA, MasterCard, Приват24, Liqpay
<?php }else{ ?>
Покупка валюты онлайн-игр
<?php } ?> :: <?=get_setting('site_title_sht'.$urlid['site_curr2'])?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<meta name="description" content="<?=$gamechoose['title']?> : Игровая валюта <?=$gamechoose['currency']?> в игру <?=$gamechoose['title']?>. Мгновенное пополнение сразу после оплаты. Webmoney, VISA, MasterCard, Liqpay, Приват24" />
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
		<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
        <link rel="shortcut icon" href="<?=$siteroot?>i/favico.ico"/>
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
        
	</head>
	<body>
	    <div class="wrapper">
            <div class="wrapper-inn">

                <?php require_once("siti/inc_top.php");?>

                <div class="middle clear">

                    <!-- Start left column -->
                    <? require_once("siti/inc_left.php");?>
                    <!-- End left column -->

                    <!-- Start central column -->
                    <div class="c-col">
<div align="center">
                     <table align="center" width="500" border="0">
    <tr>
    	<td colspan="2" height="40" align="center"><h1>Покупка игровой валюты <?=$gamechoose['currency']?> в игру <?=$gamechoose['title']?></h1>
        <p><a href="<?=$gamechoose['url']?>"></a></p>
        Ваучеры и ПИн-коды для игр WarCraft, StarCraft, EVE, AION, <br />
Властелин Колец можно купить в нашем <a href="<?=$siteroot?>shop.php">магазине</a>.<br />
<h1>Минимальная сумма покупки составляет эквивалент 20 рос. рублей (WMR)</h1>
<br />
<br />

        </td>
    </tr>     
    
    <tr><td>

<div align="center">
<style>
  .ggm td{padding:3px;}
</style>
<script src="game/game/jquery.js"></script>
<script src="game/game/site.js"></script>
<script>
var course = new Array;
<?
echo "var total=".$WM_amount['WMZ'].";";
$courses = $wmbank->getCourses();
foreach($courses as $k=>$v)echo 'course["'.$k.'"] = '.$v.';'."\n";
//maildebugger($projectlist);
echo 'var game_amounts=new Array();'."\n";
foreach($projectlist as $v)echo 'game_amounts['.$v['projectid'].'] = "'.(strlen($v['values'])<2 ? '' : 'Допускаются значения: '.$v['values']).'";'."\n";

?>
</script>
<?
//print_r($_POST);
if ( isset($_POST['payment']) ) {
	parse_str($_POST['payment'], $payrez);
	$payment_no=$payrez['order'];
	if ( isset($payrez['order']) && $payrez['order']!="" ) {
		$query=_query("select * from gamedealer_wmreq where pid=".$payrez['order'],"");
		$row=$query->fetch_assoc();
		$nick=$row['nick'];
		$projectid=$row['projectid'];
	}
}
if(isset($_POST['LMI_PAYMENT_NO']) && isset($_POST['nick'])){
	$payment_no=(int)$_POST['LMI_PAYMENT_NO'];
	$nick=isset($_POST['nick']);
	$projectid=(int)$_POST['projectid'];
}
if ( isset($payment_no) && isset($nick) ) {
   $status = $wmbank->check_status($payment_no);
   $color = ($status['status'] == 1)?'#009600':'red'; //print_r($status);
   $projectinfo = isset($projectlist[$projectid])?$projectlist[$projectid]:array();
   echo '<div style="padding:20px"><div style="width:500px;padding:5px;color:'.$color.'">'.(isset($projectinfo['img'])?'<img align="left" src="'.str_replace("http://gamedealer.ru/img3/","https://obmenov.com/i/game/",$projectinfo['img']).'">':'').''.$status['desc'].'<br>['.$nick.' :: '.(isset($projectlist[$projectid])?$projectlist[$projectid]['title']:'').']</div></div>';
}
?>
<form method="post" action="specification_redirect.php" id="wmgameform" name="wmgameform">
  <input type="hidden" name="payment" value="<?=time()?>" />
  <input type="hidden" name="result" value="<?=$siteroot?>game/wmresult.php" />
  <input type="hidden" name="success" value="<?=$siteroot?>gamepay.php" />
  <input type="hidden" name="game" value="65438" />  
  <table id="ggm">
     <tr>
         <td id="gameLogo" valign="top"><b>Выберите игру:</b></td>
         <td id="head" height="30" valign="top"><h1>
         <?php if ( strlen($gamechoose['title'])>0 ) { 
		echo $gamechoose['title'];
		?></h1>
        <p><a href="<?=$siteroot?>game.php">Выбрать другую игру</a></p>
         	<div style="visibility:hidden">
           <?php }else{ ?>
           <div>
           <?php } ?><select style="width:200px" id="projectid" name="projectid" >
              <?php foreach($projectlist as $v){?>
              <option rubvalue="<?=$v['price_rub']?>" <?=((isset($_GET['game']) && $v['projectid']==$_GET['game'])? 'selected="selected"' :'')?> img="<?=str_replace("http://gamedealer.ru/img3/","https://obmenov.com/i/game/",$v['img'])?>" pay_attr="<?=$v['pay_attr']?>" gamecurrency="<?=$v['currency']?>" value="<?=$v['projectid']?>"><?=$v['title']?></option>
              <?php } ?>
           </select></div>
         </td>
     </tr>
     <tr>
        <td height="30" width="150"><b>
        <?php if ( strlen($gamechoose['title'])>0 ) { 
			echo $gamechoose['pay_attr'].": ";
		}else{
			?>
        <span id="pay_attr"></span>:
        <?php } ?>
        </b></td>
        <td><input name="nick" value=""/></td>
     </tr>
     <tr>
        <td height="30"><b>В игровой валюте:</b></td>
        <td><input name="getamount" id="getamount" value="0" size="4" /> <span id="gamecurrency"></span></td>
    </tr>
    <tr>
    	<td height="30">Всего доступно:</td> 
        <td><span id="total"></span> <span id="gamecurrency2"></span><br />
<span id="gamecurrency_values" style="color:#F00"></span>
        </td>
    </tr>
     <tr>
         <td height="30"><b>Сумма:</b></td>
         <td valign="middle"><input size="4" id="amount" value="0" name="amount" width="30px"/> <select id="currency" style="width:160px" name="purse"><?php foreach($wmbank->wmPurses as $k=> $v)echo '<option currency="'.$k.'" value="'.$v.'">'.$k.'</option>';?></select></td>
     </tr>

    <tr>
       <td align="center" height="20"></td><td><br>
       <input type="submit" class="button1" value="Оплатить >>">
 <?php /*?><span class="button"><a onClick="javascript:$('wmgameform').submit();">&nbsp;&nbsp;&nbsp;Оплатить</a></span><?php */?></td>
    </tr>
  </table>
 <div id="gameresult"></div>
</form>
</div>             
    
    </td></tr>
    <tr><td><br />
<br />
    <h2>Принимаем к оплате (без комиссии!):</h2><br>
    <img src="i/webmoney.gif" width="131" height="27" hspace="0" vspace="0" border="0" ><br>
Webmoney WMZ, Webmoney WMR, Webmoney WMU
<br><br>
  <img src="i/logo_MC.png" align="middle" alt="Прием платежей по картам MaterCard" width="79" height="35" hspace="0" vspace="0" border="0">

  <img src="i/logo_Visa.png" align="middle" alt="Прием платежей по картам VISA" width="79" height="36" hspace="0" vspace="0" border="0" ><br>Платежи с карт VISA и MasterCard любых банков мира
<br><br>

     <img src="i/privatbank-logo.gif" align="middle" width="91" height="55" hspace="0" vspace="0" border="0"> 

<br>
Оплата со счетов, открытых в системе Приват24 <br>
и с платежных карт, эммитированных Приватбанком.<br><br><br>


    <h2>Инструкции:</h2>
Для того, чтобы пополнить ваш счет, выберите игру, ваш никнейм в игре, укажите сумму покупки либо количество покупаемой игровой валюты. <br>
Проверка существования ника в игре осуществляется автоматически до момента оплаты. <br>
Минимальная сумма пополнения составляет 1 игровую валюту.

    </td></tr>
    <?php if ( isset($game) ) { ?>
    <tr><td><br><br /><h2>Описание игры:</h2><br>
	<?=$game['descr']?></td></tr>
    
    <?php } ?>
    </table>   
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