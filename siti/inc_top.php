<div class="header clear">
                    <a href="/index.php" class="logo"></a>
                    <div class="auth">
                    	<form action="<?=$siteroot?>login.php" method="post">
						<?php if ( !isset ($_SESSION['AuthUsername']) ) { ?>
                            <div class="form-col1">
                                <input type="text" name="user" onfocus="if(this.value=='Логин') this.value='';" onblur="if(this.value=='') this.value='Логин';" value="Логин" />
                                <input type="password" name="pass" onfocus="if(this.value=='Пароль') this.value='';" onblur="if(this.value=='') this.value='Пароль';" value="Пароль" />
                            </div>
                            <div class="form-col2">
                                <input type="submit" value="" />
                            </div>
                            <?php } else {?>
                            <div class="form-col1">
                              <div style="width:193px">
                            	<p class="auth-discount">Пользователь:</p> <p class="auth-reg"><a href="#"><?=$_SESSION['AuthUsername'];?></a> <?php /*?><?php if (isset($urlid['index.php']) ) { ?><p class="auth-discount"><a href="https://login.wmtransfer.com/GateKeeper.aspx?RID=<?=$urlid['index.php']?>"><?php if ( isset($_SESSION['WmLogin_WMID']) ) {?><?=$_SESSION['WmLogin_WMID']?><?php }else{ ?>WM-авторизация<?php }?></a> </p><?php }?></p><?php */?>
                              </div>
                            </div>
                            <div class="form-col2">
                             
                            </div>
                            <?php } ?>
                            <div class="form-col3">
                                <p class="auth-reg">
                                <?php if ( isset ($_SESSION['AuthUsername']) ) { ?>
                                <a href="<?=$siteroot?>cabinet.php?history">Кабинет</a>&nbsp;&nbsp;&nbsp; 
                                <a href="<?=$siteroot?>login.php?doLogout=true">Выход</a>
                                <?php }else{ ?>
                                <a href="<?=$siteroot?>register.php">Регистрация</a>
                                <?php } ?>
                                </p>
                                <p class="auth-discount">Ваша скидка: <a href="<?=$siteroot?>cabinet.php"><?=($discount['total']*100-100)?>%</a></p>
                                
                            </div>
                        </form>
                    </div>
                </div>

                <div class="nav">
                    <ul>
                    	<?php if ( $urlid['site_curr2']==2 ) { ?>
                    	<li><a href="https://obmenov.com">Обменов.ком</a></li>
                        <?php }elseif ( $urlid['site_curr2']==1 ) { ?>
                        <li><a href="http://obmenov.biz">Obmenov.biz</a></li>
                        <?php } ?>
                        <li><a href="<?=$siteroot?>register.php">Регистрация</a></li>
                        <?php /* if ( $urlid['site_curr2']==1 ) {?>
						<li><a href="<?=$siteroot?>shop.php">Магазин</a></li><?php } */?>
                        <li><a href="<?=$siteroot?>commission.php">Тарифы и комиссия</a></li>
                        <?php if ( $urlid['site_curr2']==2 ) {?>
                        <li><a href="<?=$siteroot?>partner_info.php">Партнерская программа</a></li>
                        <?php } ?>
                        <?php /* if ( $urlid['site_curr2']==1 ) {?>
                        <li><a href="<?=$siteroot?>gamepay.php">Игровая валюта</a></li><?php } */ ?>
                        <?php /*?><li><a href="http://forum.obmenov.com/viewtopic.php?f=10&t=88" target="_blank">Ответы на вопросы</a></li><?php */?>
                        <?php /*?><li><a href="http://forum.obmenov.com">Форум</a></li><?php */?>
                        <li><a href="<?=$siteroot?>about.php">О сервисе</a></li>
                        <li><a href="<?=$siteroot?>contacts.php">Контакты</a></li>
                    </ul>
                </div>