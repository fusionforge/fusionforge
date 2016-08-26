ServerName ${FF__core__scm_host}
ServerPath ${FF__core__url_prefix}

SetEnvIf Request_URI . ITKUID=${FF__core__apache_user}
SetEnvIf Request_URI . ITKGID=${FF__core__apache_group}

# Run programs under a specific uid:
AssignUserIDExpr %{reqenv:ITKUID}
AssignGroupIDExpr %{reqenv:ITKGID}

<LocationMatch "^/authscm/[^/]+/">
  AuthType Basic
  AuthName "${FF__core__apache_auth_realm}"
  AuthUserFile ${FF__core__data_path}/scm-passwd
</LocationMatch>
# Cf. vhost-scm-macros.conf for the 'Require User' directive

# Load plugins after the above LocationMatch to allow specialization
IncludeOptional ${FF__core__config_path}/httpd.conf.d/vhost-scm-plugin-scm*.inc
IncludeOptional ${FF__core__data_path}/scm*-auth*.inc

Alias /robots.txt ${FF__core__source_path}/common/robots/scm.txt
<Directory "${FF__core__source_path}/common/robots/">
  Require all granted
</Directory>

# Configuration notes to setup without 'scm-passwd'

# with mod-authnz-pam (not packaged for CentOS)
#<LocationMatch "^/authscm/[^/]+/">
#  AuthType Basic
#  AuthName "SCM for FusionForge"
#  AuthBasicProvider PAM
#  AuthPAMService sshd
#</LocationMatch>

# with mod-authnz-external+pwauth (not packaged for CentOS 7)
# -> would avoid leaking the password hashes
#AddExternalAuth pwauth /usr/sbin/pwauth
#SetExternalAuthMethod pwauth pipe
#<LocationMatch "^/authscm/[^/]+/">
#  AuthType Basic
#  AuthName "SCM for FusionForge"
#  AuthBasicProvider external
#  AuthExternal pwauth
#</LocationMatch>

# with mod-auth-pgsql (no RPM package at all)
#<LocationMatch "^/authscm/[^/]+/">
#  AuthType Basic
#  AuthName "SCM for FusionForge"
#  AuthBasicProvider     pgsql
#  Auth_PG_host          127.0.0.1
#  Auth_PG_user          fusionforge_nss
#  #Auth_PG_pwd          <no password>
#  Auth_PG_database      fusionforge
#  Auth_PG_pwd_table     nss_passwd
#  Auth_PG_uid_field     login
#  Auth_PG_pwd_field     passwd
#  Auth_PG_encrypted     on
#  Auth_PG_hash_type     CRYPT
#</LocationMatch>
