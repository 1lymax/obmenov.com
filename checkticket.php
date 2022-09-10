<?php
require_once("Connections/ma.php");
_query('SET character_set_database = cp1251',"");
_query('SET NAMES cp1251',"");
foreach ($_POST as $key => $value) {
    $_POST[$key] = mysqli_real_escape_string($ma,$value);
  }

  //This stops SQL Injection in GET vars
  foreach ($_GET as $key => $value) {
    $_GET[$key] = mysqli_real_escape_string($ma,$value);
  } 
  
if ( isset($_GET['seed']) && isset($_GET['purse']) && isset($_GET['ptype']) ) { 
	if ( substr($_GET['purse'],0,1)!=$_GET['ptype'] &&
				( substr($_GET['purse'],0,1)=="R" || substr($_GET['purse'],0,1)=="Z" || substr($_GET['purse'],0,1)=="U" 
																					|| substr($_GET['purse'],0,1)=="E") ) {
		
		echo '<img src="i/attention.gif" width="11" height="11" title="Указан неправильный тип кошелька">';die();}																  
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
	echo '<img src="i/attention.gif" width="11" height="11" title="Неправильный номер кошелька">';
	}
	die();
}else {echo '<img src="i/attention.gif" width="11" height="11" title="Номер кошелька в данный момент не может быть проверен">';}

}

if ( isset($_GET['type']) && $_GET['type']=="nikname" && isset($_GET['string']) ) {
	$select="select id from clients where nikname='".htmlspecialchars($_GET['string'])."'";
	$query=_query($select, "checkticket.php nikname");
	if ( $query->num_rows==1 ) {
		echo '<img src="i/attention.gif" width="11" height="11" title="Имя '.htmlspecialchars($_GET['string']).' уже используется другим пользователем">';
	}else {
		echo '<img src="i/li_sm.gif" width="11" height="10" alt="ОК" />';
	}
}
if ( isset($_GET['type']) && $_GET['type']=="email" && isset($_GET['string']) ) {
	$select="select id from clients where email='".htmlspecialchars($_GET['string'])."'";
	$query=_query($select, "checkticket.php email");
	if ( $query->num_rows==1 ) {
		echo '<img src="i/attention.gif" width="11" height="11" title="Адрес '.htmlspecialchars($_GET['string']).' занят другим пользователем">';
	}else {
		if ( strstr(htmlspecialchars($_GET['string']), "@") && strstr(htmlspecialchars($_GET['string']), ".") ) {
			echo '<img src="i/li_sm.gif" width="11" height="10" alt="ОК" />';
		}else {
			echo '<img src="i/attention.gif" width="11" height="11" title="Неправильный формат e-mail">';	
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
			echo ' <img src="i/li_sm.gif" width="11" height="10" alt="Кошелек принадлежит идентификатору '.
				htmlspecialchars($_GET['string2']).'"/>';
			die();
		}else {echo ' <img src="i/attention.gif" width="11" height="11" title="Кошелек не принадлежит идентификатору '.
				htmlspecialchars($_GET['string2']).'"/>';die();}
	}else{
		echo '<img src="i/attention.gif" width="11" height="11" title="Неправильный номер кошелька"/>';
	}
	die();
}else {echo '<img src="i/attention.gif" width="11" height="11" title="Номер кошелька в данный момент не может быть проверен"/>';}

	
}
if ( isset($_GET['seed']) && isset($_GET['type']) && $_GET['type']=="acc" ) {
	$select="select orders.id from orders 
			where orders.fname='".iconv("utf-8","windows-1251",htmlspecialchars(rawurldecode($_GET['string2'])))."'
			and orders.iname='".iconv("utf-8","windows-1251",htmlspecialchars(rawurldecode($_GET['string3'])))."'
			and orders.account='".htmlspecialchars($_GET['string'])."'
	and length(fname)>0 and length(iname)>0  and char_length(account)>0 and orders.needcheck=0";
	$query=_query2($select,"checkticket.php 23");

	if ( $query->num_rows>0 ) {
		$row=$query->fetch_assoc();
		$r='<img src="i/li_sm.gif" width="11" height="10"> Реквизиты проверены.</span>';
	}else{
		$r='<img src="i/attention.gif" width="11" height="11"> Требуется проверка Вашей банковской карты. <br />
Заполните все необходимые поля и нажмите кнопку "Далее".<br />
После проверки реквизитов оператором, обмен будет производиться в автоматическом режиме</span>';
	}
	//if ( substr_count($_GET['string'], "44058858")!=0 ) $r= '<br /><img src="i/attention.gif" width="11" height="11"> Уважаемый клиент!<br />Просим обратить внимание, что вывод на карты, начинающихся последовательностью цифр 44058858 не производится.</span>';
	echo $r;

}

if ( isset($_GET['type']) && $_GET['type']=="x19" ) {
	//echo 5;
	$order = "SELECT *	FROM orders WHERE orders.id=".$_GET['oid'].";";
			$row_order=_query2($order, 17);
			$row_order=$row_order->fetch_assoc();
	$fname = isset ($_GET['fname']) ? $_GET['fname'] : "";
	$iname = isset ($_GET['iname']) ? $_GET['iname'] : "";
	$account = isset ($_GET['account']) ? $_GET['account']  : "";
	$wmid = isset ($_GET['wmid']) ? $_GET['wmid'] : "";
	$passport = isset ($_GET['pass']) ? $_GET['pass'] : "";
	$phone = isset ($_GET['phone']) ? $_GET['phone'] : "";
	
	$order=new orders();
	$x19=$order->check_X19($row_order, "get");
	//print_r($x19);
	if ( isset($x19['w3s.response']) ) {
		if ( $x19['w3s.response']['retval'] == 1 ){
			if ( $x19['w3s.response']['testwmpurse']['purse']=='' ) {
				echo '<br /><img src="i/attention.gif" width="11" height="11"/> Заявка не может быть проведена по указанным реквизитам. Кошелек-получатель должен принадлежать идентификатору '.htmlspecialchars($wmid);
			}else{
				echo '<img src="i/li_sm.gif" width="11" height="10"> Реквизиты проверены и удовлетворяют требованиям';
			}
		}else{
			echo '<img src="i/attention.gif" width="11" height="11"/>Заявка не может быть проведена по указанным реквизитам';
		}

	}elseif ( isset($x19['passport.response']) ) { 
		if ( $x19['passport.response']['retval']=="500" ) {
			switch ( $x19['passport.response']['retdesc'] ) {
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/fname" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> Не указана фамилия.';break;
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/iname" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> Не указано имя.';break;
				case "ошибка при проверке входных параметров step=20.1" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> Недостаточно данных для проверки.';break;
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/card_number" :
					echo '<img src="i/attention.gif" width="11" height="11"/> Не указан номер банковской карты.';break;
				case "пропущен обязательный параметр /passport.request/userinfo/wmid" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> Не указан WMID.';break;
				case "не указан обязательный для данного типа вызова параметр /passport.request/userinfo/pnomer" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> Не указаны данные паспорта.';break;
				case "неизвестная ошибка" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> Неизвестный ответ от сервера Webmoney. <br />
Попробуйте оформить через 10-15 минут.';break;
					
				
			}
		}elseif ( $x19['passport.response']['retval']=="0" ) {
			echo '<img src="i/li_sm.gif" width="11" height="10"> Обмен с указанными реквизитами разрешен.';
			
		}elseif ( $x19['passport.response']['retval']=="404" ) {
			echo '<img src="i/attention.gif" width="11" height="11"/> Обмен с указанными реквизитами не разрешен. Данные указанные в заявке не совпадают с владельцем WM-идентификатора.';
		}elseif ( $x19['passport.response']['retval']=="408" ) {
			echo '<img src="i/attention.gif" width="11" height="11"/> На указанную банковскую платежную карту не разрешен вывод средств для вашего WMID. см. <a href="http://link.wmtransfer.com/1Q">http://link.wmtransfer.com/1Q</a>.';
			
		}
		
		//print_r($x19);
	}
	
	
}
?>