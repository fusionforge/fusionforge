#! /bin/sh

# Author : Olivier BERGER <olivier.berger@it-sudparis.eu>

# This script will checkout the needed branch and will setup the
# scripts symlink to give the correct up-to-date scripts versions to
# the user

if [ $# -ne 1 ]; then
    echo "Please provide branch name to work on (Branch_5_1|trunk)"
    exit 1
fi

cd $HOME

if [ -f ./fusionforge ]; then
    if [ ! -L ./fusionforge ]; then
	echo "You have an existing ./fusionforge file or directory. Stopping."
	exit 1
    fi
fi

BRANCH="$1"

if [ "$BRANCH" = "trunk" -o "$BRANCH" = "Branch_5_1" ]; then
    if [ -d "./fusionforge-$BRANCH" ]; then
	echo "Assuming './fusionforge-$BRANCH/' already contains a bzr checkout of the $BRANCH. Please check following output of 'bzr info' :"
	(cd "./fusionforge-$BRANCH/" && bzr info)
    else
	if [ "$BRANCH" = "trunk" ]; then
	    echo "no 'fusionforge-trunk/' dir found : checking out from SVN's trunk with 'bzr checkout svn://scm.fusionforge.org/svnroot/fusionforge/trunk' :"
	    bzr checkout svn://scm.fusionforge.org/svnroot/fusionforge/trunk fusionforge-trunk
	else
	    echo "no 'fusionforge-$BRANCH/' dir found : checking out from SVN's $BRANCH with 'bzr checkout svn://scm.fusionforge.org/svnroot/fusionforge/branches/$BRANCH' :"
	    bzr checkout "svn://scm.fusionforge.org/svnroot/fusionforge/branches/$BRANCH" "fusionforge-$BRANCH"
	fi
    fi
else
    echo "The supplied branch : $BRANCH wasn't recognized. Maybe the script is now outdated"
    exit 1
fi

if [ -L ./fusionforge ]; then
    oldlink=$(ls -ld ./fusionforge)
    echo "Removing old ./fusionforge link ($oldlink)"
    rm ./fusionforge
fi

echo "Creating a link from './fusionforge' to 'fusionforge-$BRANCH'"
ln -s "fusionforge-$BRANCH" fusionforge

if [ -d scripts ]; then
    echo "Saving old 'scripts/' dir in 'scripts.old/'."
    mv scripts scripts.old
fi

if [ -L ./scripts ]; then
    oldlink=$(ls -ld ./scripts)
    echo "Removing old ./scripts link ($oldlink)"
    rm ./scripts
fi

echo "Creating a link from 'fusionforge-$BRANCH/tools/VM-scripts/' to './scripts'."
ln -s "fusionforge-$BRANCH/tools/VM-scripts/" scripts

