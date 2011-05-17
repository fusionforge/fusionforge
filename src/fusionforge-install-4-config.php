#!/usr/bin/php
<?php
/**
 * FusionForge Installation final step
 *
 * Copyright 2010, Roland Mas
 *
 * This file is part of FusionForge
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

# Set up config

system('PATH=/opt/gforge/utils/:$PATH migrate-to-ini-files.sh') ;
system('PATH=/opt/gforge/utils/:$PATH manage-apache-config.sh install') ;
system('for i in /etc/gforge/httpd.conf.d/*.generated ; do mv $i ${i%%.generated} ; done') ;
