<?php
global $gfplugins;
require_once $gfplugins.'scmhook/common/scmhook.class.php';

class allowRevPropChange extends scmhook {
	function allowRevPropChange() {
		$this->name        = "Allow RevProp Changes";
		$this->description = _('Allow SCM committers to change revision properties.');
		$this->classname   = "allowRevPropChange";
		$this->command     = 'exit 0';
		$this->hooktype    = "pre-revprop-change";
		$this->label       = "scmsvn";
		$this->unixname    = "allowrevpropchange";
		$this->needcopy    = 0;
	}
}
?>
