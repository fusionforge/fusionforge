<Directory {core/plugins_path}/scmgit/cgi-bin>
  SetEnv GITWEB_CONFIG {core/config_path}/plugins/scmgit/gitweb.conf
  <IfVersion >= 2.3>
    Require all granted
  </IfVersion>
  <IfVersion < 2.3>
    order allow,deny
    allow from all
  </IfVersion>
</Directory>

SetEnv GIT_PROJECT_ROOT {scmgit/repos_path}
SetEnv GIT_HTTP_EXPORT_ALL

ScriptAlias /anonscm/git/ {core/plugins_path}/scmgit/libexec/git-http-backend/
ScriptAliasMatch ^/authscm/[^/]+/git/(.*) {core/plugins_path}/scmgit/libexec/git-http-backend/$1

<LocationMatch "^/authscm/[^/]+/git/">
  AuthType Basic
  AuthName "SCM for {core/forge_name}"
  AuthUserFile {core/data_path}/scmgit-userfile
  AuthGroupFile {core/data_path}/scmgit-groupfile
</LocationMatch>

ScriptAlias /anonscm/gitweb {core/plugins_path}/scmgit/cgi-bin/gitweb.cgi
ScriptAliasMatch ^/authscm/[^/]+/gitweb/([^/?]+) {core/plugins_path}/scmgit/cgi-bin/gitweb.cgi

<LocationMatch "^/authscm/[^/]+/gitweb/">
  AuthType Basic
  AuthName "SCM for {core/forge_name}"
  AuthUserFile {core/data_path}/scmgit-userfile
  AuthGroupFile {core/data_path}/scmgit-groupfile
</LocationMatch>
