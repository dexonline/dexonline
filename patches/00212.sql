alter table Source add structurable int not null after percentComplete;
update Source set structurable = 1 where urlName in ('dex', 'dex96', 'dex84', 'dex75', 'dexs88', 'dex09', 'dex12', 'dex16', 'dlrlc', 'dlrm', 'dn', 'mdn00', 'mdn08', 'nodex');
