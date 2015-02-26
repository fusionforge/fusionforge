# Redirect URLs from FF < 6.0 (web_host -> scm_host)
# - use_ssh=1 => {core/web_host}/anonscm/git  -> {core/scm_host}/anonscm/git
Redirect permanent /anonscm/git https://{core/scm_host}/anonscm/git
# - use_dav=1 => {core/web_host}/scmrepos/git -> {core/scm_host}/authscm/git
Redirect permanent /scmrepos/git https://{core/scm_host}/authscm/git/
# - gitweb    => still at {core/web_host}/plugins/scmgit/cgi-bin/gitweb.cgi
#             => authenticated gitweb (for private repos) previously not supported
