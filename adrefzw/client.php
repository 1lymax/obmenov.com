<?php 
require_once("../Connections/ma.php");
require_once("../function.php");

if ( isset($_POST['client_update']) && $_POST['client_update'] == 'ok' ) {
			//echo intval($_POST['purse']);
			$name = isset ($_POST['name']) ? $_POST['name'] : "";
			$nikname = isset ($_POST['nikname']) ? $_POST['nikname'] : "";
			$email = isset ($_POST['email']) ? $_POST['email'] : "";
			$wmid = isset ($_POST['wmid']) ? $_POST['wmid'] : "";
			$purse_z = isset ($_POST['purse_z']) ? $_POST['purse_z'] : "";
			$purse_r = isset ($_POST['purse_r']) ? $_POST['purse_r'] : "";
			$purse_e = isset ($_POST['purse_e']) ? $_POST['purse_e'] : "";
			$purse_u = isset ($_POST['purse_u']) ? $_POST['purse_u'] : "";
			$bank_name = isset ($_POST['bank_name']) ? $_POST['bank_name'] : "";
			$account = isset ($_POST['account']) ? $_POST['account'] : "";
			$phone = isset ($_POST['phone']) ? $_POST['phone'] : "";
			$passport = isset ($_POST['passport']) ? $_POST['passport'] : "";
			$comment = isset ($_POST['comment']) ? $_POST['comment'] : "";
			$activated = isset ($_POST['activated']) ? $_POST['activated'] : "";
			$update="UPDATE clients SET name='".$name."',
											nikname='".$nikname."', 
											email='".$email."',
											wmid='".$wmid."',
											phone='".$phone."',
											email='".$email."',
											purse_e='".$purse_e."',
											purse_r='".$purse_r."',
											purse_u='".$purse_u."',
											purse_z='".$purse_z."',
											passport='".$passport."',
											comment='".$comment."',
											activated='".$activated."',
											account='".$account."'
											WHERE clients.id=".$_POST['id'];
			
			$updateSQL=_query($update, "ad update orders 1");
}

// SELECT * FROM `clients` WHERE email='' and nikname='' and purse_z='' and purse_e=='' and purse_u='' and purse_u=='' and partnerid=0
if ( isset($_GET['search1nikname']) ) { 
	$nikname=$_GET['search1nikname'];
}else{ 
	$nikname='';
}
// переход на сайт с правами пользователя
if ( isset($_GET['clid']) && isset($_GET['action']) && $_GET['action']=="cabinet" ) {
	$select="select nikname from clients where clid='".$_GET['clid']."'";
	$query=_query($select, "adrefzw");
	$client=$query->fetch_assoc();
	$_SESSION['AuthUsername']=$client['nikname'];
	$_SESSION['authorized']=1;
	if ( isset($_GET['action']) && $_GET['action']=="cabinet" ) {
		header("Location: http://obmenov.com/cabinet.php?history");
	}
}

	// чистка референса
	if ( isset ($_GET['refdel']) ) {
	$refquery=_query("SELECT referer.id, ref, scrname, agent, clid, partnerid, COUNT( referer.clid ) AS total
FROM referer
GROUP BY referer.clid", "ad/client.php 1");
	$i=0;
	//$rr=mysql_fetch_assoc($clquery);
	//print_r($rr);
	while ($refrow=mysql_fetch_assoc($refquery) ) {
		if ($refrow['total']==1)	{
			$i=$i+1;
			echo $refrow['id']." ".$refrow['ref']." ".$refrow['agent']." -----".$refrow['partnerid']."<br />";
			_query("DELETE FROM referer WHERE id='".$refrow['id']."';","ad/cliient.php 2");
			
		}
	
	}
 		echo "По клиентам удалено ".$i." записей";
	}
	// чистка референса
	
$searchvalues['search1id']='';
$searchvalues['search1name']='';
$searchvalues['search1nikname']='';
$searchvalues['search1wmid']='';
$searchvalues['search1purse_z']='';
$searchvalues['search2purse_u']='';
$searchvalues['search1purse_e']='';
$searchvalues['search1phone']='';
$searchvalues['search1email']='';
$searchvalues['search1clid']='';
$searchvalues['search1activated']='';
$searcharr = array();
$table = array();
reset($_POST);
$wordlen=strlen("search");	
$table[1]="clients";

while (list($key, $val) = each($_GET)) {
	$badcondition="";	

	//$table3="clients";
	if ( substr($key,0,$wordlen)=="search") {
	
			$searcharr[$key]=" AND ".$table[substr($key,$wordlen,1)].".".substr($key,$wordlen+1,strlen($key)-$wordlen)." LIKE '%".$val."%' ";
			$searchvalues[$key]=$val;
	}
	
}
$currentPage = $_SERVER["PHP_SELF"];

$max = 30;
$page = 0;
if (isset($_GET['page'])) {
  $page = $_GET['page'];
}
$start = $page * $max;
$query = "SELECT * from clients where 1 ";//.$badcondition;
			
while (list($key, $val) = each($searcharr)) {
	$query=$query.$val;
}
//echo "1 ".time()."<br />";
if ( count($searcharr)==0 ) {
	$query= $query." and 1=2 ";
}
$query = $query." ORDER BY clients.nikname desc";
$query_limit = sprintf("%s LIMIT %d, %d", $query, $start, $max);
$clients = _query($query_limit, "client.php 1");
//echo "2 ".time()."<br />";
$total = 300;
$totalPages = ceil($total/$max)-1;

$queryString = "";
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
    $queryString = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString = sprintf("&total=%d%s", $total, $queryString);	
	

 ?>
<html><head>
<link href="../wm.css" rel="stylesheet" type="text/css" />
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251"></head>
<body>
<?php require_once("top.php");?> 
<form action="client.php" method="get">
<select name="search1nikname">
<?php
$select="select nikname from clients where nikname!='' order by nikname asc"; 
$query=_query($select, "detal.php 6");
while ($row_client=$query->fetch_assoc()) {
	if ( $nikname==$row_client['nikname'] ) {
		$selected="selected";
	}else{
		$selected='';
	}
	
?>

<option <?=$selected;?> value="<?=$row_client['nikname']?>"><?=$row_client['nikname']?></option> 

<?php } ?>
</select>
<input type="submit" value="Фильтр">    
</form>
<?php
  	while ($row = mysql_fetch_assoc($clients)) { 
?>

<form name="<?php echo $row['id'];?>" action="client.php?<?php echo $queryString;?>" method="post">
    <input type="hidden" name="id" value="<?php echo $row['id'];?>" />
	<table><tr><td valign="top">
    <table align="left">
      <tr><td width="60">Логин</td><td align="right" width="170"> <input name="nikname" value="<?=$row['nikname'];?>" 
      				size="17" style="text-align:right" /></td></tr>
      <tr><td width="100">Фамилия</td><td align="right"> <input name="name" value="<?=$row['fname'];?>" 
      				size="30" style="text-align:right" /></td></tr>
      <tr><td width="100">Имя</td><td align="right"> <input name="name" value="<?=$row['iname'];?>" 
      				size="30" style="text-align:right" /></td></tr>
      <tr><td width="100">e-mail</td><td align="right"> <input name="email" value="<?=$row['email'];?>" 
      				size="30" style="text-align:right" /></td></tr> 
      <tr><td width="100">Телефон</td><td align="right"> <input name="phone" value="<?=$row['phone'];?>" 
      				size="17" style="text-align:right" /></td></tr>  
      <tr><td width="100">WMID</td><td align="right"> <input name="wmid" value="<?=$row['wmid'];?>" 
      				size="17" style="text-align:right" /></td></tr>
      <tr><td width="100">Кошелек Z</td><td align="right"> <input name="purse_z" value="<?=$row['purse_z'];?>" 
      				size="17" style="text-align:right" /></td></tr>
      <tr><td width="100">Кошелек U</td><td align="right"> <input name="purse_u" value="<?=$row['purse_u'];?>" 
      				size="17" style="text-align:right" /></td></tr>
      <tr><td width="100">Кошелек R</td><td align="right"> <input name="purse_r" value="<?=$row['purse_r'];?>" 
      				size="17" style="text-align:right" /></td></tr>
      <tr><td width="100">Кошелек E</td><td align="right"> <input name="purse_e" value="<?=$row['purse_e'];?>" 
      				size="17" style="text-align:right" /></td></tr> 
      <tr><td width="100">Активация</td><td align="right"> <input type="checkbox" name="activated" value="1" 
	  <?=$row['activated']==1 ? "checked='checked'" : "";?> 
      				size="17" style="text-align:right" /></td></tr>                   
      <tr><td width="100">Счет</td><td align="right" width="170"> <input name="account" value="<?=$row['account'];?>" 
      				size="25" style="text-align:right" /></td></tr>
      <tr><td width="100">Банк.</td><td align="right"> <input name="bank_name" value="<?=$row['bank_name'];?>" 
      				size="25" style="text-align:right" /></td></tr>
      <tr><td width="100" valign="top">Паспорт</td><td align="right"><input name="passport" value="<?=$row['passport'];?>" 
      				size="25" style="text-align:right" /></td></tr>   
      <tr><td width="100" valign="top">Комментарий</td><td align="right"> <textarea name="comment" cols="20" rows="4"><?=$row['comment'];?></textarea></td></tr>
      <tr><td width="100" colspan="2"><?=$row['id']?> - <?=$row['clid']?>&nbsp;</td></tr> 
      <tr><td width="100" colspan="2"><input type="submit" value="Обновить" /></td></tr> 
	</table>
	</td>
    <td width="20"></td>
    <td>
    	<table>
        <tr><td>Действия:</td></tr>
        <tr><td><a href="<?=$siteroot?>adrefzw/orders.php?search1clid=<?=$row['clid']?>">Заявки</a></td></tr>
        <tr><td><a href="<?=$siteroot?>adrefzw/referer.php?search1clid=<?=$row['clid']?>">Переходы</a></td></tr>
        <tr><td><a href="<?=$siteroot?>adrefzw/post.php?search1clid=<?=$row['clid']?>">Посты</a></td></tr>
        <tr><td><a href="<?=$siteroot?>adrefzw/auth.php?search1clid=<?=$row['clid']?>">Аутентификация</a></td></tr>
        <tr><td><a href="<?=$siteroot?>adrefzw/detail.php?id=<?=$row['id']?>">Тарифы по клиенту</a></td></tr>
        <tr><td><a href="<?=$siteroot?>adrefzw/client.php?action=cabinet&clid=<?=$row['clid']?>">Войти на сайт как клиент-></a></td></tr>
        
        </table>
    
    </td>
    </tr>
    </table>
    <input name="client_update" value="ok" type="hidden" />
    
</form>


<?php } ?>

<?php //<a href="<?=$_SERVER['PHP_SELF']."?cldel"">Чистка клиентов</a> ?>