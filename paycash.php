<?php require_once('Connections/ma.php');
require_once('function.php');
	$pb_account['P24UAH']="6762462602190511";
	$pb_account['UAH']="6762462602190511";
	$pb_account['USD']="6762462602779495";
	$pb_account['P24USD']="6762462602779495";	// 48790
	// 4405885016445667 ���������� ����
	// 4405885017967131 ���������� ����
	// 4405885016445758 ���������� ���
	// 4405885600039975 ���� �������� ����
	
	//$oid=11585;
	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_'.$urlid['site_curr2'])?></title>
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
                            
                    <?php if ( isset($_POST['oid']) ) {
			$oid=(int)$_POST['oid'];
			$order = "SELECT orders.id, orders.summin, orders.currin, orders.currout, orders.disc, orders.summout, orders.clid,
	  			orders.discammount, (select extname from currency where name=orders.currin) as curr1, 
				(select type from currency where name=orders.currin) as currtypein, orders.needcheck,
				(select extname from currency where name=orders.currout) as curr2,
  				orders.purse_z, orders.purse_u, orders.purse_r, orders.purse_e	
				FROM orders WHERE orders.id=".$oid ." AND orders.clid='".$clid."';";
			$row_order=_query($order, 17);
			$order_numrows=mysql_num_rows($row_order);
			$row_order=$row_order->fetch_assoc();
		if ( $order_numrows== 1) {
			if ( isset($_POST['Purse']) ) {
				$purse=", `purse_".strtolower($_POST['purseType'])."`='".
				trim(strtr($_POST['Purse'], "URZEurze", "        "))."'";}
			else{$purse='';}
			if ( isset($_POST['PurseOut']) ) {
				
				$purseOut=", `purse_".strtolower($_POST['purseTypeOut'])."`='".
				trim(strtr($_POST['PurseOut'], "URZEurze", "        "))."'";}
			else{$purseOut='';}
			$name = isset ($_POST['name']) ? ", name='".substr($_POST['name'],0,30)."'" : "";
			$phone = isset ($_POST['phone']) ? ", phone='".$_POST['phone']."'" : "";
			$wmid = isset ($_POST['wmid']) ? ", wmid='".substr($_POST['wmid'],0,12)."'" : "";
			$email = isset ($_POST['email']) ? ", email='".$_POST['email']."'" : "";
			$query = "UPDATE orders SET ordered=1 ".$name.$purse.$purseOut.$email." WHERE id=".$row_order['id'].";";
			$result = _query($query,"specification_redirect 2");
			//$pb_account['P24UAH']="4405885015109975";

                         
            $select="select percent from orders_ecomode where oid=".$row_order['id'];
			$query=_query($select,"");
			if ( mysql_num_rows($query)==0 ){
				$economy=1;
			}else{
				$row=$query->fetch_assoc();
				$economy=1+$row['percent'];
			}
			
			?>
                         
                           <table align="center" width="550" border="0">
    <tr>
    	<td colspan="2" height="20"></td>
    </tr>     
    <tr>
    	<td colspan="2" align="left"><h1>������ �<?=$row_order['id']?>.</h1>
	 <h1>����� <?=$row_order['summin']." ".$row_order['curr1'] ?> �� <?=round(($row_order['summout']+$row_order['discammount'])*$economy,2)." ".$row_order['curr2']; ?>.</h1>
     <div class="otzyv-date">���� ������ <?=($row_order['disc']*100-100)?>% ��� ������ � �������������� �����.</div></td>
    </tr>	
    <tr><td><br><br />
<br />

<table class="tableborder2" width="400"><tr><td>&nbsp;</td></tr></table><br />
<br />
      <h1>������ ������ � ������ ������ ����� ������� ������24.</h1>
            ��� �������� ����� ��������� ��������� ��������:<br>
            1. ������������� � ������� ������24, ��������� ���� ����� � ������.<br>
			2. ����� ��������� ������ ����� ������� ������24, ������� � ���� -> ������� -> �������... -> ������� �� ����� �����������.<br>
            3. ����� � ���� "�����" ������� ����� ������� <strong><?php echo $row_order['summin']." ".$row_order['currtypein']; ?></strong>, � � ���� "�� �����/����" ������� 
            <strong><?=$pb_account[$row_order['currin']]?></strong>. ������� "����������".<br>
            4. ����������� ������.<br>
            5. ����� ����� ������� � "������� ��������" (� ������ ����� ������) �, ������ ����������� ��������� ������� �������.<br>
<table class="tableborder2" width="400"><tr><td>&nbsp;</td></tr></table>
<br><br />

		<?php if ( $row_order['currin']=="P24UAH" || $row_order['currin']=="UAH") { ?>
            <h1>������ ������ ��������� ����� ��������� ����������� (��������� ������ ��� ��������� ��������):</h1>
			������� ������������� ����� ����� <strong><?=$pb_account[$row_order['currin']]?></strong> � ����� ������� <strong><?php echo $row_order['summin']." ".$row_order['currtypein']; ?></strong>. ��� ����� ������� �������� �������� �������������� �������� ����� � ������� 0.75% �� ����� �������.
<table class="tableborder2" width="400"><tr><td>&nbsp;</td></tr></table>
<br>

	<table width=400 align="left">
    <tr><td colspan=2><h1>����������� ������ �� ������ ����� �������:</h1></td></tr>
	<tr><td width="50%">����������:</td><td width="50%">����������</td></tr>
	<tr><td>������������ �����:</td><td>����������</td></tr>
	<tr><td>����� �����:</td><td>29244825509100</td></tr>
	<tr><td>���:</td><td>305299</td></tr>
	<tr><td>����:</td><td>14360570</td></tr>
	<tr><td><br /></td></tr>
	<tr><td valign="top">���������� �������: </td><td><b><?=$row_order['id']?></b><br>
	��������� ������ ���������, ��� 2934408073 <br>��� ���������� �� ����� <?=$pb_account['P24UAH']?></td></tr></table>
           
            <?php
				} ?>
                

                </td></tr></table><br />
<br />

                                <p>����� ������������� �������� ��������� � ���������� ������� (ICQ: 450-750-453, email: support@obmenov.com). ������� ����� ������, �� ������� ���� ������������ ������, ����� � ������ �������� �������� �������.</p>
                <?php
		
		
		}else {
			echo "<h1>������</h1> ������ ����������. ��� ���������� �������� ������ �������.";
		}

	} else {
		echo "<h1>������</h1> ������ ����������. ��� ���������� �������� ������ �������.";	
	} ?>
                          
                          
                          
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