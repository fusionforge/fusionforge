<Directory {scmdarcs/repos_path}>
  Include {core/config_path}/httpd.conf.d/disable-scripts.inc
</Directory>
Alias {core/url_prefix}anonscm/darcs {scmdarcs/repos_path}

<Directory {core/plugins_path}/scmdarcs/cgi-bin>
  SetEnv DARCSWEB_CONFPATH {core/config_path}/plugins/scmdarcs/
</Directory>
