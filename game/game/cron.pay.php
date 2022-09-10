<?
//крон :)
		$dont_insert_client=1;
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php");
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/function.php");
		include '/var/www/webmoney_ma/data/www/obmenov.com/game/game/wm.class.php';

$wm = new wmbank;
//$wm->updateProjects();$wm->updateCourses();

$select="select `id` as wmreq_id , `pid` as id , `webmId` , `purse` , `timestamp` , `wm_amount` , `wm_currency` , 
				`rur_amount` , `nick` , `status` , `projectid` , `time` 
		from gamedealer_wmreq where left(wm_currency,3)='MCV' and (time + interval 1 hour > now())  order by pid desc limit 0,10";
$query=_query2($select, "");
while ( $row=$query->fetch_assoc() ) {
	require_once($GLOBALS['serverroot']."siti/lp.class.php");
	$liqpay= new liqpay($GLOBALS['lq_id']);
	$response=$liqpay->check_trans($row,1);
	if ( !isset($response->status) ) continue;
	if ( $response->status!='failure' ) {
		$status = $response->transaction->status;
	}else{
		$status='failure';
	}
	if ($response->status == 'success' ){
		$query=_query("select * from gamedealer_wmreq where pid=".$response->transaction_order_id,"wmclass-checkmcv");
		$row=$query->fetch_assoc();
		$data['pid']=	$response->transaction_order_id;
		$data['wmid']=	"";
		$data['purse']=	$response->transaction->from;
        $data['amount'] = (float)$response->transaction->amount;
        $data['currency']= substr($row['wm_currency'],3,3);
		$data['wm_currency']=$row['wm_currency'];
        $data['nick']=$row['nick'];
		$data['projectid'] = (int)$row['projectid'];
 		$wm->update_pay($data);
	}
}
 
$wm->checkCron();
//echo $_SERVER['DOCUMENT_ROOT'];
//mail('2nik@ua.fm','wmbanka','cron wmbanka');
//phpinfo();
?>