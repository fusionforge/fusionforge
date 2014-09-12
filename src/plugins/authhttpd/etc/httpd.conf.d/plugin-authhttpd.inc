<Location {core/url_prefix}/plugins/authhttpd/post-login.php>
  # You'll need to customize the following according to your needs

  AuthType Basic
  AuthName "Forge Authentication"

  AuthUserFile {core/config_path}/authhttpd.authfile
  Require valid-user
</Location>
