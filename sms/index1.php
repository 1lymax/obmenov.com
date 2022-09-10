<?php require_once('../Connections/ma.php');
require_once($serverroot.'function.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title>�������.��� :: Webmoney �� SMS :: ������� � ��� ����� 30 ����� - ���������!.</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<?php require_once($serverroot."Connections/meta.php"); ?>
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
		<link rel="stylesheet" href="<?=$siteroot?>style.css" type="text/css" media="screen" />
        <link rel="shortcut icon" href="<?=$siteroot?>i/favico.ico"/>
		<!--[if lte IE 7]><link rel="stylesheet" href="ie.css" type="text/css" media="screen" /><![endif]-->
        <style>
		<?php
       	if ( isset($_SESSION['AuthUsername']) ) {
		echo '.wrapper {background: url("../i/wrapper-auth.jpg") center 0 no-repeat;}';
		}else{
		echo '.wrapper {background: url("../i/wrapper.jpg") center 0 no-repeat;}';
		}
		?>
		</style>
       	<?php // <link rel="stylesheet" href="viewer.css" type="text/css" /> ?>
        <script>
        courses=new Array();
courses['WMR']=<?=round($courses['USD']['RUR']*get_setting('sms_profit'),2)?>;
courses['WMZ']=<?=round(get_setting('sms_profit'),2)?>;
courses['WMU']=<?=round($courses['USD']['UAH']*get_setting('sms_profit'),2)?>;
courses['WME']=<?=round($courses['USD']['EUR']*get_setting('sms_profit'),2)?>;
rezerve=new Array();
rezerve['WMR']=<?=$WM_amount_r['WMR']?>;
rezerve['WMZ']=<?=$WM_amount_r['WMZ']?>;
rezerve['WMU']=<?=$WM_amount_r['WMU']?>;
rezerve['WME']=<?=$WM_amount_r['WME']?>;
        </script>
        <script src="../_main.js" type="text/javascript"></script>
		<script src="dropdown.js" type="text/javascript"></script>
        <script language="javascript">
function upd () {
	$('rezerv').innerHTML='����� ��������: ' + rezerve[$('moneyOut').value] + ' ' + $('moneyOut').options[$('moneyOut').options.selectedIndex].text;
	selectCountry('-');
	$('select_country').selectedIndex=0;
}
		</script>

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
        <table width="500" border="0" align="center">
        <tr><td>
		<div id="ui" class="dropdown" style="display: none">
        
        	<h1>���������� Webmoney �� SMS � �������, ������, ����������. ����� ����� 30 �����.</h1>
            <p>�� ���� �������� �� ������ �������� ������ �� ������������ Webmoney �� SMS (Short Message Service - ������ �������� ���������). ���� ������� ����������, ������ � ��������� ������� ������ �������� �� ����� 3-� �����. �������������� �������� ����� ������. ��� ���������� ����� ��������������� ������� ��� ������������� ������, ������, � ������� ��������� ��� �������� ��������� �����, ��������� � ����� ��������� ������, ������� ������ ����������.<br />
��� ��������� � ���������� ��������� ��� ����� ���������� ������ �� ��������� ��������.</p>
<p><strong>��������!</strong> ������ ������� Webmoney �� SMS ��������� � �������������������, �� �������� ������. �� ���� ��������, �������� � ����������������� ������� ������� ��������� � ������������ �������. ���������� � ��������� �� ������ ����� <a href="<?=$siteroot?>contacts.php">�����.</a></p>
<br />

            <p>�������� ��� ������������� ������:<br />
            <form action="<?=$siteroot?>specification.php" method="POST">
            <select name="moneyOut" id="moneyOut" onChange="upd();">
            <option value="WMZ" selected="selected">Webmoney WMZ</option>
            <option value="WMR">Webmoney WMR</option>
            <option value="WMU">Webmoney WMU</option>
            <option value="WME">Webmoney WME</option>
            </select> <span id="rezerv"></span ></p><br /><br />
			<p>�������� ������:<br />
			<select id="select_country">
				<option value="-">�������� ������:</option>
			</select></p><br />
			<div id="providers" style="display: none">
				<p>�������� ���������:<br />
				<select id="select_provider">
					<option value="-">�������� ���������:</option>
				</select></p><br />
			</div>
			<div id="instructions" style="display: none">
				<p>��������� ���������:<br />
					<select id="select_cost">
						<option value="-">�������� �����:</option>
					</select></p>
				<p id="notes" style="display: none"></p>
			</div>
			<br />
				<p>
					<input name="s_amount" id = "s_amount" type="hidden" value="0" />
                    <input name="moneyIn" id = "moneyIn" type="hidden" value="SMS" />
                    <input name="SummIn" id = "SumIn" type="hidden" value="1" />
                    <input name="order" id = "order" type="hidden" value="ok" />
					<input type="submit" value="����������" style="display: none" id = "sub" class="button1"/>
				</p>
			</form>
		</div>
        </td></tr></table>
		<div id="fail" style="display: none">
			<h1>������ ����� � ��������</h1>
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