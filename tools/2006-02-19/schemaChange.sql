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
   '$Numai$ se foloseşte în construcţii afirmative, iar $decât$ se foloseşte în construcţii negative.'),

  ('Eu sunt',
   'Eu sînt, eu sânt',
   'Fără comentarii. Dacă ţineţi morţiş să vă împotriviţi normelor ortografice curente şi să scrieţi cum aţi învăţat, scrieţi $eu sînt$. $Eu sânt$ este greşit după ambele seturi de norme.'),

   ('Înger\nA hotărî\nReîntregire\nHotărât\nHotărând',
    'Ânger\nA hotărâ\nReântregire\nHotărît\nHotărînd',
    'Se foloseşte $î$, nu $â$, la începutul cuvântului, la sfârşitul cuvântului şi în cuvintele formate cu prefix dacă $î$ este prima literă din rădăcină. Participiul şi gerunziul verbelor nu fac excepţie: dacă participiul se formează cu $-ât$ sau dacă gerunziul se formează cu $-ând,$ folosim $â$.'),

   ('Fii cuminte!\nNu fi fraier!\nSă fii punctual.',
    'Fi cuminte!\nNu fii fraier!\nSă fi punctual.',
    'Pentru imperativul afirmativ $(fii cuminte!)$ şi pentru conjunctiv $(să fii punctual)$ n-am argumente, dar aşa e :) Imperativul negativ se formează întotdeauna cu $nu$ + infinitivul verbului: $nu veni, nu pleca, nu uita$. Deci, în cazul lui $a fi$, avem $nu fi fraier!$'),

   ('Filozofie',
    'Filosofie',
    'Aşa e în |DEX|filozo*|. Pentru $filozof$ este menţionată ca formă acceptată şi $filosof$, ceea ce ne face să credem că şi $filosofie$ este o formă acceptată pentru $filozofie$. Oricum, forma de bază este $filozofie, filozof$.'),

   ('Munţii noştri',
    'Munţii noştrii',
    'Singurul motiv pentru care s-ar adăuga un al doilea $i$ este articolul hotărât. Dar $noştri$ este pronume şi nu se articulează niciodată.'),

   ('Băiatul a cărui carte\nFata a cărei carte\nBăiatul ale cărui cărţi\nFata ale cărei cărţi\nBăieţii a căror carte\nFetele a căror carte\nBăieţii ale căror cărţi\nFetele ale căror cărţi\n\nBăiatul al cărui câine\nFata al cărei câine\nBăiatul ai cărui câini\nFata ai cărei câini\nBăieţii al căror câine\nFetele al căror câine\nBăieţii ai căror câini\nFetele ai căror câini',
    'Oricum altcumva',
    '"Băiatul $pauză pauză$ cărţi", un subiect drag nouă. Ca principiu de viaţă, e mult mai bine să vă opriţi din vorbire două secunde şi să judecaţi construcţia decât să o spuneţi cum s-o nimeri. Mai ales când audienţei îi pasă. :) Regula de construcţie este acordul "în cruce": $al / a / ai / ale$ se acordă cu obiectul $(cartea / cărţile)$, iar $cărui / cărei / căror$ se acordă cu posesorul ($băiatul / fata$ etc).'),

   ('Eu creez\nTu creezi\nEl creează',
    'Eu crez\nTu crezi\nEl crează',
    'Verbul $a crea$, deşi se termină în $-ea$, nu este un verb de grupa a II-a, ci de grupa I. Sufixul este $-a$, iar rădăcina este $cre-$. Pentru uşurinţă, conjugaţi-l ca şi pe $a lucra$, înlocuind $lucr-$ cu $cre-$. Oriunde $a lucra$ se conjugă cu un $e$, $a crea$ se conjugă cu doi ($eu lucrez - eu creez$). Formele $crez, crezi, crează$ există în limba română în cu totul alt context; ele sunt conjugări populare ale verbului $a crede$.'),

   ('Eu creez\nEu agreez\nEu întemeiez\nEu încleiez\n',
    'Eu creiez\nEu agreiez\nEu întemeez\nEu încleez\n',
    'Dacă infinitivul verbului se termină în $-ia, (a întemeia, a încleia)$ atunci litera $i$ apare şi la persoana I a timpului prezent, altfel nu $(a crea, a agrea).$'),

   ('Fumător inveterat\nAdevăr învederat',
    'Invers',
    'Paronime - vezi |definiţiile|înve*erat|'),

   ('@Masculin@\nEu însumi\nTu însuţi\nEl însuşi\nNoi înşine\nVoi înşivă\nEi înşişi\n\n@Feminin@\nEu însămi\nTu însăţi\nEa însăşi\nNoi însene\nVoi însevă\nEle înseşi (însele)\n',
    'Oricum altcumva',
    'Pronume de întărire - vezi |DEX|însumi|.'),

   ('Repercusiune',
    'Repercursiune',
    'Vezi |definiţia|repercusiune|.'),

   ('Oprobriu',
    'Oprobiu',
    'Vezi |definiţia|oprobriu|.'),

   ('Complet, complect',
    '',
    '|DEX-ul|comple*t| menţionează $complect$ ca variantă acceptată a lui $complet$. Totuşi, dacă până acum aţi folosit $complet$, vă sugerăm să-l folosiţi şi în continuare. :)'),

   ('Delincvent',
    'Delicvent',
    'Vezi |definiţia|delincvent|. $Delicvent$ este doar o formă acceptată (ca şi $complet / complect...)$'),

   ('Piuneză',
    'Pioneză',
    'Vezi |definiţia|piuneză|. $Pioneză$ este doar o formă acceptată.$'),

   ('Saxana',
    'Sarsana',
    'Vezi |definiţia|saxana|.'),

   ('Crevetă, pl. crevete',
    'Crevete, pl. creveţi',
    'Vezi |definiţia|crevetă|.'),

   ('Robinet, pl. robinete',
    'Robinet, pl. robineţi',
    'Vezi |definiţia|robinet|.'),

   ('Suport, pl. suporturi',
    'Suport, pl. suporţi',
    'Vezi |definiţia|suport|.'),

   ('Balot, pl. baloturi',
    'Balot, pl. baloţi',
    'Vezi |definiţia|balot|.'),

   ('Acumulator, pl. acumulatoare',
    'Acumulator, pl. acumulatori',
    'Vezi |definiţia|acumulator|. $Acumulatori$ este o formă acceptată de unele dicţionare.'),

   ('Laser, pl. lasere',
    'Laser, pl. laseri',
    'Vezi |definiţia|laser|.'),

   ('Reactor, pl. reactoare',
    'Reactor, pl. reactori',
    'Vezi |definiţia|reactor|.'),

   ('Festival, pl. festivaluri',
    'Festival, pl. festivale',
    'Vezi |definiţia|festival|.'),

   ('Jantă, pl. jante',
    'Jeantă, pl. jenţi',
    'Vezi |definiţia|jantă|.'),

   ('Acciz, pl. accize',
    'Acciză',
    'Vezi |definiţia|acciz|.'),

   ('Grep sau grepfrut sau grape-fruit',
    'Oricum altcumva',
    'Vezi |definiţiile|grepfrut|.'),

   ('Ingrediente (pl. lui ingredient)',
    'Ingredienţi',
    'Vezi |definiţia|ingredient|.'),

   ('Amândurora (dativul lui amândoi)',
    'Amândorura',
    'Vezi |definiţia|amândoi|. Cum spunem $tuturor$, nu $totorur$, la fel spunem şi $amândurora.'),

   ('De asemenea',
    'Deasemenea',
    '$Deasemenea$ nu există în DEX.'),

   ('Merită să aştepţi',
    'Se merită să aştepţi',
    'Verbul |a merita|| nu este reflexiv.'),

   ('Ce-i cu cărţile astea pe masă?',
    'Ce-s cu cărţile astea pe masă?',
    'Folosirea verbului $a fi$ la plural nu are sens şi nu are legătură cu faptul că vorbim despre mai multe cărţi. Un exemplu şi mai evident este $Ce este cu voi aici?$, nu $Ce sunteţi cu voi aici?$'),

   ('Serviciu',
    'Servici',
    '$Servici$ nu există în DEX.'),

   ('Mi-ar plăcea',
    'Mi-ar place',
    'Infinitivul verbului este $a plăcea$ (vezi |definiţia|plăcea|), iar condiţional-optativul se formează cu verbul auxiliar $a avea$ (în acest caz, $ar$) şi cu infinitivul.'),

   ('Cartea de pe noptieră\nCartea după noptieră',
    '-',
    '$|DEX|după|$ menţionează $de pe$ ca sens impropriu al lui $după$ (sensul 5). Totuşi, denaturarea sensului este foarte mare şi consider că folosirea lui $după$ este foarte neliterară.'),

   ('Cartea pe care am citit-o\nOmul pe care l-am întrebat\n\nCartea care mi-a plăcut\nOmul care mi-a răspuns',
    'Cartea care am citit-o\nOmul care l-am întrebat',
    'În toate cazurile avem de-a face cu propoziţii subordonate: "pe care am citit-o", "pe care l-am întrebat" etc. În primele două, "pe care" este complement direct, arătând asupra cui se efectuează acţiunea: $Pe cine am citit? Pe ea, pe carte. Pe cine am întrebat? Pe el, pe om.$ Complementul direct este în cazul acuzativ, de aceea este nevoie şi de prepoziţia $pe$.\n\nÎn ultimele două cazuri, "care" este subiect, arătând cine face acţiunea: $Cine a plăcut? Cartea. Cine a răspuns? Omul.$ Subiectul este în cazul nominativ, de aceea nu este nevoie de prepoziţia $pe$. $Cartea care am citit-o$ este o contaminare între cele două construcţii şi este incorectă.'),

   ('Optsprezece',
    'Oricum altcumva',
    'DEX (vezi |definiţia|optsprezece|) nu indică nici o altă variantă acceptată.'),

   ('Optulea',
    'Oricum altcumva',
    'DEX (vezi |definiţia|optulea|) nu indică nici o altă variantă acceptată.'),

   ('Mă doare apendicele.\nMă dor amigdalele.',
    'Mă doare apendicita.\nMă doare amigdalita.',
    'Apendicita şi amigdalita sunt boli. Ele nu sunt părţi ale corpului, deci nu au cum să doară. $Mă doare amigdalita$ este la fel de incorect ca şi $mă doare durerea de cap.$'),

   ('Un copil\nDoi copii\nCopiii se joacă\n\nUn cobai\nDoi cobai\nCobaii sunt rozătoare\n',
    'Copii se joacă\n\nCobaiii sunt rozătoare\n',
    'În cele mai multe cazuri, forma de plural articulat a substantivelor masculine se formează prin adăugarea literei $i$ la forma nearticulată. Substantivele care au doi $i$ la plural $(copii, ulii, vizitii)$ capătă trei $i$ în forma articulată $(copiii, uliii, vizitiii)$, iar substantivele care au un singur $i$ la plural $(oameni, cobai)$ capătă doi $i$ în forma articulată $(oamenii, cobaii).$'),

   ('Majoritatea salariaţilor este mulţumită...',
    'Majoritatea salariaţilor sunt mulţumiţi...',
    'Este pur şi simplu vorba despre acordul subiectului cu predicatul. $Majoritate$ este la singular, deci şi verbul $a fi$ trebuie pus la singular. Ce-i drept, $majoritate$ este un substantiv colectiv, cu sens de plural, dar forma gramaticală este în continuare cea de singular. Un exemplu şi mai elocvent este $tineretul merge la mare$ (nu $tineretul merg la mare).$'),

   ('Cel mai bine plătită doctoriţă',
    'Cea mai bine plătită doctoriţă',
    'Formarea superlativului cu expresia $cel mai$ se referă, în acest caz, la adverbul $bine,$ nu la adjectivul $plătită.$ Doctoriţa nu este $cea mai plătită,$ ci cea plătită $cel mai bine.$ Şi, întrucât adverbele nu au gen, număr sau caz, superlativul se formează implicit cu $cel$ pus la masculin.'),

   ('Până număr până la trei să plecaţi de-aici!',
    'Până număr la trei să plecaţi de-aici!',
    'Numărăm (de la unu) $până$ la trei. Probabil prezenţa primului $până$ creează confuzie, dar este necesar şi cel de-al doilea.'),

   ('Dă-te la o parte.\nDaţi-vă jos.',
    'De-te la o parte\nDădeţi-vă jos.',
    'Fără comentarii.'),

   ('Nu face asta.',
    'Nu fă asta.',
    'Imperativul negativ se formează cu infinitivul verbului: $nu mânca, nu vorbi, nu pleca.$ Deci, $nu face.$'),

   ('Chiuvetă',
    'Ghiuvetă',
    'Vezi |definiţia|chiuvetă|.'),

   ('A avansa',
    'A avansa înainte',
    '$A avansa$ implică noţiunea de înaintare.'),

   ('De ce râzi?',
    'De ce te râzi?',
    '|A râde|| este verb intranzitiv, nu reflexiv.'),

   ('Îmi place cineva',
    'Îmi place de cineva',
    '$Cineva$ este subiect gramatical, deci trebuie pus în cazul nominativ, fără prepoziţia $de$.'),

   ('Orar, pl. orare',
    'Orar, pl. orarii',
    'Vezi |definiţia|orar|.'),

   ('Drajeu, pl. drajeuri',
    'Drajeu, pl. drajee',
    'Vezi |definiţia|drajeu|.'),

   ('Preţ mare',
    'Preţ scump',
    'Un produs este scump atunci când preţul său este mare. $Preţ scump$ este o struţo-cămilă.'),

   ('sandviş / sandvici / sanviş',
    'Oricum altcumva',
    'DEX (vezi |definiţia|sandviş|) indică $sandviş$ ca formă corectă, iar celelalte două ca variante acceptate.'),

   ('Nes (prescurtare de la nescafe)',
    'Ness',
    'Vezi |definiţia|nes|.'),

   ('Lubrifiant',
    'Lubrefiant',
    'Vezi |definiţia|lubrifiant|.'),

   ('Preşedinţie',
    'Preşedenţie',
    'Vezi |definiţia|preşedinţie|. România are preşedinte, nu "preşedente"!'),

   ('Anticameră',
    'Antecameră',
    'Vezi |definiţia|anticameră|. O ediţie veche a Dicţionarului de neologisme acceptă totuşi şi $antecameră$ ca variantă.'),

   ('Cotidian (ziar), pl. cotidiene',
    'Cotidian, pl. cotidiane',
    'Vezi |definiţia|cotidian|.'),

   ('Eu continuu',
    'Eu continui',
    'Vezi definiţia|continua|.'),

   ('Genuflexiune',
    'Genoflexiune',
    'Vezi |definiţia|genuflexiune|.'),

   ('Remarcă, pl. remarce',
    'Remarcă, pl. remarci',
    'Vezi |definiţia|remarcă|.'),

   ('Să aibă parte',
    'Să aibe parte',
    'Vezi |definiţia|avea| (mai ales cea din Dicţionarul ortografic).'),

   ('Abia',
    'Abea',
    'Vezi |definiţia|abia|.'),

   ('El/ea se aşază',
    'El/ea se aşează',
    'Vezi |definiţia|aşeza|.'),

   ('Clasa întâi',
    'Clasa întâia\nClasa a-ntâia',
    'Vezi |definiţia|întâi|.'),

   ('Colonel, pl. colonei',
    'Colonel, pl. coloneli',
    'Vezi |definiţia|colonel|.'),

   ('Sticlă de un litru',
    'Sticlă de un kilogram',
    'Volumul se măsoară în litri. Dacă umplem o sticlă de un litru cu mercur, ea va cântări peste 13 kilograme, iar dacă o umplem cu ulei de floarea soarelui, ea va cântări numai 920 de grame! Deci "sticlă de un kilogram" nu are sens.'),

   ('Greşeală',
    'Greşală',
    'La fel ca şi $repezeală, umezeală$ (nu $repezală, umezală).$'),

   ('Până la adânci bătrâneţi',
    'Până la adânci bătrâneţe',
    '$Adânci$ este la plural, deci şi $bătrâneţi$ trebuie pus la plural.'),

   ('Ora douăsprezece',
    'Ora doisprezece',
    'La fel cum spunem $ora două,$ nu $ora doi$.'),

   ('Am luat un pix de la ei şi li l-am dat înapoi',
    'Am luat un pix de la ei şi i l-am dat înapoi',
    'Deoarece am dat pixul înapoi mai multor persoane, corect este $...li l-am dat...$'),

   ('Obstetrică',
    'Obstretică',
    'Vezi |definiţia|obstetric|.'),

   ('Nu există aşa ceva',
    'Nu se există aşa ceva',
    'Verbul |a exista| nu este reflexiv.'),

   ('Linişte! Să se audă musca!',
    'Linişte! Să nu se audă musca!',
    'Daca musca @nu@ se aude, probabil e gălăgie.'),

   ('Cu surle şi tobe',
    'Cu surle şi trâmbiţe',
    'Surla şi trâmbiţa sunt amândouă instrumente de suflat. O paradă militară are şi instrumente de suflat şi de percuţie.'),

   ('Datorită publicului şi încurajărilor acestuia...',
    'Datorită publicului şi a încurajărilor acestuia...',
    'După $datorită$ se foloseşte dativul. Articolul posesiv $a (al, ai, ale)$ este specific genitivului, deci nu are ce căuta aici.');
