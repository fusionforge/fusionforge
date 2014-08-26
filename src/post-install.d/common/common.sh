#!/bin/bash
# Call all common post-install scripts in order

source_path=$(forge_get_config source_path)

$source_path/post-install.d/common/ini.sh
$source_path/post-install.d/common/user.sh
