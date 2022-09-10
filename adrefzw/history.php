<?php require_once('../Connections/ma1.php');
require_once($serverroot.'adrefzw/top.php');
require_once($serverroot.'siti/class.php');
@ini_set ("display_errors", true);
$user=new user();
if ($user->auth("adm")==1) {

}else{
	$user->bad_auth();
}


if ( isset($_POST['merchant']) ) {
	switch ($_POST['merchant']) {
	case "lr":
		require_once($serverroot.'siti/lib.lr.php');
		$lr=new lrWorker($GLOBALS['lr_id']);
		$startdate=$_POST['startdateYYYY']."-".$_POST['startdateMM']."-".$_POST['startdateDD']." 00:00:03";
		$enddate=$_POST['enddateYYYY']."-".$_POST['enddateMM']."-".$_POST['enddateDD']." 23:59:58";
		$result=$lr->History(microtime()*10000000,$startdate,$enddate,$_POST['currency'],$_POST['corrAccount'],
							$_POST['direction'],$_POST['TransferId'],'','',
							$_POST['Source'], $_POST['Anonymous'], $_POST['AmountFrom'], $_POST['AmountTo'], $_POST['PageSize'], 
							$_POST['PageNumber'],true);
		
		$result=simplexml_load_string($result);
		break;
	case "wm":
		require_once($serverroot.'siti/_header.php');
		$datestart=$_POST['datestartYYYY'].$_POST['datestartMM'].$_POST['datestartDD']." 00:00:00";
		$datefinish=$_POST['datefinishYYYY'].$_POST['datefinishMM'].$_POST['datefinishDD']." 23:59:59";
		$result=$wmxi->X3($_POST['purse'], $_POST['wmtranid'], $_POST['tranid'], $_POST['wminvid'], $_POST['orderid'], $datestart, $datefinish);
		$result=simplexml_load_string($result);
		//print_r($result);
		break;
	}
	
}

$spec=new specification();
$select="SELECT merchant, merch_descr FROM `history_fields` WHERE 1 GROUP BY merchant";
$query=_query($select, "history.php");
?>

<form action="history.php" method="post">
История операций <select name="history">
<?php
while ($row=$query->fetch_assoc() ) { ?>
	<option value="<?=$row['merchant']?>" <?=(isset($_POST['history']) && $_POST['history']==$row['merchant'] ? "selected='selected'" : "")?>><?=$row['merch_descr']?></option>
<?php } ?>
</select> <input type="submit" value="ОК">
</form>
<?php if ( isset($_POST['history']) ) { 
	$select="select * from history_fields where merchant='".mysql_real_escape_string($_POST['history'])."'";
	$query=_query($select,"history.php"); ?>
<table>
<form action="history.php" method="post">
<input type="hidden" name="history" value="<?=$_POST['history']?>">
<?php while ($row=$query->fetch_assoc() ) { ?>
<tr>
<td><?=$row['label']?></td>
<td><?php 
	switch ( $row['type'] ) {
		case "date" :
			$spec->date_fields($row['field']);
			break;
		case "text" :
			echo "<input type='text' name='".$row['field']."' value='".(isset($_POST[$row['field']]) ? $_POST[$row['field']] :"")."'>";
			break;
		case "select" :
			$spec->select_fields ($row['field'], $row['val']);
			break;
		case "sql" :
			$spec->sql_fields ($row['field'], $row['val'], array(0=>'name',1=>'number'));
			break;
	}
			?>
            </td>
</tr>
<?php } ?>
<tr><td></td><td><input type="submit" value="Получить">
<input type="hidden" name="merchant" value="<?=$_POST['history']?>" /></td></tr>
</form>
</table>
<?php } ?>
<table width="100%" border="1">

<?php if ( isset($_POST['merchant']) ) {
	switch ($_POST['merchant']) {
		case "lr":
	?>
    <tr>
<td height="50">Дата</td><td>Заявка</td><td colspan="2">Плательщик</td><td colspan="2">Получатель</td>
<td>Сумма</td><td>Примечание</td><td>Комиссия</td><td>Баланс</td><td>Источник</td><td>Аноним</td>
</tr>
    <?php
	foreach ($result->Receipt as $k=>$v) {
?>
<tr>
<td><?=$v->Date?></td>
<td><?=$v->TransferId?></td>
<td><?=$v->PayerName?></td>
<td><?=$v->Payer?></td>
<td><?=$v->PayeeName?></td>
<td><?=$v->Payee?></td>
<td><?=round(str_replace(",",".",$v->Amount),2)." ".$v->CurrencyId?></td>
<td width="150"><?=iconv('utf-8','windows-1251',$v->Memo)?></td>
<td><?=round(str_replace(",",".",$v->Fee),2)?></td>
<td><?=round(str_replace(",",".",$v->ClosingBalance),2)?></td>
<td><?=$v->Source?></td>
<td><?=$v->Anonymous?></td>

</tr>
<?php 
	}
	break;
	case "wm":
		$p=array();
		$p[0]="";$p[4]="ПР. Не завершена";$p[12]="ПР. Возврат";
	?>
    <tr><td></td>
<td height="50">Дата</td><td>Заявка</td><td>Счет №</td><td>Плательщик</td>
<td>Получатель</td><td>WMID</td><td>Сумма /<br />Комиссия</td><td>Описание</td><td>Остаток</td><td>Тип <br />операции</td>
</tr>
<?		foreach ($result->operations->operation as $k=>$v) {
			if ( $v->pursedest==$_POST['purse'] && $_POST['direction']=="Исходящие" )continue;
			if ( $v->pursesrc==$_POST['purse'] && $_POST['direction']=="Входящие" )continue;
			?>
<tr>
<td><?=($v->pursedest==$_POST['purse']? "in" : "out")?></td>
<td><?=$v->datecrt.($v->datecrt!=$v->dateupd ? "<br />".$v->dateupd : "")?></td>
<td><?=$v->tranid?></td>
<td><?=$v->orderid?></td>
<td><?=$v->pursesrc?></td>
<td><?=$v->pursedest?></td>
<td><a href="https://passport.webmoney.ru/asp/certview.asp?wmid=<?=$v->corrwm?>" target=_blank><?=$v->corrwm?></a></td>
<td><?=$v->amount."<br />".$v->comiss?></td>
<td><?=iconv('utf-8','windows-1251',$v->desc)?></td>
<td><?=$v->rest?></td>
<td><?=$p[intval($v->opertype)].($v->period!=0 ? "<br />".$v->period." дней" : "")?></td>
            <?php
		}
		break;
	}
} ?>
</table>

</body>
</html>