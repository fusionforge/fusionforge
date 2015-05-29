# Redirect URLs from FF < 6.0 (web_host -> scm_host)
# - use_ssh=1 => ${FF__core__web_host}/anonscm/git  -> ${FF__core__scm_host}/anonscm/git
Redirect permanent /anonscm/git https://${FF__core__scm_host}/anonscm/git
# - use_dav=1 => ${FF__core__web_host}/scmrepos/git -> ${FF__core__scm_host}/authscm/git
Redirect permanent /scmrepos/git https://${FF__core__scm_host}/authscm/git/
# - gitweb - anonymous
Redirect permanent /plugins/scmgit/cgi-bin/gitweb.cgi https://${FF__core__scm_host}/anonscm/gitweb/
# - gitweb - authenticated => previously not supported
