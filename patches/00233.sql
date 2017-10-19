alter table CrawlerUrl
  add siteId varchar(255) not null default '' after id,
  add key(siteId);

update CrawlerUrl set siteId = 'romlit' where url like 'http://www.romlit.ro/%';
update CrawlerUrl set siteId = 'dilema-veche' where url like 'http://dilemaveche.ro/%';
update CrawlerUrl set siteId = 'romania-libera' where url like 'http://romanialibera.ro/%';
update CrawlerUrl set siteId = 'gandul' where url like 'http://www.gandul.info/%';
update CrawlerUrl set siteId = 'zf' where url like 'http://www.zf.ro/%';
