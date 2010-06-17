#permet de démarer via xinet svnserve afin d'activer les accés anonyme pour svn
#si les accés anonymes ne sont nécessaire, ce script n'est pas à utiliser.


cat >> /etc/xinetd.d/svn << "EOF"
# Begin /etc/xinetd.d/svn

service svn
{
        port                    = 3690
        socket_type             = stream
        protocol                = tcp
        wait                    = no
        user                    = svn
        server                  = /usr/bin/svnserve
        server_args             = -i -r /var/lib/gforge/chroot/
}

# End /etc/xinetd.d/svn
EOF