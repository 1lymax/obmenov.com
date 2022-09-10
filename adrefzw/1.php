<?php
require_once('../Connections/ma.php');
//Секретный ключ интернет-магазина

$key = $w1_key;

$fields; // Добавление полей формы в ассоциативный массив

$fields{"WMI_MERCHANT_ID"}    = "150449552204";
$fields{"WMI_PAYMENT_AMOUNT"} = "100.00";
$fields{"WMI_CURRENCY_ID"}    = "980";
$fields{"WMI_PAYMENT_NO"}     = "12345-001";
$fields{"WMI_DESCRIPTION"}    = "Payment for order #12345-001 in MYSHOP.com";
$fields{"WMI_EXPIRED_DATE"}   = "2019-12-31T23:59:59";
$fields{"WMI_SUCCESS_URL"}    = "https://myshop.com/w1/success.php";
$fields{"WMI_FAIL_URL"}       = "https://myshop.com/w1/fail.php";
$fields{"WMI_AUTO_ACCEPT"}    = "1";
$fields{"MyShopParam1"}       = "Value1"; // Дополнительные параметры
$fields{"MyShopParam2"}       = "Value2"; // интернет-магазина тоже участвуют
$fields{"MyShopParam3"}       = "Value3"; // при формировании подписи!

// Формирование сообщения, путем объединения значений формы, 
// отсортированных по именам ключей в порядке возрастания.

ksort($fields, SORT_STRING);
$fieldValues = "";

foreach($fields as $name => $val) 
{
   $fieldValues .= iconv("utf-8", "windows-1251", $val);
}

// Формирование значения параметра WMI_SIGNATURE, путем 
// вычисления отпечатка, сформированного выше сообщения, 
// по алгоритму MD5 и представление его в Base64

$signature = base64_encode(pack("H*", md5($fieldValues . $key)));

//Добавление параметра WMI_SIGNATURE в словарь параметров формы

$fields{"WMI_SIGNATURE"} = $signature;

// Формирование HTML-кода платежной формы

print "<form action=\"https://merchant.w1.ru/checkout/default.aspx\" method=\"POST\">";

foreach($fields as $key => $val)
{
    print "$key: <input type=\"text\" name=\"$key\" value=\"$val\"/>";
}

print "<input type=\"submit\"/></form>";
?>
