<?php require_once('Connections/ma.php'); ?>
<?php require_once($serverroot.'function.php');
		require_once($serverroot.'siti/_header.php');
//maildebugger(print_r($_POST,1));
$currentPage = $_SERVER["PHP_SELF"];
$order_condition="";
if ( !isset($_SESSION['authorized']) && ( 
			(!isset($_GET['oid']) && !isset($_GET['clid'])) ) ) { //begin of false user auth
					  
					  
					  
}else  //begin of true user auth
{
//echo "else";

$max = 10;
$page = 0;
if (isset($_GET['page'])) {
  $page = (int)$_GET['page'];
}
$start = $page * $max;
if ( isset($_SESSION['authorized']) ) {
	$condition="WHERE clients.nikname='".$_SESSION['AuthUsername']."';";
}
if ( isset($_SESSION['WmLogin_WMID']) ) {$condition="WHERE clients.wmid='".$_SESSION['WmLogin_WMID']."';";}

if ( isset($_GET['clid']) && isset($_GET['oid']) ) {
	$clid=$_GET['clid'];
	//$order_condition=" AND orders.clid='".$_GET['clid']."'";
	$order_condition .=" AND orders.id=".(int)$_GET['oid'];	
}else {
	$query_client="SELECT clients.id, clients.name, clients.nikname, clients.wmid, clients.email, clients.clid, clients.phone 
		FROM clients ".$condition;
		
	$client = _query($query_client, 'cabinet.php 1');
	$num_row_client=$client->num_rows;
	$row_client=$client->fetch_assoc();
	$clid=$row_client['clid'];
	$order_condition=" AND orders.authorized=1 ";
}

$query_orders = "SELECT orders.id as orderid, summin, orders.summout, orders.currin as currin1, orders.currout as currout1,
			(select extname from currency where name=orders.currin) as currin, orders.status as orderstatus, orders.needcheck,
			(select extname from currency where name=orders.currout) as currout,
			(select type2 from currency where name=orders.currin) as curr1type,
			(select type2 from currency where name=orders.currout) as curr2type,
			orders.purse_z, orders.currin as currency1, orders.currout as currency2, orders.phone,
			orders.purse_e, orders.purse_r, orders.purse_u, orders.discammount, orders.disc, 
			orders.time, orders.ordered, orders.attach, orders.clid, payment.id as paymentid,
			payment.LMI_PAYER_PURSE as paypurse, orders.bank_type, orders.account, orders.mfo,
			payment.status, (select purse from currency where orders.currin=currency.name) as pursein,
			(select purse from currency where orders.currout=currency.name) as purseout,
			payment.timestamp as payment_time, orders.purse_other,
			payment.LMI_PAYER_WM,
			payment.status,
			payment.ordered AS recept, payment.canceled 
			FROM orders, payment 
			WHERE payment.orderid=orders.id AND orders.ordered=1 AND orders.clid='".$clid."' 
			and ( payment.ordered=1 or (orders.time +  INTERVAL 72 HOUR > NOW()) )
			 ".$order_condition." 
			GROUP by orders.id order by orders.id desc";
			//maildebugger($query_orders);
$query_limit_orders = sprintf("%s LIMIT %d, %d", $query_orders, $start, $max);
$orders = _query2($query_limit_orders, 'cabinet.php 2');

//$row_orders = mysql_fetch_assoc($orders);
//echo $query_orders;
$all_orders = _query2($query_orders, "cabinet.php 3");
$total1 = $all_orders->num_rows;

$totalPages = ceil($total1/$max)-1;

$queryString_orders = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "page") == false && 
        stristr($param, "total") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_orders = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_orders = sprintf("&total=%d%s", $total1, $queryString_orders);

	//discount
	
$discount=$shop->discount();

} // end of true user auth

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Кабинет пользователя</title>
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
                        
                       <div align="center"> 
                        
                     <?php if ( isset($_SESSION['authorized']) || (isset($_GET['oid']) && isset($_GET['clid'])) ) { ?>
                    <h1>Личный кабинет</h1> 
	<table width="550" align="center"><tr>
    <td align="right" valign="top">
    <?php echo !isset($_GET['history']) ? '<img src="i/li_sm.gif" width="11" height="10" border="0" /> Статистика<br />' : 
							'<a href="cabinet.php">Статистика</a><br />';
     echo isset($_GET['history']) ?  '<img src="i/li_sm.gif" width="11" height="10" border="0" /> История операций<br />' : '<a href="cabinet.php?history">История операций</a><br />';
	?>
    <a href="#">Мои покупки</a><br />
	<a href="register.php">Персональные данные</a><br />
	<?php if ( isset($_SESSION['authorized']) ){ ?>
    <a href='login.php?doLogout=true'>Выход</a><br /><br />
	<?php 
	$select="SELECT time, ip, realm, success FROM `auth` where name='".$_SESSION['AuthUsername']."' and realm like '%user%' and success=1 order by time desc"; // Не забыть поправить проверку по входу ВМИД. Сейчас ищет только вход по нику.
	$query1=_query($select,"cabinet.php time,ip");
	$enter_stat=$query1->fetch_assoc();
	$enter_stat=$query1->fetch_assoc();
	 
	$dtf=dateformat($enter_stat['time']);
	
	
	?>
	Последний вход:<br />
	
    Время: <?php echo $dtf['d'].".".$dtf['m'].".".$dtf['y']." ".$dtf['h'].":".$dtf['mi'];?><br />
	IP-адрес: <?php echo $enter_stat['ip'];
	}?>    
    </td>
    </tr>
    <tr><td height="50"></td></tr>
    </table>

<?php
	if ( isset($_GET['history']) || (isset($_GET['oid']) && isset($_GET['clid'])) ) {	
	// begin history
	?>
    <h2>Данные ваших заявок</h2>
<table border="0" align="center" cellpadding="6" cellspacing="0" width="530">
  <tr class='td_head'>
    <td align="left">
	<font style="color:#666">Отображаются только оформленные заявки</font>
    </td>
    <td align="right"><font style="color:#666"><br />*новые заявки отображаются первыми</font></td>

  </tr>
  <?php if ( isset($_GET['message']) ) { 
		$select="select descr from errors where source='cabinet' and inname='".$_GET['message']."'";
		$query=_query($select,"");
		$row=$query->fetch_assoc();
  ?>
<tr><td align="center"><font style="color:#F00"><?=$row['descr']?></font></td></tr>

  <?php }
  	$td_style='td';$i=0;
  	 while ($row_orders = $orders->fetch_assoc()) { 
  	$i=$i+1;
  ?>
    <tr><td class="<?=$td_style; ?>" width="70%" valign="top">
    	<table cellpadding="5" align="center" width="90%"><tr>
        <td height="20"><h2>Заявка № <?=$row_orders['orderid']; ?></h2></td><td align="right">&nbsp;</td></tr>
        <tr>
        <tr><td height="20" class="cabinet-text" align="left">Время заявки (GMT+2):</td>
        <td align="right" class="cabinet-text"><?php $dtf=dateformat($row_orders['time']);
		echo $dtf['d'].".".$dtf['m'].".".$dtf['y']." ".$dtf['h'].":".$dtf['mi'];
		?></td>
        </tr>
        <td height="20" class="cabinet-text" align="left">Обмен </td>
        <td align="right" class="cabinet-text"><?=$row_orders['summin']?> <?=$row_orders['currin']; ?></td></tr>
        <td height="20" class="cabinet-text" align="left">На </td>
        <td align="right" class="cabinet-text"><?=$row_orders['summout']+$row_orders['discammount']?> <?=$row_orders['currout']; ?></td>
        </tr>
        <tr><td height="20" class="cabinet-text" align="left">Из них скидка (<?=($row_orders['disc']*100-100)?>%):</td>
        <td align="right" class="cabinet-text"> <?=$row_orders['discammount']?>
			<?=$row_orders['currout']; ?>
		</td>
        </tr>
        <?php if ( $row_orders['ordered']==1 ) {?>
        <tr><td height="20" class="cabinet-text" align="left">Плательщик:</td>
        <td align="right" class="cabinet-text">
        	<?=isset($row_orders[$row_orders['pursein']]) ? $row_orders[$row_orders['pursein']] : ""?>
            </td></tr>
        <tr><td height="20" class="cabinet-text" align="left">Получатель:</td>
        <td align="right" class="cabinet-text">
			
            <?=(isset($row_orders[$row_orders['purseout']]) ? $row_orders[$row_orders['purseout']] : "")?></td>
        </tr><?php }
		?>
        <tr><td height="20" class="cabinet-text" align="left">Время последней проверки оплаты:</td>
        
        <td align="right" class="cabinet-text"><?php $dtf=dateformat($row_orders['payment_time']);
		echo $dtf['d'].".".$dtf['m'].".".$dtf['y']." ".$dtf['h'].":".$dtf['mi'];?></td></tr>
        
        <?php
		/*if ( $row_orders['currin1']=="P24UAH" || $row_orders['currin1']=="P24USD" ) {
			$select="select * from payment where orderid=".$row_orders['orderid'];		
			$query=_query($select, "cabinet.php 23");
			$row_payment=$query->fetch_assoc();
		?>
			
        <tr><td height="20" class="cabinet-text" align="left">Референс-описание:</td>
        <td align="right" class="cabinet-text"><?=$row_payment['LMI_SYS_TRANS_NO']?></td></tr>		
        
		<?php	
		}*/
		
		$select="select * from payment_out where payment=".$row_orders['orderid'];		
		$query=_query($select, "cabinet.php 23");
		$row_payment=$query->fetch_assoc();		
		if ( $query->num_rows==1 ) {
			
		if ( $row_orders['currout1']=="P24UAH" || $row_orders['currout1']=="P24USD" ) { 

		?>
		<tr><td height="20" class="cabinet-text">Перевод в</td><td align="right" class="cabinet-text">Приватбанк</td></tr>
        <tr><td height="20" class="cabinet-text">Банк. счет №</td>
        
        <td align="right" class="cabinet-text"><?=$row_orders['account']?></td></tr>
        
		<?php }	?>
        <tr><td height="20" class="cabinet-text">Референс-описание:</td>
        <td align="right" class="cabinet-text"><?=utf8_decode($row_payment['retdesc']);?>
        </td></tr>
        <?php if ( $row_orders['canceled']==1 ) {?>
        <tr><td height="20" class="cabinet-text">Время оплаты сервисом</td>
        <td align="right" class="cabinet-text"><?php $dtf=dateformat($row_payment['time']);
		echo $dtf['d'].".".$dtf['m'].".".$dtf['y']." ".$dtf['h'].":".$dtf['mi'];?></td></tr>		
        
        <?php } } ?>
<?php if ( $row_orders['needcheck']==1 ) {?>
        <tr><td colspan=2 height="20" class="cabinet-text">
        <img src="i/attention.gif" width="11" height="11" title=""> Ожидание проверки реквизитов оператором...</td></tr>
        <?php } /*if ( $row_orders['orderstatus']!="" ) {?>
        <tr><td colspan=2 height="20" class="cabinet-text">
        <img src="i/attention.gif" width="11" height="11" title=""> <?=$row_orders['orderstatus']?></td></tr>
        <?php }*/ if ( $row_orders['LMI_PAYER_WM']=="failure" ) { ?>
        <tr><td colspan="2" height="20" class="cabinet-text"><strong>Платеж не выполнен. Статус ошибки: </strong><?=$row_orders['status'];?></td></tr>
        <?php }elseif ( $row_orders['LMI_PAYER_WM']=="wait_secure" ) { ?>
        <tr><td colspan="2" height="20" class="cabinet-text"><img src="i/attention.gif" width="11" height="11" title=""> Верификация платежа в процессинге...</td></tr>
        <?php } 
		
		/*
		if ( $row_orders['canceled']==1 && $row_orders['recept']==1 ) {
			
		}else{

			$response = $wmxi->X4(  // проверка, есть ли уже выписанный счет
			$shop_wm_purse[substr(htmlspecialchars($row_orders['currin1']),2,1)],               # номер кошелька 
							#для оплаты на который которого выписывался счет
			0,     # целое число > 0
			intval($row_orders['paymentid']),     # номер счета в системе учета магазина; любое целое число без знака
			$dtf['y'].$dtf['m'].$dtf['d']." 00:00:00",
			$dtf['y'].$dtf['m'].$dtf['d']." 23:59:59"    # ГГГГММДД ЧЧ:ММ:СС
			);
			$structure = $parser->Parse($response, DOC_ENCODING);
			$transformed = $parser->Reindex($structure, true); 
			//print_r( $transformed );
			echo "<img src='images/question.gif' width='11' height='11' /> <strong>";
			switch ($transformed['w3s.response']['outinvoices']['outinvoice']['state']) {
				case 0 : echo "Счет не оплачен"; break;
				case 1 : echo "Счет оплачен. Ждет погашения."; break;
				case 2 : echo "Счет оплачен. Ждет погашения."; break;
				case 3 : echo "Отказ от оплаты"; break;
			}
			echo "</strong>";
		}*/
		?>
        </table>
    
    </td><td align="center" class="<?=$td_style; ?>" valign="top" width="30%">
    	<table cellpadding="5" width="90%">
        <tr><td colspan="2" height="20" class="cabinet-text"><strong>Статус заявки:</strong></td></tr>
        <tr>
        <td height="20" class="cabinet-text">Заявка оформлена?</td><td align="right">
        <input name="ordered" type="checkbox" disabled value="1" <?php echo $row_orders['ordered']!=0 ? 'checked' : ''; ?> /></td></tr>
        <tr>
    	<td height="20" class="cabinet-text">Заявка оплачена?</td><td align="right">
        <input name="payed" type="checkbox" disabled value="1" <?php echo $row_orders['recept']!=0 ? 'checked' : ''; ?> /></td></tr>
        <tr><td height="20" class="cabinet-text">Заявка погашена?</td><td align="right">
        <input type="checkbox" name="canceled" disabled value="1" <?php echo $row_orders['canceled']==1 ? 'checked' : ''; ?> /></td></tr>
        <tr><td height="20" class="cabinet-text">Код протекции:</td><td align="right">
        <?php
		if ( $row_orders['canceled']==1 ) {
			$select="select protection from payment_out where payment=".$row_orders['orderid'];
	  		$query_protection = _query($select,"");
	   		$row_protection=$query_protection->fetch_assoc();
	  		 if ( $row_protection['protection']!='' ) {
		  	 echo "<br /> <strong>".$row_protection['protection']."</strong>"; }
		  	 else {echo "-"; }
		   
		}?>
        </td>
        </tr>
        <?php $select="select id from orders where orders.id=".$row_orders['orderid']." and (orders.time +  INTERVAL 2 HOUR > NOW())";
		$query=_query2($select,"cabinet.php");
		if ( $query->num_rows!=0 && $row_orders['canceled']!=1 && $row_orders['recept']!=1 ) { ?>
        <tr>
    	<td height="20" colspan="2" align="right">
        <form action="<?=$siteroot?>specification.php" method="get">
        <input type="hidden" name="clid" value="<?=$row_orders['clid']?>" />
        <input type="hidden" name="oid" value="<?=$row_orders['orderid']?>" />
        <input type="submit"  value="Произвести оплату" class="button1"/>
        </form>
        </td></tr>
        <?php } ?>
        </table>
    
    </td></tr>
 
    <?php 
	if ( $i==1 ) {$td_style='td_white'; $i=-1; } else {$td_style='td';}
	} ?>
<?php /*?>    <tr><td colspan="7">Пояснения:</td></tr>
    <tr><td colspan="7">Пояснения:</td></tr>
    <tr><td colspan="7">Пояснения:</td></tr>
    <tr><td colspan="7">Пояснения:</td></tr><?php */?>
</table>
<table width="550" align="center">
	 <tr>
      <td align="right">
      <?php /*?><form><input type="checkbox" style="border:none;" <?php echo isset($_GET['active']) ? "checked" : $queryString_orders ?> onclick="javascript:document.location.href='cabinet.php?<?php echo isset($_GET['active']) ? "" : $queryString_orders."&active" ?>'" /> Убрать неоформленные заявки
</form><?php */?>
      </td>
    </tr>
 </table>
<?php if ( !isset($_GET['oid']) && !isset($_GET['clid']) ) { ?>
<table border="0" align="center">
  <tr>
    <td><?php if ($page > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, 0, $queryString_orders); ?>"><<Начало</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($page > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, max(0, $page - 1), $queryString_orders); ?>"><Пред.</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($page < $totalPages) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, min($total1, $page + 1), $queryString_orders); ?>">След.></a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($page < $totalPages) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, $totalPages, $queryString_orders); ?>">Посл>></a>
        <?php } // Show if not last page ?></td>
  </tr>
  </table><br />
<br />

  <table align="center">
  <tr>
    <td align="center" colspan="4">Запись с <?php echo ($start + 1) ?> по <?php echo min($start + $max, $total1) ?>. Всего: <?php echo $total1; ?>
	</td>
  </tr>
</table>
<?php } // end navigation
	} // end history
	?>
    <?php
	if ( !isset($_GET['history']) && (!isset($_GET['oid']) && !isset($_GET['clid'])) ) {
		
	$discount_table_query = 'SELECT * FROM discount';
	$discount_table = _query($discount_table_query,'cabinet.php 5');
	
	?>
   
	<table width="550" border="0"><tr><td align="center">
     <h2><img src="<?=$siteroot?>i/icq-online.png" width="10" height="10" /> Обмены</h2>
    <table border="0" align="center" cellpadding="4" cellspacing="0" width="250">
  <tr>
    <td align="center" class="td_head" height="30" rowspan="2"></td>
    <td colspan="2" align="center" valign="middle" class="td_head">Сумма обменов</td>
    <td align="center" width="50" class="td_head" rowspan="2">Скидка от нашего заработка</td>
  </tr>
  <tr>
    <td align="center" class="td_head">От</td>
    <td align="center" class="td_head">До</td>
    

  </tr>
<?php

while ($discount_table_row = $discount_table->fetch_assoc()) { 
		$color='';
		if ( $discount['exchange_discount'] == $discount_table_row['disc'] ) {
		$color='class="td_white"';
		}?>
    <tr>
      <td height="20" align="left" <?=$color?>> </td>
      <td height="20" align="center" <?=$color?>> <?=$discount_table_row['value']; ?></td>
      <td align="center" <?=$color?>> <?=$discount_table_row['value_till']; ?></td>
      <td align="center" <?=$color?>> <b><?=(($discount_table_row['disc']-1)*100); ?>%</b></td>
    </tr>
<?php	};	?>
	<tr><td height="30"></td></tr>
    </table>

	<table width="250" align="center" cellpadding="4" cellspacing="0">
    <tr><td colspan="3" height="30" class="td_head"></td></tr>
    <tr>
	
	
	<td bgcolor="#FFFFFF" height="20">Скидка по обменам:</td><td align="right" bgcolor="#FFFFFF"><?=(($discount['exchange_discount']-1)*100)?>% *</td></tr>
    <td height="20">Текущая сумма обменов:</td><td align="right"><?=round($discount['total_exchange'],2)?> USD</td></tr>
	<td bgcolor="#FFFFFF" height="20">Следующий уровень скидки через:</td><td align="right" bgcolor="#FFFFFF"><?=round($discount['exchange_value_till']-$discount['total_exchange'],2)?> USD</td>
	<?php mysql_free_result($orders);	?>
    </tr></table>
    
    
    </td>
    <td align="center" width="275">
    
     <h2><img src="<?=$siteroot?>i/icq-online.png" width="10" height="10" /> Покупки</h2>
    <table border="0" align="center" cellpadding="4" cellspacing="0" width="250">
  <tr>
    <td align="center" class="td_head" height="30" rowspan="2"></td>
    <td colspan="2" align="center" valign="middle" class="td_head">Общая сумма покупок</td>
    <td align="center" width="50" class="td_head" rowspan="2">Скидка от нашего заработка</td>
  </tr>
  <tr>
    <td align="center" class="td_head">От</td>
    <td align="center" class="td_head">До</td>
    

  </tr>
<?php $discount_table_query = 'SELECT * FROM prepaid_discount';
	$discount_table = _query($discount_table_query,'cabinet.php 5');
while ($discount_table_row = $discount_table->fetch_assoc()) { 
		$color='';
		if ( $discount['prepaid_discount'] == $discount_table_row['disc'] ) {
		$color='class="td_white"';
		}?>
    <tr>
    <td height="20" align="left" <?=$color?>> <?php #$discount_table_row['descr']; ?></td>
      <td  height="20" align="center" <?=$color?>> <?=$discount_table_row['value']; ?></td>
      <td align="center" <?=$color?>> <?=$discount_table_row['value_till']; ?></td>
      <td align="center" <?=$color?>> <b><?=(($discount_table_row['disc']-1)*100); ?>%</b></td>
    </tr>
<?php	};	?>
	<tr><td height="30"></td></tr>
    </table>

	<table width="250" align="center" cellpadding="4" cellspacing="0">
    <tr><td colspan="3" height="30" class="td_head"></td></tr>
    <tr>
	
	
	<td bgcolor="#FFFFFF" height="20">Скидка по покупкам:</td><td align="right" bgcolor="#FFFFFF"><?=(($discount['prepaid_discount']-1)*100)?>% *</td></tr>
    <td height="20">Текущая сумма покупок:</td><td align="right"><?=round($discount['total_prepaid'],2)?> USD</td></tr>
	<td bgcolor="#FFFFFF" height="20">Следующий уровень скидки через:</td><td align="right" bgcolor="#FFFFFF"><?=round($discount['prepaid_value_till']-$discount['total_prepaid'],2)?> USD</td>
    </tr></table>
    
    
    
    </td>
    </tr></table><br /><br />


    <h1><img src="<?=$siteroot?>i/icq-online.png" width="10" height="10" /> Общая скидка на услуги сервиса: <?=($discount['total']-1)*100?>%*</h1>
    <p>* - максимальная скидка на услуги сервиса составляет 30% от нашего заработка.</p>
    <?php
	
	} //end statistics
} else {
?>
<h1>Кабинет пользователя</h1>
<form ACTION="login.php?accesscheck=cabinet.php?history" METHOD="POST" name="login" id="auth">
<table align="center" border="0">
	<tr><td height="50"></td></tr>
	<tr><td colspan="2">Для просмотра данных вам нужно авторизоваться<br /><br /></td></tr>
	<tr><td>Имя</td><td class="form-normal"><input name="user" type="text" /></td></tr>
    <tr><td>Пароль</td><td class="form-normal"><input name="pass" type="password" /></td></tr>
	<tr><td>          <input name="" type="image" src="i/empty.gif" width="1" height="1"  style="border:none"/></td>
    <td><input type="button" class="button1" value="Вход" onClick="javascript:d.$('auth').submit();"></td></tr>
    <tr><td></td><td><a href="register.php?forgot">Забыл пароль?</a></td></tr>
</table>
</form>



<!--======================-->

<?php 
}   ?>
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