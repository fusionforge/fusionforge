-- by: pfalcon
-- purpose: add logo image support for any group

ALTER TABLE groups ADD COLUMN logo_image_id int;
UPDATE groups SET logo_image_id=100;
ALTER TABLE groups ALTER COLUMN logo_image_id SET NOT NULL;
ALTER TABLE groups ALTER COLUMN logo_image_id SET DEFAULT 100;
