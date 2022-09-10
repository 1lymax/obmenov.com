<?php

	# ��������� �����
	define("DOC_ENCODING", "windows-1251");

	# ���������� ����������, ���������� �� ����������
	# �������� �� ������ � ���� �������
	include_once("wmxi.php");

	# ������ ������ ������ WMXI. ������������ ���������:
	# - ���� � �����������, ������������� ��� ������ �� ����� � �������� ���
	# - ���������, ������������ �� �����. �� ��������� ������������ UTF-8
	$wmxi = new WMXI('/var/www/webmoney_ma/data/etc/WebMoneyCA.crt', DOC_ENCODING);

	# ������������� � ������� ��������� ����� ������
	# �� Webmoney Keeper Classic. ������������ ���������:
	# - WMID - ������������� ������������
	# - ������ ������������ �� ��������� ����� ����� ������
	# - ���� � ��������� ����� ����� ������ �������� 164 �����
	#   ��� �������� ���������� ����� �����
	$wmxi->Classic("418941129503", "ghjvsdrfvjpujd", "/var/www/webmoney_ma/data/etc/418941129503.kwm");
	# $kwm = file_get_contents("../keys/000000000000.kwm");
	#$wmxi->Classic("000000000000", "password", $kwm);

	# http://www.webmoney.ru/rus/developers/interfaces/xml/xml_php/index.shtml
	# ������ "������ � ������������� WM Keeper Light (X.509)"
	# ������������� � ������� �����������
	# �� Webmoney Keeper Lite. ������������ ���������:
	# - ���� � ����� ���������� �����
	# - ���� � ����� �����������
	# - ������ �� ���������� �����
	# $wmxi->Lite("keyfile.key", "keyfile.cer", "pass");

	# ���������� ������ ������ ������� � ������ ������
	include_once("wmxiparser.php");
	$parser = new WMXIParser();

?>