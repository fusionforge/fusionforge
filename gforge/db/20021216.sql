--
-- If the theme with theme_id=1 exits it is copied at the end of the list
--
INSERT INTO themes (dirname, fullname) SELECT dirname,fullname FROM themes where theme_id=1;
DELETE FROM themes WHERE theme_id=1;
INSERT INTO themes (theme_id,dirname, fullname) VALUES (1,'gforge', 'Default Theme');

ALTER TABLE theme_prefs
	ADD CONSTRAINT themeprefs_userid FOREIGN KEY (user_id)
	REFERENCES users(user_id);

ALTER TABLE theme_prefs
	ADD CONSTRAINT themeprefs_themeid FOREIGN KEY (user_theme)
	REFERENCES themes(theme_id);
