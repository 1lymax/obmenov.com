<?php require_once('../Connections/ma.php');
	require_once('../function.php');
	require_once('../siti/class.php');

$user=new user();
if ($user->auth("adm")==1) {

}else{
	$user->bad_auth();
}
?>


<table border="0"><tr><td>
<form action="private_order.php" method="post" name="form2" id="form2">
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