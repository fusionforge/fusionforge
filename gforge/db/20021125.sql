drop table theme_prefs;
drop table themes;
DROP TABLE foundry_data;
DROP TABLE foundry_news;
DROP TABLE foundry_preferred_projects;
DROP TABLE foundry_project_downloads_agg;
DROP TABLE foundry_project_rankings_agg;
DROP TABLE foundry_projects;

insert into artifact_resolution values ('100','None');
select setval('artifact_resolution_id_seq',101);
select setval('users_pk_seq',101);


INSERT INTO doc_states VALUES (1,'active');
INSERT INTO doc_states VALUES (2,'deleted');
INSERT INTO doc_states VALUES (3,'pending');
INSERT INTO doc_states VALUES (4,'hidden');
INSERT INTO doc_states VALUES (5,'private');

CREATE TABLE frs_dlstats_file(
ip_address text,
file_id int,
month int,
day int
);

DROP TABLE cache_store;
ALTER TABLE users ADD COLUMN jabber_address text;
ALTER TABLE users ADD COLUMN jabber_only int;
DROP TABLE top_group;
drop table intel_agreement;
drop table stats_ftp_downloads;
drop table stats_http_downloads;
