delimiter //

CREATE PROCEDURE refreshStudentStats ()
BEGIN
truncate OCR_stats;
insert into OCR_stats SELECT userId, editorId, sourceId, status, count(*) defCnt, sum(char_length(ocrText)) defTotalSize, max(dateModified) FROM OCR GROUP BY userId, editorId, sourceId, status;
END//

delimiter ;
