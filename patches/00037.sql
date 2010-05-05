update Lexem set formUtf8General = formNoAccent where formNoAccent != formUtf8General collate utf8_romanian_ci;
update InflectedForm set formUtf8General = formNoAccent where formNoAccent != formUtf8General collate utf8_romanian_ci;
