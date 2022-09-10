<?php require_once('../Connections/ma.php');
	require_once('../function.php');
	require_once('../siti/class.php');

$user=new user();
if ($user->auth("adm")==1) {

}else{
	$user->bad_auth();
}

if ( isset($_GET['clid']) ) { 
	$clid=intval($_GET['clid']);
}else{ 
	$clid='0';
}

if ( isset($_POST['update_predel']) && $_POST['update_predel']=="insert" ) { 
		$base_value=floatval($_POST['value']);
		$select="select type2 from currency where name='".$_POST['currname1']."'";
		$query=_query($select,"");
		$rowin=$query->fetch_assoc();
		if ( $rowin['type2']==0 )	$exchtype="in";
		$select="select type2 from currency where name='".$_POST['currname2']."'";
		$query=_query($select,"");
		$rowout=$query->fetch_assoc();
		if ( $rowin['type2']==0 )	$exchtype="in";	
		if ( $rowout['type2']==0 )	$exchtype="out";

		if ( $rowin['type2']==$rowout['type2'] )$exchtype="auto";

		$select="select value from addon where currname1='".$_POST['currname1']."'
															and currname2='".$_POST['currname2']."'
															and clientid=0 order by date desc";
		$query=_query($select, "detail.php 4");
		$row=$query->fetch_assoc();
		
		if ( isset($_POST['value']) && ($row['value'] != $_POST['value']) ) {												
 			 
			 $update="update addon set 
			 			value=".$_POST['value'].",
						exchtype='".$exchtype."',
						inactive=".(isset($_POST['inactive']) ? $_POST['inactive'] :0).",
						onsite_visible=".(isset($_POST['onsite_visible']) ? $_POST['onsite_visible'] :0)."
						where 
						currname1='".$_POST['currname1']."' and
						currname2='".$_POST['currname2']."' and
						clientid=".$clid."";
			 $query=_query($update, "detail.php 4");
			 if ( mysql_affected_rows()==0 ) {
			 	$insert = "INSERT INTO addon (currname1, currname2, value, clientid, date, exchtype, inactive, onsite_visible) VALUES ('".
						$_POST['currname1']."', '".
						$_POST['currname2']."', ".
						$_POST['value'].", ".
						$clid.", '".
						date("Y-m-d H:i:s")."', '".
						$exchtype."',".
						(isset($_POST['inactive']) ? $_POST['inactive'] :0).",".
			 			(isset($_POST['onsite_visible']) ? $_POST['onsite_visible'] :0).")";
				
			 	$query=_query($insert, "detail.php 4");
			 }
		}
		
		
		
		//============================
		function update_addon_predel($type,$clid, $base_value) {
		
		$select="select value from addon_predel where type=".$type." and currname1='".$_POST['currname1']."'
															and currname2='".$_POST['currname2']."'
															and clientid=".$clid." order by date desc";
		$query=_query($select, "detail.php 4");
		$numrows=mysql_num_rows($query);
		//$row=$query->fetch_assoc();
		if ( isset($_POST['value'.$type]) && $_POST['value'.$type]!="" ) {
			$update="update addon_predel set 
						value=".(($base_value-floatval($_POST['value'.$type]))*100)."
						where
						currname1='".$_POST['currname1']."' and
						currname2='".$_POST['currname2']."' and
						clientid=".$clid." and
						type=".$type;
			$query=_query($update,"detail.php 7");
			if ( mysql_affected_rows()==0 and $numrows==0 ) {
				$insert="insert into addon_predel (currname1, currname2, date, clientid, value, type) values ('".
							$_POST['currname1']."', '".
							$_POST['currname2']."', '".
							date("Y-m-d H:i:s")."',".
							$clid.",".
							(($base_value-floatval($_POST['value'.$type]))*100).", ".$type.")";
				$query=_query($insert,"detail.php 7");
			}
		
		}
	}
	
	update_addon_predel (1,$clid,$base_value);
	update_addon_predel (2,$clid,$base_value);
	update_addon_predel (3,$clid,$base_value);
	
	
		//================
		
		/*
		$select="select value from addon_predel where type=2 and currname1='".$_POST['currname1']."'
															and currname2='".$_POST['currname2']."'
															and clientid=0 order by date desc";
		$query=_query($select, "detail.php 4");
		$row=$query->fetch_assoc();
		if ( isset($_POST['value2']) && $row['value'] != $_POST['value2'] ) {
			$insert="insert into addon_predel (currname1, currname2, clientid, date, value, type) values ('".
							$_POST['currname1']."', '".
							$_POST['currname2']."', ".
							$clid.",'".
							date("Y-m-d H:i:s")."',".
							(($base_value-floatval($_POST['value2']))*100).", 2)";
			$query=_query($insert,"detail.php 7");
		}
		$select="select value from addon_predel where type=3 and currname1='".$_POST['currname1']."'
															and currname2='".$_POST['currname2']."' 
															and clientid=0 order by date desc";
		$query=_query($select, "detail.php 4");
		$row=$query->fetch_assoc();
		if ( isset($_POST['value3']) && $row['value'] != $_POST['value3'] ) {
			$insert="insert into addon_predel (currname1, currname2, clientid, date, value, type) values ('".
							$_POST['currname1']."', '".
							$_POST['currname2']."', ".
							$clid.",'".
							date("Y-m-d H:i:s")."', ".
							(($base_value-floatval($_POST['value3']))*100).", 3)";
			$query=_query($insert,"detail.php 7");
		}*/
	
	}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
		if ( substr($_POST['currname1'],0,2)==substr($_POST['currname2'],0,2) ) { // autoexchange

		$exchtype="auto";
		}
		if ( ($_POST['currname1']=="UAH" || $_POST['currname1']=="CARDUAH" || $_POST['currname1']=="USD" || $_POST['currname1']=="P24UAH" || $_POST['currname1']=="P24USD")	&& (substr($_POST['currname2'],0,2)=="WM" || substr($_POST['currname2'],0,2)=="PM") ) {
		$exchtype="in";
		}
		if ( ($_POST['currname2']=="UAH" || $_POST['currname2']=="CARDUAH" || $_POST['currname2']=="USD" || $_POST['currname2']=="P24UAH" || $_POST['currname2']=="P24USD")	&& (substr($_POST['currname1'],0,2)=="WM" || substr($_POST['currname1'],0,2)=="PM" ) ) {
		$exchtype="out";
		}
	
  $insertSQL = sprintf("INSERT INTO addon (currname1, currname2, `value`, `date`, `exchtype`, inactive) VALUES (%s, %s, %s, %s, '".$exchtype."',".$_POST['inactive'].")",
                       GetSQLValueString($_POST['currname1'], "text"),
					   GetSQLValueString($_POST['currname2'], "text"),
                       str_replace(",",".",GetSQLValueString($_POST['value'], "float")),
                       GetSQLValueString($_POST['date'], "date"));


  //$Result1 = mysql_query($insertSQL, $ma) or die(mysql_error());

  $insertGoTo = "detail.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}
$query_addon = "SELECT addon.id, addon.currname1, addon.currname2, 
				(select extname from currency where name=currname1) as extname1,
				(select extname from currency where name=currname2) as extname2,
				addon.`value`, addon.`date` FROM addon 
				where clientid=0 
				order by date asc, exchtype asc, currname1 asc, currname2 asc";
$addon = mysql_query($query_addon, $ma) or die(mysql_error());
$row_addon = mysql_fetch_assoc($addon);
$money = array();


while ($row_addon = mysql_fetch_assoc($addon)) {
	if ( $clid!=0 ) {
		$select="select value from addon where clientid=".$clid." 
							and currname1='".$row_addon['currname1']."'
							and currname2='".$row_addon['currname2']."' order by date desc";
		$query=_query($select, "detail.php 11");
		if ( mysql_num_rows($query)!=0 ) {
			$row=$query->fetch_assoc();
			$base_value=$row['value'];
		}else{
			$base_value=$row_addon['value'];
		}
	}else{
		$base_value=$row_addon['value'];
	}
	$in=$row_addon['currname1']; $out=$row_addon['currname2'];
if (!$base_value==NULL){
	$money[$in][$out]['curr1']=$row_addon['currname1'];
	$money[$in][$out]['curr2']=$row_addon['currname2'];
	$money[$in][$out]['extname1']=$row_addon['extname1'];
	$money[$in][$out]['extname2']=$row_addon['extname2'];	
	$money[$in][$out]['value']=$base_value;//round($base_value,4);
	$money[$in][$out]['date']=$row_addon['date'];
	
	//if ($row_addon['date']>$money[$in][$out]['date']){ 
	//$money[$in][$out]['date']=$row_addon['date'];
	//$money[$in][$out]['value']=$base_value;//round($base_value,4);
	//	}
}
} 


?>
<html><head>
<link href="../wm.css" rel="stylesheet" type="text/css" />
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251"></head>
<body>
<?php include_once ("top.php"); 
?>


<table border="0"><tr><td>
<?php $select="select id, nikname, clid from clients where nikname!='' order by nikname asc"; 
	$query=_query($select, "detal.php 6");
echo mysql_num_rows($query);
?>
<form action="detail.php" method="get">
<select name="clid">
<?php

while ($row_client=$query->fetch_assoc()) {
	
?>

<option <?=($clid==$row_client['id'] ? "selected" : "")?> value="<?=$row_client['id']?>"><?=$row_client['nikname']?></option> 

<?php } ?>
</select>
<input type="submit" value="Фильтр">    
	  Текущий пользователь: 
	  <?php 
	  if ( isset($_GET['clid']) ) {
		  $select="select * from clients where id=".$_GET['clid'];
		  $query=_query($select, "detail.php 12");
		  if ( mysql_num_rows($query)==1 ) {
			  $row_client=$query->fetch_assoc();
			echo $row_client['nikname']." id=".$row_client['id']." clid=".$row_client['clid'];
			
		  }
		  ?>
      	
      <?php }else{  ?>
      -
      <?php } ?>
</form>
<table border="0"><tr><td>
<form action="detail.php?<?=$_SERVER['QUERY_STRING']?>" method="post" name="form2" id="form2">
  <table align="left">
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Направление:</td>
      <td><select name="currname1" size="1" id="predel_currname1" onChange="set2()">
<?php 	$query_currency = "SELECT currency.name, currency.extname, currency.type3 FROM currency 
	ORDER BY type3 asc, name desc";
	//WHERE active=1 
	//ORDER BY type3 asc, name desc";
	$currency = _query($query_currency, 'index.php 5');
	$curtype[1]="Электронные валюты";
	$curtype[2]="Наличные валюты";
	$curtype[3]="Банковские переводы";
	$curtype[4]="Денежные переводы";
	$curtype[5]="Мобильные деньги";
	$curtype[6]="Форекс-брокеры";
$t=0;

while ($row=mysql_fetch_assoc($currency)) { 
	if ( $t != $row['type3'] ) { 
		$t=$row['type3'];
		?>
    	<option value="<?=$t?>" style="color:#999; font-weight:bold"><?=$curtype[$t]?></option>
        <?php 
	}
	?>

	<option <?=($row['name']=="WMZ" ? "selected" : "")?> value="<?php echo $row['name']; ?>"><?php echo $row['extname']; ?></option>
<?php } ?>

    </select> <br>

<select name="currname2" size="1" id="predel_currname2" onChange="set2();">
    
<?php 
		$query_currency = "SELECT currency.name, currency.extname, currency.type3 FROM currency
		ORDER BY type3 asc, name desc";
		//WHERE active=1 
		//ORDER BY type3 asc, name desc";
		$currency = _query($query_currency, 'index.php 4');	
	$t=0;
	$curtype[1]="Электронные валюты";

	while ($row=mysql_fetch_assoc($currency)) { 
		if ( $t != $row['type3'] ) { 
			$t=$row['type3'];
			?>
    		<option value="<?=$t?>" style="color:#999; font-weight:bold"><?=$curtype[$t]?></option>
        	<?php 
		}
	?>
	<option <?=($row['name']=="WMR" ? "selected" : "")?> value="<?php echo $row['name']; ?>"><?php echo $row['extname']; ?></option>
<?php } ?>
    </option>
    </select>

      </td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right"></td>
      <td>      Базовый: <input type="text" name="value" id="value" value="" size="6" />%
      Дополнительные:
      <input type="text" name="value1" id="value1" value="" size="6" /> /
      <input type="text" name="value2" id="value2" value="" size="6" /> /
      <input type="text" name="value3" id="value3" value="" size="6" /> 
</td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input type="checkbox" name="inactive" value="1"/> Скрытый (на сайте не видно, только в экспортных файлах)</td>
      <td><input type="checkbox" name="onsite_visible" value="1"/> Скрытый (на сайте видно, в экспорте отсутствует)</td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input type="submit" value="Добавить" /></td>
    </tr>
  </table>
  <input type="hidden" name="update_predel" value="insert" />
</form>


</td><td>

</td>
</tr>
</table>

<?php
$select="SELECT id, currname1, currname2, value,
		type , date, (

			SELECT extname
			FROM currency
			WHERE name = tb.currname1
		) AS extname1, (

			SELECT extname
			FROM currency
			WHERE name = tb.currname2
		) AS extname2
		FROM (select * from addon_predel order by date desc) as tb
		GROUP BY currname1, currname2,
		type ORDER by currname1 ASC , currname2 ASC ,
		type ASC;";
$query=_query($select, "detail.php 5");
$row_predel=$query->fetch_assoc();

?>


<table border="0" cellpadding="5" cellspacing="0">
<tr><td height="40"></td></tr>
<tr><td id="right_border" colspan="2">Тарифная сетка</td>
<td width="40" id="right_border">Базовая</td>
	<td align="center" id="right_border">100-1000</td>
    <td align="center" id="right_border">1000 - 5000</td>
    <td align="center" id="right_border">5000 - &infin;</td>
</tr>

<?php foreach ($money as $dimm1) { 
		foreach ($dimm1 as $dimm2) { 



?><tr>
 <form action="detail.php?<?=$_SERVER['QUERY_STRING']?>" method="post">
    <td id="right_border" valign="top">
    <?=strtolower($dimm2['curr1'])?>
    </td>
	<td valign="top"><?=strtolower($dimm2['curr2'])?>
	<?php /* <img src="../images/<?=strtolower($dimm2['curr1'])?>.gif" height="16"> <strong>|||</strong> <img src="../images/<?=strtolower($dimm2['curr2'])?>.gif" height="16"> */?>
</td>
<td width="120" align="left" height="25"
  id="right_border">
<?php 

if ( $clid!='0' ) {
	$select="SELECT addon.id, addon.currname1, addon.currname2, 
				addon.value FROM addon 
				where currname1='".$dimm2['curr1']."' 
				and currname2='".$dimm2['curr2']."' 
				and clientid=".$clid." order by date desc";
	$query=_query($select, "detail.php 7");

	if ( mysql_num_rows($query)==0 ) {
		$base_value=$money[$dimm2['curr1']][$dimm2['curr2']]['value'];
		$base_style='';
		$client_value='';
	}else{
		$row=$query->fetch_assoc();
		$base_value=$row['value'];
		$base_style='color:#CC0000;';
		$client_value=$money[$dimm2['curr1']][$dimm2['curr2']]['value'];
	}

}else{
	$base_value=$money[$dimm2['curr1']][$dimm2['curr2']]['value'];
	$base_style='';
	$client_value='';
}
	
?>


<input type="text" style="<?=$base_style?>text-align:right" name="value" value="<?=$base_value?>" size="6"> <?=$client_value?><br>
Курсы:<br>
<?=round($courses[$dimm2['curr1']][$dimm2['curr2']]/$base_value,3);?> - 
<?=round($base_value/$courses[$dimm2['curr1']][$dimm2['curr2']],3);?>  
<input type="hidden" name="currname1" value="<?=$dimm2['curr1']?>">
<input type="hidden" name="currname2" value="<?=$dimm2['curr2']?>">
</td>

<?php 
	$select="select value from addon_predel where type=1 and currname1='".$dimm2['curr1']."'
															and currname2='".$dimm2['curr2']."'
															and clientid=".$clid." order by date desc";
	$query=_query($select, "detail.php 8");
	$row_predel=$query->fetch_assoc();
	if ( $clid!='0' ) {	

	
	if ( mysql_num_rows($query)==0 ) {
		$select="select value from addon_predel where type=1 and currname1='".$dimm2['curr1']."'
															 and currname2='".$dimm2['curr2']."'
															 and clientid=0 order by date desc";
															
		$query=_query($select, "detail.php 8");
		$row_predel=$query->fetch_assoc();
																	
		$base1_value=$row_predel['value'];
		$base1_style='';

	}else{
		$base1_value=$row_predel['value'];
		$base1_style='color:#CC0000;';
	}

}else {
		$base1_style='';
		$base1_value=$row_predel['value'];
}
		
		
?>
		
<td id="right_border">&nbsp;
<?php if ( mysql_num_rows($query)!=0 ) { ?>
<input type="text" style="<?=$base1_style?>text-align:right" name="value1" id="value1"
 value="<?=($base_value-($base1_value/100))?>" size="6" /> <?=floatval($base1_value)?>%<br>
Курсы: <br>
<?=round($courses[$dimm2['curr1']][$dimm2['curr2']]/($base_value-($base1_value/100)),3);?> / 
<?=round(($base_value-($base1_value/100))/$courses[$dimm2['curr1']][$dimm2['curr2']],3);
$values1[$dimm2['curr1']][$dimm2['curr2']]=($base_value-($base1_value/100));
}else {
$values1[$dimm2['curr1']][$dimm2['curr2']]='';
}?>
</td>


<?php 
	$select="select value from addon_predel where type=2 and currname1='".$dimm2['curr1']."'
															and currname2='".$dimm2['curr2']."'
															and clientid=".$clid." order by date desc";
	$query=_query($select, "detail.php 8");
	$row_predel=$query->fetch_assoc();
	if ( $clid!='0' ) {	
		
		
	if ( mysql_num_rows($query)==0 ) {
		$select="select value from addon_predel where type=2 and currname1='".$dimm2['curr1']."'
															 and currname2='".$dimm2['curr2']."'
															 and clientid=0 order by date desc";
															
		$query=_query($select, "detail.php 8");
		$row_predel=$query->fetch_assoc();
																	
		$base2_value=$row_predel['value'];
		$base2_style='';

	}else{
		$base2_value=$row_predel['value'];
		$base2_style='color:#CC0000;';
	}

}else {
		$base2_style='';
		$base2_value=$row_predel['value'];
}
?>
		
<td id="right_border">&nbsp;
<?php if ( mysql_num_rows($query)!=0 ) { ?>
<input type="text" style="<?=$base2_style?>text-align:right" name="value2" id="value2"
 value="<?=($base_value-($base2_value/100))?>" size="6" /> <?=floatval($base2_value)?>% <br>
Курсы: <br>
<?=round($courses[$dimm2['curr1']][$dimm2['curr2']]/($base_value-($base2_value/100)),3);?> / 
<?=round(($base_value-($base2_value/100))/$courses[$dimm2['curr1']][$dimm2['curr2']],3);
$values2[$dimm2['curr1']][$dimm2['curr2']]=($base_value-($base2_value/100));?>  
<?php }else{
	$values2[$dimm2['curr1']][$dimm2['curr2']]='';
}?>
</td>

<?php 
		$select="select value from addon_predel where type=3 and currname1='".$dimm2['curr1']."'
															and currname2='".$dimm2['curr2']."'
															and clientid=".$clid." order by date desc";
		$query=_query($select, "detail.php 8");
		$row_predel=$query->fetch_assoc();
	if ( $clid!='0' ) {	

		
	if ( mysql_num_rows($query)==0 ) {
		$select="select value from addon_predel where type=3 and currname1='".$dimm2['curr1']."'
															 and currname2='".$dimm2['curr2']."'
															 and clientid=0 order by date desc";
															
		$query=_query($select, "detail.php 8");
		$row_predel=$query->fetch_assoc();
																	
		$base3_value=$row_predel['value'];
		$base3_style='';

	}else{
		$base3_value=$row_predel['value'];
		$base3_style='color:#CC0000;';
	}

}else {
		$base3_style='';
		$base3_value=$row_predel['value'];
}
?>
		
<td id="right_border">&nbsp;
<?php if ( mysql_num_rows($query)!=0 ) { ?>
<input type="text" style="<?=$base3_style?>text-align:right" name="value3" id="value3"
 value="<?=($base_value-($base3_value/100))?>" size="6" /> <?=floatval($base3_value)?>%<br>
Курсы: <br>
<?=round($courses[$dimm2['curr1']][$dimm2['curr2']]/($base_value-($base3_value/100)),3);?> / 
<?=round(($base_value-($base3_value/100))/$courses[$dimm2['curr1']][$dimm2['curr2']],3);
	$values3[$dimm2['curr1']][$dimm2['curr2']]=($base_value-($base3_value/100));
}else{
	$values3[$dimm2['curr1']][$dimm2['curr2']]='';
}?>
</td>
<td width="20"></td>
<td><input type="submit" value="Изменить">  <input type="hidden" name="update_predel" value="insert" /></td>
<td></td>
	</form>
    </tr><?php } 
} ?>
</table>

</td>
<td width="60"></td>
<td valign="top">


</td></tr>
</table>
<?php $select="select * from currency";
$query=_query($select,"");?>
<script language="javascript">
values= new Array;
<?php while ( $row=$query->fetch_assoc() ) { ?>
values['<?=$row['name']?>']=new Array;
<?php } 
	//reset($money);
foreach ($money as $row1){
	foreach ($row1 as $row2){
		echo "values['".$row2['curr1']."']['".$row2['curr2']."']=".$row2['value'].";
		";
	}
}
	reset($values1);
	echo "
	values1=new Array;";
	while (list($key, $val) = each($values1)) { 
		echo "values1['".$key."']=new Array;";

		foreach ( $val as $val1 => $val2 ) {
		//print_r ($val2);
			if ( $val2!='' ) {
				echo "values1['".$key."']['".$val1."']=".$val2.";";
			} else {
				echo "values1['".$key."']['".$val1."']='';";
			}
		}
	}
	
	reset($values2);
	echo "
	values2=new Array;";
	while (list($key, $val) = each($values2)) { 
		echo "values2['".$key."']=new Array;";

		foreach ( $val as $val1 => $val2 ) {
		//print_r ($val2);
			if ( $val2!='' ) {
				echo "values2['".$key."']['".$val1."']=".$val2.";";
			} else {
				echo "values2['".$key."']['".$val1."']='';";
			}
		}
	}
	
	reset($values3);
	echo "
	values3=new Array;";
	while (list($key, $val) = each($values3)) { 
		echo "values3['".$key."']=new Array;";

		foreach ( $val as $val1 => $val2 ) {
		//print_r ($val2);
			if ( $val2!='' ) {
				echo "values3['".$key."']['".$val1."']=".$val2.";";
			} else {
				echo "values3['".$key."']['".$val1."']='';";
			}
		}
	}
?>
function set2() {
	document.forms[1].value.value=values[document.getElementById("predel_currname1").value][document.getElementById("predel_currname2").value];	
	document.forms[1].value1.value=values1[document.getElementById("predel_currname1").value][document.getElementById("predel_currname2").value];
	document.forms[1].value2.value=values2[document.getElementById("predel_currname1").value][document.getElementById("predel_currname2").value];
	document.forms[1].value3.value=values3[document.getElementById("predel_currname1").value][document.getElementById("predel_currname2").value];
}

</script>
<?php mysql_free_result($addon); 
?>