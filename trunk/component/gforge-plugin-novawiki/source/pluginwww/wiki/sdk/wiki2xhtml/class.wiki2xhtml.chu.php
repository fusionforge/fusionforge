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

require(dirname(__FILE__) . '/class.wiki2xhtml.basic.php');

class wiki2xhtmlChu extends wiki2xhtmlBasic
{
	var $bInCode = false;

	function wiki2xhtmlChu()
	{
		parent::wiki2xhtml();

		# Mise en place des options
		$this->setOpt('active_title',1);		# Activation des titres !!!
		$this->setOpt('active_setext_title',0);	# Activation des titres setext (EXPERIMENTAL)
		$this->setOpt('active_hr',1);			# Activation des <hr />
		$this->setOpt('active_lists',1);		# Activation des listes
		$this->setOpt('active_deflists',1);		# Activation des listes de définitions
		$this->setOpt('active_quote',1);		# Activation du <blockquote>
		$this->setOpt('active_pre',1);		# Activation du <pre>
		$this->setOpt('active_empty',0);		# Activation du bloc vide øøø
		$this->setOpt('active_auto_urls',0);	# Activation de la reconnaissance d'url
		$this->setOpt('active_urls',1);		# Activation des liens []
		$this->setOpt('active_auto_img',0);	# Activation des images automatiques dans les liens []
		$this->setOpt('active_img',1);		# Activation des images (())
		$this->setOpt('active_anchor',1);		# Activation des ancres ~...~
		$this->setOpt('active_em',1);			# Activation du <em> ''...''
		$this->setOpt('active_strong',1);		# Activation du <strong> __...__
		$this->setOpt('active_br',1);			# Activation du <br /> %%%
		$this->setOpt('active_q',1);			# Activation du <q> {{...}}
		$this->setOpt('active_code',1);		# Activation du <code> @@...@@
		$this->setOpt('active_acronym',1); 	# Activation des acronymes
		$this->setOpt('active_ins',0);		# Activation des ins ++..++
		$this->setOpt('active_del',0);		# Activation des del --..--
		$this->setOpt('active_footnotes',0);	# Activation des notes de bas de page
		$this->setOpt('active_wikiwords',0);	# Activation des mots wiki
		$this->setOpt('active_macros',0);		# Activation des macros «««..»»»
		
		$this->setOpt('parse_pre',0);			# Parser l'intérieur de blocs <pre> ?
		
		$this->setOpt('active_fix_word_entities',0); # Fixe les caractères MS
		$this->setOpt('active_fr_syntax',0);	# Corrections syntaxe FR
		
		$this->setOpt('first_title_level',2);	# Premier niveau de titre <h..>
	}
	
	// Change inline syntax
	function __initTags()
	{
		$this->tags = array(
			'strong' => array('__','__'),
			'em' => array("_","_"),
			'acronym' => array('??','??'),
			'a' => array('[',']'),
			'img' => array('((','))'),
			'q' => array('\'\'','\'\''),
			'code' => array('@@','@@'),
			'anchor' => array('~','~')
		);
				
		$this->open_tags = $this->__getTags();
		$this->close_tags = $this->__getTags(false);
		$this->all_tags = $this->__getAllTags();
		$this->tag_pattern = $this->__getTagsPattern();
		
		$this->escape_table = $this->all_tags;
		array_walk($this->escape_table,create_function('&$a','$a = \'\\\\\'.$a;'));
	}

	// Change block syntax
	function __getLine($i,&$type,&$mode)
	{
		$pre_type = $type;
		$pre_mode = $mode;
		$type = $mode = NULL;
		
		if (empty($this->T[$i])) {
			return false;
		}
		
		$line = htmlspecialchars($this->T[$i],ENT_NOQUOTES);
		
		# Fin d'un bloc préformaté
		if( $this->bInCode && preg_match('/(.*)}}}\s*$/',$line,$cap) )
		{
			$type = 'pre';
			$line = $cap[1];
			$this->bInCode = false;
		}
		# Déjà dans un bloc de code
		elseif( $this->bInCode )
		{
			$type = 'pre';
		}
		# Ligne vide
		else if (empty($line))
		{
			$type = NULL;
		}
		elseif ($this->getOpt('active_empty') && preg_match('/^øøø(.*)$/',$line,$cap))
		{
			$type = NULL;
			$line = trim($cap[1]);
		}
		# Titre
		elseif ($this->getOpt('active_title') && preg_match('/^([!]{1,4})(.*)$/',$line,$cap))
		{
			$type = 'title';
			$mode = strlen($cap[1]);
			$line = trim($cap[2]);
		}
		# Ligne HR
		elseif ($this->getOpt('active_hr') && preg_match('/^[\-=]{4}[=\- ]*$/',$line))
		{
			$type = 'hr';
			$line = NULL;
		}
		# Blockquote
		elseif ($this->getOpt('active_quote') && preg_match('/^(&gt;|;:)(.*)$/',$line,$cap))
		{
			$type = 'blockquote';
			$line = trim($cap[2]);
		}
		# Liste de définitions
		elseif ($this->getOpt('active_deflists') && preg_match('/^;(((\\\\:)|[^\\\\:])*):(.*)$/',$line,$cap))
		{
			$type = 'deflist';
			$line = array( str_replace('\:', ':', trim($cap[1])), trim($cap[4]) );
		}
		# Liste
		elseif ($this->getOpt('active_lists') && preg_match('/^([*#-]+)(.*)$/',$line,$cap))
		{
			$type = 'list';
			$mode = $cap[1];
			$valid = true;
			
			# Vérification d'intégrité
			$dl = ($type != $pre_type) ? 0 : strlen($pre_mode);
			$d = strlen($mode);
			$delta = $d-$dl;
			
			if ($delta < 0 && strpos($pre_mode,$mode) !== 0) {
				$valid = false;
			}
			if ($delta > 0 && $type == $pre_type && strpos($mode,$pre_mode) !== 0) {
				$valid = false;
			}
			if ($delta == 0 && $mode != $pre_mode) {
				$valid = false;
			}
			if ($delta > 1) {
				$valid = false;
			}
			
			if (!$valid) {
				$type = 'p';
				$mode = NULL;
				$line = '<br />'.$line;
			} else {
				$line = trim($cap[2]);
			}
		}
		# Début d'un bloc préformaté
		elseif ($this->getOpt('active_pre') && !$this->bInCode && preg_match('/^\\s*{{{(.*)/',$line,$cap) )
		{
			$type = 'pre';
			$line = $cap[1];
			$this->bInCode = true;
		}
		# Préformaté
		elseif ($this->getOpt('active_pre') && preg_match('/^[ ]{1}(.*)$/',$line,$cap) )
		{
			$type = 'pre';
			$line = $cap[1];
		}
		# Paragraphe
		else 
		{
			$type = 'p';
			$line = trim($line);
		}
		
		return $line;
	}

	function __openLine($type,$mode,$pre_type,$pre_mode)
	{
		$open = ($type != $pre_type);
		
		if ($open && $type == 'p')
		{
			return "\n<p>";
		}
		elseif ($open && $type == 'blockquote')
		{
			return "\n<blockquote><p>";
		}
		elseif (($open || $mode != $pre_mode) && $type == 'title')
		{
			$fl = $this->getOpt('first_title_level');
			$l = $fl + $mode - 1;
			return "\n<h".($l).'>';
		}
		elseif ($open && $type == 'pre')
		{
			return "\n<pre>";
		}
		elseif ($open && $type == 'hr')
		{
			return "\n<hr />";
		}
		elseif ($open && $type == 'deflist')
		{
			return "\n<dl>\n";
		}
		elseif ($type == 'list')
		{
			$dl = ($open) ? 0 : strlen($pre_mode);
			$d = strlen($mode);
			$delta = $d-$dl;
			$res = '';
			
			if($delta > 0) {
				if(substr($mode, -1, 1) == '#') {
					$res .= "<ol>\n";
				} else {
					$res .= "<ul>\n";
				}
			} elseif ($delta < 0) {
				$res .= "</li>\n";
				for($j = 0; $j < abs($delta); $j++) {
					if (substr($pre_mode,(0 - $j - 1), 1) == '#') {
						$res .= "</ol>\n</li>\n";
					} else {
						$res .= "</ul>\n</li>\n";
					}
				}
			} else {
				$res .= "</li>\n";
			}
			
			return $res."<li>";
		}
		else
		{
			return NULL;
		}
	}
	
	function __closeLine($type,$mode,$pre_type,$pre_mode)
	{
		$close = ($type != $pre_type);
		
		if ($close && $pre_type == 'p')
		{
			return "</p>\n";
		}
		elseif ($close && $pre_type == 'blockquote')
		{
			return "</p></blockquote>\n";
		}
		elseif (($close || $mode != $pre_mode) && $pre_type == 'title')
		{
			$fl = $this->getOpt('first_title_level');
			$l = $fl + $pre_mode - 1;
			return '</h'.($l).">\n";
		}
		elseif ($close && $pre_type == 'pre')
		{
			return "</pre>\n";
		}
		elseif ($close && $pre_type == 'deflist')
		{
			return "\n</dl>\n";
		}
		elseif ($close && $pre_type == 'list')
		{
			$res = '';
			for($j = 0; $j < strlen($pre_mode); $j++) {
				if(substr($pre_mode,(0 - $j - 1), 1) == '#') {
					$res .= "</li>\n</ol>";
				} else {
					$res .= "</li>\n</ul>";
				}
			}
			return $res;
		}
		else
		{
			return "\n";
		}
	}

	function __parseBlocks()
	{
		$mode = $type = NULL;
		$res = '';
		$max = count($this->T);
		
		for ($i=0; $i<$max; $i++)
		{
			$pre_mode = $mode;
			$pre_type = $type;
			$end = ($i+1 == $max);
			
			$line = $this->__getLine($i,$type,$mode);
			
			if ($type != 'pre' || $this->getOpt('parse_pre')) {
				if ( $type == 'deflist' )
				{
					$line[0] = $this->__inlineWalk($line[0]);
					$line[1] = $this->__inlineWalk($line[1]);
				}
				else
				{
					$line = $this->__inlineWalk($line);
				}
			}
			
			$res .= $this->__closeLine($type,$mode,$pre_type,$pre_mode);
			$res .= $this->__openLine($type,$mode,$pre_type,$pre_mode);
						
			# P dans les blockquotes
			if ($type == 'blockquote' && trim($line) == '' && $pre_type == $type) {
				$res .= "</p>\n<p>";
			}
			
			# Correction de la syntaxe FR dans tous sauf pre et hr
			# Sur idée de Christophe Bonijol
			# Changement de regex (Nicolas Chachereau)
			if ($this->getOpt('active_fr_syntax') && $type != NULL && $type != 'pre' && $type != 'hr') {
				$line = preg_replace('/[ ]+([:?!;](\s|$))/','&nbsp;$1',$line);
				$line = preg_replace('/[ ]+(»)/','&nbsp;$1',$line);
				$line = preg_replace('/(«)[ ]+/','$1&nbsp;',$line);
			}
			
			if ( $type == 'deflist' )
			{
				$res .= '<dt>' . $line[0] . '</dt><dd>' . $line[1] . '</dd>';
			}
//			elseif ( $type == 'pre' && $pre_type == 'pre' )
//			{
//				$res .= $line;
//				$res .= "\n";
//			}
			else
			{
				$res .= $line;
			}
		}
		
		return trim($res);
	}
}

?>