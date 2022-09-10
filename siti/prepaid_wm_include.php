<?
// $Id: wm_include.php,v 1.6 2006/07/31 14:11:41 asor Exp $
//
//            ***************************************************
//            *** Extra Options Section                       ***
//            ***************************************************

require_once('prepaid_wm_config.php');

//////////////////////////////////////////////////
// wm_GetSign - creating signature
// Paramteres:
//   $inStr  - string to be signed
// Returns :
//   signed string 
function wm_GetSign($inStr)
{ global $WM_WMSIGNER_PATH;

$descriptorspec = array(
   0 => array("pipe", "r"),
   1 => array("pipe", "w"),
   2 => array("pipe", "r") );
$process = proc_open($WM_WMSIGNER_PATH, $descriptorspec, $pipes );
if (is_resource($process)) {
    fwrite($pipes[0], "$inStr\004\r\n");
    fclose($pipes[0]);
    $s = fgets($pipes[1], 133);
    fclose($pipes[1]);
    $return_value = proc_close($process);
    return $s;
   }
}
/*
// For PHP < 4.3.0
function wm_GetSign($inStr)
{ global $WM_WMSIGNER_PATH;
// Starts WMSigner and transferring data to be signed 
   $fp = popen('/bin/echo -ne "'.$inStr.'\004\r\n" |'.$WM_WMSIGNER_PATH, "r");
    $s = fgets($fp, 133);
    pclose($fp);
    if(strlen($s)==132){
        return $s;
    }else{
        return;
    };
}
*/


///////////////////////////////////////////////////////////////////////////////
// wm_HttpsReq - https-request towards webmoney certification center
//Parameters :
//   $addr  -Request address (starting from script title)
//           for example "/cgi-bin/myscript.cgi?PRM1=VAL1"
// Returns :
//   Https request result (without header)
function wm_HttpsReq($addr)
{ global $WM_CACERT;

  $ch = curl_init("https://w3s.webmoney.ru".$addr);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
#  If WebMoney CA root certificate is not installed into SSL, state path to it:
  curl_setopt($ch, CURLOPT_CAINFO, $WM_CACERT);
# Attention!Do not use curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE)!
# It can allow DNS  attack.  
  $result=curl_exec($ch);
  if( curl_errno($ch) != 0 ) {
    die('CURL_error: ' . curl_errno($ch) . ', ' . curl_error($ch));
  };
  curl_close($ch);
  return $result;
}

?>
