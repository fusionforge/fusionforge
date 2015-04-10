#!/bin/bash
# Run all post-install scripts for *installed* components
#
# Copyright (C) 2015  Inria (Sylvain Beucler)
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

#set -x
set -e

source_path=$(forge_get_config source_path)
plugins_path=$(forge_get_config plugins_path)

# Base
for i in common db web scm shell lists mta-postfix mta-exim4; do
    script="$source_path/post-install.d/$i/$i.sh"
    [ -x $script ] && $script configure
done

# Plugins
if [ -d "$plugins_path" ]; then
    for i in $(cd $plugins_path && find * -maxdepth 0 -type d); do
	$source_path/post-install.d/common/plugin.sh $i configure
    done
fi
