<Macro ScmsvnRepoAnon $project_anon $repo_anon>
<Location /anonscm/svn/$project_anon/$repo_anon>
  DAV svn
  SVNPath ${FF__scmsvn__repos_path}/$project_anon.svn/$repo_anon
  # Note: ^ this need to be in the macro, doesn't work in a LocationMatch
  # This is pretty annoying, because this requires a plugin-specific macro :/
  # <LocationMatch /authscm/[^/]+/svn>
  # -> Can't open file '/srv/svn/authscm/format'
</Location>
</Macro>

# Note: macros defined in a separate file because they can't be
# defined twice (e.g. included twice from http and https virtualhosts)
