#!/bin/sh -e

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

git archive --format=tar --prefix=fusionforge-$u/ $tag \
    | tar -f - --delete fusionforge-$u/debian \
    | gzip -c \
    > ../fusionforge_$u.orig.tar.gz
