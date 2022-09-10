<?
// $Id: index.php,v 1.5 2006/07/31 14:11:41 asor Exp $
require_once('Connections/ma.php');
require_once('function.php');
$oid=0;
	$oid = isset ($_POST['oid']) ? $_POST['oid'] : 0 ;
	$order = "SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.summout, orders.discammount, orders.clid
				FROM orders WHERE orders.id=".$oid; //.$_POST['oid'];
			$row_order=_query($order, "success.php 1");
			$numrows = mysql_num_rows($row_order);
			$row_order=$row_order->fetch_assoc();
if ( $numrows == 1 ) {
	$direction = $row_order['summin'].' '.$row_order['currin'].' -> '.
				($row_order['summout']+$row_order['discammount']).' '.$row_order['currout'];
}
else{
	$direction="-";
}


if ( isset($_POST['message']) && $_POST['message']=='ok' && isset($_SESSION['clid_num']) ) {
		$badmessage=0;
		date_default_timezone_set('Europe/Helsinki');
		$message_query = "INSERT INTO comment (`name`, `direction`, `message`, time, active, `clid`) VALUES ('"
								.$_POST['name']."','"
								.$direction."','"
								.$_POST['question']."','"
								.date("Y-m-d H:i:s")."', 1 ,'"
								.$_SESSION['clid_num']."');";
		
		$select = "select word from blackwords";
		$query = _query ($select, "comment 1");
		while ($blackword = $query->fetch_assoc() ) {
			if ( strlen(strstr($_POST['message'],$blackword['word']))!=0 ) {
				$badmessage=true;
				break;
			}
									  
		}

	
	if ($badmessage) {

	}
	else
	{
		$Result= _query($message_query,'contacts.php 1');
	}

		send_mail($shop_email, 
				  'Имя: '.$_POST['name'].
				  '<br />Комментарий: '.$_POST['question']			  
				  , "Message", "support@obmenov.com", "Feedback Form");
		$mess="Спасибо за ваш комментарий. Вы можете вернуться на <a href='".$siteroot."index.php'>главную страницу</a>";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Понравился наш сервис? Оставьте свой комментарий.</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<?php require_once($serverroot."Connections/meta.php"); ?>
		<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
        <link rel="shortcut icon" href="<?=$siteroot?>i/favico.ico"/>
		<!--[if lte IE 7]><link rel="stylesheet" href="ie.css" type="text/css" media="screen" /><![endif]-->
        <style>
		<?php
       	if ( isset($_SESSION['AuthUsername']) ) {
		echo '.wrapper {background: url("i/wrapper-auth.jpg") center 0 no-repeat;}';
		}else{
		echo '.wrapper {background: url("i/wrapper.jpg") center 0 no-repeat;}';
		}
		?>
		</style>
        <script src="<?=$siteroot?>fun.js"></script>
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
                     <table align="center" width="500">
					 <?php if ( $oid != 0 ) { ?>
                     <tr><td valign="middle" id="head" height="50" align="center">         
          <h1>Спасибо за использование услуг нашего сервиса</h1>
          </td></tr><?php } ?>
          <tr><td>
           <table width="450" align="center" border="0"><form action="<?=$siteroot?>comment.php" method="post" id="message">
            <tr><td colspan="2" align="center">
            <?php

if ( isset($mess) ) {
		   echo $mess;
		   }
		   else {

?>           
            <h1>Понравились наши услуги или есть пожелания?</h1>
            Выскажите своё мнение на <a href="http://forum.obmenov.com/posting.php?mode=reply&f=8&t=7" target="_blank">нашем форуме</a>,<br />
            и его увидят остальные клиенты нашего сервиса. <br />Также можете заполнить приведенную ниже форму:
            </td></tr>
            
            <tr>
              <td align="right" class="cabinet-text" width="130"><strong>Ваше имя:</strong></td>
              <td align="left" width="130">             
                <input name="name" type="text" size="20">
              </td>
             </tr>
             <tr>
              <td align="right" valign="top"><span class="cabinet-text"><strong>Сообщение:</strong></span><br />
				<span style="font-size:10px; color:#666;">Максимальная длина -<br />500 символов. <br />
				Реклама и сообщения не по теме
                удаляются.</span></td>
              <td align="left"><textarea name="question" cols="30" rows="4" type="text" size="20"></textarea></td>
             </tr>
             <tr>
              <td align="right"></td>
              <td align="left"><input type="button" class="button1" value="Отправить" onclick="d.$('message').submit();"></td>
             <input name="message" type="hidden" value="ok" />
             <input type="hidden" value="<?=$oid ;?>" name="oid" />
			<input name="" type="image" src="i/empty.gif" width="1"  style="border:none" />  
            
           
          </tr></form>
          <tr><td height="50" colspan="2" align="center"><br />
	Можете оставить свой отзыв на наш WMID в системе Wemboney Arbitrage - <a href="http://arbitrage.webmoney.ru/asp/claims.asp?wmid=219391095990">здесь</a>, <br />
		или на наш сайт в Webmoney Advisor - <a href="http://advisor.wmtransfer.com/FeedBackList.aspx?url=obmenov.com">здесь</a>,<br />
		а также на нашем <a href="http://forum.obmenov.com">форуме</a>.
        
          <?php } ?>        
        
        </td></tr>
          </table>
          <br /><br />

          <table align="center" width="500">
          <tr><td colspan="2"><h2>Комментарии покупателей:</h2></td></tr>
		  <?php 
		  $limit = isset($_GET['showall']) ? "" : "limit 0,30";
		  $query="select name, direction, message, time from comment where active=1 order by id  desc ".$limit;
		  $comment_query=_query($query,1);
		 
		   while ( $comment=mysql_fetch_assoc($comment_query) ){
			  ?>
			  <tr><td valign="top" width="130">
              Имя:
			  </td>
			 <td valign="top" class="otzyv-name">
             <?=strlen($comment['name'])==0 ? "-" : $comment['name'];?>
             </td></tr>
			<tr><td valign="top">
              Дата:
			  </td>
			 <td valign="top" class="otzyv-date"><?=substr($comment['time'],0,16);?>
             </td></tr>
             <tr><td valign="top">
              Что поменял:
			  </td>
			 <td valign="top" align="left" class="otzyv-text"><?=$comment['direction'];?>
             </td></tr>
             <tr><td valign="top">
              Комментарий:
			  </td>
			 <td valign="top" class="otzyv-text"><?=$comment['message'];?>
             </td></tr>
             <tr><td colspan="2" class="otzyv"></td></tr>
              <?php
		  }
		  ?>
          <tr><td colspan="2">Показаны последние 30 отзывов</td></tr>
          <tr><td colspan="2"><a href="<?=$siteroot?>comment.php?showall">Посмотреть все>>></a></td></tr>
          </table>
          </td></tr></table>   
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