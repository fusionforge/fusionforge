INSERT INTO themes (theme_id,dirname, fullname) VALUES (1,'gforge', 'Default Theme');

ALTER TABLE theme_prefs
	ADD CONSTRAINT themeprefs_userid FOREIGN KEY (user_id)
	REFERENCES users(user_id);

ALTER TABLE theme_prefs
	ADD CONSTRAINT themeprefs_themeid FOREIGN KEY (user_theme)
	REFERENCES themes(theme_id);
