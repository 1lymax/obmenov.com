<?php require_once('../Connections/ma1.php');
require_once($serverroot.'adrefzw/top.php');

$user=new user();
if ($user->auth("adm")==1) {

}else{
	$user->bad_auth();
}

if ( isset($_POST['merchant']) && $_POST['merchant']=="edit" ) {
	$update="update merch set active=0 where currency='".$_POST['currency']."' and type='".$_POST['type']."'";
	$query=_query($update,"");
	echo $update."<br>";
	$update="update merch set active=1 where id=".$_POST['id'];
	echo $update."<br>";
	$query=_query($update,"");
	
}

if ( isset($_POST['transfer']) && $_POST['transfer']=="p24-p24" ) {
	$select="select * from merch where id=".$_POST['merch_from'];
	$query=_query($select,"");
	$merch_from=$query->fetch_assoc();
	$select="select * from merch where id=".$_POST['merch_to'];
	$query=_query($select,"");
	$merch_to=$query->fetch_assoc();
	
	require_once($serverroot."siti/p24api.php");
	$p24api = new p24api($merch_from['merchant_id'], $merch_from['merchant'], 'https://api.privatbank.ua:9083/p24api/pay_pb');
	$details=iconv("windows-1251","utf-8","Перевод личных средств.");
	$payment= Array(
					Array('id'=>"inner".time(),
					'phone'=>$merch_from['phone'],
					'b_card_or_acc'=>$merch_to['number'],
					'amt'=>$_POST['summ'],
					'ccy'=>$merch_from['currency'],
					//'details'=>$row_orders['id']));
					'details'=>$details)
					);
	//$response = $p24api->sendCmtRequest($payment,0,1); тестовый платеж
	//$response=simplexml_load_string($response);
	//print_r($response); тестовый платеж
	//if ( $response[0]['state'] == 1 ){ тестовый платеж
		$response = $p24api->sendCmtRequest($payment,0,0);
		if ( $response[0]['state'] == 1 ){
			//$response=simplexml_load_string($response);
			print_r($response);
		}
	//} тестовый платеж
	
	

	
}

if ( isset($_POST['transfer']) && $_POST['transfer']=="lq-p24" ) {
	$select="select * from merch where id=".$_POST['merch_to'];
	$query=_query($select,"");
	$merch_to=$query->fetch_assoc();
	
	require_once($serverroot."siti/lp.class.php");
	$liqpay = new liqpay($_POST['merch_from']);
	$liqpay->inner_pay($merch_to['number'],$_POST['summ'], $_POST['currency']);
}
if ( isset($_POST['transfer']) && $_POST['transfer']=="lq-p24_out" ) {
	require_once($serverroot."siti/lp.class.php");
	$liqpay = new liqpay($_POST['merch_from']);
	$liqpay->out_pay($_POST['card'],$_POST['summ'], $_POST['currency']);
}

if ( isset($_POST['setting']) && $_POST['setting']=="Сохранить" ) {
	
	foreach ( $_POST as $key=>$value ) {
		if ( $key != "setting" ) {
			$update="update LOW_PRIORITY settings set value='".$value."'
			where id=".$key;
			$query=_query($update,"adrefzw-index.php");
			//echo $update."<br />";
		}
	}

	
}
if ( isset($_POST['ks']) && $_POST['ks']=="update" ) {
	$select="insert into amounts (val, amount, account) values ('KS','".$_POST['value']."', '380974182889')";
	$query=_query($select,"");
}



$select="SELECT * FROM `settings` WHERE `show` =0";
$query=_query($select,"adrefzw-index");
?>
<table>

<tr><td>
<form action="index.php" method="post">
<table>
<?php while ($row=$query->fetch_assoc() ) { ?>

<tr>
<td><?=$row['name']?></td>
<td>
<?php if ( $row['type']=="box" ) {?>
<select name="<?=$row['id']?>">
<option value="1" <?=$row['value']==1 ? 'selected="selected"' : "" ?>>Вкл.</option>
<option value="0" <?=$row['value']==0 ? 'selected="selected"' : "" ?>>Выкл.</option>
</select>
<?php }else{?>
<input type="text" value="<?=$row['value']?>" name="<?=$row['id']?>" />
<?php } ?>
</td>
</tr>

<?php } ?>
<tr><td></td><td><input type="submit" value="Сохранить" name="setting" /></td></tr>
</table>
</form>
</td>
<td width="300" align="center" valign="top">
<?php
require_once("../siti/class.php");
require_once("../function.php");
$a=$WM_amount;
$b=$WM_amount_r;
echo $WMZWMU_base." - ".$WMZWMR_base." - ".$WMEWMZ_base. " - ". round(1/$WMRWMU_base,3);
$a['total1'] = round($a['WMZ'] + 
		 $a['WMU']/$WMZWMU_base + 
		 $a['WMR']/$WMZWMR_base +
		 $a['WME']*$WMEWMZ_base,2);
$a['total']=$a['total1']+round($a['P24USD']+$a['P24EUR']*$WMEWMZ_base+$a['P24UAH_real']/12
		+$a['LQUAH']/12+$a['LQEUR']*$WMEWMZ_base+$a['LQRUB']/$WMZWMR_base+$a['LQUSD']+$a['PMUSD']+$a['PMEUR']*$WMEWMZ_base
		,2);
?>
  <table>
	<?php 
	$select="select * from currency where active2=0 and name!='UAH' and name!='USD' order by extname desc";
	$query=_query($select,"");
	$a['P24UAH']=$a['P24UAH_real'];
	while ( $row=$query->fetch_assoc() ) {
		if (substr($row['name'],0,3)=="MCV")$row['name']=str_replace("MCV","LQ",$row['name']);
		$row['name']=($row['name']=="LQRUR"?"LQRUB":$row['name']);
	?>
    <tr><td width="300"><?=$row['extname']?></td><td width="150" align="right"><?=$a[$row['name']]?></td><td width="150" align="right"><?=$b[$row['name']]?></td></tr>
	<?php } ?>   
    <tr><td>WM всего</td><td><?=$a['total1']?></td></tr>
    <tr><td>Всего</td><td><?=$a['total']?></td></tr>
  </table>

</td>
</tr>
</table>
<hr />
<?php 
$select="select * from merch where work=1 and type='p24'";
$query=_query($select,"");
// select * from ( select merch.name, merch.number, merch.currency, amounts.amount, amounts.time from merch, amounts where merch.work=1 and merch.type='p24' and merch.number=amounts.account order by time desc) as am group by number

?>
<table cellpadding="4" cellspacing="4">
<tr><td>Наименование</td><td>Номер карты</td><td>Баланс</td><td>Валюта</td><td>Дата баланса</td><td>Активность</td></tr>
<?php while ( $row=$query->fetch_assoc() ) { ?>
<form action="index.php" method="post">
<tr>
<td>
<?=$row['name']?>
</td>
<td><input type="text" value="<?=$row['number']?>" /></td>
<input type="hidden" name="id" value="<?=$row['id']?>" />
<input type="hidden" name="merchant" value="edit" />
<input type="hidden" name="type" value="<?=$row['type']?>" />
<input type="hidden" name="currency" value="<?=$row['currency']?>" />
<?php
		if ($row['merchant_id']!="") {
			$p24balance = new p24api($row['merchant_id'], $row['merchant'], 'https://api.privatbank.ua:9083/p24api/balance');
			$response = $p24balance->sendBalanceRequest();
			if ( is_array($response) || strlen(trim($response))==0 ) {
				print_r($response);
			}else {
				$response = simplexml_load_string($response);
				if ( isset($response->data->info->cardbalance) ) {
					$bal_value = $response->data->info->cardbalance->av_balance;
					$bal_date= $response->data->info->cardbalance->bal_date;
					$balance[$row['id']]=$bal_value;
				}else{
					$balance[$row['id']]="недоступен";
					print_r($response);
				}
			}
		}
?>
<td align="right"><?=isset($bal_value) ? iconv("utf-8","windows-1251",$bal_value) : "недоступен"?></td>
<td align="center"><?=$row['currency']?></td>
<td align="right"><?=isset($bal_date) ? $bal_date : "недоступен"?></td>
<td align="center"><input type="checkbox" readonly="readonly" name="value" value="1" <?=$row['active']==1 ? 'checked="checked"' : ""?> /></td>
<td><input type="submit" value="Сделать активным" /></td>
</tr>
</form>
<?php } ?>
</table>
<hr />
<form action="index.php" method="post">
<input type="hidden" name="transfer" value="p24-p24" />
<table>
<tr><td>Внутренние переводы Приват24:</td></tr>
<tr>
<td>
С мерчанта 
<select name="merch_from">
<?php $select="select * from merch where work=1 and type='p24' and api=1";
$query=_query($select,"");
while ( $row=$query->fetch_assoc() ) { ?>
<option value="<?=$row['id']?>"><?=$row['name']." - ".(isset($balance[$row['id']]) ? $balance[$row['id']]." ".$row['currency'] : "недоступен" )?></option>
<?php } ?>
</select>
</td>
<td>на мерчант
<select name="merch_to">
<?php $select="select * from merch where work=1 and type='p24' and api=1";
$query=_query($select,"");
while ( $row=$query->fetch_assoc() ) { ?>
<option value="<?=$row['id']?>"><?=$row['name']." - ".(isset($balance[$row['id']]) ? $balance[$row['id']]." ".$row['currency'] : "недоступен" )?></option>
<?php } ?>
</select>
</td>
</tr>
<tr><td>Отправляемая сумма: <input type="text" name="summ" value="" size="10"/>
<input type="submit" value="Перевести" /></td></tr>
</table>
</form>
<hr />
<form action="index.php" method="post" >
<table>
<tr><td>Внутренние переводы с ликпей на Приват24:</td></tr>
<tr>
<td>
С мерчанта 
<select name="merch_from" onchange="d.$('currency').value=d.$('lq').options[d.$('lq').options.selectedIndex].attributes[1].value" id="lq" />

<?php 
require_once($serverroot."siti/lp.class.php");
$select="select * from merch where work=1 and type='liqpay'";
$query=_query($select,"");

while ( $row=$query->fetch_assoc() ) { 
	$liqpay=new liqpay($row['id']);
	$lqbalance=$liqpay->balance();
	foreach ( $lqbalance as $k=>$v ) {
		
?>
<option currency="<?=$k?>" value="<?=$row['id']?>"><?=$row['name']." - ".$v." ".$k?></option>
<?php 
	}
} ?>
</select>

<input type="hidden" name="transfer" value="lq-p24" />
<input type="hidden" name="currency" value="USD" id="currency"/>
</td>
<td>на карту
<select name="merch_to">
<?php $select="select * from merch where work=1 and type='p24'";
$query=_query($select,"");
while ( $row=$query->fetch_assoc() ) { ?>
<option value="<?=$row['id']?>"><?=$row['name']." - ".
			(isset($balance[$row['id']]) ? $balance[$row['id']]." ".$row['currency'] : "недоступен" )?></option>
<?php } ?>
</select>
</td>
</tr>
<tr><td>Отправляемая сумма: <input type="text" name="summ" value="" size="10" />
<input type="submit" value="Перевести" /></td></tr>
</table>
</form>

<hr />
<form action="index.php" method="post" >
<table>
<tr><td>Переводы с ликпей на карту:</td></tr>
<tr>
<td>
С мерчанта 
<select name="merch_from" id="lq" />

<?php 
require_once($serverroot."siti/lp.class.php");
$select="select * from merch where work=1 and type='liqpay'";
$query=_query($select,"");

while ( $row=$query->fetch_assoc() ) { 
	$liqpay=new liqpay($row['id']);
	$lqbalance=$liqpay->balance();
	foreach ( $lqbalance as $k=>$v ) {
		
?>
<option currency="<?=str_replace("RUB","RUR",$k)?>" value="<?=$row['id']?>"><?=$row['name']." - ".$v." ".$k?></option>
<?php 
	}
} ?>
</select>

<input type="hidden" name="transfer" value="lq-p24_out" />
<select name="currency" Id="currency"/>
<option value="USD" >USD</option>
<option value="UAH" >UAH</option>
<option value="EUR" >EUR</option>
</select>
</td>
<td>на карту
<input name="card">

</td>
</tr>
<tr><td>Отправляемая сумма: <input type="text" name="summ" value="" size="10" />
<input type="submit" value="Перевести" /></td></tr>
</table>
</form>