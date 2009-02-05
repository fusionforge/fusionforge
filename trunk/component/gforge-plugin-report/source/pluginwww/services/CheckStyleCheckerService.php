<?php 
require_once(dirname(__FILE__) . '/ServiceHeaders.php');
require_once('plugins/report/include/facade/CheckStyleCheckerFacade.php');

$server = new soap_server;
$server->configureWSDL("CheckStyleCheckerService", $gNamespace, $gNamespace . '/CheckStyleCheckerService.php');


$server->register('addCheckStyleChecker',
                  array('userName'      => 'xsd:string', 
                        'userPw'        => 'xsd:string',
                        'unixGroupName' => 'xsd:string',
                        'mavenArtefactId' => 'xsd:string',
                        'mavenGroupId' => 'xsd:string',
                        'mavenVersion' => 'xsd:string',
                        'checkStyleCheckerDTOCollection' => 'tns:CheckStyleCheckerDTOCollection'),
                  array('return' => 'xsd:boolean'),
                  $gNamespace . '/CheckStyleCheckerService.php',                     // namespace
                  $gNamespace . '/CheckStyleCheckerService.php/addCheckStyleChecker', // SOAPAction
                  'rpc',                                                  // style
                  'encoded'                                               // use   
);

$server->register('deleteCheckStyleCheckerByMavensIds',
                  array('userName'      => 'xsd:string', 
                        'userPw'        => 'xsd:string',
                        'unixGroupName' => 'xsd:string',
                        'mavenArtefactId' => 'xsd:string',
                        'mavenGroupId' => 'xsd:string',
                        'mavenVersion' => 'xsd:string'),
                  array('return' => 'xsd:boolean'),
                  $gNamespace . '/CheckStyleCheckerService.php',                   // namespace
                  $gNamespace . '/CheckStyleCheckerService.php/deleteCheckStyleCheckerByMavensIds', // SOAPAction
                  'rpc',                                                // style
                  'encoded'                                             // use                  
);


$server->wsdl->addComplexType(
    'CheckStyleCheckerDTO',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'objective'        => array('name' => 'objective',        'type' => 'xsd:string'),        
        'criteriaName'     => array('name' => 'criteriaName',     'type' => 'xsd:string'),
        'criteriaCoef'     => array('name' => 'criteriaCoef',     'type' => 'xsd:string'),
        'criteriaContext'  => array('name' => 'criteriaContext',  'type' => 'xsd:string'),
        'criteriaMethod'   => array('name' => 'criteriaMethod',   'type' => 'xsd:string'),
        'ruleId'           => array('name' => 'ruleId',           'type' => 'xsd:string')        
    )
);


$server->wsdl->addComplexType('CheckStyleCheckerDTOCollection',
                'complexType',
                'array',
                '',
                'SOAP-ENC:Array',
                array(),
                array(
                    array('ref'=>'SOAP-ENC:arrayType',
                    'wsdl:arrayType'=>'tns:CheckStyleCheckerDTO[]')
                ),
                'tns:CheckStyleCheckerDTO'
);

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);


/**
 * Ajoute les regles Checkstyle en vérifiant que l'utilisateur a le droit de le faire.
 * 
 * @param userName le login de l'utilisateur.
 * @param userPw le password de l'utilisateur.
 * @param unixGroupName le nom unix du projet.
 * @param checkStyleCheckerDTOCollection le tableau des informations sur les regles Checkstyle.
 * @return vrai si l'ajout a réussi.
 */
function addCheckStyleChecker($userName, $userPw, $unixGroupName, $mavenArtefactId, $mavenGroupId, $mavenVersion, $checkStyleCheckerDTOCollection){
	
    $groupId = GroupFacade::getGroupId($userName, $userPw, $unixGroupName);
    
    if($groupId === false){
        return false;
    }
    
    TransactionFacade::begin();
    
    foreach($checkStyleCheckerDTOCollection as $checkStyleCheckerDTO){
    
        $dto = new CheckStyleCheckerDTO();
        $dto->setObjective($checkStyleCheckerDTO["objective"]);
        $dto->setCriteriaName($checkStyleCheckerDTO["criteriaName"]);
        $dto->setCriteriaCoef($checkStyleCheckerDTO["criteriaCoef"]);
        $dto->setCriteriaContext($checkStyleCheckerDTO["criteriaContext"]);
        $dto->setCriteriaMethod($checkStyleCheckerDTO["criteriaMethod"]);
        $dto->setRuleId($checkStyleCheckerDTO["ruleId"]);
        $dto->setMavenArtefactId($mavenArtefactId);
        $dto->setMavenGroupId($mavenGroupId);
        $dto->setMavenVersion($mavenVersion);
        $dto->setGroupId($groupId);
                             
        if(CheckStyleCheckerFacade::addCheckStyleChecker($dto) === false){
            TransactionFacade::rollback();
            return false;
        }
    
    }
    
    TransactionFacade::commit();
    
    return true;
}

function deleteCheckStyleCheckerByMavensIds($userName, $userPw, $unixGroupName, $mavenArtefactId, $mavenGroupId, $mavenVersion){
	
	$groupId = GroupFacade::getGroupId($userName, $userPw, $unixGroupName);
    
    if($groupId === false){
        return false;
    }
    
    TransactionFacade::begin();
    
    if(CheckStyleCheckerFacade::deleteCheckStyleCheckerByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion) === false){
        TransactionFacade::rollback();
        return false;
    }
    
    TransactionFacade::commit();
    
    return true;
}

?>