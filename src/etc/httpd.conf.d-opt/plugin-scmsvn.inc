<Location /svn>
  DAV svn
  SVNParentPath /var/lib/gforge/svnroot
  
  # how to authenticate a user
  AuthzSVNAccessFile /var/lib/gforge/svnroot-access
  AuthType Basic
  AuthName "Document repository"
  AuthUserFile /var/lib/gforge/svnroot-authfile
  SVNIndexXSLT "/svnindex.xsl"
  
  # only authenticated users may access the repository
  Require valid-user
</Location>
