-- by: pfalcon
-- purpose: add field to allow ratings opt-out

ALTER TABLE users ADD COLUMN block_ratings int NOT NULL DEFAULT 0;

