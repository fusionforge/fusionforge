INSERT INTO themes (dirname, fullname) SELECT dirname,fullname FROM themes where theme_id=1;
UPDATE themes SET dirname='gforge',fullname='Default Theme' WHERE theme_id=1;

ALTER TABLE theme_prefs
	ADD CONSTRAINT themeprefs_userid FOREIGN KEY (user_id)
	REFERENCES users(user_id);

ALTER TABLE theme_prefs
	ADD CONSTRAINT themeprefs_themeid FOREIGN KEY (user_theme)
	REFERENCES themes(theme_id);
