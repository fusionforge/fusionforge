#!/bin/bash

set -ex

os=$1
method=$2
branch=$(echo $GIT_BRANCH | sed -e s,origin/,, -e s,remotes_,, -e s,remotes/,, -e s,/,_,g)

case $os in
    debian9)
	dist=stretch
	;;
    debian10)
	dist=buster
	;;
    debian11)
	dist=bullseye
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
	debsign -m"FusionForge buildbot" *changes
	dput buildbot fusionforge*changes
	rsync -e "ssh -o StrictHostKeyChecking=no" -av --delete-after --exclude=/mini-dinstall --exclude=/*.db --delete-excluded /var/lib/jenkins/deb/ ffbuildbot@fusionforge.org:/home/groups/fusionforge/htdocs/deb/
	;;
    rpm)
	rpmsign --addsign $WORKSPACE/packages/*/*.rpm
	(cd $WORKSPACE/packages/ && createrepo .)
	gpg --detach-sign --armor $WORKSPACE/packages/repodata/repomd.xml
	rsync -e "ssh -o StrictHostKeyChecking=no" -av --delete $WORKSPACE/packages/ /var/lib/jenkins/rpm/$dist-$branch/
	rsync -e "ssh -o StrictHostKeyChecking=no" -av --delete-after  /var/lib/jenkins/rpm/ ffbuildbot@fusionforge.org:/home/groups/fusionforge/htdocs/rpm/
	;;
    *)
	echo "Unknown install method"
	exit 1
	;;
esac
