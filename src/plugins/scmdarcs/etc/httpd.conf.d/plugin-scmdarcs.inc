<Directory ${FF__scmdarcs__repos_path}>
  Include ${FF__core__config_path}/httpd.conf.d/disable-scripts.inc
</Directory>
Alias ${FF__core__url_prefix}anonscm/darcs ${FF__scmdarcs__repos_path}

<Directory ${FF__core__plugins_path}/scmdarcs/cgi-bin>
  SetEnv DARCSWEB_CONFPATH ${FF__core__config_path}/plugins/scmdarcs/
e their variability
</Directory>
