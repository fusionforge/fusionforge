#!/bin/bash
# Create to-be-specified 'fusionforge' user

# TODO: specify the role of this user and its permissions
# Currently used in: plugin-scmbzr, plugin-moinmoin, ???

system_user=$(forge_get_config system_user)
data_path=$(forge_get_config data_path)
if ! getent passwd $system_user >/dev/null; then useradd $system_user -s /bin/false -d $data_path; fi
