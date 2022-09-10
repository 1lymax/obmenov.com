<?php
require_once('../Connections/ma.php');
// ��������� ���� ��������-�������� (������������� � ��������)

$skey = $w1_key;

// �������, ������� ���������� ��������� � ������ �����

function print_answer($result, $description)
{
  print "WMI_RESULT=" . strtoupper($result) . "&";
  print "WMI_DESCRIPTION=" .urlencode($description);
  exit();
}

// �������� ������� ����������� ���������� � POST-�������

if (!isset($_POST["WMI_SIGNATURE"]))
  print_answer("Retry", "����������� �������� WMI_SIGNATURE");

if (!isset($_POST["WMI_PAYMENT_NO"]))
  print_answer("Retry", "����������� �������� WMI_PAYMENT_NO");

if (!isset($_POST["WMI_ORDER_STATE"]))
  print_answer("Retry", "����������� �������� WMI_ORDER_STATE");

// ���������� ���� ���������� POST-�������, ����� WMI_SIGNATURE

foreach($_POST as $name => $value)
{
  if ($name !== "WMI_SIGNATURE") $params[$name] = $value;
}

// ���������� ������� �� ������ ������ � ������� �����������
// � ������������ ���������, ����� ����������� �������� �����

ksort($params, SORT_STRING); $values = "";

foreach($params as $name => $value)
{
  $values .= $params[$name];
}

// ������������ ������� ��� ��������� �� � ���������� WMI_SIGNATURE

$signature = base64_encode(pack("H*", md5($values . $skey)));

//��������� ���������� ������� � �������� W1

if ($signature == $_POST["WMI_SIGNATURE"])
{
  if (strtoupper($_POST["WMI_ORDER_STATE"]) == "ACCEPTED")
  {
    // TODO: �������� �����, ��� ����������� � ������� ����� ��������

    print_answer("Ok", "����� #" . $_POST["WMI_PAYMENT_NO"] . " �������!");
  }
  else if (strtoupper($_POST["WMI_ORDER_STATE"]) == "PROCESSING")
  {
    // TODO: �������� �����, ��� ����������� � ������� ����� ��������

    print_answer("Ok", "����� #" . $_POST["WMI_PAYMENT_NO"] . " �������!");

    // ������ �������� ���������, ���� � ��������� ����� WMI_AUTO_ACCEPT=0.
    // � ���� ������ ��������-������� ����� ������� ������ ��� �������� ��.
  }
  else if (strtoupper($_POST["WMI_ORDER_STATE"]) == "REJECTED")
  {
    // TODO: �������� �����, ��� ������������� � ������� ����� ��������

    print_answer("Ok", "����� #" . $_POST["WMI_PAYMENT_NO"] . " �������!");
  }
  else
  {
	// ��������� ���-�� ��������, ������ ����������� ��������� ������

    print_answer("Retry", "�������� ��������� ". $_POST["WMI_ORDER_STATE"]);
  }
}
else
{
  // ������� �� ���������, �������� �� �������� ��������� ��������-��������

  print_answer("Retry", "�������� ������� " . $_POST["WMI_SIGNATURE"]);
}

?>
