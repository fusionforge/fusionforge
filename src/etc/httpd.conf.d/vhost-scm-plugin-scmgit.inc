SetEnvIf Request_URI ^/authgit/([^/]+)/ ITKUID=$1
SetEnvIf Request_URI ^/authgit/([^/]+)/([^/]+)/ ITKGID=$2
