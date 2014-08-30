ALTER TABLE stats_cvs_user ADD COLUMN updates INT;
ALTER TABLE stats_cvs_group ADD COLUMN updates INT;
ALTER TABLE stats_cvs_user ALTER COLUMN updates SET DEFAULT 0;
ALTER TABLE stats_cvs_group ALTER COLUMN updates SET DEFAULT 0;
ALTER TABLE stats_cvs_user ADD COLUMN deletes INT;
ALTER TABLE stats_cvs_group ADD COLUMN deletes INT;
ALTER TABLE stats_cvs_user ALTER COLUMN deletes SET DEFAULT 0;
ALTER TABLE stats_cvs_group ALTER COLUMN deletes SET DEFAULT 0;
