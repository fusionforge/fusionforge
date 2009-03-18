<?php
function LoadGforgeSkin() {
        require_once('/usr/share/mediawiki/skins/GForge.php');
        GforgeRegisterMWHook();
}

$wgSkinExtensionFunctions[]='LoadGforgeSkin';
?>
