-- by: pfalcon
-- purpose: add default due periods (in sec) for tools
--30*24*60*60,
--0*24*60*60,
--15*24*60*60

ALTER TABLE groups ADD COLUMN bug_due_period int NOT NULL DEFAULT 2592000;
ALTER TABLE groups ADD COLUMN patch_due_period int NOT NULL DEFAULT 5184000;
ALTER TABLE groups ADD COLUMN support_due_period int NOT NULL DEFAULT 1296000;
