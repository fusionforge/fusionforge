<?php
////////////////////////////////////////////////////////////////////////////////
// ChuWiki syntax to xhtml
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

class chu_to_xhtml extends WikiRendererConfig 
{
	var $inlinetags = array( 
		'chuxhtml_strong',
		'chuxhtml_em',
		'chuxhtml_code',
		'chuxhtml_q',
		'chuxhtml_acronym',
		'chuxhtml_link', 
		'chuxhtml_image',
		'chuxhtml_anchor', 
	);

	var $textLineContainer = 'WikiHtmlTextLine';

	var $bloctags = array(
		'chuxhtml_title', 
		'chuxhtml_list', 
		'chuxhtml_pre',
		'chuxhtml_hr',
		'chuxhtml_blockquote',
		'chuxhtml_definition',
		'chuxhtml_p'
	);

   var $simpletags = array('%%%' => '<br />');

   /**
    * @var   integer   niveau minimum pour les balises titres
    */
   var $minHeaderLevel = 2;


   /**
    * indique le sens dans lequel il faut interpreter le nombre de signe de titre
    * true -> ! = titre , !! = sous titre, !!! = sous-sous-titre
    * false-> !!! = titre , !! = sous titre, ! = sous-sous-titre
    */
   var $headerOrder = true;
   var $escapeSpecialChars = true;
   var $inlineTagSeparator = '|';
   var $blocAttributeTag = '°°';

   var $checkWikiWord = false;
   var $checkWikiWordFunction = null;

}

///////////////////////////////////////////////////////////////////////////////
// déclarations des tags inlines
///////////////////////////////////////////////////////////////////////////////

class chuxhtml_strong extends WikiTagXhtml {
    var $name = 'strong';
    var $beginTag = '__';
    var $endTag = '__';
}

class chuxhtml_em extends WikiTagXhtml {
    var $name = 'em';
    var $beginTag = '_';
    var $endTag = '_';
}

class chuxhtml_code extends WikiTagXhtml {
    var $name = 'code';
    var $beginTag = '@@';
    var $endTag = '@@';
}

class chuxhtml_q extends WikiTagXhtml {
    var $name = 'q';
    var $beginTag = '\'\'';
    var $endTag = '\'\'';
    var $attribute = array('$$','lang','cite');
    var $separators = array('|');
}

class chuxhtml_acronym extends WikiTagXhtml {
    var $name = 'acronym';
    var $beginTag = '??';
    var $endTag = '??';
    var $attribute = array('$$','title');
    var $separators = array('|');
}

class chuxhtml_anchor extends WikiTagXhtml {
    var $name = 'anchor';
    var $beginTag = '~~';
    var $endTag = '~~';
    var $attribute = array('name');
    var $separators = array('|');
    function getContent(){
        return '<a name = "'.htmlspecialchars($this->wikiContentArr[0]).'"></a>';
    }
}

class chuxhtml_link extends WikiTagXhtml {
    var $name = 'a';
    var $beginTag = '[';
    var $endTag = ']';
    var $attribute = array('$$','href','hreflang','title');
    var $separators = array('|');
    function getContent(){
        $cntattr = count($this->attribute);
        $cnt = ($this->separatorCount + 1 > $cntattr?$cntattr:$this->separatorCount+1);
        if($cnt == 1 ){
            $contents = $this->wikiContentArr[0];
            $href = $contents;
            if(strpos($href,'javascript:') !== false) // for security reason
                $href = '#';
            if(strlen($contents) > 40)
                $contents = substr($contents,0,40).'(..)';
            return '<a href = "'.htmlspecialchars($href).'">'.htmlspecialchars($contents).'</a>';
        }else{
            if(strpos($this->wikiContentArr[1],'javascript:') !== false) // for security reason
                $this->wikiContentArr[1] = '#';
            return parent::getContent();
        }
    }
}

class chuxhtml_image extends WikiTagXhtml {
    var $name = 'image';
    var $beginTag = '((';
    var $endTag = '))';
    var $attribute = array('src','alt','align','longdesc');
    var $separators = array('|');

    function getContent(){
        $contents = $this->wikiContentArr;
        $cnt = count($contents);
        $attribut = '';
        if($cnt > 4) $cnt = 4;
        switch($cnt){
            case 4:
                $attribut .= ' longdesc = "'.$contents[3].'"';
            case 3:
                if($contents[2] == 'l' ||$contents[2] == 'L' || $contents[2] == 'g' || $contents[2] == 'G')
                    $attribut .= ' style = "float:left;"';
                elseif($contents[2] == 'r' ||$contents[2] == 'R' || $contents[2] == 'd' ||$contents[2] == 'D')
                    $attribut .= ' style = "float:right;"';
            case 2:
                $attribut .= ' alt = "'.$contents[1].'"';
            case 1:
            default:
                $attribut .= ' src = "'.$contents[0].'"';
                if($cnt == 1) $attribut .= ' alt = ""';
        }
        return '<img'.$attribut.'/>';
    }
}

///////////////////////////////////////////////////////////////////////////////
// déclaration des différents bloc wiki
///////////////////////////////////////////////////////////////////////////////

/**
 * traite les signes de types liste
 */
class chuxhtml_list extends WikiRendererBloc {

   var $_previousTag;
   var $_firstItem;
   var $_firstTagLen;
   var $type = 'list';
   var $regexp = "/^\s*([\*#-]+)(.*)/";

   function open(){
      $this->_previousTag = $this->_detectMatch[1];
      $this->_firstTagLen = strlen($this->_previousTag);
      $this->_firstItem = true;

      if(substr($this->_previousTag,-1,1) == '#')
         return "<ol>\n";
      else
         return "<ul>\n";
   }
   function close(){
      $t = $this->_previousTag;
      $str = '';

      for($i = strlen($t); $i >= $this->_firstTagLen; $i--){
          $str .= ($t{$i-1} == '#'?"</li></ol>\n":"</li></ul>\n");
      }
      return $str;
   }

   function getRenderedLine(){
      $t = $this->_previousTag;
      $d = strlen($t) - strlen($this->_detectMatch[1]);
      $str = '';

      if( $d > 0 ){ // on remonte d'un ou plusieurs cran dans la hierarchie...
         $l = strlen($this->_detectMatch[1]);
         for($i = strlen($t); $i>$l; $i--){
            $str .= ($t{$i-1} == '#'?"</li></ol>\n":"</li></ul>\n");
         }
         $str .= "</li>\n<li>";
         $this->_previousTag = substr($this->_previousTag,0,-$d); // pour être sur...

      }elseif( $d < 0 ){ // un niveau de plus
         $c = substr($this->_detectMatch[1],-1,1);
         $this->_previousTag .= $c;
         $str = ($c == '#'?"<ol><li>":"<ul><li>");

      }else{
         $str = ($this->_firstItem ? '<li>':"</li>\n<li>");
      }
      $this->_firstItem = false;
      return $str.$this->_renderInlineTag($this->_detectMatch[2]);

   }
}

/**
 * traite les signes de types hr
 */
class chuxhtml_hr extends WikiRendererBloc {

   var $type = 'hr';
   var $regexp='/^\s*={4,} *$/';
   var $_closeNow = true;

   function getRenderedLine(){
      return '<hr />';
   }

}

/**
 * traite les signes de types titre
 */
class chuxhtml_title extends WikiRendererBloc {
   var $type = 'title';
   var $regexp = "/^\s*(\!{1,3})(.*)/";
   var $_closeNow = true;

   var $_minlevel = 2; // starts with h2
   /**
    * indique le sens dans lequel il faut interpreter le nombre de signe de titre
    * true -> ! = titre , !! = sous titre, !!! = sous-sous-titre
    * false-> !!! = titre , !! = sous titre, ! = sous-sous-titre
    */
   var $_order = true;

   function getRenderedLine(){
      if($this->_order)
         $hx= $this->_minlevel + strlen($this->_detectMatch[1])-1;
      else
         $hx= $this->_minlevel + 3-strlen($this->_detectMatch[1]);
      return '<h'.$hx.'>'.$this->_renderInlineTag($this->_detectMatch[2]).'</h'.$hx.'>';
   }
}

/**
 * traite les signes de type paragraphe
 */
class chuxhtml_p extends WikiRendererBloc {
   var $type = 'p';
   var $_openTag = '<p>';
   var $_closeTag = '</p>';

   function detect($string){
      if($string == '') return false;
      if(preg_match("/^\s*[\*#\-\!\| \t>;< = ].*/",$string)) return false;
      $this->_detectMatch = array($string,$string);
      return true;
   }
}

/**
 * traite les signes de types pre (pour afficher du code..)
 */
class chuxhtml_pre extends WikiRendererBloc {

    var $type = 'pre';
    var $_openTag = '<pre>';
    var $_closeTag = '</pre>';
    var $isOpen = false;

    function getRenderedLine(){
        return htmlspecialchars($this->_detectMatch);
    }

    function detect($string){
        if($this->isOpen){
            if(preg_match("/(.*)}}}\s*$/",$string,$m)){
                $this->_detectMatch = $m[1];
                $this->isOpen = false;
            }else{
                $this->_detectMatch = $string;
            }
            return true;

        }else{
            if(preg_match("/^\s*{{{(.*)/",$string,$m)){
                $this->isOpen = true;
                $this->_detectMatch = $m[1];
                return true;
            }else{
                return false;
            }
        }
    }
}


/**
 * traite les signes de type blockquote
 */
class chuxhtml_blockquote extends WikiRendererBloc {
   var $type = 'bq';
   var $regexp = "/^\s*(\>+)(.*)/";

   function open(){
      $this->_previousTag = $this->_detectMatch[1];
      $this->_firstTagLen = strlen($this->_previousTag);
      $this->_firstLine = true;
      return str_repeat('<blockquote>',$this->_firstTagLen).'<p>';
   }

   function close(){
      return '</p>'.str_repeat('</blockquote>',strlen($this->_previousTag));
   }


   function getRenderedLine(){

      $d = strlen($this->_previousTag) - strlen($this->_detectMatch[1]);
      $str = '';

      if( $d > 0 ){ // on remonte d'un cran dans la hierarchie...
         $str = '</p>'.str_repeat('</blockquote>',$d).'<p>';
         $this->_previousTag = $this->_detectMatch[1];
      }elseif( $d < 0 ){ // un niveau de plus
         $this->_previousTag = $this->_detectMatch[1];
         $str = '</p>'.str_repeat('<blockquote>',-$d).'<p>';
      }else{
         if($this->_firstLine)
            $this->_firstLine = false;
         else
            $str = '<br />';
      }
      return $str.$this->_renderInlineTag($this->_detectMatch[2]);
   }
}

/**
 * traite les signes de type définitions
 */
class chuxhtml_definition extends WikiRendererBloc {

   var $type = 'dfn';
   var $regexp = '/^;(((\\\\:)|[^\\\\:])*):(.*)$/';
   var $_openTag = '<dl>';
   var $_closeTag = '</dl>';

   function getRenderedLine(){
      $dt = $this->_renderInlineTag($this->_detectMatch[1]);
      $dd = $this->_renderInlineTag($this->_detectMatch[4]);
      return "<dt>$dt</dt>\n<dd>$dd</dd>\n";
   }
}

?>