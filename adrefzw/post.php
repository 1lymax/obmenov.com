<?php require_once('../Connections/ma.php');
		require_once('../function.php');

$searchvalues['search1post']='';
$searchvalues['search1scrname']='';
$searchvalues['search1clid']='';
$searchvalues['search1date']='';


$currentPage = $_SERVER["PHP_SELF"];
$searcharr = array();
$searchvalues = array();

reset($_GET);
$wordlen=strlen("search");
while (list($key, $val) = each($_GET)) {
	
	$table1="post";
	$table2="";
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

$maxRows_referer = 100;
$pageNum_referer = 0;
if (isset($_GET['page'])) {
  $pageNum_referer = $_GET['page'];
}
$startRow_referer = $pageNum_referer * $maxRows_referer;



$query_referer = "SELECT * FROM post WHERE 1=1 ";
while (list($key, $val) = each($searcharr)) {
	$query_referer=$query_referer.$val;
	
}
$query_referer=$query_referer." order by date desc";

$query_limit_referer = sprintf("%s LIMIT %d, %d", $query_referer, $startRow_referer, $maxRows_referer);
$referer = mysql_query($query_limit_referer, $ma) or die(mysql_error());
$row_referer = mysql_fetch_assoc($referer);

if (isset($_GET['total'])) {
  $totalRows_referer = $_GET['total'];
} else {
  $all_referer = mysql_query($query_referer);
  $totalRows_referer = mysql_num_rows($all_referer);
}
$totalPages_referer = ceil($totalRows_referer/$maxRows_referer)-1;

$queryString_referer = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "page") == false && 
        stristr($param, "total") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_referer = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_referer = sprintf("&total=%d%s", $totalRows_referer, $queryString_referer);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Обменов.ком :: Посты</title>
<link href="../wm.css" rel="stylesheet" type="text/css"> 
<script type="text/javascript" src="../fun.js"></script>
</head>
<body>
<table class="tableborder" cellpadding="5" cellspacing="0">
  <tr>
    <td>время</td>
    <td>скрипт</td>
    <td>Клиент</td>

    <td>Массив</td>
  </tr>
 <form action="post.php" method="get">
  <tr>
    <td><input name="search1date" size="10" value="<?=$searchvalues['search1date']?>" /></td>
    <td><input name="search1scrname" size="13" value="<?=$searchvalues['search1scrname']?>" /></td>
    <td><input name="search1clid" size="10" value="<?=$searchvalues['search1clid']?>" /></td>
    <td><input name="search1post" size="13" value="<?=$searchvalues['search1post']?>" /></td>

  </tr>
    <tr>
    <td></td>
  
    <td></td>
    <td></td>
    <td><input type="submit" value="Поиск" /></td>
  </tr>
  </form>
  <?php do { 
  ?>
    <tr>
      <td width="120" valign="top"><?=$row_referer['date']; ?></td>
      <td width="120" valign="top"><?=$row_referer['scrname']; ?></td>
      <td width="120" valign="top"><?=$row_referer['clid']; ?></td>

      <td><pre><?=$row_referer['post']?></pre></td>
    </tr>
    <?php } while ($row_referer = mysql_fetch_assoc($referer)); ?>
</table>
<table border="0">
  <tr>
    <td><?php if ($pageNum_referer > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, 0, $queryString_referer); ?>">First</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_referer > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, max(0, $pageNum_referer - 1), $queryString_referer); ?>">Previous</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_referer < $totalPages_referer) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, min($totalPages_referer, $pageNum_referer + 1), $queryString_referer); ?>">Next</a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum_referer < $totalPages_referer) { // Show if not last page ?>
        <a href="<?php printf("%s?page=%d%s", $currentPage, $totalPages_referer, $queryString_referer); ?>">Last</a>
        <?php } // Show if not last page ?></td>
  </tr>
</table>
</body>
</html>
<?php
mysql_free_result($referer);
?>
