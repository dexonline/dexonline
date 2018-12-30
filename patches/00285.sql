alter table Model drop flag;
alter table UserWordBookmark drop comment;

delete from AdsClick where skey not in (select skey from AdsLink);
