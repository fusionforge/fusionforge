[ ! -d gforge-theme-starterpack ] && echo "gforge-theme-starterpack not found" && exit 1
rm -rf gforge-theme-starterpack/www
find . -type d -maxdepth 1 -name "gforge-theme-*" | grep -v starterpack | while read themedir
do
	echo "Copying $themedir/www -> gforge-theme-starterpack"
	cp -r $themedir/www gforge-theme-starterpack
done
echo "Removing CVS dirs"
find gforge-theme-starterpack/www -type d -name CVS | xargs rm -rf

echo "Building register and unregister scripts"
grep bin/unregister gforge-theme-*/debian/prerm | grep -v gforge-theme-starterpack/debian/prerm | cut -d: -f2 > gforge-theme-starterpack/unregister-theme-starterpack
grep bin/register gforge-theme-*/debian/postinst | grep -v gforge-theme-starterpack/debian/postinst | cut -d: -f2 > gforge-theme-starterpack/register-theme-starterpack
chmod +x gforge-theme-starterpack/unregister-theme-starterpack gforge-theme-starterpack/register-theme-starterpack

ls -d gforge-theme-starterpack/www/themes/* | sed 's:.*themes/::' | while read theme
do 
	echo $theme
done

