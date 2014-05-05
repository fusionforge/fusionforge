SetEnvIf Request_URI ^/authsvn/([^/]+)/ ITKUID=$1
SetEnvIf Request_URI ^/authsvn/([^/]+)/([^/]+)/ ITKGID=$2
