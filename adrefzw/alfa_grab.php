<?php 
$path="/var/www/webmoney_ma/data/www/obmenov.com/adrefzw/alfa.htm";
$sock = fopen ($path, 'r');
if (!$sock){
	echo "Не получилось подключиться к yandex.";
}else{
	$html = '';
	while (!feof($sock)){
		$html .= fgets($sock);
	}
}
fclose ($sock);



preg_match_all('~<tr id="a1">(.*)</tr>~', $html, $data);
print_r ($data);



?>