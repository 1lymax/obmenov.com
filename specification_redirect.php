<?php 
require_once('Connections/ma.php');
require_once($serverroot.'function.php');
//require_once("siti/amounts.php");
require_once($serverroot."siti/class.php");
error_reporting(0);
$redirect = '';
$needcheck=0;
$bank_rekvizits="";

//  6762462602190511  
//  6762462602779495

	if ( isset($_POST['game']) ) { //редирект от игр
		if ( $_POST['purse']=="R247297158388" || $_POST['purse']=="Z337459962936" || $_POST['purse']=="U276969565150" ) {
			$redirect="https://merchant.webmoney.ru/lmi/payment.asp";
		}elseif ( substr($_POST['purse'],0,3)=="MCV" ) { 
			$redirect="https://liqpay.com/?do=clickNbuy";
		}elseif ( substr($_POST['purse'],0,3)=="P24" ) { 
			$redirect="https://api.privatbank.ua:9083/p24api/ishop";
		}
		
		//$wmTrans  = array('Webmoney WMZ'=>'USD','Webmoney WMR'=>'RUB','Webmoney WMU'=>'UAH','Webmoney WME'=>'EUR',
		//	 'MasterCard/VISA USD'=>'USD','MasterCard/VISA RUR'=>'RUR','MasterCard/VISA UAH'=>'UAH');
		$_POST['nick']=str_ireplace("<script>","",$_POST['nick']);
		$_POST['nick']=str_ireplace("</script>","",$_POST['nick']);
		echo '<body onLoad="script:document.form1.submit();" >';
		echo "<form action='".$redirect."' method='post' name='form1'>";
		echo "Подождите, сейчас Вы будете перемещены...<br>
				Или нажмите 'Продолжить', если этого не происходит.<br>";
		echo "<input type='Submit' value='Продолжить'>";
		if ( $redirect=="https://merchant.webmoney.ru/lmi/payment.asp" ) {
			$post=array('LMI_PAYMENT_NO'=>htmlspecialchars((int)$_POST['payment']),
						'LMI_PAYMENT_DESC'=>htmlspecialchars($_POST['description']),
						'LMI_PAYMENT_AMOUNT'=>htmlspecialchars((float)$_POST['amount']),
						'LMI_PAYEE_PURSE'=>htmlspecialchars($_POST['purse']),
						'LMI_RESULT_URL'=>"https://obmenov.com/game/wmresult.php",
						'LMI_SUCCESS_URL'=>"https://obmenov.com/gamepay.php",
						'projectid'=>htmlspecialchars((int)$_POST['projectid']),
						'nick'=>htmlspecialchars($_POST['nick'])
						);			
		}elseif ( $redirect=="https://liqpay.com/?do=clickNbuy" ) {
			$select="select * from gamedealer_projects where projectid=".(int)htmlspecialchars($_POST['projectid']);
			$query=_query($select, "specification_redirect.php 5");
			$project=$query->fetch_assoc();
			$title=$project['title_english']==""?$project['title']:$project['title_english'];
			$description="Payment for ".htmlspecialchars($_POST['nick']). " account to the ".$project['title_english']." Game";
			//<server_url>https://obmenov.com/game/wmresult.php</server_url>
			$xml="<request>      
				<version>1.2</version>
				<result_url>https://obmenov.com/gamepay.php</result_url>
				
				<merchant_id>$lq_merchant_id</merchant_id>
				<order_id>".htmlspecialchars($_POST['payment'])."</order_id>
				<amount>".htmlspecialchars($_POST['amount'])."</amount>
				<currency>".htmlspecialchars(substr($_POST['purse'],3,3))."</currency>
				<description>".$description."</description>
				<default_phone></default_phone>
				<goods_id>".(int)htmlspecialchars($_POST['projectid'])."</goods_id>
				<pay_way></pay_way> 
				</request>
				";
				//echo $xml;
				$xml_encoded = base64_encode($xml); 
				$lqsignature = base64_encode(sha1($lq_signature.$xml.$lq_signature,1));		
				$post=array('operation_xml'=>$xml_encoded,
							'signature'=>$lqsignature);
			$q = _query("select id from gamedealer_wmreq WHERE 1 AND pid = ".(int)$_POST['payment'],"");
            if(mysql_num_rows($q)==0){;//return 'Платеж с таким номером уже существует. Повторите запрос';
            	_query("insert into gamedealer_wmreq SET pid = '".$_POST['payment']."', 
													nick='".$_POST['nick']."',
													wm_amount=".(float)$_POST['amount'].",
													wm_currency='".$_POST['purse']."',
													projectid='".$_POST['projectid']."'
													","");
			}
		}elseif ( $redirect=="https://api.privatbank.ua:9083/p24api/ishop" ) {
			$post=array('amt'=>htmlspecialchars($_POST['amount']),
						'ccy'=>htmlspecialchars(substr($_POST['purse'],3,3)),
						'merchant'=>$_POST['purse']=="P24USD" ? $pb_usd_merchant : $pb_uah_merchant,
						'order'=>(int)htmlspecialchars($_POST['payment']),
						'details'=>iconv("windows-1251","utf-8",htmlspecialchars($_POST['description'])),
						'ext_details'=>htmlspecialchars($_POST['payment']),
						'pay_way'=>"privat24",
						'return_url'=>"https://obmenov.com/gamepay.php",
						'server_url'=>"https://obmenov.com/game/wmresult.php"
						);
			$q = _query("select id from gamedealer_wmreq WHERE 1 AND pid = ".(int)$_POST['payment'],"");
            if($q->num_rows==0){
				_query("insert into gamedealer_wmreq SET pid = '".(int)$_POST['payment']."', 
													nick='".$_POST['nick']."',
													wm_amount=".(float)$_POST['amount'].",
													wm_currency='".$_POST['purse']."',
													projectid='".(int)$_POST['projectid']."'
													","");
			}
		}
		reset($_POST);
		while (list($key, $val) = each($post)) {
			echo "<input type='hidden' name='$key' value='$val'>";
		}
	
	
	}else{  // редирект от обмена
	
	
	
	
	$params['bank_name']="Банк: ";
	$params['mfo']="МФО: ";
	$params['fname']="ФИО: ";
	$params['iname']="";
	$params['passport']="Паспорт: ";
	$params['inn']="ИНН: ";
	$params['account']="Счет: ";
	$params['PurseOut']="Purse: ".( isset($_POST['purseTypeOut']) ? $_POST['purseTypeOut'] : "");
	$params['bank_comment']="...";
	$comment="";
	$comment1="";
	$bank_account_type=0;
	$rnd = isset($_POST['RND']) ? $_POST['RND'] : "";
	$orderid = isset($_POST['oid']) ? (int)htmlspecialchars($_POST['oid']) : 0;
	$clid = isset($_POST['clid']) ? htmlspecialchars($_POST['clid']) : "";
	$paymentid = isset($_POST['pid']) ? (int)htmlspecialchars($_POST['pid']) : 0;
	$order = "SELECT *	FROM orders WHERE clid='".$clid."' and orders.id=".$orderid ." AND (time + INTERVAL 25 MINUTE > NOW());";
	$row_order=_query2($order, 17);
	$order_numrows=$row_order->num_rows;
	$row_order=$row_order->fetch_assoc();
	if ( $order_numrows != 1 ) {
			$message = 'order_time_exceeded';
			header("Location:specification.php?clid=".$clid."&oid=".$orderid."&message=".$message);

	    //_error('time exceeded or order not found specification_redirect.php', 5, '');
	}			
	$order = "SELECT `id` , `summin` , `summout` , `currin` , `currout` , `disc` , 
				`discammount` , `ordered` , `attach` , `date` , `clid` , `authorized` , 
				`partnerid` , `name` , `fname` , `iname`, `oname`, `email` , `passport` , `mfo` , 
				`account` , `account_comment` , `bank_name` , `bank_type` , `bank_comment` , 
				`inn` , `purse_u` , `purse_r` , `purse_e` , `purse_z` , `purse_other` , `wmid` , 
				`phone` , `time` , `droped` , `needcheck` , `blacklist` , `descr` , `retid` , `status` , 
				`retval` , `item` , `bank_account_type`, `phone`,
				(select needcheck from currency where name=orders.currin) as needcheck1, 
				(select needcheck from currency where name=orders.currout) as needcheck2
			FROM orders WHERE orders.id=".$orderid .";";
			$row_order=_query2($order, 17);
			$row_order=$row_order->fetch_assoc();
	$order=new orders();
	$check_summ=$order->check_order_summ($row_order['id']);
	//maildebugger($check_summ);
	if ( $check_summ[1]!=$check_summ[2] ) {
			$message = 'not_allowed';
			header("Location:specification.php?clid=".$clid."&oid=".$orderid."&message=".$message);die();

	    //_error('time exceeded or order not found specification_redirect.php', 5, '');
	}
	$query = "SELECT payment.id FROM payment WHERE ".
	"payment.id =".$paymentid." AND payment.orderid = ".$orderid." AND RND='".$rnd."';";
	$result = _query($query, 4);
	$rows = $result->num_rows;
	
					// заполнение таблиц клиентов и ордеров недостающим		
			if ( isset($_POST['Purse']) ) {
				//require_once("siti/_header.php");
				//$response = $wmxi->X8("",$_POST['purseType'].trim(strtr($_POST['Purse'], "URZEurze", "        ")));
				//if ( $transformed["w3s.response"]["retval"]==0 ) {
				//	$message= 'purse_in_invalid';
				//}
				$purse=", `purse_".strtolower(substr($_POST['purseType'],0,1))."`='".
				trim(strtr($_POST['Purse'], "URZEurze", "        "))."'";}
			else{$purse='';}
			if ( isset($_POST['PurseOut']) ) {
				//require_once("siti/_header.php");
				//echo $_POST['purseTypeOut'].trim(strtr($_POST['PurseOut'], "URZEurze", "        "));
				//$response = $wmxi->X8("",$_POST['purseTypeOut'].trim(strtr($_POST['PurseOut'], "URZEurze", "        ")));
				//$structure = $parser->Parse($response, DOC_ENCODING);
				//$transformed = $parser->Reindex($structure, true);				
				//print_r($transformed);
				//if ( $transformed["w3s.response"]["retval"]==0 ) {
				//	$message= 'purse_out_invalid';
				//}				
				$purseOut=", `purse_".strtolower($_POST['purseTypeOut'])."`='".
				trim(strtr($_POST['PurseOut'], "URZEurze", "        "))."'";}
			else{$purseOut='';}
			$fname = isset ($_POST['fname']) ? ", fname='".$_POST['fname']."'" : "";
			$iname = isset ($_POST['iname']) ? ", iname='".$_POST['iname']."'" : "";
			$oname = isset ($_POST['oname']) ? ", oname='".$_POST['oname']."'" : "";
			$phone = isset ($_POST['phone']) ? ", phone='".str_replace("+", "", $_POST['phone'])."'" : "";
			$wmid = isset ($_POST['wmid']) ? ", wmid='".$_POST['wmid']."'" : "";
			$email = isset ($_POST['email']) ? ", email='".$_POST['email']."'" : "";
			$passport = isset ($_POST['passport']) ? ", passport='".$_POST['passport']."'" : "";
			$mfo = isset ($_POST['mfo']) ? ", mfo='".str_replace(" ", "", $_POST['mfo'])."'" : "";
			$inn = isset ($_POST['inn']) ? ", inn='".str_replace(" ", "", $_POST['inn'])."'" : "";
			$unk = isset ($_POST['unk']) ? ", inn='".str_replace(" ", "", $_POST['unk'])."'" : "";
			$account = isset ($_POST['account']) ? ", account='". str_replace(" ", "", $_POST['account']) . "'": "";
			$account_comment = isset ($_POST['account_comment']) ? ", account_comment='".$_POST['account_comment']."'" : "";
			$bank_name = isset ($_POST['bank_name']) ? ", bank_name='". $_POST['bank_name'] . "'": "";
			$bank_comment = isset ($_POST['bank']) ? ", bank_comment='". $_POST['bank'] . "'": "";
			$bank_type = isset ($_POST['bank_type']) ? ", bank_type='". $_POST['bank_type'] . "'": "";
			$purse_other = isset ($_POST['purse_other']) ? ", purse_other='". str_replace(" ","",strtoupper($_POST['purse_other'])) . "'": "";
			$purse_other_cl = isset ($_POST['purse_other']) ? ", purse_".$row_order['currout']."='". 
										str_replace(" ","",strtoupper($_POST['purse_other'])) . "'": "";
			//$bank_account_type = isset ($_POST['bank_accout_type']) ? ", bank_account_type='". $_POST['bank_account_type'] . "'": "";
		    if ( (substr($row_order['currin'],0,3)=="P24" || substr($row_order['currout'],0,3)=="P24" || 
																	$row_order['currout']=="WIREEUR" ) &&
				isset($_POST['fname']) && isset($_POST['iname']) && isset($_POST['account']) ) {
				$bank_name="";
				$bank_comment=""; $mfo=""; $inn="";$params['inn']=""; $params['mfo']=""; $params['bank_comment']=""; $params['bank_name']="";
				$select="select mfo, bank_name from orders where fname='".$_POST['fname']. "' and iname='".$_POST['iname'].
				"' and account='".str_replace(" ", "", $_POST['account'])."' 
					and orders.needcheck=0";
					//id=payment.orderid 
					//and payment.canceled=1 order by time desc";
				
				$query=_query($select,"checkticket.php 23");
				
				if ( $query->num_rows>0 ) {
					$row=$query->fetch_assoc();
					$select="select * from orders where fname='".$_POST['fname']."' and iname='".$_POST['iname'].
					"' and account='".str_replace(" ", "", $_POST['account'])."' and mfo!='' order by time desc";
					$query=_query($select,"checkticket.php 23");
					if ( $query->num_rows>0 ) {
						$row=$query->fetch_assoc();
						$bank_rekvizits.=", МФО:".$row['mfo'];
						$bank_rekvizits.=($row['bank_name']!='')? (", ".$row['bank_name']):"";
						
					}
				}else{
					$needcheck=1;
				}
				
				$select="select bank_account_type from orders, payment where account='".str_replace(" ", "", $_POST['account'])."' 
					and orders.id=payment.orderid 
					and payment.canceled=1 order by time desc";
				$query=_query($select,"checkticket.php 23");
				
				if ( $query->num_rows>0 ) {
					$row=$query->fetch_assoc();
					$bank_account_type=$row['bank_account_type'];
				}else{
					$bank_account_type=0;
				}
				
				$bank_rekvizits.="";
				if ( $row_order['currout']=="WIREEUR" ) $bank_rekvizits.=" IBAN: ".$row_order['account'];
				//", КБ Приватбанк";///var/www/webmoney_ma/data/www/obmenov.com/siti/passport.webmoney.ru.crt
				/*$wmxi = new WMXI("", DOC_ENCODING);
				$wmxi->Classic("418941129503", "ghjvsdrfvjpujd", "/var/www/webmoney_ma/data/etc/418941129503.kwm");
				$response=$wmxi->X19("4", 
						 $_POST['purseType'], 
						 $row_order['summin'], 
						 $_POST['wmid'], 
						 '', 
						 $_POST['fname'],
						 $_POST['iname'], 
						 $_POST['account'], 
						 '');
				print_r($response);*/
				
			}
			if ( substr($row_order['currin'],0,3)=="P24" ) {
				if ( isset($_POST['economy']) && $_POST['economy']=="2" ) {
					$select="insert into orders_ecomode (oid, percent) values (".
								$row_order['id'].",".
								get_setting('p24_ecomode_discount').")";
					$query=_query($select,"");
				}
			}
							
			$query = "UPDATE clients SET RND='' ".$purse.$fname.$oname.$unk.$iname.$purseOut.$phone.$wmid.$passport.$account."
			WHERE clid='".$row_order['clid']."';";			
			$result = _query($query,"specification_redirect 1");
			
		    $query = "UPDATE orders SET ordered=1 ".$fname.$iname.$oname.$phone.$unk.$purse.$wmid.$purseOut.$mfo.$email.$passport.$account.
			$account_comment.$purse_other.
			$bank_name.$inn.$bank_comment.$bank_type.", needcheck=".$needcheck.", bank_account_type=".$bank_account_type.", 
			time=NOW() WHERE id=".$row_order['id'].";";
			$result = _query($query,"specification_redirect 2");
			if ( isset($_POST['account']) and isset($_POST['account_comment']) and strlen($_POST['account_comment'])!=0 ) {
				$query="select id from bank_accounts where 
								account_comment='".$_POST['account_comment']."' and 
								account='".$_POST['account']."' and 
								clid='".$_POST['clid']."'";
				$result=_query($query,"");
				if ( $result->num_rows==0) {
					$query="update bank_accounts set account_comment='".$_POST['account_comment']."' 
							where clid='".$_POST['clid']."' and account='".$_POST['account']."'";
					$result = _query($query,"specification_redirect 2");
					//echo $query;
					if ( msql_affected_rows($result)==0 ) {
						$query="insert into bank_accounts ( fname, iname, clid, account, account_comment )
								values ( '".(isset($_POST['fname'])?$_POST['fname']:"")."',
										 '".(isset($_POST['iname'])?$_POST['iname']:"")."',
										 '".$_POST['clid']."',
										 '".$_POST['account']."',
										 '".$_POST['account_comment']."'
										 )";
						$result=_query($query,"");
					
					}
				}
					
			}
			
			// заполнение таблиц клиентов и ордеров недостающим	
	
	if ( substr($row_order['currin'],0,2)=="WM" && in_array($row_order['currout'],array("KS","INSTAFX")) ) {
		require_once($serverroot."siti/prepaid_wm_config.php");
		$lmi_description=($row_order['currout']=="KS"?"Пополнение Киевстар: ".htmlspecialchars($_POST['purse_other']). " на сумму ".
										floor($row_order['summout']+$row_order['discammount'])."UAH":$lmi_description);
		$lmi_description=($row_order['currout']=="INSTAFX"?"Пополнение баланса трейдера. Счет: ".htmlspecialchars($_POST['purse_other']). ", брокер: Instaforex.com. ".$row_order['fname']." ".$row_order['iname'].", Паспорт: ".$row_order['passport']:$lmi_description);										
		$post=array('LMI_PAYMENT_NO'=>htmlspecialchars((int)$paymentid),
						'LMI_PAYMENT_DESC'=>$lmi_description,
						'LMI_PAYMENT_AMOUNT'=>$row_order['summin'],
						'LMI_PAYEE_PURSE'=>$WM_SHOP_PURSE[$row_order['currin']],
						'LMI_RESULT_URL'=>"https://obmenov.com/cellresult.php",
						'LMI_SUCCESS_URL'=>"https://obmenov.com/comment.php",
						'email'=>$row_order['email'],
						'oid'=>$row_order['id'],
						'pid'=>$paymentid,
						'clid'=>$row_order['clid']
						);
		$update="update orders set descr='".$lmi_description."' where id=".$row_order['id'];
							$query=_query2($update,"specification_redirect lmi");
		echo "<body onLoad='script:document.form1.submit();' >Подождите, сейчас Вы будете перемещены...<br>Или нажмите 'Продолжить', если этого не происходит.<br>";
		echo "<form action='https://merchant.webmoney.ru/lmi/payment.asp' method='post' name='form1'>";
		echo "<input type='Submit' value='Продолжить'>";
		while (list($key, $val) = each($post)) {
			echo "<input type='hidden' name='$key' value='$val'>";
		}
		echo "</form></body></html>";
		die();	
	}
	
	
	
	$specification = new specification();
	$fields=$specification->fields($row_order);
	if ( isset($fields['section_wmid']) ) {
		$response=$order->check_X19($row_order, "post");
	//print_r($response);
		$retid="";
		if ( is_array($response) ) {
			if ( !isset($response['passport.response']) ) {
				$message="noresponse";
				header("Location:specification.php?clid=".$row_order['clid']."&oid=".$row_order['id']."&message=".$message);
				maildebugger($response);
			}
	
		if ( $response['passport.response']['retval']!="0" ) {
		
			switch ( $response['passport.response']['retdesc'] ) {
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/fname" : 
					$message="fname_invalid";break;
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/iname" : 
					$message="iname_invalid";break;
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/card_number" : 
					$message="bankcard_invalid";break;
				case "ошибка при проверке входных параметров step=20.1" : 
					$message="input_invalid";break;
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/pnomer" : 
					$message="pnomer_invalid";break;
				case "пропущен обязательный параметр /passport.request/userinfo/wmid" : 
					$message="no_wmid";break;
			}
		//echo "404";
		
			if ($response['passport.response']['retval']=="404") {
				$message="not_allowed";
			}elseif ($response['passport.response']['retval']=="408") {
				$message="not_allowed_card";
			}elseif ($response['passport.response']['retval']=="405") {
				$message="fattest";
			}elseif ($response['passport.response']['retval']=="415") {
				$message="bad_phone";
			}elseif ($response['passport.response']['retval']=="407") {
				$message="pscan";
			}elseif ($response['passport.response']['retval']=="409") {
				$message="attest7";
			}elseif ($response['passport.response']['retval']=="499") {
				$message="query_limit";
			}elseif ($response['passport.response']['retval']=="500") {
				
			}else {	$message="ret=".$response['passport.response']['retval'];
			}
			if ( isset($message) ) {
				header("Location:specification.php?clid=".$row_order['clid']."&oid=".$row_order['id']."&message=".$message);
			}
		}
	//maildebugger($response);
		$retid=$response['passport.response']['retid'];	
		}
	}
		echo '<body onLoad="script:document.form1.submit();" >';
		//print_r($response);
		
		if ( $_POST['redirect']=="http://bank.smscoin.com/bank/" ) { // обмен на смс
		echo "<form action='http://bank.smscoin.com/bank/' method='post' name='form1'>";
			require_once($serverroot.'siti/sms_config.php');
			$order_id=(int)htmlspecialchars($row_order['id']);
			$s_amount=$row_order['bank_comment'];
			$s_description=$_POST['comment'];
			$sign = md5($purse."::".$order_id."::".$s_amount."::".$clear_amount."::".$s_description."::".$secret_code);
			
			$post=array('s_purse'=>$purse,
						's_order_id'=>$order_id,
						's_amount'=>$s_amount,
						's_clear_amount'=>$clear_amount,
						's_description'=>$s_description,
						's_sign'=>$sign,
						'clid'=>$row_order['clid']
						);
			reset($_POST);
			while (list($key, $val) = each($post)) {
				echo "<input type='hidden' name='$key' value='$val'>";
			}
			echo "</form></body>";die();
		}	// обмен на смс	
		
		
		if ( (substr($row_order['currin'] ,0,3)=="MCV") || (substr($row_order['currin'] ,0,2)=="LQ") ) {
			//if ( isset($_POST['wmid']) ) {
				//$client=new client();
				//$summ=$row_order['attach']+$client->day_summ($_POST['wmid']);
				//if ( $summ>2000 && !$client->good_wmid($wmid) ) $redirect="index.php?message=max_limit";
			//} // проверка на ввод более 2к вмз
			if (substr($row_order['currin'] ,0,3)=="MCV")$method='card';
			if (substr($row_order['currin'] ,0,2)=="LQ")$method='liqpay';
			$merchant_id=$lq_merchant_id;
			$signature=$lq_signature;
			$url="https://liqpay.com/?do=clickNbuy";
			
			$lq_phone='';
			$select="select type from currency where name='".$row_order['currin']."'";
			$query=_query($select, "specification_redirect.php 19");
			$valtype=$query->fetch_assoc();
			//
			
			if (strlen($row_order['purse_other'])==0) {
				if ($row_order['currout']=='KS') {
					$purse=$phone;
				}else{
					$purse = "Webmoney ".substr($row_order['currout'],2,1).$row_order["purse_".strtolower(substr($row_order['currout'],2,1))];
				}
			}else{
				$purse=$row_order['purse_other'];
			}
								  
			$xml="<request>      
				<version>1.2</version>
				<result_url>".$siteroot."mcvresult.php</result_url>
				<server_url>".$siteroot."mcvresult.php</server_url>
				<merchant_id>$merchant_id</merchant_id>
				<order_id>".$row_order['id']."</order_id>
				<amount>".$row_order['summin']."</amount>
				<currency>".$valtype['type']."</currency>
				<description>".$row_order['id']."</description>
				<default_phone>$lq_phone</default_phone>
				<pay_way>$method</pay_way>
				<goods_id>1000</goods_id>
				</request>
				";
				/*Exchange Order #".$row_order['id']." [".
										$row_order['summin']." ".
										$valtype['type']." -> ".
										$row_order['summout']." " .
										$row_order['currout']."] ".$purse."*/

			$xml_encoded = base64_encode($xml); 
			$lqsignature = base64_encode(sha1($signature.$xml.$signature,1));
		}
	$client = "SELECT clients.id, clients.purse_z, clients.purse_r, clients.purse_e, clients.purse_u, clients.wmid FROM clients WHERE clid='".$row_order['clid']."';";
	$row_client=_query($client, 18);
	$row_client=$row_client->fetch_assoc();


		if ( isset($_POST['recept']) && !isset($_SESSION['WmLogin_WMID']) ) {
			$message="bad_wmlogin";
		}
		/*if ( (checkExch( $row_order['currin'], $row_order['currout'] ) == 2.1 ) && 
				( ($row_order['summout']+$row_order['discammount']) > $WM_amount_r[$row_order['currout']]) ) {
					$message="order_amount_exceeded";
						
					_error('заявка № '. $row_order['id']." не хватает резерва: ".$WM_amount_r[$row_order['currout']] , 22, '');			
				} else {*/
					

				if ( isset($message) ) { $redirect = "specification.php"; //echo $redirect;
					//print_r($_POST);
					echo "Подождите, сейчас Вы будете перемещены...<br>
						Или нажмите 'Продолжить', если этого не происходит.<br>";
						echo "<form action='$redirect?message=$message' method='post' name='form1'>";
						echo "<input type='Submit' value='Продолжить'>";
						echo "<input type='hidden' name='message' value='$message'>";
						echo "<input type='hidden' name='err1' value='Необходимо для обмена: ".
							($row_order['summout']+$row_order['discammount'])." ".$row_order['currout']."'>";
						echo "<input type='hidden' name='err2' value='Текущий резерв: ".$WM_amount_r[$row_order['currout']]." ".
							$row_order['currout']."'>";
						echo "<input type='hidden' name='order' value='ok'>";
						echo "<input type='hidden' name='moneyIn' value='".$row_order['currin']."'>";
						echo "<input type='hidden' name='moneyOut' value='".$row_order['currout']."'>";
						echo "<input type='hidden' name='SummIn' value='".$row_order['summin']."'>";
						echo "<input type='hidden' name='SummOut' value='".$row_order['summout']."'>";
						echo "<input type='hidden' name='SumIn' value='".$row_order['summin']."'>";
						echo "<input type='hidden' name='SumOut' value='".$row_order['summout']."'>";
					}
					while (list($key, $val) = each($_POST)) {
						//echo $key."->".$val;
						if ( $key!="redirect" && $key!="comment" && $key!="comment1" && $key!="mm" ) {
							echo "<input type='hidden' name='$key' value='$val'>";
						}
					}
				//}
				
				
				//print_r($_POST);
					// перенаправление
				$select="select * from orders where id=".$row_order['id'];
				$query=_query($select,"specification_redirect 3");
				$row_order=$query->fetch_assoc();
			if ( strlen($redirect) == 0 ) {					
				reset($_POST);
				if ( isset ($_POST['recept']) ) { // выписывание счета
				
					while (list($key, $val) = each($_POST)) {
						if ( $key == "comment" ) { 
							$params1 = explode(",", $_POST['comment']);
 							foreach ($params1 as $param) {
								if ( isset($_POST[$param]) && trim($_POST[$param])!="" ) {
									if ( $param=="account" || $param=="inn" || $param=="mfo" || $param=="phone" ) {
										$comment .=$params[$param].str_replace(" ", "",$_POST[$param]).". ";
									}else{
										$comment .=$params[$param].$_POST[$param].". ";
									}
								}								
							
							}
							/*if ( ($row_order['currout']=="P24UAH" || $row_order['currout']=="P24USD" || $row_order['currout']=="P24EUR") ) 
							{
								$comment .="Приватбанк.";
							}*/
							$comment=(isset($_POST['comment1']) ? $_POST['comment1'] : "").", ".trim($comment);
						}
					}
					
					echo "Подождите, сейчас Вы будете перемещены...<br>
						Или нажмите 'Продолжить', если этого не происходит.<br>";					
					echo "<form action='cabinet.php' method='get' name='form1'>";
					echo "<input type='Submit' value='Продолжить'>";
					echo "<input type='hidden' name='oid' value='".$row_order['id']."'>";
					echo "<input type='hidden' name='clid' value='".$row_order['clid']."'>";
									
					$dtf=dateformat($row_order['time']);
					require_once($serverroot."siti/_header.php");
					
					$response = $wmxi->X4(  // проверка, есть ли уже выписанный счет
										  
										  
					$shop_wm_purse[substr(htmlspecialchars($row_order['currin']),2,1)],               # номер кошелька 
							#для оплаты на который которого выписывался счет
					0,     # целое число > 0
					intval($_POST["LMI_PAYMENT_NO"]),     # номер счета в системе учета магазина; любое целое число без знака
					$dtf['y'].$dtf['m'].$dtf['d']." 00:00:00",
					$dtf['y'].$dtf['m'].$dtf['d']." 23:59:59"    # ГГГГММДД ЧЧ:ММ:СС
					);
					$structure = $parser->Parse($response, DOC_ENCODING);
					$transformed = $parser->Reindex($structure, true); 
					if ( $transformed['w3s.response']['outinvoices']['outinvoice']['orderid']== intval($_POST["LMI_PAYMENT_NO"]) ) {
						
					} else {
					
						$response = $wmxi->X1(  //выписываем счет
						intval($_POST["LMI_PAYMENT_NO"]),    # номер счета в системе учета магазина; любое целое число без знака
						$_SESSION['WmLogin_WMID'],       # WMId покупателя
						$shop_wm_purse[substr(htmlspecialchars($row_order['currin']),2,1)],         # номер кошелька,
							#на который необходимо оплатить счет
						floatval($_POST["LMI_PAYMENT_AMOUNT"]),   # число с плавающей точкой без незначащих символов
						trim($comment),         # произвольная строка от 0 до 255 символов; 
							#пробелы в начале или конце не допускаются
						"Информация по заявке доступна по адресу ".$siteroot."cabinet.php?oid=".
						$row_order['id']."&clid=".$row_order['clid'],      # произвольная строка 
							#от 0 до 255 символов; пробелы в начале или конце не допускаются
						0,     # целое число от 0 до 255; если 0 - протекция сделки при оплате счета не разрешена
						1  # целое число от 0 до 255; если 0 - срок оплаты не определен
						);
						//$structure = $parser->Parse($response, DOC_ENCODING);
						//$transformed = $parser->Reindex($structure, true);
						//print_r($transformed);
					}
					
					
					
				}else{ // формирование формы на отправку в мерчант
					while (list($key, $val) = each($_POST)) {
						if ( $key == "redirect" ) { echo "<form action='$val' method='post' name='form1'>";
							echo "Подождите, сейчас Вы будете перемещены...<br>
							Или нажмите 'Продолжить', если этого не происходит.<br>";
							echo "<input type='Submit' value='Продолжить'>";
						}
					}
					reset($_POST);

					
					while (list($key, $val) = each($_POST)) {
						if ( $key == "comment" ) { 
							$params1 = explode(",", $_POST['comment']);
 							foreach ($params1 as $param) {
								if ( isset($_POST[$param]) && trim($_POST[$param])!="" ) {
									if ( $param=="account" || $param=="inn" || $param=="mfo" || $param=="phone" ) {
										$t=str_replace(" ", "",$_POST[$param]);
										$t=str_replace("-", "",$t);
										$t=str_replace("/", "",$t);
										$comment .=$params[$param].htmlspecialchars($t)." ";
									}else{
										$comment .=$params[$param].htmlspecialchars($_POST[$param])." ";
									}
								}								
							
							}
							$lmi_payment_desc=(isset($_POST['comment1']) ? htmlspecialchars($_POST['comment1']) : "").", ".
										trim($comment).$bank_rekvizits;
							if ( substr($row_order['currin'],0,2)=="WM" ) {

								if ( isset($retid) && $retid!="" ) {
									echo "<input type='hidden' name='LMI_PAYMENT_DESC' value='".$lmi_payment_desc." [rd: ".$retid."]'>";
								}else{
									if ( substr($row_order['currout'],0,2)=="LQ" ) {
										$lmi_payment_desc="Частный ".$lmi_payment_desc." ".$row_order['phone'];
									}
									if ( strlen($row_order['purse_other'])!=0 ) {
										$lmi_payment_desc="Частный обмен ".$lmi_payment_desc." ".$row_order['purse_other'];
									}
									echo "<input type='hidden' name='LMI_PAYMENT_DESC' value='".$lmi_payment_desc."'>";
								}
								//echo "<input type='hidden' name='LMI_RESULT_URL' value='https://top.obmenov.com/result.php'>";
								//echo "<input type='hidden' name='LMI_FAIL_URL' value='https://top.obmenov.com/payment_failed.php'>";
							}else{
															
							}
							$update="update orders set descr='".$lmi_payment_desc."', retid='".
																			(isset($retid)?$retid:"")."' where id=".$row_order['id'];
							$query=_query2($update,"specification_redirect lmi");
						}else if ( $key == "account" ) {
							echo "<input type='hidden' name='$key' value='".str_replace(" ", "",$val)."'>";
						}else {
							echo "<input type='hidden' name='$key' value='$val'>";
													
						}
					}


				}  // формирование формы на отправку в мерчант
					//echo '<input type="submit">';
					
					if ( (substr($row_order['currin'] ,0,3)=="MCV") || (substr($row_order['currin'] ,0,2)=="LQ") ) {
						echo "<input type='hidden' name='operation_xml' value='$xml_encoded' />
    				  <input type='hidden' name='signature' value='$lqsignature' />";
					}
					echo '</form>';
				}

	}

?>
</body>