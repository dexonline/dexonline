CREATE TABLE Visual (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  path VARCHAR(255) NOT NULL,
  userId INT(11) NOT NULL,
  revised BOOL DEFAULT 0,
  createDate INT(11),
  modDate INT(11)
) DEFAULT CHARACTER SET "utf8" ENGINE=InnoDB;

CREATE TABLE VisualTag (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  imageId INT(11),
  lexemId INT(11),
  isMain BOOLEAN DEFAULT 1,
  label VARCHAR(255),
  textXCoord INT(11),
  textYCoord INT(11),
  imgXCoord INT(11),
  imgYCoord INT(11),
  createDate INT(11),
  modDate INT(11)
) DEFAULT CHARACTER SET "utf8" ENGINE=InnoDB;
