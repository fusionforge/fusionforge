ScriptAliasMatch ^/plugins/([^/]*)/cgi-bin/(.*) {core/source_path}/plugins/$1/cgi-bin/$2

Alias {core/url_prefix}anonscm/ {core/data_path}/chroot/scmrepos/

<DirectoryMatch {core/data_path}/chroot/scmrepos/[^/]*>
  Options -Indexes
</DirectoryMatch>
<DirectoryMatch {core/data_path}/chroot/scmrepos/[^/]*/.*>
  # Enable directory index listing, but disable symlinks and CGI
  Options Indexes

  # Permit HTTP Auth for somewhat private projects (mechanism
  # other than the SCM anon bit in the forge)
  AllowOverride AuthConfig

  # Prevent cookie theft in case a script does manage to execute
  RequestHeader unset Cookie

  # Disable all scripting engines (taken from Savannah)
  # except for empty filenames == directory index
  <Files "?*">
    SetHandler default
  </Files>

  # Disable PHP5 explicitly for security (CVE-2014-0468)
  <IfModule mod_php5.c>
    php_admin_flag engine off
  </IfModule>
</DirectoryMatch>
