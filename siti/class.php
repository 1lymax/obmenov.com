<?php 

require_once($serverroot."siti/_header.php");
require_once($serverroot."siti/p24api.php");
class amount {
	var $shop_wm_purse= array('Z'=>'Z159873834302','R'=>'R173176236342','E'=>'E209292123025','U'=>'U345709199686');
	public $shopwmid="219391095990";
	
	function __construct(){
     //nothing
    }	
	
	function update ($ccy=false, $paymentid=false) {
		//Webmoney
		$wmxi=new WMXI("/var/www/webmoney_ma/data/etc/WebMoneyCA.crt", DOC_ENCODING);
		$wmxi->Classic("418941129503", "", "/var/www/webmoney_ma/data/etc/418941129503.kwm");
		$parser = new WMXIParser();
		$response = $wmxi->X9($this->shopwmid);
		$structure = $parser->Parse($response, DOC_ENCODING);
		$transformed = $parser->Reindex($structure, true);
		if ( !isset($transformed["w3s.response"]) ) die();
		if ( @$transformed["w3s.response"]["retval"]!=0 ){
			_error("Ошибка интерфейса Х9: Код: ".$transformed["w3s.response"]["retval"].
				" Описание: ".$transformed["w3s.response"]["retdesc"] , "result.php X9", "");	
		}else{
			$items = @$structure["0"]["node"]["1"]["node"];
			$items = is_array($items) ? $items : array();
			foreach($items as $k => $v) {
				$vv = $parser->Reindex($v["node"], true);
				if ( $vv["pursename"]==$this->shop_wm_purse[substr($vv["pursename"],0,1)] ) {
					$WM_amount["WM".substr($vv["pursename"],0,1)]=$vv["amount"];
					if ( $ccy==false || $ccy=="WM".substr($vv["pursename"],0,1)) {
						//echo 1;
						$select="select * from amounts where id>".$GLOBALS['idmax_amounts']." and val='WM".
											substr($vv["pursename"],0,1)."' and (time + INTERVAL 1 DAY > NOW()) order by time desc;";
						$query=_query2($select,"");
						$row=$query->fetch_assoc();
						if ( floor($row['amount'])!=floor($vv["amount"]) ) {
							//if ( substr($vv["pursename"],0,1)=="R" ) maildebugger($row['amount']." ".$vv["amount"]);
							$insert="insert into amounts (val,amount, account) values ('"."WM".substr($vv["pursename"],0,1)."',".
																				$vv["amount"].",'".$vv["pursename"]."')";
							$query=_query($insert,"");
						}
							
					}
						
					
				}
			}
		}//Webmoney
		
	// liqpay
	if ( 1==1 ) {
  		require_once($GLOBALS['serverroot']."siti/lp.class.php");
		$select="select * from merch where type='liqpay' and work=1";
		$query=_query($select,"");
		while ( $row=$query->fetch_assoc() ) {
			echo $row['id']."<br>";
			$liqpay=new liqpay($row['id']);
			$lq_balance=$liqpay->balance();
			foreach ($lq_balance as $a=>$v) {
			//echo $a."=".$v."<br />";
				$select="select * from amounts where id>".$GLOBALS['idmax_amounts']." and val='LQ".
													str_replace("RUR","RUB",strtoupper($a))."' and account='".
													$row['number']."' and (time + INTERVAL 1 DAY > NOW()) order by time desc;";
				//echo $select."<br>";
				$query1=_query2($select, "class.php liqpay balance");
				$amount=$query1->fetch_assoc();
				if ( floor($amount['amount'])!=floor($v) ) {
					//echo $amount['amount']." - ".$v."<br>";
					$insert="insert into amounts (val,amount, account) values ('LQ".
												str_replace("RUR","RUB",strtoupper($a))."',".$v.",'".$row['number']."')";
					//echo $insert;
					$query2=_query($insert,"");
				}
			}
		echo $row['id']."<br>";
		}
	}
	// end liqpay
	
	//pm update
if (1==1) {
	//echo "pm_update";
	$ch = curl_init();
	$url='https://perfectmoney.is/acct/balance.asp?AccountID='.$GLOBALS['pm_id'].'&PassPhrase='.$GLOBALS['pm_phrase'];
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CAINFO, $GLOBALS['serverroot']."siti/perfectmoney.crt"); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $out = curl_exec($ch);
	curl_close($ch);

	if(!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $out, $result, PREG_SET_ORDER)){
	   print_r($out);
	   exit;
	}
	//$insert="insert into xml ( `iface`, `query`, `answer` ) values ('PM balance', '".mysql_real_escape_string($out)."', '".print_r($result)."')";
	//$query=_query2($insert, "pm.xml");
	$ar="";
	foreach($result as $item){
	   $key=$item[1];
	   $ar[$key]=$item[2];
	}
	// проверка баланса по usd
	$select="select * from amounts where id>".$GLOBALS['idmax_amounts']." and val='PMUSD' and account='".$GLOBALS['pm_usd']."' and (time + INTERVAL 1 DAY > NOW()) order by time desc;";
	$query=_query2($select, "class.php pm balance");
	$row=$query->fetch_assoc();
	if ( floor($row['amount'])!=floor($ar[$GLOBALS['pm_usd']]) ) {
		$insert="insert into amounts (val,amount, account) values ('PMUSD',".
										$ar[$GLOBALS['pm_usd']].", '".$GLOBALS['pm_usd']."')";
		$query=_query($insert,"");
	}
	$select="select * from amounts where id>".$GLOBALS['idmax_amounts']." and val='PMEUR' and account='".$GLOBALS['pm_eur']."' and (time + INTERVAL 1 DAY > NOW()) order by time desc;";
	$query=_query2($select, "class.php pm balance");
	$row=$query->fetch_assoc();
	if ( $row['amount']!=$ar[$GLOBALS['pm_eur']] ) {
		$insert="insert into amounts (val,amount, account) values ('PMEUR',".
							$ar[$GLOBALS['pm_eur']].", '".$GLOBALS['pm_eur']."')";
		$query=_query($insert,"");
	}

}
if (1!=1) { // liberty update
	require_once($GLOBALS['serverroot']."siti/lib.lr.php");
	$lr=new lrWorker($GLOBALS['lr_id']);
	//$lr->lr($GLOBALS['lr_id']);
	$balance=$lr->Balance(microtime()*10000000);
	if ( isset($balance['BALANCE']) ) {
		$select="select * from amounts where id>".$GLOBALS['idmax_amounts']." and val='LRUSD' and account='".$GLOBALS['lr_acc']."' and (time + INTERVAL 1 DAY > NOW()) order by time desc;";
		
		$query=_query2($select, "class.php pm balance");
		$row=$query->fetch_assoc();
		echo $row['amount']." - ".$balance['BALANCE']['usd']['VALUE']."<br>";
		if ( floor($row['amount'])!=floor($balance['BALANCE']['usd']['VALUE']) ) {
			$insert="insert into amounts (val,amount,account) values ('LRUSD',".
										$balance['BALANCE']['usd']['VALUE'].",'".$GLOBALS['lr_acc']."')";
			$query=_query($insert,"");
		}
		$select="select * from amounts where id>".$GLOBALS['idmax_amounts']." and val='LREUR' and account='".$GLOBALS['lr_acc']."' and (time + INTERVAL 1 DAY > NOW()) order by time desc;";
		$query=_query2($select, "class.php pm balance");
		$row=$query->fetch_assoc();
		if (  floor($row['amount'])!=floor($balance['BALANCE']['euro']['VALUE']) ) {
			$insert="insert into amounts (val,amount,account) values ('LREUR',".
							$balance['BALANCE']['euro']['VALUE'].",'".$GLOBALS['lr_acc']."')";
			$query=_query($insert,"");
		}
	}
	
} //end liberty update




	}
	
	function update_p24_balance ($ccy=false) {
	
	//Privat
		
		require_once($GLOBALS['serverroot']."siti/p24api.php");
		$select="select * from merch where type='p24' and api=1";
		$query1=_query($select,"");
		while ( $row=$query1->fetch_assoc() ){

			$p24balance = new p24api($row['merchant_id'], $row['merchant'], 'https://api.privatbank.ua:9083/p24api/balance');
			$response = $p24balance->sendBalanceRequest();
			if ( is_array($response))return $response;
			$response=simplexml_load_string($response);
		
			if ( isset($response->data->info->cardbalance) ) {
				$p24_balance=$response->data->info->cardbalance->balance;
				$card=$response->data->info->cardbalance->card->card_number;
				
				$select="select * from amounts where id>".$GLOBALS['idmax_amounts']." and val='P24".$row['currency']."' and account='".$card."' 
								and (time + INTERVAL 3 DAY > NOW()) order by time desc";
				$query=_query2($select, "cron_amounts.php 5");
				$balance=$query->fetch_assoc();
				if ( intval($balance['amount']) != intval($p24_balance-1) && $p24_balance!=0 && $p24_balance!="" ) {
					$insert="insert into amounts (val, amount, account) values ('P24". $row['currency']."',".($p24_balance-1).", '".$card."')";
					$query=_query($insert, "cron_amounts.php 7");
				}
			}
		}
	echo "end_privat";		
		
	}
	
	
	function get ($type){
		

			$select="SELECT * FROM (
								SELECT *
								FROM amounts
								WHERE amounts.id > ".$GLOBALS['idmax_amounts']." and amounts.account
								IN (
									SELECT number
									FROM merch
									WHERE merch.active =1
								)
								ORDER BY time DESC
							) AS am
							GROUP BY val ";
			$query1=_query2($select, "amounts.php amount");

			if ( isset($_POST['clid']) ) {$clid=$_POST['clid'];}else {
				if ( isset($_SESSION['clid']) ) {$clid=$_SESSION['clid'];}else {$clid=0;}
			}

			$select = "SELECT currout, SUM( summout ) AS reserved_sum FROM orders, payment
					WHERE orders.id>".$GLOBALS['idmax_orders']." and orders.id=payment.orderid AND
					( (payment.canceled!=1 and payment.ordered=1 AND (time + INTERVAL 24 HOUR > NOW())) 
					OR ( payment.canceled=1 AND (time + INTERVAL 10	MINUTE > NOW()) and left(currout, 2)!='WM' ) )
			 		AND orders.clid!='".$clid."' group by currout";
			$query= _query2($select, "index.php 25");
			while ($reserved_row=$query->fetch_assoc() ) {
				$reserved1[$reserved_row['currout']]=$reserved_row['reserved_sum'];
			}
			$select = "SELECT currout, SUM( summout ) AS reserved_sum FROM orders, payment
					WHERE orders.id>".$GLOBALS['idmax_orders']." AND orders.id=payment.orderid
					AND payment.ordered=1 AND payment.canceled=0 AND (time + INTERVAL 3 DAY > NOW())
					GROUP BY orders.currout";
			
			$query= _query2($select, "index.php 25");
			$reserved_row=$query->fetch_assoc();
			while ($reserved_row=$query->fetch_assoc() ) {
				$reserved2[$reserved_row['currout']]=$reserved_row['reserved_sum'];
			}
		

		while ( $amount=$query1->fetch_assoc() ) {
			if (!isset($reserved1[$amount['val']]))$reserved1[$amount['val']]=0;
			if (!isset($reserved2[$amount['val']]))$reserved2[$amount['val']]=0;
			$WM_amount[1][$amount['val']]=$amount["amount"];
			$WM_amount[2][$amount['val']]=round($amount["amount"]/2-$reserved1[$amount['val']]-$reserved2[$amount['val']],1);
			if ( $WM_amount[2][$amount['val']] < 0 ) { 
				$WM_amount[2][$amount['val']] = 0;
			}

		}
		//$WM_amount['P24UAH']=5;
		//$WM_amount_r['P24UAH']=5;
		$WM_amount[2]["P24UAH_real"]=$WM_amount[2]["P24UAH"];
		$WM_amount[1]['P24UAH_real']=$WM_amount[1]['P24UAH'];
		/*if ( $WM_amount[2]["P24UAH"]>round($WM_amount[2]['P24USD']*7.75,1) || get_setting("use_privat24_uah_merchant")==1 ) {
			
		}else{
			$WM_amount[2]["P24UAH"]=$WM_amount[1]['P24UAH']=round($WM_amount[1]['P24USD']*7.75-
																$reserved1['P24UAH']-$reserved2['P24UAH'],1);
			if ( $WM_amount[2]['P24UAH'] < 0 ) $WM_amount[2]['P24UAH']=0;
		}*/
		//$WM_amount[1]['P24UAH_real']=$WM_amount[1]['P24UAH'];
		//$WM_amount[2]["MCVUAH"]=$WM_amount[2]["LQUAH"];
		//$WM_amount[2]["MCVUSD"]=$WM_amount[2]["LQUSD"];
		//$WM_amount[2]["MCVRUR"]=$WM_amount[2]["LQRUB"];
		//$WM_amount[2]["MCVEUR"]=$WM_amount[2]["LQEUR"];
		
		
//,'TBRUB','RUSSTRUB','SVBRUB'*/
		
			$query="select sum(amount) as amm, val from amounts where val in ('KS','".implode("','",$GLOBALS['rbanks'])."') group by val";
			$result=_query($query,"");
			while ( $a=$result->fetch_assoc() ){
				$row_amount[$a['val']]=$a['amm'];
			}
			$query="select sum(orders.summout) as s, orders.currout as curr from orders, payment where 
						orders.currout in ('KS','".implode("','",$GLOBALS['rbanks'])."') and 
						orders.id=payment.orderid 
						and payment.ordered=1 and orders.email!='".$GLOBALS['partner_email_for_reserve']."' 
						group by orders.currout";
			$result=_query($query,"");
			while ( $a=$result->fetch_assoc() ) {	
				$ordered_summ_out[$a['curr']]=$a['s'];
			}
			
			$query="select sum(orders.summin) as s, orders.currin as curr from orders, payment where 
						orders.currin in ('KS','".implode("','",$GLOBALS['rbanks'])."') and 
						orders.id=payment.orderid and orders.email!='".$GLOBALS['partner_email_for_reserve']."' 
						and payment.ordered=1 group by orders.currin";
			$result=_query($query,"");
			while ( $a=$result->fetch_assoc() ) {
				$ordered_summ_in[$a['curr']]=$a['s'];
			}
			
		
			foreach ( $row_amount as $currency=>$balance ) {
				$WM_amount[1][$currency]=$WM_amount[2][$currency]=round($row_amount[$currency]-$ordered_summ_out[$currency]+
																								$ordered_summ_in[$currency]);
				if ( $WM_amount[1][$currency]<0 ) {
					$WM_amount[1][$currency]=$WM_amount[2][$currency]=0;
				}
				
			}
		
		$b1=get_setting("privat_uah_balance");
		$b2=get_setting("privat_usd_balance");
		if ($b1!=0) $WM_amount[2]["P24UAH_real"]=$WM_amount[2]["P24UAH"]=$b1;
		if ($b2!=0) $WM_amount[2]["P24USD"]=$b2;
		//$WM_amount[2]["P24USD"]=12768;
		
		foreach ($WM_amount[2] as $key=>$val) {
			if ($val>999999) $WM_amount[2][$key]=999999;
		}
		//$WM_amount[2]["MCVUSD"]=$WM_amount[2]["LQUSD"]=5999;
		//$WM_amount[2]["MCVUAH"]=$WM_amount[2]["LQUAH"]=10999;
		//$WM_amount[2]["PMUSD"]=0;
		$WM_amount[1]["INSTAFX"]=$WM_amount[2]["INSTAFX"]=20000;
		$WM_amount[2]["UAH"]=0;
		$WM_amount[2]["USD"]=0;
		return $WM_amount;
	
			
	}
	
	
}

class client {
	//public $tt=$GLOBALS['ttt'];
	
	function __construct(){
     //nothing
    }	
	
	function clientid ($f, $clid){
		$select="select * from clients where ".$f."='".$clid."'";
		$query=_query($select,"");
		$query=$query->fetch_assoc();
		return $query;
		
	}
	function wmids($wmid) {
		require_once($GLOBALS['serverroot']."siti/_header.php");
		$wmxi=new WMXI($GLOBALS['serverroot']."siti/ns.cer", DOC_ENCODING);
		$wmxi->Classic($GLOBALS['shopwmid2'],$GLOBALS['wmkey'] , "/var/www/webmoney_ma/data/etc/418941129503.kwm");
		$parser = new WMXIParser();
		$response = $wmxi->X11(
			$wmid,     # 12 цифр
			0,           # 0/1
			0,           # 0/1
			1            # 0/1
		);

		$structure = $parser->Parse($response);
		$transformed = $parser->Reindex($structure, true);
		
		$structure=$structure['0']['node']['1']['node']['1']['node'];
		while (list($key, $val) = each($structure)) {
			$wmids[$key]=$val['@']['WMID'];
		}
		return $wmids;

	}
	
	function day_summ($wmid) {
		$wmids=$this->wmids($wmid);
		if ( $this->good_wmid($wmid) ) {
			return 0;
		}
		$select="SELECT sum( orders.attach ) AS sum
FROM orders , payment where
(orders.time + INTERVAL 1440 MINUTE > NOW()) and
orders.ordered=1 and payment.ordered=1 and payment.canceled=1
AND payment.orderid = orders.id and (";
		$wmid="wmid=";
		while (list($key, $val) = each($wmids)) {
			$select .= $wmid.$val;
			$wmid=" or wmid=";
		}
		$select .=")";
		$query=_query2($select,"class client->day_summ 1");
		$query=$query->fetch_assoc();
		return $query['sum'];
	}
	function good_wmid ($wmid) {
		if ( $wmid=="944818418518" ) {
			return 1;
		}else{
			return 0;
		}
	}
	
}



class orders {
	public $pb_usd_merchant="133";
	public $pb_usd_merchant_p="MHLLUHLsdYfHZhgfQ89sl9U6V60gQbnm";	
	public $pb_uah_merchant="79";
	public $pb_uah_merchant_p="Fz1EYvp63JwG9we74cOkt5Qo6izv27FI";	
	public $shop_email="support@obmenov.com";
	public $shop_name="Обменов.ком";
	var $shop_wm_purse= array('Z'=>'Z159873834302','R'=>'R173176236342','E'=>'E209292123025','U'=>'U345709199686');
	public $shopwmid="219391095990";
	public $shopwmid2="418941129503";
	public $key_pass="";
	public $key_path="/var/www/webmoney_ma/data/etc/418941129503.kwm";
	public $WebmoneyCA_path="/var/www/webmoney_ma/data/etc/WebMoneyCA.crt";
	

	function __construct(){
     //nothing
    }	
	
	function check_X19 ($row_order=0, $type) {
		$bank_name="";
		$direction=1;
		switch ( $row_order['currin'] ) {
			case "UAH" : case "USD" : $opertype=1;$direction=2;break;
			case "WMZ" : case "WMU" : case "WME" : case "WMR"  : 
				
				if ( substr($row_order['currout'],0,3)=="P24") {$opertype=4;$bank_name="Приватбанк UA";break;}
				if ( $row_order['currout']=="ACRUB" ) {$opertype=4;$bank_name="Альфабанк RU";break;}
				if ( $row_order['currout']=="TBRUB") {$opertype=4;$bank_name="ВТБ RU";break;}
				if ( $row_order['currout']=="RUSSTRUB") {$opertype=4;$bank_name="Банк 'Русский Стандарт' RU";break;}
				if ( $row_order['currout']=="SVBRUB") {$opertype=4;$bank_name="Банк 'Связной' RU";break;}
				if ( $row_order['currout']=="WIREEUR") {$opertype=4;$bank_name="IBAN EUR";break;}
				if ( substr($row_order['currout'],0,2)=="WM") $opertype=5;
		}
		switch ( $row_order['currout'] ) {
			case "UAH" : case "USD" : case "KS" : case "INSTAFX"  : $opertype=1;break;
			case "WMZ" : case "WMU" : case "WME" : case "WMR"  : 
				
				if ( substr($row_order['currin'],0,3)== "P24") {$opertype=4;$direction=2;$bank_name="Приватбанк UA";break;}
				if ( $row_order['currin']== "ACRUB") {$opertype=4;$direction=2;$bank_name="Альфабанк RU";break;}
				if ( $row_order['currin']== "TBRUB") {$opertype=4;$direction=2;$bank_name="ВТБ RU";break;}
				if ( $row_order['currin']== "RUSSTRUB") {$opertype=4;$direction=2;$bank_name="Банк 'Русский Стандарт' RU";break;}
				if ( $row_order['currin']== "SVBRUB") {$opertype=4;$direction=2;$bank_name="Банк 'Связной' RU";break;}
				if ( substr($row_order['currin'],0,2)=="WM" ) $opertype=5;
		}

		//echo $opertype;
		if ( substr($row_order['currin'],0,2)=="WM" ) {
			$pursetype=isset ($_POST['purseType']) ? "WM".$_POST['purseType'] : "WM".substr($row_order['currin'],2,1);
			$amount = $row_order['summin'];
		}elseif ( substr($row_order['currout'],0,2)=="WM" )  {
			$check=$this->check_pursewmid($row_order);
			if ( is_array($check) && $check['w3s.response']['retval'] == 1 && $check['w3s.response']['testwmpurse']['purse']=="" ) {
				return $check;
			}
			$pursetype=isset ($_POST['purseTypeOut']) ? "WM".$_POST['purseTypeOut'] : "WM".substr($row_order['currout'],2,1);
			$amount=($row_order['summout']+$row_order['discammount']);
		}
		if ( substr($row_order['currin'],0,3)=="MCV" ) {
			$opertype=6;
			$direction=2;
		}
		if ( $row_order['currin']=="SMS" ) {
			$opertype=1;
			$direction=1;
		}
		$parser = new WMXIParser();
		$wmxi=new WMXI($GLOBALS['serverroot']."siti/ns.cer", DOC_ENCODING);
					   //
					   //"/var/www/webmoney_ma/data/etc/WebMoneyCA.crt", DOC_ENCODING);
		$wmxi->Classic($this->shopwmid2, $this->key_pass, $this->key_path);
		
		if ( $opertype!=5 ) {
		if ( $type=="post" ) {
			$wmid=isset ($_POST['wmid']) ? $_POST['wmid'] : $row_order['wmid'];
			$pnomer=isset ($_POST['passport']) ? $_POST['passport'] : $row_order['passport'];
			$fname=isset ($_POST['fname']) ? $_POST['fname'] : $row_order['fname'];
			$iname=isset ($_POST['iname']) ? $_POST['iname'] : $row_order['iname'];
			$phone=isset ($_POST['phone']) ? $_POST['phone'] : $row_order['phone'];
			$card_number=isset ($_POST['account']) ? $_POST['account'] : $row_order['account'];
			
		}
		if ( $type=="get" ) {
			$wmid=isset ($_GET['wmid']) ? $_GET['wmid'] : $row_order['wmid'];
			$pnomer=isset ($_GET['pass']) ? iconv("utf-8","windows-1251",rawurldecode($_GET['pass'])) : $row_order['passport'];
			$fname=isset ($_GET['fname']) ? iconv("utf-8","windows-1251",rawurldecode($_GET['fname'])) : $row_order['fname'];
			$iname=isset ($_GET['iname']) ? iconv("utf-8","windows-1251",rawurldecode($_GET['iname'])) : $row_order['iname'];
			$phone=isset ($_GET['phone']) ? iconv("utf-8","windows-1251",rawurldecode($_GET['phone'])) : $row_order['phone'];
			$card_number=isset ($_GET['account']) ? $_GET['account'] : $row_order['account'];
		}

		//$wmid= $wmid=="" ? $row_order['wmid'] : $wmid;
		//$pnomer=  $pnomer=="" ? $row_order['passport'] : $pnomer;
		//$fname= $fname=="" ? $row_order['fname'] : $fname;
		//$iname=  $iname=="" ? $row_order['iname'] : $iname;
		//$card_number=  $card_number=="" ? $row_order['account'] : $card_number;
		$bank_account="";
		$emoney_name="";
		$emoney_id="";
		


			$response=$wmxi->X19($opertype, $pursetype, $amount, $wmid, $pnomer, $fname, $iname, $card_number, $bank_account, $bank_name, $emoney_name, $emoney_id, $direction, $phone);
			$structure = $parser->Parse($response, DOC_ENCODING);
			$transformed = $parser->Reindex($structure, true);

			return $transformed;

		}else{
			return $this->check_pursewmid($row_order);
		}
					
		
		
	}
	function check_pursewmid ($row_order) {
		
			if ( isset($_GET['wmid']) && isset($_GET['purse']) && $_GET['wmid']!="" && $_GET['purse']!="" ) {
				if ( substr($_GET['purse'],0,1) == "R" || substr($_GET['purse'],0,1) == "Z" || 
														substr($_GET['purse'],0,1) =="U" || 
														substr($_GET['purse'],0,1) == "E" ) {
					//echo "88".substr($_GET['purse'],0,1);
					if ( substr($_GET['purse'],0,1)!=substr($row_order['currout'],2,1) ) return "Неправильный тип кошелька для этой операции";
					$purse=$_GET['purse'];
				}else{
					$purse=substr($row_order['currout'],2,1).$_GET['purse'];
				}
					
				$wmxi=new WMXI("/var/www/webmoney_ma/data/etc/WebMoneyCA.crt", DOC_ENCODING);
				$wmxi->Classic($this->shopwmid2, $this->key_pass, $this->key_path);
				$parser = new WMXIParser();
				$response = $wmxi->X8($_GET['wmid'],$purse);
				$structure = $parser->Parse($response, DOC_ENCODING);
				$transformed = $parser->Reindex($structure, true);
				if ( isset($transformed["w3s.response"]) ) {
					return $transformed;
				}else{
					return "Запрос сейчас не может быть обработан";
				}
			}else{
				return "Не указан кошелек или WMID";
			}
		
	}
	
	
	function privat_blacklist ($oid){
		$select="select account from orders where id=".$oid;
		$query=_query($select,"class privatblacklist");
		$row_orders=$query->fetch_assoc();
		$select="select account, blacklist from orders where account='".$row_orders['account']."' and blacklist=1";
		$blacklist=_query($select,"paybank.php 56");
		if ( $blacklist->num_rows>0 ) {
			return 1;
		}else{
			return 0;
		}
	}

	function partner_bonus ($row_order=array()){
		if ( !is_array($row_order) ) {
			
			$select="select * from orders where id=".$row_order;
			$query=_query($select,"class->paypb");
			$row_order=$query->fetch_assoc();
		}
		$select="select * from partner_bonus where partnerid = ".$row_order['partnerid']." and orderid=".$row_order['id'];
		echo $select;
		$query=_query($select,"class-partner_bonus");
		if ( $query->num_rows==0 ) {
			echo "pb";
			$attach=_query("SELECT orders.attach FROM orders, payment 
				WHERE payment.orderid=orders.id 
				AND payment.ordered=1 
				AND orders.partnerid=".$row_order['partnerid']." AND orders.id=".$row_order['partnerid'], 'partner.php 3');
				$attach=$attach->fetch_assoc();
				$clients_count=_query("SELECT COUNT(DISTINCT orders.clid) FROM orders, payment 
					WHERE payment.orderid=orders.id 
					AND payment.ordered=1 
					AND orders.partnerid=".$row_order['partnerid'], 'partner.php 4');
				$clients_count=$clients_count->fetch_assoc();
				$clients_count=$clients_count['COUNT(DISTINCT orders.clid)'];
 				$query_disc="SELECT pndiscount.discount, pndiscount.descr, pndiscount.per_click FROM pndiscount WHERE pndiscount.users < ".
				$clients_count." AND pndiscount.till > ".$clients_count.";";
				$discount=_query2($query_disc, 'partner.php 8');
				$numrow_discount=$discount->num_rows;
				$partn_row_discount=$discount->fetch_assoc();
				if ( !$numrow_discount) {
					$query_disc="SELECT pndiscount.discount, pndiscount.descr, pndiscount.per_click 
					FROM pndiscount WHERE pndiscount.users = 0;";
					$discount=_query($query_disc, 'partner.php 9');
					$numrow_discount=$discount->num_rows;
					$partn_row_discount=$discount->fetch_assoc();
		
				}
				$bonus=$row_order['attach']*(($partn_row_discount['discount']-1)/65);
				$select="select id from partner_bonus where orderid=".$row_order['id'];
				$query=_query($select, "mcvresult.php 45");
 				if ( $query->num_rows == 0 ) {
					$insert=" INSERT INTO partner_bonus (partnerid, orderid, sum, bonus) VALUES (".
							$row_order['partnerid'].", ".
							$row_order['id'].", ".
							$row_order['attach'].", ".
							$bonus .");";
 
  					$insert=_query($insert,"result.php partner_bonus");
				}
		}
	}
	
	function pay_pb ($row_orders, $amounts, $test=0){
		if ( !is_array($row_orders) ) {
			
			$select="select * from orders where id=".$row_orders;
			$query=_query($select,"class->paypb");
			$row_orders=$query->fetch_assoc();
		}
		$select="select * from clients where clid='".$row_orders['clid']."'";
		$query=_query($select,"");
		$row_client=$query->fetch_assoc();
 		// платеж на карту приватбанка
		if ( $row_orders['currout']=="P24USD" ){
			$response= $this->pay_privat24($GLOBALS['pb_usd_merchant'],$GLOBALS['pb_usd_merchant_p'],
													$GLOBALS['pb_usd_merchant_card'],"USD",$amounts,$row_orders,$test);	
		}elseif ( $row_orders['currout']=="P24EUR" ){
			$response= $this->pay_privat24($GLOBALS['pb_eur_merchant'],$GLOBALS['pb_eur_merchant_p'],
													$GLOBALS['pb_eur_merchant_card'],"EUR",$amounts,$row_orders,$test);	
		}elseif ( $row_orders['currout']=="P24UAH" ){
			 if ( get_setting("use_privat24_uah_merchant") ) {
				 $response= $this->pay_privat24($GLOBALS['pb_uah_merchant'],$GLOBALS['pb_uah_merchant_p'],
													$GLOBALS['pb_uah_merchant_card'],"UAH",$amounts,$row_orders,$test);
			 }else {
				 $response= $this->pay_privat24($GLOBALS['pb_usd_merchant'],$GLOBALS['pb_usd_merchant_p'],
													$GLOBALS['pb_usd_merchant_card'],"UAH",$amounts,$row_orders,$test);
			 }
		} // Платеж в приват24 USD
		//maildebugger($response);
		return $response;
		
	
	}

	function pay_privat24 ($m,$p,$card,$ccy, $amounts, $row_orders, $test) {
			$parser = new WMXIParser();		
			//$p24api = new p24api($m, $p, 'https://api.privatbank.ua:9083/p24api/balance');
			//$response = $p24api->sendBalanceRequest();
			//$structure = $parser->Parse($response, DOC_ENCODING);
			$balance=($amounts['P24'.$ccy]>$row_orders['av_balance'] ? $amounts['P24'.$ccy] : $row_orders['av_balance']);
			//$structure['0']['node']['1']['node']['1']['node']['0']['node']['1']['data'];
		  if ( ($row_orders['summout']+$row_orders['discammount']) > $balance && 
						get_setting("privat24_check_balance_before_pay") && $test!=1  ) {
			$response[0]['message']= "Not enough money (Current balance: ".$balance.")";
			$response[0]['state']= 20;
			$errmessage="Недостаточно баланса для завершения операции";
			//echo "Недостаточно баланса (Текущий баланс: ".$balance.")";
		
		  }else{ 
			//echo "(Текущий баланс: ".$balance.")";
			$p24api = new p24api($m, $p, 'https://api.privatbank.ua:9083/p24api/pay_pb');
			$details=iconv("windows-1251","utf-8","Пополнение карточного счета №".$row_orders['account'].
																					". Перевод личных средств. ".$row_orders['id']);
			$payment= Array(
							Array('id'=>$row_orders['id'],
							'phone'=>'+380682587483',
							'b_card_or_acc'=>$row_orders['account'],
							'amt'=>($row_orders['summout']+$row_orders['discammount']),
							'ccy'=>$ccy,
							//'details'=>$row_orders['id']));
							'details'=>$details)
						);
			//$response = $p24api->sendCmtRequest($payment,0,1);    тестовый платеж
			//maildebugger($response);
			if ( 1==1 ) { 
			//$response[0]['state'] == 1 ){ тестовый платеж
				
				$response = $p24api->sendCmtRequest($payment,0,$test);
				if ( $response[0]['state'] == 1 && $test==0){
					$update="update payment set ordered=1, canceled=1 WHERE orderid=".$row_orders['id'];
					$query=_query($update,"ad/orders.php p24-2");
					$reference=strlen($response[0]['ref'])>0 ? $response[0]['ref']." " : "";	

					//$response[0]['message']="Платеж прошел. Референс: ".$reference;
				//отправка на мыло
					$client_info="SELECT clients.email, clients.id as clientid, orders.id FROM clients, orders WHERE clients.clid=orders.clid 
					AND orders.id=".$row_orders['id'];
					$row_client=_query($client_info,"ad update orders 3");
					$row_client=$row_client->fetch_assoc();
					send_mail($row_client['email'], '
Уважаемый(ая) покупатель!
			
Уведомляем вас об изменении статуса вашего заказа №: '.$row_client['id'].'
Ваш заказ получил статуc "Обмен завершен".

С уважением, Обменов.ком', "Обменов.ком :: изменен статус заявки № ".$row_client['id'],$GLOBALS['shop_email'] , $GLOBALS['shop_name']) ;
				$message=strlen($response[0]['message'])>0 ? ";message=".$response[0]['message'].";" : "";
				$select="select * from payment_out where payment=".$row_orders['id'];
				$query=_query($select,"paybank.php 45");
				$select="select partner from partner_transfers where own_order_id=".$row_orders['id'];
				$query2=_query($select,"");
				$partner_id=$query2->fetch_assoc();
				if ( $query->num_rows!=0 ) {
					$partner_pay=$query2->num_rows!=0 ? "partnerid=".$partner_id['partner']."," : "";					
					$insert="update payment_out set purse='".$row_orders['account']."',
													payer='".$card."',".
													$partner_pay.
													"clid='".$row_client['clientid']."', 
													summ=".($row_orders['summout']+$row_orders['discammount']).", 
													retdesc='".$reference.$message."' WHERE payment=".$row_orders['id'];
				}else{
					$partner_pay=$query2->num_rows!=0 ? $partner_id['partner'] : "0";	
					$insert="insert into payment_out (payment, purse, payer, clid, summ, retdesc, partnerid) values (".$row_orders['id'].
														", '".$row_orders['account'].
														"', '".$card.
														"', '".$row_client['clientid'].
														"', ".($row_orders['summout']+$row_orders['discammount']).
														", '".$reference.$message."',".$partner_pay.
														")";
									
				}
				$query=_query($insert,"siti/paybank.php p24-3");
				//$this->insert_p24uah_balance($row_orders);
				$this->partner_bonus($row_orders);
				//$this->send_sms($row_orders);
			}else{
			}
				
				
				// response-state == 1


			}else {
				$reference="";
				//$response[0]['message']="Платеж не прошел. Статус платежа не получен";
				$select="select * from payment_out where payment=".$row_orders['id'];
				$query=_query($select,"paybank.php 45");
				if ( $query->num_rows!=0 ) {
					//maildebugger($response);
					$update="update payment_out set retdesc='".iconv("utf-8","windows-1251",$response[0]['message'])."' WHERE payment=".$row_orders['id'];
				}else{
					$update="insert into payment_out (payment, retdesc) values (".$row_orders['id'].",'".$response[0]['message']."')";
				}
				$query=_query($update,"ad/orders.php p24-2");				
			}
			
		  }
		//if ( isset($errmessage) ) {return $errmessage;}
		return $response;
		
	}
	
	
	
	function insert_p24uah_balance($row_orders) {
		$select="select p24uah, timestamp from amount where p24uah!=0 order by timestamp desc";
				$query=_query($select,"siti/paybank.php 15");
				$p24uahbal=$query->fetch_assoc();
				
				$select="select p24uah_virtual, timestamp from amount where p24uah_virtual != 0 order by timestamp desc";
				$query=_query($select,"siti/paybank.php 16");
				$p24uahvirt_bal=$query->fetch_assoc();
				
				if ( $p24uahbal['timestamp'] > $p24uahvirt_bal['timestamp'] ) {
					$WM_amount_r["P24UAH"]=$p24uahbal['p24uah'];
				}else{
					$WM_amount_r["P24UAH"]=$p24uahvirt_bal['p24uah_virtual'];
				}
				
				
								
				$insert="insert into amount (p24uah_virtual, payment_id) values (".( $WM_amount_r["P24UAH"]-
																	($row_orders['summout']+$row_orders['discammount']) ).", ".$row_orders['id'].")";
				$query=_query($insert, "siti/paybank.php 6");
		
	}
	function update_p24_balance($ccy) {}
	function order_query($orderid) {
		$select="SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.summout, orders.clid, orders.purse_other,
	  			orders.discammount, orders.partnerid, orders.attach, orders.needcheck, orders.email, orders.disc,
				orders.fname, orders.iname, orders.account, orders.phone, orders.phone, orders.authorized,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e,	orders.retid, orders.wmid,
				(select type from currency where name=orders.currin) as currintype, 
				(select type from currency where name=orders.currout) as currouttype, 
				(select extname from currency where name=orders.currin) as extnamein, 
				(select extname from currency where name=orders.currout) as extnameout, 
				orders.needcheck
				FROM orders where id=".$orderid;
		$query=_query($select,"class->paywm");
		$row_order=$query->fetch_assoc();
		return $row_order;
	}
	
	function back_wm ($row_order) {
		$wmxi=new WMXI($this->WebmoneyCA_path, DOC_ENCODING);
		$wmxi->Classic($this->shopwmid2, $this->key_pass, $this->key_path);
		$parser = new WMXIParser();	
		if ( !is_array($row_order) ) $row_order=$this->order_query($row_order);	
		$select="select LMI_SYS_TRANS_NO as tranid from payment where orderid=".$row_order['id'];
		$query=_query($select,"class->backwm");
		$row_payment=$query->fetch_assoc();
		$response = $wmxi->X14(
			$row_payment['tranid'],
			floatval($row_order['summin'])
		);
		
		$structure = $parser->Parse($response, DOC_ENCODING);
		$transformed = $parser->Reindex($structure, true);
		
		if ( $transformed['w3s.response']['retval']==0 ) {
			$update_string="Возвращены отправителю. Детали операции(ID: ".$transformed['w3s.response']['operation']['inwmtranid']
																.", Кошелек: ".$transformed['w3s.response']['operation']['pursedest']
																.", Сумма: ".$transformed['w3s.response']['operation']['amount']
																.", Дата: ".$transformed['w3s.response']['operation']['dateupd'];
		}else{
			$update_string="Ошибка № ".$transformed['w3s.response']['retval'].
							", описание: ".$transformed['w3s.response']['retdesc'];
		}
		$update="update orders set status='".$update_string."', ordered=0 where id=".$row_order['id'];
		$query=_query($update, "class->backwm");
		$update="update payment set ordered=0 where orderid=".$row_order['id'];
		$query=_query($update, "class->backwm");
	}
	
	function pay_order($row_order){
		if ( substr($row_order['currout'],0,2)=="WM" ) {
			$this->pay_wm($row_order);
		}
		
	}
	function pay_wm ($row_order) {
		//echo 1;
		$wmxi=new WMXI($this->WebmoneyCA_path, DOC_ENCODING);
		$wmxi->Classic($this->shopwmid2, $this->key_pass, $this->key_path);
		$parser = new WMXIParser();	
		if ( !is_array($row_order) ) $row_order=$this->order_query($row_order);	
			$select="select id from payment_out where payment=".$row_order['id'];
			$query=_query($select, "result.php 42");
			if ( $query->num_rows==0 ) {
				$select="insert into payment_out (payment) values (".$row_order['id'].")";
				$query=_query($select, "result.php 41");
			}		
			
			$client = "SELECT clients.name, clients.id, clients.purse_z, clients.purse_r, 
			clients.purse_e, clients.purse_u, clients.email, clients.wmid FROM clients WHERE clid='".$row_order['clid']."';";
			$row_client=_query($client, 18);
			$row_client=$row_client->fetch_assoc();
			//print_r($row_order);
				$summ = $row_order['summout']+$row_order['discammount'];
				$protection_period=0;
				$protection_code="";
				if ( isset($_POST['protection']) ) {
					$protection_period=1;
					$protection_code=strtolower(substr(md5(uniqid(microtime(), 1)).getmypid(),1,8));
				}
				
				//echo "all is ok";
				
				if ( substr($row_order['currin'],0,2)=="WM" ) {
					$select="select LMI_PAYER_PURSE as purse from payment where orderid=".$row_order['id'];
					$query=_query($select, "pay_wm->12");
					$row_payment=$query->fetch_assoc();
					$from=$row_payment['purse'];
				}else{
					# проверка соотвествия указанного в заявке вмид с тем на который будет инициирован платеж
					$response = $wmxi->X8("",$this->purse_out($row_order));
					$structure = $parser->Parse($response, DOC_ENCODING);
					$transformed = $parser->Reindex($structure, true);
					
					$to_wmid=$transformed['w3s.response']['testwmpurse']['wmid'];
					//maildebugger(print_r($row_order,1)); 
					if ( isset($row_order['wmid']) && $row_order['wmid']!=$to_wmid ) {
						$update="update payment_out set wmid='".$to_wmid."' where payment=".$row_order['id'];
						$query=_query($update,"class->paywm");
						$update="update orders set status='WMID-плательщик ".$to_wmid.
						" отличается от WMID, указанного в заявке (".$row_order['wmid'].")', retval=5 where id=".$row_order['id'];
						//$query=_query($update,"class->paywm");
						//return;
					}
							
					$from=$row_order['currintype'];
				}
				$comment="";
				if ( substr($row_order['currin'],0,3)=="P24" ) {
					$comment=$row_order['fname']." ".$row_order['iname'].", Счет ".$row_order['account']. " КБ Приватбанк";
				}
				$retid= $row_order['retid']=="" ? "" : " [rd: ".$row_order['retid']."]";
				$select="select percent from orders_ecomode where oid=".$row_order['id'];
				$query=_query($select,"");
				if ( $query->num_rows==0 ) {
					$economy=1;
				}else{
					$row=$query->fetch_assoc();
					$economy=1+$row['percent'];
				}
				$summ=round($summ*$economy,2);
				
				$response = $wmxi->X2(
					$row_order['id'],							 
					$this->shop_wm_purse[substr($row_order['currout'],2,1)],
					$this->purse_out($row_order),
					floatval($summ),
					$protection_period,
					$protection_code,
					$row_order['id'].'. '.
					$row_order['summin'].' '.$from.' -> '.
					($row_order['summout']+$row_order['discammount']).' '.$row_order['currout'].' ('.$from.' - '
					.$this->purse_out($row_order).') '.$comment.'. Obmenov.com'.$retid,  
					0,
					1
				);

				$structure = $parser->Parse($response, DOC_ENCODING);
				$transformed = $parser->Reindex($structure, true);
				//maildebugger(print_r($transformed,1).print_r($structure,1));

		if ( !isset($transformed["w3s.response"]["retval"]) ) {
				$update = "UPDATE payment_out SET retval=1000, retdesc='Не получен ответ от Webmoney' WHERE payment=".$row_order['id'];
				//echo $update;
				_query ($update, "payment update");
		}else {
			if ( intval($transformed["w3s.response"]["retval"]) != 0 ){ // платеж не прошел
				$update = "UPDATE payment_out SET retval=".
				htmlspecialchars(@$transformed["w3s.response"]["retval"], ENT_QUOTES).
				", retdesc='".
				htmlspecialchars(@$transformed["w3s.response"]["retdesc"], ENT_QUOTES).
				"' WHERE payment=".$row_order['id'];
				//echo $update;
				_query ($update, "payment update");
				switch ( intval($transformed["w3s.response"]["retval"]) ) {
					case 7: $retdesc = "Неправильно указан номер кошелька."; break;
					case 30: $retdesc = "Кошелек не поддерживает прямой перевод"; break;
					case 35: $retdesc = "Вы не авторизовали WMID нашего сервиса для выполнения перевода"; break;
					case 58: $retdesc = "Превышен лимит средств на кошельках получателя, который использует системы Телепат(C 01.06.2008 в системе Телепат вводятся финансовые ограничения для владельцев аттестата ниже начального). Более подробную информацию можно прочесть по следующей ссылке: http://wiki.webmoney.ru/wiki/show/Finansovye_ogranicheniya_WebMoney_Keeper_Mobile"; break;
					case 17: $retdesc="Недостаточно резерва для выполнения операции. Очередная попытка отправки в ".date("i:H d:m:Y"); return;
					case 14: return;
					case -14: $retdesc="sig"; return;
					case 103: return;
					case 110: $redesc = "Нет доступа к интерфейсу, либо сервер платежей не доступен"; break;
					default: $retdesc = "Другая ошибка"; break;
				}
				send_mail($row_order['email'], '
Уважаемый клиент!

Обмен не может быть завершен по вашей заявке №: '.$row_order['id'].'.
Причина: '.$retdesc.', Номер ошибки: '.intval($transformed["w3s.response"]["retval"]).'
Данные вашей заявки: '
				.$row_order['summin'].' '.$row_order['currin'].' -> '.
				($row_order['summout']+$row_order['discammount']).' '.$row_order['currout'].' ('.$this->purse_out($row_order).').
Пожалуйста, свяжитесь со службой поддержки.


---
С уважением, Обменов.ком', "Обменов.ком :: Заявка № ".$row_order['id'],$this->shop_email , $this->shop_name) ;
				
				send_mail("support@obmenov.com", '
Обмен не может быть завершен по заявке №: '.$row_order['id'].'.
Причина: '.$retdesc.", ".$transformed["w3s.response"]["retdesc"].', Номер ошибки: '.intval($transformed["w3s.response"]["retval"]).'

Данные заявки: '
				.$row_order['summin'].' '.$row_order['currin'].' -> '.
				($row_order['summout']+$row_order['discammount']).' '.$row_order['currout'].' ('.$this->purse_out($row_order).').', 
				"Обменов.ком :: Заявка № ".$row_order['id']." не погашена",$GLOBALS['shop_email'] , $GLOBALS['shop_name']) ;
				$header='MIME-Version: 1.0' . "\n";
				$header .='Content-type: text/html; charset=koi8-r' . "\n";
				$header .='From: Obmenov.com <support@obmenov.com>' . "\n" .
    			'Reply-To: Obmenov.com <support@obmenov.com>' . "\n";
				$header .='X-Mailer: PHP/' . phpversion();
				@mail("380682587483@sms.beeline.ua","!".$row_order['id']."->".
								intval($transformed["w3s.response"]["retval"]). "->".
								$transformed["w3s.response"]["retdesc"],'',$header);
				$update="update orders set droped=1 where id=".$row_order['id'];
				$query=_query($update,"");
				
			}else { // платеж прошел
				$this->partner_bonus($row_order);
				$update="UPDATE payment SET payment.canceled=1	WHERE payment.orderid=".$row_order['id'];
				$updateSQL=_query($update, "ad update orders 2");
				$insert = "UPDATE payment_out set purse='".$this->purse_out($row_order)."',
													payer='".$this->shop_wm_purse[substr($row_order['currout'],2,1)]."',
													  summ=$summ, 
													  protection='$protection_code', 
													  clid=".$row_client['id']." where payment=".$row_order['id'];
				_query ($insert, "x9");	
				// сообщение на ВМ-идентификатор с кодом протекции	
					if ( isset($_POST['protection']) ) {
						$response = $wmxi->X6(
							htmlspecialchars($_POST['LMI_PAYER_WM']),                            
							"Заявка №: ".$row_order['id'],                           
							trim("\nЗаявка №: ".$row_order['id']."\nКод протекции сделки: ".$protection_code."\n_______________________________________________________________\nДанное сообщение сформировано автоматически и не требует ответа"));
						$structure = $parser->Parse($response, DOC_ENCODING);
						$transformed = $parser->Reindex($structure, true);
						if ( @$transformed["w3s.response"]["retval"]!=0 ){
							_error("Ошибка интерфейса Х6: Код: ".@$transformed["w3s.response"]["retval"].
								" Описание: ".@$transformed["w3s.response"]["retdesc"] , "result.php X6", "");	
						}
				
					}
					if ( $protection_period==0 ) {
						$protection_code = "Перевод без кода протекции";
					}
				

				 	send_mail($row_order['email'], 
'Уважаемый клиент!
Уведомляем вас об изменении статуса вашей заявки №: '.$row_order['id'].'.
Ваш заказ получил статут "Обмен завершен".
'.$row_order['summin'].' '.$row_order['currin'].' -> '.
				($row_order['summout']+$row_order['discammount']).' '.$row_order['currout'].' ('.$this->purse_out($row_order).').

-------------------------------------------------
Код протекции: '.$protection_code.'
-------------------------------------------------

---

Мы будем благодарны, если Вы оставите положительный отзыв о нашем сервисе
http://arbitrage.webmoney.ru/asp/claims.asp?wmid=219391095990
http://advisor.wmtransfer.com/FeedBackList.aspx?url=obmenov.com

---
С уважением, Обменов.ком', "Обменов.ком :: Заявка №".$row_order['id']." погашена",$GLOBALS['shop_email'] , $GLOBALS['shop_name']) ;
					//$this->send_sms($row_order);
					
			} // платеж прошел
	
		} // !isset(w3s.response)
	
	}
	function pay_pm ($row_order) {
		if ( !is_array($row_order) ) $row_order=$this->order_query($row_order);	
		$select="select id from payment_out where payment=".$row_order['id'];
		$query=_query($select, "result.php 42");
		if ( $query->num_rows==0 ) {
			$select="insert into payment_out (payment) values (".$row_order['id'].")";
			$query=_query($select, "result.php 41");
		}
		$row=$this->pm_history($row_order);
		$pay_exist=false;
		while (list($k,$v)=each($row)) {
			if ( $v['payment_id']==$row_order['id'] ) {
				if ( substr($row_order['currin'],0,2)==substr($row_order['currin'],0,2) ) {
					echo "step1";
					$row_order['id']=$row_order['id']."_";
					$row2=$this->pm_history($row_order);
					print_r ($row2);
					while (list($k2,$v2)=each($row2)) {
						
						if ( $v2['payment_id']==$row_order['id'] )$pay_exist=true;
					}
				}else{			
					$pay_exist=true;
				}
			}
		}
		
		
		if ( !$pay_exist ) {
			//echo"платеж не существует";die();
			$select="select percent from orders_ecomode where oid=".(int)$row_order['id'];
			$query=_query($select,"");
			$summ=$row_order['summout']+$row_order['discammount'];
			if ( $query->num_rows==0 ) {
				$economy=1;
			}else{
				$row=$query->fetch_assoc();
				$economy=1+$row['percent'];
			}
			$summ=round($summ*$economy,2);
			$out=$this->pm_curl('https://perfectmoney.is/acct/confirm.asp?AccountID='.$GLOBALS['pm_id'].
										'&PassPhrase='.$GLOBALS['pm_phrase'].
										'&Payer_Account='.$GLOBALS['pm_'.strtolower(substr($row_order['currout'],2,3))].
										'&Payee_Account='.$row_order['purse_other'].
										'&Amount='.$summ.
										'&PAYMENT_ID='.$row_order['id']);
			$row_order['id']=(int)$row_order['id'];
			//maildebugger($out);
			if ( isset($out['ERROR']) ) {
				$insert = "UPDATE payment_out set  
							retdesc='".$out['ERROR']."' where payment=".intval($row_order['id']);
				
							
			}else{
				
				$this->partner_bonus($row_order);
				$insert = "UPDATE payment set payment.canceled=1
							 where orderid=".(int)$row_order['id'];
				$query=_query($insert,"");
				$insert = "UPDATE payment_out set summ=".$out['PAYMENT_AMOUNT'].", purse='".$out['Payee_Account']."', 
							retdesc='".$out['PAYMENT_BATCH_NUM']."', 
							clid='".$row_order['clid']."' where payment=".intval($row_order['id']);
			}
				print_r($row_order);
				_query ($insert, "x9");	

		
	}else{ // повторный платеж.
		//$insert = "UPDATE payment_out set  
		//					retdesc='Payment #".$row_order['id']." already exists' where payment=".intval($row_order['id']);
		//_query ($insert, "x9");
		$insert = "UPDATE payment set payment.canceled=1
							 where orderid=".intval($row_order['id']);
		$query=_query($insert,"");
		
	}
		
	}
	function pm_history ($row_order){
		$select=" SELECT 
					date_format(date- interval 1 day,'%Y') as st_y, 
					date_format(date- interval 1 day,'%c') as st_m, 
					date_format(date- interval 1 day,'%e') as st_d,
					date_format(date+ interval 1 day,'%Y') as end_y, 
					date_format(date+ interval 1 day,'%c') as end_m, 
					date_format(date+ interval 1 day,'%e') as end_d
 				FROM `orders` WHERE id=".(int)$row_order['id'];
		$row=_query($select,"");
		$row=$row->fetch_assoc();
		$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, 'https://perfectmoney.is/acct/historycsv.asp?AccountID='.$GLOBALS['pm_id'].
										'&PassPhrase='.$GLOBALS['pm_phrase'].
										'&startmonth='.$row['st_m'].
										'&startday='.$row['st_d'].
										'&startyear='.$row['end_y'].
										'&endmonth='.$row['end_m'].
										'&endday='.$row['end_d'].
										'&endyear='.$row['end_y'].
										'&payment_id='.$row_order['id']
										);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CAINFO, $GLOBALS['pm_crt_path']); 
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    	$chk_trans = curl_exec($ch);
		curl_close($ch);
		$ar=array();
		$lines=array();
		$lines=explode("\n",$chk_trans);
		if($lines[0]!='Time,Type,Batch,Currency,Amount,Fee,Payer Account,Payee Account,Payment ID,Memo'){

		}else{

   // do parsing
   			
   			$n=count($lines);
   			for($i=1; $i<$n; $i++){
				$item=explode(",", $lines[$i], 10);
    	  		if(count($item)!=10) continue; // line is invalid - pass to next one
    			  $item_named['Time']=$item[0];
    			  $item_named['Type']=$item[1];
      			$item_named['Batch']=$item[2];
      			$item_named['Currency']=$item[3];
      			$item_named['Amount']=$item[4];
      			$item_named['Fee']=$item[5];
      			$item_named['Payer Account']=$item[6];
      			$item_named['Payee Account']=$item[7];
      			$item_named['payment_id']=$item[8];
	  			$item_named['Memo']=$item[9];
	   			array_push($ar, $item_named);
   			}

		}
		return($ar);
	}
		
	function pm_curl ($url) {
		$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CAINFO, $GLOBALS['pm_crt_path']); 
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    	$out = curl_exec($ch);
		if (curl_errno($ch) != 0)
		{
		maildebugger("PerfectMoney api error CURL: CODE:".curl_error($ch)." INFO:".curl_getinfo($ch, CURLINFO_HTTP_CODE));
		}
		curl_close($ch);
		if(!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $out, $result, PREG_SET_ORDER)){
   			echo 'Ivalid output';
   			exit;
		}
		$insert="insert into xml ( `iface`, `query`, `answer` ) values ('PM $url', '".mysqli_real_escape_string($GLOBALS['ma'],$out)."', '".print_r($result)."')";
		$query=_query2($insert, "pm.xml");
		
		$ar="";
		foreach($result as $item){
		   $key=$item[1];
		   $ar[$key]=$item[2];
		}
		return($ar);
		
	}
	
	function pay_lr($row_order) {
		require_once($GLOBALS['serverroot']."siti/lib.lr.php");
		if ( !is_array($row_order) ) $row_order=$this->order_query($row_order);	
		$lr=new lrWorker($GLOBALS['lr_id']);
		
		//$row_order['id']=82450;
		
		$hist=$this->lr_pay_exist($row_order);
		$Currency=($row_order['currout']=="LRUSD"?"usd":"");
		$Currency=($row_order['currout']=="LREUR"?"euro":$Currency);
		//maildebugger($hist['RECEIPT']['0']['CURRENCYID']."==".$currency." ".
		//												print_r($hist,1)." ==== ".$row_order['id']);
		if ( (!isset($hist['RECEIPT']) && isset($hist['PAGER']['PAGENUMBER']) && $hist['PAGER']['PAGENUMBER']==0) ||
			 (isset($hist['RECEIPT']) && isset($hist['RECEIPT']['0']['CURRENCYID']) 
									&& $hist['RECEIPT']['0']['CURRENCYID']!=$Currency ) ) {
				
				$reqID=microtime()*10000000;
				$Payee=$row_order['purse_other'];
				$select="select percent from orders_ecomode where oid=".$row_order['id'];
				$query=_query($select,"");
				$summ=$row_order['summout']+$row_order['discammount'];
				if ( $query->num_rows==0 ) {
					$economy=1;
				}else{
					$row=$query->fetch_assoc();
					$economy=1+$row['percent'];
				}
				$Amount=round($summ*$economy,2);
				$Memo='for contract #'.$row_order['id'];
				/*$Memo='Exchange Order #'.$row_order['id'].'. '.$row_order['summin'].' '.$row_order['currin'].' - '.
								($row_order['summout']+$row_order['discammount']).' '.$row_order['currout'];*/
				$TransferId=$row_order['id'];
				$trans=$lr->Transfer($reqID,$Payee,$Currency,$Amount,$Memo,$TransferId);
				print_r($trans);
				if ( isset($trans['RECEIPT']) ) {
					$update="update payment set canceled=1 where orderid=".$TransferId;
					$query=_query($update,"");
					$select="select id from payment_out where payment=".$TransferId;
					$query=_query($select,"");
					if ( $query->num_rows>0 ) {
						$update="update payment_out set
							retdesc='".$trans['RECEIPT']['RECEIPTID']."',
							purse='".$trans['RECEIPT']['PAYEENAME']." ".$trans['RECEIPT']['PAYEE']."',
							summ='".$trans['RECEIPT']['AMOUNT']."'
							where payment=".$trans['RECEIPT']['TRANSFERID'];
																	   
						$query=_query($update,"");
					}else{
						$insert="insert into payment_out (retdesc, purse, summ, payment) values ('".
							$trans['RECEIPT']['RECEIPTID']."','".
							$trans['RECEIPT']['PAYEENAME']." ".$trans['RECEIPT']['PAYEE']."','".
							$trans['RECEIPT']['AMOUNT']."','".
							$trans['RECEIPT']['TRANSFERID']."')";
						$query=_query($insert,"");
					}
				}
		}else {
			echo "nohist";
			print_r($hist);
			
		}
		
		
	}
	function lr_pay_exist ($row_order) {
		if ( !is_array($row_order) ) $row_order=$this->order_query($row_order);	
		require_once($GLOBALS['serverroot']."siti/lib.lr.php");
		$lr=new lrWorker($GLOBALS['lr_id']);
		$select=" SELECT 
					date_format(date- interval 1 day,'%Y') as st_y, 
					date_format(date- interval 1 day,'%m') as st_m, 
					date_format(date- interval 1 day,'%d') as st_d,
					date_format(date+ interval 1 day,'%Y') as end_y, 
					date_format(date+ interval 1 day,'%m') as end_m, 
					date_format(date+ interval 1 day,'%d') as end_d
 				FROM `orders` WHERE id=".$row_order['id'];
		$row=_query($select,"");
		$row=$row->fetch_assoc();
		
		$reqID=microtime()*10000000;
		$TransferId=$row_order['id'];
		//формат YYYY-DD-MM HH:mm:SS
		$startdate=$row['st_y'].'-'.$row['st_m'].'-'.$row['st_d'].' 00:00:00';
		$enddate=$row['end_y'].'-'.$row['end_m'].'-'.$row['end_d'].' 00:00:00';
		$hist=$lr->History($reqID,$startdate,$enddate,'','','',$TransferId);
		return $hist;
		
	}
	
	function get_pay_comment($row_order) {
		if ( substr($row_order['currin'],0,2)=="WM" ) {
			$select="select LMI_PAYER_PURSE as purse from payment where orderid=".$row_order['id'];
			$query=_query($select, "pay_wm->12");
			$row_payment=$query->fetch_assoc();
			$from=$row_payment['purse'];
		}else{
			$from=$row_order['currintype'];
		}
		$retid= $row_order['retid']=="" ? "" : " [rd: ".$row_order['retid']."]";
		$comment="";
		if ( substr($row_order['currin'],0,3)=="P24" || substr($row_order['currout'],0,3)=="P24" ) {
			$comment=$row_order['fname']." ".$row_order['iname'].", Счет ".$row_order['account']. " КБ Приватбанк";
		}
		return 'Обмен по заявке №'.$row_order['id'].'. '.
		$row_order['summin'].' '.$from.' -> '.
		($row_order['summout']+$row_order['discammount']).' '.$row_order['currout'].' ('.$from.' - '
		.$this->purse_out($row_order).') '.$comment.'. Obmenov.com'.$retid;
		
	}
	function email_pay_recieved($row_order) {
		
	if ( in_array($row_order['currin'],$GLOBALS['rbanks']) || in_array($row_order['currout'],$GLOBALS['rbanks']) ) {
		/*mail("vniknis@gmail.com",'',
		'Заявка №'.$row_order['id'].', '.$row_order['summin'].' '.$row_order['extnamein'].' -> '.
						($row_order['summout']+$row_order['discammount']).' '.$row_order['extnameout'].', ФИО: '.$row_order['fname'].' '.$row_order['iname'].', '.$row_order['oname'].', Счет: '.$row_order['account'].', E-mail: '.$row_order['email'].',  https://obmenov.com/cabinet.php?clid='.$row_order['clid'].'&oid='.$row_order['id'], 
				"Оплачена заявка на обмен ".$row_order['id']) ;
	*/
	mail("vniknis@gmail.com",'',
		'https://obmenov.com/partner/dsufgyhskdfh.php?clid='.$row_order['clid'].'&oid='.$row_order['id'].' \r\n <br> 
'.
		'https://obmenov.com/cabinet.php?clid='.$row_order['clid'].'&oid='.$row_order['id'], 
				"Order ".$row_order['id']." was payed") ;
	}	
	/*mail($this->shop_email,'',
		'Заявка №'.$row_order['id'].'
'.$row_order['summin'].' '.$row_order['extnamein'].' -> '.
						($row_order['summout']+$row_order['discammount']).' '.$row_order['extnameout'].'
Скидка: '.$row_order['discammount'].' '.$row_order['extnameout'].' 
Фамилия: '.$row_order['fname'].'
Имя: '.$row_order['iname'].'
Счет: '.$row_order['account'].'
E-mail: '.$row_order['email'].'
Подробная информация по заявке: https://obmenov.com/cabinet.php?clid='.$row_order['clid'].'&oid='.$row_order['id'], 
				"Оплачена заявка на обмен ".$row_order['id']) ;*/
			
			return	send_mail($row_order['email'], 'Уважаемый клиент!
Уведомляем вас об изменении статуса вашей заявки №: '.$row_order['id'].'
Ваша заявка получила статус "Оплата принята".

Постоянный адрес с информацией о вашей заявке:
https://obmenov.com/cabinet.php?clid='.$row_order['clid'].'&oid='.$row_order['id'].'
---
С уважением, Обменов.ком', "Обменов.ком :: изменен статус заявки № ".$row_order['id'],$GLOBALS['shop_email'] , $GLOBALS['shop_name']) ;
			
	
	}
	function purse_out($row_order){
		if ( substr($row_order['currout'],0,2)=="WM" ) {
			return substr($row_order['currout'],2,1).$row_order["purse_".strtolower(substr($row_order['currout'],2,1))];
		}elseif ( $row_order['currout']=='KS' ){
			return $row_order['phone'];
		}else{return "";}
		
	}
	function mail_needcheck($row_order, $type) {
	if ( $type==0 ) {
		$body='Уважаемый клиент!

Уведомляем вас об успешной проверке реквизитов вашей карты(счета).
Данные, прошедшие проверку:
Карта №: '.$row_order['account'].'
Фамилия: '.$row_order['fname'].'
Имя: '.$row_order['iname'].'
---
Теперь Вы можете оформлять заявки в рамках автоматического ввода/вывода средств по указанным реквизитам.
Произвести оплату по оформленной заявке Вы можете по этой ссылке:
https://obmenov.com/cabinet.php?clid='.$row_order['clid'].'&oid='.$row_order['id'].'
Оплату по указанной ссылке можно произвести в течение 2-х часов после получения этого письма.
По прошествии указанного времени нужно оформить новую заявку.

---
С уважением, Обменов.ком';
		$subject="Обменов.ком :: Успешная проверка реквизитов ";
	}else{
		$body='Уважаемый клиент!

Реквизиты проверку не прошли. Проверьте правильность указания реквизитов.
Данные, которые проверялись:
Карта №: '.$row_order['account'].'
Фамилия: '.$row_order['fname'].'
Имя: '.$row_order['iname'].'

Возможно ваш счет не персонифицирован и требует дополнительной верификации. 
Для выполнения верификации отправьте скрин из Приват24, где видно этот номер счета и 
любой другой персонифицированый, по адресу support@obmenov.com. 
Скрин должен быть в формате PNG или GIF без сжатия.

---
С уважением, Обменов.ком';
		$subject="Обменов.ком :: Проверка реквизитов не пройдена";
		
	}
	
	
	send_mail($row_order['email'], $body, $subject,$GLOBALS['shop_email'] , $GLOBALS['shop_name']) ;
	send_mail("support@obmenov.com", $body, $subject,$GLOBALS['shop_email'] , $GLOBALS['shop_name']) ;
	}
	
	function send_sms($row_order){
		
	}
	function check_order_summ($row_order){
		if ( !is_array($row_order) ) $row_order=$this->order_query($row_order);
//==================================		
		$predel[1]=99.99;
		$predel[2]=999.99;
		$predel[3]=4999.99;
		$row_order['sumOut']=$row_order['summout']+$row_order['discammount'];
		if ( $row_order['authorized'] ) {
			$select="select id from clients where clid='".$row_order['clid']."'";
			$query=_query($select,"index.php 55");
			$clrow=$query->fetch_assoc();
			$clientid_predel=$clrow['id'];
		}else{
			$clientid_predel=0;
		}	
		if ( isset($money[$row_order['currin']][$row_order['currout']]['addon_value']) 
									&& $money[$row_order['currin']][$row_order['currout']]['addon_value']==1 ) {
			$addon_value=0;
		}else { 
			$addon_value=(($row_order['disc']-1)/100);
		}
		$predel1=0.001;
		$predel2=0.002;	
		$predel3=0.003;
	// первый предел
		$select="select value from addon_predel where currname1='".$row_order['currin']."' 
			and currname2='".$row_order['currout']."'
			AND type=1 AND clientid=0 order by date desc";
		$query=_query($select, "specification.php predel_addon 1");
		if ( $query->num_rows != 0 ) {
			$predel1=$query->fetch_assoc();
			$predel1=$predel1['value']/100;
		}
		if ( $clientid_predel!=0 ) {
			$select="select value from addon_predel where currname1='".$row_order['currin']."' 
			and currname2='".$row_order['currout']."'
			AND type=1 AND clientid=".$clientid_predel." order by date desc";
			$query=_query($select, "specification.php predel_addon 1");
			if ( $query->num_rows!=0 ) {
				$predel1=$query->fetch_assoc();
				$predel1=$predel1['value']/100;	
			}
		} // конец первый предел
	
	
		// второй предел
		$select="select value from addon_predel where currname1='".$row_order['currin']."' 
			and currname2='".$row_order['currout']."'
			AND type=2 AND clientid=0 order by date desc";
		$query=_query($select, "specification.php predel_addon 1");
		if ( $query->num_rows != 0 ) {
			$predel2=$query->fetch_assoc();
			$predel2=$predel2['value']/100;
		}
		if ( $clientid_predel!=0 ) {
			$select="select value from addon_predel where currname1='".$row_order['currin']."'
				and currname2='".$row_order['currout']."'
				AND type=2 AND clientid=".$clientid_predel." order by date desc";
			$query=_query($select, "specification.php predel_addon 2");
			if ( $query->num_rows!=0 ) {
				$predel2=$query->fetch_assoc();
				$predel2=$predel2['value']/100;	
			}
		} // конец второй предел	
	
	
	
		// третий предел
		$select="select value from addon_predel where currname1='".$row_order['currin']."' 
			and currname2='".$row_order['currout']."'
			AND type=3 AND clientid=0 order by date desc";
		$query=_query($select, "specification.php predel_addon 1");
		if ( $query->num_rows != 0 ) {
			$predel3=$query->fetch_assoc();
			$predel3=$predel3['value']/100;
		}
		if ( $clientid_predel!=0 ) {
			$select="select value from addon_predel where currname1='".$row_order['currin']."' 
				and currname2='".$row_order['currout']."'
				AND type=3 AND clientid=".$clientid_predel." order by date desc";
			$query=_query($select, "specification.php predel_addon 3");
			if ( $query->num_rows!=0 ) {
				$predel3=$query->fetch_assoc();
				$predel3=$predel3['value']/100;	
			}
		} // конец третий предел
		$GLOBALS['closed_exchange']=1;
		$money=commiss();
		$courses=$GLOBALS['courses'];
		foreach ($money as $row1){
			foreach ($row1 as $row2){

				if ( $row2['curr1']==$row_order['currin'] && $row2['curr2']==$row_order['currout'] ){
 					$check=$row_order['summin']*$courses[$row_order['currin']]["USD"];//$row2['value'];
				
					if ($check<$predel[1]) {$row_discountammount=0;
					if ($check*1.001>$predel[1]){$row_discountammount=$predel1+0.0001;}}
					if ($check>$predel[1] && $check<$predel[2]) {$row_discountammount=$predel1;}
					if ($check>$predel[2] && $check<$predel[3]) {$row_discountammount=$predel2;}
					if ($check>$predel[3]) {$row_discountammount=$predel3;}
					$tuUSD=round($check,2);
					$result[1]=round($row_order['summin']*$courses[$row_order['currin']][$row_order['currout']]/($row2['value']-$row_discountammount)*(1+$addon_value),2);
				}
			}
		}
	if ( substr($row_order['currin'],0,3)=="MCV" ) { 
		$percent_for_courses=0.02; 
	}
	if ( $row_order['currout']=="KS" ) { 
		$percent_for_courses=0.031; 
	}
	$percent_for_courses=$GLOBALS['percent_for_courses'];
	if ( $result[1] > round($row_order['sumOut'],2)*(1+$percent_for_courses) || 
			$result[1] < round($row_order['sumOut'],2)/(1+$percent_for_courses) ) {
		$result[2]=$row_order['sumOut'];
		$result[3]="Ошибка проверки суммы обмена: ".$result[1]. ' = '.htmlspecialchars(round($row_order['sumOut'],2));
	}else{
		$result[1]=$row_order['sumOut'];
		$result[2]=$row_order['sumOut'];
		
	}
	return $result;
		
//==================================		
	}
}

class user {
	function auth ($realm) {
		if (!isset($_SESSION)) {
  session_cache_expire(5);session_start();
}
$MM_authorizedUsers = $realm;
$MM_donotCheckaccess = "false";
// *** Restrict Access To Page: Grant or deny access to this page

$MM_restrictGoTo = "reg.php";
if (isset($_SESSION['MM_Username']) && $this->isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  return 1;
  header("Location: ". $MM_restrictGoTo); 
  exit;
} else {
	return 0;
}
	// end user validation
	}
	function bad_auth(){//{$folder="adref") {
		//if ( $folder="") {
			require_once($GLOBALS["serverroot"]."adrefzw/top.php");
			echo "<br />Не хватает прав доступа для просмотра. Для переавторизации нажмите <a href='reg.php?doLogout=true'>эту ссылку</a>";
		die();
		//}
	}
	function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = false; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = explode(",", $strUsers); 
    $arrGroups = explode(",", $strGroups);
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username.
	//if (in_array($UserGroup, $arrGroups)) {
 	if ( strlen(strstr($strGroups,$UserGroup))==0 ) {
	}else{
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
	  echo "22:";		
      $isValid = true; 
    } 
  }
  return $isValid; 
}
function regen_pass ($email, $partner=false) {
		$select = "SELECT clients.passmd5, clients.name, clients.email, clients.clid FROM clients WHERE clients.email='".$email."'";
		$query=_query($select, "register.php 3");
		$row = $query->fetch_assoc();	
		$numrow=$query->num_rows;
		if ($numrow>0){
			$rnd = substr(md5(uniqid(microtime(), 1)).getmypid(),1,8);
			send_mail($row['email'], "Здравствуйте,

Ваш новый пароль в системе Обменов.ком - ".$rnd."

Изменить его на более удобный Вы можете в своем личном кабинете https://obmenov.com/cabinet.php


--
С уважением, ".$GLOBALS['shop_email'], 'Обменов.ком: Запрос пароля', $GLOBALS['shop_email'], $GLOBALS['shop_name']);
			$mess='Новый пароль отправлен по указанному вами адресу электронной почты.';
			$query="UPDATE clients SET passmd5='".md5(md5($rnd))."' WHERE clid='".$row['clid']."'";
			$update=_query ($query, "register.php 44");
		}else {	
			$mess='Пользователь с указанным e-mail не найден.';
		}
		return $mess;
}
	
}

class shop {
	function get_price ($unit,$p) {
		$courses=$GLOBALS['courses'];
		
		if ( $unit=="WMU" ) {
            $units['WMZ']=(round($p*$courses['WMU']['WMZ']*$GLOBALS['percent_addon'],2));
			$units['WMU']=$p;
            $units['WMR']=(round($p*$courses['WMU']['WMR']*$GLOBALS['percent_addon'],2));
			$units['WME']=(round($p*$courses['WMU']['WME']*$GLOBALS['percent_addon'],2));
			//echo '<input type="radio" style="border:0" name="unit" value="MCVUSD">'.(round($item['price']*$courses['WMU']['WMZ'],2)). 'USD';
			//echo '<input type="radio" style="border:0" name="unit" value="MCVUAH">'.(round($item['price'],2)). 'UAH';
		}
		if ( $unit=="WMZ" ) {
			$units['WMZ']=$p;
            $units['WMU']=(round($p*$courses['WMZ']['WMU'],2));
			$units['WMR']=(round($p*$courses['WMZ']['WMR'],2));
			$units['WME']=(round($p*$courses['WMZ']['WME'],2));
            //echo '<input type="radio" style="border:0" name="unit" value="MCVUSD">'.(round($item['price'],2)). 'USD';
			//echo '<input type="radio" style="border:0" name="unit" value="MCVUAH">'.(round($item['price']*$courses['WMZ']['WMU'],2)). 'UAH';
		}
		if ( $unit=="WMR" ) {
			$units['WMZ']=(round($p*$courses['WMR']['WMZ'],2));
            $units['WMU']=(round($p*$courses['WMR']['WMU'],2));
			$units['WMR']=$p;
			$units['WME']=(round($p*$courses['WMR']['WME'],2));
		}
		return $units;
	}
	function discount() {
		$discount['total']=1;
		$discount['exchange_discount']=1;
		$clid_num=0;
		if ( isset($_SESSION['authorized']) ) {
		# скидка по припейду
			if ( isset($_SESSION['clid_num']) ) {
				$clid_num=$_SESSION['clid_num'];
				//maildebugger($clid_num." case1");
			}else{
				$select="select id from clients where clid='".$GLOBALS['clid']."'";
				//_error($select);
				$query=_query($select, "shop->dsicount");
				$row=$query->fetch_assoc();
				$clid_num=$row['id'];
				//maildebugger($clid_num." case2");
			}
			$select="SELECT sum(disc_amount) as total
					FROM prepaid_payment
					WHERE prepaid_payment.clientid =".$clid_num."
					and authorized=1
					and prepaid_payment.state='G'";
			$query=_query($select,"shop->discount");
			$row=$query->fetch_assoc();
			if ( !isset($row['total']) || $row['total']==0 ) {
				$row['total']=0;
			}
			//maildebugger($select);
			$discount['total_prepaid']=$row['total'];
			$select="select * from prepaid_discount WHERE value <= ".$row['total']." 
						AND value_till > ".$row['total'];
			//maildebugger($select);
			$query=_query2($select, "shop->discount");
			$row=$query->fetch_assoc();
			$discount['prepaid_value_till']=$row['value_till'];
			$discount['prepaid_discount']=$row['disc'];
		
		
		
			#скидка по обменам
		
			$select="SELECT clients.id, clients.name, clients.nikname, clients.wmid, clients.email, clients.clid, clients.phone 
				FROM clients WHERE clients.nikname='".$_SESSION['AuthUsername']."';";
		
			$query = _query($select, 'cabinet.php 1');
			$num_row_client=$query->num_rows;
			$row_client=$query->fetch_assoc();
			$clid=$row_client['clid'];
			/*$select="select (SELECT SUM(attach) 
					FROM orders, payment 
					WHERE orders.ordered=1 
					AND orders.authorized=1
					AND payment.ordered=1
					AND payment.canceled=1
					AND orders.clid='".$clid."'
					AND orders.id=payment.orderid and left(orders.time,4)='2012')+ 
					(select sum(sum) from i where i.clid='".$clid."') as total;";*/
			$select="SELECT SUM(attach) as total
					FROM orders, payment 
					WHERE orders.ordered=1 
					AND orders.authorized=1
					AND payment.ordered=1
					AND payment.canceled=1
					AND orders.clid='".$clid."'
					AND orders.id=payment.orderid ";
			$query = _query($select, 'cabinet.php 4'); //_query($query_ordered, "index 2");
			$row_ordered=$query->fetch_assoc();
			$row_ordered['total']!=NULL ? $discount_value=$row_ordered['total'] : $discount_value=1;
			
			$query_disc="SELECT discount.disc, discount.descr, discount.value_till 
					FROM discount 
					WHERE discount.value <= ".$discount_value." 
					AND discount.value_till > ".$discount_value.";";
			$query=_query2($query_disc,'cabinet.php 5');
			$discount['total_exchange']=$discount_value;
			$numrow_discount=$query->num_rows;
			$exchange_discount = $query->fetch_assoc();
			$discount['exchange_value_till']=$exchange_discount['value_till'];
			if ( $numrow_discount==1 ) {$discount['exchange_discount']=$exchange_discount['disc'];}
			$discount['total']=$discount['prepaid_discount']+$discount['exchange_discount']-1;
			$discount['total'] = ($discount['total'] > 1.3) ? 1.3 : $discount['total'];
			
			
		}
		return $discount;
		
	}
	function partner_discount($item, $type=false) {
		# type=0 - возвращает процент скидки
		# type=1 - возвращает денежный эквивалент скидки
		$select="select * from item_name where id=".$item;
		$query=_query($select,"shop->partner_discount");
		$item=$query->fetch_assoc();
		$discount_percent = ($item['partner']==0) ? get_setting('prepaid_partner_def_value') : $item['partner'];
		if ( $type==0 ) {
			$discount=$discount_percent;
		}elseif ( $type==1 ) {
			$discount=$this->get_price($item['unit'],$item['price']);
			$discount=$discount['WMZ'];
			$discount=round($discount*($discount_percent-1),2);
			//$discount=$discount.' '.$discount_percent;
		}
		return $discount;
	}
	function value_discount($item, $type=false) {
		# type=0 - возвращает процент скидки
		# type=1 - возвращает денежный эквивалент скидки
		$select="select * from item_name where id=".$item;
		$query=_query($select,"shop->discount_value");
		$item=$query->fetch_assoc();
		$discount_percent = ($item['profit']==0) ? get_setting('prepaid_discount_value') : $item['profit'];
		if ( $type==0 ) {
			$discount=$discount_percent;
		}elseif ( $type==1 ) {
			$discount=$this->get_price($item['unit'],$item['price']);
			$discount=$discount['WMZ'];
			$discount=round($discount*($discount_percent-1),2);
			//$discount=$discount.' '.$discount_percent;
		}
		return $discount;
	}

}
class specification {
	function fields($row_order) {
		$fields['form_action']='specification_redirect.php';
		switch ($row_order['currin']){
			case "WMZ": case "WMU": case "WME": case "WMR" :
				$fields['section_wmid']=1;
				$fields['form_action']='https://merchant.webmoney.ru/lmi/payment.asp';
				$fields['purseType']=substr(htmlspecialchars($row_order['currin']),2,1);
				$fields['section_purse']=1;
				$fields['section_email']=1;
				$fields['purseType1']=$fields['purseType'];$fields['purseType2']="Purse";
				$fields['lmi_comment']="";
				$fields['section_wmcomment']=1;
				$fields['wmin']=1;
				if ($row_order['currout']=='KS' || $row_order['currout']=='INSTAFX'){$fields['section_fname']=1;$fields['section_iname']=1;}
				break;
			case "USD" : case "UAH" :
				$fields['form_action']='paycash.php';
				$fields['lmi_comment']="";
				$fields['section_phone']=1;
				$fields['section_email']=1;
				$fields['section_passport']=1;
				$fields['section_fname']=1;
				$fields['section_iname']=1;	
				break;
			case "PMUSD" : case "PMEUR" :
				$fields['form_action']='https://perfectmoney.is/api/step1.asp';
				$fields['lmi_comment']="";
				$fields['section_email']=1;
				break;
			case "LRUSD" : case "LREUR" :
				$fields['form_action']='https://sci.libertyreserve.com/';
				$fields['lmi_comment']="";
				$fields['section_email']=1;
				break;
			case "P24USD": case "P24UAH": case "P24EUR":
				$fields['economy_mode']=1;
				$fields['lmi_comment']="";
				$fields['section_fname']=1;
				$fields['section_iname']=1;
				$fields['economy_mode']=1;
				$fields['section_bank']=1;
				$fields['section_email']=1;
				$fields['form_action']='paybank.php';
				break;
			case "ACRUB": case 'SVBRUB': case 'RUSSTRUB': case 'TBRUB':
				$fields['section_fname']=1;
				$fields['section_iname']=1;
				$fields['section_oname']=1;
				$fields['section_email']=1;
				$fields['section_r_bank_account']=1;
				$fields['form_action']='payrbank.php';
				break;
			case "MCVUSD" : case "MCVRUR" : case "MCVUAH" : case "MCVEUR" :
				$fields['form_action']='https://liqpay.com/?do=clickNbuy';
				$fields['section_fname']=1;
				$fields['section_iname']=1;
				$fields['lmi_comment']="";
				$fields['section_comment_phone']=1;
				$fields['section_phone']=1;
				$fields['section_mastercard']=1;
				$fields['section_email']=1;
				break;
			case "LQUSD" : case "LQUAH" : case "LQRUB" : case "LQEUR" :
				$fields['form_action']='https://liqpay.com/?do=clickNbuy';
				$fields['lmi_comment']="";
				$fields['section_phone']=1;
				$fields['section_mastercard']=1;
				$fields['section_email']=1;
				break;
			case "SMS" :
				$fields['form_action']='http://bank.smscoin.com/bank/';
				$fields['lmi_comment']="SMS -> ".($row_order['summout']+$row_order['discammount'])." ";//.$row_order['curr2'];
				$fields['section_comment_phone']=1;
				$fields['section_phone']=1;
				$fields['section_email']=1;
				$fields['section_passport']=1;
				$fields['section_fname']=1;
				$fields['section_iname']=1;
				break;
			default:
				$fields[1]="";
		}
		switch ($row_order['currout']){
			case "WMZ": case "WMU": case "WME": case "WMR" :
				$fields['purseTypeOut']=substr(htmlspecialchars($row_order['currout']),2,1);
				$fields['section_wmid']=1;
				$fields['purseType1']=substr(htmlspecialchars($row_order['currout']),2,1);$fields['purseType2']="purseOut";
				if ( isset($fields['wmin']) ){
					unset($fields['section_purse']);
					$fields['purseType2']="purseOut";
					$fields['purseType1']=$fields['purseTypeOut'];
				}
				$fields['section_purseOut']=1;
				$fields['lmi_comment']="PurseOut";
				$fields['section_wmcomment']=1;
				if ( substr($row_order['currin'],0,2)=="LR" || 
									   substr($row_order['currin'],0,2)=="PM"  || 
									   substr($row_order['currin'],0,2)=="LQ" ||  substr($row_order['currin'],0,3)=="P24" ||
									   in_array($row_order['currin'],$GLOBALS['rbanks'])
									   ) {
					unset ($fields['section_wmid']);
					unset($fields['section_wmcomment']);
				}
				break;
			case "USD" : case "UAH":
				$fields['section_fname']=1;
				$fields['section_iname']=1;
				$fields['lmi_comment']="fname,iname,passport";
				$fields['section_phone']=1;
				$fields['section_email']=1;
				$fields['section_passport']=1;
				break;
			case "P24USD": case "P24UAH" : case "P24EUR" :
				$fields['lmi_comment']="fname,iname,account,mfo,bank_name,inn,bank_comment"; //name
				$fields['section_phone']=1;
				$fields['section_email']=1;
				$fields['section_fname']=1;
				$fields['section_iname']=1;
				$fields['section_bank']=1;
				unset($fields['section_purse']);
				if ( !$row_order['needcheck1'] ) {
					unset($fields['section_fname']);
					unset($fields['section_iname']);
				}				
				break;
			case "ACRUB": case 'SVBRUB': case 'RUSSTRUB': case 'TBRUB':
				$fields['lmi_comment']="fname,iname,account"; //name
				$fields['section_phone']=1;
				$fields['section_email']=1;
				$fields['section_fname']=1;
				$fields['section_iname']=1;
				$fields['section_r_bank_account']=1;
				unset($fields['section_purse']);
				$fields['section_fname']=1;
				$fields['section_iname']=1;
				$fields['section_oname']=1;
				if ( $row_order['currout']=='TBRUB' ) $fields['unk']=1;
				break;
			case "PMUSD" : case "PMEUR" :
				$fields['lmi_comment']="";
				$fields['section_pm_purse']=1;
				$fields['section_email']=1;
				if ( substr($row_order['currout'],0,3)=="P24" ) {
					unset($fields['section_fname']);
					unset($fields['section_iname']);
				}
				unset($fields['economy_mode']);
				unset($fields['section_bank']);
				if ( substr($row_order['currin'],0,2)=="WM" ) {
					unset ($fields['section_wmid']);
					unset($fields['section_wmcomment']);
					unset($fields['section_purse']);
				}
				break;
			case "LQUSD" : case "LQRUB" : case "LQUAH" : case "LQEUR" :
				$fields['section_phone']=1;
				$fields['section_comment_phone']=1;
				$fields['section_mastercard']=1;
				$fields['section_email']=1;
				if ( substr($row_order['currin'],0,2)=="WM" ) {
					unset ($fields['section_wmid']);
					unset($fields['section_wmcomment']);
					unset($fields['section_purse']);
				}
				break;
			case "MCVUSD" : case "MCVRUR" : case "MCVUAH" : case "MCVEUR" :
				$fields['section_bank']=1;
				$fields['section_email']=1;
				break;
			case "WIREEUR" :
				$fields['wire_eur_iban']=1;
				$fields['section_email']=1;
				$fields['section_fname']=1;
				$fields['section_iname']=1;
				if ( substr($row_order['currin'],0,2)=="WM" ) {
					unset($fields['section_purse']);
				}
				break;
			case "KS" :
				$fields['section_phone']=1;
				$fields['section_email']=1;
				$fields['section_phone_recharge']=1;
				//$fields['section_passport']=1;
				unset($fields['section_wmid']);
				unset($fields['section_purse']);
				unset($fields['section_bank']);
				unset($fields['section_fname']);
				unset($fields['section_iname']);
				break;
			case "LRUSD" : case "LREUR" :
				$fields['lmi_comment']="";
				$fields['section_lr_purse']=1;
				$fields['section_email']=1;
				$fields['section_phone']=1;
				if ( substr($row_order['currout'],0,3)=="P24" ) {
					unset($fields['section_fname']);
					unset($fields['section_iname']);
				}
				//unset($fields['economy_mode']);
				unset($fields['section_bank']);
				if ( substr($row_order['currin'],0,2)=="WM" ) {
					unset ($fields['section_wmid']);
					unset($fields['section_purse']);
					unset($fields['section_wmcomment']);
				}
				break;
			case "INSTAFX" :
				$fields['section_forex_acc']=1;
				$fields['section_email']=1;
				$fields['section_passport']=1;
				$fields['lmi_comment']="";
				unset($fields['economy_mode']);
				break;
			default:
				$fields[1]="";			
		}
		
		
		return $fields;
			
	}
	function date_fields ($prefix="") {
		echo "<select name='".$prefix."DD'>";
		for ($i = 1; $i <= 31; $i++) {
			$selected="";
			if ( isset($_POST[$prefix.'DD']) && $_POST[$prefix.'DD']==$i ) {
				$selected=" selected='selected'";
			}else{
				if ( !isset($_POST[$prefix.'DD']) && $i==date('d') )$selected=" selected='selected'";
			}
			echo "<option value='".(strlen($i)==1 ? "0".$i : $i)."'".$selected.">$i</option>";
			$selected="";
		}
		echo "</select> / ";
		echo "<select name='".$prefix."MM'>";
		for ($i = 1; $i <= 12; $i++) {
			$selected="";
			if ( isset($_POST[$prefix.'MM']) && $_POST[$prefix.'MM']==$i ) {
				$selected=" selected='selected'";
			}else{
				if ( !isset($_POST[$prefix.'MM']) && $i==date('m') )$selected=" selected='selected'";
			}
			echo "<option value='".(strlen($i)==1 ? "0".$i : $i)."'".$selected.">$i</option>";
			$selected="";
		}
		echo "</select> / ";
		echo "<select name='".$prefix."YYYY'>";
		for ($i = 2008; $i <= 2020; $i++) {
			$selected="";
			if ( isset($_POST[$prefix.'YYYY']) && $_POST[$prefix.'YYYY']==$i ) {
				$selected=" selected='selected'";
			}else{
				if ( !isset($_POST[$prefix.'YYYY']) && $i==date('Y') )$selected=" selected='selected'";
			}
				
			echo "<option value='$i'".$selected.">$i</option>";
		}
		echo "</select>";	
	}
	function select_fields ($name, $val) {
		echo "<select name='".$name."'>";
			$r = explode(",", $val);
			foreach ($r as $v) {
				$selected="";
				if ( isset($_POST[$name]) && $_POST[$name]==$v ) {
					$selected=" selected='selected'";
				}
				echo "<option value='$v'".$selected.">$v</option>";	
			}
			echo "</select>";	
	}
	function sql_fields ($name, $query, $params) {
		$query=_query($query,"");
		echo "<select name='".$name."'>";
		while ( $row=$query->fetch_assoc() ) {
			$selected="";
			if ( isset($_POST[$name]) && $_POST[$name]==$row[$params[1]] ) {
				$selected=" selected='selected'";
			}
			echo "<option value='".$row[$params[1]]."'".$selected.">".$row[$params[0]]."</option>";	
		}
		echo "</select>";	
	}
	
}
?>