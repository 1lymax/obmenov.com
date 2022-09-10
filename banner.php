<?php //require_once('Connections/ma.php');
//mysql_select_db($database_ma, $ma);
//if (!isset($_SESSION)) {
//  session_start();
//}
//$clid= isset($_COOKIE['clid']) ? $_COOKIE['clid'] : session_id();
$partnerid = isset($_GET['pn']) ? $_GET['pn'] : 0;
//$referer= isset ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
$bannerid  = isset($_GET['bn']) ? $_GET['bn'] : 0;
//$agent= isset ($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";

//$_SESSION['ref']=$referer;
/*$referrer="INSERT INTO banner (`ref`, `scrname`, `clid`, `partnerid`, `ip`, `agent`) VALUES ('"
									.$referer."','"
									.$bannerid."', '"
									.$clid."', '"
									.$partnerid."', '"
									.$_SERVER['REMOTE_ADDR']."', '"
									.$agent."') ";*/
	//$Result = _query($referrer, 'banner1 '.$referrer." ".print_r($_POST,1));


	
	switch ($bannerid) {
		case '1' : header("Location: i/banners/88x31_1.gif");die();
		case '11' : header("Location: i/banners/88x31_2.gif");die();
		case '12' : header("Location: i/banners/100x100_1.gif");die();
		case '13' : header("Location: i/banners/100x100_2.gif");die();
		case '14' : header("Location: i/banners/486x60_1.gif");die();
		case '15' : header("Location: i/banners/486x60_2.gif");die();
		case '2' : header("Location: i/banners/banner2.gif");die();
		case '3' : header("Location: i/banners/banner3.png");die();
		case '4' : header("Location: i/banners/banner4.png");die();
		case '5' : header("Location: i/banners/banner5.png");die();
		case '6' : header("Location: i/banners/banner6.gif");die();
		case '7' : header("Location: i/banners/banner7.gif");die();

}

?>