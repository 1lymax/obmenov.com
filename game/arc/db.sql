
CREATE TABLE `gamedealer_courses` (
  `id` int(11) NOT NULL auto_increment,
  `currency` varchar(100) NOT NULL,
  `value` float NOT NULL,
  `timestamp` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;


CREATE TABLE `gamedealer_payments` (
  `id` int(11) NOT NULL auto_increment,
  `nick` varchar(255) character set cp1251 collate cp1251_ukrainian_ci NOT NULL,
  `projectid` int(11) NOT NULL,
  `amount` float NOT NULL,
  `currency` varchar(100) NOT NULL,
  `status` int(11) NOT NULL,
  `balance` float NOT NULL,
  `balance_after` float NOT NULL,
  `timestamp` varchar(100) NOT NULL,
  `pid` int(11) NOT NULL,
  `gdid` int(11) NOT NULL,
  `paymessage` varchar(255) character set cp1251 collate cp1251_ukrainian_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;



CREATE TABLE `gamedealer_projects` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) character set cp1251 collate cp1251_ukrainian_ci NOT NULL,
  `projectid` int(11) NOT NULL,
  `price_rub` float NOT NULL,
  `img` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `currency` varchar(255) character set cp1251 collate cp1251_ukrainian_ci NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=34 ;

INSERT INTO `gamedealer_projects` VALUES(1, 'Berserk-Online', 5, 40, 'http://gamedealer.ru/img3/icon_barserk2.gif', 'http://berserk.mail.ru/', '�����', 0);
INSERT INTO `gamedealer_projects` VALUES(2, 'COSMICS: ������������� �����', 27, 40, 'http://gamedealer.ru/img3/icon_cosmics.gif', 'http://www.cosmics.ru/', '�������', 0);
INSERT INTO `gamedealer_projects` VALUES(4, '����@mail.ru', 2, 40, 'http://gamedealer.ru/img3/icon_bags.gif', 'http://zhuki.mail.ru/', 'GB', 0);
INSERT INTO `gamedealer_projects` VALUES(3, 'FarLands', 53, 40, 'http://gamedealer.ru/img3/icon_farlands.gif', 'http://www.farlands.ru/', 'eZ', 0);
INSERT INTO `gamedealer_projects` VALUES(5, '�������: �������� �������� (���-�����)', 23, 40, 'http://gamedealer.ru/img3/icon_legend.gif', 'http://w1.dwar.ru/', '�����', 0);
INSERT INTO `gamedealer_projects` VALUES(7, '������������@mail.ru', 26, 40, 'http://gamedealer.ru/img3/icon_kingdom.gif', 'http://www.3kingdom.ru/', '�� ', 0);
INSERT INTO `gamedealer_projects` VALUES(10, 'PoolOnline (������ �������)', 54, 40, 'http://gamedealer.ru/img3/icon_pool.jpg', 'http://poolonline.ru/', '���', 0);
INSERT INTO `gamedealer_projects` VALUES(6, '����������', 3, 40, 'http://gamedealer.ru/img3/icon_terra.gif', 'http://www.territory.ru/', '��', 0);
INSERT INTO `gamedealer_projects` VALUES(9, 'TimeZero', 236, 40, 'http://gamedealer.ru/img3/icon_timezero.gif', 'http://www.timezero.ru/', 'GC', 0);
INSERT INTO `gamedealer_projects` VALUES(8, '�����@mail.ru', 13, 40, 'http://gamedealer.ru/img3/icon_drive.gif', 'http://drive.mail.ru/', 'GD', 0);
INSERT INTO `gamedealer_projects` VALUES(16, '8-� ����', 11, 1, 'http://gamedealer.ru/img3/p_8day.gif', 'http://8day.ru', '', 0);
INSERT INTO `gamedealer_projects` VALUES(12, '�������: �������� �������� (���-�����)', 123, 40, 'http://gamedealer.ru/img3/shop_legend2.gif', 'http://w2.dwar.ru/', 'DIAMOND', 0);
INSERT INTO `gamedealer_projects` VALUES(11, '�������', 234, 1.34, 'http://gamedealer.ru/img3/icon_pirat.gif', 'http://www.piratia.ru/', '���', 0);
INSERT INTO `gamedealer_projects` VALUES(15, '11x11 - ������', 41, 50, 'http://gamedealer.ru/img3/p_11x11.gif', 'http://www.11x11.ru/', '������', 0);
INSERT INTO `gamedealer_projects` VALUES(17, 'ARENA: Dragon Age - ��� ����', 8, 30, 'http://gamedealer.ru/img3/arena1.gif', 'http://arena.ru', '�������', 0);
INSERT INTO `gamedealer_projects` VALUES(14, '�����: �����������', 29, 40, 'http://gamedealer.ru/img3/icon_heros.gif', 'http://www.dantar.ru/', '���', 0);
INSERT INTO `gamedealer_projects` VALUES(13, 'Perfect World', 247, 13.38, 'http://gamedealer.ru/img3/p_world.gif', 'http://www.pwonline.ru/', '�������', 0);
INSERT INTO `gamedealer_projects` VALUES(18, 'ARENA: Dragon Age - ���� �����', 9, 30, 'http://gamedealer.ru/img3/arena2.gif', 'http://arena.ru', '�������', 0);
INSERT INTO `gamedealer_projects` VALUES(19, 'Bloody world', 2351, 40, 'http://gamedealer.ru/img3/bw.gif', 'http://www.bloodyworld.com/ ', '�������', 0);
INSERT INTO `gamedealer_projects` VALUES(20, 'Carnage', 16, 30, 'http://gamedealer.ru/img3/p_carnage.gif', 'http://www.carnage.ru/', '���������', 0);
INSERT INTO `gamedealer_projects` VALUES(27, '������������� �����', 49, 30, 'http://gamedealer.ru/img3/ganjawars.gif', 'http://www.ganjawars.ru/', 'EUN', 0);
INSERT INTO `gamedealer_projects` VALUES(24, '������: ������� �������', 30, 40, 'http://gamedealer.ru/img3/p_desant.gif', 'http://destrider.ru/', '������', 0);
INSERT INTO `gamedealer_projects` VALUES(23, '����������', 43, 15, 'http://gamedealer.ru/img3/p_gladiators.gif', 'http://www.gladiators.ru/', '�����', 0);
INSERT INTO `gamedealer_projects` VALUES(22, 'World of Tides', 12, 30, 'http://gamedealer.ru/img3/p_wot.gif', 'http://wotgame.ru/', '��������� ������', 0);
INSERT INTO `gamedealer_projects` VALUES(26, '���� ��', 34, 0.4, 'http://gamedealer.ru/img3/parapa.gif', 'http://www.parapa.ru', '������', 0);
INSERT INTO `gamedealer_projects` VALUES(25, '������� �����', 42, 30, 'http://gamedealer.ru/img3/p_butsa.gif', 'http://www.butsa.ru/', '������', 0);
INSERT INTO `gamedealer_projects` VALUES(31, 'Last Chaos', 97, 0.5, 'http://gamedealer.ru/img3/lastchaos.gif', '', '������', 0);
INSERT INTO `gamedealer_projects` VALUES(32, '����', 96, 40, 'http://gamedealer.ru/img3/faor.gif', '', '�����', 0);
INSERT INTO `gamedealer_projects` VALUES(28, '�����������', 14, 32, 'http://gamedealer.ru/img3/p_kingdom.png', 'http://www.kor.ru/ ', '�����', 0);
INSERT INTO `gamedealer_projects` VALUES(33, '�������: �������� �������� (���-�������)', 125, 40, 'http://gamedealer.ru/img3/legend_mediant.gif', 'http://w3.dwar.ru/', '�����', 0);

CREATE TABLE `gamedealer_wmreq` (
  `id` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL,
  `webmId` int(11) NOT NULL,
  `purse` varchar(100) NOT NULL,
  `timestamp` varchar(100) NOT NULL,
  `wm_amount` float NOT NULL,
  `wm_currency` varchar(5) NOT NULL,
  `wmid` varchar(100) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

