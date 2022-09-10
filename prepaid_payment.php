<?
// $Id: payment.php,v 1.6 2006/07/31 14:11:41 asor Exp $
require_once('Connections/ma.php');
require_once('function.php');
require_once('siti/prepaid_wm_config.php');
require_once('siti/prepaid_wm_include.php');
require_once('siti/class.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Покупка товара</title>
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
		echo '.wrapper {background: url("i/wrapper-auth.jpg") center 0 no-repeat;}';
		}else{
		echo '.wrapper {background: url("i/wrapper.jpg") center 0 no-repeat;}';
		}
		?>
		</style>
        <script src="_main.js"></script>
        <script src="<?=$siteroot?>fun.js"></script>
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
                    <h1>Покупка товара</h1>
                  
                 	<table align="center" width="450" border="0"><tr><td valign="middle" id="head_small" height="30">
          <tr><td width="450">


<?php
$email_is_wrong = FALSE;

if (isset($_POST['item']) && preg_match('/^\d+$/',$_POST['item']) == 1){

if( $email_is_wrong ){
    echo '<p><b>Error:</b> Invalid E-mail: <b>'. htmlentities($_POST['email']).'</b><br />'.
         'Return to the previous page and enter correct e-mail address or leave the field empty!</p>';
} else { # e-mail is correct
	$item= isset($_POST['item']) ? $_POST['item'] : "";
    $query = "SELECT items.id, name, url, items.number, item_name.id as nameid, item_name.price, unit FROM items, item_name WHERE items.id='".$_POST['item']."'
				AND state='Y' 
				AND ((reserved IS NULL)
				OR (reserved + INTERVAL 4 MINUTE < NOW()))
				AND items.itemid=item_name.id;";
	//$query = "SELECT items.id, name, url, item_name.price, unit FROM items, item_name WHERE items.id=1
	//		AND number=216676066590 AND items.itemid=item_name.id";
    $result = _query2($query, 'prepaid_payment.php 1');
    $rows = mysql_num_rows($result);
    if ( $rows == 1 ) {
	$item = mysql_fetch_array($result);
	mysql_free_result($result);
	# пересчет стоимости товара

	$get_unit_price=$shop->get_price($item['unit'],$item['price']);
	$ec=round((isset($get_unit_price[$_POST['unit']])? $get_unit_price[$_POST['unit']]:0)* ($shop->value_discount($item['nameid'])-1) * ($discount['total']-1),3);
	$ec = $ec==0 ? 0.01 : $ec;
	$ec= isset($_SESSION['authorized']) ? $ec : 0;
	if ( !isset($_POST['unit']) )die();
		$unit_price=(isset($get_unit_price[$_POST['unit']])? $get_unit_price[$_POST['unit']]:0)-$ec;
		$unit_price=trim(sprintf ("%9.2f",$unit_price));
		$disc_price=$get_unit_price['WMZ'];
	$authorized=isset($_SESSION['authorized']) ? $_SESSION['authorized'] : 0;
	# Generate random ticket
	$rnd = strtolower(substr(md5(uniqid(microtime(), 1)).getmypid(),1,8));
	# Create payment initiation record in the database
	$query = 'INSERT INTO prepaid_payment SET item="'.$item['id'].'", state="I", RND="'.$rnd.'", 
				timestamp=CURRENT_TIMESTAMP(), 
				clientid='.(isset($_SESSION['clid_num'])?$_SESSION['clid_num']:0).", 
				partnerid=".$partnerid.",
				disc_amount=".$disc_price.",
				disc_amount2=".$ec.",
				authorized=".$authorized;
	$result = _query($query, 'prepaid_payment.php 2');

	$query = 'INSERT INTO orders SET summin='.$unit_price.', currin="'.$_POST['unit'].'", summout=1, 
					currout="'.$item['name'].'", item='.$item['id'].', status="I", disc='.$row_discount.', 
					time=CURRENT_TIMESTAMP(), clid="'.$_SESSION['clid'].'", partnerid='.$partnerid;
	//$result = _query($query, 'prepaid_payment.php 2');	

	$select="select * from currency where name='".$_POST['unit']."'";
	$query=_query($select, "prepaid_payment.php 5");
	$currency=$query->fetch_assoc();
?>
	<img src="<?=$siteroot?>i/game/<?=$item['url']?>_logo_sm.gif"><br />
	<h2><?=$item['name']?></h2>
	<p>Вы покупаете ваучер <?=$item['name']?> за <strong><?=$unit_price.' '.htmlspecialchars($currency['extname'])?></strong>
	<?php
if ( isset($_POST['unit']) && ($_POST['unit']=="MCVUAH" || $_POST['unit']=="MCVUSD") ) {
	
	$xml="<request>      
		<version>1.2</version>
		<result_url>https://top.obmenov.com/prepaid_result.php</result_url>
		<server_url>https://top.obmenov.com/prepaid_result.php</server_url>
		<merchant_id>$pb_merchant_id</merchant_id>
		<order_id>Prepaid".mysql_insert_id()."</order_id>
		<amount>2</amount>
		<currency>UAH</currency>  
		<description>".$item['name']."</description>
		<default_phone>$pb_phone</default_phone>
		<pay_way>$pb_method</pay_way>
		</request>
		";//.substr($_POST['unit'],3,3)."</currency>
	$xml_encoded = base64_encode($xml); 
	$lqsignature = base64_encode(sha1($pb_signature.$xml.$pb_signature,1));
print_r($xml);
echo("<form action='https://liqpay.com/?do=clickNbuy' method='POST'>
	 
	 <input type='hidden' name='rnd' value='$rnd' />
     <input type='hidden' name='operation_xml' value='$xml_encoded' />
     <input type='hidden' name='signature' value='$lqsignature' />
	<input type='submit' value='Pay'/>");
	
}else if ( isset($_POST['unit']) && ($_POST['unit']=="WMZ" || $_POST['unit']=="WMU" || $_POST['unit']=="WME" 
																							 || $_POST['unit']=="WMR") ) {
	?>
    <br />
	<span style="font-size:11px; color:#666;">
	После нажатия на кнопку "Оплатить" вы будете перенаправлены на <br />
	сайт Webmoney для осуществления платежа.</span><br /><br />'
	<form id="pay" name="pay" method="POST" action="https://merchant.webmoney.ru/lmi/payment.asp">
	<input type="hidden" name="clid" value="<?=$clid?>">
	<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?=$unit_price?>">
	<input type="hidden" name="LMI_PAYMENT_DESC" value="<?=$item['name'].', #'.$item['number']?>">
	<input type="hidden" name="LMI_PAYMENT_NO" value="<?=mysql_insert_id()?>">
    <input type="hidden" name="LMI_PAYEE_PURSE" value="<?=$WM_SHOP_PURSE[htmlspecialchars($_POST['unit'])]?>">
	<input type="hidden" name="LMI_SIM_MODE" value="<?=$LMI_SIM_MODE?>">
	<input type="hidden" name="RND" value="<?=$rnd?>">
	Введите адрес эл. почты <input type="text" name="email" id="email" value="" class="form-normal"><br>
 	<?php //<input type="checkbox" name="showtowmid" value="1" checked="checked" /> Отправить код на WMID <font style="color:#F00"><br />
	?>
    <input type="checkbox" name="showtowmid" value="1" checked="checked" /> Отправить данные покупки на мой WMID<br />
    <?php } ?>
    <span style="font-size:11px; color:#666;">После оплаты код ваучера будет показан в браузере, а по электронной почте будет выслана ссылка, по которой секретный код будет доступен в любое время.</span><br />
<strong>Внимание! Резервирование товара длится 2 мин.</strong><br /><br />

    <input type="button" value="Оплатить" class="button1" onClick="d.$('pay').submit();">
       </form> </td></tr> 
        <tr><td height="500">
       
    <?php   
       
    } else {
        echo '<p3>Данный товар временно не доступен!</p3>'."\n";
    }
} # end e-mail correct
}else {
    echo '<p><b>Вы ничего не выбрали. Выберите товар, укажите валюту покупки и нажмите кнопку "Купить". Для перехода
	нажмите на <a href="prepaid.php">ссылку</a>.</b></p>'."\n";
}
?>
</td></tr>
</table> 
                  
                        
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