ALTER TABLE Source ADD COLUMN remark varchar(255) COLLATE utf8_romanian_ci DEFAULT '' AFTER reformId;
UPDATE Source SET remark = 'cu ortografie modificată conform normelor din 1993', year = substr(year, 1, 4) WHERE id IN (3,4,5,15,16);
