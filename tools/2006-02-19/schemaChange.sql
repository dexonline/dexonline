DROP TABLE IF EXISTS GuideEntry;

CREATE TABLE GuideEntry (
  Id int(11) NOT NULL auto_increment,
  Correct text,
  CorrectHtml text,
  Wrong text,
  WrongHtml text,
  Comments text,
  CommentsHtml text,
  Status int(6) NOT NULL default 0,
  CreateDate int(11) NOT NULL default 0,
  ModDate int(11) NOT NULL default 0,
  PRIMARY KEY  (Id)
);

INSERT INTO GuideEntry (Correct, Wrong, Comments) VALUES
  ('Eu am numai trei picioare.\nNu am decât 10000 lei.',
   'Am decât 10000 lei.',
   '$Numai$ se folosește în construcții afirmative, iar $decât$ se folosește în construcții negative.'),

  ('Eu sunt',
   'Eu sînt, eu sânt',
   'Fără comentarii. Dacă țineți morțiș să vă împotriviți normelor ortografice curente și să scrieți cum ați învățat, scrieți $eu sînt$. $Eu sânt$ este greșit după ambele seturi de norme.'),

   ('Înger\nA hotărî\nReîntregire\nHotărât\nHotărând',
    'Ânger\nA hotărâ\nReântregire\nHotărît\nHotărînd',
    'Se folosește $î$, nu $â$, la începutul cuvântului, la sfârșitul cuvântului și în cuvintele formate cu prefix dacă $î$ este prima literă din rădăcină. Participiul și gerunziul verbelor nu fac excepție: dacă participiul se formează cu $-ât$ sau dacă gerunziul se formează cu $-ând,$ folosim $â$.'),

   ('Fii cuminte!\nNu fi fraier!\nSă fii punctual.',
    'Fi cuminte!\nNu fii fraier!\nSă fi punctual.',
    'Pentru imperativul afirmativ $(fii cuminte!)$ și pentru conjunctiv $(să fii punctual)$ n-am argumente, dar așa e :) Imperativul negativ se formează întotdeauna cu $nu$ + infinitivul verbului: $nu veni, nu pleca, nu uita$. Deci, în cazul lui $a fi$, avem $nu fi fraier!$'),

   ('Filozofie',
    'Filosofie',
    'Așa e în |DEX|filozo*|. Pentru $filozof$ este menționată ca formă acceptată și $filosof$, ceea ce ne face să credem că și $filosofie$ este o formă acceptată pentru $filozofie$. Oricum, forma de bază este $filozofie, filozof$.'),

   ('Munții noștri',
    'Munții noștrii',
    'Singurul motiv pentru care s-ar adăuga un al doilea $i$ este articolul hotărât. Dar $noștri$ este pronume și nu se articulează niciodată.'),

   ('Băiatul a cărui carte\nFata a cărei carte\nBăiatul ale cărui cărți\nFata ale cărei cărți\nBăieții a căror carte\nFetele a căror carte\nBăieții ale căror cărți\nFetele ale căror cărți\n\nBăiatul al cărui câine\nFata al cărei câine\nBăiatul ai cărui câini\nFata ai cărei câini\nBăieții al căror câine\nFetele al căror câine\nBăieții ai căror câini\nFetele ai căror câini',
    'Oricum altcumva',
    '"Băiatul $pauză pauză$ cărți", un subiect drag nouă. Ca principiu de viață, e mult mai bine să vă opriți din vorbire două secunde și să judecați construcția decât să o spuneți cum s-o nimeri. Mai ales când audienței îi pasă. :) Regula de construcție este acordul "în cruce": $al / a / ai / ale$ se acordă cu obiectul $(cartea / cărțile)$, iar $cărui / cărei / căror$ se acordă cu posesorul ($băiatul / fata$ etc).'),

   ('Eu creez\nTu creezi\nEl creează',
    'Eu crez\nTu crezi\nEl crează',
    'Verbul $a crea$, deși se termină în $-ea$, nu este un verb de grupa a II-a, ci de grupa I. Sufixul este $-a$, iar rădăcina este $cre-$. Pentru ușurință, conjugați-l ca și pe $a lucra$, înlocuind $lucr-$ cu $cre-$. Oriunde $a lucra$ se conjugă cu un $e$, $a crea$ se conjugă cu doi ($eu lucrez - eu creez$). Formele $crez, crezi, crează$ există în limba română în cu totul alt context; ele sunt conjugări populare ale verbului $a crede$.'),

   ('Eu creez\nEu agreez\nEu întemeiez\nEu încleiez\n',
    'Eu creiez\nEu agreiez\nEu întemeez\nEu încleez\n',
    'Dacă infinitivul verbului se termină în $-ia, (a întemeia, a încleia)$ atunci litera $i$ apare și la persoana I a timpului prezent, altfel nu $(a crea, a agrea).$'),

   ('Fumător inveterat\nAdevăr învederat',
    'Invers',
    'Paronime - vezi |definițiile|înve*erat|'),

   ('@Masculin@\nEu însumi\nTu însuți\nEl însuși\nNoi înșine\nVoi înșivă\nEi înșiși\n\n@Feminin@\nEu însămi\nTu însăți\nEa însăși\nNoi însene\nVoi însevă\nEle înseși (însele)\n',
    'Oricum altcumva',
    'Pronume de întărire - vezi |DEX|însumi|.'),

   ('Repercusiune',
    'Repercursiune',
    'Vezi |definiția|repercusiune|.'),

   ('Oprobriu',
    'Oprobiu',
    'Vezi |definiția|oprobriu|.'),

   ('Complet, complect',
    '',
    '|DEX-ul|comple*t| menționează $complect$ ca variantă acceptată a lui $complet$. Totuși, dacă până acum ați folosit $complet$, vă sugerăm să-l folosiți și în continuare. :)'),

   ('Delincvent',
    'Delicvent',
    'Vezi |definiția|delincvent|. $Delicvent$ este doar o formă acceptată (ca și $complet / complect...)$'),

   ('Piuneză',
    'Pioneză',
    'Vezi |definiția|piuneză|. $Pioneză$ este doar o formă acceptată.$'),

   ('Saxana',
    'Sarsana',
    'Vezi |definiția|saxana|.'),

   ('Crevetă, pl. crevete',
    'Crevete, pl. creveți',
    'Vezi |definiția|crevetă|.'),

   ('Robinet, pl. robinete',
    'Robinet, pl. robineți',
    'Vezi |definiția|robinet|.'),

   ('Suport, pl. suporturi',
    'Suport, pl. suporți',
    'Vezi |definiția|suport|.'),

   ('Balot, pl. baloturi',
    'Balot, pl. baloți',
    'Vezi |definiția|balot|.'),

   ('Acumulator, pl. acumulatoare',
    'Acumulator, pl. acumulatori',
    'Vezi |definiția|acumulator|. $Acumulatori$ este o formă acceptată de unele dicționare.'),

   ('Laser, pl. lasere',
    'Laser, pl. laseri',
    'Vezi |definiția|laser|.'),

   ('Reactor, pl. reactoare',
    'Reactor, pl. reactori',
    'Vezi |definiția|reactor|.'),

   ('Festival, pl. festivaluri',
    'Festival, pl. festivale',
    'Vezi |definiția|festival|.'),

   ('Jantă, pl. jante',
    'Jeantă, pl. jenți',
    'Vezi |definiția|jantă|.'),

   ('Acciz, pl. accize',
    'Acciză',
    'Vezi |definiția|acciz|.'),

   ('Grep sau grepfrut sau grape-fruit',
    'Oricum altcumva',
    'Vezi |definițiile|grepfrut|.'),

   ('Ingrediente (pl. lui ingredient)',
    'Ingredienți',
    'Vezi |definiția|ingredient|.'),

   ('Amândurora (dativul lui amândoi)',
    'Amândorura',
    'Vezi |definiția|amândoi|. Cum spunem $tuturor$, nu $totorur$, la fel spunem și $amândurora.'),

   ('De asemenea',
    'Deasemenea',
    '$Deasemenea$ nu există în DEX.'),

   ('Merită să aștepți',
    'Se merită să aștepți',
    'Verbul |a merita|| nu este reflexiv.'),

   ('Ce-i cu cărțile astea pe masă?',
    'Ce-s cu cărțile astea pe masă?',
    'Folosirea verbului $a fi$ la plural nu are sens și nu are legătură cu faptul că vorbim despre mai multe cărți. Un exemplu și mai evident este $Ce este cu voi aici?$, nu $Ce sunteți cu voi aici?$'),

   ('Serviciu',
    'Servici',
    '$Servici$ nu există în DEX.'),

   ('Mi-ar plăcea',
    'Mi-ar place',
    'Infinitivul verbului este $a plăcea$ (vezi |definiția|plăcea|), iar condițional-optativul se formează cu verbul auxiliar $a avea$ (în acest caz, $ar$) și cu infinitivul.'),

   ('Cartea de pe noptieră\nCartea după noptieră',
    '-',
    '$|DEX|după|$ menționează $de pe$ ca sens impropriu al lui $după$ (sensul 5). Totuși, denaturarea sensului este foarte mare și consider că folosirea lui $după$ este foarte neliterară.'),

   ('Cartea pe care am citit-o\nOmul pe care l-am întrebat\n\nCartea care mi-a plăcut\nOmul care mi-a răspuns',
    'Cartea care am citit-o\nOmul care l-am întrebat',
    'În toate cazurile avem de-a face cu propoziții subordonate: "pe care am citit-o", "pe care l-am întrebat" etc. În primele două, "pe care" este complement direct, arătând asupra cui se efectuează acțiunea: $Pe cine am citit? Pe ea, pe carte. Pe cine am întrebat? Pe el, pe om.$ Complementul direct este în cazul acuzativ, de aceea este nevoie și de prepoziția $pe$.\n\nÎn ultimele două cazuri, "care" este subiect, arătând cine face acțiunea: $Cine a plăcut? Cartea. Cine a răspuns? Omul.$ Subiectul este în cazul nominativ, de aceea nu este nevoie de prepoziția $pe$. $Cartea care am citit-o$ este o contaminare între cele două construcții și este incorectă.'),

   ('Optsprezece',
    'Oricum altcumva',
    'DEX (vezi |definiția|optsprezece|) nu indică nici o altă variantă acceptată.'),

   ('Optulea',
    'Oricum altcumva',
    'DEX (vezi |definiția|optulea|) nu indică nici o altă variantă acceptată.'),

   ('Mă doare apendicele.\nMă dor amigdalele.',
    'Mă doare apendicita.\nMă doare amigdalita.',
    'Apendicita și amigdalita sunt boli. Ele nu sunt părți ale corpului, deci nu au cum să doară. $Mă doare amigdalita$ este la fel de incorect ca și $mă doare durerea de cap.$'),

   ('Un copil\nDoi copii\nCopiii se joacă\n\nUn cobai\nDoi cobai\nCobaii sunt rozătoare\n',
    'Copii se joacă\n\nCobaiii sunt rozătoare\n',
    'În cele mai multe cazuri, forma de plural articulat a substantivelor masculine se formează prin adăugarea literei $i$ la forma nearticulată. Substantivele care au doi $i$ la plural $(copii, ulii, vizitii)$ capătă trei $i$ în forma articulată $(copiii, uliii, vizitiii)$, iar substantivele care au un singur $i$ la plural $(oameni, cobai)$ capătă doi $i$ în forma articulată $(oamenii, cobaii).$'),

   ('Majoritatea salariaților este mulțumită...',
    'Majoritatea salariaților sunt mulțumiți...',
    'Este pur și simplu vorba despre acordul subiectului cu predicatul. $Majoritate$ este la singular, deci și verbul $a fi$ trebuie pus la singular. Ce-i drept, $majoritate$ este un substantiv colectiv, cu sens de plural, dar forma gramaticală este în continuare cea de singular. Un exemplu și mai elocvent este $tineretul merge la mare$ (nu $tineretul merg la mare).$'),

   ('Cel mai bine plătită doctoriță',
    'Cea mai bine plătită doctoriță',
    'Formarea superlativului cu expresia $cel mai$ se referă, în acest caz, la adverbul $bine,$ nu la adjectivul $plătită.$ Doctorița nu este $cea mai plătită,$ ci cea plătită $cel mai bine.$ Și, întrucât adverbele nu au gen, număr sau caz, superlativul se formează implicit cu $cel$ pus la masculin.'),

   ('Până număr până la trei să plecați de-aici!',
    'Până număr la trei să plecați de-aici!',
    'Numărăm (de la unu) $până$ la trei. Probabil prezența primului $până$ creează confuzie, dar este necesar și cel de-al doilea.'),

   ('Dă-te la o parte.\nDați-vă jos.',
    'De-te la o parte\nDădeți-vă jos.',
    'Fără comentarii.'),

   ('Nu face asta.',
    'Nu fă asta.',
    'Imperativul negativ se formează cu infinitivul verbului: $nu mânca, nu vorbi, nu pleca.$ Deci, $nu face.$'),

   ('Chiuvetă',
    'Ghiuvetă',
    'Vezi |definiția|chiuvetă|.'),

   ('A avansa',
    'A avansa înainte',
    '$A avansa$ implică noțiunea de înaintare.'),

   ('De ce râzi?',
    'De ce te râzi?',
    '|A râde|| este verb intranzitiv, nu reflexiv.'),

   ('Îmi place cineva',
    'Îmi place de cineva',
    '$Cineva$ este subiect gramatical, deci trebuie pus în cazul nominativ, fără prepoziția $de$.'),

   ('Orar, pl. orare',
    'Orar, pl. orarii',
    'Vezi |definiția|orar|.'),

   ('Drajeu, pl. drajeuri',
    'Drajeu, pl. drajee',
    'Vezi |definiția|drajeu|.'),

   ('Preț mare',
    'Preț scump',
    'Un produs este scump atunci când prețul său este mare. $Preț scump$ este o struțo-cămilă.'),

   ('sandviș / sandvici / sanviș',
    'Oricum altcumva',
    'DEX (vezi |definiția|sandviș|) indică $sandviș$ ca formă corectă, iar celelalte două ca variante acceptate.'),

   ('Nes (prescurtare de la nescafe)',
    'Ness',
    'Vezi |definiția|nes|.'),

   ('Lubrifiant',
    'Lubrefiant',
    'Vezi |definiția|lubrifiant|.'),

   ('Președinție',
    'Președenție',
    'Vezi |definiția|președinție|. România are președinte, nu "președente"!'),

   ('Anticameră',
    'Antecameră',
    'Vezi |definiția|anticameră|. O ediție veche a Dicționarului de neologisme acceptă totuși și $antecameră$ ca variantă.'),

   ('Cotidian (ziar), pl. cotidiene',
    'Cotidian, pl. cotidiane',
    'Vezi |definiția|cotidian|.'),

   ('Eu continuu',
    'Eu continui',
    'Vezi definiția|continua|.'),

   ('Genuflexiune',
    'Genoflexiune',
    'Vezi |definiția|genuflexiune|.'),

   ('Remarcă, pl. remarce',
    'Remarcă, pl. remarci',
    'Vezi |definiția|remarcă|.'),

   ('Să aibă parte',
    'Să aibe parte',
    'Vezi |definiția|avea| (mai ales cea din Dicționarul ortografic).'),

   ('Abia',
    'Abea',
    'Vezi |definiția|abia|.'),

   ('El/ea se așază',
    'El/ea se așează',
    'Vezi |definiția|așeza|.'),

   ('Clasa întâi',
    'Clasa întâia\nClasa a-ntâia',
    'Vezi |definiția|întâi|.'),

   ('Colonel, pl. colonei',
    'Colonel, pl. coloneli',
    'Vezi |definiția|colonel|.'),

   ('Sticlă de un litru',
    'Sticlă de un kilogram',
    'Volumul se măsoară în litri. Dacă umplem o sticlă de un litru cu mercur, ea va cântări peste 13 kilograme, iar dacă o umplem cu ulei de floarea soarelui, ea va cântări numai 920 de grame! Deci "sticlă de un kilogram" nu are sens.'),

   ('Greșeală',
    'Greșală',
    'La fel ca și $repezeală, umezeală$ (nu $repezală, umezală).$'),

   ('Până la adânci bătrâneți',
    'Până la adânci bătrânețe',
    '$Adânci$ este la plural, deci și $bătrâneți$ trebuie pus la plural.'),

   ('Ora douăsprezece',
    'Ora doisprezece',
    'La fel cum spunem $ora două,$ nu $ora doi$.'),

   ('Am luat un pix de la ei și li l-am dat înapoi',
    'Am luat un pix de la ei și i l-am dat înapoi',
    'Deoarece am dat pixul înapoi mai multor persoane, corect este $...li l-am dat...$'),

   ('Obstetrică',
    'Obstretică',
    'Vezi |definiția|obstetric|.'),

   ('Nu există așa ceva',
    'Nu se există așa ceva',
    'Verbul |a exista| nu este reflexiv.'),

   ('Liniște! Să se audă musca!',
    'Liniște! Să nu se audă musca!',
    'Daca musca @nu@ se aude, probabil e gălăgie.'),

   ('Cu surle și tobe',
    'Cu surle și trâmbițe',
    'Surla și trâmbița sunt amândouă instrumente de suflat. O paradă militară are și instrumente de suflat și de percuție.'),

   ('Datorită publicului și încurajărilor acestuia...',
    'Datorită publicului și a încurajărilor acestuia...',
    'După $datorită$ se folosește dativul. Articolul posesiv $a (al, ai, ale)$ este specific genitivului, deci nu are ce căuta aici.');
