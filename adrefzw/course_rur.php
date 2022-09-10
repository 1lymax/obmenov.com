<?php require_once('../Connections/ma.php'); ?>
<?php require_once($serverroot.'siti/class.php');

$user=new user();
if ($user->auth("adm")==1) {

}else{
	$user->bad_auth();
}


$currentPage = $_SERVER["PHP_SELF"];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
//update
			date_default_timezone_set('Europe/Helsinki');
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && isset($_POST["update"])) {

  $updateSQL = sprintf("UPDATE course_rur SET `min`=%s, `max`=%s, `date`=%s WHERE id=%s",
                       str_replace(",",".",GetSQLValueString($_POST['min'], "float")),
                       str_replace(",",".",GetSQLValueString($_POST['max'], "float")),
                       GetSQLValueString($_POST['date'], "date"),
                       GetSQLValueString($_POST['id'], "int"));
   

  mysql_select_db($database_ma, $ma);
  $Result1 = mysql_query($updateSQL, $ma) or die(mysql_error());

  $updateGoTo = "course_rur.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
} //end update
// begin insert
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && isset($_POST["insert"])) {
  $updateSQL = sprintf("INSERT INTO course_rur (min, max, `date`) VALUES (%s, %s, %s)",
                       str_replace(",",".",GetSQLValueString($_POST['min'], "float")),
                       str_replace(",",".",GetSQLValueString($_POST['max'], "float")),
                       GetSQLValueString(date("Y-m-d H:i:s"), "date"));
   

  mysql_select_db($database_ma, $ma);
  _query('SET character_set_database = cp1251',"");
_query('SET NAMES cp1251',"");
  $Result1 = mysql_query($updateSQL, $ma) or die(mysql_error());

  $updateGoTo = "course_rur.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}
// end insert
//begin delete
if ((isset($_GET['id'])) && ($_GET['id'] != "") && (isset($_GET['delete']))) {
  $deleteSQL = sprintf("DELETE FROM course_rur WHERE id=%s",
                       GetSQLValueString($_GET['id'], "int"));

  mysql_select_db($database_ma, $ma);
  $Result1 = mysql_query($deleteSQL, $ma) or die(mysql_error());

  $deleteGoTo = "course_rur.php";
/*  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }*/
  header(sprintf("Location: %s", $deleteGoTo));
}
//end delete
$maxRows_course_rur = 10;
$pageNum_course_rur = 0;
if (isset($_GET['pageNum_course_rur'])) {
  $pageNum_course_rur = $_GET['pageNum_course_rur'];
}
$startRow_course_rur = $pageNum_course_rur * $maxRows_course_rur;

mysql_select_db($database_ma, $ma);
$query_course_rur = "SELECT course_rur.id, course_rur.`min`, course_rur.`max`, course_rur.`date` FROM course_rur ORDER BY date desc";
$query_limit_course_rur = sprintf("%s LIMIT %d, %d", $query_course_rur, $startRow_course_rur, $maxRows_course_rur);
$course_rur = mysql_query($query_limit_course_rur, $ma) or die(mysql_error());
$row_course_rur = mysql_fetch_assoc($course_rur);

if (isset($_GET['totalRows_course_rur'])) {
  $totalRows_course_rur = $_GET['totalRows_course_rur'];
} else {
  $all_course_rur = mysql_query($query_course_rur);
  $totalRows_course_rur = mysql_num_rows($all_course_rur);
}
$totalPages_course_rur = ceil($totalRows_course_rur/$maxRows_course_rur)-1;

$queryString_course_rur = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_course_rur") == false && 
        stristr($param, "totalRows_course_rur") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_course_rur = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_course_rur = sprintf("&totalRows_course_rur=%d%s", $totalRows_course_rur, $queryString_course_rur);

?>
<?php

$maxRows_DetailRS1 = 10;
$pageNum_DetailRS1 = 0;
if (isset($_GET['pageNum_DetailRS1'])) {
  $pageNum_DetailRS1 = $_GET['pageNum_DetailRS1'];
}
$startRow_DetailRS1 = $pageNum_DetailRS1 * $maxRows_DetailRS1;

$colname_DetailRS1 = "-1";
if (isset($_GET['recordID'])) {
  $colname_DetailRS1 = $_GET['recordID'];
}
mysql_select_db($database_ma, $ma);
$query_DetailRS1 = sprintf("SELECT course_rur.id, course_rur.`min`, course_rur.`max`, course_rur.`date` FROM course_rur WHERE id = %s", GetSQLValueString($colname_DetailRS1, "int"));
$query_limit_DetailRS1 = sprintf("%s LIMIT %d, %d", $query_DetailRS1, $startRow_DetailRS1, $maxRows_DetailRS1);
$DetailRS1 = mysql_query($query_limit_DetailRS1, $ma) or die(mysql_error());
$row_DetailRS1 = mysql_fetch_assoc($DetailRS1);

if (isset($_GET['totalRows_DetailRS1'])) {
  $totalRows_DetailRS1 = $_GET['totalRows_DetailRS1'];
} else {
  $all_DetailRS1 = mysql_query($query_DetailRS1);
  $totalRows_DetailRS1 = mysql_num_rows($all_DetailRS1);
}
$totalPages_DetailRS1 = ceil($totalRows_DetailRS1/$maxRows_DetailRS1)-1;

include_once ("top.php"); ?>
<table border="1" align="center">
  <tr>
    <td> id </td>
    <td>Мин. курс UAH-RUR</td>
    <td>Макс. курс UAH-RUR</td>
    <td>Дата</td>
  </tr>
  <?php do { ?>
    <tr>
      <td><a href="course_rur.php?recordID=<?php echo $row_course_rur['id']; ?>"> <?php echo $row_course_rur['id']; ?>&nbsp; </a></td>
      <td><?php echo $row_course_rur['min']; ?>&nbsp; </td>
      <td><?php echo $row_course_rur['max']; ?>&nbsp; </td>
      <td><?php echo $row_course_rur['date']; ?>&nbsp; </td><td><a href="course_rur.php?id=<?php  echo $row_course_rur['id'] ?>&amp;delete">удалить</a></td>
    </tr>
    <?php } while ($row_course_rur = mysql_fetch_assoc($course_rur)); ?>
</table>
<br />
<table border="0">
  <tr>
    <td><?php if ($pageNum_course_rur > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_course_rur=%d%s", $currentPage, 0, $queryString_course_rur); ?>">First</a>
    <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_course_rur > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_course_rur=%d%s", $currentPage, max(0, $pageNum_course_rur - 1), $queryString_course_rur); ?>">Previous</a>
    <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_course_rur < $totalPages_course_rur) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_course_rur=%d%s", $currentPage, min($totalPages_course_rur, $pageNum_course_rur + 1), $queryString_course_rur); ?>">Next</a>
    <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum_course_rur < $totalPages_course_rur) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_course_rur=%d%s", $currentPage, $totalPages_course_rur, $queryString_course_rur); ?>">Last</a>
    <?php } // Show if not last page ?></td>
  </tr>
</table>
Records <?php echo ($startRow_course_rur + 1) ?> to <?php echo min($startRow_course_rur + $maxRows_course_rur, $totalRows_course_rur) ?> of <?php echo $totalRows_course_rur ?>

<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
  <table align="center">
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Id:</td>
      <td><?php echo $row_DetailRS1['id']; ?></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Min:</td>
      <td><input type="text" name="min" id="min" value="<?=$row_DetailRS1['min']; ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Max:</td>
      <td><input type="text" name="max" id="max" value="<?=$row_DetailRS1['max']; ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Date:</td>
      <td><input name="date" type="text" id="date" value="<?php if ($row_DetailRS1['date']==''){echo date("Y-m-d H:i:s");} else {echo $row_DetailRS1['date'];} ?>" size="32" readonly="true" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input name="update" type="submit" disabled value="Update record" />
      <input name="insert" type="submit" value="Insert record" />
      <input name="" type="button" onClick="document.forms.form1.min.value='';
      document.forms.form1.max.value='';
      document.forms.form1.id.value='';
      document.forms.form1.date.value='<?php echo date("Y-m-d H:i:s"); ?>';
      " value="Очистить" /></td>
    </tr>
  </table>
  <input type="hidden" name="MM_update" value="form1" />
  <input type="hidden" name="id" id="id" value="<?=$row_DetailRS1['id']; ?>" />
  
</form>
<p>&nbsp;</p>
<?php mysql_free_result($DetailRS1);
mysql_free_result($course_rur);

?>
<a href="http://finance.i.ua/" target="_blank"><img src="http://f.i.ua/fp3_b15_c1_l0.png" border="0" alt="Курс рубля"></a>
</body>