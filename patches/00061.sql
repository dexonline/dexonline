create trigger commentInsertTS before insert on Comment for each row set new.createDate = unix_timestamp(), new.modDate = unix_timestamp();
create trigger commentUpdateTS before update on Comment for each row set new.modDate = unix_timestamp();

create trigger cookieInsertTS before insert on Cookie for each row set new.createDate = unix_timestamp();

create trigger definitionInsertTS before insert on Definition for each row set new.createDate = unix_timestamp(), new.modDate = unix_timestamp();
create trigger definitionUpdateTS before update on Definition for each row set new.modDate = unix_timestamp();

create trigger lexemInsertTS before insert on Lexem for each row set new.createDate = unix_timestamp(), new.modDate = unix_timestamp();
create trigger lexemUpdateTS before update on Lexem for each row set new.modDate = unix_timestamp();

create trigger lexemDefinitionMapInsertTS before insert on LexemDefinitionMap for each row set new.createDate = unix_timestamp(), new.modDate = unix_timestamp();
create trigger lexemDefinitionMapUpdateTS before update on LexemDefinitionMap for each row set new.modDate = unix_timestamp();

create trigger passwordTokenInsertTS before insert on PasswordToken for each row set new.createDate = unix_timestamp();

create trigger userWordBookmarkInsertTS before insert on UserWordBookmark for each row set new.createDate = unix_timestamp(), new.modDate = unix_timestamp();
create trigger userWordBookmarkUpdateTS before update on UserWordBookmark for each row set new.modDate = unix_timestamp();

create trigger wikiArticleInsertTS before insert on WikiArticle for each row set new.createDate = unix_timestamp(), new.modDate = unix_timestamp();
create trigger wikiArticleUpdateTS before update on WikiArticle for each row set new.modDate = unix_timestamp();

-- note: creationDate, not createDate
create trigger wordOfTheDayInsertTS before insert on WordOfTheDay for each row set new.creationDate = unix_timestamp();

create trigger divertaBookInsertTS before insert on diverta_Book for each row set new.createDate = unix_timestamp(), new.modDate = unix_timestamp();
create trigger divertaBookUpdateTS before update on diverta_Book for each row set new.modDate = unix_timestamp();
