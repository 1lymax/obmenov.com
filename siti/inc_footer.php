<div class="footer">
                   <?php  if ( $urlid['site_curr2']==1 ) {?><div class="footer-t"></div><?php } ?>
                    <div class="footer-b clear">
                        <p class="copy">Copyright &copy; 2010 <a href="/">obmenov.com</a><br />
						<a href="<?=$siteroot?>about.php#risk">Уведомление о рисках</a> | <a href="https://login.wmtransfer.com/GateKeeper.aspx?RID=ea1990fe-6e9b-45a9-8ef6-9ef5011e683b">Авторизация</a> | New!</p>
                        <ul class="footer-nav">
                        <li>
<?php if ( $urlid['site_curr2']==1 ) { ?>
<!-- HotLog -->
<script type="text/javascript" language="javascript">
hotlog_js="1.0"; hotlog_r=""+Math.random()+"&s=600140&im=700&r="+
escape(document.referrer)+"&pg="+escape(window.location.href);
</script>
<script type="text/javascript" language="javascript1.1">
hotlog_js="1.1"; hotlog_r+="&j="+(navigator.javaEnabled()?"Y":"N");
</script>
<script type="text/javascript" language="javascript1.2">
hotlog_js="1.2"; hotlog_r+="&wh="+screen.width+"x"+screen.height+"&px="+
(((navigator.appName.substring(0,3)=="Mic"))?screen.colorDepth:screen.pixelDepth);
</script>
<script type="text/javascript" language="javascript1.3">
hotlog_js="1.3";
</script>
<script type="text/javascript" language="javascript">
hotlog_r+="&js="+hotlog_js;
document.write('<a href="http://click.hotlog.ru/?600140" target="_blank"><img '+
'src="http://hit30.hotlog.ru/cgi-bin/hotlog/count?'+
hotlog_r+'" border="0" width="88" height="31" alt="HotLog"><\/a>');
</script>
<noscript>
<a href="http://click.hotlog.ru/?600140" target="_blank"><img
src="http://hit30.hotlog.ru/cgi-bin/hotlog/count?s=600140&im=700" border="0"
width="88" height="31" alt="HotLog"></a>
</noscript>
<!-- /HotLog -->

<?php }else{ ?>
<!-- HotLog -->
<script type="text/javascript" language="javascript">
hotlog_js="1.0"; hotlog_r=""+Math.random()+"&s=2134353&im=357&r="+
escape(document.referrer)+"&pg="+escape(window.location.href);
</script>
<script type="text/javascript" language="javascript1.1">
hotlog_js="1.1"; hotlog_r+="&j="+(navigator.javaEnabled()?"Y":"N");
</script>
<script type="text/javascript" language="javascript1.2">
hotlog_js="1.2"; hotlog_r+="&wh="+screen.width+"x"+screen.height+"&px="+
(((navigator.appName.substring(0,3)=="Mic"))?screen.colorDepth:screen.pixelDepth);
</script>
<script type="text/javascript" language="javascript1.3">
hotlog_js="1.3";
</script>
<script type="text/javascript" language="javascript">
hotlog_r+="&js="+hotlog_js;
document.write('<a href="http://click.hotlog.ru/?2134353" target="_blank"><img '+
'src="http://hit37.hotlog.ru/cgi-bin/hotlog/count?'+
hotlog_r+'" border="0" width="88" height="31" alt="HotLog"><\/a>');
</script>
<noscript>
<a href="http://click.hotlog.ru/?2134353" target="_blank"><img
src="http://hit37.hotlog.ru/cgi-bin/hotlog/count?s=2134353&im=357" border="0"
width="88" height="31" alt="HotLog"></a>
</noscript>
<!-- /HotLog -->

<?php } ?>
</li>


<li>
<!--WebMoney TOP--><script language="JavaScript"><!--
document.write('<a href="http://top.owebmoney.ru" target=_blank>'+'<img src="https://top.owebmoney.ru/counter.php?site_id=1160&from='+escape(document.referrer)+'&host='+location.hostname+'&rand='+Math.random()+'" alt="WebMoney TOP" '+'border=0 width=88 height=31></a>')//--></script>
<noscript><a href="http://top.owebmoney.ru" target=_blank>
<img src="https://top.owebmoney.ru/counter.php?site_id=1160"
width=88 height=31 alt="WebMoney TOP" border=0></a></noscript>
<!--/WebMoney TOP--> 
</li>

<?php if ( $urlid['site_curr2']==1 ) { ?>
<li><a href="http://www.webmoney.ru" target="_blank"><img src="<?=$siteroot?>i/AWMgreen.png" alt="принимаем Webmoney" width="88" height="31" border="0" /></a></li>
<li>
<a href="https://passport.webmoney.ru/asp/certview.asp?wmid=219391095990" target=_blank><IMG SRC="<?=$siteroot?>i/88x31_wm_attest.png" title="Здесь находится аттестат нашего WM идентификатора 219391095990" border="0"></a></li>
<?php /*?><li>
<A href="http://club.owebmoney.ru/537/" target="blank"><img src="http://serv1.owebmoney.ru/images/club/club6.gif" width="88" height="31" border="0" title="WM-Клуб"></A></li><?php */?>

<?php }elseif ( $urlid['site_curr2']==2 ) { ?>
<li>
<a href="http://perfectmoney.com?ref=<?=$pm_id?>" title="Perfect Money - новое поколение платежных систем Интернета">
<img src="<?=$siteroot?>i/logos/pmoney.jpg" height="31" hspace="5" width="88"></a>
</li>
<li>
<a href="http://www.walletone.com/?ref=150449552204" target="_blank" title="Платежный сервис Единый кошелек">
  <img src="https://www.walletone.com/w1/img/partner/button/ru.gif" width="88" height="31" alt="Платежный сервис Единый кошелек"/>
</a>
</li>
<?php } 
?>

                            <!--li><a href="#">Соглашение</a></li>
                            <li><a href="#">WM за SMS</a></li>
                            <li><a href="#">Вопросы</a></li>
                            <li><a href="#">Отзывы</a></li>
                            <li><a href="#">Контакты</a></li-->
                        </ul>
                    </div>
                </div>
                <?php
				//msql_close($ma);
				?>
