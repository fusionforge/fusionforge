ScriptAliasMatch ^/plugins/([^/]*)/cgi-bin/(.*) /opt/fusionforge/plugins/$1/cgi-bin/$2

Alias /anonscm/ /opt/fusionforge/data/chroot/scmrepos/

<DirectoryMatch /opt/fusionforge/data/chroot/scmrepos/[^/]*>
  Options -Indexes
</DirectoryMatch>
<DirectoryMatch /opt/fusionforge/data/chroot/scmrepos/[^/]*/.*>
  Options +Indexes
</DirectoryMatch>
