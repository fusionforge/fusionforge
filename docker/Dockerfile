FROM centos:centos7
MAINTAINER FusionForge TrivialDev <franck.villaume@trivialdev.com>

RUN yum -y update; yum clean all
RUN yum -y install git openssh-server; yum clean all
RUN mkdir -p /opt/sources/ && cd /opt/sources && git clone https://scm.fusionforge.org/anonscm/git/fusionforge/fusionforge.git
RUN cd /opt/sources/fusionforge && source ./autoinstall/common-backports && \
    yum install -y make tar && \
    backports_rpm && \
    yum --enablerepo=epel install -y httpd-itk && \
    yum install -y gettext php-cli php-pgsql php-process php-mbstring php-pear-HTTP \
         httpd mod_dav_svn mod_ssl postgresql-server postgresql-contrib nscd \
         cvs subversion viewvc python-pycurl git gitweb xinetd \
         moin mod_wsgi python-psycopg2 \
         unoconv poppler-utils libreoffice-headless && \
    cd src/ && \
    make && \
    make install-base install-shell install-scm \
         install-plugin-scmcvs install-plugin-scmsvn install-plugin-scmgit \
         install-plugin-blocks install-plugin-moinmoin \
         install-plugin-taskboard install-plugin-message \
         install-plugin-repositoryapi

RUN yum --enablerepo=epel install -y supervisor

RUN mkdir /var/run/sshd
RUN /usr/sbin/sshd-keygen

USER postgres
RUN /usr/bin/initdb -D /var/lib/pgsql/data

USER root
ADD ./supervisord.conf /etc/supervisord.conf
ADD ./startpoint.sh /startpoint.sh
RUN chmod +x /startpoint.sh

EXPOSE 22 80 443
ENTRYPOINT ["/startpoint.sh"]
