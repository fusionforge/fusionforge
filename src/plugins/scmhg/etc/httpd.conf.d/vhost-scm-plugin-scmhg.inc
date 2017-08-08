<Directory ${FF__scmhg__repos_path}>
  Include ${FF__core__config_path}/httpd.conf.d/disable-scripts.inc
</Directory>

AliasMatch ^/authscm/[^/]+/hg/(.*)  ${FF__core__plugins_path}/scmhg/www/cgi-bin/$1
AliasMatch ^/anonscm/hg/(.*) ${FF__core__plugins_path}/scmhg/www/cgi-bin/$1

ScriptAlias /anonscm/scmhg/cgi-bin/ ${FF__core__plugins_path}/scmhg/www/cgi-bin/
ScriptAliasMatch ^/authscm/[^/]+/scmhg/cgi-bin/(.*) ${FF__core__plugins_path}/scmhg/www/cgi-bin/$1

<Directory ${FF__core__plugins_path}/scmhg/www/cgi-bin >
	Options +ExecCGI +FollowSymLinks +Multiviews
	SetHandler cgi-script
	RewriteEngine On
	RewriteRule .* - [E=REMOTE_USER:%{ENV:REDIRECT_REMOTE_USER}]
	AllowOverride None
	Require all granted
	# Work-around for FF40 https://bugzilla.mozilla.org/show_bug.cgi?id=647010
	ErrorDocument 401 "Please <a href='' target='_top'>login to gitweb</a> to browse private source code.
	# FF40 discards this, hopefully they'll make it work in the near future:
	#Header always set Access-Control-Allow-Origin "${FF__core__web_host}"
	#Header always set Access-Control-Allow-Credentials "true"
</Directory>
