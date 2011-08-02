<?php
/**
 * getSCMData_soap
 */
class getSCMData_soap {
	/**
	 * @access public
	 * @var string
	 */
	public $group_id;
}

/**
 * getSCMDataResponse_soap
 */
class getSCMDataResponse_soap {
	/**
	 * @access public
	 * @var scmData_soap
	 */
	public $scm_data;
}

/**
 * scmData_soap
 */
class scmData_soap {
	/**
	 * @access public
	 * @var integer
	 */
	public $allow_anonymous;
	/**
	 * @access public
	 * @var string
	 */
	public $box;
	/**
	 * @access public
	 * @var string
	 */
	public $connection_string;
	/**
	 * @access public
	 * @var string
	 */
	public $module;
	/**
	 * @access public
	 * @var string
	 */
	public $root;
	/**
	 * @access public
	 * @var string
	 */
	public $type;
	/**
	 * @access public
	 * @var integer
	 */
	public $public;
}

/**
 * getVersion_soap
 */
class getVersion_soap {
}

/**
 * getVersionResponse_soap
 */
class getVersionResponse_soap {
	/**
	 * @access public
	 * @var string
	 */
	public $version;
}

/**
 * getGroups_soap
 */
class getGroups_soap {
	/**
	 * @access public
	 * @var string[]
	 */
	public $group_id;
}

/**
 * getGroupsResponse_soap
 */
class getGroupsResponse_soap {
	/**
	 * @access public
	 * @var group_soap[]
	 */
	public $group;
}

/**
 * group_soap
 */
class group_soap {
	/**
	 * @access public
	 * @var integer
	 */
	public $group_id;
	/**
	 * @access public
	 * @var string
	 */
	public $group_name;
	/**
	 * @access public
	 * @var string
	 */
	public $homepage;
	/**
	 * @access public
	 * @var boolean
	 */
	public $is_public;
	/**
	 * @access public
	 * @var dateTime
	 */
	public $register_time;
	/**
	 * @access public
	 * @var string
	 */
	public $scm_box;
	/**
	 * @access public
	 * @var string
	 */
	public $short_description;
	/**
	 * @access public
	 * @var string
	 */
	public $status;
	/**
	 * @access public
	 * @var string
	 */
	public $unix_group_name;
}

/**
 * getPublicProjectNames_soap
 */
class getPublicProjectNames_soap {
}

/**
 * getPublicProjectNamesResponse_soap
 */
class getPublicProjectNamesResponse_soap {
	/**
	 * @access public
	 * @var string[]
	 */
	public $project_name;
}

/**
 * userGetGroups_soap
 */
class userGetGroups_soap {
	/**
	 * @access public
	 * @var string
	 */
	public $user_id;
}

/**
 * userGetGroupsResponse_soap
 */
class userGetGroupsResponse_soap {
	/**
	 * @access public
	 * @var group_soap
	 */
	public $group;
}

/**
 * getUsers_soap
 */
class getUsers_soap {
	/**
	 * @access public
	 * @var string[]
	 */
	public $user_id;
}

/**
 * getUsersResponse_soap
 */
class getUsersResponse_soap {
	/**
	 * @access public
	 * @var user[]
	 */
	public $user;
}

/**
 * user_soap
 */
class user_soap {
	/**
	 * @access public
	 * @var dateTime
	 */
	public $add_date;
	/**
	 * @access public
	 * @var string
	 */
	public $address;
	/**
	 * @access public
	 * @var string
	 */
	public $address2;
	/**
	 * @access public
	 * @var string
	 */
	public $country_code;
	/**
	 * @access public
	 * @var string
	 */
	public $fax;
	/**
	 * @access public
	 * @var string
	 */
	public $firstname;
	/**
	 * @access public
	 * @var integer
	 */
	public $language_id;
	/**
	 * @access public
	 * @var string
	 */
	public $lastname;
	/**
	 * @access public
	 * @var string
	 */
	public $phone;
	/**
	 * @access public
	 * @var string
	 */
	public $status;
	/**
	 * @access public
	 * @var string
	 */
	public $timezone;
	/**
	 * @access public
	 * @var string
	 */
	public $title;
	/**
	 * @access public
	 * @var integer
	 */
	public $user_id;
	/**
	 * @access public
	 * @var string
	 */
	public $user_name;
}

/**
 * getGroupsByName_soap
 */
class getGroupsByName_soap {
	/**
	 * @access public
	 * @var string[]
	 */
	public $group_name;
}

/**
 * getGroupsByNameResponse_soap
 */
class getGroupsByNameResponse_soap {
	/**
	 * @access public
	 * @var group_soap[]
	 */
	public $group;
}

/**
 * getUsersByName_soap
 */
class getUsersByName_soap {
	/**
	 * @access public
	 * @var string[]
	 */
	public $user_name;
}

/**
 * getUsersByNameResponse_soap
 */
class getUsersByNameResponse_soap {
	/**
	 * @access public
	 * @var user_soap[]
	 */
	public $user;
}
?>
