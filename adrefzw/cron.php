<?php  
require_once ("/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php");
include("/var/www/webmoney_ma/data/www/obmenov.com/adrefzw/wmxml.inc.php");

@ini_set ("display_errors", true);
$money=array();


update_course ('WMZ','WMU');
update_course ('WMR','WMU');
update_course ('WMZ','WMR');
update_course ('WME','WMZ');
update_course ('WME','WMR');
update_course ('WME','WMU');



?>