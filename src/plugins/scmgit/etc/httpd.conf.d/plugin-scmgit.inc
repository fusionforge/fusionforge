<Directory {core/source_path}/plugins/scmgit/cgi-bin>
  SetEnv GITWEB_CONFIG {core/config_path}/plugins/scmgit/gitweb.conf
  <IfVersion >= 2.3>
    Require all granted
  </IfVersion>
  <IfVersion < 2.3>
    order allow,deny
    allow from all
  </IfVersion>
</Directory>
