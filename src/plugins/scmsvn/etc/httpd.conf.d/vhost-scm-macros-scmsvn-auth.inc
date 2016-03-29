<Macro ScmsvnUser $user_auth $project_auth $repo_auth>
<Location /authscm/$user_auth/svn/$project_auth/$repo_auth>
  DAV svn
  SVNPath ${FF__scmsvn__repos_path}/$project_auth.svn/$repo_auth
  # Note: ^ this need to be in the macro, doesn't work in a LocationMatch
  # This is pretty annoying, because this requires a plugin-specific macro :/
  # <LocationMatch /authscm/[^/]+/svn>
  # -> Can't open file '/srv/svn/authscm/format'
</Location>
</Macro>

# Note: macros defined in a separate file because they can't be
# defined twice (e.g. included twice from http and https virtualhosts)
