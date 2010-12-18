ALTER TABLE plugin_extratabs_main ADD COLUMN type INTEGER;
ALTER TABLE plugin_extratabs_main ALTER COLUMN type SET DEFAULT 0;
