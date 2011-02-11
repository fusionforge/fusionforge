The initial setup a user should do is :

 # cd /root
 # sh .../scripts/configure-scripts.sh BRANCH
where BRANCH is either 'Branch_5_1' or 'trunk' (without quotes)

This will setup the ~/fusionforge "directory" (actually a link to the
wanted branch's checked-out copy), that should hold the to-be-tested
tested contents of the repository.

The 'fusionforge' symlink can be changed to point to another branch
checkout if you intend to have several branches under test in the same
VM.

The 'scripts' "directory" is also a symlink to the tools/VM-scripts/
dir inside the corresponding checked-out branch.


For using the other scripts see ~/Desktop/README.html which contains some docs.

See also :
https://fusionforge.org/plugins/mediawiki/wiki/fusionforge/index.php/Virtual_machine_development_environment
for some more details.

-- Olivier Berger
