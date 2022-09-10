<?php
$hostname_ma = "db5.freehost.com.ua";
$database_ma = "webmoneyy_ma";
$username_ma = "webmoneyy_ma";
$password_ma = "pXqQBhRst";
$ma = mysql_pconnect($hostname_ma, $username_ma, $password_ma) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_select_db($database_ma, $ma);

function _query($query, $step) {
    $result = mysql_query(htmlspecialchars($query,ENT_NOQUOTES));
    if( !$result ) {
	_error("Query failed : " . mysql_error(), $step, $query." ".print_r($_POST,1));
    } else {
	return $result;
    };
};
function _query2($query, $step) {
    $result = mysql_query($query);
    if( !$result ) {
	_error("Query failed : " . mysql_error(), $step, $query." ".print_r($_POST,1));
    } else {
	return $result;
    };
};

function _error($string, $step, $query) {
    error_log($string . ", step: $step, sql=".$query, 1, "support@obmenov.com");
    die();

};

function myErrorHandler ($errno, $errstr, $errfile, $errline) {
	if ( strpos(print_r($_SERVER,1),"current_rates.php") ) {
	} else {			
 	error_log("Err#:".$errno ."\r\n".
			  "Description: ".$errstr ."\r\n". 
			  "Script: ".$errfile ."\r\n". 
			  "Line: ".$errline."\r\n".
			  "Session: ".print_r($_SESSION,1).
			  "Server: ".print_r($_SERVER,1).
			  "POST: ".print_r($_POST,1).
			  "Request: ".print_r($_REQUEST,1), 1, "support@obmenov.com");
	
	}
    return false;
}
	
		if ( isset($_GET['rid']) ) {
			$rnd=$_GET['rid'];
			$form_action=$_SERVER['PHP_SELF']."?rid=".$rnd;
		}else{
			$rnd = strtolower(substr(md5(uniqid(microtime(), 1)).getmypid(),1,20));
			$form_action=$_SERVER['PHP_SELF']."?rid=".$rnd;
		}
	if ( isset($_POST['addgoodtype']) && $_POST['addgoodtype']==1 ) {

		$insert="insert into goodstypes (`parent`, `descr`, `extdescr`, type) values (".
							$_POST['parent'].", '".
							$_POST['desc']."', '".
							$_POST['extdesc']."', '".
							$_POST['type']."')";
		$query=_query($insert,"");
															  
	}
	$step=1;
	if ( isset($_POST['step']) ) {
		$step=intval($_POST['step']);
		reset($_POST);
		//echo 1;
		while ( list($key, $val) = each($_POST) ) {
			if ( $key == "step" or $key == "submit" or $key == "back" ) { //echo "<form action='$val' method='post' name='form1'>";
			}else {
			//echo 2;
							
				//"delete from session where clid='".$clid."' and rid='".$rnd."' and `step` >".intval($_POST['step']).";";
				
				
				$insert="insert into session (field, value, rid, clid, step) values ('".
										htmlspecialchars($key)."','".
										htmlspecialchars($val)."','".
										htmlspecialchars($rnd)."','".
										htmlspecialchars($clid)."',".
										$step.")";
				$query=_query($insert,"addgoods.php 7");
			}
			$delete="DELETE FROM session WHERE clid = '".$clid."' AND rid = '".$rnd."' AND `step` >".$step;
			$query=_query2($delete,"addgoods.php 7");
			//echo $delete;
		}
	
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Untitled Document</title>
</head>
<link href="../wm.css" rel="stylesheet" type="text/css" />
<script>
function change (step) {
document.addgoods.step.value=step;
document.addgoods.submit();
	
}
</script>
<body>

<form action="addgoods.php" method="post">
<?php
$select="select * from goodstypes";
$query=_query($select, "");
?>
<select name="parent">
<option value="0">Верх</option>
<?php 
while ( $row_goods=$query->fetch_assoc() ) { 
	$select1="select * from goodstypes where id=".$row_goods['parent'];
	$query1=_query($select1, "");
	if ( mysql_num_rows($query1)==1 ) { 
		$parent_row=$query1->fetch_assoc();
		$parent= $parent_row['descr']." - ";
	}else{
		$parent="";}

?>
<option value="<?=$row_goods['id']?>"><?=$parent.$row_goods['descr']?></option>
<?php } ?>
</select>
Описание: <input name="desc" value="" /> Дет. описание: <input name="extdesc" /><br />
Тип: <select name="type">
<option value="1">Участник группы</option>
<option value="2">Маленькое поле</option>
<option value="3">Большое поле</option>
<option value="4">Описание</option>
<option value="5">Файл</option>
</select>
<input type="hidden" name="addgoodtype" value="1" /><input type="submit" />
</form>

<br />
<br />
<br />


<table border="1"><tr><td width="50" valign="bottom" align="right" rowspan="2">

<?php if ( $step>1 ) {?>
<form action="<?=$form_action?>" method="post"><input type="hidden" name="step" value="<?=($step-1)?>" /><input type="submit" value="Назад" name="back" /></form>
<?php } ?>
</td>

<td>
<form action="<?=$form_action?>" method="post" name="addgoods">
<table class="tableborder" cellpadding="0" cellspacing="0" border="1">
<tr>
<td colspan="2">Добавление нового товара :: Цена 0,00$</td>
</tr>
<?php if ( !isset($_POST['step']) || $_POST['step']<=1 ) { ?>
<tr>
<td valign="top">1. Выберите тип товара</td>
<td>
<?php 
$select="select * from goodstypes where parent=0";
$query=_query($select,"");

while ( $row_good=$query->fetch_assoc() ) {
?>
<input type="radio" name="goodtype" value="<?=$row_good['id']?>" /><?=$row_good['extdescr']?><br />
<?php } ?>

    <input type="hidden" value="2" name="step"/>

    <?php } ?>

</td>
</tr>

<?php if ( isset($_POST['step']) ){ 

	if ( $step==2 ) { 
	
	$goodtype="select value from session WHERE clid = '".$clid."' AND rid = '".$rnd."' AND `step` ="
				.$step. " AND field='goodtype'";
	$query=_query($goodtype, "addgoods 3");
	$goodtype=$query->fetch_assoc();
	$select="select * from goodstypes where id=".$goodtype['value'];
	$query=_query($select,"");
	$row_good=$query->fetch_assoc();
	?>
    <tr><td colspan="2">1. Тип товара: <?=$row_good['descr']?></td></tr>   
    	<tr><td colspan="2">
Выберите то, что Вы хотите разместить в качестве товара. Именно это покупатель получит после оплаты.</td></tr>
	<tr><td valign="top">2. Покупатель получит</td><td>
    <?php

	$select="select * from goodstypes where parent=".$goodtype['value'];
	$query=_query($select,"");

	while ( $row_good=$query->fetch_assoc() ) {		?>
<input type="radio" name="goodtype2" value="<?=$row_good['id']?>" /><?=$row_good['extdescr']?><br />
<?php } ?>
	<input type="hidden" value="3" name="step" />
</td></tr>
	<?php } //step 2 ?> 
    
	<?php 
	if ( $step==3 ) { ?>
    <?php $goodtype="select value from session WHERE clid = '".$clid."' AND rid = '".$rnd."' AND `step` =2"
				. " AND field='goodtype'";
	echo $goodtype;
	$query=_query($goodtype, "addgoods 5");
	$goodtype=$query->fetch_assoc();
	$select="select * from goodstypes where id=".$goodtype['value'];
	$query=_query($select,"");
	$row_good=$query->fetch_assoc();
	?>
    <tr><td>1. Тип товара: </td><td><?=$row_good['descr']?></td></tr>
    <?php
	$goodtype="select value from session WHERE clid = '".$clid."' AND rid = '".$rnd."' AND `step` ="
				.$step. " AND field='goodtype2'";
	$query=_query($goodtype, "addgoods 6");
	$goodtype=$query->fetch_assoc();
		?>
     <tr>
     <td valign="top">2. Покупатель получит</td>
     <td><?php 
	 $select="select * from goodstypes where id=".$goodtype['value'];
	$query=_query($select,"");
	$row_good=$query->fetch_assoc();
	 
	 ?><?=$row_good['extdescr']?></td>
     </tr> 
	    
	
    	<tr><td colspan="2">
Укажите здесь дополнительные характеристики товара. Поля отмеченные символом * являются обязательными для заполнения.</td></tr>
	<tr><td valign="top">3. Характеристики товара</td>
    <td><?php //echo "good=".$goodtype['value']."-";
$select="select * from goodstypes where parent=".$goodtype['value'];
$query=_query($select,"");

while ( $row_good=$query->fetch_assoc() ) {
	if ( strlen($row_good['fieldname'])==0 ) {
?>
<input type="radio" name="goodtype3" value="<?=$row_good['id']?>" /><?=$row_good['extdescr']?><br />
<?php }else{ ?>
<?php echo $row_good['descr'].":<br />"; 
   switch ($row_good['type']) { 
   		case "1" : echo "<input type='radio' name='".$row_good['fieldname']."' value='1' /><br />"; break ;
   		case "2" : echo "<input type='text' name='".$row_good['fieldname']."' size=6 value='' /><br />"; break ;
   		case "3" : echo "<input type='text' name='".$row_good['fieldname']."' size=30 value='' /><br />"; break ;
   		case "4" : echo "<textarea type='text' name='".$row_good['fieldname']."' rows=6 cols=40 value='' /></textarea><br />"; break ;
   		case "5" : echo "<input type='file' name='".$row_good['fieldname']."' size=30 value='' /><br />"; break ;
   }
?>
<?php	
}

} ?>
<input type="hidden" value="4" name="step" />

	<?php } else if ( $_POST['step']>3 ) {
		?>
     <td valign="top">3. Характеристики товара</td><td>
	<select name="3type"  onchange="change(4);">
    <?php 
	$select="select * from goodstypes where type=1 and parent=".$_POST['3type'];
	$query=_query($select,"");
	while ( $row_good=$query->fetch_assoc() ) { ?>
     <option value="<?=$row_good['id'] ?>" <?=$_POST['3type']==$row_good['id'] ? "selected" : ""?>><?=$row_good['descr']?>
     </option>
     <?php } ?>
    </select>

    <?php } } ?>
</td>
</tr>
</table>

</td></tr><tr><td><input type="submit" name="submit" value="Далее" /></form></td></tr></table>

<?=print_r($_POST)?>

</body>
</html>