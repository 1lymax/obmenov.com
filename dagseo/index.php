<?php require_once('../Connections/ma.php');
require_once($serverroot.'dagseo/top.php');
require_once($serverroot.'dagseo/include.php');

// vniknis@mail.ru
$partnid=2148;
$user_=new user_();
if ($user_->auth("adm, oper1")==1) {

}else{
	$user_->bad_auth();
}


if ( isset($_POST['balance']) && $_POST['balance']=="update" ) {
	if ( in_array($_POST['currency'], $rbanks) ) {
		$select="insert into amounts (val, amount, account) values ('".$_POST['currency']."','".$_POST['value']."','".$_POST['currency']."')";
		$query=_query($select,"");
	}
}



$select="select extname, name from currency where name in ('".implode("','",$rbanks)."')";
$query=_query($select,"");
?><hr /><br />
<table>
<tr><td><form action="index.php" method="post">
Добавить <select name="currency">
<?php while ( $curr=$query->fetch_assoc() ) {?>
<option value="<?=$curr['name']?>"><?=$curr['extname']?></option>
<?php } ?>
</select>
<input name="value" type="text" size="10"/><input type="hidden" name="balance" value="update" /><input type="submit" value="добавить" /></form>
</td></tr>
</table>
<hr /><br />


<table>
<tr class="tableborder2"><td colspan="2">Текущие балансы:</td></tr>
<?php 
require_once("../siti/class.php");
require_once("../function.php");

	$select="select * from currency where active2=0 and name in ('".implode("','",$rbanks)."') order by extname desc";
	$query=_query($select,"");
	while ( $row=$query->fetch_assoc() ) {
	?>
    <tr><td width="200"><?=$row['extname']?></td><td width="100" align="right"><?=$WM_amount_r[$row['name']]?></td></tr>
	<?php } ?>
    <tr class="tableborder2"><td colspan="2"></td></tr>
    </table><hr /><br /><br />
    Последние изменения баланса:
    <table width="550">
    <tr class="tableborder2">
<td>Время</td><td>Валюта</td><td>Аккаунт</td><td align="right">Сумма</td></tr>
    <?php $select="select currency.extname, amounts.amount, amounts.account, amounts.time from amounts, currency where currency.name=amounts.val and  name in ('".implode("','",$rbanks)."') order by time desc limit 0,15";
	$query=_query($select,"");
	while ($row=$query->fetch_assoc()) {?>
    <tr><td><?=$row['time']?></td><td><?=$row['extname']?></td><td><?=$row['account']?></td><td align="right"><?=$row['amount']?></td></tr>
    <?php } ?>
    </table>
    <hr /><br />
Последние 15 записей взаиморасчетов:
<table width="900">
<tr class="tableborder2">
<td>Время</td><td>Примечание</td><td align="right">Сумма</td><td align="right">Зачтено</td><td align="right">OrderID</td></tr>
<?php /*$select="select * from partner where id=".$partnid;
$query=_query($select,"");
$partner=$query->fetch_assoc();*/
$select="select sum(bonus) as itog from partner_bonus where partnerid=".$partnid." group by partnerid";
$query=_query($select,"");
$itog=$query->fetch_assoc();

$select="select sum,bonus,orderid, comment,time from partner_bonus where partnerid=".$partnid." order by time desc ";
$query=_query($select,"");
while ( $bonus=$query->fetch_assoc() ) { 
?>
<tr><td><?=$bonus['time']?></td><td><?=$bonus['comment']?></td><td align="right"><?=round($bonus['sum'],2)?></td>
<td  align="right"><?=round($bonus['bonus'],2)?></td><td  align="right"><?=($bonus['orderid']!=0?$bonus['orderid']:"")?></td></tr>
<?php } ?>
<tr class="tableborder2"><td colspan="5"></td></tr>
<tr><td colspan="3"></td><td align="right"><?=round($itog['itog'],2)?></td></tr>
</table>