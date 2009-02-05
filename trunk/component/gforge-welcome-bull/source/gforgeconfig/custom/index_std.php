<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
?>
<!-- whole page table -->
<table width="100%" cellpadding="5" cellspacing="0" border="0"><tr>
<!-- Left column -->
<td width="65%" valign="top">
<h3>NovaForge est l'usine de développement logiciel de Bull</h3>
<p>
Elle est basée sur un ensemble d'outils Open Source utilisés depuis des années par la R&D de Bull dans ses programmes de développements répartis.
</p>
<h3>Une plate-forme collaborative</h3>
<p>
NovaForge offre des services de gestion de sources, de listes de diffusion, de gestion de bugs, de forums de discussion, de gestion de tâches, d'hébergement de sites, d'archivage permanent de fichiers, de sauvegardes complètes.
Tous ces outils sont accessibles et administrables via une interface web.
</p>
<h3>Utiliser NovaForge</h3>
<p>
Pour pouvoir utiliser NovaForge au mieux, il est nécessaire de <a href="/account/register.php">s'inscrire en tant qu'utilisateur</a>.
Cela vous permettra d'accéder à tous les services que nous offrons.
Vous pouvez bien entendu naviguer sur le site sans vous inscrire, mais vous n'aurez pas un accès complet et ne pourrez pas utiliser pleinement tous les services.
</p>
<h3>Créer votre propre projet</h3>
<p>
Commencez par <a href="/account/register.php">vous inscrire en tant qu'utilisateur</a>, puis identifiez-vous et enfin <a href="/register/"> enregistrez votre projet</a>.
</p>
<?php
echo $HTML->boxTop ($Language->getText ('group', 'long_news'));
echo news_show_latest ($sys_news_group, 5, true, false, false, 5);
echo $HTML->boxBottom ();
?>
</td>
<!-- Right column -->
<td width="35%" valign="top">
<?php echo show_features_boxes (); ?>
</td>
</tr></table>
