<?php
require_once('../Connections/ma.php');
// Секретный ключ интернет-магазина (настраивается в кабинете)

$skey = $w1_key;

// Функция, которая возвращает результат в Единую кассу

function print_answer($result, $description)
{
  print "WMI_RESULT=" . strtoupper($result) . "&";
  print "WMI_DESCRIPTION=" .urlencode($description);
  exit();
}

// Проверка наличия необходимых параметров в POST-запросе

if (!isset($_POST["WMI_SIGNATURE"]))
  print_answer("Retry", "Отсутствует параметр WMI_SIGNATURE");

if (!isset($_POST["WMI_PAYMENT_NO"]))
  print_answer("Retry", "Отсутствует параметр WMI_PAYMENT_NO");

if (!isset($_POST["WMI_ORDER_STATE"]))
  print_answer("Retry", "Отсутствует параметр WMI_ORDER_STATE");

// Извлечение всех параметров POST-запроса, кроме WMI_SIGNATURE

foreach($_POST as $name => $value)
{
  if ($name !== "WMI_SIGNATURE") $params[$name] = $value;
}

// Сортировка массива по именам ключей в порядке возрастания
// и формирование сообщения, путем объединения значений формы

ksort($params, SORT_STRING); $values = "";

foreach($params as $name => $value)
{
  $values .= $params[$name];
}

// Формирование подписи для сравнения ее с параметром WMI_SIGNATURE

$signature = base64_encode(pack("H*", md5($values . $skey)));

//Сравнение полученной подписи с подписью W1

if ($signature == $_POST["WMI_SIGNATURE"])
{
  if (strtoupper($_POST["WMI_ORDER_STATE"]) == "ACCEPTED")
  {
    // TODO: Пометить заказ, как «Оплаченный» в системе учета магазина

    print_answer("Ok", "Заказ #" . $_POST["WMI_PAYMENT_NO"] . " оплачен!");
  }
  else if (strtoupper($_POST["WMI_ORDER_STATE"]) == "PROCESSING")
  {
    // TODO: Пометить заказ, как «Оплаченный» в системе учета магазина

    print_answer("Ok", "Заказ #" . $_POST["WMI_PAYMENT_NO"] . " оплачен!");

    // Данная ситуация возникает, если в платежной форме WMI_AUTO_ACCEPT=0.
    // В этом случае интернет-магазин может принять оплату или отменить ее.
  }
  else if (strtoupper($_POST["WMI_ORDER_STATE"]) == "REJECTED")
  {
    // TODO: Пометить заказ, как «Неоплаченный» в системе учета магазина

    print_answer("Ok", "Заказ #" . $_POST["WMI_PAYMENT_NO"] . " отменен!");
  }
  else
  {
	// Случилось что-то странное, пришло неизвестное состояние заказа

    print_answer("Retry", "Неверное состояние ". $_POST["WMI_ORDER_STATE"]);
  }
}
else
{
  // Подпись не совпадает, возможно вы поменяли настройки интернет-магазина

  print_answer("Retry", "Неверная подпись " . $_POST["WMI_SIGNATURE"]);
}

?>
