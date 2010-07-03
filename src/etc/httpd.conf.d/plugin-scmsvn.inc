<Location /svn>
  <IfModule dav_svn>
  DAV svn
  SVNParentPath {scmsvn/repos_path}
  
  # how to authenticate a user
  AuthzSVNAccessFile {core/data_path}/svnroot-access
  AuthType Basic
  AuthName "Document repository"
  AuthUserFile {core/data_path}/svnroot-authfile
  SVNIndexXSLT "/svnindex.xsl"
  </IfModule>
  
  # only authenticated users may access the repository
  Require valid-user
</Location>
