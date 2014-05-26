<Directory {core/source_path}/plugins/scmgit/cgi-bin>
  SetEnv GITWEB_CONFIG {core/config_path}/plugins/scmgit/gitweb.conf
</Directory>

SetEnv GIT_PROJECT_ROOT {scmgit/repos_path}
SetEnv GIT_HTTP_EXPORT_ALL

ScriptAlias /anonscm/git/ /usr/lib/git-core/git-http-backend/
ScriptAliasMatch ^/authscm/[^/]+/git/(.*) /usr/lib/git-core/git-http-backend/$1

<LocationMatch "^/authscm/[^/]+/git/">
  AuthType Basic
  AuthName "SCM for {core/forge_name}"
  AuthUserFile {core/data_path}/scmgit-userfile
  AuthGroupFile {core/data_path}/scmgit-groupfile
</LocationMatch>
