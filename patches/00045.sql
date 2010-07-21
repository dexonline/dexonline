-- Use the standard lozenge
update Definition set htmlRep = replace(htmlRep, "&#x2662;", "&#x25ca;");
-- Mark all the definitions with abbreviations as modified so the clients can reacquire them.
-- This is necessary because of a time glitch: We introduced abbreviations and all the clients downloaded definitions with hash marks
-- Then we patched update protocol 3.0 to omit hash marks
-- But the clients won't download the patched definitions until we mark them below:
update Definition set modDate = 1279590000 where sourceId in (1, 2, 3, 4, 5) and status = 0;
