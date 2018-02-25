#! /bin/sh

set -e

repos_path=$(forge_get_config repos_path scmgit)
cd "$repos_path"
find -path \*.git/config | while read i ; do
    p="${i%/config}"
    r=$(realpath "$p")
    GIT_DIR="$r" git config core.logAllRefUpdates true
done
