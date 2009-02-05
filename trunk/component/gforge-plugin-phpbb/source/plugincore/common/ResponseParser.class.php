<?php
/**
 * PhpBB plugin
 * 
 * xml parser used to retieve data dent by phpbb 
 *
 * @author Panneer CANABADY <panneerselvam.canabady@bull.net>
 * @version 1.0
 * @package PluginPhpBB
 */
class ResponseParser {

    var $parser;
    var $error_code;
    var $error_string;
    var $current_line;
    var $current_column;
    var $data = array();
    var $attributes = array();
    var $debug = false;

    var $rules = array();
    var $bookmarks = array();
    var $categories = array();
    function parse($data)
    {
        $returned =false ;
        $data=stripslashes(trim($data));
        if ($this->debug)
        {
            syslog(LOG_INFO,">> response parse($data)");
        }

        $this->parser = xml_parser_create('UTF-8');
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
        xml_set_element_handler($this->parser, 'tag_open', 'tag_close');
        xml_set_character_data_handler($this->parser, 'cdata');
        if (!xml_parse($this->parser, $data))
        {
            $this->data = array();
            $this->error_code = xml_get_error_code($this->parser);
            $this->error_string = xml_error_string($this->error_code);
            $this->current_line = xml_get_current_line_number($this->parser);
            $this->current_column = xml_get_current_column_number($this->parser);
        }
        xml_parser_free($this->parser);

        if ( !( isset( $this->error_code ) ) )
        {
            $returned=true;
        }
        else
        {
            /*
             *treatment :  xml parser error
             */
            syslog(LOG_ERR,"error to parse the response ");
            syslog(LOG_ERR,"code  = ".$this->error_code);
            syslog(LOG_ERR,"error   = ".$this->error_string);
            syslog(LOG_ERR,"current_line  = ".$this->current_line);
            syslog(LOG_ERR,"current_column  = ".$this->current_column);
        }

        if ($this->debug)
        {
            syslog(LOG_INFO,"<<responseparse()");
        }

        return $returned;
    }

    function tag_open($parser, $tag, $attribs)
    {
        if ($this->debug)
        {
            syslog(LOG_INFO,">>ResponseParser tag_open( $tag)");
        }
         
        if ($tag == "RULE"){
            
            array_push($this->rules,$attribs);
        }else if($tag == "BOOKMARK"){
            
            array_push($this->bookmarks,$attribs);            
        }else if($tag == "CATEGORY"){
            
            array_push($this->categories,$attribs);        
        }else{
            
            $this->attributes[$tag]= $attribs;
        }
        

        if ($this->debug)
        {
            if (count($attribs)) {
                foreach ($attribs as $k => $v)
                {
                    syslog(LOG_INFO,">>ResponseParser tag_open( attr key=$k val=$v");
                }
            }
        }
    }

    function cdata($parser, $cdata)
    {
        if ($this->debug)
        {
            if ((strlen(trim($cdata)))>0)
            {
                syslog(LOG_INFO,">>ResponseParser element = $cdata.");
            }
        }
    }

    function tag_close($parser, $tag)
    {
        if ($this->debug)
        {
            syslog(LOG_INFO,">>ResponseParser tag_close = $tag.");
        }
    }

}

?>
