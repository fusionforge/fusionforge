#!/bin/bash -e
# Quota Management post-install

source $(forge_get_config source_path)/post-install.d/common/service.inc


case "$1" in
	configure)
		
		mountpointtocheck="$(forge_get_config homedir_prefix) $(forge_get_config groupdir_prefix) $(forge_get_config chroot) $(forge_get_config data_path)"
		for each in $mountpointtocheck; do
			echo "Checking mount option: usrquota & grpquota on $each"
			if [ -d $each ]; then
				mountpoint=$(df -P $each | tail -1 | awk '{print $6}')
				mountoptions=$(mount | grep "$mountpoint " | grep "usrquota" | grep "grpquota")
				if [ -z $mountoptions ];then
					echo "Enabling usrquota & grpquota on $mountpoint"
					#add option in fstab
					mount -o remount,usrquota,grpquota $mountpoint
				else
					echo "usrquota & grpquota already enabled on $mountpoint"
				fi
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
