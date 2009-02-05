<?php 
require_once(dirname(__FILE__) . '/ServiceHeaders.php');
require_once('plugins/report/include/facade/CheckstyleReportFacade.php');

$server = new soap_server;
$server->configureWSDL("CheckStyleService", $gNamespace, $gNamespace . '/CheckStyleService.php');

$server->register('addCheckstyleReport',
                  array('userName'      => 'xsd:string', 
                        'userPw'        => 'xsd:string',
                        'unixGroupName' => 'xsd:string',
                        'mavenArtefactId' => 'xsd:string',
                        'mavenGroupId' => 'xsd:string',
                        'mavenVersion' => 'xsd:string',
                        'checkstyleDTOCollection' => 'tns:CheckstyleDTOCollection'),
                  array('return' => 'xsd:boolean'),
                  $gNamespace . '/CheckStyleService.php',                     // namespace
                  $gNamespace . '/CheckStyleService.php/addCheckstyleReport', // SOAPAction
                  'rpc',                                                  // style
                  'encoded'                                               // use   
);

$server->register('deleteCheckstyleReportByMavensIds',
                  array('userName'      => 'xsd:string', 
                        'userPw'        => 'xsd:string',
                        'unixGroupName' => 'xsd:string',
                        'mavenArtefactId' => 'xsd:string',
                        'mavenGroupId' => 'xsd:string',
                        'mavenVersion' => 'xsd:string'),
                  array('return' => 'xsd:boolean'),
                  $gNamespace . '/CheckStyleService.php',                   // namespace
                  $gNamespace . '/CheckStyleService.php/deleteCheckstyleReportByMavensIds', // SOAPAction
                  'rpc',                                                // style
                  'encoded'                                             // use                  
);


$server->wsdl->addComplexType(
    'CheckstyleDTO',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'fileName'     => array('name' => 'fileName',     'type' => 'xsd:string'),        
        'nbLine'       => array('name' => 'nbLine',       'type' => 'xsd:int'   ),
        'nbColumn'     => array('name' => 'nbColumn',     'type' => 'xsd:int'   ),
        'severity'     => array('name' => 'severity',     'type' => 'xsd:string'),
        'message'      => array('name' => 'message',      'type' => 'xsd:string'),
        'moduleId'     => array('name' => 'moduleId',     'type' => 'xsd:string'),
        'source'       => array('name' => 'source',       'type' => 'xsd:string')        
    )
);

$server->wsdl->addComplexType('CheckstyleDTOCollection',
                'complexType',
                'array',
                '',
                'SOAP-ENC:Array',
                array(),
                array(
                    array('ref'=>'SOAP-ENC:arrayType',
                    'wsdl:arrayType'=>'tns:CheckstyleDTO[]')
                ),
                'tns:CheckstyleDTO'
);

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);


/**
 * Ajoute un rapport Checkstyle en vérifiant que l'utilisateur a le droit de le faire.
 * 
 * @param userName le login de l'utilisateur.
 * @param userPw le password de l'utilisateur.
 * @param unixGroupName le nom unix du projet.
 * @param checkstyleDTOCollection le tableau des informations sur le rapport Checkstyle.
 * @return vrai si l'ajout a réussi.
 */
function addCheckstyleReport($userName, $userPw, $unixGroupName, $mavenArtefactId, $mavenGroupId, $mavenVersion, $checkstyleDTOCollection){
	
    $groupId = GroupFacade::getGroupId($userName, $userPw, $unixGroupName);
    
    if($groupId === false){
        return false;
    }
    
    TransactionFacade::begin();
    
    foreach($checkstyleDTOCollection as $checkstyleDTO){
    
        $dto = new CheckstyleDTO();
        $dto->setFileName($checkstyleDTO["fileName"]);
        $dto->setNbLine($checkstyleDTO["nbLine"]);
        $dto->setNbColumn($checkstyleDTO["nbColumn"]);
        $dto->setSeverity($checkstyleDTO["severity"]);
        $dto->setMessage($checkstyleDTO["message"]);
        $dto->setModuleId($checkstyleDTO["moduleId"]);
        $dto->setSource($checkstyleDTO["source"]);
        $dto->setMavenArtefactId($mavenArtefactId);
        $dto->setMavenGroupId($mavenGroupId);
        $dto->setMavenVersion($mavenVersion);
        $dto->setGroupId($groupId);
                             
        if(CheckstyleReportFacade::addCheckstyleReport($dto) === false){
            TransactionFacade::rollback();
            return false;
        }
    
    }
    
    TransactionFacade::commit();
    
    return true;
}

function deleteCheckstyleReportByMavensIds($userName, $userPw, $unixGroupName, $mavenArtefactId, $mavenGroupId, $mavenVersion){
	
	$groupId = GroupFacade::getGroupId($userName, $userPw, $unixGroupName);
    
    if($groupId === false){
        return false;
    }
    
    TransactionFacade::begin();
    
    if(CheckstyleReportFacade::deleteCheckstyleReportByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion) === false){
        TransactionFacade::rollback();
        return false;
    }
    
    TransactionFacade::commit();
    
    return true;
}
?>