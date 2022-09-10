<?PHP
/**
* @desc lrWorker - класс для работы с электронной платежной системой Liberty Reserve
* Возможности:
* - получение имени аккаунта по номеру
* - получение баланса аккаунта
* - получение истории операций аккаунта
* - проведение транзакции по переводу средств
* для использования класса нужно:
* - зарегистрировать аккаунт
* - в аккаунте, в настройках Merchant Tools создать API и настроить его должным образом
* класс работает по следующим параметрам:
* - id аккаунта (например U34256781)
* - id интерфейса API
* - ключевое слово для API интерфейса
*/
Class lrWorker{
	protected $URL = 'https://api2.libertyreserve.com/xml/';
    public $Error = '';
    public $TestMode = false;
	private $Account='';
	private $APIName='';
	private $SecWord='';
    /**
    * @desc lrWorker - object constructor
    * @param string - LR account number
    * @param string - LR API name
    * @param string - Security word for LR API
    * @param boolean - test mode
    */	
	function lrWorker ($id) {
		$select="select * from merch where work=1 and id=".$id;
		$query=_query($select,"");
		$row=$query->fetch_assoc();
		$this->Account=$row['merchant_id'];
		$this->APIName=$row['merchant2'];
		$this->SecWord=$row['merchant'];
		
		
		
	}
  
    /**
    * @desc проверка валидности (правильного написания) аккаунта
    */
    public function isValidAccountNumber($acct) {
        return ereg("^(U|X)[0-9]{1,}$", $acct);
    }

    /**
    * @desc создание контрольного кода
    */
    private function createAuthToken($str=''){
        $datePart = gmdate("Ymd");
        $timePart = gmdate("H");    
        $authString = $this->SecWord.":".$str.$datePart.":".$timePart;
        $sha256 = hash('sha256', $authString);
		print_r($authString);
        return strtoupper($sha256);
    }    

    /** 
    * @desc xmlHttpsReq - https query
    * @param string - interface URL (ex: "balance.aspx")
    * @param string - XML request as string
    * @return string|false - Result CURL excecution https-query (without headers) | false - incorrect query (errortext in Error)
    */
    private function xmlHttpsReq($addr, $params){ 
        if($this->TestMode) echo "<b># Begin</b> wm_xmlHttpsReq: '".$addr."','".nl2br(htmlspecialchars($params))."'<br>";
        $ch = curl_init(substr($addr, 0, 4) == "http" ? $addr : $this->URL.$addr);
        if($this->TestMode) echo " - full addr: '".(substr($addr, 0, 4) == "http" ? $addr : $this->URL.$addr)."'<br>";
       	curl_setopt($ch, CURLOPT_CAINFO, $GLOBALS['serverroot']."siti/libertyreserve2.crt");  
    	curl_setopt($ch, CURLOPT_TIMEOUT, 40);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        $result=curl_exec($ch);
		//echo " result= ";
		//print_r($result);
        if($this->TestMode) echo " - CURL result : '".htmlspecialchars($result)."'<br>";
        if(curl_errno($ch) != 0){
            $this->Error = 'CURL_error: '.curl_errno($ch).', '.curl_error($ch);
			echo 'CURL_error: '.curl_errno($ch).', '.curl_error($ch);
            return false;
        }
        curl_close($ch);
		$insert="insert into xml ( `iface`, `query`, `answer` ) values ('LR $addr', '".
									mysql_real_escape_string(urldecode($params))."', '".mysql_real_escape_string($result)."')";
		$query=_query2($insert, "lr.xml");
        return $result;
    }
    
    /**
    * @desc lr_XML_Parse parse and check XML from response
    * @param string - XML
    * @return array|false - array of correct vals | false - error in responce
    */
    private function lr_XML_Parse($XMLString){
        $XMLString = preg_replace("!>[\s]+<!Ums", "><", $XMLString);
        if($this->TestMode) {
            echo "<b># Begin</b> lr_XML_Parse<br>";
            echo " - input XML : '".htmlspecialchars($XMLString)."'<br>";
        }
        $xml_parser = xml_parser_create('UTF-8');
        $res = xml_parse_into_struct($xml_parser, $XMLString, $vals, $index);
        if($res===0){
            if($this->TestMode) echo "XML parser error ".xml_get_error_code($xml_parser).": ".xml_error_string(xml_get_error_code($xml_parser))."<br>";
            $this->Error = "XML parser error ".xml_get_error_code($xml_parser).": ".xml_error_string(xml_get_error_code($xml_parser));
            return false;
        }
        xml_parser_free($xml_parser);
        //if($this->wm_CheckResp($vals,$index)===false) return false;
        return $vals;
    }

    /**
    * @desc Проверка баланса на счету
    * @param int - номер транзакции (64-bit integer)
    */
    public function Balance($reqID){
        $req = '<BalanceRequest id="'.$reqID.'"><Auth><AccountId>'.$this->Account.'</AccountId><ApiName>'.$this->APIName.'</ApiName><Token>'.$this->createAuthToken($reqID.":").'</Token></Auth>';
        $req .= '<Balance><CurrencyId>usd</CurrencyId></Balance>';
		$req .= '<Balance><CurrencyId>euro</CurrencyId></Balance>';
        $req .= '</BalanceRequest>';
        $resp = $this->xmlHttpsReq('balance','req='.urlencode($req));
        if($resp === false) return false;
        $vals = $this->lr_XML_Parse($resp);
        if($vals===false) return false;
        $return = array();
        for($n = 0; $n < count($vals); ++$n){
            switch(true){
                case($vals[$n]['tag']=='BALANCERESPONSE' && $vals[$n]['type'] == "open"):
                    $return["OPERATION_ID"] = $vals[$n]['attributes']['ID'];
                    $return["OPERATION_DATE"] = $vals[$n]['attributes']['DATE'];
                    break;
                case($vals[$n]['tag']=='BALANCE'):
                    if($vals[$n]['type'] == "open"){
                        $acc = '';
                        $curr = '';
                        $date = '';
                        $value = '';
                    } else {
                        $return['BALANCE'][$curr] = array('ACCOUNT'=>$acc,
                                                          'CURRENCY'=>$curr,
                                                          'DATE'=>$date,
                                                          'VALUE'=>$value
                                                        );
                    }
                    break;
                case($vals[$n]['tag']=='ACCOUNTID'):
                    $acc = $vals[$n]['value'];
                    break;
                case($vals[$n]['tag']=='CURRENCYID'):
                    $curr = $vals[$n]['value'];
                    break;
                case($vals[$n]['tag']=='DATE'):
                    $date = $vals[$n]['value'];
                    break;
                case($vals[$n]['tag']=='VALUE'):
                    $value = $vals[$n]['value'];
                    break;
            }
        }
        if($return["OPERATION_ID"]!=$reqID){
            if($this->TestMode) echo "ID value of Request and Response is mismatch!!!<br>";
            $this->Error = "ID value of Request and Response is mismatch!!!";
            return false;
        }
        return $return;
    }

    /**
    * @desc получение имени аккаунта
    * @param int - номер транзакции (64-bit integer)
    * @param string - запрашиваемый аккаунт
    */
    public function AccountName($reqID,$account){
        $req = '<AccountNameRequest id="'.$reqID.'"><Auth><AccountId>'.$this->Account.'</AccountId><ApiName>'.$this->APIName.'</ApiName><Token>'.$this->createAuthToken().'</Token></Auth>';
        $req .= '<AccountName><AccountId>'.$this->Account.'</AccountId><AccountToRetrieve>'.$account.'</AccountToRetrieve></AccountName></AccountNameRequest>';
        $resp = $this->xmlHttpsReq('accountname','req='.urlencode($req));
        if($resp === false) return false;
        $vals = $this->lr_XML_Parse($resp);
        if($vals===false) return false;
        $return = array();
        for($n = 0; $n < count($vals); ++$n){
            switch(true){
                case($vals[$n]['tag']=='ACCOUNTNAMERESPONSE' && $vals[$n]['type'] == "open"):
                    $return["OPERATION_ID"] = $vals[$n]['attributes']['ID'];
                    $return["OPERATION_DATE"] = $vals[$n]['attributes']['DATE'];
                    break;
                case($vals[$n]['tag']=='ACCOUNTNAME'):
                    if($vals[$n]['type'] == "open"){
                        $date = '';
                        $name = '';
                    } else {
                        $return['ACCOUNT_DATE'] = $date;
                        $return['ACCOUNT_NAME'] = $name;
                    }
                    break;
                case($vals[$n]['tag']=='DATE'):
                    $date = $vals[$n]['value'];
                    break;
                case($vals[$n]['tag']=='NAME'):
                    $name = $vals[$n]['value'];
                    break;
            }
        }
        if($return["OPERATION_ID"]!=$reqID){
            if($this->TestMode) echo "ID value of Request and Response is mismatch!!!<br>";
            $this->Error = "ID value of Request and Response is mismatch!!!";
            return false;
        }
        return $return;
    }

    /**
    * @desc получение истории транзакций
    * @param int - номер транзакции (64-bit integer)
    * @param string - начальная дата периода (формат YYYY-DD-MM HH:mm:SS)
    * @param string - конечная дата периода (формат YYYY-DD-MM HH:mm:SS)
    * @param string - список интересующих валют через запетую (например: 'LRUSD, LREUR')
    * @param string - история по этому аккаунту-корреспонденту
    * @param string - направление транзакций (incoming/outgoing/any)
    * @param string - Merchant ID (varchar(20)) - ID в системе продавца
    * @param string - ID транзакции (то что было в reqID при совершении операции)
    * @param string - TransferType (возможное значение 'transfer')
    * @param string - Site (transfer performed from Liberty Reserve site), Wallet (transfer performed from Liberty Reserve Wallet), SCI (transfer performed from Liberty Reserve SCI), API (transfer performed from Liberty Reserve API interface)
    * @param string - yes/no/any
    * @param string - минимальный размер транзакции
    * @param string - максимальный размер транзакции
    * @param string - кол-во транзакций на страницу результата
    * @param string - номер страницы в результате
    */
    public function History($reqID,$startdate='',$enddate='',$currency='',$corrAccount='',$direction='',$TransferId='',$ReceiptId='',$TransferType='',$Source='', $Anonymous='', $AmountFrom='', $AmountTo='', $PageSize='', $PageNumber='', $returnxml=false){
        $req = '<HistoryRequest id="'.$reqID.'"><Auth><AccountId>'.$this->Account.'</AccountId><ApiName>'.$this->APIName.'</ApiName><Token>'.$this->createAuthToken($reqID.":".$startdate.":".$enddate.":").'</Token></Auth>';
        $req .= '<History><CurrencyId>'.$currency.'</CurrencyId><From>'.$startdate.'</From><Till>'.$enddate.'</Till>';
        $req .= '<CorrespondingAccountId>'.$corrAccount.'</CorrespondingAccountId><Direction>'.$direction.'</Direction><TransferId>'.$TransferId.'</TransferId>';
        $req .= '<Source>'.$Source.'</Source><Anonymous>'.$Anonymous.'</Anonymous>';
        $req .= '<AmountFrom>'.$AmountFrom.'</AmountFrom><AmountTo>'.$AmountTo.'</AmountTo>';
        if(strlen($PageSize.$PageNumber)>0) $req .= '<Pager><PageSize>'.$PageSize.'</PageSize><PageNumber>'.$PageNumber.'</PageNumber></Pager>';
        $req .= '</History></HistoryRequest>';
        $resp = $this->xmlHttpsReq('history','req='.urlencode($req));
        if($resp === false) return false;
		if($returnxml) return $resp;
        $vals = $this->lr_XML_Parse($resp);
        if($vals===false) return false;
        $return = array();
        $trans_flag = false;
        for($n = 0; $n < count($vals); ++$n){
            switch(true){
                case($vals[$n]['tag']=='HISTORYRESPONSE' && $vals[$n]['type'] == "open"):
                    $return["OPERATION_ID"] = $vals[$n]['attributes']['ID'];
                    $return["OPERATION_DATE"] = $vals[$n]['attributes']['DATE'];
                    break;
                case($vals[$n]['tag']=='PAGER'):
                    if($vals[$n]['type'] == "open"){
                        $pager = array();
                    } else {
                        $return['PAGER'] = $pager;
                    }
                    break;
                case($vals[$n]['tag']=='RECEIPT'):
                    if($vals[$n]['type'] == "open"){
                        $receipt = array();
                    } else {
                        $return['RECEIPT'][] = $receipt;
                    }
                    break;
                case($vals[$n]['tag']=='TRANSFER'):
                    if($vals[$n]['type'] == "open"){
                        $transfer = array();
                        $trans_flag = true;
                    } else {
                        $receipt['TRANSFER'] = $transfer;
                        $trans_flag = false;
                    }
                    break;
                case(in_array($vals[$n]['tag'],array('RECEIPTID','DATE','PAYERNAME','PAYEENAME','FEE','CLOSINGBALANCE',
								'TRANSFERID','TRANSFERTYPE','PAYER','PAYEE','CURRENCYID','MEMO','ANONYMOUS','SOURCE'))):
                    $receipt[$vals[$n]['tag']] = $vals[$n]['value'];
                    break;
                case(in_array($vals[$n]['tag'],array('TRANSFERID','TRANSFERTYPE','PAYER','PAYEE','CURRENCYID','MEMO','ANONYMOUS','SOURCE'))):
                    $transfer[$vals[$n]['tag']] = $vals[$n]['value'];
                    break;
                case($vals[$n]['tag']=='AMOUNT'):
                    if($trans_flag) $transfer[$vals[$n]['tag']] = $vals[$n]['value'];
                    else $receipt[$vals[$n]['tag']] = $vals[$n]['value'];
                    break;
                case(in_array($vals[$n]['tag'],array('PAGESIZE','PAGECOUNT','PAGENUMBER','TOTALCOUNT'))):
                    $pager[$vals[$n]['tag']] = $vals[$n]['value'];
                    break;
            }
        }
        if($return["OPERATION_ID"]!=$reqID){
            if($this->TestMode) echo "ID value of Request and Response is mismatch!!!<br>";
            $this->Error = "ID value of Request and Response is mismatch!!!";
            return false;
        }
        return $return;
    }

    /**
    * @desc выполнение транзакции (перевода средств)
    * @param int - номер транзакции (64-bit integer)
    * @param string - аккаунт получателя платежа
    * @param string - валюта платежа
    * @param string - объем транзакции (кол-во денег)
    * @param string - описание транзакции
    * @param string - Merchant ID (varchar(20)) - ID в системе продавца
    * @param string - TransferType (возможное значение 'transfer')
    * @param string - 'true'|'false' - анонимный платеж
    */
    public function Transfer($reqID='',$Payee,$Currency,$Amount,$Memo='',$TransferID='',$TransferType='transfer',$Anonimous='false'){
		//maildebugger($_SESSION);
		//if ( isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']=="adm" ){
		//}else{
		//	return false; // убрать
		//}
		$reqID=$this->reqID();
        $req = '<TransferRequest id="'.$reqID.'"><Auth><AccountId>'.$this->Account.'</AccountId><ApiName>'.$this->APIName.'</ApiName><Token>'.$this->createAuthToken($reqID.":".$TransferID.":".$Payee.":".$Currency.":".$Amount.":").'</Token></Auth>';
        $req .= '<Transfer><TransferId>'.$TransferID.'</TransferId><TransferType>'.$TransferType.'</TransferType><Payee>'.$Payee.'</Payee>';
        $req .= '<CurrencyId>'.$Currency.'</CurrencyId><Amount>'.$Amount.'</Amount><Memo>'.$Memo.'</Memo><Anonymous>'.$Anonimous.'</Anonymous><PaymentPurpose>service</PaymentPurpose></Transfer></TransferRequest>';
        $resp = $this->xmlHttpsReq('transfer','req='.urlencode($req));
        if($resp === false) return false;
        $vals = $this->lr_XML_Parse($resp);
        if($vals===false) return false;
        $return = array();
        $trans_flag = false;
        for($n = 0; $n < count($vals); ++$n){
            switch(true){
                case($vals[$n]['tag']=='TRANSFERRESPONSE' && $vals[$n]['type'] == "open"):
                    $return["OPERATION_ID"] = $vals[$n]['attributes']['ID'];
                    $return["OPERATION_DATE"] = $vals[$n]['attributes']['DATE'];
                    break;
                case($vals[$n]['tag']=='RECEIPT'):
                    if($vals[$n]['type'] == "open"){
                        $receipt = array();
                    } else {
                        $return['RECEIPT'] = $receipt;
                    }
                    break;
                case($vals[$n]['tag']=='TRANSFER'):
                    if($vals[$n]['type'] == "open"){
                        $transfer = array();
                        $trans_flag = true;
                    } else {
                        $receipt['TRANSFER'] = $transfer;
                        $trans_flag = false;
                    }
                    break;
                case(in_array($vals[$n]['tag'],array('RECEIPTID','DATE','PAYERNAME','PAYEENAME','FEE','CLOSINGBALANCE',
							'TRANSFERID','TRANSFERTYPE','PAYER','PAYEE','CURRENCYID','MEMO','ANONYMOUS','SOURCE'))):
                    $receipt[$vals[$n]['tag']] = $vals[$n]['value'];
                    break;
                case(in_array($vals[$n]['tag'],array('TRANSFERID','TRANSFERTYPE','PAYER','PAYEE','CURRENCYID','MEMO','ANONYMOUS','SOURCE'))):
                    $transfer[$vals[$n]['tag']] = $vals[$n]['value'];
                    break;
                case($vals[$n]['tag']=='AMOUNT'):
                    if($trans_flag) $transfer[$vals[$n]['tag']] = $vals[$n]['value'];
                    else $receipt[$vals[$n]['tag']] = $vals[$n]['value'];
                    break;
            }
        }
        if($return["OPERATION_ID"]!=$reqID){
            if($this->TestMode) echo "ID value of Request and Response is mismatch!!!<br>";
            $this->Error = "ID value of Request and Response is mismatch!!!";
            return false;
        }
        return $return;
    }
	function reqID() {
		return microtime()*10000000;
	}
	function insert_badlog ($oid,$lrtransferid="",$lrpaidby="") {
		$query="update orders set droped=1 where orders.id=".$oid;
		$result=_query($query,"");
		$query="insert into badlog (type,ip,data) values ('fake data, droped','".
								(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:"")."','".print_r(($_REQUEST),1)."')";
      	$result=_query($query,"");
	   	$query="update payment set LMI_SYS_TRANS_NO='".$lrtransferid."',
						LMI_PAYER_WM='fraud entry #".mysql_insert_id()."',
						LMI_PAYER_PURSE='".$lrpaidby."',
						status='fake data, order is droped',
						ordered=0,
						canceled=0
						WHERE orderid='".$oid."'";
		$result=_query($query, "mcvresult.php 1");	
	}
}
?>