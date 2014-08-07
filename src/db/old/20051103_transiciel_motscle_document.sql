--- Modification de la base gforge pour permettre la recherche dans le contenu des documents stockés
--- Fabio Bertagnin - Transiciel Technologies
--- fbertagnin@mail.transiciel.com
--- 03/11/2005

ALTER TABLE doc_data ADD COLUMN data_words TEXT;
ALTER TABLE doc_data ALTER data_words SET DEFAULT '';
UPDATE doc_data SET data_words = '';
ALTER TABLE doc_data ALTER data_words SET NOT NULL;
