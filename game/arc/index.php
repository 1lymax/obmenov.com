<?php require_once('/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php');
require_once('/var/www/webmoney_ma/data/www/obmenov.com/function.php');
include '/var/www/webmoney_ma/data/www/obmenov.com/game/game/wm.class.php';	
	
if ( isset($_GET['game']) ) {
	$select="select * from gamedealer_projects where projectid=".$_GET['game'];
	$query=_query($select,"gamepay.php 1");
	if ( mysql_num_rows($query)==1 ) {
		$game=$query->fetch_assoc();
	}
}
$wmbank = new wmbank;
 //print_r($_POST);
  if(isset($_POST['ajax']) && isset($_POST['nick']) && isset($_POST['projectid'])){

	if($_POST['getamount']<1)die($wmbank->json(array('status'=>-1,'desc'=>'Извините, сумма должна составлять 1 игровую валюту, как минимум')));
     $result = $wmbank->json($wmbank->checkLogin($_POST['nick'],$_POST['projectid']));
     die(iconv('utf-8','windows-1251',$result));
  }


$projectlist = $wmbank->projectlist(); //print_R($projectlist)	
			
			
			
			?>
            <html>
<script language="javascript" type="text/javascript">
//<![CDATA[
var cot_loc0=(window.location.protocol == "https:")? "https://secure.comodo.net/trustlogo/javascript/cot.js" :
"http://www.trustlogo.com/trustlogo/javascript/cot.js";
document.writeln('<scr' + 'ipt language="JavaScript" src="'+cot_loc0+'" type="text\/javascript">' + '<\/scr' + 'ipt>');
//]]>
</script>
<head>
<title>Обменов.ком :: Онлайн-игры
</title>
<script type="text/javascript" src="<?=$siteroot?>fun.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
</head>
<link rel="icon" href="https://obmenov.com/images/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="https://obmenov.com/images/favicon.ico" type="image/x-icon">
<link href="<?=$siteroot?>wm.css" rel="stylesheet" type="text/css">
<body>
            <?php
			require_once("/var/www/webmoney_ma/data/www/obmenov.com/top_left.php");
			$select="select * from gamedealer_projects";
			$query=_query($select, "game.php 1");
			
			?>
             <table align="center" width="600" border="0">
    <tr>
    	<td colspan="3" height="40" id="head" align="center">  Онлайн-игры</td>
    </tr> 
    <tr>
    <td colspan="3">
    <select style="width:200px" id="projectid" name="projectid" class="comm_in" >
    <option>Все</option>
    <?php while ( $game=$query->fetch_assoc() ) {?>
    	<option value=""><?=$game['title']?></option>
    <?php } ?>
    </select>
    </td>
    </tr>
    <tr><td colspan=3>
    <table width="500" class="tableborder2"><tr><td>&nbsp;</td></tr></table>
    </td></tr>   
    </table>
    <table align="center" width="600" border="0" id="game">
    <?php 
	$select="select * from gamedealer_projects";
	$query=_query($select, "game.php 1");	
	while ( $game=$query->fetch_assoc() ) {?>
    <tr>
    <td valign="top"><img src="<?=str_replace("http://gamedealer.ru/img3/","https://obmenov.com/images/game/",$game['img'])?>"><br>
	<a href="<?=$game['url']?>">Сайт проекта >></a><br>
	<a href="<?=$siteroot."gamepay.php?game=".$game['projectid']?>">Оплата >></a>
	</td>
    <td width="10"></td>
    <td valign="top"><span id="head_small"><strong><?=$game['title']?></strong></span><br>
    <?=$game['descr']?>
</td>
    
    </tr>
    <tr><td colspan="3">
    <table width="500" class="tableborder2"><tr><td>&nbsp;</td></tr></table>
    </td></tr>
    <?php } ?>
    </table>
    
    
                <?php
		
		
		
 require_once("/var/www/webmoney_ma/data/www/obmenov.com/bottom_right.php");

?>
