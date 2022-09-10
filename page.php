<?php require_once("Connections/ma.php");
$page['commission']="commission.php";
$page['index']="index.php";
$page['register']="register.php";

require_once(isset($page[$_GET['page']])?$page[$_GET['page']]:"index.php"); ?>