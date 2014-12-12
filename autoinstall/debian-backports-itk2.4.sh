#!/bin/bash
# Backports Apache 2.4 and dependent modules for Debian Wheezy

# Based on my
# https://wiki.debian.org/BuildingFormalBackports#Self-contained_example_for_Apache_2.4
# Takes ~1h

set -e

# Prepare local repo
mkdir -p /usr/src/backports/wheezy/
touch /usr/src/backports/wheezy/Packages
cat <<'EOF' > /usr/src/backports/wheezy/D70results
#!/bin/bash
# Make sure local repo is updated before building
cd /usr/src/backports/wheezy/
dpkg-scanpackages . /dev/null > Packages
# It would be better to sign but that's complex in the context of pbuilder
# Also 'pbuilder --allow-untrusted' only works with the default satisfydepends
echo 'APT::Get::AllowUnauthenticated "true";' > /etc/apt/apt.conf.d/99AllowUnauthenticated
apt-get update
EOF
chmod 755 /usr/src/backports/wheezy/D70results

# Create initial environment
if [ ! -e /var/cache/pbuilder/base-wheezy-bpo.tar.gz ]; then
    sudo pbuilder --create --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
      --distribution wheezy \
      --othermirror "deb http://security.debian.org/ wheezy/updates main|deb http://ftp.fr.debian.org/debian wheezy-backports main|deb file:///usr/src/backports/wheezy ./" \
      --bindmounts /usr/src/backports/wheezy/
fi
# Update regularly:
# sudo pbuilder --update --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz --bindmounts /usr/src/backports/wheezy/

# Setup identity
export DEBEMAIL="fusionforge-general@lists.fusionforge.org" 
export DEBFULLNAME="FusionForge Hackers"

# Configure build
export DEB_BUILD_OPTIONS="parallel=$(nproc) nocheck"
#export DEB_BUILD_OPTIONS="parallel=$(nproc)"

# Add source for 'apt-get source'
echo "deb-src http://ftp.fr.debian.org/debian/ jessie main" \
  | sudo tee /etc/apt/sources.list.d/jessie-src.list
sudo apt-get update

# Dependencies to add in our local repo
apt-get source apr/jessie
(
    cd apr-1.5.1/
    dch --bpo "No changes."
    sed -i '1 s/~bpo/~ff/' debian/changelog
    pdebuild --debbuildopts '-v1.4.6-3+deb7u1' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)
apt-get source apr-util/jessie
(
    cd apr-util-1.5.3/
    dch --bpo "No changes."
    sed -i '1 s/~bpo/~ff/' debian/changelog
    pdebuild --debbuildopts '-v1.4.1-3' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)

# Apache 2.4 itself
apt-get source apache2/jessie
(
    cd apache2-2.4.10/
    dch --bpo "Note: depends on backported libapr."
    sed -i '1 s/~bpo/~ff/' debian/changelog
    pdebuild --debbuildopts '-v2.2.22-13+deb7u3' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)

#####

# PHP 5.6 dependency
apt-get source libgd2/jessie
(
    cd libgd2-2.1.0/
    dch --bpo "No changes."
    sed -i '1 s/~bpo/~ff/' debian/changelog
    pdebuild --debbuildopts '-v2.0.36~rc1~dfsg-6.1' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)

# PHP 5.6 for Apache 2.4
apt-get source php5/jessie
(
    cd php5-5.6.0+dfsg/
    dch --bpo "Note: libapache2-mod-php5 rebuilt against Apache 2.4."
    sed -i '1 s/~bpo/~ff/' debian/changelog
    pdebuild --debbuildopts '-v5.4.4-14+deb7u14' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)

# Run-time dependency for libapache2-mod-php5
apt-get source dh-php5/jessie
(
    cd dh-php5-0.2/
    dch --bpo "No changes."
    sed -i '1 s/~bpo/~ff/' debian/changelog
    pdebuild --debbuildopts '-v0' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)
apt-get source php-json/jessie
(
    cd php-json-1.3.6/
    dch --bpo "No changes."
    pdebuild --debbuildopts '-v0~0' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)

# PHPUnit indirect dependency - rebuilt against PHP 5.6
apt-get source xdebug/jessie
(
    cd xdebug-2.2.5/
    dch --bpo "Note: rebuilt against PHP 5.6."
    sed -i '1 s/~bpo/~ff/' debian/changelog
    pdebuild --debbuildopts '-v2.2.1-2' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)

# mpm_itk - separate package in Jessie
apt-get source mpm-itk/jessie
(
    cd mpm-itk-2.4.7-02/
    dch --bpo "Note: compiled against Apache 2.4."
    sed -i '1 s/~bpo/~ff/' debian/changelog
    pdebuild --debbuildopts '-v0' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)

# mod_dav_svn for Apache 2.4
apt-get source subversion/jessie
(
    cd subversion-1.8.10/
    sed -i -e 's/db5.3/db5.1/' debian/control
    dch --bpo "Use libdb5.1 instead of 5.3."
    patch -p1 <<'EOF'
diff -u subversion-1.8.10/debian/ruby-svn.install subversion-1.8.10/debian/ruby-svn.install
--- subversion-1.8.10/debian/ruby-svn.install
+++ subversion-1.8.10/debian/ruby-svn.install
@@ -1,3 +1,2 @@
 debian/tmp/usr/lib/*/libsvn_swig_ruby*.so.*
-debian/tmp/usr/lib/*/ruby
 debian/tmp/usr/lib/ruby
diff -u subversion-1.8.10/debian/rules subversion-1.8.10/debian/rules
--- subversion-1.8.10/debian/rules
+++ subversion-1.8.10/debian/rules
@@ -357,7 +357,7 @@
 ifdef DEB_OPT_WITH_RUBY
 	$(MAKE_B) install-swig-rb $(rb_defs) \
 		DESTDIR=$(CURDIR)/debian/tmp
-	find debian/tmp/$(libdir)/ruby \( -name \*.a -o -name \*.la \) -exec $(RM) {} +
+	find debian/tmp/usr/lib/ruby \( -name \*.a -o -name \*.la \) -exec $(RM) {} +
 endif
 
 	cd debian/tmp/$(libdir); for lib in ra fs auth swig; do \
EOF
    dch -a "Adapt ruby libdir as it's not multiarched in wheezy."
    dch -a "Note: compiled against Apache 2.4."
    sed -i '1 s/~bpo/~ff/' debian/changelog
    pdebuild --debbuildopts '-v1.6.17dfsg-4+deb7u6' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)

# mod_wsgi for Apache 2.4
apt-get source mod-wsgi/jessie
(
    cd mod-wsgi-4.2.7/
    dch --bpo "Note: compiled against Apache 2.4."
    sed -i '1 s/~bpo/~ff/' debian/changelog
    pdebuild --debbuildopts '-v3.3-4+deb7u1' \
        --use-pdebuild-internal --buildresult /usr/src/backports/wheezy/ \
        --pbuildersatisfydepends /usr/lib/pbuilder/pbuilder-satisfydepends-experimental \
        -- --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz \
           --bindmounts /usr/src/backports/wheezy/ \
           --hookdir /usr/src/backports/wheezy/
)


# Sadly there's no hook to do that *after* the packages are installed in --buildresult
(cd /usr/src/backports/wheezy && dpkg-scanpackages . /dev/null > Packages)

#If something goes wrong and you need to test manually:
#{{{
#sudo pbuilder --login --basetgz /var/cache/pbuilder/base-wheezy-bpo.tar.gz --bindmounts /usr/src/backports
#apt-get update
#echo 'APT::Get::AllowUnauthenticated "true";' > /etc/apt/apt.conf.d/99AllowUnauthenticated
#cd /usr/src/backports/sources/apache2-2.4.10/
#apt-get install pbuilder devscripts fakeroot
#/usr/lib/pbuilder/pbuilder-satisfydepends-experimental
#debuild ...
#}}}
