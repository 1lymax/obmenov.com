<?php
	require_once("../Connections/ma.php");
	require_once($serverroot."siti/sms_config.php");
print_r($_POST);
	$amount	   = $_REQUEST['s_amount'];			// transaction sum	сумма транзакции
	$amount=4.53;
	// printing the form
	// печатаем форму
	print_form($purse, $order_id, $amount, $clear_amount, $description, $secret_code, $submit);
	// the function returns an MD5 of parameters passed
	// функция возвращает MD5 переданных ей параметров
	function ref_sign() {
		$params = func_get_args();
		$prehash = implode("::", $params);
		return md5($prehash);
	}
	
	// the function prints a request form
	// функция печатает форму запроса
	function print_form($purse, $order_id, $amount, $clear_amount, $description, $secret_code, $submit) {
		// making signature
		// создаем подпись
		$sign = ref_sign($purse, $order_id, $amount, $clear_amount, $description, $secret_code);
		
		// printing the form
		// печатаем форму
		echo <<<Form
		<form action="http://bank.smscoin.com/bank/" method="POST">
			<p>
				<input name="s_purse" type="hidden" value="$purse" />
				<input name="s_order_id" type="hidden" value="$order_id" />
				<input name="s_amount" type="hidden" value="$amount" />
				<input name="s_clear_amount" type="hidden" value="$clear_amount" />
				<input name="s_description" type="hidden" value="$description" />
				<input name="s_sign" type="hidden" value="$sign" />
				<input type="submit" value="$submit" />
			</p>
		</form>

Form;
	}
	
?>
<form action="smsresult12.php" method="post">
<input type="submit" />
<?php
$post=array('s_purse' => 10167,
   's_order_id' => 60487,
   's_amount' => 0.01,
   's_clear_amount' => 1,
   's_inv' => '7787094',
   's_sign' => '09152555f741595de9da671f170d7311',
   's_phone' => '380682587483',
   's_sign_v2' => 'c3352b8b4e99e5ffe57b6ced9d567f66',
   'clid' => '3lucf7b3goo6fkj06ammb9m7h7');
   
			reset($_POST);
			while (list($key, $val) = each($post)) {
				echo "<input type='hidden' name='$key' value='$val'>";
			}
			echo "</form></body>";
			?>