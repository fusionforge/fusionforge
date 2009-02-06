ALTER TABLE users ADD COLUMN sys_state char(1) ;
ALTER TABLE users ALTER COLUMN sys_state SET  DEFAULT 'N';
ALTER TABLE groups ADD COLUMN sys_state char(1) ;
ALTER TABLE groups ALTER COLUMN sys_state SET  DEFAULT 'N';
ALTER TABLE user_group ADD COLUMN sys_state char(1) ;
ALTER TABLE user_group ALTER COLUMN sys_state SET  DEFAULT 'N';
ALTER TABLE user_group ADD COLUMN sys_cvs_state char(1) ;
ALTER TABLE user_group ALTER COLUMN sys_cvs_state SET  DEFAULT 'N';
