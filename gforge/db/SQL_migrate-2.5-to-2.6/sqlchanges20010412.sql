-- by: pfalcon
-- purpose: add logo image support for any group

ALTER TABLE groups ADD COLUMN logo_image_id int NOT NULL DEFAULT 100;

