<?php require_once('Connections/ma.php'); ?>
<?php require_once('function.php'); 

if ( isset($_GET['sid']) &&  isset($_GET['clid']) && isset($_GET['rnd']) ) {
		
		$clupdate = "SELECT partner.activated FROM partner WHERE partner.id=".(int)$_GET['sid']." 
		AND partner.clid='".substr($_GET['clid'],0,36)."' AND partner.rnd='".substr($_GET['rnd'],0,8)."';";
		$clupdate_row=_query($clupdate, 'partner_register.php 1');
		$num_clientrow=$clupdate_row->num_rows;
		$clupdate_row=$clupdate_row->fetch_assoc();
		if ($clupdate_row['activated'] == 1) {$only_mess="Ваша учетная запись уже активирована.<br />
			Авторизоваться в системе можно <a href='http://obmenov.com/partner_login.php'>здесь</a>";}
		else {
			$clupdate = "UPDATE partner SET activated=1 WHERE partner.id=".(int)$_GET['sid']." AND partner.clid='".substr($_GET['clid'],0,36)."';";
			$clupdate = _query($clupdate, 'partner_register.php 2');
			if (mysqli_affected_rows($GLOBALS['ma']) ==1) {$only_mess="Ваш аккаунт успешно активирован.<br />
				Авторизоваться в системе можно <a href='http://obmenov.com/partner_login.php'>здесь</a>";
			}
		else {$only_mess='Ваша учетная запись не активирована. Обратитесь в <a href="http://obmenov.com/contacts.php">службу поддержки</a>';}
		}
}
if ( isset($_POST['form']) && $_POST['form']=='forgot' ) {
	if ( isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['keystring'] ){
		$clientrow = "SELECT partner.passmd5, partner.nikname, partner.email, partner.clid FROM partner WHERE partner.email='".$_POST['email']."'";
		$clientrow=_query($clientrow, "partner_register.php 3");
		$row_client = $clientrow->fetch_assoc();	
		$num_clientrow=$clientrow->num_rows;
		if ($num_clientrow>0){
			$rnd = substr(md5(uniqid(microtime(), 1)).getmypid(),1,8);
			send_mail($row_client['email'], "Здравствуйте,
Ваш новый пароль в системе Обменов.ком - ".$rnd."
Изменить его на более удобный Вы можете в своем личном кабинете http://obmenov.com/partner.php

--
С уважением, ".$shop_email, 'Обменов.ком: Запрос пароля', $shop_email, $shop_name);
			$only_mess='Новый пароль отправлен по указанному вами адресу электронной почты.';
			$query="UPDATE partner SET passmd5='".md5(md5($rnd))."' WHERE clid='".$row_client['clid']."'";
			$update=_query ($query, "register.php 44");
		}else {	
			$mess='Пользователь с указанным e-mail не найден.';
		}
	}else{
		$mess="Вы ввели неверно символы с картинки.";
	}
	unset($_SESSION['captcha_keystring']);
}


if ( (isset($_POST['reg']) && $_POST['reg']=='ok') && (!isset($_SESSION['Partner_AuthUsername'])) ) {
// *** Redirect if email exists	
  $LoginRS__query = sprintf("SELECT email FROM partner WHERE email=%s", GetSQLValueString($_POST['email'], "text"));
  $LoginRS=_query($LoginRS__query, "partner_register.php 4");
  $emailFoundUser = $LoginRS->num_rows;
	
// *** Redirect if username exists	
  $MM_dupKeyRedirect="partner_register.php";
  $loginUsername = $_POST['nikname'];
  $LoginRS__query = sprintf("SELECT nikname FROM partner WHERE nikname=%s", GetSQLValueString($loginUsername, "text"));
  $LoginRS= _query($LoginRS__query, "partner_register.php 6");
  $loginFoundUser = $LoginRS->num_rows;

  if($emailFoundUser){
    $mess='E-mail <strong>'. htmlspecialchars($_POST["email"]) .'</strong> был указан при регистрации другого пользователя.<br />
 Попробуйте другой.';  }
  //if there is a row in the database, the username was found - can not add the requested username
  if($loginFoundUser){
    $mess='Пользователь <strong>'. htmlspecialchars($_POST["nikname"]) .'</strong> уже существует. Попробуйте другой логин.';  }
  
}


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ( isset($_POST["reg"]) && ($_POST["reg"] == "ok") && isset($_SESSION['Partner_AuthUsername']) ) {
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
	if ( isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['keystring'] ){
		$rnd = strtoupper(substr(md5(uniqid(microtime(), 1)).getmypid(),1,8));
		$clientSQL = "SELECT id, email FROM partner WHERE nikname='".$_SESSION['Partner_AuthUsername']."'" ;
		$clientSQL = _query ($clientSQL, "partner_register.php 7");
		$row_clientSQL = $clientSQL->num_rows;
		$clientSQL = $clientSQL->fetch_assoc ();
		if ( $clientSQL['email'] != $_POST['email'] ) //проверка изменения эл. почты
		  { 		


		  			send_mail($_POST['email'], $row_clientSQL." 
							 Здравствуйте,
Вы изменили адрес электронной почты
Для того чтобы повторно активировать ваш аккаунт в системе Обменов.ком пройдите по этой ссылке:
http://obmenov.com/partner_register.php?sid=".$clientSQL['id']."&clid=".$clientid."&rnd=".$rnd."
Если вы не понимаете о чем идет речь, просто проигнорируйте это сообщение

С уважением, ".$shop_email,
							"Обменов.ком: активация аккаунта", $shop_email, $shop_name);
						$mess='Вы сменили адрес электронной почты. Вам необходимо заново активировать ваш аккаунт. Письмо с инструкциями выслано на новый адрес электронной почты.';
		  					$activated=', activated=0';

		  } else {$activated=''; $rnd='';
		  $mess="Данные сохранены";}
		
	     $insertSQL = "UPDATE partner SET 
					   ".$passmd5." 
					   phone='".$_POST["phone"]."', email='".$_POST["email"]."'".$activated.", 
					   rnd='".$rnd."' WHERE partner.nikname='".$_SESSION['Partner_AuthUsername']."'"; 
		$Result1 = _query($insertSQL, "partner_register.php 8");

	}else {$mess="Вы ввели неверно символы с картинки";}
	unset($_SESSION['captcha_keystring']);
}
else {
	if ( isset($_POST["reg"]) && ($_POST["reg"] == "ok") && (!isset($mess)) ) {
		if ( isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['keystring'] ){
		  			$rnd = strtoupper(substr(md5(uniqid(microtime(), 1)).getmypid(),1,8));
		  $insertSQL="INSERT INTO partner (`clid`, `nikname`, `passmd5`, `phone`, `rnd`, `email`, `purse_z`, `purse_lr`) VALUES ('"
									.substr($_COOKIE['clid'],0,36)."', '"
									.substr($_POST['nikname'],0,15)."', '"
									.md5(md5($_POST['pass']))."', '"
									.substr($_POST['phone'],0,20)."', '"
									.$rnd."', '"
									.substr($_POST['email'],0,50)."',
									'".$_POST['purse_z']."',
									'".$_POST['purse_lr']."');";
	   		$Result = _query($insertSQL, "partner_register.php 9");
	  		send_mail($_POST['email'], "
Здравствуйте,
Для того чтобы активировать партнерский доступ в системе Обменов.ком пройдите по этой ссылке:
http://obmenov.com/partner_register.php?sid=".mysqli_insert_id($GLOBALS['ma'])."&clid=".htmlspecialchars($_COOKIE['clid'])."&rnd=".$rnd."
Если вы не понимаете о чем идет речь, просто проигнорируйте это сообщение

С уважением, ".$shop_email,
							"Обменов.ком: активация аккаунта", $shop_email, $shop_name);
	  		$only_mess = "Регистрация прошла успешно. На электронный ящик ".htmlspecialchars($_POST['email']). " отправлено письмо с инструкциями о том,
			как активировать ваш аккаунт.";
			
		}else {$mess="Вы ввели неверно символы с картинки";}
	unset($_SESSION['captcha_keystring']);};
}
	$query_clientinfo = "SELECT partner.id, partner.nikname, partner.passmd5, 
								partner.email, partner.purse_z, partner.clid, 
								partner.phone, partner.purse_lr FROM partner WHERE partner.clid='".$clid."' ORDER BY date desc";
	$clientinfo = _query($query_clientinfo, "partner_register.php 10");
	$row_clientinfo = $clientinfo->fetch_assoc();
	if ( isset($_POST['nikname']) && strlen($_POST['nikname'])>0 ) {$nikname=htmlspecialchars($_POST['nikname']);}else{$nikname=$row_clientinfo['nikname'];}
	if ( isset($_POST['phone']) && strlen($_POST['phone'])>0 ) {$phone=htmlspecialchars($_POST['phone']);}else{$phone=$row_clientinfo['phone'];}
	if ( isset($_POST['email']) && strlen($_POST['email'])>0 ) {$email=htmlspecialchars($_POST['email']);}else{$email=$row_clientinfo['email'];}
	if ( isset($_POST['purse_z']) && strlen($_POST['purse_z'])>0 ) {$purse_z=htmlspecialchars($_POST['purse_z']);}else{$purse_z=$row_clientinfo['purse_z'];}
	if ( isset($_POST['purse_lr']) && strlen($_POST['purse_lr'])>0 ) {$purse_lr=htmlspecialchars($_POST['purse_lr']);}else{$purse_lr=$row_clientinfo['purse_lr'];}
	if ( isset($_SESSION['Partner_AuthUsername']) ){
		$changepwd=false;}
		else{
		$changepwd=true;}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Партнерская программа</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<?php require_once($serverroot."Connections/meta.php"); ?>
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
        <script src="_main.js"></script>
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
   <script type="text/javascript" src="<?=$siteroot?>fun.js"></script>
<script language="javascript">

function makesubmit(){
	
<?php echo !isset($_SESSION['Partner_AuthUsername']) ?  'if (document.form1.nikname.value.length == 0) { alert("Не указан логин");return(false);}': '' ; ?>
if ( document.form1.pass.value.length < 6 && !document.form1.pass.disabled ) { alert("Пароль не должен содержать менее 6 символов"); return(false); }
//if ( !/[,./;''[]-=+{}"":<>?]$/.test(d.$("pass").value && !d.$("pass").disabled) ) {alert("Пароль должен содержать цифры и латинские буквы");return(false);  }
if ( document.form1.pass.value != document.form1.retype.value && !document.form1.pass.disabled ) { alert("Пожалуйста, подтвердите правильно пароль"); return(false); }
//if ( !/[0-9]{12}$/.test(d.$("wmid").value) ) {alert("Неправильно указан WMID");return(false);  }

if ( !/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(document.form1.email.value) ) { alert("Неправильно указан Email адрес");return(false); }


document.form1.submit();
}

function changepass(){
<?php if ( isset($_SESSION['Partner_AuthUsername']) ) { ?>

	if (d.$("changepwd").checked) {
		d.$("pass").disabled=false;
		d.$("retype").disabled=false;}	
	else {
		d.$("pass").disabled=true;
		d.$("retype").disabled=true;

	}

<?php } ?>

}
function forgot_submit(){

if ( !/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(d.$("email").value) ) { alert("Неправильно указан Email адрес");return(false); }

document.forgot.submit();
}

</script>      

        
	</head>
	<body onload="changepass();">
	    <div class="wrapper">
            <div class="wrapper-inn">

                <?php require_once("siti/inc_top.php");?>


                <div class="middle clear">

                    <!-- Start left column -->
                    <?php require_once("siti/inc_left.php");?>
                    <!-- End left column -->

                    <!-- Start central column -->
                    <div class="c-col">
<?php if ( !isset($_REQUEST['forgot']) ) { ?>
                            <h1 align="center">Регистрация партнера</h1><br /><br />
                            
<?php if ( isset($only_mess) ) {echo '<h1 align="center">'.$only_mess.'</h1>';}
	if ( isset($_GET['fail']) ) {echo '<h1 align="center">Направильно указан логин или пароль</h1>';}?>
                            
<br />
<br />
                   <div class="intro">
                        
                        
         <div align="center"> 
                        
     <form action="<?=str_replace('"',"",$editFormAction)?>" method="post" name="form1" id="form1">
  <table align="center">
  <tr><Td align="center" colspan="2"><h1><?php if ( isset($mess) ) {echo $mess;} ?></h1></Td></tr>
    <tr>
      <td nowrap="nowrap" align="right"><p>Логин:<span style="color:#F00">*</span>&nbsp;&nbsp;</p></td>
      <td class="form-normal">
      <?php if ( !isset($_SESSION['Partner_AuthUsername']) ) { ?>
	  <input name="nikname" id="nikname" type="text" value="<?=$nikname?>"/>
      <?php }else { echo $nikname; } ?> </td>
    </tr>
    <tr>
      <td nowrap="nowrap" align="right"><p>Пароль:<span style="color:#F00">*</span>&nbsp;&nbsp;</p></td>
      <td class="form-normal"><input name="pass" type="password" id="pass"/>
      <?php if ( isset($_SESSION['Partner_AuthUsername']) ) {?><input name="changepwd" style="border:none"  type="checkbox" id="changepwd" onClick="changepass();" value="Yes" <?php echo $changepwd ? 'checked' : ''; ?>/>сменить пароль<?php } ?> </td>
    </tr>
    <tr>
      <td nowrap="nowrap" align="right"><p>Подтверждение пароля:<span style="color:#F00">*</span>&nbsp;&nbsp;</p></td>
      <td class="form-normal"><input name="retype" id="retype" type="password"/> </td>
    </tr>
    <tr>
      <td nowrap="nowrap" align="right"><p>Контактный телефон:&nbsp;&nbsp;</p> </td>
      <td class="form-normal"><input type="text" id="phone" name="phone" value="<?php echo $phone;?>" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right"><p>WMZ-кошелек:<span style="color:#F00">*</span>&nbsp;&nbsp;</p>
		</td>
      <td class="form-normal"><input type="text" id="purse_z" name="purse_z" value="<?=$purse_z==''? 'Z' : $purse_z;?>" size="15" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right"><p>LR-аккаунт:</p>
		<span style="color:#666; font-size:9px">Из соображений безопасности партнерские <br />
        выплаты будут осуществлятся <br />на LR или на WMZ-кошелек. <br />В будущем его изменить будет нельзя</span></td>
      <td class="form-normal"><input type="text" id="purse_z" name="purse_lr" value="<?=$purse_lr==''? '' : $purse_lr;?>" size="15" /></td>
    </tr>
    <tr>
      <td nowrap="nowrap" align="right"><p>Email:<span style="color:#F00">*</span>&nbsp;&nbsp;</p></td>
      <td class="form-normal"><input type="text" id="email" name="email" value="<?php echo $email;?>"/></td>
    </tr>
    <tr>
      <td nowrap="nowrap" align="right"><p>      
          Введите текст на картинке:&nbsp;&nbsp;</p>
</td>
          <td class="form-normal"><input type="text" name="keystring"><br />
			<img id="kap" src="kcaptcha/index.php?<?php echo htmlspecialchars(session_name())?>=<?php echo htmlspecialchars(session_id())?>">
          <a href="javascript:rel();"><img src="i/reload.gif" alt="обновить" width="16" height="16" border="0" align="middle" /></a></td></tr>
			 
    
    <tr>
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td>
      <?php if ( isset($_SESSION['Partner_AuthUsername']) ){ ?>
      <input class="button1" type="button" value="Сохранить" onclick="makesubmit();"/>
      <?php }else{ ?>
      <input  class="button1" type="button" value="Регистрация" onclick="makesubmit();"/><br />
      <?php } ?>
      </td>
    </tr>
  </table>
  <input type="hidden" name="reg" value="ok" />
</form>               
       <?php } else {?>
        <div class="intro">
                        
                        
         <div align="center"> 
       <table width="550" align="center">
    <tr><td align="center"><h1>Кабинет партнера</h1></td></tr>
    
    <tr>
    <td align="right" valign="top">
    <a href="partner_login.php">Вход</a><br />
    <a href="partner.php">Статистика</a><br />
<!--    <a href="cabinet.php?history">История операций</a><br />-->
	<img src="<?=$siteroot?>i/li_sm.gif" width="16" height="15" border="0" /> Персональные данные<br />
    <?php if ( isset($_SESSION['Partner_AuthUsername']) ){ ?>
    <a href='partner_login.php?doLogout=true'>Выход</a> <?php } ?>
    </td>
    </tr>
    <tr><td height="50"></td></tr>
</table>
<form action="<?php echo $editFormAction; ?>" method="post" name="forgot" id="forgot">
<table align="center">
<tr><td align="center" colspan="2"><h2>Напоминание пароля</h2></td></tr>
<?php if ( isset($only_mess) ) {?>
<tr><td colspan="2"><p><?=$only_mess?></p></td></tr>
<? }else{ ?>
<tr><td align="right">Введите e-mail, указанный при регистрации</td>
<td class="form-normal"><input type="text" name="email" id="email" size="25" /></td></tr>
<tr><td valign="top" align="right">  Введите текст на картинке:<br />
          </td>
          <td valign="middle" class="form-normal"><input type="text" name="keystring"><br />
			<img id="kap" src="kcaptcha/index.php?<?php echo session_name()?>=<?php echo session_id()?>">
          <a href="javascript:rel();"><img src="i/reload.gif" width="16" height="16" border="0" /></a></td>
</tr>
<tr><td></td><td><input type="button" class="button1" onClick="forgot_submit();" value="Напомнить"></td></tr>
<?php } ?>
</table>
<input type="hidden" name="form" value="forgot" />
</form>	
       
       <?php } ?>
         </div>
                        
                        
                        
                        
                        
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