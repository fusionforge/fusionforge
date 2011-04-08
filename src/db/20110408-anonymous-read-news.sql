DELETE FROM pfo_role_setting WHERE role_id=1 AND section_name='new_forum';
INSERT INTO pfo_role_setting (SELECT 1, 'new_forum', group_id, 3 FROM groups WHERE unix_group_name = 'newsadmin') ;
