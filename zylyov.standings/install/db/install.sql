CREATE TABLE IF NOT EXISTS `zylyov_standings_team` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
);

CREATE TABLE IF NOT EXISTS `zylyov_standings_standing` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `DEPTH` tinyint(4) NOT NULL,
  `THIRD_PLACE_GAME` char(1) DEFAULT NULL,
  PRIMARY KEY (`ID`)
);

CREATE TABLE IF NOT EXISTS `zylyov_standings_match_team` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `STANDING_ID` int(18) DEFAULT NULL,
  `TEAM_ID` int(18) DEFAULT NULL,
  `SCORE` int(18) DEFAULT NULL,
  `DEPTH` int(18) DEFAULT NULL,
  `POSITION` int(18) DEFAULT NULL,
  `PLACE` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_standing_id` (`STANDING_ID`)
);

INSERT INTO `zylyov_standings_match_team` (`ID`, `STANDING_ID`, `TEAM_ID`, `SCORE`, `DEPTH`, `POSITION`, `PLACE`) VALUES
	(1, 1, 8, 2, 3, 0, NULL),
	(2, 1, 19, 1, 3, 1, NULL),
	(3, 1, 35, 0, 3, 2, NULL),
	(4, 1, 26, 8, 3, 3, NULL),
	(5, 1, 5, 1, 3, 4, NULL),
	(6, 1, 23, 0, 3, 5, NULL),
	(7, 1, 20, 2, 3, 6, NULL),
	(8, 1, 17, 3, 3, 7, NULL),
	(9, 1, 8, 3, 2, 0, NULL),
	(10, 1, 26, 0, 2, 1, NULL),
	(11, 1, 5, 0, 2, 2, NULL),
	(12, 1, 17, 1, 2, 3, NULL),
	(13, 1, 8, NULL, 1, 0, NULL),
	(14, 1, 17, NULL, 1, 1, NULL),
	(15, 1, 26, NULL, 1, 2, NULL),
	(16, 1, 5, NULL, 1, 3, NULL);

INSERT INTO `zylyov_standings_standing` (`ID`, `NAME`, `DEPTH`, `THIRD_PLACE_GAME`) VALUES
	(1, 'Чемпионат Мира по футболу 2033', 3, 'Y');

INSERT INTO `zylyov_standings_team` (`ID`, `NAME`) VALUES
	(1, 'Австралия'),
	(2, 'Алжир'),
	(3, 'Ангола'),
	(4, 'Аргентина'),
	(5, 'Афганистан'),
	(6, 'Боливия'),
	(7, 'Ботсвана'),
	(8, 'Бразилия'),
	(9, 'Венесуэла'),
	(10, 'Египет'),
	(11, 'Замбия'),
	(12, 'Индия'),
	(13, 'Индонезия'),
	(14, 'Иран'),
	(15, 'Испания'),
	(16, 'Йемен'),
	(17, 'Казахстан'),
	(18, 'Канада'),
	(19, 'Кения'),
	(20, 'Китай'),
	(21, 'Колумбия'),
	(22, 'Ливия'),
	(23, 'Мавритания'),
	(24, 'Мадагаскар'),
	(25, 'Мали'),
	(26, 'Мексика'),
	(27, 'Мозамбик'),
	(28, 'Монголия'),
	(29, 'Мьянма'),
	(30, 'Намибия'),
	(31, 'Нигер'),
	(32, 'Нигерия'),
	(33, 'Пакистан'),
	(34, 'Перу'),
	(35, 'Россия'),
	(36, 'Саудовская Аравия'),
	(37, 'Соединённые Штаты Америки'),
	(38, 'Сомали'),
	(39, 'Судан'),
	(40, 'Таиланд'),
	(41, 'Танзания'),
	(42, 'Турция'),
	(43, 'Украина'),
	(44, 'Франция'),
	(45, 'Чад'),
	(46, 'Чили'),
	(47, 'Эфиопия'),
	(48, 'Южный Судан');
