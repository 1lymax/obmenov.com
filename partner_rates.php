<?php require_once('Connections/ma.php'); ?>
<?php require_once('function.php');



$currentPage = $_SERVER["PHP_SELF"];

if ( !isset($_SESSION['Partner_AuthUsername']) ) { //begin of false user auth
					  
					  
					  
}else  //begin of true user auth
{


$max = 10;
$page = 0;
if (isset($_GET['page'])) {
  $page = htmlspecialchars($_GET['page']);
}
$start = $page * $max;

$query_client="SELECT partner.id, partner.nikname, partner.email, partner.clid, partner.phone FROM partner WHERE partner.nikname='".$_SESSION['Partner_AuthUsername']."';";
	$client = _query($query_client, 'partner_rates.php 1');
	$num_row_client=$client->num_rows;
	$row_client=$client->fetch_assoc();
	




} // end of true user auth

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: ������� ��������</title>
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
    <script src="fun.js"></script>
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
                       <?php if ( isset($_SESSION['Partner_AuthUsername']) ) {?>
                       <div align="center">
	<table width="500" align="center">
    <tr><td align="center" ><h1>������� ��������</h1></td></tr>
    
    <tr>
    <td align="right" valign="top"><h2>
    <?='�������: '.$_SESSION['Partner_AuthUsername'].'<br />';?>
    <?='�������������: '.$row_client['id'];?></h2>
    <a href="<?=$siteroot?>partner.php">����������</a><br />
<!--    <a href="cabinet.php?history">������� ��������</a><br />-->
	 <a href="<?=$siteroot?>partner_register.php">������������ ������</a><br />
	 <a href="<?=$siteroot?>partner_banner.php">������</a><br />
	 ����� �����<br />     
    <a href='<?=$siteroot?>partner_login.php?doLogout=true'>�����</a>
    </td>
    </tr>
    <tr><td height="50"></td></tr>
    </table>

	<table width="550" align="center" border="0">
    <tr><td align="center" colspan="3"><h2>����� ����� � ����� ��������</h2>
<br />
</td></tr>
<tr><td>
<span class="otzyv-date">������ �� ����� ������ ����� (����������� ������ ������):</span><br /><br />
������ �������.���:
������ ���� - <a href="http://obmenov.com/current_state.txt">http://obmenov.com/current_state.txt</a><br />
������ �����: �������� ������, �������������� ������, ���� ������ (�������� � ��������������), ��������� ������ �������������� ������
<br /><br />
�������� ���� - <a href="http://obmenov.com/current_state2.txt">http://obmenov.com/current_state2.txt</a><br />
������ �����: �������� ������, �������������� ������, ���� ������ (�������������� � ��������), ��������� ������ �������������� ������<br /><br />

����������������� ���� - <a href="http://obmenov.com/estandards.txt">http://obmenov.com/estandards.txt</a><br />
��� (������� ������������ ��� �������� ����������):<br />
<textarea cols="60" rows="4">
WMZ; WMU; 1; 7.964639; 573.91
WMZ; WMR; 1; 29.538698; 598.94
P24UAH; WMU; 1; 0.980392; 573.91
P24UAH; WMR; 1; 3.486082; 598.94
</textarea>
<br />
<br />
����������������� ���� - <a href="http://obmenov.com/estandards2.txt">http://obmenov.com/estandards2.txt</a><br />
���:<br />
<textarea cols="60" rows="4">
P24UAH -> WMZ: rate=0.12074378, reserve=1266.04
P24USD -> WMZ: rate=0.99009901, reserve=1266.04
UAH -> WMR: rate=3.48608182, reserve=255.58
UAH -> WMZ: rate=0.11956002, reserve=1266.04
</textarea>
<br />
<br />

���� �����:<br />
1 - Webmoney WMZ<br />
2 - Webmoney WMR<br />
3 - Webmoney WME<br />
20 - Webmoney WMU<br />
440 - ������24 USD<br />
441 - ������24 ������<br />
370 - USD<br />
373 - �������� ������<br />
	</td></tr>
    <tr><td height="20"></td></tr>
    <tr><td>
    ������ Obmenov.biz:<br />
    ������ ���� - <a href="http://obmenov.biz/_current_state.txt">http://obmenov.biz/_current_state.txt</a><br />
    �������� ���� - <a href="http://obmenov.biz/_current_state2.txt">http://obmenov.biz/_current_state2.txt</a><br />
    ����������������� ���� - <a href="http://obmenov.biz/_estandards.txt">http://obmenov.biz/_estandards.txt</a><br />
	����������������� ���� - <a href="http://obmenov.biz/_estandards2.txt">http://obmenov.biz/_estandards2.txt</a><br />
    </td></tr>
    <tr><td height="20"></td></tr>
    <tr><td>
    <span class="otzyv-date">������ �� ������� � ��������� ������������� ��������</span><br /><br />

	<a href="http://obmenov.com/prepaid_amount.txt">http://obmenov.com/prepaid_amount.txt</a><br />
    ������ �����: ��������, ��������� ���-��, WMZ, WMR, WMU, WME;<br />
	��� WMx - �������������� ��������� ������� � ��. �������.
    
    </td></tr>
    </table>
    </div>

<br />

<?php
} //end true user auth 
else
{
	
?>

	<table width="550" align="center">
    <tr><td align="center"><h1>������� ��������</h1></td></tr>
    
    <tr>
    <td align="right" valign="top">
����<br />
    <a href="partner.php">����������</a><br />
<!--    <a href="cabinet.php?history">������� ��������</a><br />-->
	<a href="partner_register.php"><?php echo ( isset($_SESSION['Partner_AuthUsername']) ) ? '������������ ������'  :  '�����������' ?></a><br />
    <?php if ( isset($_SESSION['Partner_AuthUsername']) ){ ?>
    <a href='partner_login.php?doLogout=true'>�����</a> <?php } ?>
    </td>
    </tr>
    <tr><td height="50"></td></tr>
</table>

<form ACTION="partner_login.php?accesscheck=partner.php" METHOD="POST" name="login" id="pauth">
<table width="300" align="center">
<tr><td align="right">��� </td><td class="form-normal"><input name="user" type="text" /></td></tr>
<tr><td align="right">������</td><td class="form-normal"><input name="pass" type="password" /></td></tr>
<tr>          <input name="" type="image" src="<?=$siteroot?>i/empty.gif" width="0" height="0"  style="border:none"/><td></td>
<td><input type="button" class="button1" onClick="d.$('pauth').submit();" value="����"></td></tr>
<tr><td></td><td><a href="partner_register.php?forgot">����� ������?</a></td></tr>

</table></form>

<?php }?> 
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