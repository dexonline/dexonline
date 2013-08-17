ALTER TABLE VisualTag DROP lexemId;
ALTER TABLE VisualTag DROP isMain;
ALTER TABLE VisualTag ADD lexemeId INT(11) AFTER imageId;
