<?php require_once('../Connections/ma.php');
require_once($serverroot.'adrefzw/top.php');
require_once($serverroot.'siti/class.php');
@ini_set ("display_errors", true);
$user=new user();
if ($user->auth("adm")==1) {

}else{
	$user->bad_auth();
}

if ( isset($_POST['edit']) && $_POST['edit']=="Добавить запись" ) {
	$tbl= mysql_real_escape_string($_POST['tbl']);
	foreach ($_POST as $k=>$v) {
		if ( $k!="tbl" && $k!="edit" ) {
			$f[$k]=$k; $f1[$k]="'".$v."'";
		}
	}
	$insert="insert into ".$tbl." (".implode(",",$f).")
						values (".implode(",", $f1).")";
	$query=_query($insert,"settings.php");
	
}

$select="select * from settings_table where 1 group by table_label";
$query=_query($select, "history.php");
?>

<form action="settings.php" method="post">
Таблица <select name="tbl">
<?php
while ($row=$query->fetch_assoc() ) { 
	$selected="";
	if ( isset($_POST['tbl']) && $_POST['tbl']==$row['tbl'] ) {
		$selected=" selected='selected'";
	}
?>
<option value="<?=$row['tbl']?>" <?=$selected?>><?=$row['table_label']?></option>
<?php $selected="";} ?>
</select> <input type="submit" value="ОК">
</form>
<?php if ( isset($_POST['tbl']) ) { ?>
Текущие записи:
<table border="1">
<?
	$select="select * from `settings_table` where tbl='".mysql_real_escape_string($_POST['tbl'])."'";
	$query=_query($select,"settings.php"); 
	$select="select ";
	?>
    <tr>
    <?php
	while ( $row=$query->fetch_assoc() ) { 
    	$select.=$row['field'].",";
		?>
   <td width="150"><?=$row['field_label']?></td>    
        <?php }  ?>
        </tr>
<?php
	$select.= "'' from ".mysql_real_escape_string($_POST['tbl']);
	$query=_query($select,"");
	?>
    
 
	<?php	while ( $row=$query->fetch_assoc() ) { ?>
    <tr>
    <?php foreach ( $row as $v ) {	?>
		<td><?=$v?></td>
	<?php } ?>
	</tr>
	<?php } ?>

<?
	$select="select * from settings_table where tbl='".mysql_real_escape_string($_POST['tbl'])."'";
	$query=_query($select,"settings.php"); ?>

<form action="settings.php" method="post">
<input type="hidden" name="tbl" value="<?=$_POST['tbl']?>">
<tr>

<?php while ($row=$query->fetch_assoc() ) { ?>

<td><?php 
	if ( $row['field']=="active" ) {
		echo "<input type='checkbox' name='".$row['field']."' value='1'>";
	}else{
		echo "<input type='text' name='".$row['field']."' value=''>";
	}
?>
            </td>

<?php } ?>
<td><input type="submit" name="edit" value="Добавить запись" /></td>
</tr>
</form>
</table>
<?php } ?>

</body>
</html>