set names 'utf8';
insert into Source values (26, 'Dicţionar argou', 'Dicţionar de argou al limbii române', 'George Volceanov', 'Editura Niculescu', '2006', 1, 1, 1, 26);
update Definition set SourceId=26 where InternalRep like '%Niculescu, 2007%';
update Definition set InternalRep=replace(InternalRep, substring(InternalRep, -96, 96), '') where SourceId = 26;
update Definition set HtmlRep=replace(HtmlRep, substring(HtmlRep, -96, 96), '') where SourceId = 26;
