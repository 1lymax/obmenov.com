<?php require_once('../Connections/ma.php'); ?>
<?php 
mysql_select_db($database_ma, $ma);
_query('SET character_set_database = cp1251',"");
_query('SET NAMES cp1251',"");

require_once($serverroot.'dagseo/include.php');
$user_=new user_();
require_once($serverroot.'function.php');
if ($user_->auth("adm, oper1")==1) {

}else{
	$user_->bad_auth();
}

@ini_set ("display_errors", true);


$pursefind=0;
$badcondition=" 1=2 ";
$clorder= new orders();
if ($user_->auth("adm")==1) {
	if ( isset($_POST['pay_p2']) && $_POST['pay_p2']=='pay' ) {
	// Приват платеж
		if ( isset($_POST['noreserve']) && $_POST['noreserve']=='ok' ) {
			$WM_amount_r['P24UAH']=100000;
			$WM_amount_r['P24USD']=10000;
			$WM_amount_r['P24EUR']=10000;
		}
		$clorder->pay_pb($_POST['oid'],$WM_amount_r);
	}
	if ( isset($_POST['pay_wm']) && $_POST['pay_wm']=='pay' ) {
	// WM платеж
		echo "paywm";
		$clorder->pay_wm($_POST['oid']);
	}
	if ( isset($_POST['pay_pm']) && $_POST['pay_pm']=='pay' ) {
	// WM платеж
		echo "paypm";
		print_r($clorder->pay_pm($_POST['oid']));
	}
	if ( isset($_POST['back_wm']) && $_POST['back_wm']=='pay' ) {
	// Возврат ВМ
		echo "backwm";
		$clorder->back_wm($_POST['oid']);
	}
	if ( isset($_POST['pay_lr']) && $_POST['pay_lr']=='pay' ) {
	// LR
		echo "paylr";
		$clorder->pay_lr($_POST['oid']);
	}
	if ( isset($_POST['pay_lp']) && $_POST['pay_lp']=='pay' ) {
	// Liqpay
		echo "pay_liqpay";
		require_once($serverroot."siti/lp.class.php");
		$liqpay=new liqpay($GLOBALS['lq_id']);
		echo $liqpay->order_pay($_POST['oid']);
	}
		if ( isset($_POST['pay_mc']) && $_POST['pay_mc']=='pay' ) {
	// Liqpay
		echo "pay_master/visa";
		require_once($serverroot."siti/lp.class.php");
		$liqpay=new liqpay($GLOBALS['lq_id']);
		$liqpay->order_pay($_POST['oid']);
	}
	
}
$client_sort='';
$clientsort='';
$client_number='';
if ( isset($_POST['action']) && $_POST['action']=="mail_needcheck" && isset($_POST['id']) ) {
		$select="select * from orders where id=".$_POST['id'];
		$query=_query($select,"orders.php mail_needcheck");
		$clorder = new orders();
		$clorder->mail_needcheck($query->fetch_assoc(), (int)$_POST['type']);
		echo "Письмо поставлено в очередь на отправку<br />";
		$update="update orders set needcheck=".(int)$_POST['type']." where id=".(int)$_POST['id'];
		$query=_query($update, "orders.php mail_needcheck");
}
	
if ( isset($_GET['action']) && $_GET['action']=="xml" && isset($_GET['oid']) ) {
	$select="select * from xml where orderid=".$_GET['oid'];
	$query=_query($select, "orders.php 4");
	?>
    <table border=1 width="100%">
    <?php
    $parser = new WMXIParser();
	while ( $rows=$query->fetch_assoc() ) {
		
		$structure = $parser->Parse($rows['query'], DOC_ENCODING);
		$transformed = $parser->Reindex($structure, true);
		//echo array_search("sign",$transformed['passport.response']);
		?>
		<tr><td><pre>
        <?=print_r($parser->Parse($rows['query'], DOC_ENCODING));?>?>
        </pre>
        
        </td>
        <td><pre>
        <?=print_r($parser->Parse($rows['answer'], DOC_ENCODING))?>
        </pre>
        </td>
        </tr>
        
        <?php } ?>
        </table>
        <?php
		die();
}

if ( isset($_POST['bank']) && $_POST['bank'] == 'update' ) {
			$query="select id from bank_accounts where 
							fname='".$_POST['fname']."' and 
							iname='".$_POST['iname']."' and 
							clid='".$_POST['clid']."' and 
							account='".$_POST['account']."'";
			$result=_query($query,"");
			$verified = isset ($_POST['verified']) ? $_POST['verified'] : "0";
			$type = isset ($_POST['type']) ? $_POST['type'] : "0";
			$account = isset ($_POST['account']) ? $_POST['account'] : "";
			$fname = isset ($_POST['fname']) ? $_POST['fname'] : "";
			$iname = isset ($_POST['iname']) ? $_POST['iname'] : "";
			$wmid = isset ($_POST['wmid']) ? $_POST['wmid'] : "";
			$clid = isset ($_POST['clid']) ? $_POST['clid'] : "";
			if ( mysql_num_rows($result)==0 ) {
				$query="insert into bank_accounts (verified, type, fname, iname, clid, account, wmid )
											values ($verified, $type, 
											'$fname', 
											'$iname',
											'$clid',
											'$account',
											'')";
				
			}else{
				$query="update bank_accounts set 
							verified=$verified,
							type=$type,
							account='$account'
							where 
								fname='$fname' and
								iname='$iname' and
								clid='$clid'";
			}
			$result=_query($query,"");
				
			
}
										 
if ( isset($_POST['update_orders']) && $_POST['update_orders'] == 'ok' ) {
			//echo intval($_POST['purse']);
			$payed = isset ($_POST['payed']) ? $_POST['payed'] : "0";
			$canceled = isset ($_POST['canceled']) ? $_POST['canceled'] : "0";
			$ordered = isset ($_POST['ordered']) ? $_POST['ordered'] : "0";
			$needcheck = isset ($_POST['needcheck']) ? $_POST['needcheck'] : "0";
			$droped = isset ($_POST['droped']) ? $_POST['droped'] : "0";
			$authorized = isset ($_POST['authorized']) ? $_POST['authorized'] : "0";
			$blacklist = isset ($_POST['blacklist']) ? $_POST['blacklist'] : "0";
			$bank_account_type = isset ($_POST['bank_account_type']) ? $_POST['bank_account_type'] : "0";
			$account = isset ($_POST['account']) ? $_POST['account'] : "";
			$purse_other = isset ($_POST['purse_other']) ? $_POST['purse_other'] : "";
			$status = isset ($_POST['status']) ? $_POST['status'] : "";
			$update="UPDATE orders SET orders.ordered=".$ordered.", needcheck=".$needcheck.", 
											authorized=".$authorized.", 
											blacklist=".$blacklist.",
											droped=".$droped.",
											account='".$account."',
											purse_other='".$purse_other."',
											status='".$status."',
											bank_account_type='".$bank_account_type."'
											
			WHERE orders.id=".$_POST['id'];
			//,orders.purse_".$_POST['purseType']."='".$_POST['purse']."'
			$updateSQL=_query($update, "ad update orders 1");
			$update="UPDATE payment SET payment.ordered=".$payed.", payment.canceled=".$canceled."
			WHERE payment.orderid=".$_POST['id'];
			$updateSQL=_query($update, "ad update orders 2");	
			$client_info="SELECT clients.email, orders.id FROM clients, orders WHERE clients.clid=orders.clid 
			AND orders.id=".$_POST['id'];
			$row_client=_query($client_info,"ad update orders 3");
			$row_client=mysql_fetch_assoc($row_client);
			if ( $canceled ) {
			/*send_mail($row_client['email'], '
Уважаемый(ая) покупатель!
			
Уведомляем вас об изменении статуса вашего заказа №: '.$row_client['id'].'
Ваш заказ получил статуc "Обмен завершен".

С уважением, Обменов.ком', "Обменов.ком :: изменен статус заявки № ".$row_client['id'],$shop_email , $shop_name) ; */
					   }
			
	$row_order=_array("select * from orders where id=".$_POST['id']);
	//echo "update";
	if ( $row_order['currin']=="UAH" || $row_order['currin']=="USD" ) {
				$select="SELECT * FROM (SELECT * FROM amounts where val='".$row_order['currin']."' ORDER BY time DESC) AS am GROUP BY val";
				$query=_query($select,"siti/paybank.php 16");
				$bal=$query->fetch_assoc();
								
				$insert="insert into amounts (val,amount, account) values ('".$row_order['currin']."',".
														($bal['amount']+$row_order['summin']).", 'N".$row_order['currin']."')";
				$query=_query($insert, "siti/paybank.php 6");
	}
	if ( $row_order['currout']== "UAH" || $row_order['currout']== "USD" ) {
				$select="SELECT * FROM (SELECT * FROM amounts where val='".$row_order['currout']."' ORDER BY time DESC) AS am GROUP BY val";
				$query=_query($select,"siti/paybank.php 16");
				$bal=$query->fetch_assoc();
				
				$insert="insert into amounts (val,amount, account) values ('".$row_order['currout']."',".
										($bal['amount']-$row_order['summout']+$row_order['discammount']).", 'N".$row_order['currout']."')";
				
				$query=_query($insert, "siti/paybank.php 6");
	}
	

}

$searchvalues['search1id']='';
$searchvalues['search1clid']='';
$searchvalues['search1currin']='';
$searchvalues['search1currout']='';
$searchvalues['search1ordered']='';
$searchvalues['search2ordered']='';
$searchvalues['search2canceled']='';
$searchvalues['search1partnerid']='';
$searchvalues['search1time']='';
$searchvalues['search1email']='';
$searchvalues['search1account']='';
$searchvalues['search1needcheck']='';
$searchvalues['search2id']='';
$searchvalues['search2LMI_SYS_INVS_NO']='';
$searchvalues['search1purse_other']='';
$searchvalues['search2LMI_PAYER_PURSE']='';
$searchvalues['search2LMI_PAYER_WM']='';
$searcharr = array();
$table = array();
reset($_POST);
$wordlen=strlen("search");	
$table[1]="orders";
$table[2]="payment";
while (list($key, $val) = each($_GET)) {
	$badcondition="";	

	//$table3="clients";
	if ( substr($key,0,$wordlen)=="search") {
	
			$searcharr[$key]=" AND ".$table[substr($key,$wordlen,1)].".".substr($key,$wordlen+1,strlen($key)-$wordlen)." LIKE '%".$val."%' ";
			$searchvalues[$key]=$val;
	}
	
}
$currentPage = $_SERVER["PHP_SELF"];

$max = 100;
$page = 0;
if (isset($_GET['page'])) {
  $page = $_GET['page'];
}
$start = $page * $max;
$query_orders = "SELECT orders.partnerid, sum(orders.summin) as summ_from,  sum(orders.summout) as summ_to, orders.id as id,
				orders.summin, orders.summout, orders.name, orders.currin, orders.id as orderid, orders.authorized, orders.ip,
				orders.blacklist, orders.fname, orders.iname, orders.bank_account_type, orders.descr, orders.retid, orders.oname,
				(select type from currency where name=orders.currin) as currintype, orders.purse_other, orders.disc,
				(select type from currency where name=orders.currout) as currouttype, orders.av_balance, 
				(select extname from currency where name=orders.currout) as currout_extname, orders.account_comment,
				orders.currout, orders.discammount, orders.time, orders.ordered, orders.attach, orders.needcheck, 
				orders.purse_z, orders.purse_r, orders.purse_e, orders.purse_u, orders.email, orders.passport, orders.phone,
				orders.mfo, orders.account, orders.bank_name, orders.bank_type, orders.inn, orders.bank_comment
				, payment.id as paymentid, payment.orderid, orders.status as orderstatus, orders.wmid, orders.droped,
				payment.ordered AS recept, payment.canceled, payment.timestamp as payment_timestamp,
				payment.LMI_SYS_INVS_NO, LMI_SYS_TRANS_NO, LMI_PAYER_PURSE, LMI_PAYER_WM, payment.status,
				orders.clid FROM orders, payment WHERE payment.orderid = orders.id AND ( orders.currin in ('".implode("','",$rbanks)."') || orders.currout in ('".implode("','",$rbanks)."') ) ";//.$badcondition;
$querysumm="select sum(orders.summin) as summ_from,  sum(orders.summout) as summ_to from orders, payment where payment.orderid = orders.id 
			AND ( orders.currin in ('".implode("','",$rbanks)."') || orders.currout in ('".implode("','",$rbanks)."') ) ";
			
while (list($key, $val) = each($searcharr)) {
	$query_orders=$query_orders.$val;
	$querysumm=$querysumm.$val;
}
//echo "1 ".time()."<br />";
$query_orders = $query_orders." GROUP BY orders.id ORDER BY orders.id desc";
//$querysumm = $querysumm." GROUP BY orders.id ORDER BY orders.id desc";
$query_limit_orders = sprintf("%s LIMIT %d, %d", $query_orders, $start, $max);
$query_limit_summ = sprintf("%s LIMIT %d, %d", $querysumm, $start, $max);

$orders = mysql_query($query_limit_orders, $ma) or die(mysql_error());
//echo $query_orders;
$orderssumm=mysql_query($query_limit_summ, $ma) or die(mysql_error());
$orderssumm_row=mysql_fetch_assoc($orderssumm);
//echo "2 ".time()."<br />";
if (isset($_GET['total'])) {
  $total = $_GET['total'];
} else {
  $total = 300;
}
$totalPages = ceil($total/$max)-1;

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
$queryString_orders = sprintf("&total=%d%s", $total, $queryString_orders);
echo "Всего записей: ".mysql_num_rows(_query($query_orders,""));

include_once ("top.php"); 

	$select = "SELECT * FROM currency where active=1 order by extname ";
	$query = _query ($select, "ad/orders.php 12");
	

	
	
?><script type="text/javascript" src="../fun.js"></script>
   <script language="javascript">
      function mkpay (vl, frm){
		if (confirm('Подтвердите оплату '+vl)) {
			d.$('pay'+frm).action="orders.php?<?=$_SERVER['QUERY_STRING']?>";
		 	d.$('pay'+frm).submit();
		}
	  }
	</script>
<table cellpadding="0" cellspacing="0" width="100%">	
 <form action="orders.php?<?php echo $queryString_orders ?>" method="get">
  <tr class='td_head'>
  <?php /*?>  <td>Ф.И.О: <br /><input name="search3name" type="text" value="<?=$searchvalues['search3name']?>"/></td>
    <td>логин: <br /><input name="search3nikname" type="text" value="<?=$searchvalues['search3nikname']?>"/></td><?php */?>
    <td>валюта из:<br /> <select name="search1currin">
        <option value=""></option>
	<?php while ( $row_currency = $query->fetch_assoc() ) {
		
	?>
    <option  <?=$searchvalues['search1currin']==$row_currency['name'] ? "selected='selected'" : ""?> 
    value="<?=$row_currency['name'];?>"><?=$row_currency['extname']; ?></option>
    
    <?php } 
	$query = _query ($select, "ad/orders.php 12");?>
    <option <?=$searchvalues['search1currin']=="--" ? "selected='selected'" : ""?> value="--">-----------------</option>
    <option <?=$searchvalues['search1currin']=="P24" ? "selected='selected'" : ""?> value="P24">Приватбанк</option>
    <option <?=$searchvalues['search1currin']=="MCV" ? "selected='selected'" : ""?> value="MCV">Mastercard/VISA</option>
    <option <?=$searchvalues['search1currin']=="WM" ? "selected='selected'" : ""?> value="WM">Webmoney</option>
     <option <?=$searchvalues['search1currin']=="RUB" ? "selected='selected'" : ""?> value="WM">Русские банки</option>
    </select>
    </td>
    <td>валюта в: <br /><select name="search1currout">
    <option value=""></option>
	<?php while ( $row_currency = $query->fetch_assoc() ) {?>
    <option  <?=$searchvalues['search1currout']==$row_currency['name'] ? "selected='selected'" : ""?> 
    value="<?=$row_currency['name'];?>"><?=$row_currency['extname']; ?></option>
    
    <?php } ?>
    <option <?=$searchvalues['search1currout']=="--" ? "selected='selected'" : ""?> value="--">-----------------</option>
    <option <?=$searchvalues['search1currout']=="P24" ? "selected='selected'" : ""?> value="P24">Приватбанк</option>
    <option <?=$searchvalues['search1currout']=="MCV" ? "selected='selected'" : ""?> value="MCV">Mastercard/VISA</option>
    <option <?=$searchvalues['search1currout']=="WM" ? "selected='selected'" : ""?> value="WM">Webmoney</option>
 	<option <?=$searchvalues['search1currout']=="RUB" ? "selected='selected'" : ""?> value="RUB">Русские банки</option>
    </select></td>
    <td>заявка оформлена?: <br /><select name="search1ordered">
    <option value=""></option>    
    <option value="1" <?=$searchvalues['search1ordered']=="1" ? "selected='selected'" : ""?>>Оформлена</option>
    <option value="0" <?=$searchvalues['search1ordered']=="0" ? "selected='selected'" : ""?>>Неоформлена</option> 
    </select></td>
    <td colspan="2">Заявка оплачена?:<br /><select name="search2ordered">
    <option value="" selected="selected"></option>    
    <option value="1" <?=$searchvalues['search2ordered']=="1" ? "selected='selected'" : ""?>>Оплачена</option>
    <option value="0" <?=$searchvalues['search2ordered']=="0" ? "selected='selected'" : ""?>>Неоплачена</option> 
    </select></td>
    <td>Заявка погашена?:<br /><select name="search2canceled">
    <option value="" selected="selected"></option>    
    <option value="1" <?=$searchvalues['search2canceled']=="1" ? "selected='selected'" : ""?>>Погашена</option>
    <option value="0" <?=$searchvalues['search2canceled']=="0" ? "selected='selected'" : ""?>>Непогашена</option> 
    </select></td>
    <td>Проверка реквизитов:<br /><select name="search1needcheck">
    <option value="" selected="selected"></option>    
    <option value="1" <?=$searchvalues['search1needcheck']=="1" ? "selected='selected'" : ""?>>Требуется</option>
    <option value="0" <?=$searchvalues['search1needcheck']=="0" ? "selected='selected'" : ""?>>Не требуется</option> 
    <option value="2" <?=$searchvalues['search1needcheck']=="2" ? "selected='selected'" : ""?>>Не удовлетв.</option>
    </select></td>
    </tr>
    <tr class="td_head">
    <td>ID заявки: <br /><input name="search1id" type="text" value="<?=$searchvalues['search1id']?>"/></td>
    <td>Время:<br /> <input name="search1time" type="text" value="<?=$searchvalues['search1time']?>"/></td>
    <td>E-mail:<br /> <input name="search1email" type="text" value="<?=$searchvalues['search1email']?>"/>
    </td>
    <td>№ банк. карты: <br /><input name="search1account" type="text" value="<?=$searchvalues['search1account']?>"/>
    </td>
   <?php /* <td>№ счета магазина: <br /><input name="search2id" type="text" value="<?=$searchvalues['search2id']?>"/>
    </td>
    <td>№ счета WM:<br /> <input name="search2LMI_SYS_INVS_NO" type="text" value="<?=$searchvalues['search2LMI_SYS_INVS_NO']?>"/>
    </td>  */ ?>  
    <td>Кошелек др.:<br /> <input name="search1purse_other" type="text" value="<?=$searchvalues['search1purse_other']?>"/>
    </td>
    <?php /*<td>Кошелек-плательщик WM:<br /> <input name="search2LMI_PAYER_PURSE" type="text" value="<?=$searchvalues['search2LMI_PAYER_PURSE']?>"/></td>*/ ?>
    <?php /*<td>Запросный WMID:<br /> <input name="search2LMI_PAYER_WM" type="text" value="<?=$searchvalues['search2LMI_PAYER_WM']?>"/>
    </td> */ ?>
    <td>clid:<br /> <input name="search1clid" type="text" value="<?=$searchvalues['search1clid']?>"/></td>
    <td><input name="" type="reset" value="Сброс" /> <input type="submit" value="OK" /></td>
  </tr></form>
 </table><hr />
<?php $row_orders=mysql_fetch_assoc($orders);?>
   
<table border="0" align="center" width="100%" class="tableborder">
 <tr><td>Сумма входа: <?=$orderssumm_row['summ_from']?></td><td>Сумма выхода: <?=$orderssumm_row['summ_to']?></td></tr>
   <tr class='td_head'>
    <td colspan="3"></td>
  </tr> 
  <?php
  $td_style='td';$i=0;
  	//echo mysql_num_rows ($orders);
  	do { 
	  	$i=$i+1;
		if ( $row_orders['ordered']==1 ) {
			$select="select nikname, name, phone from clients where clid='".$row_orders['clid']."'";
			$query=_query($select, "orders.php 33");
			$row_client=$query->fetch_assoc();
		}
  
  	?>
    <form name="<?=$row_orders['orderid'];?>" action="orders.php?<?=$queryString_orders;?>#href<?=$row_orders['orderid']?>" method="post">
    <input type="hidden" name="id" value="<?=$row_orders['orderid'];?>" />
    <tr><td colspan="3" height="30"></td></tr>
    <tr><td rowspan="2" valign="top">
    <a name="href<?=$row_orders['orderid']?>"></a>
    <table>
      <tr><td width="100"><strong>№ заявки</strong></td><td align="right" width="250"> <strong><?=$row_orders['orderid']; ?></strong></td></tr>
      <tr><td>Направление</td><td align="right"> <?=$row_orders['summin'].' '.$row_orders['currin']." -> ".
	  ($row_orders['summout']+$row_orders['discammount']).' '.$row_orders['currout']?></td></tr>
	  <tr><td>Скидка:</td><td align="right"> <?=$row_orders['discammount'].' '.$row_orders['currout'].' ('.(($row_orders['disc']-1)*100).'%)'?></td></tr>
      <?php if ( $row_orders['summin']!=0 && ($row_orders['summout']+$row_orders['discammount'])!=0 ) { ?>
      <tr><td>Курс/кросс:</td><td align="right"> <?=round($row_orders['summin']/($row_orders['summout']+$row_orders['discammount']),3)?> /
      <?=round(($row_orders['summout']+$row_orders['discammount'])/$row_orders['summin'],3)?> </td></tr>
      <?php } ?>      
	  <tr><td>Доступный баланс:</td><td align="right"> <?=$row_orders['av_balance']?>&nbsp; </td></tr>
        
      <?php /*<tr><td>Партнер</td><td align="right"> <?=$row_orders['partnerid']?>&nbsp; </td></tr> */ ?>
      <tr><td>Время</td><td align="right"> <?=$row_orders['time']?>&nbsp; </td></tr>
      <?php if ( substr($row_orders['currin'],0,2)=="WM" || substr($row_orders['currout'],0,2)=="WM" ) { ?>
      <tr><td>Кошелек</td><td align="right"> 
	  <?php 
	
	  if ( $row_orders['purse_z']!=0 ) {$s="z";$b="Z";} 
	  if ( $row_orders['purse_r']!=0 ) {$s="r";$b="R";}
	  if ( $row_orders['purse_u']!=0 ) {$s="u";$b="U";}
	  if ( $row_orders['purse_e']!=0 ) {$s="e";$b="E";}
		if ( (isset($s) && $s!="") && (isset($b) && $b!="") ) {
		echo '<input type="hidden" name="purseType" value="'.$s.'">
		'.$b.'<input type="text" name="purse" size=15 value="'.$row_orders['purse_'.$s].'">';
		$pursefind=1;
		$s="";$b="";
		}
		if ( $pursefind!=1 ) {
		?>
        <select name="purseType"><option value="z">Z</option><option value="r">R</option><option value="u">U</option><option value="e">E</option>
        </select>
        <input type="text" size="15" name="purse" />
        
        <?php	
		}
		$pursefind=0;
	  ?>
      &nbsp;
	  </td></tr>
      
      <?php } if ( $row_orders['wmid']!="" ) {  ?>
      <tr><td>Запросный WMID</td><td align="right"><input name="wmid" value="<?=$row_orders['wmid']?>" size="15" style="text-align:right" /> || BL:
    <a href="https://passport.webmoney.ru/asp/certview.asp?wmid=<?=$row_orders['wmid']?>" target=_blank>
    <img src="https://stats.wmtransfer.com/Levels/pWMIDLevel.aspx?wmid=<?=$row_orders['wmid']?>&bg=0xF5F5F5" 
  	width="32" height="24" align="absmiddle" border="0">
    </a></td></tr>
      <?php } if ( $row_orders['ordered']==1 ) {  ?>
      <tr><td>Клиент</td><td align="right"><?=$row_client['nikname']?>&nbsp;</td></tr>
      <?php } if (strlen($row_orders['fname'])!=0) { ?>
      <tr><td>ФИО:</td><td align="right"><?=$row_orders['fname']." ".$row_orders['iname']." ".$row_orders['oname']?></td></tr>
      <?php } if (strlen($row_orders['purse_other'])!=0) {?>
      <tr><td>Кошелек:</td><td align="right"><input name="purse_other" value="<?=$row_orders['purse_other']?>" size="15" style="text-align:right" /></td></tr>
      <?php } if (strlen($row_orders['passport'])!=0) {?>
      <tr><td>Паспорт:</td><td align="right"><?=$row_orders['passport']?></td></tr>
      <?php } if (strlen($row_orders['phone'])!=0) {?>
      <tr><td>Телефон:</td><td align="right"><?=$row_orders['phone']?></td></tr>
      <?php } if (strlen($row_orders['email'])!=0) {?>      
      <tr><td>e-mail:</td><td align="right"><?=$row_orders['email']?></td></tr>             
      <?php } if (strlen($row_orders['account'])!=0) {?>
      <tr><td>счет:</td><td align="right"><input name="account" value="<?=$row_orders['account']?>" size="30" style="text-align:right" /></td></tr>
      <?php } if (strlen($row_orders['account_comment'])!=0) {?>
      <tr><td>Примечание к счету:</td><td align="right"><?=$row_orders['account_comment']?></td></tr>
      <?php } if (strlen($row_orders['bank_name'])!=0) {?>
      <tr><td>Банк</td><td align="right"><?=$row_orders['bank_name']?></td></tr>
      <?php } if (strlen($row_orders['inn'])!=0) {?>
      <tr><td>ИНН</td><td align="right"><?=$row_orders['inn']?></td></tr>
      <?php } if (strlen($row_orders['bank_comment'])!=0) {?>
      <tr><td>Комментарий:</td><td align="right"><?=$row_orders['bank_comment']?></td></tr>
      <?php } ?>
      <tr><td>В черный список</td><td align="right"><input type="checkbox" name="blacklist" value="1" <?php echo $row_orders['blacklist']==1 ? 'checked' : ''; ?> /></td></tr>
      <tr><td>IP:</td><td align="right"><a href="http://dns.com.ua/whois/?domain=<?=$row_orders['ip'];?>"><?=$row_orders['ip']?></a></td></tr>
     <tr><td colspan=2>Примечание:<br />
    <textarea cols="30" rows="2" name="status"><?=$row_orders['orderstatus']?></textarea>
	</td></tr>
    <tr><td></td><td align="right">
      <input type="hidden" name="update_order" value="ok" /><input type="submit" value="Обновить" /></td></tr>  
  </table>
  </td><td align="left" valign="top">
  <?php /*<form name="<?php echo $row_orders['orderid'];?>" action="orders.php?<?php echo $queryString_orders;?>" method="post">
    <input type="hidden" name="id" value="<?php echo $row_orders['orderid'];?>" />
	*/ ?>
  <table>
  <tr><td colspan="2"><strong>Входящий платеж</strong></td></tr>
  <tr><td width="100">Время:</td><td align="right" width="250"> <?php  echo $row_orders['payment_timestamp']; ?></td></tr>
  <tr><td>Транзакция:</td><td align="right"> <?=$row_orders['LMI_SYS_TRANS_NO']?></td></tr>
  <tr><td>Плательщик:</td><td align="right"> <?=$row_orders['LMI_PAYER_PURSE']?></td></tr>
  <tr><td>WMID(статус)</td><td align="right"> <?=$row_orders['LMI_PAYER_WM']?> 
  	<?php if (strlen($row_orders['LMI_PAYER_WM'])>0 && substr($row_orders['currin'],0,2)=="WM" ) { ?> || BL:
    <a href="https://passport.webmoney.ru/asp/certview.asp?wmid=<?=$row_orders['LMI_PAYER_WM']?>" target=_blank>
    <img src="https://stats.wmtransfer.com/Levels/pWMIDLevel.aspx?wmid=<?=$row_orders['LMI_PAYER_WM']?>&bg=0xF5F5F5" 
  	width="32" height="24" align="absmiddle" border="0">
    </a>
    <?php } ?>
  </td></tr>
  <tr><td>Статус</td><td align="right"> <?=$row_orders['status']?></td></tr>      
    </table>
    <?php /*<input type="hidden" name="update_payment" value="ok" />
    </form> */ ?>
    </td>
      <?php if ( isset($row_orders['orderid']) ) {
	   		$select="select * from payment_out where payment=".$row_orders['orderid'];
	   		$query = _query($select,"ad/orders.php 12");
	   		$row_out=$query->fetch_assoc();
	  }	?>
            
      <td valign="top">
      <?php if ( isset($row_out) ) {?>
    <table>
  <tr><td colspan="2"><strong>Исходящий платеж</strong></td></tr>          
            


  <tr><td width="100">Время:</td><td align="right" width="250"> <?php  echo $row_out['time']; ?></td></tr>
	<?php if ( $row_out['protection']!='' ) { ?>
        <tr><td>Код протекции</td><td align="right"><?=$row_out['protection']?></td></tr>
    <?php } ?>  
  <tr><td>Получатель:</td><td align="right"> <?=$row_out['purse']?></td></tr>
  <?php if ( $row_orders['wmid']!='' ) { ?> 
  <tr><td>WMID:</td><td align="right"> <?=$row_out['wmid']?></td></tr>
  <?php } if ( $row_out['retval']!=0 ) { ?>
  <tr><td>Статус-код:</td><td align="right"> <?=$row_out['retval']?></td></tr>
  <?php } if ( $row_out['retdesc']!='' ) { ?>
  <tr><td>Статус-описание:</td><td align="right"> <?=$row_out['retdesc'];?></td></tr>
  <?php } if ( $row_out['opertype']!='' ) { ?>
  <tr><td>Статус операции (0 - успешно):</td><td align="right"> <?=$row_out['opertype']?></td></tr> 
  <?php } if ( $row_out['payer']!='' ) { ?>
  <tr><td>Плательщик:</td><td align="right"> <?=$row_out['payer']?></td></tr> 
  <?php } ?>    
    </table>
    <?php } ?>
    </td>
    
    
    </tr>  

   <tr>
   <td colspan="2">
   <strong>Действия:</strong><br />  

   
    
   		<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" border="0" width="800">
       <form name="t<?=$row_orders['orderid'];?>#href<?=$row_orders['orderid']?>" action="orders.php?<?=$queryString_orders;?>" method="post">
        <input type="hidden" name="id" value="<?php echo $row_orders['orderid'];?>" />
        <tr><td>Оформлена-></td><td align="right"> 
        		<input type="checkbox" name="ordered" value="1" <?=$row_orders['ordered']!=0 ? 'checked' : ''; ?> /></td>
          <td width="20"></td>
        <td>Оплачена-></td><td align="right"> 
        		<input type="checkbox" name="payed" value="1" <?=$row_orders['recept']!=0 ? 'checked' : ''; ?> /></td>
                <td width="20"></td>
      	<td>Погашена-></td><td align="right"> 
        		<input type="checkbox" name="canceled" value="1" <?=$row_orders['canceled']==1 ? 'checked' : ''; ?> /></td>
                <td width="20"></td>
      
      	<td>Проверка:<br />
		<select name="needcheck">
        <option value="0" <?=$row_orders['needcheck']==0 ? 'selected="selected"' : ""?> >Не требуется</option>
        <option value="1" <?=$row_orders['needcheck']==1 ? 'selected="selected"' : ""?> style="color:#F00" >Требуется</option>
        <option value="2"<?=$row_orders['needcheck']==2 ? 'selected="selected"' : ""?>>Не удовл. треб.</option>
        </select>
</td>
                <td width="20"></td>
      
      	<td>Авторизована-></td><td align="right">
        	<input type="checkbox" name="authorized" value="1" <?=$row_orders['authorized']==1 ? 'checked' : ''; ?> /></td>
        <td>Вручную-></td><td align="right">
        	<input type="checkbox" name="droped" value="1" <?=$row_orders['droped']==1 ? 'checked' : ''; ?> /></td>
            <input type="hidden" name="update_orders" value="ok" />
    	</tr>
        <tr><td colspan="4">Счет: <select name="bank_account_type">
        <option value="0" <?=$row_orders['bank_account_type']==0 ? 'selected="selected"' : ""?> >Именной</option>
        <option value="1" <?=$row_orders['bank_account_type']==1 ? 'selected="selected"' : ""?>>Виртуальный</option>
        <option value="2"<?=$row_orders['bank_account_type']==2 ? 'selected="selected"' : ""?>>Родственник</option>
        <option value="3"<?=$row_orders['bank_account_type']==3 ? 'selected="selected"' : ""?>>Ошибка в написании</option>
        </select></td><td colspan="2"><input type="submit" value="Обновить"></td><td colspan="4"><input size="40" value="<?=$clorder->get_pay_comment($row_orders);?>"></td></tr>
        </form>
        <?php if ( $row_orders['needcheck']==1 ) { ?>
        <tr><td colspan="7"><form name="mail<?=$row_orders['orderid'];?>#href<?=$row_orders['orderid']?>" action="orders.php?<?php echo $queryString_orders;?>" method="post">
        <input type="hidden" name="id" value="<?=$row_orders['orderid'];?>" />
        <input type="hidden" name="action" value="mail_needcheck" />
        Проверка реквизитов: <select name="type">
        <option value="0">Успешная</option>
        <option value="2">Неуспешная</option>
        </select> <input type="submit" value="Отправить письмо">
        </form></td>
        </tr>
        <?php } ?>
        </table>
     <?php if ( (substr($row_orders['currout'],0,3)=="P24" || substr($row_orders['currin'],0,3)=="P24" ) 
			   && strlen($row_orders['account'])>5 ) {
		 $select="select * from bank_accounts where 
		 							account='".$row_orders['account']."' 
									and fname='".$row_orders['fname']."'
									and iname='".$row_orders['iname']."'";
		 $query=_query($select,"");
		 $bank=$query->fetch_assoc();
		 ?>
         <form method="post" action="orders.php?<?=$queryString_orders;?>#href<?=$row_orders['orderid']?>">
         <input type="hidden" name="fname" value="<?=$row_orders['fname']?>" />
         <input type="hidden" name="iname" value="<?=$row_orders['iname']?>" />
         <input type="hidden" name="clid" value="<?=$row_orders['clid']?>" />
         <input type="hidden" name="account" value="<?=$row_orders['account']?>" />
         <input type="hidden" name="wmid" value="<?=$row_orders['wmid']?>" />
         <input type="hidden" name="bank" value="update" />
    <table><tr><td>X19:<br />
		<select name="verified">
        <option value="0" <?=$bank['verified']==0 ? 'selected="selected"' : ""?> >Требуется</option>
        <option value="1" <?=$bank['verified']==1 ? 'selected="selected"' : ""?> >Проверено</option>
        <option value="2"<?=$bank['verified']==2 ? 'selected="selected"' : ""?>>Не удовл. треб.</option>
        </select>
        Счет: <select name="type">
        <option value="0" <?=$bank['type']==0 ? 'selected="selected"' : ""?> >Именной</option>
        <option value="1" <?=$bank['type']==1 ? 'selected="selected"' : ""?>>Виртуальный</option>
        <option value="2"<?=$bank['type']==2 ? 'selected="selected"' : ""?>>Родственник</option>
        <option value="3"<?=$bank['type']==3 ? 'selected="selected"' : ""?>>Ошибка в написании</option>
        <option value="4"<?=$bank['type']==4 ? 'selected="selected"' : ""?>>Транслит</option>
        </select>
        <input type="submit" value="Обновить" />
        </td></tr></table>
        </form>
    <?php 
	
	} ?>
    
   <strong>Платежи:</strong>

   <table bgcolor="#FFFFFF"><tr><td width="800"> 
   <?php if ( $row_orders['ordered']==1 && $row_orders['recept']==1 && $row_orders['canceled']==0 ) { ?>
	<form method='post' id="pay<?=$row_orders['id']?>">
	  <input type='hidden' name='pay_<?=substr(strtolower($row_orders['currout']),0,2)?>' value='pay'>
	  <input type='hidden' name='oid' value='<?=$row_orders['orderid']?>'>
	  <input type='button' value='Выполнить платеж <?=$row_orders['currout_extname']?>'
	onclick="mkpay('<?=($row_orders['summout']+$row_orders['discammount']." ".$row_orders['currout_extname'])?>',<?=$row_orders['id']?>);">
    </form>
    <?php if ( substr($row_orders['currout'],0,3)=="P24" ) { ?>
    <form method='post' id="pays<?=$row_orders['id']?>">
	  <input type='hidden' name='pay_<?=substr(strtolower($row_orders['currout']),0,2)?>' value='pay'>
      <input type="hidden" name="noreserve" value="ok" />
	  <input type='hidden' name='oid' value='<?=$row_orders['orderid']?>'>
	  <input type='button' value='Выполнить платеж <?=$row_orders['currout_extname']?> без учета резерва'
	onclick="mkpay('<?=($row_orders['summout']+$row_orders['discammount']." ".$row_orders['currout_extname'])?>','s<?=$row_orders['id']?>');">
    </form>
    <?php } ?>
      <?php if ( substr($row_orders['currin'],0,2)=="WM" ) { ?>
		<form  method='post' id="pay_<?=$row_orders['id']?>">
	  	<input type='hidden' name='back_wm' value='pay'>
	  	<input type='hidden' name='oid' value='<?=$row_orders['orderid']?>'>
	  	<input type='button' value='Вернуть WM отправителю' onclick="mkpay('<?=($row_orders['summout']+$row_orders['discammount']." ".$row_orders['currout_extname'])?>','_<?=$row_orders['id']?>');"></form>	  
	  <?php } 
   }?>
      
   <?php /*?><input type="button" value="Выполнить X19" onClick="do_x19();" /><span id="x19"></span><?php */?>
   
   </td></tr></table>
   <br />
<br />

<?php /*
   <strong>Информация о клиенте:</strong><br /> 
   <a href="<?="orders.php?search1clid=".$row_orders['clid'] ?>">Заявки</a> | 
   <a href="<?="referer.php?search1clid=".$row_orders['clid'] ?>">Переходы</a> | 
   <a href="<?=$siteroot?>cabinet.php?oid=<?=$row_orders['orderid']."&clid=".$row_orders['clid']?>">Инфо о заявке</a> |
   <a href="<?=$siteroot?>adrefzw/client.php?search1clid=<?=$row_orders['clid']?>">Карточка клиента</a> |
   <a href="<?=$siteroot?>adrefzw/client.php?action=cabinet&clid=<?=$row_orders['clid']?>">Войти на сайт как клиент-></a> | 
	<a href="<?=$siteroot?>adrefzw/orders.php?action=xml&oid=<?=$row_orders['orderid']?>" target="_blank">XML-запросы</a> |
	*/ ?>
   </td>
   </tr>
 	<tr class='td_head'>
    <td colspan="3"></td>
  </tr>
    <?php 
		if ( $i==1 ) {$td_style='td_white'; $i=-1; } else {$td_style='td';}
	} while ($row_orders = mysql_fetch_assoc($orders))  ?>
</table>
<table border="0" align="center">
  <tr>
    <td><?php if ($page > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, 0, $queryString_orders); ?>"><<Начало</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($page > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, max(0, $page - 1), $queryString_orders); ?>"><Пред.</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($page < $totalPages) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, min($total, $page + 1), $queryString_orders); ?>">След.></a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($page < $totalPages) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, $totalPages, $queryString_orders); ?>">Посл>></a>
        <?php } // Show if not last page ?></td>
  </tr>
  </table>
  <table align="center">
  <tr>
    <td align="center" colspan="4">Запись с <?php echo ($start + 1) ?> по <?php echo min($start + $max, $total) ?>. Всего: <?php echo $total; ?>
	</td>
  </tr>
</table>
