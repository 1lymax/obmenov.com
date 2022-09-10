<?php require_once('../Connections/ma.php');
require_once('../function.php');
include 'game/wm.class.php';

	
if ( isset($_GET['game']) ) {
	$select="select * from gamedealer_projects where active=0 and projectid=".(int)$_GET['game'];
	$query=_query($select,"gamepay.php 1");
	if ( mysql_num_rows($query)==1 ) {
		$game=$query->fetch_assoc();
	}
}
$wmbank = new wmbank;
//$wmbank->updateProjects();
 //print_r($_POST);
  if(isset($_POST['ajax']) && isset($_POST['nick']) && isset($_POST['projectid'])){

	if($_POST['getamount']<1)die($wmbank->json(array('status'=>-1,'desc'=>'Извините, сумма должна составлять 1 игровую валюту, как минимум')));
     $result = $wmbank->json($wmbank->checkLogin($_POST['nick'],$_POST['projectid']));
     die(iconv('utf-8','windows-1251',$result));
  }


$projectlist = $wmbank->projectlist(); //print_R($projectlist)	
			
			
			
			?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Онлайн-игры</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<?php require_once($serverroot."Connections/meta.php"); ?>
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
		<link rel="stylesheet" href="<?=$siteroot?>style.css" type="text/css" media="screen" />
        <link rel="shortcut icon" href="<?=$siteroot?>i/favico.ico"/>
		<!--[if lte IE 7]><link rel="stylesheet" href="<?=$siteroot?>ie.css" type="text/css" media="screen" /><![endif]-->
        <style>
		<?php
       	if ( isset($_SESSION['AuthUsername']) ) {
		echo '.wrapper {background: url("'.$siteroot.'i/wrapper'.$urlid['site_ext'].'-auth.jpg") center 0 no-repeat;}';
		}else{
		echo '.wrapper {background: url("'.$siteroot.'i/wrapper'.$urlid['site_ext'].'.jpg") center 0 no-repeat;}';
		}
		?>
		</style>
        
	</head>
	<body>
	    <div class="wrapper">
            <div class="wrapper-inn">

                <?php require_once($serverroot."siti/inc_top.php");?>

                <div class="middle clear">

                    <!-- Start left column -->
                    <? require_once($serverroot."siti/inc_left.php");?>
                    <!-- End left column -->

                    <!-- Start central column -->
                    <div class="c-col">
                     <?php
					 
					 $select="select * from gamedealer_projects where active=0 order by title asc";
			$query=_query($select, "game.php 1");
			
			?>
            <div align="center">
             <table align="center" width="500" border="0">
    <tr>
    	<td colspan="3" height="40" align="center"><h1>Онлайн-игры.<br />
Сервис продажи игровой валюты.</h1></td>
    </tr> 
    <tr>
    <td colspan="3"><form action="<?=$siteroot?>gamepay.php" method="get" id="gamesubmit" name="gamesubmit">
    <select style="width:200px" id="projectid" name="projectid" class="comm_in" onchange="document.gamesubmit.submit();">
    <option>Все</option>
    <?php while ( $game=$query->fetch_assoc() ) {?>
    	<option value=""><?=$game['title']?></option>
    <?php } ?>
    </select>
    </form>
    </td>
    </tr>
    <tr><td colspan=3>
    <table width="500" class="tableborder2"><tr><td>&nbsp;</td></tr></table>
    </td></tr>   
    </table>
    <table align="center" width="500" border="0" id="game">
    <?php 
	$select="select * from gamedealer_projects where active=0 order by title";
	$query=_query($select, "game.php 1");	
	while ( $game=$query->fetch_assoc() ) {?>
    <tr>
    <td valign="top"><img src="<?=str_replace("http://gamedealer.ru/img3/","https://obmenov.com/i/game/",$game['img'])?>"><br>
	<a href="<?=$game['url']?>">Сайт проекта >></a><br>
	<a href="<?=$siteroot."gamepay.php?game=".$game['projectid']?>">Оплата >></a>
	</td>
    <td width="10"></td>
    <td valign="top"><span class="otzyv-date"><?=$game['title']?></span><br>
    <?=$game['descr']?>
</td>
    
    </tr>
    <tr><td colspan="3">
    <table width="500" class="tableborder2"><tr><td>&nbsp;</td></tr></table>
    </td></tr>
    <?php } ?>
    </table>
    </div>
                    </div>
                    <!-- End central column -->

                    <!-- Start right column -->
                    <?php require_once($serverroot."siti/inc_right.php");?>
                    <!-- End right column -->

                </div>

                <?php require_once($serverroot."siti/inc_footer.php"); ?>

            </div>
	    </div>

	</body>
</html>