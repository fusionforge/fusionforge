<Macro ScmsvnUser $user>
<Location /authscm/$user/svn>
  DAV svn
  SVNParentPath ${FF__scmsvn__repos_path}
  # Note: ^ this need to be in the macro, doesn't work in a LocationMatch
  # This is pretty annoying, because we could trigger a lot less SCM_REPO
  # events if we didn't have to generate this plugin-specific config :/
  # <LocationMatch /authscm/[^/]+/svn>
  # -> Can't open file '/srv/svn/authscm/format'
</Location>
</Macro>

# Note: macros defined in a separate file because they can't be
# defined twice (e.g. included twice from http and https virtualhosts)
