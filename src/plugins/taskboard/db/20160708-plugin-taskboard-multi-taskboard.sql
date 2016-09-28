ALTER TABLE plugin_taskboard
   ADD taskboard_name text NOT NULL DEFAULT ''::text,
   ADD description text NOT NULL DEFAULT ''::text;

UPDATE plugin_taskboard as ptb
   SET taskboard_name = 'Default Taskboard for '|| group_name, 
   description = 'Default Taskboard for ' || group_name
FROM plugin_taskboard
NATURAL INNER JOIN groups;