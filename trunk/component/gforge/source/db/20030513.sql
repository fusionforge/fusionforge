ALTER TABLE themes ADD COLUMN enabled BOOLEAN ;
ALTER TABLE themes ALTER enabled SET DEFAULT TRUE ;
UPDATE themes SET enabled=true;
