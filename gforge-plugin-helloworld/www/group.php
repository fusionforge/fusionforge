<?php
/*
 * Hello world project plugin
 *
 * Francisco Gimeno <kikov@fco-gimeno.com>
 */

require_once('pre.php');

function hellopluginproject_header($params) {                                                                                                                                         
        global $DOCUMENT_ROOT,$HTML,$group_id,$Language;                                                                            
        $params['toptab']='helloworld'; 
        $params['group']=$group_id;
        /*                                                                                                                                                              
            Show horizontal links                                                                                                                                   
        */                                                                                                                                                              
        site_project_header($params);                                                                                                                           
                                                                           
} 

	if (!$group_id) {
    	    exit_no_group();
	} 

	hellopluginproject_header(array('title'=>'Hello World Project Plugin!','pagename'=>'helloworld','sectionvals'=>array(group_getname($group_id))));    
	$group =&group_get_object($group_id); 
	print $HTML->boxTop("The ".$group->getPublicName(). " project says Hello!");
	print $HTML->boxBottom();
	print $HTML->footer(array());


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:



?>
