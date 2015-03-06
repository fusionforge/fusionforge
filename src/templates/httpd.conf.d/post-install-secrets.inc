# Settings for configuration variables that are normally kept in a configuration file unreadable to www-data
# Restart Apache after changing these

RequestHeader unset GForgeDbhost
RequestHeader append GForgeDbhost "${FF__core__database_host}"

RequestHeader unset GForgeDbport
RequestHeader append GForgeDbport "${FF__core__database_port}"

RequestHeader unset GForgeDbname
RequestHeader append GForgeDbname "${FF__core__database_name}"

RequestHeader unset GForgeDbuser
RequestHeader append GForgeDbuser "${FF__core__database_user}"

RequestHeader unset GForgeDbpasswd
RequestHeader append GForgeDbpasswd "${FF__core__database_password}"

RequestHeader unset GForgeLdapPasswd
RequestHeader append GForgeLdapPasswd "${FF__core__ldap_password}"

RequestHeader unset GForgeSessionKey
RequestHeader append GForgeSessionKey "${FF__core__session_key}"
