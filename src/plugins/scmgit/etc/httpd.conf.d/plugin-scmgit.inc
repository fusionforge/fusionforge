# git-http-backend
SetEnv GIT_PROJECT_ROOT {scmgit/repos_path}
SetEnv GIT_HTTP_EXPORT_ALL

# Pass supported URLs to git-http-backend, leave the rest to Apache
# Allows efficiency + directory	browsing (debugging)
ScriptAliasMatch \
  "(?x)^/anonscm/git/(.*/(HEAD | \
                          info/refs | \
                          objects/(info/[^/]+ | \
                                   [0-9a-f]{2}/[0-9a-f]{38} | \
                                   pack/pack-[0-9a-f]{40}\.(pack|idx)) | \
                          git-(upload|receive)-pack))$" \
  {core/plugins_path}/scmgit/libexec/git-http-backend/$1
Alias /anonscm/git {scmgit/repos_path}

ScriptAliasMatch \
  "(?x)^/authscm/[^/]+/git/(.*/(HEAD | \
                            info/refs | \
                            objects/(info/[^/]+ | \
                                     [0-9a-f]{2}/[0-9a-f]{38} | \
                                     pack/pack-[0-9a-f]{40}\.(pack|idx)) | \
                            git-(upload|receive)-pack))$" \
  {core/plugins_path}/scmgit/libexec/git-http-backend/$1
AliasMatch ^/authscm/[^/]+/git/(.*) /srv/git/$1


# gitweb
<Directory {core/plugins_path}/scmgit/cgi-bin>
  SetEnv GITWEB_CONFIG {core/config_path}/plugins/scmgit/gitweb.conf
  Require all granted
</Directory>

ScriptAlias /anonscm/gitweb {core/plugins_path}/scmgit/cgi-bin/gitweb.cgi

ScriptAliasMatch ^/authscm/[^/]+/gitweb/ {core/plugins_path}/scmgit/cgi-bin/gitweb.cgi
