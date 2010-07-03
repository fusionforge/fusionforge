<Location /svn>
  <IfModule dav_svn>
  DAV svn
  SVNParentPath /var/lib/gforge/chroot/scmrepos/svn
  
  # how to authenticate a user
  AuthzSVNAccessFile /var/local/lib/gforge/svnroot-access
  AuthType Basic
  AuthName "Document repository"
  AuthUserFile /var/local/lib/gforge/svnroot-authfile
  SVNIndexXSLT "/svnindex.xsl"
  </IfModule>
  
  # only authenticated users may access the repository
  Require valid-user
</Location>
