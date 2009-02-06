-- by: pfalcon
-- purpose: add field to allow ratings opt-out

ALTER TABLE users ADD COLUMN block_ratings int;
UPDATE users SET block_ratings=0;
ALTER TABLE users ALTER COLUMN block_ratings SET NOT NULL;
ALTER TABLE users ALTER COLUMN block_ratings SET DEFAULT 0;
