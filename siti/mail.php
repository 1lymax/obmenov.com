<?php

class Pop3Worker {
  private $pop_conn = NULL; // Здесь храним указатель на поток скрипт<->сервер
 
  private $pop3Server = "mail.obmenov.com";
  private $pop3User = "sms@obmenov.com";
  private $pop3Pass = "1kqInE99";
 
  private $successAuth = False; // Если удалось авторизироваться на сервере
  private $letterCount = 0; // Количество писем на сервере
 
  function __construct()
  {
    // Вызываем все рабочие функции в конструкторе
    //$this->connectPop3Server();
    //$this->authPop3Server();
    //$this->startWork();
    //$this->processMail(); // Ознакомимся позже
    //$this->disconnectPop3Server();
  }
 
  function connectPop3Server()
  {
    $this->pop_conn = fsockopen($this->pop3Server, 110, $errno, $errstr, 30);
	
    $connectResult = fgets($this->pop_conn, 1024);
 
    if (strpos($connectResult, "OK")) {
      echo "Successful connect!<br />";
    } else {
      echo "Failed to connect pop3-sever!<br />";
    }
  }
 
  function disconnectPop3Server()
  {
    fputs($this->pop_conn,"QUIT\r\n");
 
    echo "<b>Connection is closed</b>";
  }
 
  function authPop3Server($user,$pass)
  {
    print "<b>Trying to process authorization under pop3-server…<br /></b>";
 
    fputs($this->pop_conn, "USER ".$user."\r\n");
    $connectResult = fgets($this->pop_conn, 1024);
 
    // Если получилось авторизироваться, делаем метку, как true
    if (strpos($connectResult, "OK")) {
      fputs($this->pop_conn, "PASS ".$pass."\r\n");
      $connectResult = fgets($this->pop_conn, 1024);
 
      if (strpos($connectResult, "OK")) {
        echo "Success authorization!<br />";
        $this->successAuth = True;
      }
    } else {
      echo "Failed to process authorization!<br />";
    }
  }
 
  function startWork()
  {
    print "<b>Receiving mail information from pop3-server…<br /></b>";
 
    fputs($this->pop_conn, "STAT\r\n");
    $connectResult = fgets($this->pop_conn, 1024);
 
    // Получаем количество писем, распарсив регулярным выражением
    // Возвращаемую сервером строку
    if (strpos($connectResult, "OK")) {
      $pattern = "/OK ([0-9]+) [0-9]+/";
      preg_match($pattern, $connectResult, $matches);
      $this->letterCount = intval($matches[1]);
 
      echo "Information is achived! <b>" . $this->letterCount . "</b> new letters.<br />";
    } else {
      echo "Failed to get information from pop3-sever!<br />";
    }
  }
  
  function getData($streamConnection){
   $data = "";
   while (!feof($streamConnection)) {
    $buffer = chop(fgets($streamConnection, 1024));
    $data .= "$buffer\r\n";
    if (trim($buffer) == ".") break;
   }
   return $data;
  }
  function fetch_structure($email) {
   $ARemail = Array();
   $separador = "\r\n\r\n";
 
   $header = trim(substr($email,0,strpos($email,$separador)));
   $bodypos = strlen($header)+strlen($separador);
   $body = substr($email,$bodypos,strlen($email)-$bodypos);
 
   $ARemail["header"] = $header;
   $ARemail["body"] = $body;
 
   return $ARemail;
  }
  
  function decode_header($header) {
  $headers = explode("\r\n",$header);
 
  $decodedheaders = Array();
 
  for ($i=1; $i < count($headers); $i++) {
    $thisheader = trim($headers[$i]);
    if (!empty($thisheader)) {
      if (!ereg("^[A-Z0-9a-z_-]+:", $thisheader)) {
        $decodedheaders[$lasthead] .= " $thisheader";
      } else {
        $dbpoint = strpos($thisheader,":");
        $headname = strtolower(substr($thisheader,0,$dbpoint));
        $headvalue = trim(substr($thisheader,$dbpoint+1));
 
        if (!array_key_exists($headname, $decodedheaders)) {
          $decodedheaders[$headname] = "; $headvalue";
        } else {
          $decodedheaders[$headname] = $headvalue;
        }
        $lasthead = $headname;
      }
    }
  }
 
  return $decodedheaders;
  }
  
  function processMail($tb){
  $timeoutPatterns = array(
    "/while sending MAIL FROM/",
    "/Connection\s*timed\s*out/"
  );
 
  for ($i=1; $i <= $this->letterCount; $i++)
  {
    fputs($this->pop_conn, "RETR ".$i."\r\n");
    $text = $this->getData($this->pop_conn);
    $struct = $this->fetch_structure($text);
	//print_r($struct['header']);
	$header=substr( $struct['header'], strpos($struct['header'], "\r\n")+2 );
	$header=str_replace("\r\n	"," ",$header);
	$header=str_replace(":\r\n",": \r\n",$header);
	$header=str_replace(array("<",">"),"",$header);
	//print_r($header);
	$header=$this->Explode(": ","\r\n", $header);
	$header=array_change_key_case($header, CASE_LOWER);
	//print_r($header);
	$select="select id from mail_balance where messid='".$header['message-id']."'";
	$query=_query($select,"");
	if ( mysql_num_rows($query)==0 ) {
		$update="insert into ".$tb." (header, body, messid, email) values (
							'".mysql_real_escape_string(print_r($header,1))."',
							'".$struct['body']."',
							'".$header['message-id']."',
							'".(isset($header['return-path'])?$header['return-path']:"")."'
							)";
		//echo $update;
		$query=_query($update,"");
	}
	
	
    for ($j=0; $j < count($timeoutPatterns); $j++)
    {
      if (preg_match($timeoutPatterns[$j], $struct['body'], $matches) > 0)
      {
	
		// Do something…
        break;
      }
    }
 
   // fputs($this->pop_conn, "DELE ".$i."\r\n");
  }
  
 }
 function Explode ($del1, $del2, $array){
    $array1 = explode("$del1", $array);
	foreach($array1 as $key=>$value){
		$array2 = explode("$del2", $value);
		foreach($array2 as $key2=>$value2){
			$array3[] = $value2; 
		}
	}
    $afinal = array();
	for ( $i = 0; $i <= count($array3); $i += 2) {
    	if( isset($array3[$i]) && $array3[$i]!="" ){
    		$afinal[trim($array3[$i])] = trim($array3[$i+1]);
    	}
	}
	return $afinal;
  }
}


?>
