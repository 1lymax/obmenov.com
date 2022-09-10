<?php 
$query_course_usd = "SELECT course_usd.id, course_usd.`min`, course_usd.`max`, course_usd.`date` FROM course_usd ORDER BY course_usd.`date` desc limit 1";
$course_usd =  _query($query_course_usd, 'function.php 7');
$row_course_usd = $course_usd->fetch_assoc();
$totalRows_course_usd = $course_usd->num_rows;
//курс рубля
$query_course_rur = "SELECT course_rur.id, course_rur.`min`, course_rur.`max`, course_rur.`date` FROM course_rur ORDER BY course_rur.`date` desc limit 1";
$course_rur = _query($query_course_rur, 'function.php 8');
$row_course_rur = $course_rur->fetch_assoc();
$totalRows_course_rur = $course_rur->num_rows;

//курс евро
$query_course_eur = "SELECT course_eur.id, course_eur.`min`, course_eur.`max`, course_eur.`date` FROM course_eur ORDER BY course_eur.`date` desc limit 1";
$course_eur = _query($query_course_eur, 'function.php 9');
$row_course_eur = $course_eur->fetch_assoc();
$totalRows_course_eur = $course_eur->num_rows;




$courses['UAH']['UAH']=1;
$courses['USD']['USD']=1;
$courses['EUR']['EUR']=1;
$courses['RUR']['RUR']=1;
$courses['USD']['UAH']=$row_course_usd['min']+($row_course_usd['max']-$row_course_usd['min'])/2;
$courses['UAH']['USD']=1/$courses['USD']['UAH'];
$courses['RUR']['UAH']=$row_course_rur['min']+($row_course_rur['max']-$row_course_rur['min'])/2;
$courses['UAH']['RUR']=1/$courses['RUR']['UAH'];

$courses['EUR']['UAH']=$row_course_eur['min']+($row_course_eur['max']-$row_course_eur['min'])/2;
$courses['UAH']['EUR']=1/$courses['EUR']['UAH'];
$courses['USD']['RUR']=$courses['USD']['UAH']/$courses['RUR']['UAH'];
$courses['RUR']['USD']=1/$courses['USD']['RUR'];
$courses['EUR']['USD']=$courses['EUR']['UAH']/$courses['USD']['UAH'];
$courses['USD']['EUR']=1/$courses['EUR']['USD'];
$courses['EUR']['RUR']=$courses['EUR']['UAH']/$courses['RUR']['UAH'];
$courses['RUR']['EUR']=1/$courses['EUR']['RUR'];

$select="SELECT value,  `to` FROM ( SELECT * FROM course_cb WHERE  `from` =  'RUR' ORDER BY TIME DESC ) AS a GROUP BY  `to`";
$query=_query($select,"");
while ( $row=$query->fetch_assoc() ) {
//	if ( $row['to']=='UAH' ){
//		$courses['RUR'][$row['to']]=1/$row['value'];
//		$courses[$row['to']]['RUR']=$row['value'];	
//	}else{
		$courses['RUR'][$row['to']]=1/$row['value'];
		$courses[$row['to']]['RUR']=$row['value'];
//	}
}
// http://cbr.ru/scripts/Root.asp?Prtid=SXML
// http://cbr.ru/scripts/XML_daily.asp
//курс wmuwmz

//maildebugger($courses['RUR']['USD']);
$WMZWMU_base = get_course_from_db ("WMZ", "WMU");
$courses['WMZ']['WMU'] = get_course_with_addon ('WMZ', 'WMU');
$courses['WMU']['WMZ'] = 1/$courses['WMZ']['WMU'];

//курс wmrwmz
$WMZWMR_base = get_course_from_db ("WMZ", "WMR");
$courses['WMZ']['WMR'] = get_course_with_addon ('WMZ', 'WMR');
$courses['WMR']['WMZ'] = 1/$courses['WMZ']['WMR'];

//курс wmewmz
$WMEWMZ_base = get_course_from_db ("WME", "WMZ");
$courses['WME']['WMZ'] = get_course_with_addon ('WME', 'WMZ');
$courses['WMZ']['WME'] = 1/$courses['WME']['WMZ'];

//курс wmuwme
$WMEWMU_base = get_course_from_db ("WME", "WMU");
$courses['WME']['WMU'] = get_course_with_addon ('WME', 'WMU');
$courses['WMU']['WME'] = 1/$courses['WME']['WMU'];

//курс wmewmr
$WMEWMR_base = get_course_from_db ("WME", "WMR");
$courses['WME']['WMR'] = get_course_with_addon ('WME', 'WMR');
$courses['WMR']['WME'] = 1/$courses['WME']['WMR'];

//курс wmuwmr
$WMRWMU_base = get_course_from_db ("WMR", "WMU");
$courses['WMR']['WMU'] = get_course_with_addon ('WMR', 'WMU');
$courses['WMU']['WMR'] = 1/$courses['WMR']['WMU'];
// недостающие курсы для схожих направлений

?>