#!/usr/bin/env python
#
#  hot-backup.py: perform a "hot" backup of a Berkeley DB repository.
#                 (and clean old logfiles after backup completes.)
#
#  Subversion is a tool for revision control. 
#  See http://subversion.tigris.org for more information.
#    
# ====================================================================
# Copyright (c) 2000-2004 CollabNet.  All rights reserved.
#
# This software is licensed as described in the file COPYING, which
# you should have received as part of this distribution.  The terms
# are also available at http://subversion.tigris.org/license-1.html.
# If newer versions of this license are posted there, you may use a
# newer version instead, at your option.
#
# This software consists of voluntary contributions made by many
# individuals.  For exact contribution history, see the revision
# history and logs, available at http://subversion.tigris.org/.
# ====================================================================

######################################################################

import sys, os, shutil, string, re

######################################################################
# Global Settings

svnpath = os.environ["SVN_PATH"]

# Path to svnlook utility
svnlook = svnpath + "/svnlook"

# Path to svnadmin utility
svnadmin = svnpath + "/svnadmin"

# Number of backups to keep around (0 for "keep them all")
num_backups = 0

######################################################################
# Command line arguments


if len(sys.argv) != 3:
  print "Usage: ", os.path.basename(sys.argv[0]), " <repos_path> <backup_path>"
  sys.exit(1)

# Path to repository
repo_dir = sys.argv[1]
repo = os.path.basename(os.path.abspath(repo_dir))

# Where to store the repository backup.  The backup will be placed in
# a *subdirectory* of this location, named after the youngest
# revision.
backup_dir = sys.argv[2]

######################################################################
# Helper functions

def comparator(a, b):
  # We pass in filenames so there is never a case where they are equal.
  regexp = re.compile("-(?P<revision>[0-9]+)(-(?P<increment>[0-9]+))?$")
  matcha = regexp.search(a)
  matchb = regexp.search(b)
  reva = int(matcha.groupdict()['revision'])
  revb = int(matchb.groupdict()['revision'])
  if (reva < revb):
    return -1
  elif (reva > revb):
    return 1
  else:
    inca = matcha.groupdict()['increment']
    incb = matchb.groupdict()['increment']
    if not inca:
      return -1
    elif not incb:
      return 1;
    elif (int(inca) < int(incb)):
      return -1
    else:
      return 1

######################################################################
# Main

print "Beginning hot backup of '"+ repo_dir + "'."


### Step 1: get the youngest revision.

infile, outfile, errfile = os.popen3(svnlook + " youngest " + repo_dir)
stdout_lines = outfile.readlines()
stderr_lines = errfile.readlines()
outfile.close()
infile.close()
errfile.close()

try:
	youngest = string.strip(stdout_lines[0])
	print "Youngest revision is", youngest
except IndexError:
	print "Error executing svnlook, maybe wrong SVN_PATH?"
	sys.exit(1)


### Step 2: Find next available backup path

backup_subdir = os.path.join(backup_dir, repo + "-" + youngest)

# If there is already a backup of this revision, then append the
# next highest increment to the path. We still need to do a backup
# because the repository might have changed despite no new revision
# having been created. We find the highest increment and add one
# rather than start from 1 and increment because the starting
# increments may have already been removed due to num_backups.

regexp = re.compile("^" + repo + "-" + youngest + "(-(?P<increment>[0-9]+))?$")
directory_list = os.listdir(backup_dir)
young_list = filter(lambda x: regexp.search(x), directory_list)
if young_list:
  young_list.sort(comparator)
  increment = regexp.search(young_list.pop()).groupdict()['increment']
  if increment:
    backup_subdir = os.path.join(backup_dir, repo + "-" + youngest + "-"
                                 + str(int(increment) + 1))
  else:
    backup_subdir = os.path.join(backup_dir, repo + "-" + youngest + "-1")

### Step 3: Ask subversion to make a hot copy of a repository.
###         copied last.

print "Backing up repository to '" + backup_subdir + "'..."
err_code = os.spawnl(os.P_WAIT, svnadmin, "svnadmin", "hotcopy", repo_dir, 
                     backup_subdir, "--clean-logs")
if(err_code != 0):
  print "Unable to backup the repository."
  sys.exit(err_code)
else:
  print "Done."


### Step 4: finally, remove all repository backups other than the last
###         NUM_BACKUPS.

if num_backups > 0:
  regexp = re.compile("^" + repo + "-[0-9]+(-[0-9]+)?$")
  directory_list = os.listdir(backup_dir)
  old_list = filter(lambda x: regexp.search(x), directory_list)
  old_list.sort(comparator)
  del old_list[max(0,len(old_list)-num_backups):]
  for item in old_list:
    old_backup_subdir = os.path.join(backup_dir, item)
    print "Removing old backup: " + old_backup_subdir
    shutil.rmtree(old_backup_subdir)
