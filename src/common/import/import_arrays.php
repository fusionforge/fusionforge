<?php
$equivs_text_value['projectadmin']['None']='0';
$equivs_text_value['projectadmin']['Admin']='A';
$equivs_text_value['frs']['Read']='0';
$equivs_text_value['frs']['Write']='1';
$equivs_text_value['scm']['No Access']='-1';
$equivs_text_value['scm']['Read']='0';
$equivs_text_value['scm']['Write']='1';
$equivs_text_value['docman']['Read/Post']='0';
$equivs_text_value['docman']['Admin']='1';
$equivs_text_value['forumadmin']['None']='0';
$equivs_text_value['forumadmin']['Admin']='2';
$equivs_text_value['forum']['No Access']='-1';
$equivs_text_value['forum']['Read']='0';
$equivs_text_value['forum']['Post']='1';
$equivs_text_value['forum']['Admin']='2';
$equivs_text_value['trackeradmin']['None']='0';
$equivs_text_value['trackeradmin']['Admin']='2';
$equivs_text_value['tracker']['No Access']='-1';
$equivs_text_value['tracker']['Read']='0';
$equivs_text_value['tracker']['Tech']='1';
$equivs_text_value['tracker']['Tech & Admin']='2';
$equivs_text_value['tracker']['Admin Only']='3';
$equivs_text_value['pmadmin']['None']='0';
$equivs_text_value['pmadmin']['Admin']='2';
$equivs_text_value['pm']['No Access']='-1';
$equivs_text_value['pm']['Read']='0';
$equivs_text_value['pm']['Tech']='1';
$equivs_text_value['pm']['Tech & Admin']='2';
$equivs_text_value['pm']['Admin Only']='3';
$equivs_text_value['webcal']['No access']='0';
$equivs_text_value['webcal']['Modify']='1';
$equivs_text_value['webcal']['See']='2';

$observer_equivs_text_value['projectpublic']['Private']=0;
$observer_equivs_text_value['projectpublic']['Public']=1;
$observer_equivs_text_value['scmpublic']['Private']=0;
$observer_equivs_text_value['scmpublic']['Public (PServer)']=1;
$observer_equivs_text_value['forumpublic']['Private']=0;
$observer_equivs_text_value['forumpublic']['Public']=1;
$observer_equivs_text_value['forumanon']['No Anonymous Posts']=0;
$observer_equivs_text_value['forumanon']['Allow Anonymous Posts']=1;
$observer_equivs_text_value['trackerpublic']['Private']=0;
$observer_equivs_text_value['trackerpublic']['Public']=1;
$observer_equivs_text_value['trackeranon']['No Anonymous Posts']=0;
$observer_equivs_text_value['trackeranon']['Allow Anonymous Posts']=1;
$observer_equivs_text_value['pmpublic']['Private']=0;
$observer_equivs_text_value['pmpublic']['Public']=1;
$observer_equivs_text_value['frspackage']['Private']=0;
$observer_equivs_text_value['frspackage']['Public']=1;

$equivs_name_value['Documentation Manager']='docman';
$equivs_name_value['File Release System']='frs';
$equivs_name_value['Forum Admin']='forumadmin';
$equivs_name_value['Forum:']='forum';
$equivs_name_value['Project Admin']='projectadmin';
$equivs_name_value['Tasks Admin']='pmadmin';
$equivs_name_value['Tasks:']='pm';
$equivs_name_value['Tracker Admin']='trackeradmin';
$equivs_name_value['Tracker:']='tracker';
$equivs_name_value['Webcal']='webcal';
$equivs_name_value['SCM']='scm';

$observer_equivs_name_value['Project']='projectpublic';
$observer_equivs_name_value['SCM']='scmpublic';
$observer_equivs_name_value['Forum:']='forumpublic';
$observer_equivs_name_value['Forum:AnonPost:']='forumanon';
$observer_equivs_name_value['Tracker:']='trackerpublic';
$observer_equivs_name_value['Project']='projectpublic';
$observer_equivs_name_value['Tracker:AnonPost:']='trackeranon';
$observer_equivs_name_value['Tasks:']='pmpublic';
$observer_equivs_name_value['Files']='frspackage';

//Default values for trackers


$is_public = 1;
$allow_anon = 0;
$email_all = '';
$email_address = '';
$due_period = 30;
$use_resolution = 0;
$submit_instructions = 0;
$use_resolution = 0;
$base_tracker_association = array( 'bugs' => 1, 'support' => 2, 'patches' => 3, 'features' => 4 );

$notExtraFields = array('assigned_to', 'attachments', 'class', 'comments', 'date', 'history', 'priority', 'status_id', 'submitter', 'summary', 'closed_at', 'description', 'type', 'type_of_search', 'id');//last 3 should not be there at all.$defaultExtraFieldsSettings = array(0,0,0);
$defaultTextFieldsSettings = array(40,100,0);
