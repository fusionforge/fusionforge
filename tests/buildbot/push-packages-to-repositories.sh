#!/bin/bash

set -ex

os=$1
method=$2
branch=$(echo $GIT_BRANCH | sed -e s,origin/,, -e s,remotes_,, -e s,remotes/,, -e s,/,_,g)

case $os in
    debian7)
	dist=wheezy
	;;
    debian8)
	dist=jessie
	;;
    debian9)
	dist=stretch
	;;
    debian10)
	dist=buster
	;;
    centos*)
	dist=$os
	;;
    *)
	echo "Unknown OS"
	exit 1
	;;
esac

case $method in
    src)
	echo "No packages to handle"
	exit 0
	;;
    deb)
	cd $WORKSPACE/packages/
	sed -i -e "s/^Distribution:.*/Distribution: $dist-$branch/" fusionforge*changes
	dput buildbot fusionforge*changes
	rsync -av --delete-after /var/lib/jenkins/deb/ ffbuildbot@fusionforge.org:/home/groups/fusionforge/htdocs/deb/
	;;
    rpm)
	rpmsign --addsign $WORKSPACE/packages/noarch/*.rpm
	gpg --detach-sign --armor $WORKSPACE/packages/repodata/repomd.xml
	rsync -av --delete $WORKSPACE/packages/ ffbuildbot@fusionforge.org:/home/groups/fusionforge/htdocs/rpm/$dist-$branch/
	;;
    *)
	echo "Unknown install method"
	exit 1
	;;
esac
