<?php require_once('Connections/ma.php');
require_once('function.php'); 

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['Partner_AuthUsername'] = NULL;
  $_SESSION['Partner_AuthUserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['Partner_AuthUsername']);
  unset($_SESSION['Partner_AuthUserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "index.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    die();
  }
}


$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = htmlspecialchars($_GET['accesscheck']);
}

if (isset($_POST['user'])) {
  $loginUsername=$_POST['user'];
  $password=$_POST['pass'];
  $MM_fldUserAuthorization = "";
  $MM_redirectLoginSuccess = "partner.php";
  $MM_redirectLoginFailed = "partner_login.php?fail";
  $MM_redirecttoReferrer = true;
  
  $LoginRS__query=sprintf("SELECT nikname, passmd5, activated, clid FROM partner WHERE nikname=%s AND passmd5=%s",
    GetSQLValueString($loginUsername, "text"), GetSQLValueString(md5(md5($password)), "text")); 
   
  $LoginRS = _query($LoginRS__query, 'partner_login.php 1');
  $loginFoundUser = $LoginRS->num_rows;
  $LoginRS_rows = $LoginRS->fetch_assoc();
  
   $insert="INSERT into auth (name, pass, clid, success, realm, ip) VALUES ('".
					$loginUsername."', '".
					md5(md5($password))."', '".
					$clid."', ".
					$loginFoundUser.", '
					partner', '".
					$_SERVER['REMOTE_ADDR']."')";
  $query = _query($insert, "partner_login.php 23");
  
  if ($loginFoundUser) {
	  if ($LoginRS_rows['activated']==0) {$mess='<strong>Ваша учетная запись не активирована.</strong> <br />
На адрес электронной почты, указанный при регистрации <br />
выслано письмо с инструкциями о том как выполнить активацию.';}
     else {
	 	$loginStrGroup = "";
    
    	//declare two session variables and assign them
    	$_SESSION['Partner_AuthUsername'] = $loginUsername;
    	$_SESSION['Partner_AuthUserGroup'] = $loginStrGroup;	      
		setcookie('clid',$LoginRS_rows['clid']);
    	if (isset($_SESSION['PrevUrl']) && true) {
      		$MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    	}
		header("Location: " . $MM_redirectLoginSuccess );
		die();
	 }
  }
  else {
		header("Location: ". $MM_redirectLoginFailed );
		die();
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Кабинет партнера</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<meta name="description" content="..." />
		<meta name="keywords" content="..." />
        <script src="_main.js"></script>
		<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
        <link rel="shortcut icon" href="<?=$siteroot?>i/favico.ico"/>
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
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
	<body onload="start();">
	    <div class="wrapper">
            <div class="wrapper-inn">

                <?php require_once("siti/inc_top.php");?>


                <div class="middle clear">

                    <!-- Start left column -->
                    <?php require_once("siti/inc_left.php");?>
                    <!-- End left column -->

                    <!-- Start central column -->
                    <div class="c-col">

                            <h1 align="center">Авторизация</h1><br /><br />
<?php if ( isset($_GET['fail']) ) {echo '<h1 align="center">Неправильно указан логин или пароль</h1>';}?>
                            
<br />
<br />
                        <div class="intro">
                        
                        
                        <div align="center">
                        <form action="<?=$loginFormAction;?>" method="post" name="login" id="pauth">
<table width="300" align="center">
<tr><td align="center" colspan="2" class="form-normal"><p><?php if ( isset($mess) ) {echo $mess;}?></p></td></tr>
<tr><td align="right"><p>Имя:&nbsp;&nbsp;</p> </td>
<td class="form-normal"><input name="user" type="text" /></td></tr>
<tr><td align="right"><p>Пароль:&nbsp;&nbsp;</p></td><td class="form-normal"><input name="pass" type="password" /></td></tr>
<tr><td></td><td><input type="submit" class="button1" value="Войти" /></td></tr>
<tr><td></td><td><a href="partner_register.php?forgot">Забыл пароль?</a></td></tr>
</table>
<input name="" type="image" src="i/empty.gif" width="0" style="border:none" />
</form>
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