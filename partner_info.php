<?php require_once('Connections/ma.php');
require_once($serverroot.'function.php');
//if ( !isset($_SERVER['HTTPS']) ) {header("Location: https://obmenov.com/index.php?".$_SERVER['QUERY_STRING']); }
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Партнерская программа</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<?php require_once($serverroot."/Connections/meta.php"); ?>
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
                        <div class="intro">
                            <h1 align="center">Партнерская программа<br />
                            <?php if ( !isset($_SESSION['Partner_AuthUsername']) ) { ?>
<a href="<?=$siteroot?>partner_register.php">Регистрация>></a></h1><br />
<?php } ?>


<ul><li>
<span class="otzyv-name">Хотите зарабатывать вместе с нами?</span><br />
Для этого Вам совершенно не обязательно прилагать много усилий. Мы предлагаем Вам принять участие в нашей партнерской программе. Для того чтобы начать зарабатывать нужно <a href="partner_register.php">зарегистрироваться</a>, выбрать понравившийся Вам банер (или несколько) и разместить его у себя на сайте. Каждый клиент, привлеченный Вами и совершивший обмен будет приносить нам прибыль, которой мы будем делится с Вами! Чем больше Вы привлечёте клиентов, тем больше будете зарабатывать!
</li>
<li><span class="otzyv-name">Зарабатывайте ВСЕГДА!</span><br />
Если привлеченный Вами клиент зарегистрируется у нас, он закрепляется за вами <strong>НАВСЕГДА</strong> и все обмены, совершенные им, будут приносить Вам <strong>ПОЖИЗНЕННУЮ ПРИБЫЛЬ.</strong> Но даже если этого не случиться, Вы всё равно будете получать прибыль от его обменов в течении 3-х месяцев от последнего перехода по ссылке.</li>

<li><span class="otzyv-name">Клиенты, клиенты... МНОГО КЛИЕНТОВ!</span><br />
Величина вашего заработка не стоит на месте. Чем больше клиентов привлекаете ВЫ, тем больший заработок с каждой операции обеспечиваем Вам МЫ! Кроме того вы получаете бонус за каждого привлеченного клиента уже на втором уровне скидки! В свою очередь мы будем стараться делать самые выгодные курсы обмена, чтобы однажды привлеченный вами клиент всегда пользовался только нашими услугами!
</li>
<li><span class="otzyv-name">Вы еще раздумываете?</span><br />
Скорее <a href="partner_register.php">регистрируйтесь</a>, и начните получать прибыль уже сегодня!</li>
<br />
<br />
P.s.: Мы не приветствуем различного рода буксы, САР'ы и т.д.. Господа-накрутчики, не теряйте свое время!
</ul>

<br />
<br />
<div align="center">
	<table border="0" align="center" cellpadding="4" cellspacing="0" width="450">
  <tr class="otzyv-name">
  	<td class="td_head" rowspan="2" align="left">Уровень</td>
  	<td class="td_head" colspan="2" align="center">Кол-во привлеченных посетителей*</td>
  	<td class="td_head" rowspan="2" valign="middle">% от нашего зааработка с операции</td>
  	<td class="td_head" rowspan="2" valign="middle" align="right">Переходы привлеченных посетителей*</td>
  </tr>
  <tr class="td_head">
    <td align="center">От</td>
    <td align="center">До</td>
  </tr>
  	<tr><td height="10"></td>
  </tr>
<?php 
	$discount_table_query = 'SELECT * FROM pndiscount';
	$discount_table = _query($discount_table_query,'partner_info.php 5');

while ($discount_table_row = $discount_table->fetch_assoc()) { 

?>

    <tr>
      <td width="25%" align="left" height="20" class="otzyv-date"><?php echo $discount_table_row['descr']; ?></td>
      <td width="25%"  align="center"><?php echo $discount_table_row['users']; ?></td>
      <td width="25%"  align="center"><?php echo $discount_table_row['till']; ?></td>
      <td width="25%"  align="center"><?php echo (($discount_table_row['discount']-1)*100);?>%</td>
      <td width="25%"  align="right"><?php echo $discount_table_row['per_click']; ?></td>
    </tr>
<?php }; ?>
	<tr><td height="50"></td></tr>
    </table>
*-привлеченным считается посетитель хоть раз совершивший обмен.
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