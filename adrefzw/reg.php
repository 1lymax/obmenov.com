<?php require_once('../Connections/ma1.php'); ?>
<?php

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = mysqli_real_escape_string($ma, $theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}
?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['doLogout'])) {
  session_destroy();
}
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['username'])) {
  $loginUsername=$_POST['username'];
  $password=$_POST['pass'];
  $MM_fldUserAuthorization = "type";
  $MM_redirectLoginSuccess = "index.php";
  $MM_redirectLoginFailed = "reg.php?fail=1";
  $MM_redirecttoReferrer = true;
   _query('SET character_set_database = cp1251',"");
_query('SET NAMES cp1251',""); 	
  $LoginRS__query=sprintf("SELECT name, pass, type FROM users WHERE name=%s AND pass=%s",
  GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
   
  $LoginRS = _query($LoginRS__query,'');
  $loginFoundUser = $LoginRS->num_rows;
  if ($loginFoundUser) {
    $LoginRS=$LoginRS->fetch_assoc();
    $loginStrGroup  = $LoginRS['type'];
    
    //declare two session variables and assign them
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      

    if (isset($_SESSION['PrevUrl']) && true) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}
require_once($serverroot."adrefzw/top.php");
?><br />

<form action="<?php echo $loginFormAction; ?>" method="POST" name="auth">
Имя: <input name="username" type="text" />
Пароль: <input name="pass" type="password" />
<input name="" type="submit" />
</form>