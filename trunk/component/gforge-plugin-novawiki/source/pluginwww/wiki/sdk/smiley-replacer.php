<?php
////////////////////////////////////////////////////////////////////////////////
// ***** BEGIN LICENSE BLOCK *****
// This file is part of ChuWiki.
// Copyright (c) 2004 Vincent Robert and contributors. All rights
// reserved.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA//
//
// ***** END LICENSE BLOCK *****
////////////////////////////////////////////////////////////////////////////////

function ParseSmileyFile($strFileName)
{
	// Chargement du fichier des smileys
	$strContent = LoadFile($strFileName);

	// Les smileys sont trait�s au niveau HTML, il faut donc convertir 
	// les caract�res sp�ciaux �ventuels
	$strContent = htmlspecialchars($strContent);

	// On va parser les lignes une par une
	$astrLines = explode("\n", $strContent);
	$aVars = array();

	foreach($astrLines as $strLine)
	{
		// Commentaires
		if( substr($strLine, 0, 1) == ';' )
		{
			continue;
		}
		
		$nMiddle = strpos($strLine, '=');
		if( $nMiddle)
		{
			$strName = trim(substr($strLine, 0, $nMiddle));
			$strValue = trim(substr($strLine, $nMiddle + 1));

			if( $strValue == 'true' )
			{
				$strValue = true;
			}
			if( $strValue == 'false' )
			{
				$strValue = false;
			}
			
			$aVars[$strValue] = $strName;
		}
	}
	
	return $aVars;
}

function MakeImageSmileys(&$strContent)
{
	global $k_strWikiURI, $k_aConfig;

	$strSmileyPackPath = $k_aConfig['SmileyPath'] . '/';
	$astrSmileys = ParseSmileyFile($strSmileyPackPath . 'smileys.ini');

	$Replacer = new CSmileyReplacer($astrSmileys, true, 
									$k_strWikiURI . $strSmileyPackPath);
	$strContent = $Replacer->Replace($strContent);
}

// SmileyReplacer par psydk (www.psydk.org) pour le ChuWiki d'Anubis
// 2004-03-26 Version 1.1
// 2004-07-05 Version 1.2
// Pas de smileys selon le caract�re qui suit
// Nouvel algorithme qui corrige une faille quand plusieurs smileys se suivent
// 2004-07-06 Version 1.3
// Plus rapide
//
// Conditions d'utilisation :
// - 64 smileys maximum ;
// - chaque code de smiley doit faire deux octets minimum.
//
// Exemple d'utilisation :
// $aReplacements = array("E-)" => "ExtraHappy", ":)"  => "Happy");
// $sr = new CSmileyReplacer($aReplacements, false);
// $str = $sr->Replace(":) E-)");

// Classe utilitaire pour CSmileyReplacer
class CClosestFinder
{
	var $m_strContent;
	var $m_astrPatterns;
	var $m_nAdvance;

	function CClosestFinder($strContent, $astrPatterns)
	{
		$this->m_strContent = $strContent;
		$this->m_astrPatterns = $astrPatterns;
		$this->m_nAdvance = 0;
		$this->m_abSearch = array_fill(0, sizeof($this->m_astrPatterns), true);
	}

	function SetReverseMode()
	{
		$this->m_strContent = strrev($this->m_strContent);
		$this->m_nAdvance = 0;
		$this->m_abSearch = array_fill(0, sizeof($this->m_astrPatterns), true);
		
		$nPatternCount = sizeof($this->m_astrPatterns);
		for($iPattern = 0; $iPattern < $nPatternCount; ++$iPattern)
		{
			$strPattern = $this->m_astrPatterns[$iPattern];
			$this->m_astrPatterns[$iPattern] = strrev($strPattern);
		}
	}

	function FindNext()
	{
		$nKeptPos = strlen($this->m_strContent);
		if( $this->m_nAdvance >= $nKeptPos)
			return false; // Fin du parcours

		$nPatternCount = sizeof($this->m_astrPatterns);
		$nKeptPattern = -1;
				
		for($iPattern = 0; $iPattern < $nPatternCount; ++$iPattern)
		{
			if( $this->m_abSearch[$iPattern])
			{
				$strPattern = $this->m_astrPatterns[$iPattern];

				$nFoundAt = strpos($this->m_strContent, $strPattern, $this->m_nAdvance);
				if( $nFoundAt === false)
				{
					// Ne plus rechercher ce pattern
					$this->m_abSearch[$iPattern] = false;
				}
				else
				{
					if( $nFoundAt < $nKeptPos)
					{
						$nKeptPos = $nFoundAt;
						$nKeptPattern = $iPattern;
					}
				}
			}
		}

		if( $nKeptPattern < 0)
			return false;

		$this->m_nAdvance = $nKeptPos + strlen($this->m_astrPatterns[$nKeptPattern]);
		return $nKeptPattern;
	}
}

// Classe principale
class CSmileyReplacer
{
	var $m_astrSmileys;
	var $m_aIds;
	var $m_aImgElements;
	var $m_astrBeforeOk; 
	var $m_astrAfterOk;

	// Retourne null si erreur
	// Si $bUtf est � false, alors on consid�re qu'on est en latin-9, latin-1 ou windows-1252
	function CSmileyReplacer($aReplacements, $bUtf8, $strBaseDir = '')
	{
		/////////////////////////////////////////////////////////////////////////
		// Cr�e les tableaux des caract�res autoris�s avant et apr�s
		// Espace normale, \n, \t, \r
		$this->m_astrBeforeOk = array(chr(32), chr(10), chr(9), chr(13), '>');
		$this->m_astrAfterOk = array(chr(32), chr(10), chr(9), chr(13), ',', '.', '<');

		// En utf-8 l'espace ins�cable est cod�e 0xC2 0xA0, en latin il est cod� 0xA0
		if( $bUtf8)
		{
			array_push($this->m_astrBeforeOk, chr(0xC2).chr(0xA0));
			array_push($this->m_astrAfterOk, chr(0xC2).chr(0xA0));
		}
		else
		{
			array_push($this->m_astrBeforeOk, chr(0xA0));
			array_push($this->m_astrAfterOk, chr(0xA0));
		}

		/////////////////////////////////////////////////////////////////////////
		$nSmileyCount = sizeof($aReplacements);
		if( $nSmileyCount > 64)
		{
			// Oops, pas possible d'en g�rer davantage
			return null;
		}
		$this->m_astrSmileys = array_keys($aReplacements);
	
		/////////////////////////////////////////////////////////////////////////
		// Cr�e le tableau de conversion code smiley-caracat�res --> smiley-ID
		$this->m_aIds = array();

		for($iSmiley = 0; $iSmiley < $nSmileyCount; ++$iSmiley)
		{
			$strSmiley = $this->m_astrSmileys[$iSmiley];

			$strSmileyId = $strSmiley;
			$nSmileyLength = strlen($strSmiley);
			if( $nSmileyLength < 2)
			{
				// Deux octets minimum
				return null;
			}
			
			// L'ID est cass� sur deux octets, 3 bits de poids fort et 3 bits de poids faible
			// Ainsi chaque octet contient un caract�re qui est interdit et qu'on ne retrouvera
			// pas ailleurs
			$strSmileyId[0] = chr($iSmiley >> 3);
			$strSmileyId[1] = chr($iSmiley & 0x07);

			// Le reste on padde avec un autre caract�re interdit mais qui ne peut pas �tre un
			// octet codant l'ID (pour �viter les remplacements malheureux)
			for($iChar = 2; $iChar < $nSmileyLength; ++$iChar)
			{
				$strSmileyId[$iChar] = chr(8); // Le 8 ira tr�s bien
			}
			$this->m_aIds[$iSmiley] = $strSmileyId;
		}
		/////////////////////////////////////////////

		/////////////////////////////////////////////
		// Construit le tableau des remplacement
		$this->m_aImgElements = array();
		
		$astrLongNames = array_values($aReplacements);
		for($iSmiley = 0; $iSmiley < $nSmileyCount; ++$iSmiley)
		{
			$strSmiley = $this->m_astrSmileys[$iSmiley];
			$strLongName = $astrLongNames[$iSmiley];

			// La routine consid�re que les images sont en png
			// � personnaliser selon ses gouts
			// Note : v�rifier si le code du smiley pour le � alt � ne contient
			// pas un caract�re sp�cial
			$this->m_aImgElements[$iSmiley] = '<img src="' . $strBaseDir . $strLongName . '" alt="' . $strSmiley . '" class="Smiley"/>';
		}
	}
	
	// Recherche si la chaine commen�ant � $nStartAt fait partie
	// du tableau de patterns
	// Le caract�re � la position $nStartCharPos est compris dans la recherche
	// $nDirection : -1 pour la gauche, +1 pour la droite
	// Retourne false si ce n'est pas le cas
	function FindImmediate($strContent, $nStartCharPos, $astrPatterns, $nDirection)
	{
		$nContentLength = strlen($strContent);
		$nPatternCount = sizeof($astrPatterns);
		for($iPattern = 0; $iPattern < $nPatternCount; ++$iPattern)
		{
			$strPattern = $astrPatterns[$iPattern];
			$nPatternLength = strlen($strPattern);
			$nAddOffset = 0;
			if( $nDirection < 0)
			{
				$nAddOffset = 1 - $nPatternLength;
			}
			else
			{
				$nAddOffset = 0;
			}
			
			for($i = 0; $i < $nPatternLength ; ++$i)
			{
				$nSrcOffset = $nStartCharPos + $nAddOffset + $i;
				if( !( 0 <= $nSrcOffset && $nSrcOffset < $nContentLength))
				{
					break;
				}
				$cPattern = $strPattern[$i];
				$cContent = $strContent[$nSrcOffset];
								
				if( $cContent != $cPattern)
				{
					break;
				}
				if( $i == $nPatternLength - 1)
				{
					// Pattern trouv�
					return $iPattern;
				}
			}
		}
		return false;
	}

	// Retourne true si on peut effectuer le remplacement par un smiley graphique
	function ShouldReplaceLeftToRight($strContent, $nFoundAt)
	{
		// D�but de document ?
		if( $nFoundAt == 0)
		{
			return true;
		}
		// Milieu de document
		$cPrevious = $strContent[$nFoundAt - 1];

		// Un smiley juste avant ?
		if( $cPrevious <= chr(8))
		{
			// Seuls les smileys utilisent ces codes interdits
			return true;
		}
		if( $this->FindImmediate($strContent, $nFoundAt - 1, $this->m_astrBeforeOk, -1) === false)
			return false;
		return true;
	}
	// Retourne true si on peut effectuer le remplacement par un smiley graphique
	// en regardant les caract�res apr�s ceux du smileys
	function ShouldReplaceRightToLeft($strContent, $nLastSmileyCharPos)
	{
		// Fin de document ?
		if( $nLastSmileyCharPos == strlen($strContent) - 1)
		{
			return true;
		}
		
		// Milieu de document
		$cNext = $strContent[$nLastSmileyCharPos + 1];
		
		// Un smiley juste apr�s ?
		if( $cNext <= chr(8))
		{
			// Seuls les smileys utilisent ces codes interdits
			return true;
		}
		if( $this->FindImmediate($strContent, $nLastSmileyCharPos + 1, $this->m_astrAfterOk, +1) === false)
			return false;
		return true;
	}

	////////////////////////////////////////////////////////////////////
	// Pas de smiley plus court que deux octets
	function Replace($strContent)
	{
		$nContentLength = strlen($strContent);
				
		////////////////////////////
		// De gauche � droite
		$strContentLeftToRight = $strContent;

		$cf = new CClosestFinder($strContent, $this->m_astrSmileys);
		while(true)
		{
			$nSmiley = $cf->FindNext();
			
			if( $nSmiley === false)
			{
				// Plus rien dans le texte
				break;
			}

			$nSmileyLength = strlen($this->m_astrSmileys[$nSmiley]);
			$strId = $this->m_aIds[$nSmiley];
			$nFoundAt = $cf->m_nAdvance - $nSmileyLength;

			if( $this->ShouldReplaceLeftToRight($strContentLeftToRight, $nFoundAt))
			{
				for($iSmileyChar = 0; $iSmileyChar < $nSmileyLength; ++$iSmileyChar)
				{
					$strContentLeftToRight[$nFoundAt + $iSmileyChar] = $strId[$iSmileyChar];
				}
			}
		}
	
		////////////////////////////
		// De droite � gauche
		$strContentRightToLeft = $strContent;

		$cf->SetReverseMode();
		while(true)
		{
			$nSmiley = $cf->FindNext();
			
			if( $nSmiley === false)
			{
				// Plus rien dans le texte
				break;
			}

			$nSmileyLength = strlen($this->m_astrSmileys[$nSmiley]);
			$strId = $this->m_aIds[$nSmiley];
			$nFoundAt = $nContentLength - $cf->m_nAdvance;

			if( $this->ShouldReplaceRightToLeft($strContentRightToLeft, $nFoundAt + $nSmileyLength - 1))
			{
				for($iSmileyChar = 0; $iSmileyChar < $nSmileyLength; ++$iSmileyChar)
				{
					$strContentRightToLeft[$nFoundAt + $iSmileyChar] = $strId[$iSmileyChar];
				}
			}
		}
	
		for($i = 0; $i < $nContentLength; ++$i)
		{
			$c0 = $strContentLeftToRight[$i];
			$c1 = $strContentRightToLeft[$i];
			if( $c0 == $c1 && $c0 <= chr(8))
			{
				// Un smiley qui va bien
				$strContent[$i] = $c0;
			}
		}

		// Un chtit coup de str_replace massif
		return str_replace($this->m_aIds, $this->m_aImgElements, $strContent);
	}
}
?>