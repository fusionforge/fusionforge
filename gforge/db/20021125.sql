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

DROP SEQUENCE "foundry_preferred_projec_pk_seq";
DROP SEQUENCE "foundry_projects_pk_seq";

--
--	After 3pre5 release, sync with debian-sf database
--
DROP SEQUENCE group_cvs_history_pk_seq;
CREATE TABLE group_cvs_history (
id SERIAL,
group_id integer DEFAULT '0' NOT NULL,
user_name character varying(80) DEFAULT '' NOT NULL,
cvs_commits integer DEFAULT '0' NOT NULL,
cvs_commits_wk integer DEFAULT '0' NOT NULL,
cvs_adds integer DEFAULT '0' NOT NULL,
cvs_adds_wk integer DEFAULT '0' NOT NULL
);

CREATE INDEX groupcvshistory_groupid ON group_cvs_history(group_id);

--
--	Re-add themes table, which I hastily dropped in 3pre2
--
CREATE TABLE themes (
theme_id SERIAL,
dirname character varying(80),
fullname character varying(80)
);

CREATE TABLE theme_prefs (
user_id integer DEFAULT '0' NOT NULL,
user_theme integer DEFAULT '0' NOT NULL,
body_font character(80) DEFAULT '',
body_size character(5) DEFAULT '',
titlebar_font character(80) DEFAULT '',
titlebar_size character(5) DEFAULT '',
color_titlebar_back character(7) DEFAULT '',
color_ltback1 character(7) DEFAULT '',
PRIMARY KEY (user_id)
);
CREATE INDEX themeprefs_userid ON theme_prefs(user_id);

--INSERT INTO themes (dirname, fullname) VALUES ('default', 'Default Theme');
--These themes have to be converted to new Layout.class
--INSERT INTO themes (dirname, fullname) VALUES ('savannah_codex', 'Savannah CodeX');
--INSERT INTO themes (dirname, fullname) VALUES ('savannah_forest', 'Savannah Forest');
--INSERT INTO themes (dirname, fullname) VALUES ('savannah_reverse', 'Savannah Reverse');
--INSERT INTO themes (dirname, fullname) VALUES ('savannah_sad', 'Savannah Sad');
--INSERT INTO themes (dirname, fullname) VALUES ('savannah_savannah', 'Savannah Original');
--INSERT INTO themes (dirname, fullname) VALUES ('savannah_slashd', 'Savannah SlashDot');
--INSERT INTO themes (dirname, fullname) VALUES ('savannah_startrek', 'Savannah StarTrek');
--INSERT INTO themes (dirname, fullname) VALUES ('savannah_transparent', 'Savannah Transparent');
--INSERT INTO themes (dirname, fullname) VALUES ('savannah_water', 'Savannah Water');
--INSERT INTO themes (dirname, fullname) VALUES ('savannah_www.gnu.org', 'Savannah www.gnu.org');

UPDATE supported_languages SET language_code='en' where classname='English';
UPDATE supported_languages SET language_code='ja' where classname='Japanese';
UPDATE supported_languages SET language_code='iw' where classname='Hebrew';
UPDATE supported_languages SET language_code='es' where classname='Spanish';
UPDATE supported_languages SET language_code='th' where classname='Thai';
UPDATE supported_languages SET language_code='de' where classname='German';
UPDATE supported_languages SET language_code='it' where classname='Italian';
UPDATE supported_languages SET language_code='no' where classname='Norwegian';
UPDATE supported_languages SET language_code='sv' where classname='Swedish';
UPDATE supported_languages SET language_code='zh' where classname='Chinese';
UPDATE supported_languages SET language_code='nl' where classname='Dutch';
UPDATE supported_languages SET language_code='eo' where classname='Esperanto';
UPDATE supported_languages SET language_code='ca' where classname='Catalan';
UPDATE supported_languages SET language_code='ko' where classname='Korean';
UPDATE supported_languages SET language_code='bg' where classname='Bulgarian';
UPDATE supported_languages SET language_code='el' where classname='Greek';
UPDATE supported_languages SET language_code='id' where classname='Indonesian';
UPDATE supported_languages SET language_code='pt' where classname='Portuguese (Brazillian)';
UPDATE supported_languages SET language_code='pl' where classname='Polish';
UPDATE supported_languages SET language_code='pt' where classname='Portuguese';
UPDATE supported_languages SET language_code='ru' where classname='Russian';
UPDATE supported_languages SET language_code='fr' where classname='French';

ALTER TABLE trove_group_link ADD CONSTRAINT tgl_group_id_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;
ALTER TABLE trove_group_link ADD CONSTRAINT tgl_cat_id_fk
	FOREIGN KEY (trove_cat_id) REFERENCES trove_cat(trove_cat_id) MATCH FULL;
ALTER TABLE trove_agg ADD CONSTRAINT trove_agg_cat_id_fk
	FOREIGN KEY (trove_cat_id) REFERENCES trove_cat(trove_cat_id) MATCH FULL;
ALTER TABLE trove_agg ADD CONSTRAINT trove_agg_group_id_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;
ALTER TABLE trove_treesums ADD CONSTRAINT trove_treesums_cat_id_fk
	FOREIGN KEY (trove_cat_id) REFERENCES trove_cat(trove_cat_id) MATCH FULL;

ALTER TABLE groups ADD COLUMN use_ftp integer;
ALTER TABLE groups ALTER COLUMN use_ftp SET DEFAULT 1;
UPDATE groups SET use_ftp = 1;
ALTER TABLE groups ADD COLUMN use_tracker integer;
ALTER TABLE groups ALTER COLUMN use_tracker SET DEFAULT 1;
UPDATE groups SET use_tracker = 1;
ALTER TABLE groups ADD COLUMN use_frs integer;
ALTER TABLE groups ALTER COLUMN use_frs SET DEFAULT 1;
UPDATE groups SET use_frs = 1;
ALTER TABLE groups ADD COLUMN use_stats integer;
ALTER TABLE groups ALTER COLUMN use_stats SET DEFAULT 1;
UPDATE groups SET use_stats = 1;
ALTER TABLE groups ADD COLUMN enable_pserver integer;
ALTER TABLE groups ALTER COLUMN enable_pserver SET DEFAULT 1;
UPDATE groups SET enable_pserver = 1;
ALTER TABLE groups ADD COLUMN enable_anoncvs integer;
ALTER TABLE groups ALTER COLUMN enable_anoncvs SET DEFAULT 1;
UPDATE groups SET enable_anoncvs = 1;

ALTER TABLE supported_languages RENAME TO supported_languages_old;
DROP SEQUENCE supported_languages_pk_seq;
CREATE TABLE supported_languages (
	language_id SERIAL,
	name text,
	filename text,
	classname text,
	language_code character(5));
INSERT INTO supported_languages SELECT * FROM supported_languages_old;
SELECT SETVAL('supported_langu_language_id_seq',(select max(language_id) FROM supported_languages));
DROP TABLE supported_languages_old;
ALTER TABLE supported_languages ADD CONSTRAINT supported_languages_pkey PRIMARY KEY (language_id);
ALTER TABLE users ADD CONSTRAINT users_languageid_fk
	FOREIGN KEY (language) REFERENCES supported_languages(language_id) MATCH FULL;
ALTER TABLE doc_data ADD CONSTRAINT docdata_languageid_fk
	FOREIGN KEY (language_id) REFERENCES supported_languages(language_id) MATCH FULL;
UPDATE supported_languages SET language_code='pt_BR',
	classname='PortugueseBrazilian', name='Pt. Brazilian',
	filename='PortugueseBrazilian.class'
	where classname='PortugueseBrazillian';

--
-- Forum Rewrite
--
DROP INDEX forum_forumid_isfollowupto;
CREATE VIEW forum_user_vw AS select forum.*,users.user_name,users.realname 
	FROM forum,users WHERE forum.posted_by=users.user_id;
CREATE VIEW forum_group_list_vw AS SELECT forum_group_list.*, forum_agg_msg_count.count as total
    FROM forum_group_list LEFT JOIN forum_agg_msg_count USING (group_forum_id);
ALTER TABLE forum_group_list ADD CONSTRAINT forumgrouplist_groupid
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ON DELETE CASCADE;
ALTER TABLE forum ADD CONSTRAINT forum_groupforumid
	FOREIGN KEY (group_forum_id) REFERENCES forum_group_list(group_forum_id) MATCH FULL ON DELETE CASCADE;
ALTER TABLE forum ADD CONSTRAINT forum_userid
	FOREIGN KEY (posted_by) REFERENCES users(user_id) MATCH FULL;

--
--
--
update users set realname='Nobody' where user_id=100;
INSERT INTO artifact_resolution VALUES (102,'Accepted');
INSERT INTO artifact_resolution VALUES (103,'Out of Date');
INSERT INTO artifact_resolution VALUES (104,'Postponed');
INSERT INTO artifact_resolution VALUES (105,'Rejected');

