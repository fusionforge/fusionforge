-- by: pfalcon
-- purpose: add default due periods (in sec) for tools
--30*24*60*60,
--0*24*60*60,
--15*24*60*60

ALTER TABLE groups ADD COLUMN bug_due_period int;
UPDATE groups SET bug_due_period=(30*24*60*60);
ALTER TABLE groups ALTER COLUMN bug_due_period SET NOT NULL;
ALTER TABLE groups ALTER COLUMN bug_due_period SET DEFAULT 2592000;

ALTER TABLE groups ADD COLUMN patch_due_period int;
UPDATE groups SET patch_due_period=(0*24*60*60);
ALTER TABLE groups ALTER COLUMN patch_due_period SET NOT NULL;
ALTER TABLE groups ALTER COLUMN patch_due_period SET DEFAULT 5184000;

ALTER TABLE groups ADD COLUMN support_due_period int;
UPDATE groups SET support_due_period=(15*24*60*60);
ALTER TABLE groups ALTER COLUMN support_due_period SET NOT NULL;
ALTER TABLE groups ALTER COLUMN support_due_period SET DEFAULT 1296000;
