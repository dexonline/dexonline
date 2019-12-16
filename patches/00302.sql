ALTER TABLE PageIndex
  ADD COLUMN `createDate` int(11) NOT NULL,
  ADD COLUMN `modDate` int(11) NOT NULL,
  ADD COLUMN `modUserId` int(11) NOT NULL,
  ADD INDEX `CreateDate` (`createDate`),
  ADD INDEX `ModDate` (`modDate`);
