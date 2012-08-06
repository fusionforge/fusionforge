ScriptAliasMatch ^/plugins/([^/]*)/cgi-bin/(.*) {core/source_path}/plugins/$1/cgi-bin/$2

Alias {core/url_prefix}anonscm/ {core/data_path}/chroot/scmrepos/

<DirectoryMatch {core/data_path}/chroot/scmrepos/[^/]*>
  Options -Indexes
</DirectoryMatch>
<DirectoryMatch {core/data_path}/chroot/scmrepos/[^/]*/.*>
  Options +Indexes
</DirectoryMatch>
