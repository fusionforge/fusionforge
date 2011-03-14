<?php

/**
 * OpendocumentPackage Class
 *
 * Copyright (c) 2011 Olivier Berger & Institut Telecom
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

// We hopefully should be able to reuse some bits of OpenDocument to support OpenDocument Packages and not only "office" docs

// Depends on pear OpenDocument (0.2.0) package : http://pear.php.net/package/OpenDocument
require_once 'OpenDocument.php';
require_once 'OpenDocument/Document.php';
require_once 'OpenDocument/Storage/Zip.php';

class OpenDocument_ManifestFileEntry {
	protected $fullpath;
	protected $mediatype;	
	public function OpenDocument_ManifestFileEntry($fullpath, $mediatype) {
		$this->fullpath = $fullpath;
		$this->mediatype = $mediatype;
	}
	public function getFullPath() {
		return $this->fullpath;
	}
	public function getMediaType() {
		return $this->mediatype;
	}
}

class OpenDocumentPackage_Manifest extends OpenDocument_Manifest {
	
	protected $fileentries;
	
    public function __construct()
    {
        $this->fileentries = array();
    }
    public function load($dom)
    {
    	$this->dom = $dom;
    	$this->fileroot = $this->dom->documentElement;

    	foreach ($dom->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:manifest:1.0', 'file-entry') as $element) {
    		$attr = $element->getAttributeNodeNS('urn:oasis:names:tc:opendocument:xmlns:manifest:1.0', 'full-path');
    		$fullpath = $attr->value;
    		$attr = $element->getAttributeNodeNS('urn:oasis:names:tc:opendocument:xmlns:manifest:1.0', 'media-type');
    		$mediatype = $attr->value;
    		$this->fileentries[] = new OpenDocument_ManifestFileEntry($fullpath, $mediatype);
		}
    }

    public function getFilesByMediaType($mediatype) {
    	$result = array();
    	foreach ($this->fileentries as $entry) {
    		if ($entry->getMediaType() == $mediatype) {
    			$result[] = $entry->getFullPath();
    		}
    	}
    	return $result;
    }
}

/**
 * Minimal implementation of a document without the Office aspects
 *
 * Needed by OpendocumentPackage
 *
 * @author Olivier Berger
 *
 * TODO : should be the other way around : OpenDocument_Document extends OpenDocument_Other
 */
class OpenDocument_Other extends OpenDocument_Document {
	
	/**
     * DOMDocument for meta information
     *
     * @var DOMDocument
     */
    protected $manifestDOM;
    
    protected $manifest;
	
	public function open(OpenDocument_Storage $storage)
	{
        $this->storage = $storage;
        
        $this->manifestDOM   = $storage->getManifestDom();
        //$this->metaXPath = new DOMXPath($this->metaDOM);
/*
        print_r($this->metaDOM);
        return;
        */
        $this->manifest = new OpenDocumentPackage_Manifest();
        $this->manifest->load($this->manifestDOM);
        
        //print_r($manifest);
        //echo self::toText($this->manifestDOM);
        /*$childrenNodes = $this->metaDOM->childNodes;
        foreach ($childrenNodes as $child) {
        	//print_r('child : '. $child->nodeName);
        	
        	
            }

        }
            */
      }
	static function toText($obj)
	{
	    $text = '';
	    if ($obj->hasChildNodes()) {
	    	foreach ($obj->childNodes as $child) {
				$text .= self::toText($child);
	    	}
	    } else {
	    	switch (get_class($obj)) {
        	case 'DOMText':
            	$text .= ' leaf text : '. $obj->wholeText;
	            break;
	        case 'DOMElement':
            	$text .= ' element attributes: ';
            	/*foreach ($obj->attributes as $attr) {
            		$text .= 
            	}*/
            	//$text .= print_r($obj->attributes, TRUE);
            	$attributes = $obj->attributes;
            	for ($i = 0; $i < $attributes->length; $i ++) {
                	$name  = $attributes->item($i)->name;
                	$value = $attributes->item($i)->value;
            		$text .= ' '. $name .':'. $value;
            	}
	            break;
    	    default:
        	    $text .= 'unknown element '.get_class($obj);
        	    break;
        	}
	    }
	    return $text;
	}
	
	public function getFileNamesByMediaType($mediatype) {
		return $this->manifest->getFilesByMediaType($mediatype);
	}
	
	public function getFileContents($filename) {
		return $this->storage->loadContentsFromZip($filename);
	}
}

/**
 * Minimal implementation of a Storage for OpenDocument Packages
 * 
 * @author Olivier Berger
 *
 */
class OpenDocumentPackage_Storage_Zip extends OpenDocument_Storage_Zip {

    /**
     * DOM document containing the manifest data
     *
     * @var DOMDocument
     */
    protected $manifestDom = null;
	
	protected function loadFile($file)
    {
        $this->zip = new ZipArchive();
        
        if ($this->zip->open($file) !== true) {
            throw new OpenDocument_Exception('Cannot open ZIP file: ' . $file);
        }
        //$this->contentDom  = $this->loadDomFromZip($this->zip, 'content.xml');
        $this->manifestDom     = $this->loadDomFromZip($this->zip, 'META-INF/manifest.xml');
        //        $this->settingsDom = $this->loadDomFromZip($this->zip, 'settings.xml');
        //$this->stylesDom   = $this->loadDomFromZip($this->zip, 'styles.xml');
        //FIXME: what to do with embedded files (e.g. images)?
    }

    /**
     * Returns the DOM object containing the meta data.
     *
     * @return DOMDocument
     */
    public function getManifestDom()
    {
        return $this->manifestDom;
    }
    
	public function loadContentsFromZip($filepath)
    {
        $index = $this->zip->locateName($filepath);
        if ($index === false) {
            throw new OpenDocument_Exception('File not found in zip: ' . $filepath);
        }

        return $this->zip->getFromIndex($index);
    }
}

/**
 * OpenDocument Package (according to specs 1.2 - draft 3, part 3)
 * @author Olivier Berger
 *
 */
class OpendocumentPackage extends OpenDocument {
	
	// "fix" the open which on
	public static function open($file)
    {
        //FIXME: detect correct storage
        $storage = new OpenDocumentPackage_Storage_Zip();
        $storage->open($file);

        $mimetype = $storage->getMimeType();
		echo "MIME : ".$mimetype;
        /*
        switch ($mimetype) {
        case 'application/vnd.oasis.opendocument.text':
            $class = 'OpenDocument_Document_Text';
            break;
        default:
            throw new OpenDocument_Exception(
                'Unsupported MIME type ' . $mimetype
            );
            break;
        }

        self::includeClassFile($class);
		*/
        return new OpenDocument_Other($storage);
    }
	
}

//$odt = OpendocumentPackage::open('test.zip');

//echo "mime type: ". $odt->getMimeType();
/*
function toText($obj)
{
    $text = '';
    foreach ($obj->getChildren() as $child) {
        switch (get_class($child)) {
        case 'OpenDocument_Element_Text':
            $text .= $child->text;
            break;
        case 'OpenDocument_Element_Paragraph':
            $text .= toText($child);
            $text .= "\n";
            break;
        case 'OpenDocument_Element_Span':
            $text .= '<span>';
            $text .= toText($child);
            $text .= '</span>';
            break;
        case 'OpenDocument_Element_Heading':
            $text .= '<h' . $child->level . '>';
            $text .= toText($child);
            $text .= '</h' . $child->level . '>';
            break;
        case 'OpenDocument_Element_Hyperlink':
            $text .= '<a href="' . $child->location . '" target="' . $child->target . '">';
            $text .= toText($child);
            $text .= '</a>';
            break;
        default:
            $text .= '<del>unknown element</del>';
        }
    }
    return $text;
}
*/
/*
print_r($odt);

exit(0);
//loop throught document children
foreach ($odt->getChildren() as $child) {
    //strip headings
   
	echo toText($child);
	//print_r($child);
	//echo $child->text;
}
*/