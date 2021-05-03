#!/bin/bash
# Quota Management post-install

source $(forge_get_config source_path)/post-install.d/common/service.inc

case "$1" in
	configure)
		mountpointtocheck="$(forge_get_config homedir_prefix) $(forge_get_config groupdir_prefix) $(forge_get_config chroot) $(forge_get_config data_path)"
		for each in $mountpointtocheck; do
			if [ -d $each ]; then
				mountpoint=$(df -P $each | tail -1 | awk '{print $6}')
				mountfilesystem=$(mount | grep "$mountpoint " | awk '{print $5}')
				case $mountfilesystem in
					'xfs')
						mountoptions=$(mount | grep "$mountpoint " | grep "uquota" | grep "gquota")
						if [ -z "$mountoptions" ]; then
							echo "Enabling uquota & gquota on $mountpoint"
							#requires boot changes!!!
						else
							echo "uquota & pquota already enabled on $mountpoint"
						fi
						;;
					'ext4'|'ext3')
						mountoptions=$(mount | grep "$mountpoint " | grep "usrquota" | grep "grpquota")
						if [ -z "$mountoptions" ]; then
							echo "Enabling usrquota & grpquota on $mountpoint"
							#add option in fstab
							mount -o remount,usrquota,grpquota $mountpoint
						else
							echo "usrquota & grpquota already enabled on $mountpoint"
						fi
						;;
				esac

			fi
			#quotaon -guvp -a
		done
		
		;;
	remove)
		echo "Checking mount option: usrquota & grpquota"
		echo "Disabling usrquota & grpquota"
		#quotaoff -guvp -a
		;;
	*)
		echo "Usage: $0 {configure|remove}"
		exit 1
		;;
esac
