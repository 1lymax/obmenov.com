<?php require_once('../Connections/ma.php');
		require_once('../function.php');

//$searchvalues['search1ref']='';
//$searchvalues['search1scrname']='';
//$searchvalues['search1ip']='';
//$searchvalues['search1time']='';


$currentPage = $_SERVER["PHP_SELF"];
$searcharr = array();
$searchvalues = array();

reset($_GET);
$wordlen=strlen("search");
while (list($key, $val) = each($_GET)) {
	
	$table1="referer";
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



$query_referer = "SELECT referer.id, referer.`ref`, referer.scrname, referer.clid, referer.partnerid, referer.ip, referer.`time` FROM referer WHERE 1=1 ";
while (list($key, $val) = each($searcharr)) {
	$query_referer=$query_referer.$val;
	
}
$query_referer=$query_referer." order by time desc";

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
<title>Обменов.ком :: Переходы</title>
<link href="../wm.css" rel="stylesheet" type="text/css"> 
<script type="text/javascript" src="../fun.js"></script>
</head>
<body>
<table class="tableborder" cellpadding="5" cellspacing="0">
  <tr>
    <td>id</td>
  
    <td>Реферер</td>
    <td>Скрипт</td>
    <td>Клиент</td>
    <td>Партнер</td>
    <td>IP-адрес</td>
    <td>Время</td>
  </tr>
 <form action="referer.php" method="get">
  <tr>
    <td></td>
  
    <td><input name="search1ref" size="13" value="<?php //$searchvalues['search1ref']?>" /></td>
    <td><input name="search1scrname" size="13" value="<?php //$searchvalues['search1scrname']?>" /></td>
    <td></td>
    <td></td>
    <td><input name="search1ip" size="10" value="<?php //$searchvalues['search1ip']?>" /></td>
    <td><input name="search1time" size="10" value="<?php //$searchvalues['search1time']?>" /></td>
  </tr>
    <tr>
    <td></td>
  
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td><input type="submit" value="Поиск" /></td>
  </tr>
  </form>
  <?php do { 
  $select="SELECT id, nikname FROM clients WHERE clid='".$row_referer['clid']."'";
  //$query=_query($select,"ad/referer.php 6");
  //$row_client=$query->fetch_assoc();
  ?>
    <tr>
      <td><?php echo $row_referer['id']; ?></td>
      <td><?php echo $row_referer['ref']; ?></td>
      <td><?php echo $row_referer['scrname']; ?></td>
      <td><?php //echo $row_client['id']." ".$row_client['nikname']; ?></td>
      <td><?php echo $row_referer['partnerid']; ?></td>
      <td><a href="https://www.nic.ru/whois/?query=<?php echo $row_referer['ip']; ?>"><?php echo $row_referer['ip']; ?></a></td>
      <td><?php echo $row_referer['time']; ?></td>
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
