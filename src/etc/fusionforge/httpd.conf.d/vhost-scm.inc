ServerName ${FF__core__scm_host}

SetEnvIf Request_URI . ITKUID=${FF__core__apache_user}
SetEnvIf Request_URI . ITKGID=${FF__core__apache_group}

IncludeOptional ${FF__core__config_path}/httpd.conf.d/vhost-scm-plugin-scm*.inc
IncludeOptional ${FF__core__data_path}/scm*-auth*.inc

# Run programs under a specific uid:
AssignUserIDExpr %{reqenv:ITKUID}
AssignGroupIDExpr %{reqenv:ITKGID}

<LocationMatch "^/authscm/[^/]+/">
  AuthType Basic
  AuthName "SCM for FusionForge"
  AuthUserFile ${FF__core__data_path}/scm-passwd
</LocationMatch>

# Cf. 05-config-macros-scm.conf for the 'Require User' directive

# Could be done with mod-authnz-pam, but it isn't packaged for CentOS :/
#<LocationMatch "^/authscm/[^/]+/">
#  AuthType Basic
#  AuthName "SCM for FusionForge"
#  AuthBasicProvider PAM
#  AuthPAMService sshd
#</LocationMatch>

# Could be done with mod-authnz-external+pwauth, but it isn't packaged
# for CentOS 7 :/
# -> would avoid leaking the password hashes
#AddExternalAuth pwauth /usr/sbin/pwauth
#SetExternalAuthMethod pwauth pipe
#<LocationMatch "^/authscm/[^/]+/">
#  AuthType Basic
#  AuthName "SCM for FusionForge"
#  AuthBasicProvider external
#  AuthExternal pwauth
#</LocationMatch>

# Could be done with mod-auth-pgsql, but it isn't packaged at all for
# RPM :/
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
