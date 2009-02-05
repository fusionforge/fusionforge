<?php
class CLanguageFormat
{
	function FormatWiki($strWikiContent)
	{
		return $strWikiContent;
	}
	
	function FormatHtml($strHtmlContent)
	{
		$strSpaces = '['.chr(0x20).chr(0xa0).chr(0x0a).',.(){}<>]';
		$astrSources = array(
			// Nombre ordinaux
			'/('.$strSpaces.')1(o|a)(s?)(?='.$strSpaces.')/',
			'/('.$strSpaces.')([23456789])(o|a)(s?)(?='.$strSpaces.')/',
			'/('.$strSpaces.')([0123456789]{2,})(o|a)(s?)(?='.$strSpaces.')/',
	
			// Espace insécables
			'/ (!|\?|:|;|»)/', 
			'/(«|¡|¿) /'
		);
		$astrDestinations = array(
			// Nombre ordinaux
			'${1}1<sup>$2$3</sup>$4',
			'$1$2<sup>$3$4</sup>$5',
			'$1$2<sup>$3$4</sup>$5',
	
			// Espace insécables
			' $1',
			'$1 '
		);
		
		$strReturn = preg_replace($astrSources, $astrDestinations, $strHtmlContent);

	
		// Le formatage précédent peut avoir placé des balises dans des balises, il faut les échapper
		$astrSources = array('/<([^>]*)<[^<>]*>([^<]*)<\/[^<>]*>/');
		$astrDestinations = array('<$1$2');
		
		while(true)
		{
			$strReturn2 = preg_replace($astrSources, $astrDestinations, $strReturn);

			if( $strReturn2 == $strReturn )
				break;

			$strReturn = $strReturn2;
		}

		return $strReturn2;
	}
}
?>