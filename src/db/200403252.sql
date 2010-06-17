ALTER TABLE users ADD COLUMN theme_id INT;
--ALTER TABLE users ALTER COLUMN theme_id SET DEFAULT NOT NULL 1;
--UPDATE users SET theme_id=1
--	WHERE NOT EXISTS (select user_theme FROM theme_prefs WHERE user_id=users.user_id);
UPDATE users SET theme_id=
	(select user_theme FROM theme_prefs WHERE user_id=users.user_id)
	WHERE EXISTS (select user_theme FROM theme_prefs WHERE user_id=users.user_id);
--
--	If there is no theme_id=1 in the themes table, we could have a problem
--
UPDATE users SET theme_id=(SELECT min(theme_id) FROM themes WHERE enabled=true LIMIT 1)
	WHERE theme_id IS NULL;
ALTER TABLE users ADD CONSTRAINT users_themeid
        FOREIGN KEY (theme_id) REFERENCES themes(theme_id) MATCH FULL;
ALTER TABLE users ADD CONSTRAINT users_ccode
        FOREIGN KEY (ccode) REFERENCES country_code(ccode) MATCH FULL;
DROP TABLE theme_prefs;

