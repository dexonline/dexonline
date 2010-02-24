insert into User set
	Id = 0,
	Nick = "Anonim",
	Email = "anonim@anonim.com",
	EmailVisible = 0,
	Password = md5("ljkasdkljwer"),
	Name = "Anonim",
	Moderator = 0;
update User set id = 0 where name = "Anonim";

insert into User set
	Id = 1,
	Nick = "reggie",
	Email = "reggie@foobar.com",
	EmailVisible = 0,
	Password = md5("hello"),
	Name = "Reggie Registered",
	moderator = 1;

insert into Word set
	Id = 1,
	Name = "fată",
	Priority = 0,
	DefinitionId = 1;

insert into Definition set
	Id = 1,
	UserId = 0,
	SourceId = 3,
	Lexicon = "fată",
	InternalRep = '@FÁTĂ,@ $fete,$ s.f. |Persoană (@1@)|Persoană| care nu bea bere.',
	HtmlRep = '<b>FÁTĂ,</b> <i>fete,</i> s.f. <a class="ref" href="/definitie/Persoană">Persoană (<b>1</b>)</a> care nu bea bere.';

insert into Word set
	Id = 2,
	Name = "fâță",
	Priority = 0,
	DefinitionId = 2;

insert into Definition set
	Id = 2,
	UserId = 1,
	SourceId = 1,
	Lexicon = "fâță",
	InternalRep = '@FẤȚĂ,@ $fâțe,$ s.f. |Pește|peste| mic care se fâțâie.',
	HtmlRep = '<b>FẤȚĂ,</b> <i>fâțe,</i> s.f. <a class="ref" href="/definitie/peste">Pește</a> mic care se fâțâie.';

insert into Word set
	Id = 3,
	Name = "copil",
	Priority = 0,
	DefinitionId = 3;

insert into Definition set
	Id = 3,
	UserId = 1,
	SourceId = 2,
	Lexicon = "copil",
	InternalRep = '@COPIL@ Trimitere cu minus: |dealurilor|deal|',
	HtmlRep = '<b>COPIL</b> Trimitere cu minus: <a class="ref" href="/definitie/deal">dealurilor</a>';

insert into Word set
	Id = 4,
	Name = "mare",
	Priority = 0,
	DefinitionId = 4;

insert into Word set
	Id = 5,
	Name = "MARI",
	Dname = "mări",
	Priority = 1,
	DefinitionId = 4;

insert into Definition set
	Id = 4,
	UserId = 1,
	SourceId = 1,
	Lexicon = "mare",
	InternalRep = "@MÁRE^2@, $mări$, s.f. Nume generic dat vastelor întinderi de apă stătătoare, adânci și sărate, de pe suprafața |Pământului|Pământ|, care de obicei sunt unite cu |oceanul|ocean| printr-o |strâmtoare|strâmtoare|; parte a oceanului de lângă |țărm|țărm|; $p.ext.$ ocean. * Expr $Marea cu sarea$ = mult, totul; imposibilul. $A vântura mări și țări$ = a călători mult. $A încerca marea cu degetul$ = a face o încercare, chiar dacă șansele de reușită sunt minime. $Peste (nouă) mări și (nouă) țări$ = foarte departe. ** Fig. Suprafață vastă; întindere mare; imensitate. ** Fig. Mulțime (nesfârșită), cantitate foarte mare. - Lat. @mare, -is@.",
	HtmlRep = '<b>MÁRE<sup>2</sup></b>, <i>mări</i>, s.f. Nume generic dat vastelor întinderi de apă stătătoare, adânci și sărate, de pe suprafața <a class="ref" href="/definitie/Pământ">Pământului</a>, care de obicei sunt unite cu <a class="ref" href="/definitie/ocean">oceanul</a> printr-o <a class="ref" href="/definitie/strâmtoare">strâmtoare</a>; parte a oceanului de lângă <a class="ref" href="/definitie/țărm">țărm</a>; <i>p.ext.</i> ocean. &loz; Expr <i>Marea cu sarea</i> = mult, totul; imposibilul. <i>A vântura mări și țări</i> = a călători mult. <i>A încerca marea cu degetul</i> = a face o încercare, chiar dacă șansele de reușită sunt minime. <i>Peste (nouă) mări și (nouă) țări</i> = foarte departe. &diams; Fig. Suprafață vastă; întindere mare; imensitate. &diams; Fig. Mulțime (nesfârșită), cantitate foarte mare. &#x2013; Lat. <b>mare, -is</b>.';

update Definition set
	Status = 0,
	CreateDate = unix_timestamp(),
	ModDate = unix_timestamp();


insert into GuideEntry set
  Correct = '@Așa@ e bine!',
  CorrectHtml = '<b>Așa</b> e bine!',
  Wrong = '@Așa@ e rău!',
  WrongHtml = '<b>Așa</b> e rău!',
  Comments = 'Și o explicație, cu |legătură|copil|.',
  CommentsHtml = 'Și o explicație, cu <a class="ref" href="/definitie/copil">legătură</a>.',
  Status = 0,
	createDate = unix_timestamp(),
	modDate = unix_timestamp();
INSERT INTO Source (
  ShortName, Name, Author, Publisher, Year, CanContribute, CanModerate
) VALUES
  ("DEX '98",
  "Dicționarul explicativ al limbii române",
  'Academia Română, Institutul de Lingvistică "Iorgu Iordan"',
  "Editura Univers Enciclopedic",
  "1998",
  0,
  1),

  ("Sinonime",
  "Dicționar de sinonime",
  "Mircea și Luiza Seche",
  "Editura Litera Internațional",
  "2002",
  1,
  1),

  ("Neoficial",
  "Această sursă include definiții ale unor cuvinte de uz curent care nu există în nici unul din celelalte dicționare",
  "",
  "",
  "",
  1,
  1);

INSERT INTO Comment SET
	DefinitionId = 1,
	UserId = 1,
	Status = 0,
	Contents = "Aici avem un @comentariu@.",
	HtmlContents = "Aici avem un <b>comentariu</b>.";
