<?php require_once('Connections/ma.php'); ?>
<?php require_once('function.php');



$currentPage = $_SERVER["PHP_SELF"];

if ( !isset($_SESSION['Partner_AuthUsername']) ) { //begin of false user auth
					  
					  
					  
}else  //begin of true user auth
{


	$max = 20;
	$page = 0;
	if (isset($_GET['page'])) {
	  $page = (int)htmlspecialchars($_GET['page']);
	}
	$start = $page * $max;

$query_client="SELECT partner.id, partner.nikname, partner.purse_z, partner.email, partner.clid, partner.phone FROM partner WHERE partner.nikname='".$_SESSION['Partner_AuthUsername']."';";
	$client = _query($query_client, 'partner.php 1');
	$num_row_client=$client->num_rows;
	$row_client=$client->fetch_assoc();
	
		$query="SELECT SUM(summ) AS total_payed, partnerid, summ, purse FROM payment_out WHERE partnerid=".$row_client['id']."
		AND retval=0
		GROUP by partnerid ORDER BY time desc";
		$partner_pay=_query($query, "partner.php partner_pay");
		$partner_pay=$partner_pay->fetch_assoc();
		
		
$query_orders = "SELECT bonus, partner_bonus.time, (select extname from currency where name=currin) as currin, 
(select extname from currency where name=currout) as currout, summin, summout
FROM partner_bonus, orders
WHERE partner_bonus.partnerid =".$row_client['id']."
AND partner_bonus.orderid = orders.id
ORDER BY time DESC";


			//AND NOT orders.clid='".$row_client['clid']."'
$query_limit_orders = sprintf("%s LIMIT %d, %d", $query_orders, $start, $max);
$orders = _query($query_limit_orders, 'partner.php 2');
$row_orders = $orders->fetch_assoc();
// oper_sum - сумма по операциям совершенных клиентом
$oper_sum = _query("SELECT SUM(bonus) as sum FROM partner_bonus  
			WHERE partnerid=".$row_client['id'], "partner.php 3");
$oper_sum = $oper_sum->fetch_assoc();
$oper_sum = $oper_sum["sum"];

// clients_count - количество уникальных клиентов, совершивших обмен
$clients_count=_query("SELECT COUNT(DISTINCT orders.clid) FROM orders, payment 
			WHERE payment.orderid=orders.id 
			AND payment.ordered=1 
			AND payment.canceled=1
			AND orders.partnerid=".$row_client['id'], 'partner.php 4');
$clients_count=$clients_count->fetch_assoc();

$clients_count=$clients_count['COUNT(DISTINCT orders.clid)'];

$select="select count(id) as total from clients where partnerid=".$row_client['id']." and activated=1";
$query=_query($select, "partner.php 23");
$reg_clients=$query->fetch_assoc();

// hit_count - количество хитов посетителей партнера
$hit_count_rows=_query("SELECT count(referer.clid) as count FROM referer 
			WHERE partnerid=".$row_client['id'], 'partner.php 5');
$hint_count_rows=$hit_count_rows->fetch_assoc();
$hit_count_rows=$hit_count_rows['count'];

$hit_count_unique=_query("SELECT DISTINCT referer.clid FROM referer 
			WHERE partnerid=".$row_client['id'], 'partner.php 6');
$hit_count_unique=$hit_count_unique->num_rows;

if (isset($_GET['total'])) {
  $total1 = (int)htmlspecialchars($_GET['total']);
} else {
  $all_orders = _query($query_orders, 'partner.php 7');
  $total1 = $all_orders->num_rows;
}
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
	$query_disc="SELECT pndiscount.id, pndiscount.discount, pndiscount.users, pndiscount.descr, pndiscount.per_click  FROM pndiscount WHERE pndiscount.users <= ".$clients_count." AND pndiscount.till >= ".$clients_count.";";
	$pdiscount=_query2($query_disc, 'partner.php 8');
	$numrow_discount=$pdiscount->num_rows;
	$partn_row_discount=$pdiscount->fetch_assoc();
	if ( !$numrow_discount) {
		$query_disc="SELECT pndiscount.id, pndiscount.discount, pndiscount.users, pndiscount.descr, pndiscount.per_click FROM pndiscount WHERE pndiscount.users = 0;";
	$pdiscount=_query($query_disc, 'partner.php 9');
	$numrow_discount=$pdiscount->num_rows;
	$partn_row_discount=$pdiscount->fetch_assoc();
		
		}	
	//  end discount
		if ( $clients_count < 10  ) {$hit_count=0; }
	elseif  ($clients_count < 30 ) {$hit_count=($clients_count-9)*$partn_row_discount['per_click']; }
	elseif ($clients_count < 100 ) {$hit_count=20*0.01+($clients_count-30)*$partn_row_discount['per_click']; }
	else { $hit_count=20*0.01+70*0.03+($clients_count-100)*$partn_row_discount['per_click']; }
//echo ((round($oper_sum,2) + $hit_count)-$partner_pay['total_payed']);
//echo ( isset($_POST['summ']) ) ? " ". $_POST['summ'] : "";
	if ( isset($_POST['order']) && isset($_POST['summ']) ) {
			$maxpay = (round($oper_sum,2) + $hit_count)-$partner_pay['total_payed'];
			if ( $_POST['summ']-0.01> $maxpay ) { $mess = "Слишком большая сумма для вывода"; }
			else {
				$query="insert into payment_out ( purse, summ, partnerid, retval ) values('".
						$row_client['purse_z']. "',".
						floatval($_POST['summ']). ",".
						$row_client['id'].",
						1)";
				$insert=_query($query,"partner.php payment_out");
				$id=mysqli_insert_id($GLOBALS['ma']);
				$response = $wmxi->X2(
				$id, 											 
				$partner_purse,
				$row_client['purse_z'], 
				floatval($_POST['summ']),
				0, 
				'',
				'Партнерское вознаграждение. Партнер: '.$_SESSION['Partner_AuthUsername'].
				" Идентификатор: ".$row_client['id']."." ,
				0,
				0);
				$structure = $parser->Parse($response, DOC_ENCODING);
				$transformed = $parser->Reindex($structure, true);
				//_error(print_r($transformed,1),"5","5");
			if ( intval($transformed["w3s.response"]["retval"]) != 0 ){ // платеж не прошел
				$update = "UPDATE payment_out SET retval=".
				htmlspecialchars(@$transformed["w3s.response"]["retval"], ENT_QUOTES).
				", retdesc='".
				htmlspecialchars(@$transformed["w3s.response"]["retdesc"], ENT_QUOTES).
				"' WHERE id=".$id;
				//echo $update;
				_query ($update, "payment update");
				$mess = "Платеж не выполнен. Детали ошибки: <br />
				номер - ".htmlspecialchars(@$transformed["w3s.response"]["retval"])."<br />
				описание - ".htmlspecialchars(@$transformed["w3s.response"]["retdesc"]);	
			}else{ 
				$update = "UPDATE payment_out SET retval=0 WHERE id=".$id;
				_query ($update, "payment update");			
				
				$mess="Платеж выполнен успешно. Благодарим Вас за сотрудничество!"; 
				$query="SELECT SUM(summ) AS total_payed, partnerid, summ, purse FROM payment_out WHERE partnerid=".$row_client['id']."
							AND retval=0 GROUP BY partnerid
							ORDER BY time desc";
				$partner_pay=_query($query, "partner.php partner_pay");
				$partner_pay=$partner_pay->fetch_assoc();
			}
		}


	}
} // end of true user auth

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Кабинет партнера</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<?php require_once($serverroot."Connections/meta.php"); ?>
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
		<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
        <link rel="shortcut icon" href="<?=$siteroot?>i/favico.ico"/>
        <script src="_main.js"></script>
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
	<script src="fun.js"></script>
	<body>
	    <div class="wrapper">
            <div class="wrapper-inn">

                <?php require_once("siti/inc_top.php");?>


                <div class="middle clear">

                    <!-- Start left column -->
                    <?php require_once("siti/inc_left.php");?>
                    <!-- End left column -->

                    <!-- Start central column -->
                    <div class="c-col">
                        
                        <div class="intro">
                            
       <?php if ( isset($_SESSION['Partner_AuthUsername']) ) {?>
	<table width="550" align="center" border="0">
    <tr><td align="center"><h1>Кабинет партнера</h1></td></tr>
    
    <tr>
    <td align="right" valign="top"><h2>
    <?='Партнер: '.$_SESSION['Partner_AuthUsername'].'<br />';?>
    <?='Идентификатор: '.$row_client['id'].'';?></h2>
    Статистика</a><br />
	 <a href="<?=$siteroot?>partner_register.php">Персональные данные</a><br />
	 <a href="<?=$siteroot?>partner_banner.php">Рекламные материалы и баннеры</a><br />
    <a href="<?=$siteroot?>partner_rates.php">Курсы валют</a><br />
    <a href='<?=$siteroot?>partner_login.php?doLogout=true'>Выход</a>
    </td>
    </tr>
    <tr><td height="50" id="head_small"><strong><?=isset($mess) ? $mess : "";?>
	</strong></td></tr>
    </table>

<div align="center"><h2 align="center">Операции</h2></div>
<table border="0" align="center" width="500" cellpadding="4" cellspacing="0">
  <tr   class='td_head'>
    <td align="left" height="30" width="90">Дата</td>
    <td align="center" height="30" colspan="3">Направление</td>
    <td align="right"  height="30">Начислено</td>

  </tr>
  <?php   $td_style='td';$i=0;
   do { 
  $i=$i+1;
  ?>
     <tr>
      <td align="left" height="20" class="<?=$td_style; ?>"><?=substr($row_orders['time'],0,10); ?>&nbsp; </td>
	 <?php /*?><td><?php echo $row_orders['orderid']; ?>&nbsp; </td><?php */?>
     <td  class="<?=$td_style; ?>"> <?=$row_orders['currin']?></td>
      <td  class="<?=$td_style; ?>" width="20">-></td>
    <td  class="<?=$td_style; ?>"><?=$row_orders['currout']?></td>
	 <?php /*?><td><?php echo $row_orders['ordered']==1 ? 'оформлена' : 'неоформлена'; ?>&nbsp; </td><?php */?>
     <?php /*?><td><?php echo $row_orders['id']; ?>&nbsp; </td><?php */?>
    <?php /*?>  <td><?php echo $row_orders['canceled']!=TRUE ? '+' : '-'; ?>&nbsp; </td><?php */?>
    <?php /*?>  <td><?php echo $row_orders['canceled']!=TRUE ? '+' : '-'; ?></td><?php */?>

      <td align="right"  class="<?=$td_style; ?>"><?= $row_orders['bonus']; ?>&nbsp; </td>

    </tr>
    <?php 
	if ( $i==1 ) {$td_style='td_white'; $i=-1; } else {$td_style='td';}
	} while ($row_orders = mysql_fetch_assoc($orders)); ?>
</table>
<br />
<table border="0" align="center">
  <tr>
    <td align="center"><?php if ($page > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, 0, $queryString_orders); ?>">Первая</a>
        <?php } // Show if not first page ?>
		<?php if ($page > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, max(0, $page - 1), $queryString_orders); ?>"><<Пред.</a>
        <?php } // Show if not first page ?>
		<?php if ($page < $totalPages) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, min($total1, $page + 1), $queryString_orders); ?>">След.>></a>
        <?php } // Show if not last page ?>
		<?php if ($page < $totalPages) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, $totalPages, $queryString_orders); ?>">Последн.</a>
        <?php } // Show if not last page ?></td>
  </tr>
  <tr>
  	<td align="center">Запись с <?php echo ($start + 1) ?> по <?php echo min($start + $max, $total1) ?> из <?php echo $total1; ?>
    </td>
   </tr>
</table><br />
<br />
<div align="center"><h2 align="center">Продажи</h2>
<table border="0" align="center" width="500" cellpadding="4" cellspacing="0">
  <tr   class='td_head'>
    <td align="left" height="30" width="90">Дата</td>
    <td align="center" height="30" colspan="3">Направление</td>
    <td align="right"  height="30">Начислено</td>

  </tr>
  <?php   $td_style='td';$i=0;
   do { 
  $i=$i+1;
  ?>
     <tr>
      <td align="left" height="20" class="<?=$td_style; ?>"><?=substr($row_orders['time'],0,10); ?>&nbsp; </td>
     <td  class="<?=$td_style; ?>"> <?=$row_orders['currin']?></td>
      <td  class="<?=$td_style; ?>" width="20">-></td>
    <td  class="<?=$td_style; ?>"><?=$row_orders['currout']?></td>
     <td align="right"  class="<?=$td_style; ?>"><?= $row_orders['bonus']; ?>&nbsp; </td>

    </tr>
    <?php 
	if ( $i==1 ) {$td_style='td_white'; $i=-1; } else {$td_style='td';}
	} while ($row_orders = mysql_fetch_assoc($orders)); ?>
</table>
<br />
<br />

<h1>Статистика:</h1>
<?php /*?><p>Статистика временно недоступна</p><?php */?>
<table align="center" border="0" width="350">
<tr class="td_head" height="30"><td colspan="2"></td></tr>

<tr><td>Привлеченные посетители:</td> <td align="right"><strong><?=$clients_count?></strong> </td></tr>
<tr><td><br />Зарегистрированные посетители: </td><td align="right"><br /><strong><?=$reg_clients['total']?></strong></td></tr>
<tr><td>Количество хитов: </td><td align="right"><strong><?=$hit_count_rows?></strong></td></tr>
<tr><td>Уникальные посетители: </td><td align="right"><strong><?=$hit_count_unique?></strong></td></tr>
<tr><td>Текущий процент: </td><td  align="right"><strong><?=($partn_row_discount['discount']*100-100)?>%</strong></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Заработано на обменах: </td><td align="right"><strong><?=round($oper_sum,2)?> WMZ</strong></td></tr>
<tr><td>Заработано на переходах: </td><td align="right"><strong><?=$hit_count?> WMZ</strong></td></tr>
<tr><td>Всего заработано: </td><td align="right"><b><?=(round($oper_sum,2) + $hit_count)?> WMZ</b>  </td></tr>
<tr><td>Уже выведено: </td><td align="right"><strong><?=$partner_pay['total_payed'] == '' ? '0' : $partner_pay['total_payed'];?> WMZ</strong></td>
</tr></table>
<br />
<br />

<table align="center"><form action="<?=$siteroot?>partner.php" method="post" id="pay" name="pay">
<tr><td colspan="2"><h2 align="center">Выплата вознаграждения</h2> <br />
</td></tr>
<tr><td align="right" valign="top">
Кошелек Z:</td><td class="form-normal"> <input disabled="disabled" type="text" name="purse" value="<?=$row_client['purse_z']; ?>" maxlength="13" /><br />
<span style="color:#666; font-size:9px">Из соображений безопасности партнерские <br />
        выплаты осуществляются только <br />на кошелек, указанный при регистрации.</span></td></tr>
<tr><td align="right">
Сумма (минимум 0.5 WMZ) :</td><td><input class="form-normal" type="text "'<?=(round($oper_sum,2) + $hit_count) < 0.5 ? 'disabled' : ''; ?>' name="summ" value="<?=
round((round($oper_sum,2) + $hit_count)-$partner_pay['total_payed'],2); ?>" maxlength="13" size="6"/> WMZ </td></tr>
<tr><td></td><td><br />
<input type="button" class="button1" onClick="<?=round($oper_sum + $hit_count,2) < 0.5 ? '' : "javascript:d.$('pay').submit();"; ?>" value="Перевести">

<input type="hidden" name="order" value="out"/>
<?php

	$discount_table_query = 'SELECT * FROM pndiscount';
	$discount_table = _query($discount_table_query,'partner.php 5');
	?>
  
    </td></tr>  </form></table>
   
<br />
<br />
<h2>Уровень вознаграждения</h2>


	<table border="0" align="center" cellpadding="4" cellspacing="0" width="350">
  <tr class="td_head" height="30"><td rowspan="2"></td>
  <td colspan="2" align="center" valign="middle">Кол-во посетителей</td>
  <td rowspan="2" valign="middle">Скидка</td>
  <td rowspan="2" valign="middle">Переходы</td>
  <tr class="td_head">
    <td align="center" valign="middle">От</td>
    <td align="center" valign="middle">До</td>
  </tr>
  <tr><td height="10"></td></tr>
<?php while ($discount_table_row = mysql_fetch_assoc($discount_table)) { 
		$color='';
		if ( $partn_row_discount['discount'] == $discount_table_row['discount'] ) {
		$color='class="td_white"';
		}
?>

    <tr>
      <td height="20" width="25%" align="right" <?=$color?>><?=$discount_table_row['descr'];?></td>
      <td width="25%"  align="right" <?=$color?>><?=$discount_table_row['users']; ?></td>
      <td width="25%"  align="right" <?=$color?>><?=$discount_table_row['till']; ?></td>
      <td width="25%"  align="right" <?=$color?>><b><?=(($discount_table_row['discount']-1)*100);?>%</b></td>
      <td width="25%"  align="right" <?=$color?>><?=$discount_table_row['per_click']; ?></td>
    </tr>
<?php	//	. 
	};	
?>
	<tr><td height="50"></td></tr>
    </table>

</div>
<?php
mysql_free_result($orders);
} //end true user auth 
else
{
	
?>
	<table width="550" align="center">
    <tr><td align="center"><h1>Кабинет партнера</h1></td></tr>
    
    <tr>
    <td align="right" valign="top">
Вход<br />
    <a href="partner.php">Статистика</a><br />
<!--    <a href="cabinet.php?history">История операций</a><br />-->
	<a href="partner_register.php"><?php echo ( isset($_SESSION['Partner_AuthUsername']) ) ? 'Персональные данные'  :  'Регистрация' ?></a><br />
    <?php if ( isset($_SESSION['Partner_AuthUsername']) ){ ?>
    <a href='<?=$siteroot?>partner_login.php?doLogout=true'>Выход</a> <?php } ?>
    </td>
    </tr>
    <tr><td height="50"></td></tr>
</table>
<div align="center">
<form ACTION="partner_login.php?accesscheck=partner.php" METHOD="POST" name="login" id="pauth">
<table width="300" align="center">
<tr><td align="right">Имя </td><td class="form-normal"> <input name="user" type="text" /></td></tr>
<tr><td align="right">Пароль </td><td class="form-normal"> <input name="pass" type="password" /></td></tr>
<tr>          <input name="" type="image" src="<?=$siteroot?>i/empty.gif" width="0" height="0"  style="border:none" /><td></td>
<td><input type="button" class="button1" onClick="d.$('pauth').submit();" value="Вход"></td></tr>
<tr><td></td><td><a href="<?=$siteroot?>partner_register.php?forgot">Забыл пароль?</a></td></tr></table>
</form>
</div>


<?php }?> 
                            
                            
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