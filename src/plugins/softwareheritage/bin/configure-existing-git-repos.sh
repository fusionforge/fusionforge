#!/bin/bash -e


case "$1" in
    configure)
	repos_path=$(forge_get_config repos_path scmgit)
	find $repos_path -path \*.git/config | while read i ; do
	    p="${i%/config}"
	    r=$(realpath "$p")
	    GIT_DIR="$r" git config core.logAllRefUpdates true
	done
        ;;
    remove)
        ;;
    *)
        echo "Usage: $0 {configure|remove}"
        exit 1
esac
