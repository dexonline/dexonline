alter table Tag add col tinyint unsigned not null default 0 after value;

update Tag set col = 1 where background = '#e01e00';
update Tag set col = 3 where background = '#ffc107';
update Tag set col = 4 where background = '#006615';
update Tag set col = 5 where background = '';
update Tag set col = 6 where background = '#ab0065';
update Tag set col = 7 where background = '#aaaaaa';
update Tag set col = 9 where background = '#ffe0b2';

update Tag set col = 13 where col = 0; -- so we can spot outliers

alter table Tag drop color, drop background;
alter table Tag rename column col to color;
