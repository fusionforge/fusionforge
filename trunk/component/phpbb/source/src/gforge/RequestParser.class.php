<?php

class RequestParser {


    var $parser;
    var $error_code;
    var $error_string;
    var $current_line;
    var $current_column;
    var $data = array();
    var $attributes = array();
    var $debug = false;

    function parse($data)
    {
        $data=stripslashes(trim($data));
        if ($this->debug)
        {
            syslog(LOG_INFO,">>RequestParser parse($data)");
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

        if ($this->debug)
        {
            syslog(LOG_INFO,"<<RequestParser parse()");
        }
    }

    function tag_open($parser, $tag, $attribs)
    {

        if ($this->debug)
        {
            syslog(LOG_INFO,">>RequestParser tag_open( $tag)");
        }

        if($tag !='PHPBB' && $tag !='REQUEST'){
                  array_push($this->attributes,array($tag,$attribs) ); 
        }


        if ($this->debug)
        {
            if (count($attribs)) {
                foreach ($attribs as $k => $v)
                {
                    syslog(LOG_INFO,">>RequestParser tag_open( attr key=$k val=$v");
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
                syslog(LOG_INFO,">>RequestParser element = $cdata.");
            }
        }
    }

    function tag_close($parser, $tag)
    {
        if ($this->debug)
        {
            syslog(LOG_INFO,">>RequestParser tag_close = $tag.");
        }
    }
}

?>
