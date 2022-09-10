<?
/*
*  класс для работы с платёжным API Приват24
*/
	
class p24api {
	private $merid;
	private $pass;
	private $apiurl;
	private $errmess;
	

	
	function __construct($mid, $password, $url){ // конструктор
		$this->merid = $mid; 
		$this->pass = $password; 
		$this->apiurl = $url; 
	}

	function sendPrpRequest() { // отправка запроса prp, возвращает xml-ответ
		$data = '<oper>prp</oper>';
		return $this->sendRequest($data);
	} 
	function sendBalanceRequest() { // отправка запроса prp, возвращает xml-ответ
		$data = '<oper>cmt</oper>';
		$data .= '<wait>0</wait>';
		$data .= '<test>0</test>';
		$data .= '<payment></payment>'; 
		return $this->sendRequest($data);
	}
	/*
	* отправка запроса на платёж 
	* $payments - массив ассоциативных массивов реквизитов платежей
	* $wait - время задержки платежа в секундах
	* $isTest - тестовый ли платёж
	* возвращает такой же массив только с полями результата
	* или xml, если запрос был информационным
	*/
	function sendCmtRequest($payments, $wait, $isTest) {
		$data = '<oper>cmt</oper>';
		$data .= '<wait>'.$wait.'</wait>';
		$data .= '<test>'.(($isTest) ? 1 : 0).'</test>';
		foreach ($payments as $pay) {
			$data .= isset($pay['id'])?('<payment id="'.$pay['id'].'">'):"<payment>";
			foreach ($pay as $k=>$v) {
				if ($k=='id' || $k=='debet' || $k=='credit' || empty($v)) continue;
				$data .= '<prop name="'.$k.'" value="'.rawurlencode($v).'" />';
			}
			$data .= '</payment>';
		}
		//echo $data;
		$resp = $this->sendRequest($data);
		if ( is_array($resp) ) return $resp;
		if (strpos($resp, "<info>")===false) { // запрос был пакетом платежей
			$dom = new DomDocument('1.0','windows-1251');
			if ( strlen($resp)==0 ) return;
			$dom->loadXML($resp);
			$xpath = new DOMXPath($dom);
			$q_pays = '//response/data/payment';
			$pays = $xpath->query($q_pays);
			if ($pays->length == 0) {
				$q_err = '//response/data/error';
				$err = $xpath->query($q_err);
				if ($err->length == 0) $this->errmess = "response: ".$resp;
				else {
					$this->errmess = $err->item(0)->getAttribute('message');
				}
				return false;
			}
			$rez = array();
			for ($i=0;$i<$pays->length;$i++) {
				$pay = $pays->item($i);
				$payrez = array();
				$payrez['id'] = $pay->getAttribute('id');
				$payrez['state'] = $pay->getAttribute('state');
				$payrez['message'] = $pay->getAttribute('message');
				$payrez['ref'] = $pay->getAttribute('ref');
				$payrez['amt'] = $pay->getAttribute('amt');
				$payrez['ccy'] = $pay->getAttribute('ccy');
				$payrez['comis'] = $pay->getAttribute('comis');
				$payrez['code'] = $pay->getAttribute('code');
				$rez[] = $payrez;
			}
			return $rez;
		}
		else { // запрос был информационным
			$start = strpos($resp, '<info>')+strlen('<info>');
			$end = strpos($resp, '</info>');
			return substr($resp, $start, ($end-$start));
		}
	}
	
	function getErrMessage() {
		return $this->errmess;
	}
	
	function sendRequest($data) {
		$str = '<?xml version="1.0" encoding="windows-1251"?><request version="1.0"><merchant>';
		$str .= '<id>'.$this->merid.'</id>';
		$str .= '<signature>'.$this->calcSignature($data).'</signature>';
		$str .= '</merchant><data>'.$data.'</data></request>';
		//echo $str;
		return $this->msoap($str);
	}
	
	function msoap($xml) { // транспортная ф-ция

		$header = array();
		$header[] = "Content-Type: text/xml";
		$header[] = "\r\n"; 
		//maildebugger($xml);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->apiurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 25);
//		curl_setopt($ch, CURLOPT_CAINFO, $GLOBALS['serverroot']."siti/privatbank.ua.crt");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); 
		$rez = curl_exec($ch);
		if (curl_errno($ch) != 0)
		{
			$resp[0]['state']=0;
			$resp[0]['message']=mysqli_real_escape_string($GLOBALS['ma'],"P24 error CODE:".curl_error($ch)." INFO:".curl_getinfo($ch, CURLINFO_HTTP_CODE));
			curl_close($ch);
			return $resp;
		}
		curl_close($ch);
		$select="insert into xml (iface, query, answer) values (".
									"'".$this->apiurl."',".							
									"'".$xml."',".
									"'".$rez."')";
		$insert=_query2($select,"");
									
		//maildebugger($xml." ".$rez);
		return $rez;//$rez;
	}
	
	function calcSignature($data) { // расчёт сигнатуры
		return sha1(md5($data.$this->pass));
	}
	
}


?>