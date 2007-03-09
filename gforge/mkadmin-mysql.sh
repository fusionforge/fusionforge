USER_NAME=$1
shift
mysql $* <<EOD
USE gforge;
INSERT INTO user_group
	(user_id,group_id,admin_flags)
VALUES
	((SELECT user_id FROM users WHERE user_name='$USER_NAME'),1,'A');
EOD
