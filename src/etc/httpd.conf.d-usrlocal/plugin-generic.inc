ScriptAliasMatch ^/plugins/([^/]*)/cgi-bin/(.*) /usr/local/share/gforge/plugins/$1/cgi-bin/$2

Alias /anonscm/ /var/local/lib/gforge/chroot/scmrepos/

<DirectoryMatch /var/local/lib/gforge/chroot/scmrepos/[^/]*>
  Options -Indexes
</DirectoryMatch>
<DirectoryMatch /var/local/lib/gforge/chroot/scmrepos/[^/]*/.*>
  Options +Indexes
</DirectoryMatch>
