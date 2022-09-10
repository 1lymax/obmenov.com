<?php
$WM_list['WMZ']['WMR']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=1";
$WM_list['WMR']['WMZ']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=2";
$WM_list['WMZ']['WME']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=3";
$WM_list['WME']['WMZ']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=4";
$WM_list['WME']['WMR']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=5";
$WM_list['WMR']['WME']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=6";
$WM_list['WMZ']['WMU']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=7";
$WM_list['WMU']['WMZ']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=8";
$WM_list['WMR']['WMU']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=9";
$WM_list['WMU']['WMR']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=10";
$WM_list['WMU']['WME']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=11";
$WM_list['WME']['WMU']="https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype=12";

$WM_top['WMZ']['WMR']=2000;
$WM_top['WMR']['WMZ']=66000;
$WM_top['WMZ']['WME']=2000;
$WM_top['WME']['WMZ']=1400;
$WM_top['WME']['WMR']=1400;
$WM_top['WMR']['WME']=66000;
$WM_top['WMZ']['WMU']=5000;
$WM_top['WMU']['WMZ']=17000;
$WM_top['WMR']['WMU']=66000;
$WM_top['WMU']['WMR']=17000;
$WM_top['WMU']['WME']=17000;
$WM_top['WME']['WMU']=1400;


function get_course ($val1,$val2) {
	global $WM_list;
	global $WM_top;
	global $money;
	$allamount1=0;
	$allamount2=0;
	$endamount1=0;
	$endamount2=0;	
	
	$ch = get_wmexch($WM_list[$val1][$val2]);
	if ( substr($ch,0,3) == "CURL" )return;
	if ( substr($ch,0,1)!="<" ) return;
	$xmlres = simplexml_load_string($ch);
	//print_r($xmlres);
	foreach ($xmlres->WMExchnagerQuerys->query as $query) {
		$amount=str_replace(",",".",$query->attributes()->allamountin);
	}
		//echo $amount."<br />";
		
		$xmlres = simplexml_load_string($ch);
		$select="select value from course_cb where `from`='".substr($xmlres->BankRate->attributes()->direction,0,3)."' and
							`to`='".substr($xmlres->BankRate->attributes()->direction,4)."' 
							order by time desc limit 0,1";
		$query1=_query($select,"");
		$row=$query1->fetch_assoc();
		print_r ($row);
		if ( str_replace(",",".",$xmlres->BankRate)!=$row['value'] ) {
			$update="insert into course_cb (`from`, `to`, value) values ("
									."'".substr($xmlres->BankRate->attributes()->direction,0,3)."',"
									."'".substr($xmlres->BankRate->attributes()->direction,4)."',"
									.str_replace(",",".",$xmlres->BankRate).")";
			//$query1=_query($update,"");
		}else{
		}
		
		foreach ($xmlres->WMExchnagerQuerys->query as $query) {
			if ( $amount*0.1 > str_replace(",",".",$query->attributes()->amountin) ) {
				if ( strval($query->attributes()->allamountin) < $WM_top[$val1][$val2] ) {
					$allamount1=$allamount1+str_replace(",",".",$query->attributes()->inoutrate)*
					str_replace(",",".",$query->attributes()->amountin);
					$endamount1 = $endamount1 + str_replace(",",".",$query->attributes()->amountin);
				}
			//	echo $allamount1." - ".$endamount1."<br />";
			}
			//echo $query->attributes()->inoutrate." ".$query->attributes()->amountin."<br />";
		}
	//echo $val1.$val2."=".$allamount1.", ".$endamount1." (".str_replace(",",".",$allamount1)/str_replace(",",".",$endamount1)."<br />";	
		
	$ch = get_wmexch($WM_list[$val2][$val1]);
		if ( substr($ch,0,3) == "CURL" ) {
		echo $ch; return;}
	if ( substr($ch,0,1) =="<" ) {
		$xmlres = simplexml_load_string($ch);
	}else{
		return 0;
	}
	if ( !isset($xmlres->WMExchnagerQuerys->query) ) return 0;
	foreach ($xmlres->WMExchnagerQuerys->query as $query) {
		$amount=str_replace(",",".",$query->attributes()->allamountin);
	}
			//echo $amount."<br />";
	$xmlres = simplexml_load_string($ch);
	//print_r($xmlres);
		foreach ($xmlres->WMExchnagerQuerys->query as $query) {
			if ( $amount*0.1 > str_replace(",",".",$query->attributes()->amountin) ) {
				if ( strval($query->attributes()->allamountin) < $WM_top[$val2][$val1] ) {
					$allamount2=$allamount2+str_replace(",",".",$query->attributes()->inoutrate)*
					str_replace(",",".",$query->attributes()->amountin);
					$endamount2=$endamount2 + str_replace(",",".",$query->attributes()->amountin);
				}
				//echo $allamount2." - ".$endamount2."<br />";
		}
		}
		//echo $val2.$val1."=".$allamount2.", ".$endamount2." (".(1/($allamount2/$endamount2))."<br />";
if ( $endamount1!=0 && $endamount2!=0 ) {
	$_base=round((str_replace(",",".",$allamount1)/str_replace(",",".",$endamount1)+1/(str_replace(",",".",$allamount2)/str_replace(",",".",$endamount2)))/2,16);
	echo $val1.$val2." ".$_base."<br />";
	return $_base;
}else{
	return 0;	
}

	
	
}

function update_course ($val1, $val2)
{
	$updated_rate = get_course($val2, $val1);
	$query = "SELECT rate FROM wmrates WHERE direction='".$val1."/".$val2."' ORDER BY time desc";
	$rates=_query($query, "wmxml.inc 1");
	$rates=$rates->fetch_assoc();
	if ( $rates['rate'] != $updated_rate ) {
		if ( $rates['rate']/1.03 > $updated_rate || $rates['rate']*1.03 < $updated_rate ) { }
		else {
			$updated_rate = ($updated_rate + $rates['rate'])/2 ;
			$insert_SQL="INSERT INTO wmrates (`direction`,`rate`) VALUES ('".$val1."/".$val2."',".$updated_rate.")";
			$insert_Query=_query($insert_SQL,"wm.exch.in ".$val1."->".$val2);	
		}
	}
}


function get_wmexch ($link) {
	$ch=curl_init($link);
	// В выводе CURL http-заголовки не нужны
	curl_setopt($ch, CURLOPT_HEADER, 0);
	// Возвращать результат, а не выводить его в браузер
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	// Коды ответов более 300 не приводят к возврату содержимого страниц
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	// Всегда новое соединение, из кеша не берем
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
	// ставим тайм ауты
	// лучше еще раз послать свежий запрос,
	// чем ждать неопределенное время, а еще хуже получить "устаревшую" информацию
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	//curl_setopt($ch, CURLOPT_CAINFO, "/var/www/webmoney_ma/data/www/obmenov.com/siti/wm.exchanger.cer"); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// Выполняем запрос, ответ помещаем в переменную $result;
	$result=curl_exec($ch);
	if (curl_errno($ch) != 0)
		{
	// записать эту ошибку в лог или сообщить о ней для "разбора полета"
	$error = "CURL: ".curl_error($ch);
	maildebugger($error);
	//echo $error;
	return $error;
	}

	$intReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($intReturnCode != 200)
	{
	// записать эту ошибку в лог или сообщить о ней для "разбора полета"
	$error = "CURL: Ошибка в ответе сервера. Код ответа:".$intReturnCode;
	//echo $error;
	return $error;
	} 
 
	return $result;
	
}
?>