FROM centos:centos7
LABEL maintainer "franck.villaume@trivialdev.com"
LABEL version "master"
LABEL name "FusionForge"
LABEL vendor "TrivialDev"
LABEL description "all-in-one"
LABEL license "GPLv2"

RUN yum -y install git openssh-server && \
    yum -y clean all;
WORKDIR /opt/sources
RUN git clone https://scm.fusionforge.org/anonscm/git/fusionforge/fusionforge.git
WORKDIR /opt/sources/fusionforge
RUN source ./autoinstall/common-backports && \
    yum install -y make tar && \
    backports_rpm && \
    yum --enablerepo=epel install -y httpd-itk && \
    yum install -y gettext \
                   php-cli \
                   php-pgsql \
                   php-process \
                   php-mbstring \
                   php-pear-HTTP \
                   httpd \
                   mod_dav_svn \
                   mod_ssl \
                   postgresql-server \
                   postgresql-contrib \
                   nscd \
                   cvs \
                   subversion \
                   viewvc \
                   python-pycurl \
                   git \
                   gitweb \
                   xinetd \
                   moin \
                   mod_wsgi \
                   python-psycopg2 \
                   unoconv \
                   poppler-utils \
                   libreoffice-headless \
                   cronie \
                   mediawiki && \
    yum -y clean all && \
    cd src/ && \
    make && \
    make install-base \
         install-shell \
         install-scm \
         install-plugin-scmcvs \
         install-plugin-scmsvn \
         install-plugin-scmgit \
         install-plugin-blocks \
         install-plugin-moinmoin \
         install-plugin-taskboard \
         install-plugin-message \
         install-plugin-repositoryapi \
         install-plugin-mediawiki \
         install-plugin-compactpreview \
         install-plugin-headermenu \
         install-plugin-gravatar \
         install-plugin-scmhook \
         install-plugin-webanalytics 

RUN yum --enablerepo=epel install -y supervisor \
                                     net-tools && \
    yum -y clean all;

RUN mkdir /var/run/sshd
RUN /usr/sbin/sshd-keygen

USER postgres
RUN /usr/bin/initdb -D /var/lib/pgsql/data

USER root
COPY ./supervisord.conf /etc/supervisord.conf
COPY ./startpoint.sh /startpoint.sh
COPY ./postinstall.sh /postinstall.sh
RUN chmod +x /startpoint.sh
RUN chmod +x /postinstall.sh

EXPOSE 22 80 443
ENTRYPOINT ["/startpoint.sh"]
CMD []
