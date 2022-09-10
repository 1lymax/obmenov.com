<?php require_once('Connections/ma.php'); ?>
<?php require_once('function.php'); 
$dir_condition="";
$url_direction="";
$direction="no";



if ( !isset($_GET['source']) ) {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title><?=get_setting('site_title_sht'.$urlid['site_curr2'])?> :: Тарифы и курсы обмена</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<meta name="language" content="ru" />
		<meta http-equiv="X-UA-Compatible" content="IE=7"/>
		<meta http-equiv="imagetoolbar" content="no" />
		<?php require_once($serverroot."Connections/meta.php"); ?>
        <?php require_once($serverroot."siti/inc_before_body.php"); ?>
		<link rel="stylesheet" href="<?=$docroot?>style.css" type="text/css" media="screen" />
        <link rel="shortcut icon" href="<?=$docroot?>i/favico.ico"/>
		<!--[if lte IE 7]><link rel="stylesheet" href="ie.css" type="text/css" media="screen" /><![endif]-->
        <style>
		<?php
       	if ( isset($_SESSION['AuthUsername']) ) {
		echo '.wrapper {background: url("'.$docroot.'i/wrapper'.$urlid['site_ext'].'-auth.jpg") center 0 no-repeat;}';
		}else{
		echo '.wrapper {background: url("'.$docroot.'i/wrapper'.$urlid['site_ext'].'.jpg") center 0 no-repeat;}';
		}
		?>
		</style>
        
	</head>
    <script language="javascript">
	function reqcomm(lev,dir)
{
    document.getElementById("text").innerHTML='<table align="center"><tr><td height=300 valign="center"><img src="i/ajax-loader_big.gif" width="54" height="55" alt="Подождите, идет загрузка" /></td></tr></table>';
	var r = gen(lev,dir);
	//handlewmid();
    sndReq(r, handlecomm);
}
function sndReq(req, handleResponse)
{
	http = createRequestObject();
	http.open("get", req);
	http.onreadystatechange = handleResponse;
	http.send(null);
}
function gen(lev,dir)
{
    var res = "../commission.php?seed="+Math.round(100*Math.random()) + "&source=commission&level="+lev+"&direction="+dir;
    return encodeURI(res);
}

function handlecomm()
{
    if(http.readyState == 4)
    {
        //alert (http.responseText);
		document.getElementById("text").innerHTML=http.responseText;
		var t = http.responseText;
        var p = t.indexOf("QueryOk");


	}
}

</script>
<script src="<?=$docroot?>fun.js"></script>
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
                        
                        <?php } if ( isset($_GET['direction']) and ( $_GET['direction']=="in" or $_GET['direction']=="no" or
																	 $_GET['direction']=="out" or $_GET['direction']=="auto") ) {
		if ( $_GET['direction']=="no" ) {
			$dir_condition="";
		}else{
			$dir_condition=" AND addon.exchtype='".substr($_GET['direction'],0,4)."'";
		}
		$url_direction="&direction=".substr($_GET['direction'],0,4);
		$direction=substr($_GET['direction'],0,4);
	}
$query_addon = "SELECT addon.id, addon.value, addon.date, 
		max( if( addon.currname1 = currency.name, currency.extname, NULL ) ) AS currin_ext,
		max( if( addon.currname2 = currency.name, currency.extname, NULL ) ) AS currout_ext, 
		max( if( addon.currname1 = currency.name, currency.server, NULL ) ) AS server_in, 
		max( if( addon.currname2 = currency.name, currency.server, NULL ) ) AS server_out,
		(select currency.im from currency where currency.name=addon.currname1) as image_in,
		(select currency.im from currency where currency.name=addon.currname2) as image_out,
		addon.currname1, addon.currname2, addon.exchtype
FROM addon
INNER JOIN currency ON addon.currname1 = currency.name
OR addon.currname2 = currency.name
WHERE addon.clientid =0 ".($closed_exchange && $urlid['site_curr2']==$closed_exchange_site ? " " : "AND 
				(inactive=0 or ( select 1 from closed_exch where closed_exch.currin=addon.currname1 
				AND closed_exch.currout=addon.currname2 and clid='".(isset($client['clid'])?$client['clid']:"")."'))" ).
$dir_condition."
AND (select currency.active from currency where currency.name=addon.currname1)=1 
AND (select currency.active from currency where currency.name=addon.currname2)=1 
GROUP BY addon.id
ORDER BY addon.exchtype ASC, addon.currname2 asc";
//maildebugger($query_addon);
$addon = _query($query_addon,"commision.php 1");
//$row_addon = mysql_fetch_assoc($addon);
$query_currency = "SELECT currency.name, currency.server FROM currency
	ORDER BY name desc";
$currency = _query($query_currency, 'function.php 17');
while ($row=$currency->fetch_assoc()) {
	$site_curr[$row['name']]=$row['server'];
}

$t = isset ($_GET['level']) ? (int)substr($_GET['level'],0,1) : 0;
if ($t>3) {$t=0;}
$summ[0]=10;
$summ[1]=100;
$summ[2]=1000;
$summ[3]=10000;
while ($row_addon = $addon->fetch_assoc()) {
	$in=$row_addon['currname1'];
	$out=$row_addon['currname2'];
	// проверка разрешения валюты на этом сайте
	//if ( $row_addon['server_in']!=0 && $row_addon['server_out']!=0 ) {
		if ( (!in_array($site_curr[$in],$urlid['site_curr']) || !in_array($site_curr[$out],$urlid['site_curr'])) 
			&& !$closed_exchange && $urlid['site_curr2']!=$closed_exchange_site )continue; 
		if ( !$closed_exchange && ( $row_addon['server_in']!=$urlid['site_curr2'] && $row_addon['server_out']!=$urlid['site_curr2']  ) ) continue;
	//}
	//if ( $site_curr[$in]==1 && $site_curr[$out]==1 ) continue;
	$course=$courses[$in][$out];
 
 	$select="select value from addon_predel 
			where currname1='".$row_addon['currname1']."'
			AND currname2='".$row_addon['currname2']."'
			AND type=".$t."
			AND clientid=0
			ORDER by date desc";
	$query=_query($select, "commission.php 2");
	if ( $query->num_rows == 0 ) {
			$value=$t/1000;
	}else{
		$value=$query->fetch_assoc();
		$value=floatval($value['value']/100);
	}
	$money1[$in.$out]['curr1']=$row_addon['currname1'];
	$money1[$in.$out]['curr2']=$row_addon['currname2'];
	$money1[$in.$out]['image_in']=$row_addon['image_in'];
	$money1[$in.$out]['image_out']=$row_addon['image_out'];
	$money1[$in.$out]['curr1_ext']=$row_addon['currin_ext'];
	$money1[$in.$out]['curr2_ext']=$row_addon['currout_ext'];
	$money1[$in.$out]['exchtype']=$row_addon['exchtype'];
	$money1[$in.$out]['value']=($row_addon['value']-$value);
	$money1[$in.$out]['value1']=$course/($row_addon['value']-$value);
	$money1[$in.$out]['date']=$row_addon['date'];
	if ($money1[$in.$out]['value']==NULL){
		$money1[$in.$out]['value']=($row_addon['value']-$value);
		$money1[$in.$out]['date']=$row_addon['date'];}
	else { //удаление старых дат
		if ($row_addon['date']>$money1[$in.$out]['date']){ 
		$money1[$in.$out]['date']=$row_addon['date'];
		$money1[$in.$out]['value']=($row_addon['value']-$value);
		$money1[$in.$out]['value1']=$course/($row_addon['value']-$value);
		}
	}
	if ($money1[$in.$out]['value1']==NULL){
		$money1[$in.$out]['value1']=$course/($row_addon['value']-$value);
		$money1[$in.$out]['date']=$row_addon['date'];}
	else { //удаление старых дат
		if ($row_addon['date']>$money1[$in.$out]['date']){ 
		$money1[$in.$out]['date']=$row_addon['date'];
		$money1[$in.$out]['value1']=$course/($row_addon['value']-$value);
		}
	}

} 

array_sort($money1, 'exchtype', 'curr1', 'curr2');
$exchtype='';
$aa=$bb='';

if ( isset($_GET['level']) ) {
	$t=substr($_GET['level'],0,1);
	$url_level=substr($_GET['level'],0,1);
}else{
	$t=0;
	$url_level=0;
}

if ( !isset($_GET['source']) ) {
?>
<div align="center">
<table align="center"><tr><td align="center"><span id="text">
<?php  } ?>
                        
     <table align="center"  cellpadding="2" cellspacing="0" border="0" width="500">
<tr><td colspan="6" align="center"><h1>Комиссия сервиса и курсы валют</h1></td></tr>
<tr><td height="20" colspan="6" align="center">
	<span class="otzyv-name">Текущие тарифы показаны для сумм обмена <br />в следующем долларовом эквиваленте:</span></td></tr>
<tr><td height="20" colspan="6" align="center">
	  | <?php if ( $t==0 ) {?>
			<span class="otzyv-name">0 - 100 $</span> |
        <?php } else { ?>
        	<a href="javascript:reqcomm(0,'<?=$direction?>')">0 - 100 $</a> |
        <?php } ?>
        
		<?php if ( $t==1 ) {?>
			<span class="otzyv-name">100 - 1000 $</span> |
        <?php } else { ?>
        	<a href="javascript:reqcomm(1,'<?=$direction?>')">100 - 1000 $</a> |
        <?php } ?> 
		
 		<?php if ( $t==2 ) {?>
			<span class="otzyv-name">1000 - 5000 $</span> |
        <?php } else { ?>
        	<a href="javascript:reqcomm(2,'<?=$direction?>')">1000 - 5000 $</a> |
        <?php } ?>        
        
        
		<?php if ( $t==3 ) {?>
			<span class="otzyv-name">5000 - &infin; $</span> |
        <?php } else { ?>
        	<a href="javascript:reqcomm(3,'<?=$direction?>')">5000 - &infin; $</a> |
        <?php } ?> 

        </td></tr>
        <tr><td height="20" colspan="6" align="center">
        	| <?php if ( $direction=="no" ) {?>
			<span class="otzyv-name">Все</span> |
        <?php } else { ?>
        	<a href="javascript:reqcomm(<?=$url_level?>,'no')">Все</a> |
        <?php } ?>
        
       <?php if ( $direction=="in" ) {?>
			<span class="otzyv-name">Ввод</span> |
        <?php } else { ?>
        	<a href="javascript:reqcomm(<?=$url_level?>,'in')">Ввод</a> |
        <?php } ?>        
		<?php if ( $direction=="out" ) {?>
			<span class="otzyv-name">Вывод</span> |
        <?php } else { ?>
        	<a href="javascript:reqcomm(<?=$url_level?>,'out')">Вывод</a> |
        <?php } ?> 
		
 		<?php if ( $direction=="auto" ) {?>
			<span class="otzyv-name">Обмен <-></span> |
        <?php } else { ?>
        	<a href="javascript:reqcomm(<?=$url_level?>,'auto')">Обмен <-></a> |
        <?php } ?>        
        

        
        </td></tr>
        
<?php 
foreach ($money1 as $row2){
//if ( $row2['curr1']=="ACRUB" )maildebugger($money1);
?>
    <?php if (1==1){ //($row2['curr1']!="MCVUSD" && $row2['curr1']!="MCVRUR") { ?>
<tr><td align="left" height="19">

<?php 

		if ( $row2['exchtype']=='auto' && $exchtype=='' ) {echo '<br />
		 
<h2><img src="'.$docroot.'i/icq-online.png" width="10" height="10" /> Обмен</h2></td></tr>'; $exchtype='auto';}
		if ( $row2['exchtype']=='in' && $exchtype=='auto' ) {echo '<br />		
		<h2><img src="'.$docroot.'i/icq-online.png" width="10" height="10" /> Ввод</h2></td></tr>
		'; $exchtype='in';}
		if ( $row2['exchtype']=='out' && $exchtype=='in' ) {echo '<br />		 
		<h2><img src="'.$docroot.'i/icq-online.png" width="10" height="10" /> Вывод</h2></td></tr>
		'; $exchtype='';}
	  	if ( $aa != $row2['curr1'] && $bb!=$row2['curr1'] ) { $aa=$row2['curr1']; $bb=$row2['curr1']; }
		else { $aa=''; }
		if ($aa!='') {
		
		?>
	 	</td></tr>
        <?php /*?><tr><td colspan=6 height="1" class="td_white"></td></tr><?php */?>
        <tr><td align="right" width="120"><img src="<?=$docroot?>i/logos/<?=$row2['image_in']?>"/> <?=$row2['curr1_ext']?>
		<?php } ?>
        </td><td align="center">

        <form name="<?php echo $row2['curr1'].$row2['curr2']; ?>" method="get" action="<?=$siteroot?>index.php">
      <input name="" style="text-align:right" type="text" class="comm_in" value="<?=isset($summ[$t]) ? $summ[$t] : ""?>" size="6" maxlength="7" readonly="true"/>
		= <input type="text" class="comm_in" 
      		value="<?php echo round($row2['value1'],4)*(isset($summ[$t]) ? $summ[$t] : 0);
			//($row2['curr1']=="MCVUSD" || $row2['curr1']=="MCVRUR") ? "0" : round($row2['value1'],4)*100?>" size="7" maxlength="9"  readonly="true" />  </form>
      </td>
      <td><img src="<?=$docroot?>i/logos/<?=$row2['image_out']?>"/> <?=str_replace("MasterCard/","",$row2['curr2_ext'])?>
      </td><td><? if (1==1) {//( $row2['curr1']=="MCVUSD" || $row2['curr1']=="MCVRUR" ) {}else{ ?>
      <a href="<?=$siteroot?>exchange/<?=urlencode($row2['curr1_ext']."-".$row2['curr2_ext'])?>.html"><img src="<?=$docroot?>i/icq-online.png" width="10" height="10" /></a>
      <?php } ?>
     </td></tr>
      <?php }
}
?>
<tr><td height="30"></td></tr>
</table>

<br />
<br />
<br />
<?php if ( !isset($_GET['source']) ) { ?>

</span></td>
</tr></table>                   
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

<?php } ?>