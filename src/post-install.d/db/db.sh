#!/bin/bash -x
# Call all DB post-install scripts in order

source_path=$(forge_get_config source_path)

$source_path/post-install.d/db/configure.sh
$source_path/post-install.d/db/populate.sh
$source_path/bin/upgrade-db.php
