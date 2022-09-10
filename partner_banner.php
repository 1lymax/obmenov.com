<?php require_once('Connections/ma.php'); ?>
<?php require_once('function.php');



$currentPage = $_SERVER["PHP_SELF"];

if ( !isset($_SESSION['Partner_AuthUsername']) ) { //begin of false user auth
					  
					  
					  
}else  //begin of true user auth
{


$max = 10;
$page = 0;
if (isset($_GET['page'])) {
  $page = htmlspecialchars($_GET['page']);
}
$start = $page * $max;

$query_client="SELECT partner.id, partner.nikname, partner.email, partner.clid, partner.phone FROM partner WHERE partner.nikname='".$_SESSION['Partner_AuthUsername']."';";
	$client = _query($query_client, 'partner_banner.php 1');
	$num_row_client=$client->num_rows;
	$row_client=$client->fetch_assoc();
	




} // end of true user auth

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Рекламные материалы</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<?php require_once($serverroot."Connections/meta.php"); ?>
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
		<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
        <link rel="shortcut icon" href="<?=$siteroot?>i/favico.ico"/>
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
    <?php 
if ( isset($_SESSION['Partner_AuthUsername']) ) {
	?>
<script language="javascript">
function ChangeCode (num) {
	switch ( num ) {
		case 1: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>">Обменов.ком - моментальный ввод/вывод электрпонных денег</a>';break;
		case 2: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>">Обменов.ком - Меняем шило на мыло</a>';break;
		case 3: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img alt="Обменов.ком - Лучшие условия для обмена" width="88" height="31" border="0" src="<?=$siteroot?>banners.php?bn=1&pn=<?=$row_client['id']; ?>"></a>';break;
		case 11: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img alt="Обменов.ком - Лучшие условия для обмена" width="88" height="31" border="0" src="<?=$siteroot?>banners.php?bn=11&pn=<?=$row_client['id']; ?>"></a>';break;
		case 12: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img alt="Обменов.ком - Лучшие условия для обмена" width="100" height="100" border="0" src="<?=$siteroot?>banners.php?bn=12&pn=<?=$row_client['id']; ?>"></a>';break;
		case 13: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img alt="Обменов.ком - Лучшие условия для обмена" width="100" height="100" border="0" src="<?=$siteroot?>banners.php?bn=13&pn=<?=$row_client['id']; ?>"></a>';break;
		case 4: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img alt="Обменов.ком - Лучшие условия для обмена" width="100" height="100" border="0" src="<?=$siteroot?>banners.php?bn=2&pn=<?=$row_client['id']; ?>" alt="Обменов.ком - Лучшие условия для обмена"></a>';break;
		case 5: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img alt="Обменов.ком - Лучшие условия для обмена" width="100" height="100" border="0" src="<?=$siteroot?>banners.php?bn=6&pn=<?=$row_client['id']; ?>" alt="Обменов.ком - Лучшие условия для обмена"></a>';break;
		case 6: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img alt="Обменов.ком - Лучшие условия для обмена" width="100" height="100" border="0" src="<?=$siteroot?>banner.php?bn=7&pn=<?php echo $row_client['id']; ?>" alt="Обменов.ком - Лучшие условия для обмена"></a>';break;
		
		case 7: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img width="468" height="60" hspace="0" vspace="0" border="0" src="<?=$siteroot?>banners.php?bn=3&pn=<?=$row_client['id']; ?>" alt="Обменов.ком - Лучшие условия для обмена"></a>';break;
		case 8: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img width="468" height="60" hspace="0" vspace="0" border="0" src="<?=$siteroot?>banners.php?bn=4&pn=<?=$row_client['id']; ?>" alt="Обменов.ком - Лучшие условия для обмена"></a>';break;
		case 9: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img width="468" height="60" hspace="0" vspace="0" border="0" src="<?=$siteroot?>banners.php?bn=5&pn=<?=$row_client['id']; ?>" alt="Обменов.ком - Лучшие условия для обмена"></a>';break;
		case 14: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img width="468" height="60" hspace="0" vspace="0" border="0" src="<?=$siteroot?>banners.php?bn=14&pn=<?=$row_client['id']; ?>" alt="Обменов.ком - Лучшие условия для обмена"></a>';break;
		case 15: document.banner_form.code.value='<a href="<?=$siteroot?>?pn=<?=$row_client['id']; ?>"><img width="468" height="60" hspace="0" vspace="0" border="0" src="<?=$siteroot?>banners.php?bn=15&pn=<?=$row_client['id']; ?>" alt="Обменов.ком - Лучшие условия для обмена"></a>';break;
		case 10: document.banner_form.code.value='<iframe src="<?=$siteroot?>iframe.php?pn=<?=$row_client['id']; ?>" width="350" height="394" marginheight="0" marginwidth="0"  frameborder="0" scrolling="no" hspace="0" vspace="0"></iframe>';break;

	}
	
	
	
}


</script>
<?php } ?>
	<body>
	    <div class="wrapper">
            <div class="wrapper-inn">

                <?php require_once("siti/inc_top.php");?>

                <div class="middle clear">

                    <!-- Start left column -->
                    <? require_once("siti/inc_left.php");?>
                    <!-- End left column -->

                    <!-- Start central column -->
                    <div class="c-col">
                      <?php if ( isset($_SESSION['Partner_AuthUsername']) ) {?>
	<table width="550" align="center">
    <tr><td align="center"><h1>Кабинет партнера</h1></td></tr>
    
    <tr>
    <td align="right" valign="top"><h2>
    <?='Пользователь: '.$_SESSION['Partner_AuthUsername'].'<br />';?>
    <?='Идентификатор: '.$row_client['id'];?></h2>
<a href="<?=$siteroot?>partner.php">Статистика</a><br />
<!--    <a href="cabinet.php?history">История операций</a><br />-->
	<a href="<?=$siteroot?>partner_register.php">Персональные данные</a><br />
    Банеры<br />
    <a href="<?=$siteroot?>partner_rates.php">Курсы валют</a><br />
    <a href='<?=$siteroot?>partner_login.php?doLogout=true'>Выход</a>
    </td>
    </tr>
    <tr><td height="50"></td></tr>
    </table>

	<table width="550" align="center" border="0" cellspacing="6" cellpadding="6">
    <tr><td align="center" colspan="3"><h2>Рекламные материалы</h2>
<br />
</td></tr>
    <form name="banner_form" id="banner_form">
    <tr>
    <td rowspan="17" width="15"></td></tr>
    <tr><td colspan="2" class="form-normal">Ваша партнерская ссылка имеет вид <br />
	<input type="text" value="http://obmenov.com/?pn=<?=$row_client['id'];?>"  /><br />
Чтобы получить код понравившегося вам банера, нажмите на перключатель слева от него (Код расположен в самом низу страницы):</td></tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(1);">
    </td>
    <td>Обменов.ком - мгновенный ввод/вывод электронных денег</td>
    </tr> 
    <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner"  id="banner" onclick="ChangeCode(2);">
    </td>
    <td>Обменов.ком - Меняем шило на мыло</td>
    </tr> 
     <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(3);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/88x31_1.gif" alt="Обменов.ком - Лучшие условия для обмена" width="88" height="31" border="0" /></td>
    </tr>
    <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(11);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/88x31_2.gif" alt="Обменов.ком - Лучшие условия для обмена" width="88" height="31" border="0" /></td>
    </tr>
    <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(12);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/100x100_1.gif" alt="Обменов.ком - Лучшие условия для обмена" width="100" height="100" border="0" /></td>
    </tr>
    <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(13);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/100x100_2.gif" alt="Обменов.ком - Лучшие условия для обмена" width="100" height="100" border="0" /></td>
    </tr>
    <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(4);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/banner2.gif" alt="Обменов.ком - Лучшие условия для обмена" width="100" height="100" border="0" /></td>
    </tr>
    <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(5);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/banner6.gif" alt="Обменов.ком - Лучшие условия для обмена" width="100" height="100" border="0" /></td>
    </tr>
        <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(6);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/banner7.gif" alt="Обменов.ком - Лучшие условия для обмена" width="100" height="100" border="0" /></td>
    </tr>
    
    <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(14);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/486x60_1.gif" width="468" height="60" hspace="0" vspace="0" border="0"  /></td>
    </tr>
        <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(15);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/486x60_2.gif" width="468" height="60" hspace="0" vspace="0" border="0"  /></td>
    </tr>
    
    <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(7);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/banner3.png" width="468" height="60" hspace="0" vspace="0" border="0"  /></td>
    </tr>
        <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(8);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/banner4.png" width="468" height="60" hspace="0" vspace="0" border="0"  /></td>
    </tr>
        <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(9);">
    </td>
    <td valign="middle"><img src="<?=$siteroot?>i/banners/banner5.png" width="468" height="60" hspace="0" vspace="0" border="0"  /></td>
    </tr>
    <tr>
    <td align="left" valign="top"><input type="radio" style="border:0" name="banner" id="banner" onclick="ChangeCode(10);">
    </td>
    <td valign="middle"><iframe src="<?=$siteroot?>iframe.php" width="350" height="394" marginheight="0" marginwidth="0"  frameborder="0" scrolling="no" hspace="0" vspace="0"></iframe></td>
    </tr>
    <tr><td height="40"></td></tr>
    <tr><td height="50" colspan="3">
    HTML-код для вставки:
    <textarea name="code" id="code" cols="50" rows="5"> </textarea>
    </td></tr></form>
    </table>

<br />

<?php
} //end true user auth 
else
{
	
?>
	<table width="550" align="center">
    <tr><td align="center"><h1>Кабинет партнера</h1></td></tr>
    
    <tr>
    <td align="right" valign="top">
Вход<br />
    <a href="partner.php">Статистика</a><br />
<!--    <a href="cabinet.php?history">История операций</a><br />-->
	<a href="partner_register.php"><?php echo ( isset($_SESSION['Partner_AuthUsername']) ) ? 'Персональные данные'  :  'Регистрация' ?></a><br />
    <a href="partner_banner.php">Банеры</a><br />
    <a href="partner_rates.php">Курсы валют</a><br />
    <?php if ( isset($_SESSION['Partner_AuthUsername']) ){ ?>
    <a href='partner_login.php?doLogout=true'>Выход</a> <?php } ?>
    </td>
    </tr>
    <tr><td height="50"></td></tr>
</table>

<form ACTION="partner_login.php?accesscheck=partner.php" METHOD="POST" name="login" id="pauth">
<table width="300" align="center">
<tr><td align="right">Имя </td><td class="form-normal"><input name="user" type="text" /></td></tr>
<tr><td align="right">Пароль</td><td class="form-normal"><input name="pass" type="password" /></td></tr>
<tr>          <input name="" type="image" src="i/empty.gif" width="0" height="0"  style="border:none" /><td></td>
<td><input type="button" class="button1" onClick="d.$('pauth').submit();" value="Вход"></td></tr>
<tr><td></td><td><a href="partner_register.php?forgot">Забыл пароль?</a></td></tr>

</table>
</form>  <?php } ?>
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