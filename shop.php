<?
require_once('Connections/ma.php');
require_once('function.php');
require_once('siti/prepaid_wm_config.php');
require_once('siti/prepaid_wm_include.php');
require_once('siti/class.php');
$shop=new shop();
$type= isset($_GET['pin']) ? " AND item_name.url='".substr(htmlspecialchars($_GET['pin']),0,10)."' " : '' ;
$item= isset($_GET['i']) ? " AND item_name.id=".(int)$_GET['i'] : '' ;
$type2= isset($_GET['t']) ? " AND item_name.typeid=".(int)$_GET['t'] : '' ;

if ( $item!="" ) $description=true;

$query = "SELECT DISTINCT items.id, item_name.price, item_name.unit, items.state, item_name.order, 
(select item_types.name from item_types where item_types.id=item_name.typeid) as itemtype,
item_name.typeid, item_name.description, item_name.id as nameid,
		 item_name.name, item_name.url, item_name.rules FROM items, item_name WHERE 
		 ((reserved IS NULL) OR (reserved + INTERVAL 4 MINUTE < NOW())) AND items.itemid=item_name.id ".$type." ".$item." ".$type2." AND items.state='Y' GROUP BY item_name.typeid, item_name.name 
		 ORDER BY typeid desc, item_name.order";
$result = _query2($query, 'prepaid.php 1');    
$rows = mysql_num_rows($result);
if ( $rows==1 ) {
	$item=mysql_fetch_assoc($result);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=isset($item['name']) ? "Купить ".$item['name']." за Webmoney, VISA, MasterCard :: " : ""?><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: <?php if ( isset($item['name']) ) {
		?>
Магазин цифровых товаров 
		<?php }else{ ?>
Магазин цифровых товаров
        <?php } ?>
        </title>
        <meta http-equiv="Cache-Control" content="no-cache" />
		<meta http-equiv="Pragma" content="no-cache" />
		<meta http-equiv="Expires" content="-1" />
		<meta http-equiv="revisit-after" content="3 days" />
		<meta name="revisit" content="3 days" />
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
        <?php if ( $rows==1 ) { ?>
        <meta name="description" content="Купить <?=$item['name'].' всего за '.$item['price'].' '.$item['unit']?>" />
        <?php } ?>
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
        
	</head>
    <script src="fun.js"></script>
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
                      <div class="otzyv"></div>
  <h1 align="left">Прямое пополнение <img src="<?=$siteroot?>i/logos/ks.png" width="17" height="17" hspace="0" vspace="0" border="0"  />Киевстар с большими скидками! <?=($urlid['site_curr2']==1) ? "Теперь и за Webmoney!" : "" ?></h1>
<p align="left">Выберите свой способ прямого пополнения Киевстар <a href="<?=$siteroot?>commission.php">по ссылке</a>. <br />
Скидки при регистрации также действуют на этом направлении обмена.</p> 
<div class="otzyv"></div><br />

                    <h1>Магазин цифровых товаров<br />
</h1><span class="otzyv-name">Самые лучшие покупки здесь</span>
<div class="otzyv"></div>
<a href="<?=$siteroot?>shop.php">...</a><br />
<?php $select="select item_types.name as name, item_types.id as id from item_types order by name";
	$query=_query($select, "shop.php");
	while ( $row=$query->fetch_assoc() ) { ?>
<a href="<?=$siteroot."shop.php?t=".$row['id']?>"><?=$row['name']?></a><br />

<?php }?>
<div align="center">
<?php

$discount=$shop->discount();
if ( $rows > 1 ) { 
$iii=0;
?>
<br />
<br />
<br />
                  
<table width="550" align="center" border="0">
<?php $td_style='td_white';$i=0; $itemtype='';
while( $item = mysql_fetch_array($result)){
  	$i=$i+1;
	?><form method="POST" action="prepaid_payment.php" id="buy<?=$item['id']?>" name="buy<?=$item['id']?>">
    <?php if ( $itemtype!=$item['itemtype'] ) { ?>
    <tr><td colspan="6" height="50" valign="bottom"><h2 align="left"><img src="<?=$siteroot?>i/icq-online.png" alt="" /> <?=$item['itemtype']?></h2></td></tr>
    <?php 
	$itemtype=$item['itemtype'];
	} ?>
    <tr>
    <td width="10" class="<?=$td_style; ?>"></td>
    <td height="40" valign="middle"  class="<?=$td_style; ?>">		
		<img src="<?=$siteroot?>i/game/<?=$item['url']?>_logo_sm.gif"></td>
        <td valign="middle" class="<?=$td_style; ?>" width="200">
<input type="hidden" name="item" value="<?=$item['id']?>">
		<span class="cabinet-text"><a href="<?=$siteroot?>shop.php?i=<?=$item['nameid']?>"><?=$item['name']?></a></span></td>
        <td valign="middle" align="right" class="<?=$td_style; ?>" width="200">
        <?php 
		$u=$shop->get_price($item['unit'],$item['price']);
        ?>
        <select name="unit" class="button1">
        <?php 
		
		foreach ($u as $k=>$v) { ?>
            <option value="<?=$k?>"><?=$v." ".$k?></option>
        <?php	}?>
        </select>
		<?php
		if ( $item['state']=="Y" ) {?>
       <input type="button" class="button1" onClick="document.buy<?=$item['id']; ?>.submit();" value="Купить">
		<?php } else { ?>
        <div align="right">Нет в наличии</div><?php } ?>
		
        </td>
        <td width="10" class="<?=$td_style; ?>"></td>
        </tr>
        <?php
		if ( $i==1 ) {$td_style='td'; $i=-1; } else {$td_style='td_white';}
		?></form>
        <?php
}
?>
</table>
<?php
}elseif ( $rows==1 ) {
?> 
<table width="500" align="center" border="0">
<tr><td height="40"></td></tr>
<tr><td></td><td colspan="2"><a href="<?=$siteroot?>shop.php">Магазин</a> >> <a href="<?=$siteroot?>shop.php?t=<?=$item['typeid']?>"><?=$item['itemtype']?></a> >> <a href="<?=$siteroot?>shop.php?i=<?=$item['nameid']?>"><?=$item['name']?></a></td></tr>
<tr><td height="20"></td></tr>
<tr><td></td>
<td height="20"><img src="<?=$siteroot?>i/game/<?=$item['url']?>_logo_sm.gif"></td>
<td><h2><img src="<?=$siteroot?>i/icq-online.png" alt="" /> <?=$item['name']?></h2></td>
</tr>
<tr class="td_white"><td width="10"></td><td height="30">Категория</td><td><?=$item['itemtype']?></td></tr>
<tr><td width="10"></td><td height="30">Описание</td><td><?=$item['description']?></td></tr>
<tr class="td_white"><td width="10"></td><td height="30">Инструкция по использованию</td><td><?=$item['rules']?></td></tr>
<?php #<tr><td width="10"></td><td height="30">Партнерское вознаграждение</td><td valign="middle"><?=$shop->partner_discount($item['nameid'],1)$
#<a href="#">Узнайте, как заработать>></a></td></tr>?>
<tr class="td_white"><td width="10"></td><td height="30" valign="middle">Цена</td><td valign="middle"><form method="POST" action="prepaid_payment.php" id="buy<?=$item['id']?>" name="buy<?=$item['id']?>">
<input type="hidden" name="item" value="<?=$item['id']?>">
        <?php 
		$u=$shop->get_price($item['unit'],$item['price']);
        ?>
        <select name="unit" class="button1">
        <?php 
		
		foreach ($u as $k=>$v) { ?>
            <option value="<?=$k?>"><?=$v." ".$k?></option>
        <?php	}?>
        </select>
		<?php
		if ( $item['state']=="Y" ) {?>
       <input type="button" class="button1" onClick="document.buy<?=$item['id']; ?>.submit();" value="Купить"><br />
       <?php $ec=round($u['WMZ'] * ($shop->value_discount($item['nameid'])-1) * ($discount['total']-1),3); ?>
       <?php if ( isset($_SESSION['authorized']) ) { ?>
Вы экономите <?=$ec==0 ? 0.01 : $ec?>$ 
		<?php }else{ ?>
<a href="<?=$siteroot?>register.php#info">Узнайте, как сэкономить>></a>
		<?php 
		}
		} else { ?>
        <div align="right">Нет в наличии</div><?php } ?>
        </form>
</td></tr>
<tr><td width="10"></td><td height="30"></td><td></td></tr>
</table>

<?php } ?>
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