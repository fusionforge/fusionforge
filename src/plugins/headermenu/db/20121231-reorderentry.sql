UPDATE plugin_headermenu SET linktype = 'url' where linktype = 'URL';
ALTER TABLE plugin_headermenu ADD COLUMN ordering integer DEFAULT 0 NOT NULL;
