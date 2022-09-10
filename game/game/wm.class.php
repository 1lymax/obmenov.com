<?
/*
// elrkjhvrelhfvwli кодовое слово
// relkbjhnerobtjnwerlkjn регистрация апи

 Класс оплаты через мерчант с запросом на сервер GameDealer
 [2nik@ua.fm]
 Victor Niko
 icq: 324856988
*/
//require_once("/var/www/webmoney_ma/data/www/obmenov.com/siti/_header.php");
//@ini_set ("display_errors", true);
class wmbank{

   var $wmPurses = array('Webmoney WMR'=>'R247297158388','Webmoney WMZ'=>'Z337459962936','Webmoney WMU'=>'U276969565150'
						 ,'Приватбанк USD'=>'P24USD','Приватбанк UAH'=>'P24UAH'
						 ,'MasterCard/VISA RUR'=>'MCVRUR','MasterCard/VISA USD'=>'MCVUSD','MasterCard/VISA UAH'=>'MCVUAH',
						 'LiqPay RUR'=>'MCVRUR','LiqPay USD'=>'MCVUSD','LiqPay UAH'=>'MCVUAH'
						 ); //219391095990
   //var $wmPurses = array('WMR'=>'R332377274109','WMZ'=>'Z348097036731','WMU'=>'U348602895102'); //418941129503
   var $wmTrans  = array('Webmoney WMZ'=>'USD','Webmoney WMR'=>'RUB','Webmoney WMU'=>'UAH','Webmoney WME'=>'EUR',
						 'MasterCard/VISA USD'=>'USD','MasterCard/VISA RUR'=>'RUB','MasterCard/VISA UAH'=>'UAH',
						 'Приватбанк USD'=>'USD','Приватбанк UAH'=>'UAH','P24USD'=>'USD','P24UAH'=>'UAH',
						 'MCVUSD'=>'USD','MCVUAH'=>'UAH','MCVRUR'=>'RUB',
						 'LiqPay USD'=>'USD','LiqPay UAH'=>'UAH',' LiqPay RUR'=>'RUB');

   public $key	= "ursurgysieurgbfdjhvkzb"; //Webmoney Merchant Key
   public $apiid = 57;  //API ID - Gamedealer
   public $apikey = '7297a2e6c481ab918bc5ea48f4935347'; //APD KEY Gamedealer
   public $mywmid  = '219391095990';//my wmid
   public $mygdpercent = 2.5;
   public $gdwmid="";
   public $wmxi;
   
   function __construct(){
     //nothing
   }

   function email($content){
       if(is_array($content)){
           $content = print_r($content,1);
       }
       mail('support@obmenov.com','GamePay',$content);
   }
   function checkPostWm($post=array()){
        if(isset($_POST['LMI_PREREQUEST'])){
           $amount = (float)$_POST['LMI_PAYMENT_AMOUNT'];

           if(!in_array($_POST['LMI_PAYEE_PURSE'],$this->wmPurses))return 'Извините, временно не принимаются платежи на этот кошелек';
             $purse = $_POST['LMI_PAYEE_PURSE'];
             foreach($this->wmPurses as $k=>$v){
                if($v == $purse)$currency = $k;
             }
             $nick = isset($post['nick'])?$post['nick']:'';
             $projectid = isset($post['projectid'])?(int)$post['projectid']:0;
             $checkNick = $this->checkLogin($nick,$projectid);
             $wmid = mysql_escape_string($post['LMI_PAYER_WM']);
             //print_r($checkNick);
             if(!isset($checkNick['status']))return 'Ошибка платежа. Попробуйте позже (связь с игрой прервана)';
             $pid = (int)$post['LMI_PAYMENT_NO'];

             //if($checkNick['status'] == 1){
              $q = $this->query("select id from gamedealer_wmreq WHERE 1 AND pid = ".$pid." AND wmid = '$wmid'");
             	if(mysql_num_rows($q)!=0)return 'Платеж с таким номером уже существует. Повторите запрос';
             	$this->query("insert into gamedealer_wmreq SET pid = '$pid',wmid = '$wmid'");
             	return 'YES';
             //}else{
			 //	 echo "Ник не найден в игре. ";
			 //}

        }
        $this->email($post);
        return 'Ошибка платежа. Попробуйте позже..';
   }

   function checkHash($post=array()){
         //$this->email($post);
         $pid = (int)$post['LMI_PAYMENT_NO'];
         $wmid = mysql_escape_string($_POST['LMI_PAYER_WM']);
         $hash = $post['LMI_HASH'];

         $MDhash = strtoupper(md5(strtoupper($post['LMI_PAYEE_PURSE'].$post['LMI_PAYMENT_AMOUNT'].$post['LMI_PAYMENT_NO'].$post['LMI_MODE'].$post['LMI_SYS_INVS_NO'].$post['LMI_SYS_TRANS_NO'].$post['LMI_SYS_TRANS_DATE']).$this->key.strtoupper($post['LMI_PAYER_PURSE'].$post['LMI_PAYER_WM'])));
         if($MDhash!=$hash){
         	$this->email('error Sign :: '.$MDhash.' :: '.$hash);
         	die();
         }
         $data['purse'] = $post['LMI_PAYER_PURSE'];
         $data['amount'] = (float)$post['LMI_PAYMENT_AMOUNT'];
         $data['wm_currency'] = 'Webmoney WM'.substr($post['LMI_PAYEE_PURSE'],0,1);
         $data['currency']    = $this->wmTrans[$data['wm_currency']];
		 $data['wmid']=$wmid;
		 $data['pid']=$pid;
		 $data['nick']=$post['nick'];
		 $data['projectid'] = (int)$post['projectid'];
		 $this->update_pay($data);
		 
   }
   function checkmcv ($post=array()) {
			$insig = $_POST['signature'];
			$signature=$GLOBALS['lq_signature'];
			$response = base64_decode($_POST['operation_xml']);
			$response=simplexml_load_string($response);
			
			$resp_status=isset($response->status) ? $response->status : "";
			$resp_descr=isset($response->response_description) ? $response->response_description : "";
			
			$gensig = base64_encode(sha1($signature.$resp.$signature,1));
			//maildebugger($insig.' '.$gensig);
	   		if ($insig == $gensig){
				if ($resp_status == 'success' ){
					$query=_query("select * from gamedealer_wmreq where pid=".$response->order_id,"wmclass-checkmcv");
					$row=$query->fetch_assoc();
					$data['pid']=	$response->order_id;
					$data['wmid']=	"";
					$data['purse']=	$response->from;
         			$data['amount'] = (float)$response->amount;
         			$data['currency']= substr($row['wm_currency'],3,3);
					$data['wm_currency']=$row['wm_currency'];
         			$data['nick']=$row['nick'];
		 			$data['projectid'] = (int)$row['projectid'];
		 			$this->update_pay($data);
				
				}
			}
	   }
   function checkp24 ($post=array()) {
	   	parse_str($_POST['payment'], $payrez);
	   	$select="select * from gamedealer_wmreq where pid=".$payrez['order'];
		$query=_query($select, "mcvresult.php 12");
		if ( mysql_num_rows($query)==1 ) {
			$row=$query->fetch_assoc();
			//echo "2";
			if ( $row['wm_currency']=="P24UAH" ) {
				
				$merchant=$GLOBALS['pb_uah_merchant_p'];
				$merchant_num=$GLOBALS['pb_uah_merchant'];
				//echo "3";
			}elseif ( $row['wm_currency']=="P24USD" ) {
				$merchant=$GLOBALS['pb_usd_merchant_p'];
				$merchant_num=$GLOBALS['pb_usd_merchant'];
				//echo "4";
			}
		}
		if ( $merchant_num!=0 ) {
			$signature = sha1(md5($_POST['payment'].$merchant));
		
			if ( $signature==$_POST['signature'] ) {
				parse_str($_POST['payment'], $payrez);
				if ( $payrez['state']=="ok") {
					$data=array();
					$data['pid']=	$payrez['order'];
					$data['wmid']=	"";
					$data['purse']=	$payrez['sender_phone'];
         			$data['amount'] = $payrez['amt'];
         			$data['currency']= substr($row['wm_currency'],3,3);
					$data['wm_currency']=$row['wm_currency'];
         			$data['nick']=$row['nick'];
		 			$data['projectid'] = (int)$row['projectid'];	
					$this->update_pay($data);
					
				}
			}
		}
		
	   }
   function update_pay ($data=array()) {
         $q = $this->query("select id from gamedealer_wmreq WHERE 1 AND wmid = '".$data['wmid']."' AND pid = '".$data['pid']."'");
           //print_r($data);
		   //echo mysql_num_rows($q);
		   if($s = mysql_fetch_array($q)){
               		$row = $this->query("select * from gamedealer_courses where currency='".$this->wmTrans[$data['wm_currency']]."'");
					$row=mysql_fetch_assoc($row);
					//maildebugger($this->wmTrans[$data['wm_currency']]." "." ".print_r($row,1)." ".print_r($data,1));
			        $this->query("update gamedealer_wmreq SET
                       purse = '".$data['purse']."',wm_amount = '".$data['amount']."',wm_currency = '".$data['wm_currency']."',
					   rur_amount=".$data['amount']*$row['value'].",
					   timestamp = UNIX_TIMESTAMP(),status = 1
                       WHERE id = " .$s['id']."
			        ");

  				$q2 = $this->query("select id from gamedealer_payments WHERE pid = ".$s['id']);
  				if(mysql_num_rows($q2) == 0){
                    $this->query("
                     INSERT INTO gamedealer_payments SET nick = '".mysql_escape_string($data['nick'])."',projectid = '".$data['projectid']."',
					 rur_amount='".$data['amount']*$row['value']."',
                     amount = '".$data['amount']."',currency = '".$data['currency']."',timestamp = UNIX_TIMESTAMP(),pid = '".$s['id']."'
                    ");
                  //return true;
                  $id = mysql_insert_id();
				  $row=$this->query("select * from gamedealer_payments where id=".$id);
				  $row=mysql_fetch_assoc($row);
  				  $reusltpayObj = $this->pay($id,$data['nick'],$data['projectid'],$row['rur_amount'],"RUB");
  				  if(isset($reusltpayObj->id)){
                      $gdId = (int)$reusltpayObj->id;
                      $this->query("update gamedealer_payments SET status = 1,gdid = '".$gdId."' WHERE id =  ".$id);
                      $this->email("Платеж ".$data['amount']." ".$data['currency']." на ник " .$data['nick']." в проект ".$data['projectid']);
  				  } else {
                     $paymessage = iconv('utf-8','windows-1251',$reusltpayObj->desc);
                     $paymessage = mysql_escape_string($paymessage);

                     $this->email("Платеж ".$data['amount']." ".$data['currency']." на ник ".$data['nick']." в проект ".$data['projectid'] ." неудался. причина: ".$paymessage);
  				     $this->query("update gamedealer_payments SET status = -1,paymessage = '$paymessage' WHERE id =  $id");
  				  }
  				}

          }
   }

    function checkCron(){

       $q = $this->query("select * from gamedealer_payments WHERE status IN (0,-1,2) ORDER by timestamp DESC LIMIT 5");
	   
	   
       while($s = mysql_fetch_array($q)){
             //print_r($s);
			 
             $reusltpayObj = $this->pay($s['id'],$s['nick'],$s['projectid'],$s['rur_amount'],'RUB');
  				  //echo '<pre>';print_r($reusltpayObj);

  				  if (isset($reusltpayObj->id)){
                      $gdId = (int)$reusltpayObj->id;
                      $this->query("update gamedealer_payments SET status = 1,gdid = '$gdId' WHERE id = ".$s['id']);
                      $this->email("Платеж ".$s['amount'].$s['currency']." на ник ".$s['nick']." в проект ".$s['projectid']);
  				  } else {
                     $paymessage = iconv('utf-8','windows-1251',$reusltpayObj->desc);
                     $paymessage = mysql_escape_string($paymessage);

                     $this->email("Платеж ".$s['amount'].$s['currency']." на ник ".$s['nick']." в проект ".$s['projectid']." неудался. причина: ".$paymessage);

  				     //print_r($reusltpayObj);
                     if  ($reusltpayObj->status == -28 && $s['status']!=2){
                     //выставляем счет
			 	
                        $invAmount = $s['rur_amount']-($s['rur_amount']/100*$this->mygdpercent);
                        $desc = "Средства для перевода игровой валюты: ".$s['nick']." (проект ".$s['projectid'].")";
                        $resultInv = $this->addInvoice($invAmount,'RUB',$desc);
						if  ($resultInv->status == 1){
							$this->query("update gamedealer_payments SET status = 2  WHERE id = ".$s['id']);
							
							$result=$this->wminvstatus($s['id']);
							$paymessage=$result['desc'];
							//echo"<pre>";print_r($result);
							if ( $result['status']==1 ){
								$this->query("update gamedealer_payments SET status = 1,gdid = '$gdId' WHERE id = ".$s['id']);
                    			$this->email("Платеж ".$s['amount'].$s['currency']." на ник ".$s['nick']." в проект ".$s['projectid']);
							}
						}

                        //print_r($resultInv);
  				     }
  				     $this->query("update gamedealer_payments SET paymessage = '$paymessage'  WHERE id = ".$s['id']);

  				  }
       }
     //$this->updateCourses();
    }

    function updateCourses(){
        $url = 'http://gamedealer.ru/course.api';
        $result = $this->curl($url);
        $mparse = $this->parse($result);
        //echo '<pre>';print_r($mparse);
        $arrayCur = array();
        if(isset($mparse->USD))$arrayCur['USD'] = (float)$mparse->USD;
        if(isset($mparse->UAH))$arrayCur['UAH'] = (float)$mparse->UAH;
        if(isset($mparse->RUB))$arrayCur['RUB'] = (float)$mparse->RUB;
        if(isset($mparse->BYR))$arrayCur['BYR'] = (float)$mparse->BYR;
        //if(isset($mparse->USD))$arrayCur['USD'] = (float)$mparse->USD;
        if(empty($arrayCur))return false;
        foreach($arrayCur as $k=>$v){
             $q = $this->query("select id from gamedealer_courses WHERE 1 AND currency = '$k'");
             if(mysql_num_rows($q) == 0)$this->query("insert into gamedealer_courses SET currency = '$k'");
             $this->query("update gamedealer_courses SET value = '$v',timestamp = UNIX_TIMESTAMP() WHERE currency = '$k'");
        }
        if(!is_object($mparse))return false;

    }
    function getCourses(){
      $q = $this->query("select * from gamedealer_courses WHERE 1");
      $a = array();
      while($s = mysql_fetch_array($q)){
        $a[$s['currency']] =  $s['value'];
        foreach($this->wmTrans as $wmKur => $vt){
           if($vt == $s['currency'])$a[$wmKur] = $s['value'];
        }
      }
      return $a;
    }

    function wmcurrency($currency){
      switch($currency){
      	case 'RUB':return 'WMR'; break;
      	case 'USD':return 'WMZ'; break;

      }
    }

    function addInvoice($amount,$currency,$desc){
       $wmid = $this->mywmid;
       $paycurrency = eregi('WM',$currency)?$currency:$this->wmcurrency($currency);

       $desc = iconv('windows-1251','utf-8',$desc);
       $xml = '<?xml version="1.0"?>
       <request>
 			<apiid>'.$this->apiid.'</apiid>
 			<type>wminv</type>
 			<wmid>'.$wmid.'</wmid>
 			<traderid>0</traderid>
 			<amount>'.$amount.'</amount>
 			<currency>'.$paycurrency.'</currency>
 			<comment>'.$desc.'</comment>
			<sign>'.md5($this->apiid.$amount.$wmid.$this->apikey).'</sign>
 		</request>';

      $url = 'http://gamedealer.ru/wminv.api';
      $result = $this->curl($url,$xml);
      $res = $this->parse($result); //print_r($res);
      return $res;
    }


    function wminvstatus($id){
       //$wmid = $this->mywmid;
       //$paycurrency = eregi('WM',$currency)?$currency:$this->wmcurrency($currency);

       //$desc = iconv('windows-1251','utf-8',$desc);
		$xml  ="
 		<request>
    		<apiid>$apiid</apiid>
    		<type>invstatus</type>
   			 <id>$id</id>
  		  <traderid></traderid>
   		 <sign>".md5($apiid.$id.$apikey)."</sign>
		 </request>
		";
      $url = 'http://gamedealer.ru/wminvstatus.api';
      $result = $this->curl($url,$xml);
      $res = $this->parse($result); //print_r($res);
      return $res;
    }



    function pay($id,$nick,$projectid,$amount,$currency){
         //2f6e4fb86dc0ce80c08a95d5c29bbf56
         $amount = number_format($amount,2,'.','');
         $sign = md5($id.$amount.$currency.$nick.$projectid.$this->apiid.$this->apikey);
         $xml = '<?xml version="1.0"?>
         		<gdrequest>
                  <apiid>'.$this->apiid.'</apiid>
                  <nick>'.iconv('windows-1251','utf-8',$nick).'</nick>
                  <projectid>'.$projectid.'</projectid>
                  <id>'.$id.'</id>
                  <amount>'.$amount.'</amount>
                  <currency>'.$currency.'</currency>
                  <sign>'.$sign.'</sign>
				</gdrequest>';//<test>1</test> 
         	$url = 'http://gamedealer.ru/set.api';
         	$result = $this->curl($url,$xml);
         	$res = $this->parse($result);
			//maildebugger($xml);
         	return $res;
         	//maildebugger($result);
    }

    function check_status($pid){

      $ret = array('status'=>-1,'desc'=>'Платеж не найден');
        $q = $this->query("select * from gamedealer_wmreq WHERE pid = ".(int)$pid);

        if($s = mysql_fetch_array($q)){ //print_r($s);
          if($s['status'] == 1){
           	$ret['status'] = -1;
           	$ret['desc']   = 'Платеж  завершен, но игровая валюта еще не переведена';
          }else {
            $ret['desc'] = 'Платеж №'.$pid.' незавершен, извините..';
          }
              $q23 = $this->query("select * from gamedealer_payments WHERE pid = ".$s['id']);
              if($d = mysql_fetch_array($q23)){
                    if($d['status'] == 1){
                       $ret['status']  = 1;
                       $ret['desc']    = 'Игровая валюта успешно переведена игроку '.$d['nick'];
                    } else {
                      $ret['status']  = -1;
                      $ret['desc']    = 'Платеж не завершен и ожидает оплаты. Обновите страницу. Подождите несколько минут';
                    }
              }

          return $ret;
        }
      return $ret;
    }

    function checkLogin($nick,$projectid){
      //$nick = iconv('utf-8','windows-1251',$nick);
     // echo 'ник: '.$nick;
      $xml = '<gdrequest><nick>'.$nick.'</nick><projectid>'.$projectid.'</projectid></gdrequest>';
      $result = $this->curl('http://gamedealer.ru/chnick.api',$xml); // echo $result;
      $res = $this->parse($result);
	  //maildebugger ($xml." ".$result." ".$res);
      return array('status'=>(int)$res->result,'desc'=>mysql_escape_string($res->desc),'nick'=>mysql_escape_string($res->nick));
    }

   function projectlist(){
      //$this->updateProjects();

      $q = $this->query("select * from gamedealer_projects WHERE active=0 ORDER by title");
      $a = array();
      while($s = mysql_fetch_array($q)){
         $a[$s['projectid']] = $s;
      }
      return $a;
   }
   function updateProjects(){
      $resultProj = $this->curl('http://gamedealer.ru/projectlist.api');
      $res = $this->parse($resultProj);
	  //$p = new WMXIParser();
      print_r($res);
      foreach($res as $v){
		$prices="";
        //echo '<pre>';print_r($v);
		foreach ($v->moneta as $mon){
			$prices.=", price_".strtolower($mon->attributes()->currency)."=".(float)$mon;
			
		}
		
		 $projectid = (int)$v->projectid;
         $title     = mysql_escape_string(iconv('utf-8','windows-1251',$v->title));
	 
         $currency  = mysql_escape_string(iconv('utf-8','windows-1251',$v->currency));
         $url	    = mysql_escape_string($v->url);
         $img	    = mysql_escape_string($v->img);
		 $pay_attr	    = mysql_escape_string(iconv('utf-8','windows-1251',$v->pay_attr));
		 $q = $this->query("select projectid from gamedealer_projects WHERE projectid = '$projectid'");
            if(mysql_num_rows($q) == 0){
				$this->query("insert into gamedealer_projects SET projectid = $projectid");
            }
              echo "update gamedealer_projects SET title = '$title' ".$prices."
						   	,currency = '$currency',
							url = '$url', pay_attr='$pay_attr' WHERE projectid = $projectid";
			  $this->query("update gamedealer_projects SET title = '$title' ".$prices."
						   	,currency = '$currency',
							url = '$url', pay_attr='$pay_attr' WHERE projectid = $projectid");
	      }
   }

   function query($q){
      $query = _query($q, "wm.class.php");
      return $query;
   }

   function parse($xml){
      $result = simplexml_load_string($xml);
      return $result;
   }

   function curl($url,$post=''){
      $ch = curl_init($url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
    	curl_setopt($ch,CURLOPT_POST,1);
     	curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
    	$result = curl_exec($ch);
    	curl_close($ch);
      return $result;
   }


  function json($a=false){
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = $this->json($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = $this->json($k).':'.$this->json($v);
      return '{' . join(',', $result) . '}';
    }
  }

}
?>