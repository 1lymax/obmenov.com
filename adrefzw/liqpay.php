<?php 
		require_once("../Connections/ma.php");
		//require_once("../function.php");
		require_once("../siti/p24api.php");
		require_once("../siti/_header.php");

	$xml="<request>      
		<version>1.2</version>
		<result_url>https://top.obmenov.com/cnb12resp.php</result_url>
		<server_url>https://top.obmenov.com/cnb12resp.php</server_url>
		<merchant_id>$pb_merchant_id</merchant_id>
		<order_id>Prepai13</order_id>
		<amount>1</amount>
		<currency>USD</currency>
		<description>retail</description>
		<default_phone></default_phone>
		<pay_way>card</pay_way> 
		</request>
		";
//$pb_phone $pb_method
	$xml_encoded = base64_encode($xml); 
	$lqsignature = base64_encode(sha1($pb_signature.$xml.$pb_signature,1));
	echo "<pre>".$xml."</pre>";
echo("<form action='$pb_url' method='POST'>
	 
	 
     <input type='hidden' name='operation_xml' value='$xml_encoded' />
     <input type='hidden' name='signature' value='$lqsignature' />
	<input type='submit' value='Pay'/>");

?>	
<script language="javascript" src="../fun.js"></script>
<body onLoad="setElementOpacity(opacityRow, 0.4);">
<table><tr>
  <td align="center" >
  <img src="../images/banners/satyuzhny_logo.jpg" onmouseover='setElementOpacity(this, 0.75);' onmouseout='setElementOpacity(this, 0.4);' id="opacityRow" />
  
  </td></tr></table>
  </body>