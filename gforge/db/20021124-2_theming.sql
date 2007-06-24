drop table theme_prefs;
drop table themes;
--
--      Re-add themes table, which I hastily dropped in 3pre2
--
CREATE TABLE themes (
theme_id SERIAL UNIQUE,
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
--These themes have to be converted to new Layout.class.php
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

