-- Make sure pfo_role is clean, otherwise this breaks 'site_stats.php'

-- Remove orphan roles
DELETE FROM pfo_role_setting  WHERE role_id IN (SELECT role_id FROM pfo_role LEFT JOIN groups ON pfo_role.home_group_id = groups.group_id WHERE groups.group_id IS NULL AND home_group_id IS NOT NULL);
DELETE FROM pfo_user_role     WHERE role_id IN (SELECT role_id FROM pfo_role LEFT JOIN groups ON pfo_role.home_group_id = groups.group_id WHERE groups.group_id IS NULL AND home_group_id IS NOT NULL);
DELETE FROM role_project_refs WHERE role_id IN (SELECT role_id FROM pfo_role LEFT JOIN groups ON pfo_role.home_group_id = groups.group_id WHERE groups.group_id IS NULL AND home_group_id IS NOT NULL);
DELETE FROM pfo_role          WHERE role_id IN (SELECT role_id FROM pfo_role LEFT JOIN groups ON pfo_role.home_group_id = groups.group_id WHERE groups.group_id IS NULL AND home_group_id IS NOT NULL);

-- Add Foreign Key constraint to groups
ALTER TABLE pfo_role ADD FOREIGN KEY (home_group_id) REFERENCES groups(group_id) ON DELETE CASCADE ON UPDATE CASCADE;
