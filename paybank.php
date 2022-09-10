<?php require_once('Connections/ma.php');
require_once('function.php');
	//$pb_uah_merchant=$pb_usd_merchant;
	$pb_account['P24UAH']="6762462602190511";
	$pb_account['P24USD']="6762462602779495";//$pb_usd_merchant_card;//"6762462602779495";	// 48790
	$pb_account['P24EUR']="6762462604268547";
	// 4405885016445667 мгновенная долл
	// 4405885017967131 мгновенная долл
	// 4405885016445758 мгновенная грн
	// 4405885600039975 виза електрон долл
	
	//$oid=11585;
	?>

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
	  			orders.discammount, (select extname from currency where name=orders.currin) as curr1, orders.attach,
				(select type from currency where name=orders.currin) as currtypein, orders.needcheck,
				(select extname from currency where name=orders.currout) as curr2,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e	
				FROM orders WHERE orders.id=".$oid ." AND orders.clid='".$clid."';";
			$row_order=_query($order, 17);
			$order_numrows=$row_order->num_rows;
			$row_order=$row_order->fetch_assoc();
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
			//$pb_account['P24UAH']="4405885015109975";

                         
            $select="select percent from orders_ecomode where oid=".$row_order['id'];
			$query=_query($select,"");
			if ( $query->num_rows==0 ){
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
    
    <?php if ( substr($row_order['currin'],0,3)=="P24" ) {?>	
    <tr><td><br><br />
<br />

<?php 

	if ( $query->num_rows==0 ) {
	  if ( !in_array($row_order['currout'],array("PMUSD", "LREUR","LRUSD", "PMEUR")) ) { ?> 

 
<h1>Оплата заявки в автоматическом режиме.</h1><br />

<form action="https://api.privatbank.ua/p24api/ishop" method="POST" id="formpb">
           <input type="hidden" name="amt" value="<?=$row_order['summin']?>"/>
           <input type="hidden" name="ccy" value="<?=$row_order['currtypein']?>" />
           <input type="hidden" name="merchant" value="<?=($row_order['currin']=="P24USD" ? $pb_usd_merchant : $pb_uah_merchant);?>" />
           <input type="hidden" name="order" value="<?=$row_order['id']?>" />
           <input type="hidden" name="details" value="<?=iconv("windows-1251","utf-8","Перевод личных средств. ".$row_order['id'].". /*".$row_order['needcheck'])?>" />
           <input type="hidden" name="ext_details" value="<?=$row_order['id']?>" />
           <input type="hidden" name="pay_way" value="privat24" />
           <input type="hidden" name="m" value="m" />
           <input type="hidden" name="return_url" 
           			value="https://obmenov.com/cabinet.php?clid=<?=$row_order['clid']?>&oid=<?=$row_order['id']?>" />
           <input type="hidden" name="server_url" 
           			value="https://obmenov.com/mcvresult.php?clid=<?=$row_order['clid']?>&oid=<?=$row_order['id']?>" />
       </form>
       <input name="" type="button" class="button1" onclick="document.forms.formpb.submit();" value="Оплатить через Приват24&gt;&gt;"/>
<br>
<br>
После нажатия на кнопку, Ваш броузер будет перенаправлен в систему Приват24 для осуществления платежа. Авторизовавшись в системе, Вам останется только выбрать счет, с которого произойдет удержание, и подтвердить платеж (все данные уже будут подставленны в соответсвующие поля).<br>
Если Вы воспользуетесь этим типом перевода, электронные средства поступят к Вам на кошелек в автоматическом режиме в течении нескольких минут после того, как будет осуществлена оплата.<br>
<span style="color:#F00">Внимание!</span> Автоматический перевод средств работает <strong>только</strong> при выборе этого способа оплаты. После совершения платежа <strong>обязательно</strong> нажмите кнопку "Вернутся на сайт".
<br>

<?php }  }
?>
<?php /*?>Оплата заявок на сумму более 400 долларов в эквиваленте на текущий момент возможна только в ручном режиме. Заявка будет обработана и проведена максимально быстро. Для того, чтобы выполнить перевод вручную через Приват24 воспользуйтесь инструкцией ниже.<?php */?><table class="tableborder2" width="400"><tr><td>&nbsp;</td></tr></table><br />
<br />


          <?php /*?>  <h1>Оплата заявки в ручном режиме через систему Приват24.</h1>

            Воспользуйтесь этим способом оплаты, если по каким-либо причинам Вы не хотите провести платеж в автоматическом режиме. Для перевода нужно выполнить следующие действия:<br>
            1. Авторизуйтесь в системе Приват24, используя свой логин и пароль.<br>
			2. Чтобы выполнить платеж через систему Приват24, войдите в Меню -> Платежи -> Создать... -> Перевод на карту Приватбанка.<br>
            3. Далее в поле "сумма" укажите сумму платежа <br /><strong><?php echo $row_order['summin']." ".$row_order['currtypein']; ?></strong>, а в поле "на карту/счет" укажите <br />
            <?=$pb_account[$row_order['currin']]?></strong>. Нажмите "Продолжить".<br>
            4. Подтвердите платеж.<br>
            5. После этого зайдите в "корзину платежей" (в правой части экрана) и, следуя инструкциям выполните перевод средств.<br>
<table class="tableborder2" width="400"><tr><td>&nbsp;</td></tr></table><?php */?>
<br><br />

		<?php if ( $row_order['currin']=="P24UAH" ) { ?>
            <h1>Оплата заявки наличными через отделение Приватбанка (действует только для гривневых операций):</h1>
			Укажите операционисту номер счета <strong><?=$pb_uah_merchant?></strong> и сумму платежа <strong><?php echo $row_order['summin']." ".$row_order['currtypein']; ?></strong>. При таком платеже возможно взимание дополнительной комиссии банка в размере 0.75% от суммы платежа.
<table class="tableborder2" width="400"><tr><td>&nbsp;</td></tr></table>
<br>

	<?php /*?><table width=400 align="left">
    <tr><td colspan=2>Безналичный платеж из любого банка Украины:</td></tr>
	<tr><td width="50%">Получатель:</td><td width="50%">Приватбанк</td></tr>
	<tr><td>Наименование банка:</td><td>Приватбанк</td></tr>
	<tr><td>Номер счета:</td><td>29244825509100</td></tr>
	<tr><td>МФО:</td><td>305299</td></tr>
	<tr><td>ОКПО:</td><td>14360570</td></tr>
	<tr><td><br /></td></tr>
	<tr><td valign="top">Назначение платежа: </td><td>Заявка <b>№<?=$row_order['id']?></b><br>
	Лысогоров Максим Сергеевич, ИНН 2934408073 <br>Для пополнения на карту <?=$pb_account['P24UAH']?></td></tr></table><?php */?>
           
            <?php }
				 ?>
                
                </td></tr>
                
                <?php } elseif ( in_array($row_order['currin'],$rbanks) ) {// конец привата?>
                <tr><td>
                
                </td></tr>
                
                <?php } ?>
                
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