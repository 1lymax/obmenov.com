<?php
### измените приведенные здесь значения на свои, если это необходимо ###
print_r($_POST);
define("SERVICE", 10167); // sms:bank id		идентификатор смс:банка
define("URL", "http://service.smscoin.com/json/bank/".SERVICE."/"); 
$purse		= SERVICE;			  

// service secret code
// секретный код сервиса
$secret_code = "sdkfjhvbdkfhbveirugwaesv";

// initializing variables
// инициализируем переменные
$order_id	 = 1234;		   // operation id	   идентификатор операции
$clear_amount = 0;			  // billing algorithm  алгоритм подсчета стоимости
$description  = "demo payment"; // operation desc	 описание операции
$submit	   = "Заплатить";	// submit label	   надпись на кнопке submit

?>
