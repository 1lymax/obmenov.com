<?php
		$dont_insert_client=1;
		$max_predel_for_courses=1.05;
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php");
		require_once("/var/www/webmoney_ma/data/www/obmenov.com/siti/_header.php");
		_query('SET character_set_database = cp1251',"");
		_query('SET NAMES cp1251',"");
$structure = $parser->Parse(curlinit("https://liqpay.com/exchanges/exchanges.cgi"), DOC_ENCODING);
$transformed = $parser->Reindex($structure, true);
@ini_set ("display_errors", true);
//$structure = curlinit("https://privat24.privatbank.ua/p24/accountorder?oper=prp&exchange&PUREXML&coursid=5", "privatbank.ua.crt");
//$structure=simplexml_load_string($structure);
	//print_r($transformed);
	// рубль
	$val[6]='rur';
	$val[7]='eur';
	$val[8]='usd';
	//die();
	foreach ( $val as $key=>$value ) {
	  if ( get_setting("update_".$value."_balance") ) {	
		if ( isset($transformed['rates'][$value]['uah']) ){
			$val2['BUY']=$transformed['rates'][$value]['uah'];
			$val2['SALE']=round(1/$transformed['rates']['uah'][$value],3);
			
			$query_course = "SELECT id, min, max, date 
						FROM course_".$value." ORDER BY 			
						date desc";
			$course =  _query($query_course, 'function.php 7');
			$row_course = $course->fetch_assoc();
			if ( $row_course['max']!=$val2['SALE'] || $row_course['min']!=$val2['BUY'] ) {
				$update = "INSERT INTO course_".$value." (min, max, `date`) VALUES (".$val2['BUY'].","
																	.$val2['SALE'].",CURRENT_TIMESTAMP())";
				if ( ( $row_course['max']/$max_predel_for_courses > $val2['SALE'] || 
						$row_course['max']*$max_predel_for_courses < $val2['SALE'])  &&
					( $row_course['min']/$max_predel_for_courses > $val2['BUY'] || 
						$row_course['min']*$max_predel_for_courses < $val2['BUY']) ) {
					maildebugger("Обновляемый курс ".$value." отличается более чем на 2%
В базе: ".$row_course['min'].", ".$row_course['max']."
Импорт: ".$val2['BUY'].", ".$val2['SALE'].print_r($structure,1));
				}else{
					//echo $update;
					$query=_query($update, "");
				}
			}
    	}
	  }
	}
	
	
/*	if ( isset($transformed['rates']['rur']['uah']) ){
		//echo1
		$val_rur_min=round($transformed['rates']['rur']['uah'],4);
		$val_rur_max=round(round(1/$transformed['rates']['uah']['rur'],4),4);
		$query_course_rur = "SELECT id, min, max, date 
						FROM course_rur ORDER BY 			
						date desc limit 1";
		$course_rur =  _query($query_course_rur, 'function.php 7');
		$row_course_rur = mysql_fetch_assoc($course_rur);
		if ( $row_course_rur['max']!=$val_rur_max || $row_course_rur['min']!=$val_rur_min ) {
			if ( ( $row_course_rur['max']/$max_predel_for_courses > $val_rur_max || $row_course_rur['max']*$max_predel_for_courses < $val_rur_max)  &&
				( $row_course_rur['min']/$max_predel_for_courses > $val_rur_min || $row_course_rur['min']*$max_predel_for_courses < $val_rur_min) ) {
				maildebugger("Обновляемый курс рубля отличается более чем на 2%");
			}else{
				echo $row_course_rur['max']."=".$val_rur_max." ".$row_course_rur['min']."=".$val_rur_min;
				$update = "INSERT INTO course_rur (min, max, `date`) VALUES (".$val_rur_min.","
																	.$val_rur_max.",CURRENT_TIMESTAMP())";
				$query=_query($update, "");
			}
		}
	}

//https://liqpay.com/exchanges/exchanges.cgi

$structure = $parser->Parse(curlinit("https://liqpay.com/exchanges/exchanges.cgi"), DOC_ENCODING);

$transformed = $parser->Reindex($structure, true);
//print_r($transformed);
*/
?>