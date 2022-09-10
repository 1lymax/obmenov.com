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
  $editFormAction .= "?" . htmlspecialchars($_SERVER['QUERY_STRING']);
}
//update
date_default_timezone_set('Europe/Helsinki');
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && isset($_POST["update"])) {
  $updateSQL = sprintf("UPDATE course_eur SET `min`=%s, `max`=%s, `date`=%s WHERE id=%s",
                       str_replace(",",".",GetSQLValueString($_POST['min'], "float")),
                       str_replace(",",".",GetSQLValueString($_POST['max'], "float")),
                       GetSQLValueString($_POST['date'], "date"),
                       GetSQLValueString($_POST['id'], "int"));
   

  mysql_select_db($database_ma, $ma);
  $Result1 = mysql_query($updateSQL, $ma) or die(mysql_error());

  $updateGoTo = "course_eur.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
} //end update
// begin insert
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && isset($_POST["insert"])) {
  $updateSQL = sprintf("INSERT INTO course_eur (min, max, `date`) VALUES (%s, %s, %s)",
                       str_replace(",",".",GetSQLValueString($_POST['min'], "float")),
                       str_replace(",",".",GetSQLValueString($_POST['max'], "float")),
                       GetSQLValueString(date("Y-m-d H:i:s"), "date"));
   

  mysql_select_db($database_ma, $ma);
  $Result1 = mysql_query($updateSQL, $ma) or die(mysql_error());

  $updateGoTo = "course_eur.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}
// end insert
//begin delete
if ((isset($_GET['id'])) && ($_GET['id'] != "") && (isset($_GET['delete']))) {
  $deleteSQL = sprintf("DELETE FROM course_eur WHERE id=%s",
                       GetSQLValueString($_GET['id'], "int"));

  mysql_select_db($database_ma, $ma);
  $Result1 = mysql_query($deleteSQL, $ma) or die(mysql_error());

  $deleteGoTo = "course_eur.php";
/*  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }*/
  header(sprintf("Location: %s", $deleteGoTo));
}
//end delete
$maxRows_course_eur = 10;
$pageNum_course_eur = 0;
if (isset($_GET['pageNum_course_eur'])) {
  $pageNum_course_eur = $_GET['pageNum_course_eur'];
}
$startRow_course_eur = $pageNum_course_eur * $maxRows_course_eur;

mysql_select_db($database_ma, $ma);
$query_course_eur = "SELECT course_eur.id, course_eur.`min`, course_eur.`max`, course_eur.`date` FROM course_eur ORDER BY course_eur.`date` desc";
$query_limit_course_eur = sprintf("%s LIMIT %d, %d", $query_course_eur, $startRow_course_eur, $maxRows_course_eur);
$course_eur = mysql_query($query_limit_course_eur, $ma) or die(mysql_error());
$row_course_eur = mysql_fetch_assoc($course_eur);

if (isset($_GET['totalRows_course_eur'])) {
  $totalRows_course_eur = $_GET['totalRows_course_eur'];
} else {
  $all_course_eur = mysql_query($query_course_eur);
  $totalRows_course_eur = mysql_num_rows($all_course_eur);
}
$totalPages_course_eur = ceil($totalRows_course_eur/$maxRows_course_eur)-1;

$queryString_course_eur = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_course_eur") == false && 
        stristr($param, "totalRows_course_eur") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_course_eur = "&" . htmlspecialchars(implode("&", $newParams));
  }
}
$queryString_course_eur = sprintf("&totalRows_course_eur=%d%s", $totalRows_course_eur, $queryString_course_eur);

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
$query_DetailRS1 = sprintf("SELECT course_eur.id, course_eur.`min`, course_eur.`max`, course_eur.`date` FROM course_eur WHERE id = %s", GetSQLValueString($colname_DetailRS1, "int"));
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
      <td><a href="course_eur.php?recordID=<?=$row_course_eur['id']?>"> <?=$row_course_eur['id']?>&nbsp; </a></td>
      <td><?php echo $row_course_eur['min']; ?>&nbsp; </td>
      <td><?php echo $row_course_eur['max']; ?>&nbsp; </td>
      <td><?php echo $row_course_eur['date']; ?>&nbsp; </td><td><a href="course_eur.php?id=<?=$row_course_eur['id']?>&amp;delete">удалить</a></td>
    </tr>
    <?php } while ($row_course_eur = mysql_fetch_assoc($course_eur)); ?>
</table>
<br />
<table border="0">
  <tr>
    <td><?php if ($pageNum_course_eur > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_course_eur=%d%s", $currentPage, 0, $queryString_course_eur); ?>">First</a>
    <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_course_eur > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_course_eur=%d%s", $currentPage, max(0, $pageNum_course_eur - 1), $queryString_course_eur); ?>">Previous</a>
    <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_course_eur < $totalPages_course_eur) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_course_eur=%d%s", $currentPage, min($totalPages_course_eur, $pageNum_course_eur + 1), $queryString_course_eur); ?>">Next</a>
    <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum_course_eur < $totalPages_course_eur) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_course_eur=%d%s", $currentPage, $totalPages_course_eur, $queryString_course_eur); ?>">Last</a>
    <?php } // Show if not last page ?></td>
  </tr>
</table>
Records <?php echo ($startRow_course_eur + 1) ?> to <?php echo min($startRow_course_eur + $maxRows_course_eur, $totalRows_course_eur) ?> of <?php echo $totalRows_course_eur ?>

<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
  <table align="center">
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Id:</td>
      <td><?php echo $row_DetailRS1['id']; ?></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Min:</td>
      <td><input type="text" name="min" id="min" value="<?=$row_DetailRS1['min']?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Max:</td>
      <td><input type="text" name="max" id="max" value="<?=$row_DetailRS1['max']?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Date:</td>
      <td><input name="date" type="text" id="date" value="<?php if ($row_DetailRS1['date']==''){echo date("Y-m-d H:i:s");} else {echo htmlspecialchars($row_DetailRS1['date'], ENT_COMPAT, '');} ?>" size="32" readonly="true" /></td>
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
  <input type="hidden" name="id" id="id" value="<?=$row_DetailRS1['id']?>" />
  
</form>
<p>&nbsp;</p>
<?php mysql_free_result($DetailRS1);
mysql_free_result($course_eur);

?>
<a href="http://finance.i.ua/" target="_blank"><img src="http://f.i.ua/fp3_b15_c2_l0.png" border="0" alt="Курс евро"></a>