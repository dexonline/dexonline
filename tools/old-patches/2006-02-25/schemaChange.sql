CREATE TABLE Source (
  Id            int(11) NOT NULL auto_increment,
  ShortName     varchar(40),
  Name          varchar(255),
  Author        varchar(255),
  Publisher     varchar(255),
  Year          varchar(255),
  CanContribute bool,
  CanModerate   bool,
  PRIMARY KEY (Id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table words add sourceId int after source;

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

  ("DEX '96",
  "Dicționarul explicativ al limbii române",
  'Academia Română, Institutul de Lingvistică "Iorgu Iordan"',
  "Editura Univers Enciclopedic",
  "1996",
  0,
  1),

  ("DEX '84",
  "Dicționarul explicativ al limbii române",
  'Academia Română, Institutul de Lingvistică "Iorgu Iordan"',
  "Editura Academiei",
  "1984",
  0,
  1),

  ("DEX '75",
  "Dicționarul explicativ al limbii române",
  'Academia Română, Institutul de Lingvistică "Iorgu Iordan"',
  "Editura Academiei",
  "1975",
  0,
  1),

  ("DEX-S '88",
  "Supliment DEX",
  'Academia Română, Institutul de Lingvistică "Iorgu Iordan"',
  "Editura Academiei",
  "1988",
  0,
  1),

  ("Sinonime",
  "Dicționar de sinonime",
  "Mircea și Luiza Seche",
  "Editura Litera Internațional",
  "2002",
  1,
  1),

  ("Antonime",
  "Dicționar de antonime",
  "Mircea și Luiza Seche",
  "Editura Litera Internațional",
  "2002",
  1,
  1),

  ("Dicționar ortografic",
  "Dicționar ortografic al limbii române",
  "Colectiv",
  "Editura Litera Internațional",
  "2002",
  1,
  1),

  ("NODEX",
  "Noul dicționar explicativ al limbii române",
  "Litera Internațional",  "Editura Litera Internațional",
  "2002",
  1,
  1),

  ("DAR",
  "Dicționar de arhaisme și regionalisme",
  "Gh. Bulgăr și Gh. Constantinescu-Dobridor",
  "Editura Saeculum Vizual, București",
  "2002",
  1,
  1),

  ("DGE",
  "Dicționar gastronomic explicativ",
  "A.M. Gal",
  "Editura Gemma Print",
  "2003",
  1,
  1),

  ("DER",
  "Dicționarul etimologic român",
  "Alexandru Ciorănescu",
  "Universidad de la Laguna, Tenerife",
  "1958-1966",
  1,
  1),

  ("DLRA",
  "Dicționar al limbii române actuale (ediția a II-a revăzută și adăugită)",
  "Zorela Creța, Lucreția Mareș, Zizi Ștefănescu-Goangă, Flora Șuteu, Valeriu Șuteu",
  "Editura Curtea Veche",
  "1998",
  1,
  1),

  ("DLRC",
  "Dicționarul limbii române contemporane",
  "Vasile Breban",
  "Editura Științifică și Enciclopedică",
  "1980",
  1,
  1),

  ("DLRM",
  "Dicționarul limbii române moderne",
  'Academia Română, Institutul de Lingvistică "Iorgu Iordan"',
  "Editura Academiei, București",
  "1958",
  1,
  1),

  ("DMLR",
  "Dictionnaire morphologique de la langue roumaine",
  "A. Lombard, C. Gadei",
  "Editura Academiei, București",
  "1981",
  1,
  1),

  ("DN",
  "Dicționar de neologisme",
  "Florin Marcu și Constant Maneca",
  "Editura Academiei, București",
  "1986",
  1,
  1),

  ("DOOM",
  "Dicționar ortografic, ortoepic și morfologic al limbii române",
  "",
  "Editura Academiei, București",
  "1982",
  1,
  1),

  ("DOOM 2",
  "Dicționar ortografic, ortoepic și morfologic al limbii române, ediția a II-a",
  "",
  "Editura Univers Enciclopedic",
  "2005",
  1,
  1),

  ("MDA",
  "Micul dicționar academic",
  'Academia Română, Institutul de Lingvistică "Iorgu Iordan"',
  "Editura Univers Enciclopedic",
  "2002",
  1,
  1),

  ("MDN",
  "Marele dicționar de neologisme",
  "Florin Marcu",
  "Editura Saeculum",
  "2000",
  1,
  1),

  ("Neoficial",
  "Această sursă include definiții ale unor cuvinte de uz curent care nu există în nici unul din celelalte dicționare",
  "",
  "",
  "",
  1,
  1);

update words
  set sourceId = (select Id from Source where ShortName = "DEX '98")
  where source = "dex98";
update words
  set sourceId = (select Id from Source where ShortName = "DEX '96")
  where source = "dex96";
update words
  set sourceId = (select Id from Source where ShortName = "DEX '84")
  where source = "dex84";
update words
  set sourceId = (select Id from Source where ShortName = "DEX '75")
  where source = "dex75";
update words
  set sourceId = (select Id from Source where ShortName = "DEX-S '88")
  where source = "dexs88";
update words
  set sourceId = (select Id from Source where ShortName = "Sinonime")
  where source = "sino";
update words
  set sourceId = (select Id from Source where ShortName = "Antonime")
  where source = "anto";
update words
  set sourceId = (select Id from Source where ShortName = "Dicționar ortografic")
  where source = "dor";
update words
  set sourceId = (select Id from Source where ShortName = "NODEX")
  where source = "nodex";
update words
  set sourceId = (select Id from Source where ShortName = "DAR")
  where source = "dar";
update words
  set sourceId = (select Id from Source where ShortName = "DGE")
  where source = "dge";
update words
  set sourceId = (select Id from Source where ShortName = "DER")
  where source = "der";
update words
  set sourceId = (select Id from Source where ShortName = "DLRA")
  where source = "dlra";
update words
  set sourceId = (select Id from Source where ShortName = "DLRC")
  where source = "dlrc";
update words
  set sourceId = (select Id from Source where ShortName = "DLRM")
  where source = "dlrm";
update words
  set sourceId = (select Id from Source where ShortName = "DMLR")
  where source = "dmlr";
update words
  set sourceId = (select Id from Source where ShortName = "DN")
  where source = "dn";
update words
  set sourceId = (select Id from Source where ShortName = "DOOM")
  where source = "doom";
update words
  set sourceId = (select Id from Source where ShortName = "DOOM 2")
  where source = "doom2";
update words
  set sourceId = (select Id from Source where ShortName = "MDA")
  where source = "mda";
update words
  set sourceId = (select Id from Source where ShortName = "MDN")
  where source = "mdn";
update words
  set sourceId = (select Id from Source where ShortName = "Neoficial")
  where source = "none";

-- make sure that we've covered everything
select count(*) from words where sourceId is null;
