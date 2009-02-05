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
// ***** END LICENSE BLOCK *****
////////////////////////////////////////////////////////////////////////////////

require(dirname(__FILE__) . '/sdk/sdk.php');
/////////////////////////////////////////////////////////////

// Chargement des informations de la page
$strPage = GetCurrentPage();

// Chargement du contenu wiki pour cette page
$strWikiContent = GetWikiContent($strPage);

// On ajoute du contenu supplémentaire pour certaines pages comme la liste ou les changements
$strModifiedWikiContent = $strWikiContent . GetSpecialContent($strPage);

// Rendu wiki
$strHtmlContent = Render($strModifiedWikiContent);

/////////////////////////////////////////////////////////////

// Chargement du template
$strContent = LoadTemplate('wiki');

// Les premiers remplacements sont en fonction du fichier de config
$astrReplacements = BuildStandardReplacements();

// Ajoute les remplacements « runtime »
AddReplacement($astrReplacements, 'Page.Name', htmlspecialchars($strPage));
AddReplacement($astrReplacements, 'Page.Wiki', $strWikiContent);
AddReplacement($astrReplacements, 'Page.Html', $strHtmlContent);

// Applique les remplacements
$strContent = ReplaceAll($strContent, $astrReplacements);

/////////////////////////////////////////////////////////////
WriteXhtmlHeader();
echo $strContent;
?>