<?php
require_once("Connections/ma.php");
mysql_select_db($database_ma, $ma);
_query('SET character_set_database = cp1251',"");
_query('SET NAMES cp1251',"");
  foreach ($_POST as $key => $value) {
    $_POST[$key] = mysql_real_escape_string($value);
  }

  //This stops SQL Injection in GET vars
  foreach ($_GET as $key => $value) {
    $_GET[$key] = mysql_real_escape_string($value);
  }
if ( isset($_GET['seed']) && isset($_GET['purse']) && isset($_GET['ptype']) ) { 
	if ( substr($_GET['purse'],0,1)!=$_GET['ptype'] &&
				( substr($_GET['purse'],0,1)=="R" || substr($_GET['purse'],0,1)=="Z" || substr($_GET['purse'],0,1)=="U" 
																					|| substr($_GET['purse'],0,1)=="E") ) {
		
		echo '<img src="images/attention.gif" width="11" height="11" title="Указан неправильный тип кошелька">';die();}																  
	require_once($serverroot."siti/_header.php");

	$response = $wmxi->X8("",$_GET['purse']);
	$structure = $parser->Parse($response, DOC_ENCODING);
	$transformed = $parser->Reindex($structure, true);
if ( isset($transformed["w3s.response"]) ) {	
	if ( $transformed["w3s.response"]["retval"]==1 ) {
		echo 'WMID '.$transformed["w3s.response"]["testwmpurse"]["wmid"];
		$wmid=1;
		die();
	}else {
		if ( !isset($wmid) ) {
			$response = $wmxi->X8("",$_GET['ptype'].$_GET['purse']);
			$structure = $parser->Parse($response, DOC_ENCODING);
			$transformed = $parser->Reindex($structure, true);
			if ( $transformed["w3s.response"]["retval"]==1 ) {
				echo 'WMID '.$transformed["w3s.response"]["testwmpurse"]["wmid"];
				$wmid=1;
				die();
			}
		}
	echo '<img src="images/attention.gif" width="11" height="11" title="Неправильный номер кошелька">';
	}
	die();
}else {echo '<img src="images/attention.gif" width="11" height="11" title="Номер кошелька на данный момент не может быть проверен">';}

}

if ( isset($_GET['type']) && $_GET['type']=="nikname" && isset($_GET['string']) ) {
	$select="select id from clients where nikname='".substr(htmlspecialchars($_GET['string']),0,15)."'";
	$query=_query($select, "checkticket.php nikname");
	if ( mysql_num_rows($query)==1 ) {
		echo '<img src="images/attention.gif" width="11" height="11" title="Имя '.htmlspecialchars($_GET['string']).' уже используется другим пользователем">';
	}else {
		echo '<img src="images/new/li_sm.gif" width="11" height="10" alt="ОК" />';
	}
}
if ( isset($_GET['type']) && $_GET['type']=="email" && isset($_GET['string']) ) {
	$select="select id from clients where email='".substr(htmlspecialchars($_GET['string']),0,50)."'";
	$query=_query($select, "checkticket.php email");
	if ( mysql_num_rows($query)==1 ) {
		echo '<img src="images/attention.gif" width="11" height="11" title="Адрес '.htmlspecialchars($_GET['string']).' занят другим пользователем">';
	}else {
		if ( strstr(htmlspecialchars($_GET['string']), "@") && strstr(htmlspecialchars($_GET['string']), ".") ) {
			echo '<img src="images/new/li_sm.gif" width="11" height="10" alt="ОК" />';
		}else {
			echo '<img src="images/attention.gif" width="11" height="11" title="Неправильный формат e-mail">';	
		}
		
	}
}

if ( isset($_GET['type2']) && $_GET['type2']=="wmid" && isset($_GET['string']) ) {
	require_once($serverroot."siti/_header.php");

	$response = $wmxi->X8($_GET['string2'],$_GET['string']);
	$structure = $parser->Parse($response, DOC_ENCODING);
	$transformed = $parser->Reindex($structure, true);
if ( isset($transformed["w3s.response"]) ) {	
	if ( $transformed["w3s.response"]["retval"]==1 ) {
		if ( $transformed["w3s.response"]["testwmpurse"]["wmid"]==$_GET['string2'] &&
			$transformed["w3s.response"]["testwmpurse"]["purse"]==$_GET['string'] )
		{
			echo ' <img src="images/new/li_sm.gif" width="11" height="10" alt="Кошелек принадлежит идентификатору '.
				htmlspecialchars($_GET['string2']).'"/>';
			die();
		}else {echo ' <img src="images/attention.gif" width="11" height="11" title="Кошелек не принадлежит идентификатору '.
				htmlspecialchars($_GET['string2']).'"/>';die();}
	}else{
		echo '<img src="images/attention.gif" width="11" height="11" title="Неправильный номер кошелька"/>';
	}
	die();
}else {echo '<img src="images/attention.gif" width="11" height="11" title="Номер кошелька в данный момент не может быть проверен"/>';}

	
}
if ( isset($_GET['seed']) && isset($_GET['type']) && $_GET['type']=="acc" ) {
	$select="select orders.id from orders, payment 
			where orders.fname='".substr(iconv("utf-8","windows-1251",htmlspecialchars(rawurldecode($_GET['string2']))),0,30)."'
			and orders.iname='".substr(iconv("utf-8","windows-1251",htmlspecialchars(rawurldecode($_GET['string3']))),0,30)."'
			and orders.account='".substr(htmlspecialchars($_GET['string']),0,16)."'
	and length(fname)>0 and length(iname)>0  and char_length(account)>0 and orders.id=payment.orderid and payment.canceled=1";
	$query=_query2($select,"checkticket.php 23");

	if ( mysql_num_rows($query)>0 ) {
		$row=$query->fetch_assoc();
		echo '<img src="images/new/li_sm.gif" width="11" height="10"><br /> <span style="font-size:10px; color:#666;">Реквизиты проверены. При доступном резерве, Вы получите средства на счет в течение нескольких минут.</span>';
	}else{
		echo '<img src="images/attention.gif" width="11" height="11"><br /> <span style="font-size:10px; color:#666;">Требуется проверка реквизитов, указанных в заявке. <br />
После проверки оператором (обычно в течение часа в рабочее время), Вы получите средства на карточный счет. В дальнейшем Вы сможете пользоватся всеми преимуществами мгновенного вывода средств!</span>';
	}
}

if ( isset($_GET['type']) && $_GET['type']=="x19" ) {
	//echo 5;
	$order = "SELECT *	FROM orders WHERE orders.id=".substr($_GET['oid'],0,7).";";
			$row_order=_query2($order, 17);
			$row_order=$row_order->fetch_assoc();
	$fname = isset ($_GET['fname']) ? $_GET['fname'] : "";
	$iname = isset ($_GET['iname']) ? $_GET['iname'] : "";
	$account = isset ($_GET['account']) ? $_GET['account']  : "";
	$wmid = isset ($_GET['wmid']) ? $_GET['wmid'] : "";
	$passport = isset ($_GET['pass']) ? $_GET['pass'] : "";
	/*if ( substr($row_order['currin'],0,3)=="MCV" && strlen($wmid)!=0 ) { // Проверка на обмен более 2к вмз в день
		$client=new client();
		$day_summ=(int)$client->day_summ($wmid);
		$summ=$row_order['attach']+	$day_summ;
		if ($summ>2000) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"> При этом направлении разрешено не более 2000 WMZ в сутки (или эквиваленте в другой валюте) на один аттестат Webmoney. На текущий момент вы обменяли '.$day_summ.' WMZ в эквиваленте.';die();
		} else {
			echo '<img src="images/new/li_sm.gif" width="11" height="10"> Обмен разрешен';die();
			
		}
	}*/
	
	
	$order=new orders();
	$x19=$order->check_X19($row_order, "get");
	//print_r($x19);
	if ( isset($x19['w3s.response']) ) {
		if ( $x19['w3s.response']['retval'] == 1 ){
			if ( $x19['w3s.response']['testwmpurse']['purse']=='' ) {
				echo '<br /><img src="images/attention.gif" width="11" height="11"/> Заявка не может быть проведена по указанным реквизитам. Кошелек-получатель должен принадлежать идентификатору '.htmlspecialchars($wmid);
			}else{
				echo '<img src="images/new/li_sm.gif" width="11" height="10"> Реквизиты проверены и удовлетворяют требованиям';
			}
		}else{
			echo '<img src="images/attention.gif" width="11" height="11"/>Заявка не может быть проведена по указанным реквизитам';
		}

	}elseif ( isset($x19['passport.response']) ) { 
		if ( $x19['passport.response']['retval']=="500" ) {
			switch ( $x19['passport.response']['retdesc'] ) {
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/fname" : 
					echo '<img src="images/attention.gif" width="11" height="11"/> Не указана фамилия';break;
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/iname" : 
					echo '<img src="images/attention.gif" width="11" height="11"/> Не указано имя';break;
				case "ошибка при проверке входных параметров step=20.1" : 
					echo '<img src="images/attention.gif" width="11" height="11"/> Недостаточно данных для проверки';break;
				case "пропущен обязательный параметр /passport.request/userinfo/wmid" : 
					echo '<img src="images/attention.gif" width="11" height="11"/> Не указан WMID';break;
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/pnomer" : 
					echo '<img src="images/attention.gif" width="11" height="11"/> Не указаны данные паспорта';break;
					
				
			}
		}elseif ( $x19['passport.response']['retval']=="0" ) {
			echo '<img src="images/new/li_sm.gif" width="11" height="10"> Обмен с указанными реквизитами разрешен';
			
		}elseif ( $x19['passport.response']['retval']=="404" ) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"/> Обмен с указанными реквизитами не разрешен. Данные указанные в заявке не совпадают с владельцем WM-идентификатора. Подробнее <a href="http://forum.obmenov.com/viewtopic.php?f=9&t=10" target="_blank">на нашем форуме</a>';
		}elseif ( $x19['passport.response']['retval']=="408" ) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"/> ',$x19['passport.response']['retdesc'].'. Подробнее <a href="http://forum.obmenov.com/viewtopic.php?f=9&t=10" target="_blank">на нашем форуме</a>';
		}elseif ( $x19['passport.response']['retval']=="405" ) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"/> Вам необходимо получить формальный (или выше) аттестат. Подробнее <a href="http://forum.obmenov.com/viewtopic.php?f=9&t=10" target="_blank">на нашем форуме</a>';
		}elseif ( $x19['passport.response']['retval']=="407" ) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"/> Вам необходимо загрузить на сайт https://passport.webmoney.ru/asp/Upload.asp цветную отсканированную копию всех значимых страниц паспорта и дождаться окончания их проверки. Подробнее <a href="http://forum.obmenov.com/viewtopic.php?f=9&t=10" target="_blank">на нашем форуме</a>';
		}elseif ( $x19['passport.response']['retval']=="409" ) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"/> '.$x19['passport.response']['retdesc'];
		}
		$update="update orders set retval=". $x19['passport.response']['retval'].", status='".$x19['passport.response']['retdesc']."' where id=".$_GET['oid'];
		$query=_query($update,"checkticket");
		//print_r($x19);
	}
	
	
}
?>
<a target="_blank">