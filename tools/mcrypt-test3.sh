#! /usr/bin/php4 -f
<?php
$td = mcrypt_module_open ('des', '', 'ejb', '') or die;
$td = mcrypt_module_open ('des', '', 'ofb', '') or die;
//$td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
$ks = mcrypt_enc_get_key_size ($td);
$key = substr (md5 ($_GET["key"]), 0, $ks);
mcrypt_generic_init ($td, $key, $iv);
echo mcrypt_generic ($td, $_GET["data"]);
mcrypt_generic_deinit ($td);
?>
