<?php 
	require_once("/var/www/webmoney_ma/data/www/obmenov.com/Connections/ma.php");
	require_once($serverroot."siti/mail.php");
	$pop3 = new Pop3Worker();
	$pop3->connectPop3Server();
    $pop3->authPop3Server("balance-update@obmenov.com","MbmTq0o6");
    $pop3->startWork();
    $pop3->processMail("mail_balance"); // Ознакомимся позже
	$pop3->disconnectPop3Server();
	
	$email=array("support@obmenov.com","maxim@lysogorov.net","vniknis@mail.ru");
	
	$select="select * from mail_balance where status=0 and email in ('".implode("','",$email)."')";
	$query=_query($select,"");
	while ( $row=$query->fetch_assoc() ){
		//$row['body']=str_replace(array(" ",""),"",$row['body']);
		$arr=explode("\r\n",$row['body']);
		//print_r($arr);
		
		while (list($name, $value) = each($arr)) {
			//echo $name." ".$value."\r\n";
			if ( $value=="" ){
				//echo "no\r\n";
			}else{
				$value_=explode(":",$value);
				//print_r($value_);
				$select="select name from currency where name='".mysql_real_escape_string($value_[0])."' 
													and name in ('".strtoupper(implode("','",$rbanks))."')";
				//echo $select."\r\n";
				$query2=_query($select,"");
				if ( mysql_num_rows($query2)==1 ){
					$update="insert into amounts (val, amount, account) values (
										'".mysql_real_escape_string($value_[0])."',
										".mysql_real_escape_string((float)$value_[1]).",
										'".mysql_real_escape_string($value_[0])."'
										)";
					$query2=_query($update,"");
					$changed[$value_[0]]=(float)$value_[1];
					
				}
			}
		}
		$update="update mail_balance set status=1 where messid='".$row['messid']."'";
		$query2=_query($update,"");
		mail($row['email'],"Autoreply from update-balance","Это информационное сообщение, которое сигнализирует лишь о том, что данные были успешно обработаны.\r\nСледующие данные были занесены в базу:\r\n".str_replace("Array\r\n","",print_r($changed,1)));
	}

?>