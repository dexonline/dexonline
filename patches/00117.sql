alter table Lexem add stopWord int not null after structStatus;
update Lexem set stopWord = 1 where formNoAccent in("a", "adj", "art", "avea", "avut", "ca", "care", "ce", "cu", "de", "despre", "din", "dinspre", "după", "el", "etc", "expr", "face", "fi", "fig", "fost", "fr", "în", "lat", "la", "mai", "nu", "pe", "pentru", "pl", "pop", "pr", "prez", "prin", "refl", "reg", "sau", "să", "său", "se", "sil", "sg", "suf", "și", "te", "tranz", "tu", "un", "var", "vb", "voi", "vrea");
update Lexem set stopWord = 1 where formNoAccent in ("al", "eu", "loc", "rar", "cf");
