UPDATE users SET theme_id=(SELECT theme_id FROM themes WHERE dirname='funky') WHERE theme_id=(SELECT theme_id FROM themes WHERE dirname='gforge');
DELETE FROM themes WHERE dirname='gforge';
