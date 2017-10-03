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
RUN git checkout 6.1
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
                   php-pear-Text_CAPTCHA \
                   php-markdown \
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
                   mediawiki \
                   vsftpd \
                   mercurial \
                   dejavu-fonts-common && \
    yum -y clean all

RUN yum --enablerepo=epel install -y supervisor \
                                     net-tools && \
    yum -y clean all

RUN curl https://kojipkgs.fedoraproject.org//packages/httpd-itk/2.4.7.04/1.el7/x86_64/httpd-itk-2.4.7.04-1.el7.x86_64.rpm -o httpd-itk-2.4.7.04-1.el7.x86_64.rpm && \
    yum downgrade -y httpd-itk-2.4.7.04-1.el7.x86_64.rpm

RUN mkdir /var/run/sshd
RUN /usr/sbin/sshd-keygen

COPY ./supervisord.conf /etc/supervisord.conf
COPY ./startpoint.sh /startpoint.sh
COPY ./postinstall.sh /postinstall.sh
RUN chmod +x /startpoint.sh
RUN chmod +x /postinstall.sh

VOLUME ["/var/lib/pgsql", "/etc/fusionforge", "/var/lib/fusionforge"]
EXPOSE 21 22 80 443
ENTRYPOINT ["/startpoint.sh"]
CMD []
