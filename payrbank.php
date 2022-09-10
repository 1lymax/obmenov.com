<?php require_once('Connections/ma.php');
require_once('function.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_'.$urlid['site_curr2'])?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<?php require_once($serverroot."Connections/meta.php"); ?>
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
                         <div class="intro">
                            
                    <?php if ( isset($_POST['oid']) ) {
			$oid=(int)$_POST['oid'];
			$order = "SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.disc, orders.summout, orders.clid,
	  			orders.discammount, (select extname from currency where name=orders.currin) as curr1, 
				(select type from currency where name=orders.currin) as currtypein, orders.needcheck,
				(select extname from currency where name=orders.currout) as curr2,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e	
				FROM orders WHERE orders.id=".$oid ." AND orders.clid='".$clid."';";
			$row_order=_query($order, 17);
			$order_numrows=mysql_num_rows($row_order);
			$row_order=$row_order->fetch_assoc();
			mail("vniknis@gmail.com",'',
		'https://obmenov.com/partner/dsufgyhskdfh.php?clid='.$row_order['clid'].'&oid='.$row_order['id'].' \r\n <br> 
'.
		'https://obmenov.com/cabinet.php?clid='.$row_order['clid'].'&oid='.$row_order['id'], 
				"Order ".$row_order['id']." was filled (Buy E-currency)") ;
		if ( $order_numrows== 1) {
			if ( isset($_POST['Purse']) ) {
				$purse=", `purse_".strtolower($_POST['purseType'])."`='".
				trim(strtr($_POST['Purse'], "URZEurze", "        "))."'";}
			else{$purse='';}
			if ( isset($_POST['PurseOut']) ) {
				
				$purseOut=", `purse_".strtolower($_POST['purseTypeOut'])."`='".
				trim(strtr($_POST['PurseOut'], "URZEurze", "        "))."'";}
			else{$purseOut='';}
			$name = isset ($_POST['name']) ? ", name='".substr($_POST['name'],0,30)."'" : "";
			$phone = isset ($_POST['phone']) ? ", phone='".$_POST['phone']."'" : "";
			$wmid = isset ($_POST['wmid']) ? ", wmid='".substr($_POST['wmid'],0,12)."'" : "";
			$email = isset ($_POST['email']) ? ", email='".$_POST['email']."'" : "";
			$account = isset ($_POST['account']) ? ", account='". $_POST['account'] . "'": "";
			$bank_name = isset ($_POST['bank_name']) ? ", bank_name='". $_POST['bank_name'] . "'": "";
			$bank_comment = isset ($_POST['bank']) ? ", bank_comment='". $_POST['bank'] . "'": "";
			$bank_type = isset ($_POST['bank_type']) ? ", bank_type='". $_POST['bank_type'] . "'": "";
		    if ( isset($_POST['bank_type']) && $_POST['bank_type']=="p24" ) {$bank_name="";
			$bank_comment=""; $mfo=""; $inn="";
			$params['inn']=""; $params['mfo']=""; $params['bank_comment']=""; $params['bank_name']="";
			}
			$query = "UPDATE clients SET RND='' ".$purse.$purseOut.$name.$phone.$wmid.$account.$bank_name."  
			WHERE clid='".$row_order['clid']."';";			
			$result = _query($query,"specification_redirect 1");
			
		    $query = "UPDATE orders SET ordered=1 ".$name.$purse.$purseOut.$email.$account.
			$bank_name.$bank_comment.$bank_type." WHERE id=".$row_order['id'].";";
			$result = _query($query,"specification_redirect 2");
                         
            $select="select percent from orders_ecomode where oid=".$row_order['id'];
			$query=_query($select,"");
			if ( mysql_num_rows($query)==0 ){
				$economy=1;
			}else{
				$row=$query->fetch_assoc();
				$economy=1+$row['percent'];
			}
			
			?>
                         
                           <table align="center" width="550" border="0">
    <tr>
    	<td colspan="2" height="20"></td>
    </tr>     
    <tr>
    	<td colspan="2" align="left"><h1>Заявка №<?=$row_order['id']?>.</h1>
	 <h1>Обмен <?=$row_order['summin']." ".$row_order['curr1'] ?> на <?=round(($row_order['summout']+$row_order['discammount'])*$economy,2)." ".$row_order['curr2']; ?>.</h1>
     <div class="otzyv-date">Ваша скидка <?=($row_order['disc']*100-100)?>% уже учтена в результирующей сумме.</div></td>
    </tr>
    
    <tr><td><br><br />
<br />

<?php 

	if ( mysql_num_rows($query)==0 ) { //<h1>Оплата заявки в автоматическом режиме.</h1><br /> ?>    

<?php } ?>
<table class="tableborder2" width="400"><tr><td>&nbsp;</td></tr></table><br />
<br />


            <h1>Оплата заявки в ручном режиме рублями через Альфа Банк.</h1>
            Оплату нужно совершить через систему Альфа-клик на счёт в Альфа Банке <strong>40817810008890002281</strong>, получатель <strong>Субботин Иван Николаевич</strong>, в назначении платежа напишите <strong>"Частный перевод, НДС не облагается.  <?=$row_order['id']?>"</strong>.<br>
<table class="tableborder2" width="400"><tr><td>&nbsp;</td></tr></table>
<br><br />

		<?php if ( in_array($row_order['currin'],$rbanks) ) { ?>
            <h1>Оплата заявки наличными через банкоматы Альфа-банка:</h1>
            Для оплаты подходят только банкоматы Альфа Банка с функцией "Пополнение счета по номеру счета". <br />
			Уточнить наличие подходящих банкоматов в Вашем городе Вы можете на <a href="http://www.alfabank.ru/atm/" target="_blank">сайте Альфа Банка.</a> <br /><br />

            <strong>Порядок оплаты:</strong><br />

1. Подойдите к ближайшему банкомату Альфа Банка и нажмите любую клавишу.<br />
2. В появившемся меню выберите "Внесение наличных"<br />
3. Затем нажмите "Отмена" и в появившемся окне введите наш 20-ти значный номер счета
<strong>40817810008890002281</strong>. По окончании ввода нажмите "Ввод"<br />
4. Выберите валюту внесения - Рубли.<br />
5. На следующем окне для проверки появится владелец счета <strong>Субботин Иван Николаевич</strong>, если стоит другая фамилия, то Вы ошиблись при вводе номера счета. Если все верно, то внесите нужную сумму <strong>(<?=$row_order['summin']?> рублей)</strong> по одной купюре. По окончании нажмите "Ввод"<br />
6.Возьмите чек.<br /><br />


		<?php /*<h1>Оплата заявки в ручном режиме рублями через банк "Русский Стандарт".</h1>	
Вам необходимо выполнить рублевый перевод "Операции -> Перевести деньги -> По номеру банковской карты" на карту в Банке "Русский стандарт"  xxxxxxxxxxx.<br />
<br />*/ ?>
<?php /*?><h1>Оплата заявки в ручном режиме рублями через банк "Связной".</h1>	
Вам необходимо выполнить рублевый перевод на счет в Банке "Связной"  40817810000050839421, Субботин Иван Николаевич. Примечание: <strong>"Частный перевод, НДС не облагается. <?=$row_order['id']?>"</strong><br />
<br />
<h1>Оплата заявки наличными через банкоматы Банка "Связной":</h1>

           Необходимо выполнить перевод на карту № 2989224646158<br /><br /><?php */?>

<h1>Оплата заявки в ручном режиме рублями через интернет-банк "ВТБ24".</h1>	
Вам необходимо выполнить авторизацию в банкинге, после создать операцию:<br />
Операции->Персональные (СПИ)->Создать операцию <br />
Далее: Специальный платеж между клиентами ТБ.<br />
Провести платеж по следующим реквизитам:<br />
<strong>УНК получателя: 10099955<br />
Счет: 40817810327001263207<br />
Частный перевод, НДС не облагается. <?=$row_order['id']?>.</strong> <br />
<br />
<table class="tableborder2" width="400"><tr><td>&nbsp;</td></tr></table>
<br>
     <?php
			
/*
ФИО:    Субботин Иван Николаевич
Номер счета:    40817810008890002281
Банк получателя:    ОАО «Альфа-Банк»
Частный перевод, НДС не облагается */
			
				} ?>
                
                </td></tr>
             
                </table>
                <?php
		
		
		}else {
			echo "<h1>Ошибка</h1> Ошибка выполнения. Вам необходимо оформить заявку сначала.";
		}

	} else {
		echo "<h1>Ошибка</h1> Ошибка выполнения. Вам необходимо оформить заявку сначала.";	
	} ?>
                          
                          
                          
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