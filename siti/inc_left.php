					<div class="l-col">
                    <noscript><span class="otzyv-date">Для корректной работы сайта требуется поддержка javascript в вашем браузере.
                            </span></noscript>
                       <?php /*?> <div class="rotator">
                        	
                            <?php /*<a href="<?=$siteroot?>exchange/P24USD-WMZ"><img src="<?=$siteroot?>img/vvod1.gif" width="198" height="120" alt="" /></a>
                        </div><?php */?>
                          <div class="aff">
                            <a href="<?=$siteroot?>partner_info.php"><h3>Партнерская программа</h3></a>
                            <div class="aff-inn">
                                <p>Зарегистрируйся сейчас и получай до 
20% прибыли с обменов!<br>до 5 центов за клик!</p>
                                <a href="<?=$siteroot?>partner.php" class="aff-cab">Кабинет 
партнера</a>
                            </div>
                        </div>
                        <?php // =====================================  ?>   
 		<?php /* if ( $urlid['site_curr2']==1 ) {
				$select="select * from gamedealer_projects where active=0 order by RAND() LIMIT 0,6";
				$query=_query($select, "game.php 1"); 
				$i=0;
				 ?>
                        <div class="games">
                            <h3>Игровая валюта</h3>
                            <div class="game-banners clear">
                            
                            
                            <?php while ( $game=$query->fetch_assoc() ) { ?>

						<a href="<?=$docroot."gamepay.php?game=".$game['projectid']?>">
        				<img title="<?=$docroot.$game['title']?>" alt="<?=$game['title']?>" border="0" id="g<?=$game['id']?>" 
                        src="<?=str_replace("http://gamedealer.ru/img3/",$docroot."i/game/",$game['img'])?>"  ></a>
					 <?php } 
				$select="select * from gamedealer_projects where active=0 order by title";
				$query=_query($select, "game.php 1");?>
                            </div>
                            <div class="game-pay">
  								<form action="<?=$siteroot?>gamepay.php" method="get" id="gamepay" name="gamepay">
    							 <select style="width:180px" id="projectid" name="game" onchange="document.gamepay.submit();">
    							 <option value="0">Оплатить игру ---------</option>
						 	    <option value="0" style="font-weight:normal">-------------------------------------------</option>
    							<?php while ( $game=$query->fetch_assoc() ) {?>
    							<option value="<?=$game['projectid']?>"><?=$game['title']?></option>
    							<?php } ?>
    							</select> <!--a href="javascript:d.$('gamepay').submit()">&raquo;</a><br-->
    							<p><a href="<?=$siteroot?>game.php">Посмотреть все (<?=mysql_num_rows($query)?>) &raquo;</a></p>
    							</form>
                            </div>
                        </div>
                 <?php } */ ?>
                 
                        <?php /*?><div class="bank">
                            <h3><img src="<?=$siteroot?>i/logos/p24.png" /> Наш банк</h3>
                            <div class="bank-inn">
                                <table>
                                    <col width="50%" />
                                    <col />
                                    <col />
                                    <thead>
                                        <tr>
                                            <td></td>
                                            <td>Ввод</td>
                                            <td>Вывод</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ( $urlid['site_curr2']==1 ) { ?> 
                                        <tr>
                                            <td class="bank-wm">WMU</td>
                                            <td><?=$money['P24UAH']['WMU']['value'] ?></td>
                                            <td><?=(1-($money['WMU']['P24UAH']['value']-1));?></td>
                                        </tr>
                                        <tr>
                                            <td class="bank-wm">WMZ</td>
                                            <td><?=round($courses['WMZ']['P24UAH']*$money['P24UAH']['WMZ']['value'],2);?></td>
                                            <td><?=round($courses['WMZ']['P24UAH']/$money['WMZ']['P24UAH']['value'],2);?></td>
                                        </tr>
                                        <tr>
                                            <td class="bank-wm">WMR</td>
                                            <td><?=round($courses['WMR']['P24UAH']*$money['P24UAH']['WMR']['value'],3);?></td>
                                            <td><?=round($courses['WMR']['P24UAH']/$money['WMR']['P24UAH']['value'],3);?></td>
                                        </tr>
                                        <tr>
                                            <td class="bank-wm">WME</td>
                                            <td><?=round($courses['WME']['P24UAH']*$money['P24UAH']['WME']['value'],3);?></td>
                                            <td><?=round($courses['WME']['P24UAH']/$money['WME']['P24UAH']['value'],3);?></td>
                                        </tr>
                                        <tr>
                                            <td class="bank-ks">KS</td>
                                            <td><?=round($money['P24UAH']['KS']['value'],3);?></td>
                                            <td>-</td>
                                        </tr>
                                        <?php }elseif ( $urlid['site_curr2']==2 ) { ?>
                                        <tr>
                                            <td class="bank-lr">LR USD</td>
                                            <td><?=$money['P24USD']['LRUSD']['value'] ?></td>
                                            <td><?=(1-($money['LRUSD']['P24USD']['value']-1));?></td>
                                        </tr>
                                        <tr>
                                            <td class="bank-pm">PM USD</td>
                                            <td><?=$money['P24USD']['PMUSD']['value'] ?></td>
                                            <td><?=(1-($money['PMUSD']['P24USD']['value']-1));?></td>
                                        </tr>
                                        <?php } ?>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div><?php */?>
                        <div class="bank">
                            <h3><img src="<?=$siteroot?>i/logos/mc.png" /> VISA USD<br /><img src="<?=$siteroot?>i/logos/mastercard.png" /> Mastercard USD </h3>
                            <div class="bank-inn">
                                <table>
                                    <col width="50%" />
                                    <col />
                                    <col />
                                    <thead>
                                        <tr>
                                            <td></td>
                                            <td>Ввод</td>
                                            <td>Вывод</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ( $urlid['site_curr2']==1 ) { ?> 
                                        <tr>
                                            <td class="bank-wm">WMU</td>
                                            <td><?=round($courses['WMU']['MCVUSD']*$money['MCVUSD']['WMU']['value'],2);?></td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="bank-wm">WMZ</td>
                                            <td><?=round($courses['WMZ']['MCVUSD']*$money['MCVUSD']['WMZ']['value'],2);?></td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="bank-wm">WMR</td>
                                            <td><?=round($courses['WMR']['MCVUSD']*$money['MCVUSD']['WMR']['value'],3);?></td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="bank-wm">WME</td>
                                            <td><?=round($courses['WME']['MCVUSD']*$money['MCVUSD']['WME']['value'],3);?></td>
                                            <td>-</td>
                                        </tr>
                                        <?php }elseif ( $urlid['site_curr2']==2 ) { ?>
                                        <?php /*?><tr>
                                            <td class="bank-lr">LR USD</td>
                                            <td><?=$money['MCVUSD']['LRUSD']['value'] ?></td>
                                            <td><?=$money['LRUSD']['MCVUSD']['value'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bank-pm">PM USD</td>
                                            <td><?=$money['MCVUSD']['PMUSD']['value'] ?></td>
                                            <td><?=$money['PMUSD']['MCVUSD']['value'] ?></td>
                                        </tr><?php */?>
                                        <?php } ?>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php   /* 
						
						if ( $urlid['site_curr2']==1 ) {
							
							$select="SELECT DISTINCT items.id, item_name.price, item_name.unit,
									items.state, item_name.name, item_name.url, item_name.id as nameid
									FROM items, item_name WHERE ((reserved IS NULL) OR 
									(reserved + INTERVAL 2 MINUTE < NOW())) AND items.itemid=item_name.id 
									AND items.state='Y' GROUP BY item_name.name 
									ORDER BY RAND() limit 0, 5";
	  							 $query = _query2($select, 'prepaid.php 1');
	   					?>
                        
                        <div class="random">
                            <h3><a href="<?=$siteroot?>shop.php">Магазин <img src="<?=$siteroot?>i/icq-online.png" alt="" /></a></h3>
                            <div class="random-inn">
                                <table>
                                    <col width="63%" />
                                    <col />
                                    <col />
                                    <thead>
                                        <tr>
                                            <td></td>
                                            <td>$</td>
                                            <td>грн</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while( $it = mysql_fetch_array($query)){ ?>
                                        <tr>
                                            <td class="random-good"><a href="<?=$siteroot?>shop.php?i=<?=$it['nameid']?>"><?=$it['name']?>>></a></td>
                                        <?php if ( $it['unit']=="WMU" ) { ?>
    										<td><?=(round($it['price']*$courses['WMU']['WMZ']*1.02,1))?></td>
    										<td><?=round($it['price'],1)?></td>
    									<?php } ?>
    									<?php if ( $it['unit']=="WMZ" ) { ?>
   											<td align="center"><?=round($it['price'],1)?></td>
    										<td align="right"><?=(round($it['price']*$courses['WMZ']['WMU'],1))?></td>
    									<?php } ?>
    									<?php if ( $it['unit']=="WMR" ) { ?>
   											<td align="center"><?=round($it['price']*$courses['WMR']['WMZ'],1)?></td>
    										<td align="right"><?=(round($it['price']*$courses['WMR']['WMU'],1))?></td>
    									<?php } ?>  
                                        </tr>
                                            <?php }
										$select="SELECT DISTINCT items.id as total, item_name.price, item_name.unit, items.state, item_name.order,
											 item_name.name, item_name.url, item_name.rules FROM items, item_name WHERE 
											 items.itemid=item_name.id AND items.state='Y' GROUP BY item_name.name";
										$query = _query($select, 'prepaid.php 1');
										$total=mysql_num_rows($query);
										?>
                                        
                                    </tbody>
                                </table>    
                            </div>
                        </div>
                        <?php } */ ?>
                        <div class="support">
                            <h3>Поддержка</h3>
                            <?php /*?><p class="support-phone">+38 (093) 0-151-005</p><?php */?>
							<p class="support-phone"><img src="<?=$siteroot?>i/email.gif" alt="" width="123" height="11" /></p>
                            <p class="support-online">skype: obmenov.com</p>
                            <p class="support-online">Телефон</p>
                            <p class="support-icq">(38)096-022-5798 <?php /*?><img src="<?=$siteroot?>i/icq-online.png" width="10" height="10" /><?php */?></p>
                            <br /><br />
<script src="<?=$siteroot?>book.js" type="text/javascript"></script><noscript><a href="http://obmenov.com"><img src="<?=$siteroot?>i/button.gif" width="136" height="16" alt="Социальные закладки" border="0"></a></noscript>
                       </div><br />
                    
                    </div>
