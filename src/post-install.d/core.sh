#!/bin/bash -x
# Call the post-install scripts for common+db+web

source_path=$(forge_get_config source_path)

$source_path/post-install.d/common/common.sh
$source_path/post-install.d/db/db.sh
$source_path/post-install.d/web/configure.sh
