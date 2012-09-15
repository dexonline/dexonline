create table NGram (id int(10) not null auto_increment, ngram VARCHAR(10), pos int(10), lexemId int(10), primary key (id), key(ngram), key(pos), key(lexemId), key(ngram, pos));
