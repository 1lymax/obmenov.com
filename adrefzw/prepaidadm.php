<?php require_once('../Connections/ma.php'); ?>
<?php require_once('../function.php');
require_once($serverroot.'siti/class.php');

$user=new user();
if ($user->auth("adm, oper1")==1) {

}else{
	$user->bad_auth();
}

if ( isset($_GET['action']) && $_GET['action']=="new" ) {
	
	
}




$i=0;
$searcharr = array();
$searchvalues['search1id']='';
$searchvalues['search2name']='';
$searchvalues['search1number']='';
$searchvalues['search1content']='';
$searchvalues['search1state']='';

if ( isset($_POST['action']) && $_POST['action']=="edit" ) {
	
	reset($_POST);
	
	while (list($key, $val) = each($_POST)) {
		if ( substr($key,0,4)=="edit" ) {
		$itemid=substr($key,4,strlen($key)-3);
		$update="UPDATE items SET number='".htmlspecialchars($_POST['number'.$itemid])."',
				 content='".htmlspecialchars($_POST['content'.$itemid])."', 
		state='".htmlspecialchars($_POST['state'.$itemid])."'
		 WHERE id=".$itemid;
		$query=_query($update, "prepaidadm.php 3");
		$i=$i+1;
		//echo substr($key,4,strlen($key-4)+1)." ";
		}
	}
										  
}

reset($_POST);
$wordlen=strlen("search");
while (list($key, $val) = each($_GET)) {
	
	$table1="items";
	$table2="item_name";

	//echo substr($key,0,6);
	if ( substr($key,0,$wordlen)=="search") {
		if ( substr($key,$wordlen,1)=="1" ) {
			$table=$table1; }
		elseif ( substr($key,$wordlen,1)=="2" ) {
			$table=$table2; }
		$searcharr[$key]=" AND ".$table.".".substr($key,$wordlen+1,strlen($key)-$wordlen)." LIKE '%".$val."%' ";
		$searchvalues[$key]=$val;
	}else {
		//$searcharr[$key]="";
	}
	
}


$mess='';
$sort="ORDER BY items.id desc";
$sortid='&sort=idd';
$sortname='&sort=namea';

$currentPage = $_SERVER["PHP_SELF"];

$max = 50;
$page = 0;
if (isset($_GET['page'])) {
  $page = $_GET['page'];
}
$start = $page * $max;

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO items (itemid, content, state, number) VALUES (%s, %s, 'Y', %s)",
                       GetSQLValueString($_POST['itemid'], "int"),
                       GetSQLValueString($_POST['content'], "text"),
					   GetSQLValueString($_POST['number'], "text")
					   );

  $Result1 = mysql_query($insertSQL, $ma) or die(mysql_error());
  $mess= "Запись успешно добавлена. Идентификатор записи -". mysql_insert_id();
}


$query_prepaid = "SELECT items.id, items.itemid, items.content, items.`state`, items.reserved FROM items";
$prepaid = mysql_query($query_prepaid, $ma) or die(mysql_error());
$row_prepaid = mysql_fetch_assoc($prepaid);
$totalRows_prepaid = mysql_num_rows($prepaid);

$items = "SELECT id, name, price, unit FROM item_name ORDER BY item_name.`order`;";
$query_item = _query($items, "prepaid_ad 1");
$query_amount="SELECT item_name.id AS item_id, item_name.name, COUNT(items.itemid) AS total, (SELECT COUNT(items.itemid) FROM items WHERE items.state='Y' AND items.itemid=item_name.id GROUP by items.itemid) as amount FROM items, item_name WHERE items.itemid=item_name.id and item_name.inactive!=1 GROUP by items.itemid ORDER BY item_name.typeid, item_name.name";
$query_amount = _query($query_amount, "prepaid_ad 3");



include_once ("top.php"); 


if ( isset($_GET['sort']) ){
	if ( $_GET['sort']=="ida") {
	$sortid="sort=idd";
	$sort=" ORDER BY items.id asc";	
	}
	if ( $_GET['sort']=="idd") {
	$sortid="sort=ida";
	$sort=" ORDER BY items.id desc";	
	}
	if ( $_GET['sort']=="namea") {
	$sortname="sort=named";
	$sort=" ORDER BY item_name.name asc";	
	}
	if ( $_GET['sort']=="named") {
	$sortname="sort=namea";
	$sort=" ORDER BY item_name.name desc";	
	}
}




$select="SELECT items.id AS itemid, item_name.name, item_name.url, item_name.unit, items.number, items.state, items.content FROM items, item_name WHERE items.itemid=item_name.id ";

while (list($key, $val) = each($searcharr)) {
	$select=$select.$val;
	
}
$select=$select.$sort;

//echo $select;
$query_limit_items = sprintf("%s LIMIT %d, %d", $select, $start, $max);
$items =_query($query_limit_items, "prepaid.php ad 5");
$item_row = mysql_fetch_assoc($items);
if (isset($_GET['total'])) {
  $total = $_GET['total'];
} else {
  $all_items = mysql_query($select);
  $total = mysql_num_rows($all_items);
}
$totalPages = ceil($total/$max)-1;

$queryString_items = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "page") == false && 
        stristr($param, "total") == false && 
        stristr($param, "sort") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_items = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_items = sprintf("&total=%d%s", $total, $queryString_items);


?>
<script>
function show(id) {
	if ( eval("document.forms[1].edit"+id+".checked")==true ) {
		document.getElementById("number"+id).disabled=false;
		document.getElementById("content"+id).disabled=false;
	}else {
		document.getElementById("number"+id).disabled=true;
		document.getElementById("content"+id).disabled=true;
	}
}

</script>
<table width=100%><tr><td colspan="2"><a href="addgoods.php">+Добавить товар</a></td></tr>
<tr><td valign="top" align="center">


<table class="tableborder" style="border-right: 1px solid #dadad1;" width="800" cellpadding="0" cellspacing="0">
<?=$i>0 ? "<tr><td colspan='3'>Обновлено ".$i." записей</td></tr>" : ""?>

<tr class='td_head'><td><?="<a href='prepaidadm.php?".$queryString_items.$sortid."'>ID</a>"?></td>
<td><?="<a href='prepaidadm.php?".$queryString_items.$sortname."'>Ваучер</a>"?></td><td>Номер</td><td>Код</td><td>Статус</td><td></tr>
<form action="prepaidadm.php" method="get">
<tr  class='td_head'><td>
  <input name="search1id" size="4" value="<?=$searchvalues['search1id']?>" />
</td>
<td>
  <input name="search2name" size="12"  value="<?=$searchvalues['search2name']?>" /></td>
<td>
  <input name="search1number" size="20" value="<?=$searchvalues['search1number']?>" />
</td>
<td>
  <input name="search1content" size="20" value="<?=$searchvalues['search1content']?>" />
</td>
<td><select name="search1state">
		<option value="" <?=$searchvalues['search1state']=="" ? "selected='selected'" : ""?>>Нет</option>
        <option value="Y" <?=$searchvalues['search1state']=="Y" ? "selected='selected'" : ""?>>Y</option>
        <option value="N" <?=$searchvalues['search1state']=="N" ? "selected='selected'" : ""?>>N</option>
        </select></td>
<td><input type="submit" value="Поиск"/> <a href="prepaidadm.php">X</a></td></tr>
<tr><td colspan="6"></td></tr>
</form>
<form action="prepaidadm.php?<?php echo $queryString_items;?>" name="editform" method="post">
<?php do  { ?>

 <tr>
  <td><?=$item_row['itemid']?>
  </td>
  <td><img src="<?=$siteroot?>i/game/<?=$item_row['url']?>_logo_sm.gif">&nbsp;&nbsp;<?=$item_row['name']?>
  </td>
  <td><input id="number<?=$item_row['itemid']?>"
  		name="number<?=$item_row['itemid']?>" value="<?=$item_row['number']?>" disabled="disabled" />
  </td>
  <td><input id="content<?=$item_row['itemid']?>" name="content<?=$item_row['itemid']?>" 
  		value="<?=$item_row['content']?>" disabled="disabled" />
  </td>
  <td><select id="state<?=$item_row['itemid']?>" name="state<?=$item_row['itemid']?>">
  	<option value="Y" <?=$item_row['state']=="Y" ? "selected" : ""?>>Активен</option>
    <option value="N" <?=$item_row['state']=="N" ? "selected" : ""?> style="color:#999">Погашен</option>
    </select>
  </td>
  <td><input type="checkbox" alt="Редактировать" name="edit<?=$item_row['itemid']?>" value="1" onchange="show(<?=$item_row['itemid']?>);" />
  </td>
 </tr>
 <?php } while ($item_row=mysql_fetch_assoc($items))?>
<tr><td colspan="6" align="right"><input type="submit" name="submit" value="Обновить..." /> 
</td></tr><input type="hidden" name="action" value="edit" />
</form>
</table>

</td>
<td width="40"></td>
<td valign="top" align="center">

<?=$mess; ?>
<form action="prepaidadm.php?<?=$queryString_items; ?>" method="post" name="form1" id="form1">

<table width="274" height="97" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="16" height="61">
			</td>
		<td align="left" width="239" height="61" style="font-size:10px; color:#666; font-family:Verdana, Geneva, sans-serif">
        
        
  <table align="center">
    <tr><td colspan="3" id="head_small">Ввод новых ваучеров</td></tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Номер:</td>
      <td><input type="text" name="number" value="" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Код:</td>
      <td><input type="text" name="content" value="" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input type="submit" value="Добавить запись" /></td>
    </tr>
    
    <tr valign="baseline">
      <td>id</td>
      <td>Ваучер</td><td>Остаток</td>
    </tr>
  <input type="hidden" name="MM_insert" value="form1" />
 <?php while ($row_amount=mysql_fetch_assoc($query_amount)) { ?>
 <tr><td>
  <input name="itemid" type="radio" value="<?=$row_amount['item_id'];?>" 
  <?= ( isset($_POST['itemid']) && $_POST['itemid'] ==$row_amount['item_id']) ? "checked" : "" ?>
    /><?=$row_amount['item_id'];?>
    
   </td>
   <td><?=$row_amount['name'];?></td>
   <td><?=$row_amount['amount'];?></td>
   </tr> 
      <?php }; ?>

 </table>
 
 
 </td>
		<td background="../images/prepaid_06.gif" width="19" height="61">
			</td>
	</tr>
	<tr>
		<td background="../images/prepaid_07.gif" width="16" height="21">
			</td>
		<td background="../images/prepaid_08.gif" width="179" height="21">
			</td>
		<td background="../images/prepaid_09.gif" width="19" height="21">
</td>
	</tr>
</table>
 
 
</form>
</td></tr>
<tr><td>
<table border="0" align="center">
  <tr>
    <td><?php if ($page > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, 0, $queryString_items); ?>"><<Начало</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($page > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, max(0, $page - 1), $queryString_items); ?>"><Пред.</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($page < $totalPages) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, min($total, $page + 1), $queryString_items); ?>">След.></a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($page < $totalPages) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, $totalPages, $queryString_items); ?>">Посл>></a>
        <?php } // Show if not last page ?></td>
  </tr>
  </table>
  <table align="center">
  <tr>
    <td align="center" colspan="4">Запись с <?php echo ($start + 1) ?> по <?php echo min($start + $max, $total) ?>. Всего: <?php echo $total; ?>
	</td>
  </tr>
</table>

</td></tr>
</table>

<p>&nbsp;</p>
</body>
</html>
<?php
mysql_free_result($prepaid);
?>
