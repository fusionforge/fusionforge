# Per-user macro to:
# - only set ITKUID if the user is legit
#   (avoids /authscm/root/ or /authscm/fusionforge/ access)
#   Maybe we can switch to LimitUIDRange, cf. 20-vhosts-scm.conf
# - specify explicit user (Require $user)

<Macro ScmUser $user>
  SetEnvIf Request_URI ^/authscm/$user/.* ITKUID=$user ITKGID=users
  # Note: when setting ITKUID, the user's groups (project memberships) are added
  # Note: it's important to set ITKGID, otherwise privilege separation breaks as gid stays 'www-data'

  <Location ${FF__core__url_prefix}authscm/$user/>
    Require user $user
  </Location>
</Macro>

# Note: macros defined in a separate file because they can't be
# defined twice (e.g. included twice from http and https virtualhosts)
