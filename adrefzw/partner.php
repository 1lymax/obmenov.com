<?php require_once('../Connections/ma.php');
		require_once('../function.php');
		
if ( isset($_POST['balance']) ) {
	$update="insert into partner_bonus (sum, bonus, partnerid, orderid, comment) values (
										".(float)$_POST['sum'].",
										".(float)$_POST['balance'].",
										".(int)$_POST['partnerid'].",
										".(isset($_POST['orderid'])?(int)$_POST['orderid']:0).",
										'".mysql_real_escape_string($_POST['comment'])."')";
	$query=_query($update,"");
	
}


$searcharr = array();
$searchvalues = array();

$searchvalues['search1id']='';
$searchvalues['search1nikname']='';
$searchvalues['search1email']='';
$searchvalues['search1clid']='';
$searchvalues['search1vip']='';


$currentPage = $_SERVER["PHP_SELF"];

reset($_GET);
$wordlen=strlen("search");
while (list($key, $val) = each($_GET)) {
	
	$table1="partner";
	$table2="";
	//echo substr($key,0,6);
	if ( substr($key,0,$wordlen)=="search") {
		if ( substr($key,$wordlen,1)=="1" ) {
			$table=$table1; }
		elseif ( substr($key,$wordlen,1)=="2" ) {
			$table=$table2; }
			
		$searcharr[$key]=" AND ".$table.".".substr($key,$wordlen+1,strlen($key)-$wordlen)." LIKE '%".$val."%' ";
		$searchvalues[$key]=$val;
	}else {
		//$searcharr[$key]="";
	}
	
}

$maxRows = 100;
$pageNum = 0;
if (isset($_GET['page'])) {
  $pageNum = $_GET['page'];
}
$startRow = $pageNum * $maxRows;



$query = "SELECT partner.id, partner.nikname, purse_z, purse_lr, email, clid, `limit` FROM partner  WHERE 1=1 ";
while (list($key, $val) = each($searcharr)) {
	$query=$query.$val;
	
}
$query=$query." order by id desc";

$query_limit= sprintf("%s LIMIT %d, %d", $query, $startRow, $maxRows);
$referer = mysql_query($query_limit, $ma) or die(mysql_error());
$row = mysql_fetch_assoc($referer);

if (isset($_GET['total'])) {
  $totalRows = $_GET['total'];
} else {
  $all = mysql_query($query);
  $totalRows = mysql_num_rows($all);
}
$totalPages = ceil($totalRows/$maxRows)-1;

$queryString = "";
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
    $queryString = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString = sprintf("&total=%d%s", $totalRows, $queryString);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Обменов.ком :: Партнеры</title>
<link href="../wm.css" rel="stylesheet" type="text/css"> 
<script type="text/javascript" src="../fun.js"></script>
</head>
<body>
<table class="tableborder" cellpadding="5" cellspacing="0">
  <tr>
    <td>id</td>
  
    <td>Ник</td>
    <td>Емейл</td>
    <td>Кошелек1</td>
    <td>Кошелек2</td>
    <td>VIP</td>
    <td></td>
  </tr>
 <form action="partner.php" method="get">
  <tr>

    <td><input name="search1id" size="13" value="<?=$searchvalues['search1id']?>" /></td>
    <td><input name="search1nikname" size="13" value="<?=$searchvalues['search1nikname']?>" /></td>
    <td><input name="search1email" size="25" value="<?=$searchvalues['search1email']?>" /></td>
    <td></td>
    <td></td>
    <td><select name="search1vip">
    <option <?=$searchvalues['search1vip']=="" ? "selected='selected'" : ""?> value=""></option>
    <option <?=$searchvalues['search1vip']=="1" ? "selected='selected'" : ""?> value="1">Да</option>
    <option <?=$searchvalues['search1vip']=="0" ? "selected='selected'" : ""?> value="0">Нет</option>
    </select></td>

    <td><input type="submit" value="Поиск" /></td>
  </tr>
    <tr>
    <td></td>
  
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  </form>
  <?php do { ?>
    <tr>
      <td><?=$row['id']; ?></td>
      <td><?=$row['nikname']; ?></td>
      <td><?=$row['email']; ?></td>
      <td><?=$row['purse_z']; ?></td>
      <td><?=$row['purse_lr']; ?></td>
      <td><a href="<?=$siteroot?>adrefzw/partner.php?search1id=<?=$row['id']?>">>> Выбрать запись >></a></td>
    </tr>    
    
	<?php if ( mysql_num_rows($referer)==1) { ?>
    <tr><td height="20"></td></tr>
    <?php 
	$select="SELECT SUM( bonus ) AS sum FROM partner_bonus WHERE partnerid=".$row['id'];
	$query=_query($select,"");
	$sum=$query->fetch_assoc();	
	$select="SELECT SUM( orders.attach ) AS spend FROM orders, payment_out, payment
						WHERE orders.id = payment_out.payment
						AND orders.id = payment.orderid
						AND payment_out.partnerid =".$row['id']."
						AND payment.ordered =1
						AND payment.canceled=1
						GROUP BY payment_out.partnerid";
	$query=_query($select,"");
	$spend=$query->fetch_assoc();		
	?>
    <tr><td colspan="5">Всего пополнено: <?=round($sum['sum'],2)?> USD</td></tr><tr><td>Потрачено: <?=round($spend['spend'],2)?> USD</td></tr>
    <tr><td colspan="5">Остаток: <?=round($sum['sum'],2)-round($spend['spend'],2)?> USD</td></tr>
    <tr><td height="15"></td></tr>
    <form action="partner.php?<?=$queryString?>" method="post">
    <input type="hidden" name="partnerid" size="20" value="<?=$row['id']?>"  />
    <tr><td>
    Добавление баланса -></td>
    <td align="right">Сумма оплаты :</td><td><input type="text" size="7" name="sum" value="" /> USD</td>
    <td align="right">На баланс: </td><td><input type="text" size="7" name="balance" value="" /> USD</td>
	<td align="right">Комментарий:</td><td><input type="text" name="comment" size="20"  /></td><td><input type="submit" value="Добавить" /></form></td>
    </tr>
    
    <tr><td colspan="5"><form action="partner.php?<?=$queryString?>" method="post">
    Лимит партнера: <input type="text" size="7" name="limit" value="<?=$row['limit']?>" /> USD
    <input type="submit" value="Обновить" /></form></td></tr>
    <tr><td height="15"></td></tr>
    <tr><td>Действия:</td></tr>
    <tr><td colspan="5"><a href="<?=$siteroot?>adrefzw/orders.php?search1clid=<?=$row['clid']?>">Заявки партнера:</a> | 
    <a href="<?=$siteroot?>adrefzw/orders.php?search1clid=<?=$row['clid']?>">Заявки партнера:</a></td></tr>
    <tr><td colspan="9">
    <hr />
    <table width="900">
<tr class="tableborder2">
<td>Время</td><td>Примечание</td><td align="right">Сумма</td><td align="right">Зачтено</td><td align="right">OrderID</td></tr>
<?php /*$select="select * from partner where id=".$partnid;
$query=_query($select,"");
$partner=$query->fetch_assoc();*/
$select="select sum(bonus) as itog from partner_bonus where partnerid=".$row['id']." group by partnerid";
$query=_query($select,"");
$itog=$query->fetch_assoc();

$select="select sum,bonus,orderid, comment,time from partner_bonus where partnerid=".$row['id']." order by time desc limit 0,30";
$query=_query($select,"");
while ( $bonus=$query->fetch_assoc() ) { 
?>
<tr><td><?=$bonus['time']?></td><td><?=$bonus['comment']?></td><td align="right"><?=round($bonus['sum'],2)?></td>
<td  align="right"><?=round($bonus['bonus'],2)?></td><td align="right"><?=($bonus['orderid']!=0?$bonus['orderid']:"")?></td></tr>
<?php } ?>
<tr class="tableborder2"><td colspan="4"></td></tr>
<tr><td colspan="3"></td><td align="right"><?=$itog['itog']?></td></tr>
</table>
    
    </td></tr>
    <tr><td colspan="9">
    <hr />
    <?php if ( mysql_num_rows($referer)==1 and isset($row['id']) && $row['id']==2148 ) { ?>
    <table width="1000">
<tr class="tableborder2">
<td>ID</td><td>Направление</td><td align="right">Процент</td><td align="right">Заработок, USD</td><td align="right">В баланс</td><td align="right">Действия</td></tr>
<?php /*$select="select * from partner where id=".$partnid;
$query=_query($select,"");
$partner=$query->fetch_assoc();*/
$partnerid=$row['id'];
$select="select orders.id, orders.currin, orders.currout, orders.summin, orders.summout, orders.discammount, orders.official_course,
			(orders.official_course/((orders.summout+orders.discammount)/orders.summin)) as addon, orders.course2usd,
			(orders.summin*((orders.official_course/
							((orders.summout+orders.discammount)/orders.summin))-1)*orders.course2usd) as spred, 
				(select id from partner_bonus where partnerid=".$partnerid." and partner_bonus.orderid=orders.id) as canceled
				from orders, payment  
				where orders.id=payment.orderid and payment.ordered=1 and 
				( currin in ('".implode("','",$rbanks)."') or currout in ('".implode("','",$rbanks)."') ) order by orders.id desc
				limit 0,300";
$query=_query($select,"");

while ( $orders=$query->fetch_assoc() ) { 
if ( $orders['summin']!=0 ) {
?>
<tr><td><?=$orders['id']?></td><td><?=$orders['summin']." ".$orders['currin']." -> ".($orders['summout']+$orders['discammount'])." ".$orders['currout']?></td><td align="right"><?=round($orders['addon'],3)?></td>
<td  align="right"><?=round($orders['spred'],2)?></td>
<td  align="right"><?=$orders['summin']*$orders['course2usd']-round($orders['spred']/2,2)?></td>
<td  align="right"><?php if ( is_null($orders['canceled']) ) {?> 
<form action="partner.php?<?=$queryString?>" method="post"><input type="hidden" name="partnerid" size="20" value="<?=$partnerid?>"  />
<input type="hidden" name="orderid" value="<?=$orders['id']?>"  />
<input ssize="7" name="sum" type="hidden" value="<?=$orders['summin']*$orders['course2usd']?>" />
<input type="text" size="7" name="balance" value="<?=$orders['summin']*$orders['course2usd']-round($orders['spred']/2,2)?>" />
<input type="text" name="comment" size="30" value="auto. oID:<?=$orders['id']?>, gain:<?=round($orders['spred'],2)?>, <?=$orders['summin']." ".$orders['currin']." -> ".($orders['summout']+$orders['discammount'])." ".$orders['currout']?>"  />

<input type="submit" value="Добавить" /></form>
<?php } ?>
</td>
</tr>
<?php } } ?>
<tr class="tableborder2"><td colspan="4"></td></tr>
<tr><td colspan="3"></td><td align="right"></td></tr>
</table>
    <?php } ?>
    </td></tr>
    
    
    <?php } ?>
    <?php } while ($row = mysql_fetch_assoc($referer)); // выборка клиентов?>

</table>

<table border="0">
  <tr>
    <td><?php if ($pageNum > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, 0, $queryString); ?>">First</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, max(0, $pageNum - 1), $queryString); ?>">Previous</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum < $totalPages) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, min($totalPages, $pageNum + 1), $queryString); ?>">Next</a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum < $totalPages) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, $totalPages, $queryString); ?>">Last</a>
        <?php } // Show if not last page ?></td>
  </tr>
</table>
</body>
</html>
<?php
mysql_free_result($referer);
?>
