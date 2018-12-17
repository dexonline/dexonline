update Inflection set rank = rank + 2 where modelType = 'V' and rank >= 7;
update Inflection set rank = rank + 1 where modelType = 'V' and rank = 6;

insert into Inflection
  set description = 'Verb, Participiu pasiv lung',
  modelType = 'V',
  rank = 6;

insert into Inflection
  set description = 'Verb, Gerunziu lung',
  modelType = 'V',
  rank = 8;

update Lexeme set staleParadigm = 1 where modelType in ('V', 'VT');
