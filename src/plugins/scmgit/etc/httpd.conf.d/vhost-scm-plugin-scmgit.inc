<Directory ${FF__scmgit__repos_path}>
  Include ${FF__core__config_path}/httpd.conf.d/disable-scripts.inc
</Directory>

# git-http-backend
SetEnv GIT_PROJECT_ROOT ${FF__scmgit__repos_path}
SetEnv GIT_HTTP_EXPORT_ALL
<Directory ${FF__core__plugins_path}/scmgit/libexec>
  Require all granted
</Directory>

# Pass supported URLs to git-http-backend, leave the rest to Apache
# Allows efficiency + directory	browsing (debugging) - cf. git-http-backend(1)
ScriptAliasMatch \
  "(?x)^/anonscm/git/(.*/(HEAD | \
                          info/refs | \
                          objects/(info/[^/]+ | \
                                   [0-9a-f]{2}/[0-9a-f]{38} | \
                                   pack/pack-[0-9a-f]{40}\.(pack|idx)) | \
                          git-(upload|receive)-pack))$" \
  ${FF__core__plugins_path}/scmgit/libexec/git-http-backend/$1
Alias /anonscm/git ${FF__scmgit__repos_path}

ScriptAliasMatch \
  "(?x)^/authscm/[^/]+/git/(.*/(HEAD | \
                            info/refs | \
                            objects/(info/[^/]+ | \
                                     [0-9a-f]{2}/[0-9a-f]{38} | \
                                     pack/pack-[0-9a-f]{40}\.(pack|idx)) | \
                            git-(upload|receive)-pack))$" \
  ${FF__core__plugins_path}/scmgit/libexec/git-http-backend/$1
AliasMatch ^/authscm/[^/]+/git/(.*) ${FF__scmgit__repos_path}/$1


# gitweb
<Directory ${FF__core__plugins_path}/scmgit/cgi-bin>
  SetEnv GITWEB_CONFIG ${FF__core__config_path}/plugins/scmgit/gitweb.conf
  Require all granted
  # Work-around for FF40 https://bugzilla.mozilla.org/show_bug.cgi?id=647010
  ErrorDocument 401 "Please <a href='' target='_top'>login to gitweb</a> to browse private source code.
  # FF40 discards this, hopefully they'll make it work in the near future:
  #Header always set Access-Control-Allow-Origin "${FF__core__web_host}"
  #Header always set Access-Control-Allow-Credentials "true"
</Directory>

ScriptAlias /anonscm/gitweb ${FF__core__plugins_path}/scmgit/cgi-bin/gitweb.cgi

ScriptAliasMatch ^/authscm/[^/]+/gitweb/$ ${FF__core__plugins_path}/scmgit/cgi-bin/gitweb.cgi


# Activity
ScriptAliasMatch ^/authscm/[^/]+/gitlog(.*) ${FF__core__plugins_path}/scmgit/libexec/gitlog.php$1
# Authentified via cookie in gitlog.php:
<LocationMatch "^/authscm/[^/]+/gitlog">
  Satisfy Any
</LocationMatch>
ScriptAlias /anonscm/gitlog ${FF__core__plugins_path}/scmgit/libexec/gitlog.php

# Redirect URLs from FF < 6.0 (scm_host -> scm_host)
# - use_ssh=1 => /anonscm/git  -> /anonscm/git
#   => OK
# - use_dav=1 => /scmrepos/git -> /authscm/git
Redirect permanent /scmrepos/git /authscm/git
