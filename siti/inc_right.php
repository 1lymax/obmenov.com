<div class="r-col">
                        <div class="rezerv">
                            <div class="rezerv-inn">
                                <h3>Резервы</h3>
                                <?php if ($urlid['site_curr2']==1) {?>
                                <p><span>Webmoney WMZ</span><b><?=floor($WM_amount_r['WMZ']);?></b></p>
                                <p><span>Webmoney WMR</span><b><?=floor($WM_amount_r['WMR']);?></b></p>
                                <p><span>Webmoney WMU</span><b><?=floor($WM_amount_r['WMU']);?></b></p>
                                <p><span>Webmoney WME</span><b><?=floor($WM_amount_r['WME']);?></b></p>
                                <p><span>Приват24 грн.</span><b><?=floor($WM_amount_r['P24UAH']);?></b></p>
                                <?php /*?><p><span>Wire EURO (IBAN)</span><b><?=floor($WM_amount_r['WIREEUR']);?></b></p><?php */?>
                                <?php }else{ 
								$select="select * from currency where server ".$urlid['curr']." and 
													active=1 and active2=0 
													and name not in ('SMS', 'USD', 'UAH') and left(name,2)!='LQ' 
													order by type3, extname";
								$query=_query($select,"");
								while ( $row=$query->fetch_assoc() ) {?>
                                <p><span><?=$row['extname_short']?></span><b><?=floor($WM_amount_r[$row['name']]);?></b></p>                               
                                <?php } 
								}?>

                            </div>
                        </div>
                        

                              <?php 
						if ( isset($_SESSION['authorized']) && isset($_SESSION['clid_num']) ) {
						
						$select = "select item_name.id, item_name.name as name, count(item_name.id) as count,
						items.itemid from item_name, items, prepaid_payment 
						where items.itemid=item_name.id 
						and prepaid_payment.clientid=".$_SESSION['clid_num']."
						and prepaid_payment.state='G'
						and prepaid_payment.authorized=1
						and prepaid_payment.item=items.id 
						group by item_name.name order by itemid desc limit 0,5";
						$query=_query($select,"inc_right.php");
						?>
 					<div class="directions">
                            <h3>Ваши покупки</h3>
                            <div class="directions">                       
                        <?php
						if ( $query->num_rows != 0 ) { 
							while ( $popular=$query->fetch_assoc() ) {?>
                                <p><a href="<?=$siteroot?>shop.php?i=<?=$popular['id']?>">
                                <?=$popular['name']?></a></p>
                        <?php } 
						}else{?>
                            <p>Нет покупок</p>
						<?php } ?>
                             </div>
                        </div>
                        <?php } ?> 	
                        <div class="directions">
                        <?php if ( isset($_SESSION['authorized']) && isset($_SESSION['clid']) ) { 
							$select="select closed_exch.id, currin, currout,
									(select extname from currency where name=closed_exch.currin) as _currin,
									(select extname from currency where name=closed_exch.currout) as _currout
									from currency, closed_exch where clid='".$_SESSION['clid']."' 
									AND (select currency.active from currency where currency.name=closed_exch.currin)=1
									AND (select currency.active from currency where currency.name=closed_exch.currout)=1
									group by closed_exch.id";
							$query=_query($select,"");
							if ($query->num_rows!=0) { ?>
                            <h3>Дополнительные направления</h3>
                            <?php }
							while ($row=$query->fetch_assoc()) { 
							?>
                            <p><a href="<?=$siteroot?>exchange/<?=$row['_currin']."-".$row['_currout']?>.html">
                                <?=$row['_currin']." &gt; ".$row['_currout']?></a></p>
                            <?php
							}
							} ?>
                        
                        <br />
                            <h3>Популярные направления</h3>
                            <div class="directions">
                              <?php 
						if ( isset($_SESSION['authorized']) ) {
						$popular_condition=" and orders.clid='".(isset($_SESSION['clid']) ? $_SESSION['clid'] : $clid)."'";
						
						
							$popular_condition=" and orders.clid='".(isset($_SESSION['clid']) ? $_SESSION['clid'] : $clid)."'";
						
						$select = "SELECT count(orders.id) as count, orders.currin, orders.currout,
								(select extname from currency where name=orders.currin) as _currin, 
								(select extname from currency where name=orders.currout) as _currout FROM orders, payment, currency
								WHERE payment.orderid = orders.id
								AND payment.canceled =1
								AND orders.ordered =1
								AND (
									orders.currin = currency.name
									OR orders.currout = currency.name
								)
								AND currency.server ".$urlid['curr']
								.$popular_condition.
								"
								AND (select currency.active from currency where currency.name=orders.currin)=1
								AND (select currency.active from currency where currency.name=orders.currout)=1
								group by currin, currout 
								order by count desc limit 0, 8";
						$query=_query($select,"inc_right.php");
							
						while ($popular=$query->fetch_assoc()) {?>
                                <p><a href="<?=$siteroot?>exchange/<?=urlencode($popular['_currin']."-".$popular['_currout'])?>.html" title=" Вы совершили <?=$popular['count']?> обменов в этом направлении">
                                <?=$popular['_currin']." &gt; ".$popular['_currout']?></a></p>
                                
                            <?php } ?>
                            
                            <div class="otzyv"></div>
                            <?php 
						}
						$popular_condition=" and orders.clid='".$clid."'";
						$popular_condition='';
						
						if ( isset($_SESSION['authorized']) ) {
							$popular_condition=" and orders.clid='".$clid."'";
						}
						$select = "SELECT * from popular, currency 
									where (popular.currin=currency.name 
										   or popular.currout=currency.name) 
									and currency.server=".$urlid['site_curr2']." 
									AND (select currency.active from currency where currency.name=popular.currin)=1
									AND (select currency.active from currency where currency.name=popular.currout)=1
									limit 0,14";
						$query=_query($select,"inc_right.php");
							$c1="";$c2="";
							while ($popular=$query->fetch_assoc()) {
								if ( $c1!=$popular['_currin'] && $c2!=$popular['_currout'] ) {
									?>
                               	 	<p><a href="<?=$siteroot?>exchange/<?=urlencode($popular['_currin']."-".$popular['_currout'])?>.html">
                                	<?=$popular['_currin']." &gt; ".$popular['_currout']?></a></p>
                                
                            <?php }
								$c1=$popular['_currin'];$c2=$popular['_currout'];
							} ?>
                            </div>
                        </div>
                        <?php 
		 				$query="select name, direction, message, time from comment where active=1 order by id  desc limit 0,12";
		  				$comment_query=_query($query,1);
		 
		  				
			  			 ?>
                        <?php /*?><div class="otzyvy">
                            <div class="otzyvy-inn">
                                <h3>Отзывы<span>пользователей</span></h3>
                                <?php while ( $comment=mysql_fetch_assoc($comment_query) ){ ?>
                                <div class="otzyv">
                                    <p class="otzyv-name">Имя: <?=$comment['name'];?></p>
                                    <p class="otzyv-date">Дата: <?=substr($comment['time'],0,16);?></p>
                                    <?php if ( $comment['direction']!="-" ) { ?>
                                    <p class="otzyv-text"><?=$comment['direction'];?></p>
                                    <?php } ?>
                                    <p class="otzyv-text"><?=$comment['message'];?></p>
                                </div>
                                <?php } 
								$query="select name, direction, message, time from comment where active=1 order by id  desc";
		  						$comment_query=_query($query,1);
		  						?>
                              </div>
                            <a href="<?=$siteroot?>comment.php" class="otzyvy-all">все (<?=mysql_num_rows($comment_query)?>)</a>
                        </div><?php */?>
                        <?php if ( $urlid['site_curr2']==1 ) { ?>
                        <div class="stat">
                            <div class="stat-inn">
                                <div class="stat-attestat">
                                    <p class="stat-seller">Аттестат продавца</p>
                                    <p class="stat-wmid">wmid 219391095990
                                    <a href="https://passport.webmoney.ru/asp/certView.asp?wmid=219391095990" target="_blank"><img src="<?=$siteroot?>i/icq-online.png" alt="" /></a></p>
                                    <p class="stat-wmid">wmid 418941129503
                                    <a href="https://passport.webmoney.ru/asp/certView.asp?wmid=418941129503" target="_blank"><img src="<?=$siteroot?>i/icq-online.png" alt="" /></a></p>
                                </div>
                                <?php /*<p class="exchanges">Пользователей: 1899</p>
                                <p class="exchanges">Партнеров: 473</p> */ ?>
                            </div>
                        </div>
                        <?php } ?><br /><br />

                        
                    </div>