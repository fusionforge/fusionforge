<?php
function LoadGforgeSkin() {
        require_once('/usr/share/mediawiki1.10/skins/GForge.php');
        GforgeRegisterMWHook();
}

$wgSkinExtensionFunctions[]='LoadGforgeSkin';
?>
