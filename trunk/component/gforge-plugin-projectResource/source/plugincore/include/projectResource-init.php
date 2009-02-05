<?php

require_once 'plugins/projectResource/config.php';

class OutilsCommunsPlugin extends Plugin {
	var $libelle;
	var $lienRedirection;
	
	function OutilsCommunsPlugin( $libelle, $lienRedirection ){ 
		//global $outilsCommunsLibelle;
		$this->Plugin();
		$this->name = "projectResource";
		$this->text = $libelle;
		$this->hooks[] = "groupmenu";
		
		$this->libelle = $libelle;
		$this->lienRedirection = $lienRedirection;
	}

    /*
    function activatePlugin(& $Group) {
        return;
    }
    
    function desactivatePlugin(& $Group) {
        return;
    }
    */

	/**
	* The function to be called for a Hook
	*
	* @param String $hookname The name of the hookname that	has been happened
	* @param String $params The params of the Hook
	*
	*/
	function CallHook ($hookname, $params) {
		if ($hookname == "groupmenu") {
			$params['DIRS'][]= $this->lienRedirection;
			$params['TITLES'][]=$this->text;
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		}

	}
}



$OutilsCommunsPluginObject = new OutilsCommunsPlugin($libelle, $lienRedirection);

register_plugin ($OutilsCommunsPluginObject) ;

?>
