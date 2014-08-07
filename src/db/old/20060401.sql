Begin;

UPDATE USERS SET language=1 WHERE language IN
	(select language_id FROM supported_languages
	WHERE classname in
	('Esperanto','Greek','Hebrew','Indonesian','Latin','Norwegian','Polish','Portuguese','Thai.tab'));
DELETE FROM doc_data where language_id IN (select language_id FROM supported_languages
        WHERE classname in
        ('Esperanto','Greek','Hebrew','Indonesian','Latin','Norwegian','Polish','Portuguese','Thai.tab'));
DELETE FROM supported_languages
	WHERE classname in
	('Esperanto','Greek','Hebrew','Indonesian','Latin','Norwegian','Polish','Portuguese','Thai.tab');

Commit;
