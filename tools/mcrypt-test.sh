#! /usr/bin/php4 -f
<?
// Voir http://bugs.php.net/bug.php?id=13399&edit=1
function encrypt_pass($ascii_pass)
{
	if ($ascii_pass) {
		$keystr="Veni, vidi, vici!!!";
		//echo "keystr=$keystr\n";
		//$td = mcrypt_module_open(MCRYPT_TripleDES, "", MCRYPT_MODE_ECB, "");
		$td = mcrypt_module_open(MCRYPT_TWOFISH, "", MCRYPT_MODE_ECB, "");
		//echo "td=$td\n";
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		//echo "iv=$iv\n";
		mcrypt_generic_init ($td, $keystr, $iv);
		$enc_pas= mcrypt_generic ($td, $ascii_pass);
		mcrypt_generic_end ($td);
	}else {$enc_pas="";}
	return $enc_pas;
}

echo encrypt_pass("ItmyPass");
?>
