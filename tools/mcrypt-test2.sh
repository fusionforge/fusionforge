#! /usr/bin/php4 -f
<?php
//Open encryption module
$keystr="Veni, vidi, vici!!!";
$td = mcrypt_module_open (MCRYPT_ARCFOUR, "", MCRYPT_MODE_STREAM, "");
//$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size ($td), MCRYPT_RAND);

//Encrypt data
$data = "message";
//mcrypt_generic_init($td, $keystr, $iv);//HERE IS THE PROBLEM
mcrypt_generic_init($td, $keystr, false);
$encrypted_data = mcrypt_generic ($td, $data);
mcrypt_generic_end ($td);
echo $encrypted_data;
?>
