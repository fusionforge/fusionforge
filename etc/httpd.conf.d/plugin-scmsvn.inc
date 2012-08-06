<Location /svn>
  <IfModule mod_dav_svn.c>
  DAV svn
  SVNParentPath {scmsvn/repos_path}
  
  # how to authenticate a user
  AuthzSVNAccessFile {core/data_path}/svnroot-access
  AuthType Basic
  AuthName "Document repository"
  AuthUserFile {core/data_path}/svnroot-authfile
  </IfModule>
  
  # only authenticated users may access the repository
  Require valid-user
</Location>
