<?php 
	require_once("../Connections/ma.php");
	header("Location: ".$siteroot."cabinet.php?clid=".$_REQUEST['clid']."&oid=".$_REQUEST['s_order_id']."&message=success");
	// printing headers
	// печатаем заголовки
	header("Content-Type: text/html; charset=windows-1251");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
	<head>
		<title>
			sms:bank - demo // смс:банк - демонстрация // success.php
		</title>
	</head>
	<body>
		<p>
			The operation has succeeded
		</p>
		<p>
			Операция прошла успешно
		</p>
	</body>
</html>
