<Directory ${FF__scmhg__repos_path}>
  Include ${FF__core__config_path}/httpd.conf.d/disable-scripts.inc
</Directory>

ScriptAlias /plugins/scmhg/cgi-bin/ ${FF__core__plugins_path}/scmhg/www/cgi-bin/
AliasMatch ^/authscm/[^/]+/hg/(.*) ${FF__scmhg__repos_path}/$1
AliasMatch ^/anonscm/[^/]+/hg/(.*) ${FF__scmhg__repos_path}/$1

<Directory ${FF__core__plugins_path}/scmhg/www/cgi-bin >
	Options +ExecCGI +FollowSymLinks +Multiviews
	AddHandler cgi-script .cgi
	RewriteEngine On
	RewriteRule .* - [E=REMOTE_USER:%{ENV:REDIRECT_REMOTE_USER}]
	AllowOverride None
	Require all granted
</Directory>
