-- Remove membership to 'Anonymous' and 'LoggedIn' roles, probably due to a bug in old versions
DELETE FROM pfo_user_role WHERE role_id IN
  (SELECT role_id FROM pfo_role WHERE role_class IN
    (SELECT class_id FROM pfo_role_class WHERE class_name != 'PFO_RoleExplicit'));
