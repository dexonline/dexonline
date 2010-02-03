set names 'utf8';
insert into Source values (26, 'Dicţionar argou', 'Dicţionar de argou al limbii române', 'George Volceanov', 'Editura Niculescu', '2006', 1, 1, 1, 26);
update Definition set SourceId=26 where InternalRep like '%Niculescu%';
update Definition set InternalRep=replace(InternalRep, concat('(Not', substring_index(InternalRep, '(Not', -1)), '') where InternalRep like '%Niculescu%';
update Definition set HtmlRep=replace(HtmlRep, concat('(Not', substring_index(HtmlRep, '(Not', -1)), '') where HtmlRep like '%Niculescu%';
