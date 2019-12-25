ALTER TABLE VisualTag ADD COLUMN `userId` INT(11) NOT NULL DEFAULT '0' AFTER imgYCoord;

UPDATE VisualTag JOIN Visual ON VisualTag.imageId=Visual.id SET VisualTag.userId=Visual.userId;
