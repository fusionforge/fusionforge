ScriptAliasMatch ^/plugins/([^/]*)/cgi-bin/(.*) {usr_share_gforge}/plugins/$1/cgi-bin/$2

Alias {sys_urlprefix}anonscm/ {var_lib_gforge}/chroot/scmrepos/

<DirectoryMatch {var_lib_gforge}/chroot/scmrepos/[^/]*>
  Options -Indexes
</DirectoryMatch>
<DirectoryMatch {var_lib_gforge}/chroot/scmrepos/[^/]*/.*>
  Options +Indexes
</DirectoryMatch>
