<?php require_once('Connections/ma.php');
require_once($serverroot.'function.php'); 
$WmLogin_WMID='';

$urlid['register']="33035c26-1258-4b1f-ba6a-9c9300cacf08"; 
if ( isset ( $_POST['WmLogin_Ticket'] ) ) {
$testticket=preg_match('/^[a-zA-Z0-9\$\!\/]{32,48}$/i', $_POST['WmLogin_Ticket']); 
if($_POST['WmLogin_UrlID']==$urlid['register'] && $testticket==1) 
	{ // Продолжаем выполнение скрипта // ... 
	
	$xml=" <request>  <siteHolder>219391095990</siteHolder>  <user>".$_POST['WmLogin_WMID']."</user>  <ticket>".$_POST['WmLogin_Ticket']."</ticket>  <urlId>".$urlid['register']."</urlId>  <authType>".$_POST['WmLogin_AuthType']."</authType>  <userAddress>".$_POST['WmLogin_UserAddress']."</userAddress> </request> "; 
	
	
$resxml=_GetAnswer_WMLogin($xml);	
	
	// Разбираем XML-ответ 
	$xmlres = simplexml_load_string($resxml); 
	if(!$xmlres) echo "Не получен XML-ответ"; 
	$result=strval($xmlres->attributes()->retval);
	// Если результат не равен 0 - прерываем и выдаем ошибку 
	if($result!=0) {echo "Тикет ошибочный :("; }else { 
							$WmLogin_WMID=$_SESSION['WmLogin_WMID']=$_POST['WmLogin_WMID']; 
							// Выполняем необходимые действия, 
							// например, авторизуете пользователя, начинаете сессию и т.д. // ... 
							} 

	} 
else echo "=== Ошибка при получении тикета ==="; 

}






if ( isset($_GET['clid']) && isset($_GET['rnd']) ) {
		
		$clupdate = "SELECT clients.activated FROM clients WHERE 
			clients.clid='".$_GET['clid']."' AND clients.rnd='".$_GET['rnd']."';";
		$clupdate_row=_query($clupdate, "register.php 1");
		$num_clientrow=$clupdate_row->num_rows;
		$clupdate_row=$clupdate_row->fetch_assoc();
		if ($clupdate_row['activated'] == 1) {$only_mess="Ваша учетная запись уже активирована.<br />
			Авторизоваться в системе можно <a href='".$siteroot."login.php'>здесь</a>";}
		else {
			$clupdate = "UPDATE clients SET activated=1 WHERE clients.clid='".$_GET['clid']."';";
			$clupdate = _query($clupdate, "register.php 2");
			if (mysqli_affected_rows($GLOBALS['ma']) != 0) {$only_mess="Ваш аккаунт успешно активирован.<br />
				Авторизоваться в системе можно <a href='".$siteroot."login.php'>здесь</a>";
			}
		else {$only_mess='Ваша учетная запись не активирована. Если Вам не пришло письмо с инструкцией о том как проделать активацию (многие почтовые службы расценивают письма как спам), обратитесь в <a href="'.$siteroot.'contacts.php">службу поддержки</a>';}
		}
}
if ( isset($_POST['form']) && $_POST['form']=='forgot' ) {
	if ( isset($_POST['r']) && $_POST['r']==$_POST['re']-76  ){
	//if ( isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['keystring'] ){
		
		$clientrow = "SELECT clients.passmd5, clients.nikname, clients.email, clients.clid FROM clients WHERE clients.email='".$_POST['email']."'";
		$clientrow=_query($clientrow, "partner_register.php 3");
		$row_client = $clientrow->fetch_assoc();	
		$num_clientrow=$clientrow->num_rows;
		if ($num_clientrow>0){
			$rnd = substr(md5(uniqid(microtime(), 1)).getmypid(),1,8);
			send_mail($row_client['email'], "Здравствуйте,
Ваш новый пароль в системе Обменов.ком - ".$rnd."
Изменить его на более удобный Вы можете в своем личном кабинете http://obmenov.com/register.php

--
С уважением, ".$shop_email, 'Обменов.ком: Запрос пароля', $shop_email, $shop_name);
			$only_mess='Новый пароль отправлен по указанному вами адресу электронной почты.';
			$query="UPDATE clients SET passmd5='".md5(md5($rnd))."' WHERE clid='".$row_client['clid']."'";
			$update=_query ($query, "register.php 44");
		}else {	
			$mess='Пользователь с указанным e-mail не найден.';
		}
	
	}else{
		$mess="Вы ввели неверно символы с картинки.";
	}
	//unset($_SESSION['captcha_keystring']);
}

$purse_lr = isset ($_POST['purse_lr']) ? ", purse_LRUSD='". $_POST['purse_lr'] . "'": "";
$purse_pmusd = isset ($_POST['purse_pmusd']) ? ", purse_PMUSD='". $_POST['purse_pmusd'] . "'": "";
$purse_pmeur = isset ($_POST['purse_pmeur']) ? ", purse_PMEUR='". $_POST['purse_pmeur'] . "'": "";
$purse_z = isset ($_POST['purse_z']) ? ", purse_z='". $_POST['purse_z'] . "'": "";
$purse_r = isset ($_POST['purse_r']) ? ", purse_r='". $_POST['purse_r'] . "'": "";
$purse_u = isset ($_POST['purse_u']) ? ", purse_u='". $_POST['purse_u'] . "'": "";
$purse_e = isset ($_POST['purse_e']) ? ", purse_e='". $_POST['purse_e'] . "'": "";

if ( (isset($_POST['reg']) && $_POST['reg']=='ok') && (!isset($_SESSION['authorized'])) ) {
// *** Redirect if email exists	
  $LoginRS__query = sprintf("SELECT email FROM clients WHERE email=%s", GetSQLValueString($_POST['email'], "text"));
  $LoginRS=_query($LoginRS__query, "register.php 4");
  $emailFoundUser = $LoginRS->num_rows;
	
// *** Redirect if username exists	
  $MM_dupKeyRedirect="register.php";
  $loginUsername = isset($_POST['nikname']) ? $_POST['nikname'] : "";
  $LoginRS__query = sprintf("SELECT nikname FROM clients WHERE nikname=%s", GetSQLValueString($loginUsername, "text"));
  $LoginRS=_query($LoginRS__query, "register.php 5");
  $loginFoundUser = $LoginRS->num_rows;

  if($emailFoundUser){
    $mess='E-mail <strong>'. htmlspecialchars($_POST["email"]) .'</strong> был указан <br />
	при регистрации другого пользователя. Попробуйте другой.';
  }
  //if there is a row in the database, the username was found - can not add the requested username
  if($loginFoundUser){
    $mess='Пользователь <strong>'. htmlspecialchars($_POST["nikname"]) .'</strong> уже существует. Попробуйте другой логин.';
  }
  
}


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
if ( isset($_POST["reg"]) && ($_POST["reg"] == "ok") && isset($_SESSION['authorized']) ) {
	if ( isset($_POST['changepwd'])) {
		if ($_POST['changepwd']=='Yes' ){
			$pass="pass='".$_POST['pass']."', ";
			$passmd5="passmd5='".md5(md5($_POST['pass']))."', ";
		}else{
			$pass=''; $passmd5='';
		
		}
	}
	else {
		if ( isset($_POST['pass']) ) {		
			$pass="pass='".$_POST['pass']."', ";
			$passmd5="passmd5='".md5(md5($_POST['pass']))."', ";
		}else {
			$pass=''; $passmd5='';
		}
	}
	//if ( isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['keystring'] ){
	if ( isset($_POST['r']) && $_POST['r']==$_POST['re']-76  ){
		$rnd = strtoupper(substr(md5(uniqid(microtime(), 1)).getmypid(),1,8));
		$condition=" clients.nikname='".$_SESSION['AuthUsername']."';";
		if ( isset($_SESSION['WmLogin_WMID']) ) {$condition="clients.wmid='".$_SESSION['WmLogin_WMID']."';";}
		$clientSQL = "SELECT id, email FROM clients WHERE ".$condition ;
		//maildebugger($clientSQL);
		 $clientSQL = _query ($clientSQL, "register.php 6");
		  $row_clientSQL = $clientSQL->num_rows;
		  $clientSQL = $clientSQL->fetch_assoc ();	
		  //maildebugger("'".$clientSQL['email']."'='".$_POST['email']."'");
		  if ( trim($clientSQL['email']) != trim($_POST['email']) ) //проверка изменения эл. почты
		  { 		//maildebugger(print_r($_SESSION,1)."1=".$clientSQL['email']." 2=".$_POST['email']);				
		  			send_mail($_POST['email'],
"Здравствуйте,

Вы изменили адрес электронной почты.
Для того, чтобы повторно активировать ваш аккаунт в системе Обменов.ком пройдите по этой ссылке:
https://obmenov.com/register.php?sid=".$clientSQL['id']."&clid=".$clientid."&rnd=".$rnd."
Если вы не понимаете о чем идет речь, просто проигнорируйте это сообщение

-----
С уважением, ".$shop_email,
							"Обменов.ком: активация аккаунта", $shop_email, $shop_name);
						$mess='Вы изменили адрес электронной почты. Вам необходимо заново активировать ваш аккаунт. 
						Письмо с инструкциями выслано на новый адрес электронной почты.';
		  					$activated=', activated=0';

		  } else {$activated=''; $rnd='';}
		//".$pass." 
				
  $insertSQL = "UPDATE clients SET 
					   fname='".$_POST['fname']."', 
					   iname='".$_POST['iname']."', 
					   ".$passmd5." activated=1, 
					   wmid='".$WmLogin_WMID."' 
					   ".$purse_r.$purse_e.$purse_u.$purse_z.$purse_lr.$purse_pmusd.$purse_pmeur.",
					   phone='".$_POST["phone"]."', email='".$_POST["email"]."'".$activated.", 
					   rnd='".$rnd."' WHERE ".$condition;
  $Result1 = _query($insertSQL, "register.php 7");
  
  
	}else {$mess="Вы ввели неверно символы с картинки";}
	//unset($_SESSION['captcha_keystring']);
}else {
	if ( isset($_POST["reg"]) && ($_POST["reg"] == "ok") && (!isset($mess)) ) {
		//if ( isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['keystring'] ){
		if ( isset($_POST['r']) && $_POST['r']==$_POST['re']-76  ){
		  $clientSQL = "SELECT id FROM clients WHERE clid='".$clientid."' AND nikname = ''" ;
		  $clientSQL = _query ($clientSQL, "register.php 8");
		  $row_clientSQL = $clientSQL->num_rows;
		  $clientSQL = $clientSQL->fetch_assoc();
		  $rnd = strtoupper(substr(md5(uniqid(microtime(), 1)).getmypid(),1,8));
		  if ( $row_clientSQL == 1 ) {

					//pass=%s,
			   $insertSQL=sprintf("UPDATE clients SET fname='".$_POST['fname']."', iname='".$_POST['iname']."',
						nikname='".$_POST['nikname']."',
						passmd5='".md5(md5($_POST['pass']))."',
					   wmid='".$WmLogin_WMID."' 
					   ".$purse_r.$purse_e.$purse_u.$purse_z.$purse_lr.$purse_pmusd.$purse_pmeur."
					   ,rnd='".$rnd."',
					   activated=1,
					   phone='".$_POST["phone"]."', email='".$_POST["email"]."',
					   partnerid=".intval($partnerid).", ip='".$_SERVER['REMOTE_ADDR']."' 
					   WHERE clients.id=".$clientSQL['id']);
  				$Result1 = _query($insertSQL, "register.php 9");
				send_mail($_POST['email'], "Здравствуйте,
							 
Для того, чтобы активировать ваш аккаунт в системе Обменов.ком пройдите по этой ссылке:
https://obmenov.com/register.php?sid=".$clientSQL['id']."&clid=".$clientid."&rnd=".$rnd."
Если вы не понимаете о чем идет речь, просто проигнорируйте это сообщение

-------
С уважением, ".$shop_email,
							"Обменов.ком: активация аккаунта", $shop_email, $shop_name);
	  			$only_mess = "Регистрация прошла успешно. На электронный ящик ".htmlspecialchars($_POST['email']). " 
				отправлено письмо с инструкциями о том,	как активировать ваш аккаунт.<br />
				Примечание: многие почтовые службы могут расценить наши письма как спам. Если Вам не пришло письмо с инструкцией о том как проделать активацию, обратитесь в службу поддержки.";
				
				
			  
		  }
		  else {  
		  	$clid = isset($_COOKIE['clid']) ? $_COOKIE['clid'] : session_id();
				$purse_z = isset($_POST['purse_z']) ? ",'".$_POST['purse_z']."'": ",''";
				$purse_r = isset($_POST['purse_r']) ? ",'".$_POST['purse_r']."'": ",''";
				$purse_u = isset($_POST['purse_u']) ? ",'".$_POST['purse_u']."'": ",''";
			 	$purse_e = isset($_POST['purse_e']) ? ",'".$_POST['purse_e']."'": ",''";
				$purse_lr = isset($_POST['purse_lr']) ? ",'".$_POST['purse_lr']."'": ",''";
				$purse_pmusd = isset($_POST['purse_pmusd']) ? ",'".$_POST['purse_pmusd']."'": ",''";
				$purse_pmeur = isset($_POST['purse_pmeur']) ? ",'".$_POST['purse_pmeur']."'": ",''";			
			$insertSQL="INSERT INTO clients (`clid`, `fname`, `iname`,`nikname`, `passmd5`, `phone`, `wmid`, `purse_z`, 
									`purse_u`, `purse_r`, `purse_e`, `purse_PMUSD`, `purse_PMEUR`, purse_LRUSD, `email`, `rnd`, `partnerid`, 
									`ip`) 
								VALUES ('"
									.$clid."', '"
									.$_POST['fname']."', '"
									.$_POST['iname']."', '"
									.$_POST['nikname']."', '"
									.md5(md5($_POST['pass']))."', '"
									.$_POST['phone']."', '"
									.$WmLogin_WMID."' "
									.$purse_r.$purse_e.$purse_u.$purse_z.$purse_lr.$purse_pmusd.$purse_pmeur.
									",'".$_POST['email']."', '"
									.$rnd."'"
									.$pn.",'"
									.$_SERVER['REMOTE_ADDR']."');";
	   			$Result = _query($insertSQL, "register.php 10");
				send_mail($_POST['email'], 
"Здравствуйте,
Для того, чтобы активировать ваш аккаунт в системе Обменов.ком пройдите по этой ссылке:
https://obmenov.com/register.php?sid=".mysqli_insert_id($GLOBALS['ma'])."&clid=".$clid."&rnd=".$rnd."
Если вы не понимаете о чем идет речь, просто проигнорируйте это сообщение

------
С уважением, ".$shop_email,
							"Обменов.ком: активация аккаунта", $shop_email, $shop_name);
	  			$only_mess = "Регистрация прошла успешно. На электронный ящик ".htmlspecialchars($_POST['email']). " 
				отправлено письмо с инструкциями о том,	как активировать ваш аккаунт.<br />
				Примечание: многие почтовые службы могут расценить наши письма как спам. Если Вам не пришло письмо с инструкцией о том как проделать активацию, обратитесь в службу поддержки.";
		  }


}else {$mess="Вы ввели неверно символы с картинки";}
	//unset($_SESSION['captcha_keystring']);
}
}	
	if ( isset($_SESSION['authorized']) ) {
		$condition=" clients.nikname='".$_SESSION['AuthUsername']."'";
		//if ( isset($_SESSION['WmLogin_WMID']) ) {$condition="clients.wmid='".$_SESSION['WmLogin_WMID']."'";}
	}else {$condition=" 1=2 ";}
	$query_clientinfo = "SELECT clients.id, clients.fname, clients.iname, 
				clients.passmd5, clients.wmid, clients.purse_z, clients.purse_u, 
				clients.purse_r, clients.purse_e, 
				clients.purse_PMUSD, clients.purse_PMEUR, clients.purse_LRUSD,
				clients.email, clients.nikname, 
				clients.passport, clients.clid, clients.phone FROM clients WHERE ".
	$condition." ORDER BY date desc";
	$clientinfo = _query($query_clientinfo, "register.php 11");
	$row_clientinfo = $clientinfo->fetch_assoc();
	if ( isset($_POST['fname']) && strlen($_POST['fname'])>0 ) {$fname=htmlspecialchars($_POST['fname']);}else{$fname=$row_clientinfo['fname'];}
	if ( isset($_POST['iname']) && strlen($_POST['iname'])>0 ) {$iname=htmlspecialchars($_POST['iname']);}else{$iname=$row_clientinfo['iname'];}
	if ( isset($_POST['nikname']) && strlen($_POST['nikname'])>0 ) {$nikname=htmlspecialchars($_POST['nikname']);}else{$nikname=$row_clientinfo['nikname'];}
	if ( isset($WmLogin_WMID) && strlen($WmLogin_WMID)>0 ) {$wmid=htmlspecialchars($WmLogin_WMID);}else{$wmid=$row_clientinfo['wmid'];}
	if ( isset($_POST['purse_z']) && strlen($_POST['purse_z'])>0 ) {$z=htmlspecialchars($_POST['purse_z']);}else{$z=$row_clientinfo['purse_z'];}
	if ( isset($_POST['purse_u']) && strlen($_POST['purse_u'])>0 ) {$u=htmlspecialchars($_POST['purse_u']);}else{$u=$row_clientinfo['purse_u'];}
	if ( isset($_POST['purse_e']) && strlen($_POST['purse_e'])>0 ) {$e=htmlspecialchars($_POST['purse_e']);}else{$e=$row_clientinfo['purse_e'];}
	if ( isset($_POST['purse_r']) && strlen($_POST['purse_r'])>0 ) {$r=htmlspecialchars($_POST['purse_r']);}else{$r=$row_clientinfo['purse_r'];}
	if ( isset($_POST['purse_pmeur']) && strlen($_POST['purse_pmeur'])>0 ) {
		$purse_pmeur=htmlspecialchars($_POST['purse_pmeur']);}else{$purse_pmeur=$row_clientinfo['purse_PMEUR'];}
	if ( isset($_POST['purse_pmusd']) && strlen($_POST['purse_pmusd'])>0 ) {
		$purse_pmusd=htmlspecialchars($_POST['purse_pmusd']);
		}else{$purse_pmusd=$row_clientinfo['purse_PMUSD'];}
	if ( isset($_POST['purse_lr']) && strlen($_POST['purse_lr'])>0 ) {
		$purse_lr=htmlspecialchars($_POST['purse_lr']);}else{$purse_lr=$row_clientinfo['purse_LRUSD'];}
	if ( isset($_POST['phone']) && strlen($_POST['phone'])>0 ) {$phone=htmlspecialchars($_POST['phone']);}else{$phone=$row_clientinfo['phone'];}
	if ( isset($_POST['email']) && strlen($_POST['email'])>0 ) {$email=htmlspecialchars($_POST['email']);}else{$email=$row_clientinfo['email'];}
	
	if ( isset($_SESSION['authorized']) ){
		$changepwd=false;}
		else{
		$changepwd=true;}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Регистрация</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<meta name="description" content="..." />
		<meta name="keywords" content="..." />
        <link rel="shortcut icon" href="<?=$siteroot?>i/favico.ico"/>
        <script src="_main.js"></script>
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
        <style>
		<?php
       	if ( isset($_SESSION['AuthUsername']) ) {
		echo '.wrapper {background: url("i/wrapper'.$urlid['site_ext'].'-auth.jpg") center 0 no-repeat;}';
		}else{
		echo '.wrapper {background: url("i/wrapper'.$urlid['site_ext'].'.jpg") center 0 no-repeat;}';
		}
		?>
		</style>
		<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
<script type="text/javascript" src="fun.js"></script>
<script language="javascript">

function req(el,el2)
{
    document.getElementById(el+"_span").innerHTML='<img src="i/ajax-loader.gif" width="16" height="16" alt="Обработка" />';
	var r = gen(el,el2);
    if (el=="nikname") {sndReq(r, handlenik);}
    if (el=="email") {sndReq(r, handleemail);}
    if (el=="purse_z") {sndReq(r, handlez);}
	if (el=="purse_u") {sndReq(r, handleu);}
	if (el=="purse_e") {sndReq(r, handlee);}
	if (el=="purse_r") {sndReq(r, handler);}
	//handle(el);
}
function sndReq(req, handle)
{
	http = createRequestObject();
	http.open("get", req);
	http.onreadystatechange = handle;
	http.send(null);

	//http.send(null);
}
function gen(type, type2)
{
    if ( type2==null ) {
	var res = "checkticket.php?seed="+Math.round(100*Math.random()) + "&type="+type+"&string="+document.getElementById(type).value;
    }else {
	var res = "checkticket.php?seed="+Math.round(100*Math.random()) + "&type="+type+"&string="+document.getElementById(type).value+"&type2="+type2+"&string2="+document.getElementById(type2).value;	
	}
	return encodeURI(res);
	
}

function handlenik()
{
    if(http.readyState == 4)
    {document.getElementById("nikname_span").innerHTML=http.responseText;}
}
function handleemail()
{
    if(http.readyState == 4)
    {document.getElementById("email_span").innerHTML=http.responseText;}
}
function handlez()
{
    if(http.readyState == 4)
    {document.getElementById("purse_z_span").innerHTML=http.responseText;}
}
function handler()
{
    if(http.readyState == 4)
    {document.getElementById("purse_r_span").innerHTML=http.responseText;}
}
function handleu()
{
    if(http.readyState == 4)
    {document.getElementById("purse_u_span").innerHTML=http.responseText;}
}
function handlee()
{
    if(http.readyState == 4)
    {document.getElementById("purse_e_span").innerHTML=http.responseText;}
}
function createRequestObject()
{
	var ro;

	if (window.XMLHttpRequest)
		ro = new XMLHttpRequest();
	else
	{
		ro = new ActiveXObject('Msxml2.XMLHTTP');
		if(!ro) 
			ro = new ActiveXObject('Microsoft.XMLHTTP');
	}

	return ro;
}

function makesubmit(){

<?php echo !isset($_SESSION['authorized']) ?  'if (document.form1.nikname.value.length == 0) { alert("Не указан логин");return(false);}': '' ; ?>
if ( document.form1.pass.value.length < 6 && document.form1.changepwd.checked ) { alert("Пароль не должен содержать менее 6 символов"); return(false); }
//if ( !/[,./;''[]-=+{}"":<>?]$/.test(document.form1.pass.value && !document.form1.pass.disabled) ) {alert("Пароль должен содержать цифры и латинские буквы");return(false);  }
if ( document.form1.pass.value != document.form1.retype.value <?=isset($_SESSION['authorized']) ? "&& document.form1.changepwd.checked" : ""?> ) { alert("Пожалуйста, подтвердите правильно пароль"); return(false); }
//if ( !/[0-9]{12}$/.test(d.$("wmid").value) ) {alert("Неправильно указан WMID");return(false);  }

if ( !/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(document.form1.email.value) ) { alert("Неправильно указан Email адрес");return(false); }

document.form1.submit();
}

function changepass(){
	<?php if ( isset($_SESSION['authorized']) ) { ?>
	if (document.form1.changepwd.checked) {
		document.form1.pass.disabled=false;
		document.form1.retype.disabled=false;}	
	else {
		document.form1.pass.disabled=true;
		document.form1.retype.disabled=true;

	}
	<?php } ?>
}

function forgot_submit(){

if ( !/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(document.forgot.email.value) ) { alert("Неправильно указан Email адрес");return(false); }

document.forgot.submit();
}

</script>

        
	</head>
	<body>
	    <div class="wrapper">
            <div class="wrapper-inn">

                <?php require_once("siti/inc_top.php");?>

                <div class="middle clear">

                    <!-- Start left column -->
                    <? require_once("siti/inc_left.php");?>
                    <!-- End left column -->

                    <!-- Start central column -->
                    
<?php if ( !isset($_REQUEST['forgot']) ) { ?>                    
                    <div class="c-col">
                        <!--div class="change"-->
                            <h1 align="center">Регистрация :: Персональные данные</h1><br />
<br />
<br />
                            <!--div class="change-form"-->
                             
 
                                
                            <!--/div-->
                        <!--/div-->
                        <div class="intro">
                        
                        
                        <div align="center">
                       <form action="<?=str_replace('"',"",$editFormAction);?>" method="post" name="form1" id="form1">
    <table align="center" width="500" cellspacing="4">
  <tr><Td align="center" id="head_small" colspan="2"><?php if ( isset($mess) ) {echo $mess;} ?><?php if ( isset($only_mess) ) {echo $only_mess;} ?></Td></tr>
    <tr>
      <td align="right" valign="top"><p>Фамилия:</p></td>
      <td colspan="2" class="form-normal"><input type="text" id="name" name="fname" value="<?php echo $fname;?>"/> </td>
    </tr>
    <tr>
      <td align="right" valign="top"><p>Имя:</p></td>
      <td colspan="2" class="form-normal"><input type="text" id="name" name="iname" value="<?php echo $iname;?>"/> </td>
    </tr>
    <tr>
      <td align="right"><p>Логин:<span style="color:red">*</span></p></td>
      <td class="form-normal">
      <?php if ( !isset($_SESSION['authorized']) ) {  ?>
	  <input name="nikname" id="nikname" type="text" size="20" maxlength="20" onChange="req('nikname')" value="<?=$nikname?>"/> <?php }else {echo $nikname;}?> <span id="nikname_span"></span>
      </td>
    </tr>

    <tr>
      <td align="right"><p>Пароль:<span style="color:red">*</span></p></td>
      <td class="form-normal"><input name="pass" type="password" id="pass" size="20" /> 
      <?php if ( isset($_SESSION['authorized']) ) {?><input name="changepwd" style="border:none"  type="checkbox" id="changepwd" onClick="changepass();" value="Yes" <?php echo $changepwd ? 'checked' : ''; ?>/>сменить пароль<?php } ?> </td>
    </tr>
    <tr>
      <td align="right" nowrap="nowrap"><p>Подтверждение пароля:<span style="color:red">*</span></p></td>
      <td class="form-normal"><input name="retype" id="retype" type="password" size="20" /> </td>
    </tr>
    <?php if ( $urlid['site_curr2']==1 ) { ?>
    <tr>
      <td align="right"><p>Webmoney ID:</p></td>
      <td class="form-normal"><input name="wmid" type="text" id="wmid" value="<?=$WmLogin_WMID?>" size="15" maxlength="12" disabled="disabled" />
	  <?php if ( !isset($_SESSION['WmLogin_WMID']) ) { ?> 
      <a href="https://login.wmtransfer.com/GateKeeper.aspx?RID=<?=$urlid['register']?>"> Авторизоваться</a>
      <?php } ?>
      </td>
    </tr>
    <tr valign="baseline">
      <td align="right"><p>Кошелёк Z:</p></td>
      <td class="form-normal"><input name="purse_z" type="text" id="purse_z" value="<?php echo $z;?>" size="15" maxlength="13" onChange="req('purse_z','wmid')"/>
      <span id="purse_z_span"></span></td>
    </tr>
    <tr valign="baseline">
      <td align="right"><p>Кошелёк U:</p></td>
      <td class="form-normal"><input name="purse_u" type="text" id="purse_u" value="<?php echo $u;?>" size="15" maxlength="13"  onChange="req('purse_u','wmid')"/>
      <span id="purse_u_span"></span></td>
    </tr>
    <tr valign="baseline">
      <td align="right"><p>Кошелёк R:</p></td>
      <td class="form-normal"><input name="purse_r" type="text" id="purse_r" value="<?php echo $r;?>" size="15" maxlength="13" onChange="req('purse_r','wmid')" />
      <span id="purse_r_span"></span></td>
    </tr>
    <tr valign="baseline">
      <td align="right"><p>Кошелёк E:</p></td>
      <td class="form-normal"><input name="purse_e" type="text" id="purse_e" value="<?php echo $e;?>" size="15" maxlength="13" onChange="req('purse_e','wmid')" /> 
      <span id="purse_e_span"></span></td>
    </tr>
    <?php }elseif ( $urlid['site_curr2']==2 ) { ?>
    <tr valign="baseline">
      <td align="right"><p>Кошелёк Perfectmoney USD:</p></td>
      <td class="form-normal"><input name="purse_pmusd" type="text" id="purse_pmusd" value="<?=$purse_pmusd;?>" size="15" maxlength="13"/></td>
    </tr>
    <tr valign="baseline">
      <td align="right"><p>Кошелёк Perfectmoney EUR:</p></td>
      <td class="form-normal"><input name="purse_pmeur" type="text" id="purse_pmeur" value="<?=$purse_pmeur;?>" size="15" maxlength="13"/></td>
    </tr>
    <tr valign="baseline">
      <td align="right"><p>Аккаунт LibertyReserve:</p></td>
      <td class="form-normal"><input name="purse_lr" type="text" id="purse_lr" value="<?=$purse_lr;?>" size="15" maxlength="13"/></td>
    </tr>    
    <?php } ?>
    <tr valign="baseline">
      <td align="right" nowrap="nowrap"><p>Контактный телефон:</p></td>
      <td class="form-normal"><input type="text" id="phone" name="phone" value="<?php echo $phone;?>" size="15" /> </td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right"><p>Email:<span style="color:red">*</span></p></td>
      <td class="form-normal" colspan="2"><input type="text" id="email" name="email" value="<?php echo $email;?>" size="25" onChange="req('email')" />
       <span id="email_span"></span></td>
    </tr>
    <tr>
      <td></td>
      <td><span style="font-size:10px; color:#666;">Некоторые почтовые службы (например, Mail.ru, inbox.ru) расценивают письма как спам. Если Вам не пришло письмо с активацией аккаунта, обратитесь в нашу слубжу поддержки.</span></td>
    </tr><?php /*?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">      
          <p>Введите текст на картинке:</p>
</td>
          <td class="form-normal">
          <input type="text" name="keystring"><br />
			<img id="kap" src="kcaptcha/index.php?<?php echo htmlspecialchars(session_name())?>=<?php echo htmlspecialchars(session_id())?>">
          <a href="javascript:rel();">
          <img src="i/reload.gif" alt="обновить" width="16" height="16" border="0" align="middle" /></a>
 
          </td></tr><?php */?>
	<tr><td align="right" class="cabinet-text"><strong><?php echo $r1=rand(3,15);?> + <?php echo $r2=rand(3,15);?> = </strong></td>
             <td><input type="text" name="r" value="" size="7" /><input type="hidden" value="<?=($r1+$r2+76);?>" name="re" /></td></tr>		 
              
    <tr><td></td><td><span style="font-size:10px; color:#666;">Данные, отмеченные знаком * обязательны к заполнению</span></td></tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><br />

      <?php if ( isset($_SESSION['authorized']) ){ ?>
      <input class="button1" type="button" value="Сохранить" onclick="makesubmit();"/>
      <?php }else{ ?>
      <input  class="button1" type="button" value="Регистрация" onclick="makesubmit();"/><br />
		<?php /*?>Не хотите регистрироваться? Используйте безопасный 
        <a href="https://login.wmtransfer.com/GateKeeper.aspx?RID=47025bb6-5500-48af-8654-9c9000ff2f76">вход сервиса Login.Webmoney</a><?php */?>      
      <?php } ?>
      </td>
    </tr>
    
  </table>
  <input type="hidden" name="reg" value="ok" />
</form> 
         </div>
<br />
<br />
   <a name="info"></a>
      <h1>Преимущества регистрации:</h1>
	<ul>
      <li>Мы настоятельно рекомендуем Вам зарегистрироваться, чтобы пользоваться всеми преимуществами сервиса!<br />
      Регистрируйтесь, делайте покупки в нашем магазине  и совершайте обмены с еще большими скидками! Теперь скидки по этим услугам суммируются и общая скидка накапливается прогрессивно!<br />
Каждый клиент после регистрации получает скидку от нашей комиссии на все услуги обменного пункта и магазина, которая растет пропорционально объемам совершенных операций. Только зарегистрированным клиентам мы предлагаем различные бонусы и подарки. Не упустите этот шанс!</li>
     <li>Максимальная эффективность действий и защита от случайных ошибок!<br />
Вам больше нет необходимости постоянно вводить номера ваших кошельков, банковские реквизиты и т. п. Система теперь будет делать это за Вас! Кроме того Вы сэкономите большую часть времени, которое можно потратить на более важные вещи. Вы не потеряете свои средства только из-за того, что ввели неправильные платежные реквизиты и Ваши деньги ушли неизвестно куда. Все данные, которые Вы вводите всегда останутся конфиденциальными.</li>

<li>История операций.<br />Все ваши заявки и их статус всегда можно просмотреть в истории операций в личном кабинете пользователя. В личном кабинете также видны данные по текущим скидкам, сумме обменов и остатку для перехода на следующий уровень скидки.</li>

<li> <a href="register.php">Регистрируйтесь</a> уже сейчас!</li></ul>
                <br />
<br />
        
   
	<table width="550" border="0"><tr><td align="center">
     <h2><img src="<?=$siteroot?>i/icq-online.png" width="10" height="10" /> Обмены</h2>
    <table border="0" align="center" cellpadding="4" cellspacing="0" width="250">
  <tr>
    <td align="center" class="td_head" height="30" rowspan="2"></td>
    <td colspan="2" align="center" valign="middle" class="td_head">Сумма обменов</td>
    <td align="center" width="50" class="td_head" rowspan="2">Скидка от нашего заработка</td>
  </tr>
  <tr>
    <td align="center" class="td_head">От</td>
    <td align="center" class="td_head">До</td>
    

  </tr>
<?php
	$discount_table_query = 'SELECT * FROM discount';
	$discount_table = _query($discount_table_query,'cabinet.php 5');
	
while ($discount_table_row = $discount_table->fetch_assoc()) {  ?>
    <tr>
      <td height="20" align="left"> </td>
      <td height="20" align="center"> <?=$discount_table_row['value']; ?></td>
      <td align="center"> <?=$discount_table_row['value_till']; ?></td>
      <td align="center"> <b><?=(($discount_table_row['disc']-1)*100); ?>%</b></td>
    </tr>
<?php	};	?>
	<tr><td height="30"></td></tr>
    </table>

    </td>
    <td align="center" width="275">
    
     <h2><img src="<?=$siteroot?>i/icq-online.png" width="10" height="10" /> Покупки</h2>
    <table border="0" align="center" cellpadding="4" cellspacing="0" width="250">
  <tr>
    <td align="center" class="td_head" height="30" rowspan="2"></td>
    <td colspan="2" align="center" valign="middle" class="td_head">Общая сумма покупок</td>
    <td align="center" width="50" class="td_head" rowspan="2">Скидка от нашего заработка</td>
  </tr>
  <tr>
    <td align="center" class="td_head">От</td>
    <td align="center" class="td_head">До</td>
    

  </tr>
<?php $discount_table_query = 'SELECT * FROM prepaid_discount';
	$discount_table = _query($discount_table_query,'cabinet.php 5');
while ($discount_table_row = $discount_table->fetch_assoc()) { ?>
    <tr>
    <td height="20" align="left"> <?php #$discount_table_row['descr']; ?></td>
      <td  height="20" align="center"> <?=$discount_table_row['value']; ?></td>
      <td align="center"> <?=$discount_table_row['value_till']; ?></td>
      <td align="center"> <b><?=(($discount_table_row['disc']-1)*100); ?>%</b></td>
    </tr>
<?php	};	?>
	<tr><td height="30"></td></tr>
    </table>


    </td>
    </tr></table><br /><br />


    <h1 align="center">Скидки по обменам и покупкам суммируются.<br />
* - максимальная скидка на услуги сервиса составляет 30% от нашего заработка.</h1>


 <?php } //конец формы регистрации/редактирования

if ( isset($_REQUEST['forgot']) ) {
?>
                    <div class="c-col">


                            <h1 align="center">Восстановление пароля</h1><br />
<br />
<br />
                        <div class="intro">

 <form action="<?=$editFormAction;?>" method="post" name="forgot" id="forgot">
                            <div align="center">
              <table align="center">
                                <tr>
                  <td align="center" colspan="2"><?=isset($mess) ? $mess : "" ?>
                  <?=isset($only_mess) ? $only_mess."<br /><br /><br />" : "" ?></td>
                </tr>
                                <tr>
                  <td align="right" nowrap="nowrap">Введите e-mail, указанный при регистрации:&nbsp;&nbsp;</td>
                  <td class="form-normal"><input type="text" name="email" id="email"/></td>
                </tr>
                                <?php /*?><tr>
                  <td valign="top" align="right" nowrap="nowrap"> Введите текст на картинке:&nbsp;&nbsp;</td>
                  <td valign="middle" class="form-normal"><input type="text" name="keystring">
                                    <br />
                                    <img id="kap" src="kcaptcha/index.php?<?=htmlspecialchars(session_name())?>=<?=htmlspecialchars(session_id());?>"> <a href="javascript:rel();"><img src="i/reload.gif" width="16" height="16" border="0" /></a></td>
                </tr><?php */?>
		<tr><td align="right" class="cabinet-text"><strong><?php echo $r1=rand(3,15);?> + <?php echo $r2=rand(3,15);?> = </strong></td>
             <td><input type="text" name="r" value="" size="7" /><input type="hidden" value="<?=($r1+$r2+76);?>" name="re" /></td></tr>
                
                
                <tr><td></td><td><input class="button1" type="submit" value="Продолжить"/></td></tr>
                              </table>
            </div>
                            <input type="hidden" name="form" value="forgot" />
                          </form>	

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