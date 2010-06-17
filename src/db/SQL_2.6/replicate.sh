#!/bin/sh
#
# SourceForge: Breaking Down the Barriers to Open Source Development
# Copyright 1999-2001 (c) VA Linux Systems
# http://sourceforge.net 
#

MasterInit alexandria
SlaveInit --host=sf-db2 stats

MasterAddTable alexandria stats_site oid
MasterAddTable alexandria stats_project oid
MasterAddTable alexandria stats_project_developers oid
MasterAddTable alexandria stats_project_metric oid
MasterAddTable alexandria frs_dlstats_file_agg oid
MasterAddTable alexandria stats_subd_pages oid
MasterAddTable alexandria stats_agg_logo_by_group oid
MasterAddTable alexandria stats_cvs_group oid
MasterAddTable alexandria stats_agg_site_by_group oid
MasterAddTable alexandria stats_site_pages_by_day oid
MasterAddTable alexandria frs_package package_id
MasterAddTable alexandria frs_release release_id
MasterAddTable alexandria frs_file file_id
MasterAddTable alexandria frs_processor processor_id
MasterAddTable alexandria frs_filetype type_id
MasterAddTable alexandria project_weekly_metric group_id
MasterAddTable alexandria trove_cat trove_cat_id
MasterAddTable alexandria trove_group_link trove_group_id
MasterAddTable alexandria groups group_id
MasterAddTable alexandria users user_id
MasterAddTable alexandria foundry_projects id
SlaveAddTable --host=sf-db2 stats foundry_projects id
SlaveAddTable --host=sf-db2 stats stats_site oid
SlaveAddTable --host=sf-db2 stats stats_project oid
SlaveAddTable --host=sf-db2 stats stats_project_developers oid
SlaveAddTable --host=sf-db2 stats stats_project_metric oid
SlaveAddTable --host=sf-db2 stats frs_dlstats_file_agg oid
SlaveAddTable --host=sf-db2 stats stats_subd_pages oid
SlaveAddTable --host=sf-db2 stats stats_agg_logo_by_group oid
SlaveAddTable --host=sf-db2 stats stats_cvs_group oid
SlaveAddTable --host=sf-db2 stats stats_agg_site_by_group oid
SlaveAddTable --host=sf-db2 stats stats_site_pages_by_day oid
SlaveAddTable --host=sf-db2 stats frs_package package_id
SlaveAddTable --host=sf-db2 stats frs_release release_id
SlaveAddTable --host=sf-db2 stats frs_file file_id
SlaveAddTable --host=sf-db2 stats frs_processor processor_id
SlaveAddTable --host=sf-db2 stats frs_filetype type_id
SlaveAddTable --host=sf-db2 stats project_weekly_metric group_id
SlaveAddTable --host=sf-db2 stats trove_cat trove_cat_id
SlaveAddTable --host=sf-db2 stats trove_group_link trove_group_id
SlaveAddTable --host=sf-db2 stats groups group_id
SlaveAddTable --host=sf-db2 stats users user_id
