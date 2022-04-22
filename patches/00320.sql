DROP TABLE IF EXISTS ExpressionOfTheMonth;

CREATE TABLE `ExpressionOfTheMonth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `displayDate` date DEFAULT NULL,
  `definitionId` int(11) NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `idArtist` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int(11) NOT NULL DEFAULT '0',
  `modDate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `displayDate` (`displayDate`),
  KEY `createDate` (`createDate`),
  KEY `modDate` (`modDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;

INSERT INTO `ExpressionOfTheMonth` VALUES
(1,'A aștepta să-i pice mură-n gură','2022-04-04',514658,'2022/A-aștepta-să-i-pice-mură-n-gură.png',3,'',1650538063,1650538063),
(2,'A avea cașcaval','2022-04-07',515581,'2022/A-avea-cașcaval.jpg',3,'',1650538063,1650538063),
(3,'A da apă la moară','2022-04-10',891012,'2022/A-da-apă-la-moară.png',3,'',1650538063,1650538063),
(4,'A face cu ou și cu oțet','2022-04-13',516999,'2022/A-face-cu-ou-și-cu-oțet.jpg',3,'',1650538063,1650538063),
(5,'A fi ca pâinea caldă','2022-04-16',544572,'2022/A-fi-ca-pîinea-caldă.jpg',3,'',1650538063,1650538063),
(6,'A fi zgârie brânză','2022-04-19',548564,'2022/A-fi-zgîrie-brînză.jpg',3,'',1650538063,1650538063),
(7,'A împăca capra cu varza','2022-04-22',521623,'2022/A-împăca-capra-cu-varza.png',3,'',1650538063,1650538063),
(8,'A intra ca-n brânză','2022-04-25',521373,'2022/A-intra-ca-n-brînză.jpg',3,'',1650538063,1650538063),
(9,'A mânca răbdări prăjite','2022-04-28',526829,'2022/A-mînca-răbdări-prăjite.jpg',3,'',1650538063,1650538063),
(10,'A o face de oaie','2022-05-01',517009,'2022/A-o-face-de-oaie.jpg',3,'',1650538063,1650538063),
(11,'A ploua cu cârnați','2022-05-04',1055039,'2022/A-ploua-cu-cîrnați.png',3,'',1650538063,1650538063),
(12,'A rânji fasolea','2022-05-07',553076,'2022/A-rînji-fasolea.jpg',3,'',1650538063,1650538063),
(13,'A scoate untul din cineva','2022-05-10',539072,'2022/A-scoate-untul-din-cineva.png',3,'',1650538063,1650538063),
(14,'A sufla și-n iaurt','2022-05-13',539970,'2022/A-sufla-și-n-iaurt.png',3,'',1650538063,1650538063),
(15,'A te da la cașcaval','2022-05-16',515581,'2022/A-te-da-la-cașcaval.jpg',3,'',1650538063,1650538063),
(16,'Ca musca-n lapte','2022-05-19',515005,'2022/Ca-musca-n-lapte.jpg',3,'',1650538063,1650538063),
(17,'Cînd o zbura porcul','2022-05-22',515741,'2022/Cînd-o-zbura-porcul.png',3,'',1650538063,1650538063),
(18,'Cît ai zice pește','2022-05-25',698854,'2022/Cît-ai-zice-pește.png',3,'',1650538063,1650538063),
(19,'Colac peste pupăză','2022-05-28',516121,'2022/Colac-peste-pupăză.png',3,'',1650538063,1650538063),
(20,'Cu un ochi la slănină și altul la făină','2022-05-31',542067,'2022/Cu-un-ochi-la-slănină-și-altul-la-făină.jpg',3,'',1650538063,1650538063),
(21,'Vrabia mălai visează','2022-06-03',917466,'2022/Vrabia-mălai-visează.jpg',3,'',1650538063,1650538063);
