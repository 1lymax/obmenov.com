<?php require_once('Connections/ma.php');
require_once('function.php'); 
if ( isset($_POST['message']) && $_POST['message']=='ok' ) {
	if ( isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['keystring'] ){
		$message_query = "INSERT INTO message (`name`, `email`, `question`, `clid`) VALUES ('"
								.$_POST['name']."','"
								.$_POST['email']."','"
								.$_POST['question']."','"
								.(isset($_SESSION['clid'])?$_SESSION['clid']:"")."');";
								
		$Result= _query($message_query,'contacts.php 1');
		send_mail($shop_email, 
				  '���: '.$_POST['name'].'
E-mail: '.$_POST['email'].'
������: '.$_POST['question']			  
				  , "Feedback", $_POST['email'], $_POST['name']);
	}else{
		$message="�� ����� ������� ������� � ��������";
	}
	unset($_SESSION['captcha_keystring']);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: ���� ��������</title>
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
        <script src="fun.js"></script>
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
              <div align="center">
                     <table align="center" width="500" height="70"><tr><td valign="middle">
          
          </td></tr>
          <tr><td align="center" colspan="2">
          <?php if ( isset($message_query) ) {
			  echo '<strong>������� �� ��� �����. <br />
			  ���� �� ������� ������, �� �������� � ���� � ��������� �����</strong><br /><br />';}
			  else { echo '<h1>��������</h1><br />';
			  }?>
			</td></tr>
          <tr><td>
          <p>���� � ��� �������� �����-���� �������, ��������� ��� ����������� ��������� ��� <a href="mailto:support at obmenov dot com">������</a> ��� �������������� ������ �������� �����.<br />
                     ��� ����� ���� ������ � �����������, ��� ����� ������ � ������������ ������ �������.
                     �� ����������� �������� �� ��������� �������, � �������� ���������� ����� ��������� �
                     ������ <a href="http://forum.obmenov.com/viewtopic.php?f=10&t=88">�������-������</a>. ����������� ������������ � ���, ��� ��� ��� ����� ����������� ����� �� ��� ������.
                     �������!</p>
          </td></tr>
          <tr><td colspan="2">
           <form action="contacts.php" method="post" id="ques" name="ques">
           <table width="300" align="center" border="0">
            <tr><td colspan="2" align="center" width="120"><h2>������� ������:</h2></td></tr>
            <tr>
              <td align="right" class="cabinet-text"><strong>���� ���:</strong></td>
              <td align="left" width="130">             
                <input name="name" type="text" size="20">
              </td>
             </tr>
             <tr>
              <td align="right" class="cabinet-text"><strong>��� e-mail:</strong></td>
              <td align="left"><input name="email" type="text" size="20"></td>
             </tr>
             <tr>
              <td align="right" class="cabinet-text" valign="top"><strong>��� ������:</strong></td>
              <td align="left"><textarea name="question" cols="20" rows="4" type="text" size="20"></textarea></td>
             </tr>
             <tr valign="baseline">
      <td nowrap="nowrap" align="right">      
          ������� ����� <br />�� ��������:<br />
          </td>
          <td valign="middle"><input type="text" name="keystring"><br />
		<img id="kap" src="kcaptcha/index.php?<?php echo htmlspecialchars(session_name())?>=<?php echo htmlspecialchars(session_id())?>">
          <a href="javascript:rel();"><img src="i/reload.gif" alt="��������" width="16" height="16" border="0" align="baseline" /></a></td></tr>
             <tr>
              <td align="right"></td>
              <td align="left"><input type="button" class="button1" onclick="document.ques.submit();" value="���������" /></td>
             </tr>
             </table>
            <input name="message" type="hidden" value="ok" />
			<input name="" type="image" src="i/empty.gif" width="1" height="1"  style="border:none" />  
            </form>
          </td>
          
          </tr>
          <tr><td height="50"></td>
          </table>
          <?php /*?><table width="330" border="0" align="center" cellpadding="5" cellspacing="0">
            <tr >
              <td colspan="2" align="center"><h2>����� ������</h2></td>
            </tr>
            <tr>

              <td width="165" class="td_white">��., ��., ��., ��., ��.</td>
              <td width="165" class="td_white">� 09:00 �� 19:00</td>
            </tr>
        	  <tr >
        	      <td>��., ��.</td>
        	      <td>� 10:00 �� 17:00</td>
        	  </tr>

            <tr class="td_white">
              <td>����� (�������� � �����������):</td><td>�. �����, ��. �������, 14/4<br />
			�. �����, ��. ������, 15/1</td>
            </tr>

          </table><?php */?><br>


           <br>
                <table width="330" border="0" align="center" cellpadding="5" cellspacing="0">
                  <tr align="center">
                    <td colspan="2" align="center">
                    <h2>��������</h2></td>
                    </tr>

                  <tr class="td_white">

                    <td width="165" nowrap><?php /*?><b>��������:</b><?php */?></td>
                    <td width="165"><?php /*?>+380 (93) 0-151-005, <br />+380 (4842) 23089<?php */?></td>
                  </tr>
                  <tr>
                    <td nowrap><b>e-mail:</b></td>
                    <td>support(@)obmenov(.)com</td>
                  </tr>
                  <tr class="td_white">
                    <td class="td_white">���.</td>
                    <td>(38)096-022-5798</td>
                  </tr></table><br /><br />
                  <?php if ($urlid['site_curr2']==1) {?>
				<table width="330">
                  <tr align="center">
                  	<td align="center" colspan="2"><h2>����������� ���������</h2></td>
                  </tr>
                  <tr>
                    <td width="165" class="cabinet-text" align="center"><br /><strong>WMID</strong></td>
                    <td width="165" class="cabinet-text" valign="top">219391095990&nbsp;&nbsp;
                      BL: <a href="javascript:showbldesc()"><img src="https://stats.wmtransfer.com/Levels/pWMIDLevel.aspx?wmid=219391095990" width="32" height="24" border="0" alt="��� ����� Businness Level"></a>

                    </td>
                  </tr>
                 <tr>
                    <td align="center">&nbsp;</td>
                    <td class="cabinet-text" valign="top">418941129503&nbsp;&nbsp;
                      BL: <a href="javascript:showbldesc()"><img src="https://stats.wmtransfer.com/Levels/pWMIDLevel.aspx?wmid=418941129503" width="32" height="24" border="0" alt="��� ����� Businness Level"></a>

                    </td>
                  </tr>
         
				  <tr>
                    <td></td>
                    <td>
                   </td>
                  </tr> 
                </table>
                <?php } ?>   
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