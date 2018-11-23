alter table Model
  add createDate int not null default 0,
  add modDate int not null default 0;

-- some number meaning "the dark ages"
update Model set createDate = 1, modDate = 1;

alter table Lexeme
  add apheresis tinyint not null default 0 after notes,
  add hasApheresis tinyint not null default 0 after apheresis,
  add hasApocope tinyint not null default 0 after hasApheresis,
  add staleParadigm tinyint not null default 0 after restriction,
  modify modDate int not null after createDate,
  add key(staleParadigm);

alter table InflectedForm
  add apheresis tinyint not null default 0 after recommended,
  add apocope tinyint not null default 0 after apheresis;

-- eu, tu, el, fi, sine, vrea
update Lexeme
  set hasApheresis = 1, staleParadigm = 1
  where id in (18468, 19519, 20836, 52656, 59076, 137123);

update Lexeme
  set hasApheresis = 1, staleParadigm = 1
  where formNoAccent like 'î%';

alter table ModelDescription
  add hasApocope tinyint not null default 0 after recommended;

-- apocope: certain I models
update ModelDescription md
  join Model m on md.modelId = m.id
  set md.hasApocope = 1
  where m.modelType = 'I'
  and m.number in ('1', '5', '7-S', '8', '9', '10', '11', '12', '13');

-- apocope: A/MF/PT models, masculine, n.-ac., with article, ending in ul
update ModelDescription md
  join Model m on md.modelId = m.id
  join ModelType mt on m.modelType = mt.canonical
  join Lexeme l on m.modelType = mt.code and m.number = l.modelNumber
  join InflectedForm f on l.id = f.lexemeId
  set md.hasApocope = 1, l.hasApocope = 1, l.staleParadigm = 1
  where mt.canonical = 'A'
  and md.inflectionId = 29
  and md.applOrder = 0
  and f.inflectionId = 29
  and f.form like '%ul';

-- apocope: M/AM models, n.-ac., with article, ending in ul
update ModelDescription md
  join Model m on md.modelId = m.id
  join ModelType mt on m.modelType = mt.canonical
  join Lexeme l on m.modelType = mt.code and m.number = l.modelNumber
  join InflectedForm f on l.id = f.lexemeId
  set md.hasApocope = 1, l.hasApocope = 1, l.staleParadigm = 1
  where mt.canonical = 'M'
  and md.inflectionId = 5
  and md.applOrder = 0
  and f.inflectionId = 5
  and f.form like '%ul';

-- apocope: N/AN/ASN models, n.-ac., with article, ending in ul
update ModelDescription md
  join Model m on md.modelId = m.id
  join ModelType mt on m.modelType = mt.canonical
  join Lexeme l on m.modelType = mt.code and m.number = l.modelNumber
  join InflectedForm f on l.id = f.lexemeId
  set md.hasApocope = 1, l.hasApocope = 1, l.staleParadigm = 1
  where mt.canonical = 'N'
  and md.inflectionId = 21
  and md.applOrder = 0
  and f.inflectionId = 21
  and f.form like '%ul';
