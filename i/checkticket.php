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
		
		echo '<img src="images/attention.gif" width="11" height="11" title="������ ������������ ��� ��������">';die();}																  
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
	echo '<img src="images/attention.gif" width="11" height="11" title="������������ ����� ��������">';
	}
	die();
}else {echo '<img src="images/attention.gif" width="11" height="11" title="����� �������� �� ������ ������ �� ����� ���� ��������">';}

}

if ( isset($_GET['type']) && $_GET['type']=="nikname" && isset($_GET['string']) ) {
	$select="select id from clients where nikname='".substr(htmlspecialchars($_GET['string']),0,15)."'";
	$query=_query($select, "checkticket.php nikname");
	if ( mysql_num_rows($query)==1 ) {
		echo '<img src="images/attention.gif" width="11" height="11" title="��� '.htmlspecialchars($_GET['string']).' ��� ������������ ������ �������������">';
	}else {
		echo '<img src="images/new/li_sm.gif" width="11" height="10" alt="��" />';
	}
}
if ( isset($_GET['type']) && $_GET['type']=="email" && isset($_GET['string']) ) {
	$select="select id from clients where email='".substr(htmlspecialchars($_GET['string']),0,50)."'";
	$query=_query($select, "checkticket.php email");
	if ( mysql_num_rows($query)==1 ) {
		echo '<img src="images/attention.gif" width="11" height="11" title="����� '.htmlspecialchars($_GET['string']).' ����� ������ �������������">';
	}else {
		if ( strstr(htmlspecialchars($_GET['string']), "@") && strstr(htmlspecialchars($_GET['string']), ".") ) {
			echo '<img src="images/new/li_sm.gif" width="11" height="10" alt="��" />';
		}else {
			echo '<img src="images/attention.gif" width="11" height="11" title="������������ ������ e-mail">';	
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
			echo ' <img src="images/new/li_sm.gif" width="11" height="10" alt="������� ����������� �������������� '.
				htmlspecialchars($_GET['string2']).'"/>';
			die();
		}else {echo ' <img src="images/attention.gif" width="11" height="11" title="������� �� ����������� �������������� '.
				htmlspecialchars($_GET['string2']).'"/>';die();}
	}else{
		echo '<img src="images/attention.gif" width="11" height="11" title="������������ ����� ��������"/>';
	}
	die();
}else {echo '<img src="images/attention.gif" width="11" height="11" title="����� �������� � ������ ������ �� ����� ���� ��������"/>';}

	
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
		echo '<img src="images/new/li_sm.gif" width="11" height="10"><br /> <span style="font-size:10px; color:#666;">��������� ���������. ��� ��������� �������, �� �������� �������� �� ���� � ������� ���������� �����.</span>';
	}else{
		echo '<img src="images/attention.gif" width="11" height="11"><br /> <span style="font-size:10px; color:#666;">��������� �������� ����������, ��������� � ������. <br />
����� �������� ���������� (������ � ������� ���� � ������� �����), �� �������� �������� �� ��������� ����. � ���������� �� ������� ����������� ����� �������������� ����������� ������ �������!</span>';
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
	/*if ( substr($row_order['currin'],0,3)=="MCV" && strlen($wmid)!=0 ) { // �������� �� ����� ����� 2� ��� � ����
		$client=new client();
		$day_summ=(int)$client->day_summ($wmid);
		$summ=$row_order['attach']+	$day_summ;
		if ($summ>2000) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"> ��� ���� ����������� ��������� �� ����� 2000 WMZ � ����� (��� ����������� � ������ ������) �� ���� �������� Webmoney. �� ������� ������ �� �������� '.$day_summ.' WMZ � �����������.';die();
		} else {
			echo '<img src="images/new/li_sm.gif" width="11" height="10"> ����� ��������';die();
			
		}
	}*/
	
	
	$order=new orders();
	$x19=$order->check_X19($row_order, "get");
	//print_r($x19);
	if ( isset($x19['w3s.response']) ) {
		if ( $x19['w3s.response']['retval'] == 1 ){
			if ( $x19['w3s.response']['testwmpurse']['purse']=='' ) {
				echo '<br /><img src="images/attention.gif" width="11" height="11"/> ������ �� ����� ���� ��������� �� ��������� ����������. �������-���������� ������ ������������ �������������� '.htmlspecialchars($wmid);
			}else{
				echo '<img src="images/new/li_sm.gif" width="11" height="10"> ��������� ��������� � ������������� �����������';
			}
		}else{
			echo '<img src="images/attention.gif" width="11" height="11"/>������ �� ����� ���� ��������� �� ��������� ����������';
		}

	}elseif ( isset($x19['passport.response']) ) { 
		if ( $x19['passport.response']['retval']=="500" ) {
			switch ( $x19['passport.response']['retdesc'] ) {
				case "�� ������ ������������ ��� ������� ���� ������ �������� /passport.request/userinfo/fname" : 
					echo '<img src="images/attention.gif" width="11" height="11"/> �� ������� �������';break;
				case "�� ������ ������������ ��� ������� ���� ������ �������� /passport.request/userinfo/iname" : 
					echo '<img src="images/attention.gif" width="11" height="11"/> �� ������� ���';break;
				case "������ ��� �������� ������� ���������� step=20.1" : 
					echo '<img src="images/attention.gif" width="11" height="11"/> ������������ ������ ��� ��������';break;
				case "�������� ������������ �������� /passport.request/userinfo/wmid" : 
					echo '<img src="images/attention.gif" width="11" height="11"/> �� ������ WMID';break;
				case "�� ������ ������������ ��� ������� ���� ������ �������� /passport.request/userinfo/pnomer" : 
					echo '<img src="images/attention.gif" width="11" height="11"/> �� ������� ������ ��������';break;
					
				
			}
		}elseif ( $x19['passport.response']['retval']=="0" ) {
			echo '<img src="images/new/li_sm.gif" width="11" height="10"> ����� � ���������� ����������� ��������';
			
		}elseif ( $x19['passport.response']['retval']=="404" ) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"/> ����� � ���������� ����������� �� ��������. ������ ��������� � ������ �� ��������� � ���������� WM-��������������. ��������� <a href="http://forum.obmenov.com/viewtopic.php?f=9&t=10" target="_blank">�� ����� ������</a>';
		}elseif ( $x19['passport.response']['retval']=="408" ) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"/> ',$x19['passport.response']['retdesc'].'. ��������� <a href="http://forum.obmenov.com/viewtopic.php?f=9&t=10" target="_blank">�� ����� ������</a>';
		}elseif ( $x19['passport.response']['retval']=="405" ) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"/> ��� ���������� �������� ���������� (��� ����) ��������. ��������� <a href="http://forum.obmenov.com/viewtopic.php?f=9&t=10" target="_blank">�� ����� ������</a>';
		}elseif ( $x19['passport.response']['retval']=="407" ) {
			echo '<br /><img src="images/attention.gif" width="11" height="11"/> ��� ���������� ��������� �� ���� https://passport.webmoney.ru/asp/Upload.asp ������� ��������������� ����� ���� �������� ������� �������� � ��������� ��������� �� ��������. ��������� <a href="http://forum.obmenov.com/viewtopic.php?f=9&t=10" target="_blank">�� ����� ������</a>';
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