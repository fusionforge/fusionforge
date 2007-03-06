alter table filemodule_monitor add column id int not null default 0 primary key auto_increment first;
alter table frs_dlstats_filetotal_agg change column file_id file_id int not null default 0 primary key;
alter table group_cvs_history add column id int not null default 0 primary key auto_increment first;

DROP TABLE system_news;
DROP TABLE system_history;
DROP TABLE system_status;
DROP TABLE system_services;
DROP TABLE system_machines;


create index foundrynews_foundry_date_approved on foundry_news(foundry_id,approve_date,is_approved);
create index news_group_date on news_bytes(group_id,date);
create index news_date on news_bytes(date);
create index news_approved_date on news_bytes(is_approved,date);
