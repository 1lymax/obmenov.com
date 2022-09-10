<?php 
require_once('../Connections/ma.php');
@ini_set ("display_errors", true);
//require_once('../function.php');
//require_once('../siti/class.php');
//require_once('../siti/lp.class.php');
//require_once('../siti/p24api.php');
//require_once('../siti/lib.lr.php');
require_once('../siti/socks.php');
	@ini_set ("display_errors", true);
	
	function multiexp ($del1, $del2, $array){
    $array1 = explode("$del1", $array);
	foreach($array1 as $key=>$value){
		$array2 = explode("$del2", $value);
		foreach($array2 as $key2=>$value2){
			$array3[] = $value2; 
		}
	}
    $afinal = array();
	for ( $i = 0; $i <= count($array3); $i += 2) {
    	if($array3[$i]!=""){
    		$afinal[trim($array3[$i])] = trim($array3[$i+1]);
    	}
	}
	return $afinal;
  }
  $type='obmenov.com';
  $post_data=array();
  $select="select id,type,host,host2,port,url,request,sms, step from clk_types where type='".$type."' order by step asc"; 
  $query=_query($select,"");
  
  while ( $row=$query->fetch_assoc() ) {
	  
	$select="select field,value from clk_post where type_id=".$row['id'];
	$query2=_query($select,"");
	while ( $fields=mysql_fetch_assoc($query2) ) $post_data[$fields['field']]=$fields['value'];
	$select="select cookie from clk_session where session_id=".$row['id'];
	$query2=_query($select,"");
	$row_cook=mysql_fetch_assoc($query2);
	echo "куки перед отправкой из базы от предыдущего шага";
	print_r($row_cook['cookie']);
	echo"
	";
	$cookies=multiexp(";","=",$row_cook['cookie']);
	echo "отправляемые куки:";
	print_r($cookies);
	echo "
	";
	$t=http_request(
    	$row['request'],             /* HTTP Request Method (GET and POST supported) */
    	$row['host'],                       /* Target IP/Hostname */
    	$row['port'],                /* Target TCP port */
    	$row['url'],                /* Target URI */
    	array(),        /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */
    	$post_data,       /* HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2') */
    	$cookies,         /* HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2') */
    	array(), /* Custom HTTP headers ie. array('Referer: http://localhost/ */
    	1000,//,           /* Socket timeout in milliseconds */
    	0,          /* Include HTTP request headers */
    	1,          /* Include HTTP response headers */
		$row['host2']
    );
	$cook=array();
	$t=explode("\r\n\r\n",$t);
	$header=$t[0];
	$header=substr($header,strpos($header,"OK")+4);
	$header=explode("\r\n",$header);
	print_r($header);
	foreach ( $header as $k=> $v ) {
		if ( strpos($v, "Set-Cookie")===false ) {
			
		}else{
			$cook[$k]=substr((string)$v,12);
		}
	}
	$session_id=1;
	$select="select cookie from clk_session where session_id=".$session_id;
	$query2=_query($select,"");
	$row_cook=mysql_fetch_assoc($query2);	
	$update="update clk_session set 
					type_id=".$row['id'].",
					cookie='".$row_cook['cookie'].implode($cook,";").";' where session_id=".$session_id;
	$query2=_query($update,"");
	echo "куки при записи в базу";
	print_r($cook);
	echo"
	";
  }
	
	
//print_r($t);
/*
$fp = fsockopen("ssl://obmenov.com", 443, $errno, $errstr, 30);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    $out = "GET / HTTP/1.1\r\n";
    $out .= "Host: obmenov.com\r\n";
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);
    while (!feof($fp)) {
        echo fgets($fp, 128);
    }
    fclose($fp);
	
	
}
*/

?>


