See ~/Desktop/README.html which contains some docs.

See also :
https://fusionforge.org/plugins/mediawiki/wiki/fusionforge/index.php/Virtual_machine_development_environment
for some more details.

The ~/fusionforge "directory" should hold the to-be-tested tested
contents of the repository.

It may conveniently be a soft link pointing to a trunk checkout or a
branch checkout if you intend to have several branches under test in
the same VM.

For instance, one may :

# cd ~
# bzr checkout svn://scm.fusionforge.org/svnroot/fusionforge/trunk fusionforge-trunk
# ln -s fusionforge-trunk fusionforge

-- Olivier Berger
