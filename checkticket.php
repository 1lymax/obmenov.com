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
		
		echo '<img src="i/attention.gif" width="11" height="11" title="������ ������������ ��� ��������">';die();}																  
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
	echo '<img src="i/attention.gif" width="11" height="11" title="������������ ����� ��������">';
	}
	die();
}else {echo '<img src="i/attention.gif" width="11" height="11" title="����� �������� � ������ ������ �� ����� ���� ��������">';}

}

if ( isset($_GET['type']) && $_GET['type']=="nikname" && isset($_GET['string']) ) {
	$select="select id from clients where nikname='".htmlspecialchars($_GET['string'])."'";
	$query=_query($select, "checkticket.php nikname");
	if ( $query->num_rows==1 ) {
		echo '<img src="i/attention.gif" width="11" height="11" title="��� '.htmlspecialchars($_GET['string']).' ��� ������������ ������ �������������">';
	}else {
		echo '<img src="i/li_sm.gif" width="11" height="10" alt="��" />';
	}
}
if ( isset($_GET['type']) && $_GET['type']=="email" && isset($_GET['string']) ) {
	$select="select id from clients where email='".htmlspecialchars($_GET['string'])."'";
	$query=_query($select, "checkticket.php email");
	if ( $query->num_rows==1 ) {
		echo '<img src="i/attention.gif" width="11" height="11" title="����� '.htmlspecialchars($_GET['string']).' ����� ������ �������������">';
	}else {
		if ( strstr(htmlspecialchars($_GET['string']), "@") && strstr(htmlspecialchars($_GET['string']), ".") ) {
			echo '<img src="i/li_sm.gif" width="11" height="10" alt="��" />';
		}else {
			echo '<img src="i/attention.gif" width="11" height="11" title="������������ ������ e-mail">';	
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
			echo ' <img src="i/li_sm.gif" width="11" height="10" alt="������� ����������� �������������� '.
				htmlspecialchars($_GET['string2']).'"/>';
			die();
		}else {echo ' <img src="i/attention.gif" width="11" height="11" title="������� �� ����������� �������������� '.
				htmlspecialchars($_GET['string2']).'"/>';die();}
	}else{
		echo '<img src="i/attention.gif" width="11" height="11" title="������������ ����� ��������"/>';
	}
	die();
}else {echo '<img src="i/attention.gif" width="11" height="11" title="����� �������� � ������ ������ �� ����� ���� ��������"/>';}

	
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
		$r='<img src="i/li_sm.gif" width="11" height="10"> ��������� ���������.</span>';
	}else{
		$r='<img src="i/attention.gif" width="11" height="11"> ��������� �������� ����� ���������� �����. <br />
��������� ��� ����������� ���� � ������� ������ "�����".<br />
����� �������� ���������� ����������, ����� ����� ������������� � �������������� ������</span>';
	}
	//if ( substr_count($_GET['string'], "44058858")!=0 ) $r= '<br /><img src="i/attention.gif" width="11" height="11"> ��������� ������!<br />������ �������� ��������, ��� ����� �� �����, ������������ ������������������� ���� 44058858 �� ������������.</span>';
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
				echo '<br /><img src="i/attention.gif" width="11" height="11"/> ������ �� ����� ���� ��������� �� ��������� ����������. �������-���������� ������ ������������ �������������� '.htmlspecialchars($wmid);
			}else{
				echo '<img src="i/li_sm.gif" width="11" height="10"> ��������� ��������� � ������������� �����������';
			}
		}else{
			echo '<img src="i/attention.gif" width="11" height="11"/>������ �� ����� ���� ��������� �� ��������� ����������';
		}

	}elseif ( isset($x19['passport.response']) ) { 
		if ( $x19['passport.response']['retval']=="500" ) {
			switch ( $x19['passport.response']['retdesc'] ) {
				case "�� ������ ������������ ��� ������� ���� ������ �������� /passport.request/userinfo/fname" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> �� ������� �������.';break;
				case "�� ������ ������������ ��� ������� ���� ������ �������� /passport.request/userinfo/iname" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> �� ������� ���.';break;
				case "������ ��� �������� ������� ���������� step=20.1" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> ������������ ������ ��� ��������.';break;
				case "�� ������ ������������ ��� ������� ���� ������ �������� /passport.request/userinfo/card_number" :
					echo '<img src="i/attention.gif" width="11" height="11"/> �� ������ ����� ���������� �����.';break;
				case "�������� ������������ �������� /passport.request/userinfo/wmid" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> �� ������ WMID.';break;
				case "�� ������ ������������ ��� ������� ���� ������ �������� /passport.request/userinfo/pnomer" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> �� ������� ������ ��������.';break;
				case "����������� ������" : 
					echo '<img src="i/attention.gif" width="11" height="11"/> ����������� ����� �� ������� Webmoney. <br />
���������� �������� ����� 10-15 �����.';break;
					
				
			}
		}elseif ( $x19['passport.response']['retval']=="0" ) {
			echo '<img src="i/li_sm.gif" width="11" height="10"> ����� � ���������� ����������� ��������.';
			
		}elseif ( $x19['passport.response']['retval']=="404" ) {
			echo '<img src="i/attention.gif" width="11" height="11"/> ����� � ���������� ����������� �� ��������. ������ ��������� � ������ �� ��������� � ���������� WM-��������������.';
		}elseif ( $x19['passport.response']['retval']=="408" ) {
			echo '<img src="i/attention.gif" width="11" height="11"/> �� ��������� ���������� ��������� ����� �� �������� ����� ������� ��� ������ WMID. ��. <a href="http://link.wmtransfer.com/1Q">http://link.wmtransfer.com/1Q</a>.';
			
		}
		
		//print_r($x19);
	}
	
	
}
?>