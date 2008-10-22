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
	HtmlRep = '<b>FÁTĂ,</b> <i>fete,</i> s.f. <a class="ref" href="/search.php?cuv=Persoană">Persoană (<b>1</b>)</a> care nu bea bere.';

insert into Word set
	Id = 2,
	Name = "fâţă",
	Priority = 0,
	DefinitionId = 2;

insert into Definition set
	Id = 2,
	UserId = 1,
	SourceId = 1,
	Lexicon = "fâţă",
	InternalRep = '@FẤŢĂ,@ $fâţe,$ s.f. |Peşte|peste| mic care se fâţâie.',
	HtmlRep = '<b>FẤŢĂ,</b> <i>fâţe,</i> s.f. <a class="ref" href="/search.php?cuv=peste">Peşte</a> mic care se fâţâie.';

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
	HtmlRep = '<b>COPIL</b> Trimitere cu minus: <a class="ref" href="/search.php?cuv=deal">dealurilor</a>';

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
	InternalRep = "@MÁRE^2@, $mări$, s.f. Nume generic dat vastelor întinderi de apă stătătoare, adânci şi sărate, de pe suprafaţa |Pământului|Pământ|, care de obicei sunt unite cu |oceanul|ocean| printr-o |strâmtoare|strâmtoare|; parte a oceanului de lângă |ţărm|ţărm|; $p.ext.$ ocean. * Expr $Marea cu sarea$ = mult, totul; imposibilul. $A vântura mări şi ţări$ = a călători mult. $A încerca marea cu degetul$ = a face o încercare, chiar dacă şansele de reuşită sunt minime. $Peste (nouă) mări şi (nouă) ţări$ = foarte departe. ** Fig. Suprafaţă vastă; întindere mare; imensitate. ** Fig. Mulţime (nesfârşită), cantitate foarte mare. - Lat. @mare, -is@.",
	HtmlRep = '<b>MÁRE<sup>2</sup></b>, <i>mări</i>, s.f. Nume generic dat vastelor întinderi de apă stătătoare, adânci şi sărate, de pe suprafaţa <a class="ref" href="/search.php?cuv=Pământ">Pământului</a>, care de obicei sunt unite cu <a class="ref" href="/search.php?cuv=ocean">oceanul</a> printr-o <a class="ref" href="/search.php?cuv=strâmtoare">strâmtoare</a>; parte a oceanului de lângă <a class="ref" href="/search.php?cuv=ţărm">ţărm</a>; <i>p.ext.</i> ocean. &loz; Expr <i>Marea cu sarea</i> = mult, totul; imposibilul. <i>A vântura mări şi ţări</i> = a călători mult. <i>A încerca marea cu degetul</i> = a face o încercare, chiar dacă şansele de reuşită sunt minime. <i>Peste (nouă) mări şi (nouă) ţări</i> = foarte departe. &diams; Fig. Suprafaţă vastă; întindere mare; imensitate. &diams; Fig. Mulţime (nesfârşită), cantitate foarte mare. &#x2013; Lat. <b>mare, -is</b>.';

update Definition set
	Status = 0,
	CreateDate = unix_timestamp(),
	ModDate = unix_timestamp();


insert into GuideEntry set
  Correct = '@Aşa@ e bine!',
  CorrectHtml = '<b>Aşa</b> e bine!',
  Wrong = '@Aşa@ e rău!',
  WrongHtml = '<b>Aşa</b> e rău!',
  Comments = 'Şi o explicaţie, cu |legătură|copil|.',
  CommentsHtml = 'Şi o explicaţie, cu <a class="ref" href="/search.php?cuv=copil">legătură</a>.',
  Status = 0,
	createDate = unix_timestamp(),
	modDate = unix_timestamp();
INSERT INTO Source (
  ShortName, Name, Author, Publisher, Year, CanContribute, CanModerate
) VALUES
  ("DEX '98",
  "Dicţionarul explicativ al limbii române",
  'Academia Română, Institutul de Lingvistică "Iorgu Iordan"',
  "Editura Univers Enciclopedic",
  "1998",
  0,
  1),

  ("Sinonime",
  "Dicţionar de sinonime",
  "Mircea şi Luiza Seche",
  "Editura Litera Internaţional",
  "2002",
  1,
  1),

  ("Neoficial",
  "Această sursă include definiţii ale unor cuvinte de uz curent care nu există în nici unul din celelalte dicţionare",
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
