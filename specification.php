<?php require_once('Connections/ma.php');
require_once('function.php');
//die();
$TheorSumm=0;
$row_discountammount=0;
$num_row_client=0;
$bank_comment="";
$errmessage['noresponse']="� ������ ������ ���������� ��������� ��������� �� ������. <br />
���������� ����������� ������� ����� ��������� �����.";
$errmessage['purse_in_invalid']="����������� ������ �������-����������";
$errmessage['purse_out_invalid']="����������� ������ �������-����������";
$errmessage['order_time_exceeded']="��������� ����� ���������� ������. <br />
						��� ���������� ����������� ���������� ��������.";
$errmessage['fname_invalid']="�� ������� �������";						
$errmessage['iname_invalid']="�� ������� ���";
$errmessage['bankcard_invalid']="�� ������ ����� ���������� �����";
$errmessage['input_invalid']="������������ ���������� ��� ��������";
$errmessage['pnomer_invalid']="�� ������� ���������� ������";
$errmessage['bad_phone']="����� �������� ������ ��� ��������� ���������, ��������� �������� � ������ ���������� Webmoney. <br />
��� ���������� ������ ��������� �������� ������ �������� <a href='https://passport.webmoney.ru/asp/mobilever.asp'>https://passport.webmoney.ru/asp/mobilever.asp</a>";
$errmessage['not_allowed']="����� � ���������� ������� ���������� ����������<br />
��� ������ ����� ������, ��� ������, ��������� � ��������� �� ��������� � ������� � ������.";
$errmessage['query_limit']="�������� ����� �������� � ����� ���������� Webmoney �� ��������� ������.<br />
��������� ��������� ����� ����� ��� ��� ��������� �������.";
$errmessage['no_wmid']="�� �� ������� WMID";
$errmessage['bad_wmlogin']="�� �� �������������� ����� ������ Login Webmoney ��� ����� ������ �������.
			������� ������ �������� ������ �� WMID �������������� ���.";
$errmessage['not_allowed_card']="�� ��������� ���������� ��������� ����� �� �������� ����� ������� ��� ������ WMID ��. http://link.wmtransfer.com/1Q";
$errmessage['fattest']="��� ���������� �������� ���������� (��� ����) ��������. <a href='http://forum.obmenov.com/viewtopic.php?f=10&t=88#p140'>��� ��� �������...</a>";
$errmessage['pscan']="��� ���������� ��������� �� ���� https://passport.webmoney.ru/asp/Upload.asp ������� <br />��������������� ����� ���� �������� ������� �������� � ��������� ��������� �� �������� <a href='http://forum.obmenov.com/viewtopic.php?f=10&t=88#p140'>��� ��� �������...</a>";
$errmessage['attest7']="C ������� ����������� � ������� ������ WMID ��� �� ������ 7 �����";
$errmessage['ret=']="� ������ ������ ���������� ��������� ��������� �� ������. <br />
���������� ����������� ������� ����� ��������� �����.";
$errmessage['ret=500']="��������� ����������� ������. ��������� ��������� ����������.";
//$row_discount=1;


$pn=isset($_COOKIE['pn']) ? (int)$_COOKIE['pn'] : 299 ;
$pn=isset($_POST['pn']) ? (int)$_POST['pn'] : $pn ;



if ( isset($_SESSION['authorized']) ){
	$Auth=1;
}else {
	$Auth=0;
}
//������ ������ ��������� ������ (order==ok)
if ( (isset($_POST["order"]) && $_POST["order"]=='ok') || (isset($_GET['oid']) && isset($_GET['clid'])) ){ 

if ( isset($_POST['moneyIn']) && $_POST['moneyIn']=="SMS" && isset($_POST['s_amount']) ) {
		$select="select type from currency where name='".$_POST['moneyOut']."'";
		$query=_query($select,"");
		$row=$query->fetch_assoc();
		$_POST['SumOut']=$_POST['s_amount']*$courses['USD'][$row['type']]/get_setting('sms_profit');
		//maildebugger($_POST);	

}

$sumOut=0;

$predel[1]=99.99;
$predel[2]=999.99;
$predel[3]=4999.99;

if ( isset($_SESSION['AuthUsername']) ) {
	
	$select="select id from clients where nikname='".$_SESSION['AuthUsername']."'";
	$query=_query($select,"index.php 55");
	$clrow=$query->fetch_assoc();
	$clientid_predel=$clrow['id'];
	//echo $select;
}else{
	$clientid_predel=0;
}
;	
if (isset($_POST["order"]) && $_POST["order"]=='ok') {
	$sumIn=isset($_POST['SumIn'])?$_POST['SumIn']:0;
	if ( isset($_POST['SummIn']) ) {$sumIn=$_POST['SummIn'];}
	$sumOut=isset($_POST['SumOut'])?$_POST['SumOut']:0;
	if ( isset($_POST['SummOut']) ) {$sumOut=$_POST['SummOut'];}
	$in=htmlspecialchars(substr($_POST['moneyIn'],0,9));
	$out=htmlspecialchars(substr($_POST['moneyOut'],0,9));
	if ( $sumIn==0 || $sumOut==0 ) {
		header("Location: ".$siteroot."index.php?message=wrong_summ") ;die();
	}
	if ( isset($money[$in][$out]['addon_value']) && $money[$in][$out]['addon_value']==1 ) {
		$addon_value=0;
	}else { 
		$addon_value=(($row_discount['total']-1)/100);
	}
	$predel1=0.001;
	$predel2=0.002;	
	$predel3=0.003;
	// ������ ������
	$select="select value from addon_predel where currname1='".$in."' and currname2='".$out."'
			AND type=1 AND clientid=0 order by date desc";
	$query=_query($select, "specification.php predel_addon 1");
	if ( $query->num_rows != 0 ) {
		$predel1=$query->fetch_assoc();
		$predel1=$predel1['value']/100;
	}
	if ( $clientid_predel!=0 ) {
		$select="select value from addon_predel where currname1='".$in."' and currname2='".$out."'
			AND type=1 AND clientid=".$clientid_predel." order by date desc";
		$query=_query($select, "specification.php predel_addon 1");
		if ( $query->num_rows!=0 ) {
			$predel1=$query->fetch_assoc();
			$predel1=$predel1['value']/100;	
		}
	} // ����� ������ ������
	
	
	// ������ ������
	$select="select value from addon_predel where currname1='".$in."' and currname2='".$out."'
			AND type=2 AND clientid=0 order by date desc";
	$query=_query($select, "specification.php predel_addon 1");
	if ( $query->num_rows != 0 ) {
		$predel2=$query->fetch_assoc();
		$predel2=$predel2['value']/100;
	}
	if ( $clientid_predel!=0 ) {
		$select="select value from addon_predel where currname1='".$in."' and currname2='".$out."'
			AND type=2 AND clientid=".$clientid_predel." order by date desc";
		$query=_query($select, "specification.php predel_addon 2");
		if ( $query->num_rows!=0 ) {
			$predel2=$query->fetch_assoc();
			$predel2=$predel2['value']/100;	
		}
	} // ����� ������ ������	
	
	
	
	// ������ ������
	$select="select value from addon_predel where currname1='".$in."' and currname2='".$out."'
			AND type=3 AND clientid=0 order by date desc";
	$query=_query($select, "specification.php predel_addon 1");
	if ( $query->num_rows != 0 ) {
		$predel3=$query->fetch_assoc();
		$predel3=$predel3['value']/100;
	}
	if ( $clientid_predel!=0 ) {
		$select="select value from addon_predel where currname1='".$in."' and currname2='".$out."'
			AND type=3 AND clientid=".$clientid_predel." order by date desc";
		$query=_query($select, "specification.php predel_addon 3");
		if ($query->num_rows !=0 ) {
			$predel3=$query->fetch_assoc();
			$predel3=$predel3['value']/100;	
		}
	} // ����� ������ ������
	
	
	foreach ($money as $row1){
		foreach ($row1 as $row2){

			if ( $row2['curr1']==$_POST["moneyIn"] && $row2['curr2']==$_POST["moneyOut"] ){
			/*echo 'sumin='.$s. ' percent='.$row2['value'].' course='.$course.'<br>'.$uah_rur;*/
 				$check=$sumIn*$courses[$in]["USD"];//$row2['value'];
				
				if ($check<$predel[1]) {$row_discountammount=0;
				if ($check*1.001>$predel[1]){$row_discountammount=$predel1+0.0001;}}
				if ($check>$predel[1] && $check<$predel[2]) {$row_discountammount=$predel1;}
				if ($check>$predel[2] && $check<$predel[3]) {$row_discountammount=$predel2;}
				if ($check>$predel[3]) {$row_discountammount=$predel3;}
				$tuUSD=round($check,2);
				if ( $tuUSD > 2000 && substr($_POST['moneyIn'],0,3)=="MCV" ) {
					header("Location: ".$siteroot."index.php?message=max_limit") ;
					die();
				}
					$TheorSumm=round($sumIn*$courses[$in][$out]/($row2['value']-$row_discountammount)*(1+$addon_value),2);
			}
		}
	}
if ( substr($in,0,3)=="MCV" ) { $percent_for_courses=0.02; }
if ( $out=="KS" ) { $percent_for_courses=0.031; }
}

if ( $TheorSumm > round($sumOut,2)*(1+$percent_for_courses) || $TheorSumm < round($sumOut,2)/(1+$percent_for_courses) ) {
	
	if ( $_POST['moneyIn']!="SMS" && !isset($_POST['s_amount']) ) {
		myErrorHandler ("1", "������ �������� ����� ������: ".$TheorSumm. ' = ' 
					.htmlspecialchars(round($sumOut,2)) ,'1' , '1');
		$sumOut=$TheorSumm;
	}else{
		$tuUSD=$sumOut;
		$bank_comment=$_POST['s_amount'];
	}
}
// ���������� ����� � �������, � ���������� ���� ���� �������.

	if (isset($_POST["order"]) && $_POST["order"]=='ok') {
		if ( $sumIn==0 || $sumOut==0 ) {
			header("Location: ".$siteroot."index.php?message=wrong_summ") ;die();
		}
 		$clid = isset($clid)? $clid : (isset($_COOKIE['clid']) ? substr($_COOKIE['clid'],0,36) : session_id()); 
  		if ( (isset($_POST['oid']) && isset($_POST['clid']) && isset($_GET['message'])) ||
			 (isset($_GET['oid']) && isset($_GET['clid'])) ) {
			//echo "select";
  			$oid= isset($_POST['oid']) ?  (int)$_POST['oid'] : (int)$_GET['oid'];
  			$clid= isset($_POST['clid']) ?  substr($_POST['clid'],0,36) : substr($_GET['clid'],0,36);
			
			$select = "SELECT id, summin, summout, currin, currout, 
				(select extname from currency where name=orders.currin) as curr1, 
				(select extname from currency where name=orders.currout) as curr2,
				currout, discammount, disc, clid from orders where id=".$oid." AND clid='".$clid."'";
			$query=_query($select, "specification.php 30");
			$row_order=$query->fetch_assoc();
			//print_r($row_order);
			$update="update orders set time=CURRENT_TIMESTAMP(), av_balance=".$WM_amount_r[$row_order['currout']]." where id=".$row_order['id'];
			$query=_query($update, "specification.php 30");
			$update="update orders_reserve set date_upd=CURRENT_TIMESTAMP() where orderid=".$row_order['id'];
			$query=_query($update, "specification.php 30");

		}else {
			$select="select type from currency where name='".$_POST['moneyIn']."'";
			$query=_query($select,'');
			$type=$query->fetch_assoc();
			//$type['type']=($type['type']=='RUB'?"RUR":$type['type']);
			$insertSQL = sprintf("INSERT INTO orders (summin, summout, `currin`, `currout`, `disc`, `discammount`, `attach`, `clid`,
						`authorized`, `partnerid`, date, bank_comment, ip, av_balance, official_course, course2usd) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, ".$Auth.", ".intval($pn).", NOW(),'"
						.$bank_comment."', '".(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']:"")."',".
						$WM_amount_r[$_POST['moneyOut']].", ".$courses[$_POST['moneyIn']][$_POST['moneyOut']].","
						.$courses[$type['type']]['USD'].")",
                       GetSQLValueString($sumIn, "float"),
					   GetSQLValueString($sumOut, "float"),
					   GetSQLValueString($_POST['moneyIn'], "text"),
					   GetSQLValueString($_POST['moneyOut'], "text"),
					   GetSQLValueString($row_discount['total'], "float"),
					   GetSQLValueString(round($sumOut*$addon_value,2), "float"),
					   GetSQLValueString($tuUSD, "float"),
					   GetSQLValueString($clid, "text"));//
			$insert_order=_query($insertSQL, "specification.php 4".$insertSQL." ".print_r($_POST,1));
			$oid=mysqli_insert_id($GLOBALS['ma']);
			$insert="insert into orders_reserve (sum,val,orderid,date_crtd) values (".
													($sumOut+round($sumOut*$addon_value,2)).",'".
													$_POST['moneyOut']."','".
													$oid."',
													CURRENT_TIMESTAMP() )";
			$query=_query($insert,"");
													
																	
			header("Location: ".$siteroot."specification.php?oid=".$oid."&clid=".$clid);
			die();
			unset($_SESSION['ref']);
			
			$select = "SELECT id, summin, summout, currin, currout, 
						(select extname from currency where name=orders.currin) as curr1, 
						(select extname from currency where name=orders.currout) as curr2, 
						(select needcheck from currency where name=orders.currin) as needcheck1, 
						(select needcheck from currency where name=orders.currout) as needcheck2,
				currout, discammount, disc, clid from orders where id=".$oid;
			$query=_query($select, "specification.php 30");
			$row_order=$query->fetch_assoc();
			
		
		}
		
	}elseif ( isset($_GET['oid']) && isset($_GET['clid']) ) {
		$select = "SELECT id, summin, summout, currin, currout, 
				(select extname from currency where name=orders.currin) as curr1, 
				(select extname from currency where name=orders.currout) as curr2,
				(select needcheck from currency where name=orders.currin) as needcheck1, 
				(select needcheck from currency where name=orders.currout) as needcheck2,				
				currout, discammount, disc, clid from orders where id=".(int)$_GET['oid']." and clid='".substr($_GET['clid'],0,36)."'";
		$query=_query($select, "specification.php 30");
		$row_order=$query->fetch_assoc();
	
	}
		
		
 //����� ������ ��������� ������ (order==ok)
}

//����� ������ ������ �� �������. 2-� �������
$in1=0;$in2=0;



if ( isset($row_order['currin']) && isset($row_order['currout']) ) {
$specification=new specification();
$fields=$specification->fields($row_order);

$form_action='specification_redirect.php';

	$query_clientinfo = "SELECT clients.id, clients.name, clients.iname, clients.fname, clients.oname, clients.wmid, clients.purse_z, clients.purse_u, clients.purse_PMUSD, clients.purse_PMEUR, clients.purse_LRUSD, clients.purse_LREUR, clients.purse_INSTAFX, clients.purse_r, clients.purse_e, clients.email, clients.bank, bank_name, mfo, account, inn, clients.passport, clients.clid, clients.phone FROM clients WHERE clients.clid='".$clid."' ORDER BY date desc";
	$clientinfo = _query($query_clientinfo, "specification.php 6");
	$row_clientinfo = $clientinfo->fetch_assoc();


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: ���������� ������</title>
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
        <script type="text/javascript" src="_main.js"></script>
        <script type="text/javascript" src="fun.js"></script>
<script language="javascript">
function reqwmid(el)
{
    document.getElementById(el+"_span").innerHTML='<img src="i/ajax-loader.gif" width="16" height="16" alt="���������" />';
	var r = gen(el);
	//handlewmid();
	if (el=="text") {sndReq(r, handlewmid);}
	if (el=="acc") {sndReq(r, handleacc);}
	if (el=="x19") {sndReq(r, x19);}
    //sndReq(r, handlewmid);
}
function sndReq(req, handleResponse)
{
	http = createRequestObject();
	http.open("get", req);
	http.onreadystatechange = handleResponse;
	http.send(null);
}
function gen(el)
{
    if (el=="text") {
		<?php if (isset($fields['section_wmid'])) { ?>
		var res = "../checkticket.php?seed="+Math.round(100*Math.random()) + "&ptype=<?=$fields['purseType1'];?>&purse="+document.getElementById("<?=$fields['purseType2'];?>").value;
		<?php } ?>
	}else if (el=="acc"){
		var res = "../checkticket.php?seed="+Math.round(100*Math.random()) + "&type=acc&string=" + document.getElementById("account").value + "&string2=" + document.getElementById("fname").value + "&string3=" + document.getElementById("iname").value;
	}else if (el=="x19"){
		pass = !is_null(d.$("passport")) ? d.$("passport").value : "";
		wmid = !is_null(d.$("wmid")) ? d.$("wmid").value : "";
		purse = is_null(d.$("Purse")) ? (!is_null(d.$("purseOut")) ? d.$("purseOut").value : "") : d.$("Purse").value;
		fname = !is_null(d.$("fname")) ? d.$("fname").value : "";
		iname = !is_null(d.$("iname")) ? d.$("iname").value : "";
		phone = !is_null(d.$("phone")) ? d.$("phone").value : "";
		account = !is_null(d.$("account")) ? d.$("account").value : "";
		//iname = isNaN(d.$("account")) ? d.$("account") : "";
		var res = "../checkticket.php?seed="+Math.round(100*Math.random()) + "&type=x19&pass=" + pass + "&wmid=" + wmid + "&fname=" + fname + "&iname=" + iname + "&account=" + account + "&purse=" + purse + "&phone=" + phone + "&oid=<?=$row_order['id']?>";
	}
    return encodeURI(res);
}

function handlewmid()
{
    if(http.readyState == 4)
    {
        //alert (http.responseText);
		document.getElementById("text_span").innerHTML=http.responseText;
		var t = http.responseText;
        var p = t.indexOf("QueryOk");


	}
}
function handleacc()
{
    if(http.readyState == 4)
    {
        //alert (http.responseText);
		document.getElementById("acc_span").innerHTML=http.responseText;
		var t = http.responseText;
        var p = t.indexOf("QueryOk");


	}
}
function x19()
{
    if(http.readyState == 4)
    {
        //alert (http.responseText);
		document.getElementById("x19_span").innerHTML=http.responseText;
		var t = http.responseText;
        var p = t.indexOf("QueryOk");


	}
}
function makestep2(){
<?php if ( isset($fields['section_fname']) ) {echo 'if (d.$("fname").value.length == 0) { alert("�� ������� �������");return(false); }
';} ?>
<?php if ( isset($fields['section_iname']) ) {echo 'if (d.$("iname").value.length == 0) { alert("�� ������� ���");return(false); }
';} ?>
<?php if ( isset($fields['section_oname']) ) {echo 'if (d.$("oname").value.length == 0) { alert("�� ������� ��������");return(false); }
';} ?>
<?php if ( isset($fields['section_phone']) ) {echo 'if ( d.$("phone").value.length == 0 ) { alert("�� ������ ���������� �������"); return(false); }
';} ?>
<?php if ( isset($fields['section_wmid']) ) {echo 'if ( !/[0-9]{12}$/.test(d.$("wmid").value) ) {alert("����������� ������ WMID");return(false);  }
';} ?>
<?php if ( isset($fields['section_purse']) ) {echo 'if ( !/[0-9]{12}$/.test(d.$("Purse").value) ) {alert("����������� ������ ����� ��������");return(false);  }
';} ?>
<?php if ( isset($fields['section_purseOut']) ) {echo 'if ( !/[0-9]{12}$/.test(d.$("purseOut").value) ) {alert("����������� ������ ����� ��������");return(false);  }
';} ?>
<?php if ( isset($fields['section_email']) ) {echo 'if ( !/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(d.$("email").value) ) { alert("����������� ������ Email �����");return(false); 
}';} ?>



if (!d.$("confirm_rules").checked) {alert("��� ���������� ������������ � ����������� � ��������� � ��������� ������"); return(false);}

document.exchange.submit();
}
<?php if ( isset($section_recept) ) { ?>
	
function showwmid () {
	if (document.exchange.recept.checked) {
		d.$("wmid_recept").style.visibility="visible";
	}	
	else {
		d.$("wmid_recept").style.visibility="hidden";
	}	
	
}
	<?php } ?>
	

</script>
	</head>
	<body onLoad="
<?php if ( isset($section_purse) ) { ?>
reqwmid('text');

<?php } if ( isset($section_bank) ) { ?>
reqwmid('acc');
<?php } ?>
">
	    <div class="wrapper">
            <div class="wrapper-inn">

                <?php require_once("siti/inc_top.php");?>

                <div class="middle clear">

                    <!-- Start left column -->
                    <? require_once("siti/inc_left.php");?>
                    <!-- End left column -->

                    <!-- Start central column -->
                    <div class="c-col">

<form action="specification_redirect.php" method="post" name="exchange" id="exchange">
  <table align="center" width="550" border="0">
   
    <tr>
    	<td colspan="2" align="center"><h1>������ �<?=$row_order['id']?>.</h1>
	 <h1>����� <?=$row_order['summin']." ".$row_order['curr1'] ?> �� <?php echo ($row_order['summout']+$row_order['discammount'])." ".$row_order['curr2']; ?></h1>
     <div class="otzyv-date">���� ������ <?=($row_order['disc']*100-100)?>% ��� ������ � �������������� �����.</div></td>
    </tr>	
    <tr>
    	<td colspan="2" align="center" height="60" class="otzyv-name">��������! ������ ������������� � ������� 10 �����.</td>
    </tr>
    <?php if ( isset($_GET['message']) ) { 
		$select="select descr from errors where inname='".$_GET['message']."'";
		$query=_query($select,"");
		$row=$query->fetch_assoc();
	?>
    <tr>
    	<td colspan="2" align="center" height="40" valign="top">
        <span style="color:#F00"><?=$row['descr']?></span>
        </td>
    </tr>   
    
    <?php } ?>
    <tr><td colspan="2"><div class="otzyv"></div></td></tr>
	<tr>
    	<td colspan="2" align="left"><h1>��������� ������:</h1></td>
    </tr>
    
 	<?php if ( isset($fields['economy_mode']) && in_array($row_order['currout'],array("WMZ", "WMU","WMR", "WME")) ) {// && $addon_value==0
	?>   
	<tr><td colspan="2"></td></tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">��� ������:</td>
      <td><p><input name="economy" type="radio" id="economy" style="border:none;" value="1" checked/> ���������� �����<br />
      <span style="font-size:10px; color:#666;">��� ����������� ���������� �����, <br />
����� ���������� � ������� 3-� �����.</span><br />

      	<?php /*?><input name="economy" id="economy" type="radio" value="2" style="border:none;"/> ����������� ����� <br />
	<span style="font-size:10px; color:#666;">�� �������� ������������� <strong>+0,3% (<?=round(($row_order['summout']+$row_order['discammount'])*0.003,2)." ".$row_order['curr2']?>).</strong> <br />����� ������ ���������� � ������� 3-� ����� � ������� �����. <br />
��� ���� ������� ������ ������ ����� ������24 �������� ������ � ������ ������.</span><?php */?> </p>
          </td>
    </tr>
    <?php }
	if(isset($fields['section_wmid'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" width="200" class="text">Webmoney ID:<span style="color:red">*</span> </td>
      <td class="form-normal"><input name="wmid" type="text" id="wmid" value="<?=isset($_SESSION['WmLogin_WMID']) ? $_SESSION['WmLogin_WMID'] : $row_clientinfo['wmid']?>" size="15" maxlength="12"/> <a href="https://login.wmtransfer.com/GateKeeper.aspx?RID=<?=$urlid['specification']."&lang=ru&oid=".$row_order['id']."&clid=".$row_order['clid']?>">��������������</a></td>
    </tr>

    <?php } if(isset($fields['section_name'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" width="50%" class="text">�. �. �. (���������):<span class="red">*</span> </td>
      <td width="50%" class="form-normal"><input type="text" id="name" name="name" 
      <?php if(isset($fields['section_bank'])){ ?>
      onChange="reqwmid('acc')"
      <?php } ?>
      value="<?php echo $row_clientinfo['name']; ?>" size="32" maxlength="40" /></td>
    </tr>
      <?php }  if(isset($fields['section_fname'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">�������:<span class="red">*</span> </td>
      <td class="form-normal"><input name="fname" id="fname" type="text" 
      <?php if(isset($section_bank)){ ?>
      onChange="reqwmid('acc')"
      <?php } ?>
      value="<?=$row_clientinfo['fname']; ?>"size="15" maxlength="40" /></td>
    </tr>
    <?php }  if(isset($fields['section_iname'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">���:<span class="red">*</span> </td>
      <td class="form-normal"><input name="iname" id="iname" type="text"
      <?php if(isset($fields['section_bank'])){ ?>
      onChange="reqwmid('acc')"
      <?php } ?>
      value="<?=$row_clientinfo['iname']; ?>"size="15" maxlength="40" /></td>
    </tr>
    <?php }  if(isset($fields['section_oname'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">��������:<span class="red">*</span> </td>
      <td class="form-normal"><input name="oname" id="oname" type="text" 
      value="<?=$row_clientinfo['oname']; ?>"size="15" maxlength="40" /></td>
    </tr>
	<?php } if(isset($fields['section_bankin'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" width="50%"></td>
      <td width="50%"><span style="font-size:10px; color:#666;">���������� ����� �.�.�. ����, ������������� ������ (�.�. ��������� �����, � ������� �������������� ������)! </span></td>
    </tr>
   <?php } /*if(isset($section_bank)){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" width="50%"></td>
      <td width="50%"><span style="font-size:10px; color:#666;">���������� ����� ������ �.�.�. ����, �� ������� ������ ��������� ����. ��� �� ���������� ������ �������� ����� ���������� �� ����������� �������� Webmoney 0,8%!</span><br />
      <?php <span style="font-size:10px; color:#F00;">��������! � ����� � ������ ������������ ��������� ������� Webmoney, �.�.�., ��������� � ����� ��������� ������ ��������� � �.�.�., �� ������� ������ ��������� ����. � ��������� ������ �� �� ������� ����������� ������.</span>  
      </td>
    </tr>      
    <?php } */   if(isset($fields['section_purse'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">������:<span style="color:#F00">*</span> </td>
      <td class="form-normal">
      <input name="Purse" id="Purse" type="text" value="<?=$fields['purseType'].$row_clientinfo['purse_'.strtolower($fields['purseType'])]; ?>"size="15" maxlength="13" onChange="reqwmid('text')" /> <br /><span id="text_span"></span></td>
    </tr>
    <?php }  if(isset($fields['section_purseOut'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">������:<span style="color:#F00">*</span><br />
	�� ������� �������� ��������</span></td>
      <td class="form-normal">
        <?=$fields['purseTypeOut'];?><input name="PurseOut" id="purseOut" type="text" value="<?= $row_clientinfo['purse_'.strtolower($fields['purseTypeOut'])]==0 ? '' : $row_clientinfo['purse_'.strtolower($fields['purseTypeOut'])]; ?>"size="15" maxlength="13" 
        <?php if(isset($fields['section_wmid'])){ ?>
        onChange="<?=($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? "" : "reqwmid('text')")?>" 
        <?php } ?>/> <br />
<span id="text_span"></span></td>
    </tr>
    <?php } if(isset($fields['section_recept'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right"></td>
      <td class="form-normal"><input name="recept" type="checkbox" id="recept" onChange="showwmid();" 
      <?=isset($_GET['recept']) ? 'checked="checked' : ""?> value="" /> ������ ����� ������� �����<br />
		        <?php if ( isset($_SESSION['WmLogin_WMID']) ) { ?>
        <span style="font-size:10px; color:#666; visibility:<?=isset($_GET['recept']) ? 'visible' : "hidden"?>" id="wmid_recept">

        ���� ����� ������� ��� WMID <?=$_SESSION['WmLogin_WMID']?>.
        <a href="https://login.wmtransfer.com/GateKeeper.aspx?RID=<?=$urlid?>&<?="oid=".$oid."&clid=".$clid."&recept=on"?>">�������� WMID</a>
        </span>
        <? } else { ?>
        <span style="font-size:10px; color:#666; visibility:hidden" id="wmid_recept">
        ��� ����, ����� �������� ������ ����� ������� �����,<br />
��� ���������� <a href="https://login.wmtransfer.com/GateKeeper.aspx?RID=<?=$urlid?>&<?="oid=".$oid."&clid=".$clid."&recept=on"?>">��������������</a> ����� ������ Webmoney.Login</span>
		<?php } ?>
</td>
    </tr>
    <?php } if(isset($fields['section_email'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">E-mail:<span style="color:#F00">*</span></td>
      <td class="form-normal"><input type="text" id="email" value="<?=$row_clientinfo['email']; ?>" name="email" size="40" /></td>
    </tr>
    <?php } if(isset($fields['section_phone'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">���������� �������:<span style="color:#F00">*</span></td>
      <td class="form-normal"><input type="text" id="phone" value="<?=$row_clientinfo['phone']; ?>" name="phone" size="32" maxlength="15"/></td>
    </tr>
    <?php } if(isset($fields['section_phone_recharge'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">����������� �������:<span style="color:#F00">*</span><br />
<span style="font-size:10px; color:#666;">��������� ����� ������ ��������<br />
��������������� �������<br />(��������, Djuice, �������)</span></td>
      <td class="form-normal"><input type="text" id="phone" value="<?=$row_clientinfo['phone']; ?>" name="purse_other" size="32" maxlength="15"/></td>
    </tr>
    <?php } if(isset($fields['section_pm_purse'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">������ � ������� <br />PerfectMoney (<?=substr($row_order['currout'],2,1)?>xxxxxxx):<span style="color:#F00">*</span></td>
      <td class="form-normal"><input type="text" value="<?=($row_clientinfo['purse_'.$row_order['currout']]!=""?$row_clientinfo['purse_'.$row_order['currout']]:substr($row_order['currout'],2,1)); ?>" name="purse_other" size="32" maxlength="15"/></td>
    </tr>
    <?php } if(isset($fields['section_lr_purse'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">������� � ������� <br />Liberty Reserve:<span style="color:#F00">*</span></td>
      <td class="form-normal"><input type="text" value="<?=($row_clientinfo['purse_'.$row_order['currout']]!=""?$row_clientinfo['purse_'.$row_order['currout']]:"U"); ?>" name="purse_other" size="32" maxlength="15"/></td>
    </tr>
    <?php } if(isset($fields['section_forex_acc'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text">������� � ������� <br /><?=$row_order['curr2']?><span style="color:#F00">*</span></td>
      <td class="form-normal"><input type="text" value="<?=$row_clientinfo['purse_'.$row_order['currout']]; ?>" name="purse_other" size="32" maxlength="15"/></td>
    </tr>
    <?php } if(isset($fields['section_comment_phone'])){ ?>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right" class="text"></td>
      <td class="form-normal">� ������������� ������� (+������������).<br />
������� ������ ����� ������ "��������" � ��������� Webmoney. ������� �������� ��������, ��� ������ ����� ��������� ���� � ��� ������, ����� �������-���������� � �������-���������� ���������.</td>
    </tr>
    <?php } if(isset($fields['section_r_bank_account'])){ ?> 
    <tr id="sec_rs_bank" name="sec_alfa_out">
    	<td align="right" class="text">����� �����<br />(20 ����):<span style="color:#F00">*</span></td>
        <td align="left" valign="top" class="form-normal"><input type="text" id="account" value="<?=$row_clientinfo['account']; ?>" 
        name="account" size="32" maxlength="32"/></td>
    </tr>
    <tr><td colspan="2" class="text">
    <?php if ( icqstatus_get_status2('474354186') ) { ?>
    	<img src="i/icq-on.gif" width="16" height="16" alt="online" />
    <?php }else{ ?>
    	<img src="i/icq-off.gif" width="16" height="16" alt="offline" />
    <?php } ?>
    �������� �/�� ���������� �����
�������������� � ������ ������.</td></tr>
     <?php } if(isset($fields['unk'])){ ?> 
    <tr id="sec_rs_bank" name="sec_unk">
    	<td align="right" class="text">���:<span style="color:#F00">*</span></td>
        <td align="left" valign="top" class="form-normal"><input type="text" id="unk" value="<?=$row_clientinfo['inn']; ?>" 
        name="unk" size="32" maxlength="32"/></td>
    </tr>
    <?php } if(isset($fields['section_passport'])){ ?>
    <tr valign="top">
      <td align="right" class="text"><div>������ ��������<span style="color:#F00">*</span><br><span style="font-size:10px; color:#666;">����������� ���������� ������ ������.<br>��� ������������ ������, <br>�� �� ������� �������� ��������</span></div></td>
      <td class="form-normal"><input name="passport" id="passport" size="12" value="<?php echo $row_clientinfo['passport']; ?>"/><br />
<span style="font-size:10px; color:#666;">(����� � �����, ��������, ��984501): </span></td>
    </tr>
    <?php }	if(isset($fields['wire_eur_iban'])){ ?>
    <tr id="sec_iban" name="sec_iban" class="text">
    	<td align="right">��� IBAN (27 ��������)<span style="color:red">*</span></td>
        <td align="left" valign="top" class="form-normal"><input type="text" id="account" value="<?=$row_clientinfo['account']; ?>" 
        name="account" size="32" maxlength="32"/></td>
    </tr>
    <tr valign="top" id="sec_bank" name="sec_bank" class="text">
      <td nowrap="nowrap" align="right">���������� � ������� (50 ��������):<br></td>
      <td class="form-normal"><input type="text" id="bank" name="bank" size="32" maxlength="50"/> </td>
    </tr>
    <?php } if(isset($fields['section_bank'])){ ?>
    <tr>
    	<td></td><td align="left" class="text">���������� ���������:</td>
    </tr>
    <?php 
	$select="select account, account_comment from bank_accounts where clid='".$row_order['clid']."' group by account";
	$query=_query($select,"");
	?>
	<?php if ( $query->num_rows!=0 ) {?>
    <script>
	card=new Array;
    <?php while ( $cards=$query->fetch_assoc() ){?>
	card['<?=$cards['account']?>']='<?=$cards['account_comment']?>';
	<?php } ?>
    </script>
    <?php $select="select account, account_comment from bank_accounts where clid='".$row_order['clid']."' group by account";
		$query=_query($select,"");
	?>
    <tr>
    	<td align="right" class="text">�������� ����� �� ������:</td>
        <td align="left" height="20">
        <select name="cards" onchange="d.$('account').value=this.value;d.$('account_comment').value=card[this.value];reqwmid('acc');" onblur="d.$('account').value=this.value;reqwmid('acc');">
        	<option></option>
        	<?php while ( $row=$query->fetch_assoc() ) { ?>
            <option value="<?=$row['account']?>"><?=$row['account'].
									(strlen($row['account_comment'])!=0 ? " - ".$row['account_comment']:"")?></option>
            <?php } ?>
        </select> ���
        </td>
    </tr>
    <?php } ?>
    <tr id="sec_account">
        <td align="right" valign="top" class="text">������� ����� �����/�����<br />(16 ����):<span style="color:#F00">*</span></td>
        <td align="left" class="form-normal"><input type="text" id="account" 
        name="account" size="22"
        <?php if ( isset($fields['section_bank']) && $row_order['needcheck1'] ) {	?> 
        onChange="reqwmid('acc')" 
        <?php } ?>
        maxlength="19" value="<?=$row_clientinfo['account'];?>"/> <br /><?php /*?><img src="i/attention.gif" width="11" height="11"> ��������� ������!<br />������ �������� ��������, ��� ����� �� �����, ������������ ������������������� ���� 44058858 �� ������������. ����� �� ��� ��������� ����� ������������ � ������� ������.<br /><?php */?></td>
    </tr>
    <tr>
    	<td align="right" class="text">���������� � �����:</td>
        <td align="left" class="form-normal"><input type="text" id="account_comment" value="" 
        name="account_comment" size="22" maxlength="30"/><br />
		<span style="font-size:10px; color:#666;">�������������� ����. <br />
������� ����� ������� ��� ��� �������� �����. <br />
��������, ��������� ��� ����. � ������� ��� ���������� � ��� <br />
����� ������� �������� ����� �� ������.</span></td>
    </tr>
    
    <?php if(isset($fields['section_mfo'])){ ?>
    <tr id="sec_mfo" name="sec_mfo">
    	<td align="right" class="text">���:<span style="color:#F00">*</span></td>
        <td align="left" class="form-normal"><input type="text" id="mfo" value="<?=$row_clientinfo['mfo']; ?>" 
        name="mfo" size="10" maxlength="6"/></td>
    </tr>

    <?php } if(isset($fields['section_bankname'])){ ?> 
    <tr id="sec_bank_name" name="sec_bank_name">
    	<td align="right" class="text">������������ �����<br><span style="font-size:10px; color:#666;">������� �� �����������.<br>������ ��� ������� ������������ ����� ���</span></td>
        <td align="left" valign="top" class="form-normal"><input type="text" id="bank_name" value="<?=$row_clientinfo['bank_name']; ?>" 
        name="bank_name" size="32" maxlength="32"/></td>
    </tr>
    <?php } if(isset($fields['section_inn'])){ ?>
    <tr id="sec_inn" name="sec_inn" class="text">
    	<td align="right">��� (������)<br><span style="font-size:10px; color:#666;">�������������� ��������� �����<span style="color:red">*</span></span></td>
        <td align="left" valign="top" class="form-normal"><input type="text" id="inn" value="<?=$row_clientinfo['inn']; ?>" 
        name="inn" size="32" maxlength="32"/></td>
    </tr>
    <?php } if(isset($fields['section_bankcomment'])){ ?>
    <tr valign="top" id="sec_bank" name="sec_bank">
      <td nowrap="nowrap" align="right">����������:<br>
      <span style="font-size:10px; color:#666;" id="comment">������� ����� �������������� ���������, <br>
		����������� ��� ���������� �������.<br>���� �� �� ������ ��� ����� ������, <br>
        �������� ��� ���� ������.</span></td>
      <td class="form-normal"><textarea name="bank" id="bank" cols="32" rows="5"/><?php echo $row_clientinfo['bank'];?></textarea></td>
    </tr>  
	<?php } 
	}?>
    <tr><td colspan="2">
       	<table class="tableborder2" width="550"><tr><td>&nbsp;</td></tr></table>
    </td></tr>
    <?php if (isset($fields['section_wmid'])) { ?>
     <tr><td></td><td><div class="otzyv"></div></td></tr>
     <tr>
    	<td align="right" width="200"></td>
        <td align="left" valign="top">
        �������� ��������� �������� Webmoney: <br />
        � ����� � ������ ������������ ��������� ������� Webmoney, ����� �� ������� ��� �������� � ��� �������� � ���������� ������� ������ ������ �������� ����������. ����� ��������� ��������� ������� ������� ������ "��������".<br />
     <tr>
    	<td align="right"></td>
        <td><input class="button1" type="button" value="��������" onClick="reqwmid('x19')"/><br /><br />
<strong><span id="x19_span"></span></strong><br /> </td>
    </tr>
    <?php } if ( isset($fields['section_bank']) && $row_order['needcheck1'] ) {	?>
    <tr><td></td><td><div class="otzyv"></div></td></tr>
    <tr><td></td><td align="left">�������� �������� <?=get_setting('site_title_sht'.$urlid['site_curr2'])?> �������������� ���������� ����� �� ���, ��������� � ������ (�������������� ������������� ��� ����� ������ ����� ��� ����� ������� ������ "��������").<br />
    <input class="button1" type="button" value="��������" onClick="reqwmid('acc')"/><br /><br />
<strong><span id="acc_span"></span></strong></td></tr>
	<?php
	}	if ( isset($fields['section_protection']) ) {
	?>   
	<tr class="tableborder2"><td colspan="2"></td></tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input name="protection" id="protection" type="checkbox" style="border:none;"/> ������������ ��������� ������.<br />
      ����������� ������������ ��������� ������. ��� ������� ��������� ��������� ������ ��� ����� ������ �������� � ���������� ������.<br />
		<font style="color:#F00">��������!</font> ��� ��������� ����� ������ �� ���������� ������ ����������� �����, �� ��� WMID, 
        � ����� �������� � ���������� ����� ������.
          </td>
    </tr>
    <?php }
	 ?>
    <tr><td></td><td><div class="otzyv"></div></td></tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><br /><input name="confirm_rules" id="confirm_rules" type="checkbox" style="border:none;"/> � ����������� � �������� <a href="<?=$siteroot?>about.php" target="_blank">� ��������� � ��������� ������</a>. <br />� �������� ������������� ����� obmenov.com � obmenov.biz �������� � ������������ ��� ������������ ������. � �������, ������������ � ����� � ���������� ���� ������������ ������, � ������ ��������� � ������������� ���� ������������ ������ ����������.
          </td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td>
      <input class="button1" type="button" value="�����>>" onClick="javascript:makestep2();"/>
	  </td>
    </tr>
    <?php	if ( isset($fields['section_mastercard']) ) {
	?> 
    <tr valign="baseline">
      <td nowrap="nowrap" align="left"><br />
</td>
      <td><span style="color:#F00">
      ��������!</span><br />
 �������� ��������� �������� � ���������� �������, ��������� � <br />
	������������ ����� �����  ��� ������� �������������� ��������. <br />
    �������� �������� �������������� �������� �������������� �������, ��������� ������������ �����.<br />
����� ������� ������ "�����", �� ������ �������������� �� �������������� ������ ��������� �������� �� ������ VISA � MasterCard.
      <br />����������: ���� ����� ������ ���� ������������ ��� �������� � ��������.<br /><img src="/i/logo_MC.png" width="79" height="35" alt="MasterCard" /><img src="/i/logo_Visa.png" width="79" height="36" alt="VISA" /><br /><br>
      

      </td>
    </tr>
    <?php } ?>
    <tr valign="baseline">
      <td align="center" colspan="2"><br />
<br />
	<?php if (isset($fields['section_wmcomment'])) { ?>
   	<table class="tableborder2" width="500"><tr><td>������������ ������ � ������ ��������������� �� �� ������ ���� ���� �����������, ���������������� ������� WebMoney Transfer. �� �������� ����������� ������������, ����������� ������, � �������������� ��������� ������� � ����� � ������������. �����������, ��������������� ������� WebMoney Transfer, �� �������� ������������ �������������� ��� ���� �������������� �� ������� � �������������� ����� � �� ����� ������� ��������������� �� ���� ������������.<br />
<br />
����������, ������������� �� ������� WebMoney Transfer, ���� ������������ ���� ��������� ��� ����� � ������������ ��������. ��� �������������� �� ������ ������� � �� ��������, ��� �� �����-���� ������� ������� � ��������� ���������� ������� WebMoney.</td></tr></table>
<?php } ?>
      
	  </td>
    </tr>
  </table>
  <input type="hidden" name="mm" value="<?=($in1+$in2); ?>" />
  <?php if (isset($fields['purseType'])) {?>
  <input type="hidden" name="purseType" value="<?=$fields['purseType']; ?>" />
  <?php } ?>
  <?php if(isset($fields['section_purseOut'])){ ?>  <input type="hidden" name="purseTypeOut" value="<?=$fields['purseTypeOut']; ?>" /><?php }
	$select="select id from payment where orderid=".$row_order['id'];
	$query=_query($select, "specification.php 56");
	if ( $query->num_rows == 1 ) {
		$row_payment=$query->fetch_assoc();
		$paymentid=$row_payment['id'];
		$row_rnd=_array("select rnd from payment where id=".$paymentid);
		$rnd=$row_rnd['rnd'];
	}else{
		$rnd = strtoupper(substr(md5(uniqid(microtime(), 1)).getmypid(),1,8));
		$query = 'INSERT INTO payment SET orderid="'.$row_order['id'].'", RND="'.$rnd.'", timestamp=CURRENT_TIMESTAMP();';
		$result = _query($query, "specification.php 8");
		$paymentid=mysqli_insert_id($GLOBALS['ma']);
	}
	if ( $fields['form_action']=='https://merchant.webmoney.ru/lmi/payment.asp' ) {
	?>
	<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?=$row_order['summin']?>" />
	<input type="hidden" name="comment1" value="<?=$row_order['id'].'. '.
	$row_order['summin'].' '.$row_order['curr1'].' - '.
	($row_order['summout']+$row_order['discammount']).' '.$row_order['curr2']?>"/>
	<input type="hidden" name="LMI_PAYMENT_NO" value="<?=$paymentid?>" />
	<input type="hidden" name="LMI_PAYEE_PURSE" value="<?=$shop_wm_purse[$fields['purseType']]?>" />
	<input type="hidden" name="LMI_SIM_MODE" value="<?=$LMI_SIM_MODE?>" />
    <?php		
  }elseif ( $fields['form_action']=='https://perfectmoney.is/api/step1.asp' ) {
	 ?>
     <input type="hidden" name="PAYMENT_ID" value="<?=$row_order['id']?>" />
     <input type="hidden" name="PAYEE_ACCOUNT" value="<?=$GLOBALS['pm_'.strtolower(substr($row_order['currin'],2,3))]?>" />
     <input type="hidden" name="PAYEE_NAME" value="<?=$GLOBALS['shop_name']?>" />
     <input type="hidden" name="PAYMENT_AMOUNT" value="<?=$row_order['summin']?>" />
     <input type="hidden" name="PAYMENT_UNITS" value="<?=substr($row_order['currin'],2,3)?>" />
     <input type="hidden" name="STATUS_URL" value="<?="https://obmenov.com/mcvresult.php"?>">
    <input type="hidden" name="PAYMENT_URL" value="<?=$siteroot."comment.php"?>">
    <input type="hidden" name="NOPAYMENT_URL" value="<?=$siteroot."payment_failed.php"?>">
    <input type="hidden" name="BAGGAGE_FIELDS" value="pid oid clid">
     <input type="hidden" name="PAYMENT_METHOD" value="PerfectMoney account">
    <input type="hidden" name="SUGGESTED_MEMO" value="<?='Exchange Order #'.$row_order['id']
	.'. '.$row_order['summin'].' '.$row_order['curr1'].' - '.
	($row_order['summout']+$row_order['discammount']).' '.$row_order['curr2']?>"/> 
     <?php 
	 }elseif ( $fields['form_action']=='https://sci.libertyreserve.com/' ) {
	 ?>
     <input type="hidden" name="lr_merchant_ref" value="<?=$row_order['id']?>" />
     <input type="hidden" name="lr_acc" value="<?=$GLOBALS['lr_acc']?>" />
     <input type="hidden" name="lr_store" value="<?=$GLOBALS['lr_store']?>" />
     <input type="hidden" name="lr_amnt" value="<?=$row_order['summin']?>" />
     <input type="hidden" name="lr_currency" value="<?=$row_order['currin']?>" />
   <?php /*?>  <input type="hidden" name="STATUS_URL" value="<?="https://obmenov.com/mcvresult.php"?>"><?php */?>
    <input type="hidden" name="lr_success_url" value="<?=$siteroot."comment.php"?>">
    <input type="hidden" name="lr_fail_url" value="<?=$siteroot."payment_failed.php"?>">
    <input type="hidden" name="Baggage fields" value="pid oid clid">
    <input type="hidden" name="lr_comments" value="<?='for contract #'.$row_order['id']
	/*.'. '.$row_order['summin'].' '.$row_order['currin'].' - '.
	($row_order['summout']+$row_order['discammount']).' '.$row_order['currout']*/?>"/> 
 <?php }
	echo '<input type="hidden" name="pid" value="'.$paymentid.'" />';
	echo '<input type="hidden" name="oid" value="'.$row_order['id'].'" />';
	echo '<input type="hidden" name="RND" value="'.$rnd.'" />';
	echo '<input type="hidden" name="clid" value="'.$clid.'" />';
    echo '<input type="hidden" name="redirect" value="'.$fields['form_action'].'" />';
	echo '<input type="hidden" name="comment" value="'.$fields['lmi_comment'].'" />';
  ?>
</form>



<?php } ?>
                        
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