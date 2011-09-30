CREATE TABLE `UserWordBookmark` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `userId` int(11) NOT NULL,
   `definitionId` int(11) NOT NULL,
   `comment` varchar(255) DEFAULT NULL,
   `createDate` int NOT NULL,
   `modDate` int NOT NULL,
   PRIMARY KEY (`id`)
);

