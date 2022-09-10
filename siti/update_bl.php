<?php 
require_once('/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php');
@ini_set ("display_errors", true);

$select="select wmid, bl, bl_upd from clients where wmid!=0 and wmid!='' and ((bl_upd + interval 7 day) < now() or bl_upd='0000-00-00 00:00:00') group by wmid limit 0,1";
$query=_query2($select,"");
while ($row=$query->fetch_assoc()){
	echo "t=".microtime()."<br />";
    $wmid = file_get_contents('http://stats.wmtransfer.com/Levels/pWMIDLevel.aspx?wmid='.$row['wmid'].'&w=35&h=18&bg=0XDBE2E9'); 
    $img = imagecreatefromstring($wmid); 
	if ( !$img )die();
    for ($i = 4; $i < 35; $i++) 
{ 
for ($j = 3; $j < 15; $j++) 
{ 
$se[$i][$j] = (imagecolorat ($img, $i, $j)); 
         } 
          } 
          $is = false; 
          $w = ''; 

               if ( (itfalse(4) ==6 ) and (itfalse(5) ==7 ) and (itfalse(6) ==1 ) and (itfalse(7) ==2 ) and (itfalse(8) == 7 )  ) 
             { 
               $is = true; 
               $w='no'; 
             } 

          if (!( itfalse(18) or itfalse(20) or itfalse(19) or ($is) ) ) 
          { 
               //one digit 

                   $p1 =  itfalse(23,$se); 
                   $p2 =   itfalse(28,$se); 
                   $w = (iscount($p1, $p2)); 
                   $is = true; 
          } 

          if ( ! ( itfalse(13) or itfalse(14) or itfalse(12) or ($is)  )   ) 
          { 
               //two digit 

                   $p1 =  itfalse(17,$se); 
                   $p2 =   itfalse(22,$se); 
                   $w = (iscount($p1, $p2)); 

                  $p1 =  itfalse(25,$se); 
                   $p2 =   itfalse(30,$se); 
                   $w .= (iscount($p1, $p2)); 
                   $is = true; 
          } 

           if (!( itfalse(7) or itfalse(8) or itfalse(6) or ($is) ) ) 
          { 

               //tree digit 
                    $p1 = itfalse(11,$se); 
                   $p2 =  itfalse(16,$se); 
                  $w  = (iscount2($p1, $p2)); 

                  $p1 =  itfalse(18,$se); 
                   $p2 =  itfalse(23,$se); 
                   $w.=(iscount2($p1, $p2)); 

                   $p1 =  itfalse(25,$se); 
                   $p2 =  itfalse(30,$se); 
                   $w.= (iscount2($p1, $p2)); 
                    $is = true; 
          } 
          if ($is == false) 
          { 

              $p1 = itfalse(6,$se); 
                   $p2 =  itfalse(11,$se); 
                   $w = (iscount2($p1, $p2)); 

                   $p1 = itfalse(13,$se); 
                   $p2 =  itfalse(18,$se); 
                   $w.= (iscount2($p1, $p2)); 

                   $p1 = itfalse(20,$se); 
                   $p2 =  itfalse(25,$se); 
                   $w.= (iscount2($p1, $p2)); 

                   $p1 = itfalse(27,$se); 
                   $p2 =  itfalse(32,$se); 
                   $w.= (iscount2($p1, $p2)); 
          } 
          	$bl= $w; 
	
	
	if ( $bl=="no" ) $bl=$row['bl'];
	echo microtime()."<br />";
	$update="update clients set 
				bl=".$bl.",
				bl_upd=NOW() where wmid=".$row['wmid'];
	$result=_query($update,"");
	$insert="insert into clients_bl ( wmid, bl, bl_upd ) values (
								".$row['wmid'].",
								".$bl.",
								NOW())";
	$result=_query($insert,"");
	echo microtime()."<br />";
	echo $update."<br />";
}
	

function itfalse($x) 
          { 
              global $se;        $ok=0; 
             for ($j = 3; $j < 15; $j++) 
               { 
                   if ($se[$x][$j] == 40)   { $ok++; } 
               }     return $ok; 

          } 
          function iscount2($x,$y) 
          { 
           if (($x == 7) and ($y == 7)) { return 0; } 
           if (($x == 1) and ($y == 0)) { return 1; } 
           if (($x == 3) )  { return 2; } 
           if (($x == 2) and ($y == 6)) { return 3; } 
           if (($x == 2) and ($y == 1))  { return 4; } 
           if (($x == 6) and ($y == 5))  { return 5; } 
           if (($x == 7) and ($y == 4))  { return 6; } 
           if (($x == 1) and ($y == 2))  { return 7; } 
           if (($x == 6) and ($y == 6))  { return 8; } 

           if (($x == 4))  { return 9; } 
          } 
                    function iscount($x,$y)  //1 2 digit 
          { 
           if ( ($x == 1) and ($y == 0) )  { return 1; } 
           if (($x == 5) and ($y == 4)) { return 2; } 
           if (($x == 2) and ($y == 7)) { return 3; } 
           if (($x == 2) and ($y == 10))  { return 4; } 
           if (($x == 7) and ($y == 5)) { return 5; } 
           if (($x == 8) and ($y == 5))  { return 6; } 
              if (($x == 1) and ($y == 3))  { return 7; } 
           if (($x == 7) and ($y == 7))  { return 8; } 
           if (($x == 5) and ($y == 8))  { return 9; } 
            if (($x == 8) and ($y == 8))  { return 0; } 

          } 

?>