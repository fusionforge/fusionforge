ScriptAliasMatch ^/plugins/([^/]*)/cgi-bin/(.*) /opt/gforge/plugins/$1/cgi-bin/$2

Alias /anonscm/ /var/lib/gforge/chroot/scmrepos/

<DirectoryMatch /var/lib/gforge/chroot/scmrepos/[^/]*>
  Options -Indexes
</DirectoryMatch>
<DirectoryMatch /var/lib/gforge/chroot/scmrepos/[^/]*/.*>
  Options +Indexes
</DirectoryMatch>
