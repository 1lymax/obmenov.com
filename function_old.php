<?php 

$num_row_client=0;
$row_discount=1;
$courses = array();
$percent_for_courses=0.005;
$header='MIME-Version: 1.0' . "\n";
$header .='Content-type: text/html; charset=koi8-r' . "\n";
$header .='From: Obmenov.com <support@obmenov.com>' . "\n" .
    'Reply-To: Obmenov.com <support@obmenov.com>' . "\n".
	'Return-Path: Obmenov.com <support@obmenov.com>' . "\n";
$header .='X-Mailer: PHP/' . phpversion();



session_cache_expire(20);
  
  foreach ($_POST as $key => $value) {
    $_POST[$key] = mysql_real_escape_string($value);
	$_POST[$key]=str_ireplace("<script>","",$_POST[$key]);
	$_POST[$key]=str_ireplace("</script>","",$_POST[$key]);
	
  }

  //This stops SQL Injection in GET vars
  //$get=serialize($_GET);
  //maildebugger($get);
  foreach ($_GET as $key => $value) {
    $_GET[$key]=mysql_real_escape_string($value);
	$_GET[$key]=str_ireplace("<script>","",$_GET[$key]);
	$_GET[$key]=str_ireplace("</script>","",$_GET[$key]);
  } 
  
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

if ( isset ( $_POST['WmLogin_Ticket'] ) ) {
$testticket=preg_match('/^[a-zA-Z0-9\$\!\/]{32,48}$/i', $_POST['WmLogin_Ticket']); 

if($_POST['WmLogin_UrlID']==$urlid['index.php'] && $testticket==1) 
	{ // Продолжаем выполнение скрипта // ... 
	
	$xml=" <request>  <siteHolder>219391095990</siteHolder>  <user>".$_POST['WmLogin_WMID']."</user>  <ticket>".$_POST['WmLogin_Ticket']."</ticket>  <urlId>".$urlid['index.php']."</urlId>  <authType>".$_POST['WmLogin_AuthType']."</authType>  <userAddress>".$_POST['WmLogin_UserAddress']."</userAddress> </request> "; 
	

$resxml=_GetAnswer_WMLogin($xml);	
	
	// Разбираем XML-ответ 
	$xmlres = simplexml_load_string($resxml); 
	if(!$xmlres) echo "Не получен XML-ответ"; 
		$result=strval($xmlres->attributes()->retval);
	// Если результат не равен 0 - прерываем и выдаем ошибку 
		if($result!=0) {
			echo "Тикет ошибочный :("; 
			unset($_SESSION['WmLogin_WMID']);
			
		}else { 
			$WmLogin_WMID=$_SESSION['WmLogin_WMID']=$_POST['WmLogin_WMID']; 
							// Выполняем необходимые действия, 
							// например, авторизуете пользователя, начинаете сессию и т.д. // ... 
		} 

	} 

}
//if ( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']=="91.192.131.43" ) {
//$closed_exchange=true;
//}else{
$closed_exchange=0;
//}
//$_SESSION['WmLogin_WMID']="19391095990";
if ( isset($_SESSION['WmLogin_WMID']) ) {
	$select="select wmid from closed_exch where wmid='".$_SESSION['WmLogin_WMID']."'";
	$query=_query($select, "");
	$closed_exchange=(mysql_num_rows($query)>0 ? 1 :0);
}
$pn=", 299";
$partnerid=299;
if ( isset($_COOKIE['pn']) ) {
	$pn=", ".(int)htmlspecialchars($_COOKIE['pn']);
	$partnerid=(int)htmlspecialchars($_COOKIE['pn']);
	}
$clientid = ( isset($_COOKIE['clid']) ) ? substr(htmlspecialchars($_COOKIE['clid']),0,36) : '' ;
// логирование перехода

//if ( $_POST ) {

	//$post = "INSERT LOW_PRIORITY INTO post (`post`, `scrname`, `clid`) VALUES ('".str_replace(">","",print_r($_POST,1))."', '"
	//																		 .$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."', '"
	//																		 .$clientid."');";
	//$post_insert=_query($post, " insert_post 1");
//}

if ( isset($_GET['pn']) ) {
	setcookie("pn",htmlspecialchars($_GET['pn']),time()+60*60*24*60);
	$partnerid=(int)htmlspecialchars($_GET['pn']);
	$pn=" ,".(int)htmlspecialchars($_GET['pn']);
	}
if ( isset($_SERVER['QUERY_STRING']) ) {
	
	$useragent =  isset ($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
	if ( strpos($referer, "webmoney.ru/rus/cooperation/exchange/onlinexchange")!=0 ) {
		setcookie ("pn",355,time()+60*60*24*60);
		$pn=" , 355";
		$partnerid=355;
	}	

	if ( strpos($referer, "megastock.ru")!=0 ) {
		setcookie ("pn",298,time()+60*60*24*60);
		$pn=" , 298";
		$partnerid=298;
	}
	

	$clid= mysql_real_escape_string(isset($_COOKIE['clid']) ? substr(htmlspecialchars($_COOKIE['clid']),0,36) : session_id());
	
	$_SESSION['ref']=$referer;

	$insertSQL = "INSERT LOW_PRIORITY INTO referer (ref, scrname, post, clid, ip, agent, partnerid) VALUES (".
				"'".$referer."',".
				"'".($_SERVER['SCRIPT_NAME'].(strlen($_SERVER['QUERY_STRING'])!=0?"?".mysql_real_escape_string($_SERVER['QUERY_STRING']):""))."',".
				"'".(count($_POST)!=0? str_replace(">","",print_r($_POST,1)):"")."',".
				"'".$clid."',".
				"'".$_SERVER['REMOTE_ADDR']."',".
				"'".mysql_real_escape_string($useragent)."',".
				intval($partnerid).
				")";

	if ( strpos($useragent, "Yahoo") || strpos($useragent, "Yandex") || 
		strpos($useragent, "Google") || strpos($useragent, "Bot") || 
		strpos($useragent, "Crawler") || strpos($useragent, "Rambler") ) {
		$dont_insert_client=1;
	}
	else
	{
		$Result = _query2($insertSQL, 'function.php 1');
	}
// конец логирование перехода
}

if ( !isset($_POST['LMI_PREREQUEST']) && !isset($_POST['LMI_SYS_TRANS_NO']) ){
//проверка и установка client id
if ( !isset($_SESSION['clid']) ) {
if (!isset($_COOKIE['clid']) && !isset($_SESSION['clid_num']) ){
	$check_client="SELECT id FROM clients WHERE clients.clid='".session_id()."';";
	$check_client_sql=_query($check_client, "function.php 1.5 ");
	$numrows_check_client=mysql_num_rows($check_client_sql);
	if ( $numrows_check_client !=1 && !isset($dont_insert_client) ) {
		$clientid_query = sprintf("INSERT INTO clients (clid, partnerid,comment) VALUES (%s".$pn.",'".$_SERVER['SCRIPT_NAME']."')",
							GetSQLValueString(session_id(),"text"));

		_query($clientid_query, 'function.php 2');
	}
	if ( isset($_COOKIE['PHPSESSID']) ){
	setcookie("clid",htmlspecialchars($_COOKIE['PHPSESSID']),time()+60*60*24*60);}
}else{
	$clientrow = "SELECT clients.id, clients.name, clients.wmid, clients.purse_z, clients.purse_u, 
				clients.purse_r, clients.purse_e, clients.email, clients.bank, clients.passport, 
				clients.clid, clients.partnerid FROM clients WHERE clients.clid='".substr($_COOKIE['clid'],0,36)."'";
	$clientrow=_query($clientrow, 'function.php 3');
	$row_client = mysql_fetch_assoc($clientrow);
	if ( mysql_num_rows($clientrow)==0 && !isset($dont_insert_client) ){
		date_default_timezone_set('Europe/Helsinki');
		$clientid_query = sprintf("INSERT INTO clients (clid, date,comment) VALUES (%s, %s,'".$_SERVER['SCRIPT_NAME']."')",
							GetSQLValueString(substr($_COOKIE['clid'],0,36),"text"),
							GetSQLValueString(date("Y-m-d H:i:s"), "date"));
		_query($clientid_query, 'function.php 4');
			$clientrow = "SELECT clients.id, clients.name, clients.wmid, clients.purse_z, clients.purse_u, clients.purse_r, clients.purse_e, clients.email, clients.bank, clients.passport, clients.clid, clients.partnerid FROM clients WHERE clients.clid='".substr($_COOKIE['clid'],0,36)."'";
	$clientrow=_query($clientrow, 'function.php 4');
	$row_client = mysql_fetch_assoc($clientrow);
		
		}
$_SESSION['clid']=$row_client['clid'];
$_SESSION['clid_num']=$row_client['id'];
$_SESSION['partnerid']=$row_client['partnerid'];
	
}


}else{
	/*
	$clid = isset($_COOKIE['clid']) ? htmlspecialchars($_COOKIE['clid']) : session_id();
	$select="SELECT clients.id, clients.partnerid FROM clients WHERE clients.clid='".$clid."';";
	$query=_query($select, 'function.php 5');
	$row=$query->fetch_assoc();
	$clid_num=$row['id'];
	if ($row['partnerid'] !=0) {$partnerid = $row['partnerid'];}
	$_SESSION['clid']=$row['clid'];
	$_SESSION['clid_num']=$row['id'];
	$_SESSION['partnerid']=$row['partnerid'];
	*/
//end client id 
}
}

//курс доллара
require_once($serverroot."siti/amounts.php");
require_once($serverroot."siti/courses.php");


		$select="select name,type from currency where active2=0 "; 
		
		//where active=1";
		$query1=_query($select, "");
		$query2=_query($select, "");
		
		while ( $row1=mysql_fetch_array($query1) ) {
			$query2=_query($select, "");
			while ( $row2=mysql_fetch_array($query2) ) {
				if ( !isset($courses[$row1['name']][$row2['name']]) ) {
					if ( !isset($courses[$row1['type']][$row2['type']]) ) {
						$courses[$row1['name']][$row2['name']]=1;
					}else{
						$courses[$row1['name']][$row2['name']]=$courses[$row1['type']][$row2['type']];
					}
				}
				if ( $courses[$row1['name']][$row2['name']]=='') {
						$courses[$row1['name']][$row2['name']]=1;			
				}
				//echo "courses['".$row1['name']."']['".$row2['name']."']".$courses[$row1['name']][$row2['name']]."<br />";
			}
		
		}
$closed_directions=array();
if ( isset($_SESSION['AuthUsername']) ) {
	$select="SELECT clients.id, clients.name, clients.nikname, clients.wmid, clients.email, clients.clid, clients.phone FROM clients WHERE clients.nikname='".$_SESSION['AuthUsername']."' ";
	$query = _query ($select, "function.php 18");
	$client=$query->fetch_assoc();
	$clientid_predel=$client['id'];
	$num_row_client=mysql_num_rows($query);
	$_SESSION['clid_num']=$client['id'];
	$_SESSION['clid']=$client['clid'];
	
	$select="select currin, currout from closed_exch where clid='".$client['clid']."'";
	//maildebugger($select);
	$query=_query($select,"");
	$i=0;
	while ( $row=$query->fetch_assoc() ) {
		$closed_directions[$i]=$row['currin']."-".$row['currout'];
		$i++;
	}
	
}else{
	$clientid_predel=0;
}
$query_addon = "SELECT addon.id, addon.currname1, addon.currname2, addon.inactive,
		(select type from currency where addon.currname1=currency.name) as type1, 
		(select type from currency where addon.currname2=currency.name) as type2, 
		addon.value, addon.date FROM addon where  clientid=0 and 
						(inactive=0 or ( select 1 from closed_exch where closed_exch.currin=addon.currname1 
						and closed_exch.currout=addon.currname2 and closed_exch.clid='".(isset($client['clid'])?$client['clid']:"")."')
						 or ".$closed_exchange.") order by date asc ";
$addon = _query($query_addon, 'function.php 16');
$row_addon = mysql_fetch_assoc($addon);
$money = array();

$query_currency = "SELECT currency.name, currency.extname, type FROM currency where 1=1 and currency.server ".$urlid['curr']."
	ORDER BY name desc";
	//where active=1 
	//ORDER BY name desc";
	//	
$currency = _query($query_currency, 'function.php 17');

do {
	if ( $clientid_predel!=0 ) {
		$select="select value from addon where clientid=".$clientid_predel."
							and currname1='".$row_addon['currname1']."'
							and currname2='".$row_addon['currname2']."' and 
							(inactive=0 or ( select 1 from closed_exch where closed_exch.currin=addon.currname1 
															and closed_exch.currout=addon.currname2 and closed_exch.clid='".(isset($client['clid'])?$client['clid']:"")."'))";
		//maildebugger($select);					
		$query=_query($select, "detail.php 11");
		if ( mysql_num_rows($query)!=0 ) {
 
 			$row=$query->fetch_assoc();
			$base_value=$row['value'];
			$addon_value=1;
		}else{
			$base_value=$row_addon['value'];
			$addon_value=0;
		}
	}else{
		$base_value=$row_addon['value'];
		$addon_value=0;
	}
	$in=$row_addon['currname1']; $out=$row_addon['currname2'];
if (!$base_value==NULL){
	$money[$in][$out]['curr1']=$row_addon['currname1'];
	$money[$in][$out]['curr2']=$row_addon['currname2'];
	$money[$in][$out]['inactive']=$row_addon['inactive'];
	//$money[$in][$out]['extname1']=$row_addon['extname1'];
	//$money[$in][$out]['extname2']=$row_addon['extname2'];	
	$money[$in][$out]['value']=round($base_value,3);
	$money[$in][$out]['date']=$row_addon['date'];
	//$dtf=dateformat($row_addon['date']);
	//$money[$in][$out]['date']=mktime($dtf['y'],$dtf['m'],$dtf['d'],$dtf['h'],$dtf['mi'],$dtf['s']);
	$money[$in][$out]['addon_value']=$addon_value;
	
	if ($row_addon['date']>$money[$in][$out]['date']){ 
		$money[$in][$out]['date']=$row_addon['date'];
		$money[$in][$out]['value']=round($base_value,3);
		$money[$in][$out]['addon_value']=$addon_value;
		}
}
} while ($row_addon = mysql_fetch_assoc($addon));
//maildebugger(print_r($money,1));
//определение текущей скидки
require_once($serverroot."siti/class.php");
$shop=new shop();
$discount=$row_discount=$shop->discount();
//конец определение текущей скидки




function checkExch ($i1, $i2)
{
	switch ($i1) {
	case 'WMU':    	$in1=1;    	break;
	case 'WMZ':    	$in1=1;    	break;	
	case 'WMR':	    $in1=1;	    break;
	case 'WME':	    $in1=1;	    break;
	case 'UAH':    	$in1=3;    	break;
	case 'USD':    	$in1=3;    	break;
	case 'P24UAH':    	$in1=5;    	break;
	case 'P24USD':    	$in1=5;    	break;
	case 'CARDUAH':    	$in1=7;    	break;
	case 'MCVUAH': $in1=9; break;
	case 'MCVUSD': $in1=9; break;
	case 'MCVRUR': $in1=9; break;
	case 'SMS': $in1=11; break;
	}
	switch ($i2){
	case 'WMU':    	$in2=1.1;    	break;
	case 'WMZ':    	$in2=1.1;    	break;	
	case 'WMR':	    $in2=1.1;	    break;
	case 'WME':	    $in2=1.1;	    break;
	case 'UAH':    	$in2=3.3;    	break;
	case 'USD':    	$in2=3.3;    	break;
	case 'P24UAH':    	$in2=5.5;    	break;	
	case 'P24USD':    	$in2=5.5;    	break;
	case 'CARDUAH':    	$in2=7.7;    	break;
	
	}	
	return $in1+$in2;
	
}


function send_mailhtml($to, $body, $subject, $fromaddress, $fromname, $attachments=false)
{
  $eol="\r\n";
  $mime_boundary=md5(time());
  $headers = "From: ".$fromname."<".$fromaddress.">".$eol;
  $headers .= "Reply-To: ".$fromname."<".$fromaddress.">".$eol;
  $headers .= "Return-Path: ".$fromname."<".$fromaddress.">".$eol;    // these two to set reply address
  $headers .= "Message-ID: <".time()."-".$fromaddress.">".$eol;
  $headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters
  $headers .= "Content-Type: text/html; charset='windows-1251'".$eol;
  $headers .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
  $msg = $body.$eol.$eol;

  # SEND THE EMAIL
  //ini_set(sendmail_from,$fromaddress);  // the INI lines are to force the From Address to be used !
  $mail_sent = mail($to, $subject, $msg, $headers);
  mail("support@obmenov.com", "Client: ".$subject, $msg, $headers);
 
  //ini_restore(sendmail_from);
 
  return $mail_sent;
}



function isZero($var) {
if (is_null($var)){return '';}
}

function array_sort_func($a,$b=NULL) {
   static $keys;
   if($b===NULL) return $keys=$a;
   foreach($keys as $k) {
      if(@$k[0]=='!') {
         $k=substr($k,1);
         if(@$a[$k]!==@$b[$k]) {
            return strcmp(@$b[$k],@$a[$k]);
         }
      }
      else if(@$a[$k]!==@$b[$k]) {
         return strcmp(@$a[$k],@$b[$k]);
      }
   }
   return 0;
}

function array_sort(&$array) {
   if(!$array) return (isset($keys) ? $keys : "");
   $keys=func_get_args();
   array_shift($keys);
   array_sort_func($keys);
   usort($array,"array_sort_func");       
} 

function _GetAnswer_WMLogin($xml){ // Инициализируем сеанс CURL 
	$ch2 = curl_init("https://login.wmtransfer.com/ws/authorize.xiface"); // В выводе CURL http-заголовки не нужны 
	curl_setopt($ch2, CURLOPT_HEADER, 0); // Возвращать результат, а не выводить его в браузер 
	curl_setopt($ch2, CURLOPT_RETURNTRANSFER,1); // Метод http-запроса - POST 
	curl_setopt($ch2, CURLOPT_POST,1); // Что передаем? 
	curl_setopt($ch2, CURLOPT_POSTFIELDS, $xml); // !!!!!! В следующей строке укажите путь // к корневому сертификату Login.WebMoney на ВАШЕМ сервере 
	curl_setopt($ch2, CURLOPT_CAINFO, "/var/www/webmoney_ma/data/www/obmenov.com/siti/ns.cer"); 
	curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, TRUE); // Выполняем запрос, ответ помещаем в переменную $result; 
	$result=curl_exec($ch2);
	//maildebugger($result);
	if(curl_errno($ch2)) echo "Curl Error number = ".curl_errno($ch2).", Error desc = ".curl_error($ch2)."<br>"; 
	curl_close($ch2); 
	return $result; 
}

function icqstatus_get_status2($number) {
        // Создаем подключение
        if( $curl = curl_init() ){
                // Задаем ссылку
                curl_setopt($curl,CURLOPT_URL,'http://www.icq.com/people/'.$number.'/');
                // Скачанные данные не выводить поток
                curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                // Скачиваем
                $out = curl_exec($curl);
                // search
            $var = strpos( $out, "online.gif"); 
        if( strpos( $out, "online.gif")!=FALSE || strpos( $out, "occupied.gif")!=FALSE )
                {
                        return true;
                }else{
                        return false;
                }
                // Закрываем соединение
                curl_close($curl);
        }
}


?>