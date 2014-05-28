#!/bin/bash -e

# Creates the .orig tarball corresponding to the upstream archive,
# base of the Debian packaging.

if [ -e src/debian/changelog ] ; then        # We're in the parent dir
    cd src
elif [ -e debian/changelog ] ; then             # probably in src/ (or a renamed src/)
    cd . # do nothing, but shell syntax requires an instruction in a then-block
elif [ -e ../src/debian/changelog ] ; then   # in tools/ or tests/ or something
    cd ../src
elif [ -e ../debian/changelog ] ; then       # In a subdir of src/
    cd ..
else
    echo "Couldn't find changelog..."
    exit 1
fi

if [ "$1" != "" ] ; then
    tag=$1
else
    tag=HEAD
fi

# package version including revision
f=$(dpkg-parsechangelog | awk '/^Version:/ { print $2 }')
# upstream version
u=${f%-*}

if [ -e ../fusionforge_$u.orig.tar.gz ] ; then
    echo "../fusionforge_$u.orig.tar.gz already exists"
    exit 1
fi

# Make the tarball using git-archive(1)
# Remove debian/, as well as plugins/fckeditor and plugins/wiki:
# we don't package them and they include sourceless files (#736107)
# TODO: don't bother with debian/ when moving to format "3.0 (quilt)"
git archive --format=tar --prefix=fusionforge-$u/ $tag | tar x
(
  cd fusionforge-$u/
  rm -rf debian/ plugins/{fckeditor,wiki}/ etc/config.ini.d/{fckeditor,wiki}.ini etc/httpd.conf.d/plugin-{fckeditor,wiki}.inc
)
tar czf ../fusionforge_$u.orig.tar.gz --owner 0 --group 0 fusionforge-$u/
rm -rf fusionforge-$u/
