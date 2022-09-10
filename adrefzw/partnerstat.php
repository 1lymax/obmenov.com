<?php require_once('../Connections/ma.php'); ?>
<?php require_once('../function.php'); 

$pid='';
if ( isset($_GET['pnik']) ) {
	$_SESSION['Partner_AuthUsername']=$_GET['pnik'];
}
if ( isset($_GET['pid']) ) {
	$pid=" and partner.id=".(int)$_GET['pid'];
}
if ( isset($_GET['clid']) ) {
	$select="select nikname from partner where clid='".$_GET['clid']."'";
	$query=_query($select, "adrefzw");
	$client=$query->fetch_assoc();
	$_SESSION['Partner_AuthUsername']=$client['nikname'];
	if ( isset($_GET['action']) && $_GET['action']=="cabinet" ) {
		header("Location: https://obmenov.com/partner.php");
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Обменов.ком :: Кабинет партнера</title>
<link href="../wm.css" rel="stylesheet" type="text/css" />

</head>


<body>
<?php include_once ("top.php"); ?>

<?php
$query_client="SELECT partner.id, partner.nikname, partner.email, partner.clid, partner.phone FROM partner ".$pid;
	$client = _query($query_client, 'partner.php 1');
	$num_row_client=mysql_num_rows($client);
	
	
while ( $row_client=mysql_fetch_assoc($client) ) {

$page['currentPage'.$row_client['id']] = $_SERVER["PHP_SELF"];

	//${"max".$row_client['id']} = 10;
	$page['max'.$row_client['id']]=10;
	$page['page'.$row_client['id']] = 0;
	if (isset($_GET['page'.$row_client['id']])) {
	  $page['page'.$row_client['id']] = htmlspecialchars($_GET['page'.$row_client['id']]);
	}
	$page['start'.$row_client['id']] = $page['page'.$row_client['id']] * $page['max'.$row_client['id']];


	
		$query="SELECT SUM(summ) AS total_payed, partnerid, summ, purse FROM payment_out WHERE partnerid=".$row_client['id']." AND retval=0 group by partnerid ORDER BY time desc";
		$partner_pay=_query($query, "partner.php partner_pay");
		$partner_pay=mysql_fetch_assoc($partner_pay);
		
		
$query_orders = "SELECT bonus, time, orderid FROM partner_bonus  
			WHERE partnerid=".$row_client['id'];
			//AND NOT orders.clid='".$row_client['clid']."'
$query_limit_orders = sprintf("%s LIMIT %d, %d", $query_orders, $page['start'.$row_client['id']], $page['max'.$row_client['id']]);
$orders = _query($query_limit_orders, 'partner.php 2');
$row_orders = mysql_fetch_assoc($orders);
// oper_sum - сумма по операциям совершенных клиентом
$oper_sum = _query("SELECT SUM(bonus) as sum FROM partner_bonus  
			WHERE partnerid=".$row_client['id'], "partner.php 3");
$oper_sum = mysql_fetch_assoc($oper_sum);
$oper_sum = $oper_sum["sum"];

// clients_count - количество уникальных(привлеченных) клиентов, совершивших обмен
$clients_count=_query("SELECT COUNT(DISTINCT orders.clid) FROM orders, payment 
			WHERE payment.orderid=orders.id 
			AND payment.ordered=1 
			AND payment.canceled=1
			AND orders.partnerid=".$row_client['id'], 'partner.php 4');
$clients_count=mysql_fetch_assoc($clients_count);
if ( $row_client['id']==302 ) {
	$clients_count=$clients_count['COUNT(DISTINCT orders.clid)']+62;
}else{
	$clients_count=$clients_count['COUNT(DISTINCT orders.clid)'];
}
// hit_count - количество хитов посетителей партнера
$hit_count_rows=_query("SELECT referer.clid FROM referer 
			WHERE partnerid=".$row_client['id'], 'partner.php 5');
$hit_count_rows=mysql_num_rows($hit_count_rows);
if ( $hit_count_rows == 0 ) {continue;}
$hit_count_unique=_query("SELECT DISTINCT referer.clid FROM referer 
			WHERE partnerid=".$row_client['id'], 'partner.php 6');
$hit_count_unique=mysql_num_rows($hit_count_unique);

if (isset($_GET['total'.$row_client['id']])) {
  $page['total'.$row_client['id']] = htmlspecialchars($_GET['total'.$row_client['id']]);
} else {
  $all_orders = _query($query_orders, 'partner.php 7');
  $page['total'.$row_client['id']] = mysql_num_rows($all_orders);
}
$page['totalPages'.$row_client['id']] = ceil($page['total'.$row_client['id']]/$page['max'.$row_client['id']])-1;

$page['queryString_orders'.$row_client['id']] = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $page['params'.$row_client['id']] = explode("&", $_SERVER['QUERY_STRING']);
  $page['newParams'.$row_client['id']] = array();
  foreach ($page['params'.$row_client['id']] as $page['param'.$row_client['id']]) {
    if (stristr($page['param'.$row_client['id']], "page".$row_client['id']) == false && 
        stristr($page['param'.$row_client['id']], "total".$row_client['id']) == false) {
      array_push($page['newParams'.$row_client['id']], $page['param'.$row_client['id']]);
    }
  }
  if (count($page['newParams'.$row_client['id']]) != 0) {
    $page['queryString_orders'.$row_client['id']] = "&" . htmlentities(implode("&", $page['newParams'.$row_client['id']]));
  }
}
$page['queryString_orders'.$row_client['id']] = sprintf("&total".$row_client['id']."=%d%s", $page['total'.$row_client['id']], $page['queryString_orders'.$row_client['id']]);

	//discount
	$query_disc="SELECT pndiscount.id, pndiscount.discount, pndiscount.users, pndiscount.descr, pndiscount.per_click FROM pndiscount WHERE pndiscount.users <= ".$clients_count." AND pndiscount.till >= ".$clients_count." order by id desc;";
	$discount=_query2($query_disc, 'partner.php 8');
	$numrow_discount=mysql_num_rows($discount);
	$partn_row_discount=mysql_fetch_assoc($discount);
	if ( !$numrow_discount) {
		$query_disc="SELECT pndiscount.id, pndiscount.discount, pndiscount.users, pndiscount.descr, pndiscount.per_click FROM pndiscount WHERE pndiscount.users = 0;";
	$discount=_query($query_disc, 'partner.php 9');
	$numrow_discount=mysql_num_rows($discount);
	$partn_row_discount=mysql_fetch_assoc($discount);
		
		}	
	//  end discount

?>
<hr />
<table border="0" align="center" width="900" cellpadding="4" cellspacing="0">
<tr><td colspan="2" align="left">Партнер - ID: <?=$row_client['id']." Логин: ". $row_client['nikname']?></td></tr>
<?php /*?>  <tr   class='td_head'>
	<td></td>
    <td align="left" height="30">Дата</td>
    <td align="right"  height="30">Начислено</td>

  </tr>
  <?php   $td_style='td';$i=0;
   do { 
  $i=$i+1;
  ?>
     <tr>
      <td  class="<?=$td_style; ?>"><a href="orders.php?showid=<?= $row_orders['orderid']; ?>"><?= $row_orders['orderid']; ?></a>&nbsp; </td>
      <td align="left" height="20" class="<?=$td_style; ?>"><?php echo substr($row_orders['time'],0,10); ?>&nbsp; </td>


	 <?php /*?><td><?php echo $row_orders['ordered']==1 ? 'оформлена' : 'неоформлена'; ?>&nbsp; </td><?php */?>
     <?php /*?><td><?php echo $row_orders['id']; ?>&nbsp; </td><?php */?>
    <?php /*?>  <td><?php echo $row_orders['canceled']!=TRUE ? '+' : '-'; ?>&nbsp; </td><?php */?>
    <?php /*?>  <td><?php echo $row_orders['canceled']!=TRUE ? '+' : '-'; ?></td>

      <td align="right"  class="<?=$td_style; ?>"><?= $row_orders['bonus']; ?>&nbsp; </td>

    </tr>
    <?php 
	if ( $i==1 ) {$td_style='td_white'; $i=-1; } else {$td_style='td';}
	} while ($row_orders = mysql_fetch_assoc($orders)); ?>
    
    <tr><td></td></tr>
</table>
<br />
<table border="0" align="center">
  <tr>
    <td><?php if ($page['page'.$row_client['id']] > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page".$row_client['id']."=%d%s", $page['currentPage'.$row_client['id']], 0, $page['queryString_orders'.$row_client['id']]); ?>">Первая</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($page['page'.$row_client['id']] > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page".$row_client['id']."=%d%s", $page['currentPage'.$row_client['id']], max(0, $page['page'.$row_client['id']] - 1), $page['queryString_orders'.$row_client['id']]); ?>"><<Пред.</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($page['page'.$row_client['id']] < $page['totalPages'.$row_client['id']]) { // Show if not last page ?>
        <a href="<?php printf("%s?page".$row_client['id']."=%d%s", $page['currentPage'.$row_client['id']], min($page['total'.$row_client['id']], $page['page'.$row_client['id']] + 1), $page['queryString_orders'.$row_client['id']]); ?>">След.>></a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($page['page'.$row_client['id']] < $page['totalPages'.$row_client['id']]) { // Show if not last page ?>
        <a href="<?php printf("%s?page".$row_client['id']."=%d%s", $page['currentPage'.$row_client['id']], $page['totalPages'.$row_client['id']], $page['queryString_orders'.$row_client['id']]); ?>">Последн.</a>
        <?php } // Show if not last page ?></td>
  </tr>
  <tr>
  	<td align="center">Запись с <?php echo ($page['start'.$row_client['id']] + 1) ?> по <?php echo min($page['start'.$row_client['id']] + $page['max'.$row_client['id']], $page['total'.$row_client['id']]) ?> из <?php echo $page['total'.$row_client['id']]; ?>
    </td>
   </tr>
</table><br />
<br />
<br /><?php */?>
<?php
	if ( $clients_count < 10  ) {$hit_count=0; }
	elseif  ($clients_count < 30 ) {$hit_count=($clients_count-10)*$partn_row_discount['per_click']; }
	elseif ($clients_count < 100 ) {$hit_count=20*0.01+($clients_count-30)*$partn_row_discount['per_click']; }
	else { $hit_count=20*0.01+70*0.03+($clients_count-100)*$partn_row_discount['per_click']; }
?>
<tr><td>Привлеченные</td><td>Хиты</td><td>Уникальные</td><td>Текущий процент</td><td>Заработано на обменах</td>
<td>Заработано на переходах</td><td>Всего</td><td>Выведено</td></tr>
<tr><td><?=$clients_count?></td>
<td><?=$hit_count_rows?></td>
<td><?=$hit_count_unique?></td>
<td><?=($partn_row_discount['discount']*100-100)?>%</td>
<td><?=round($oper_sum,2)?> WMZ</td>
<td><?=$hit_count?>WMZ</td>
<td><?=(round($oper_sum,2) + $hit_count)?> WMZ</td>
<td><?=$partner_pay['total_payed'] == '' ? '0' : $partner_pay['total_payed']?> WMZ</td>
</tr>
<tr><td colspan="5"><a href="orders.php?search1partnerid=<?=$row_client['id']?>">Заявки</a> :: 
<a href="referer.php?search1partnerid=<?=$row_client['id']?>">Переходы</a> ::
<a href="partnerstat.php?action=cabinet&clid=<?=$row_client['clid']?>">Войти на сайт-></a> ::</td></tr>
</table>

<?php
}
mysql_free_result($orders);

	
?>

