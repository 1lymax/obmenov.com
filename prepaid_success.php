<?
// $Id: success.php,v 1.6 2006/07/31 14:11:41 asor Exp $
require_once('Connections/ma.php');
require_once('function.php');
require_once('siti/prepaid_wm_config.php');
require_once('siti/prepaid_wm_include.php');
$message="";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: ПИН-коды</title>
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
                     <?php

if(isset($_POST['LMI_PAYMENT_NO']) && preg_match('/^\d+$/',$_POST['LMI_PAYMENT_NO']) == 1){ # Payment ref. number
        # select payment with ref. number
        $query = "SELECT prepaid_payment.id, prepaid_payment.item, prepaid_payment.state, name, LMI_SYS_INVS_NO, 
		LMI_SYS_TRANS_NO, LMI_SYS_TRANS_DATE, RND FROM prepaid_payment, item_name, items 
		WHERE prepaid_payment.id =".$_POST['LMI_PAYMENT_NO']. "  
		AND prepaid_payment.item=items.id AND items.itemid=item_name.id";


		$result = _query($query, "prepaid_success.php 1");
        $rows = mysql_num_rows($result);
        if ( $rows == 1 ) { # If payment is found, and actually paid
            $pay = mysql_fetch_array($result);
            mysql_free_result($result);
    	    if(    $_POST['LMI_SYS_INVS_NO'] == $pay['LMI_SYS_INVS_NO']
		&& $_POST['LMI_SYS_TRANS_NO'] == $pay['LMI_SYS_TRANS_NO']
		&& $_POST['LMI_SYS_TRANS_DATE'] == $pay['LMI_SYS_TRANS_DATE']
		&& $_POST['RND'] == $pay['RND'] ) {
		    # select item 
		    $query = "SELECT content, name, url, rules, number FROM items, item_name WHERE items.id=".$pay['item']." AND state='N' 
			AND items.itemid=item_name.id;";
		    $result = _query($query, "prepaid_success.php 2");
		    $rows = mysql_num_rows($result);
		    if ( $rows == 1 ) { # item found
			$item = mysql_fetch_array($result);
			mysql_free_result($result);
			# update state to "delivered" to customer
			$query = "UPDATE prepaid_payment SET state='G', timestamp=CURRENT_TIMESTAMP() WHERE id=".$pay['id'].";";
			$result = _query($query, "prepaid_success.php 3"); 
			if(mysql_affected_rows() != 1){ die("Payment table UPDATE failed!");};
		    };
		}
	}
?>
    

	<table align="center" width="450" border="0"><tr><td valign="middle" height="30">
          
          </td></tr>
          <tr><td width="450">

<h1>Ваша покупка:</h1><br /><br />
<?php if ( isset($item['name']) ) { ?>
<img src="<?=$siteroot?>i/game/<?=$item['url']?>_logo.gif"><br />
<b>Ваучер:</b> <? echo $item['name'] ?><br />
<b>Номер карты:</b> <? echo $item['number'] ?><br />
<b>Код пополнения:</b> <? echo $item['content'] ?></p><br />
<b>Инструкции:</b><br>
<?=$item['rules']; ?>
<?php }else{ ?>
Произошла ошибка. Обратитесь в техподдержку сервиса.
<?php } ?>
</td></tr></table>

<?php
} else {?>

<table align="center" width="450" border="0"><tr><td valign="middle" height="30">
          
          </td></tr>
          <tr><td width="450"><h2>
          Ошибка платежа. Обратитесь в <a href="contacts.php">службу поддержки</a></h2>
          </td></tr></table>
 <?php }
 echo $message;
 ?>
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