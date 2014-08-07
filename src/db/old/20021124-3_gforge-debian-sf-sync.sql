insert into artifact_resolution values ('100','None');
select setval('artifact_resolution_id_seq',101);
select setval('users_pk_seq',101);

INSERT INTO doc_states VALUES (1,'active');
INSERT INTO doc_states VALUES (2,'deleted');
INSERT INTO doc_states VALUES (3,'pending');
INSERT INTO doc_states VALUES (4,'hidden');
INSERT INTO doc_states VALUES (5,'private');

drop table intel_agreement;

--
--      After 3pre5 release, sync with debian-sf database
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
SELECT SETVAL('supported_languages_language_id_seq',(select max(language_id) FROM supported_languages));
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

INSERT INTO artifact_resolution VALUES (102,'Accepted');
INSERT INTO artifact_resolution VALUES (103,'Out of Date');
INSERT INTO artifact_resolution VALUES (104,'Postponed');
INSERT INTO artifact_resolution VALUES (105,'Rejected');
