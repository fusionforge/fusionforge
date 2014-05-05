<Location /foosvn>
  <IfModule mod_dav_svn.c>
    DAV svn
    SVNParentPath {scmsvn/repos_path}

    <IfModule mod_authz_svn.c>
      # how to authenticate a user
      AuthzSVNAccessFile {core/data_path}/svnroot-access
      AuthType Basic
      AuthName "Document repository"
      AuthUserFile {core/data_path}/svnroot-authfile

      # only authenticated users may access the repository
      Require valid-user
    </IfModule>
    <IfModule !mod_authz_svn.c>
      # allow read-only browsing
      <LimitExcept GET PROPFIND OPTIONS REPORT>
      </LimitExcept>
    </IfModule>
  </IfModule>
</Location>

<LocationMatch "^/authsvn/">
    <IfModule mod_authz_svn.c>
      # how to authenticate a user
      AuthzSVNAccessFile {core/data_path}/svnroot-access
      AuthType Basic
      AuthName "SCM for {core/forge_name}"
      AuthUserFile {core/data_path}/svnroot-authfile

      # only authenticated users may access the repository
      Require valid-user
    </IfModule>
</LocationMatch>
