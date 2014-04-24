-- Store destination e-mail for commit notifications
CREATE TABLE plugin_scmhook_scmsvn_commitemail (
  group_id int REFERENCES groups NOT NULL,
  dest text NOT NULL
);
