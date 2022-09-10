<?php require_once('../Connections/ma.php'); 
 require_once($serverroot.'siti/class.php');
 require_once("top.php");
 
 
 
 
 $select="select * from settings";
 $query=_query($select,"");
 
 
 ?>
 <form action="set.php" method="post">
 <table>
 <?php
 while ( $row=$query->fetch_assoc() ) {
	?>
    <tr>
    <td>
    <?php if ( $row['value']==1 || $row['value']==0 ) { ?>
      <input type="checkbox" name="value" value="<?=$row['id']?>"> 
      <?=$row['name']?>
    <?php }else{ ?>
      <input type="text" name="value" value="<?=$row['value']?>">
    <?php } ?>
   </td>
    <td><?=$row['name']?></td>
    </tr>
	
	
	
	<?php } ?>
 </table>
 </form>