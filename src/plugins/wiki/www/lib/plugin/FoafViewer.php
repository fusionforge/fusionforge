<?php
// $Id: FoafViewer.php 8071 2011-05-18 14:56:14Z vargenau $
/*
 * Copyright (C) 2004 $ThePhpWikiProgrammingTeam
 *
 * This file is part of PhpWiki.
 *
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
* Basic FoafViewPlugin for PHPWiki.
*
* Please note; this is <em>heavily</em> based on an example file distributed with XML_FOAF 0.2
* HTTP GET vars:
* <kbd>foaf=uri</kbd> - Used to determine the URI of a FOAF file.
* <kbd>original=true</kbd> - Simply dumps contents of $foaf
*
* @author Daniel O'Connor <http://ahsonline.com.au/dod/FOAF.rdf>
* @author Davey Shafik <http://pear.php.net/user/davey>
* @date 2004-06-07
* @version 0.0.2
* @bug XML_FOAF 0.2 has problems with named RDF nodes (ie, http://www.ahsonline.com.au/dod/FOAF.rdf).
*      Davey knows, will be fixing this soon.
* @todo "Friends" component
* @todo Named URLs (DC metadata)
* @todo "View in FOAFNAUT/foafexplorer/other"
* @bug Full name !available incorrectly handled.
*/

/**
 * FoafViewer:  Parse an RDF FOAF file and extract information to render as HTML
 * usage:   &lt;?plugin FoafViewer foaf=http://www.ahsonline.com.au/dod/rawksuga.rdf original=true?&gt;
 * author:  Daniel O'Connor <http://www.ahsonline.com.au/dod/FOAF.rdf>
 *
 * phpwiki version based on version 0.0.2 by Daniel O'Connor
 *
 * TODO:
 *  - use a template.
 *  - use the phpwiki internal user foaf data (stored by a UserPreferences extension)
 *  - fix the pear FOAF Parser or we'll write our own (based on our XmlParser)
 */
class WikiPlugin_FoafViewer
extends WikiPlugin
{
    // The handler is handled okay. The only problem is that it still
    // throws a fatal.
    function _error_handler($error) {
        if (strstr($error->errstr,"Failed opening required 'XML/FOAF/Parser.php'"))
            return true;
        elseif (strstr($error->errstr,'No such file or directory'))
            return true;
        return false;
    }

    function getName() {
        return _("FoafViewer");
    }

    function getDescription() {
        return _("Parse an RDF FOAF file and extract information to render as HTML");
    }

    function getDefaultArguments() {
        return array( 'foaf'     => false, // the URI to parse
                      //'userid'   => false,
                      'original' => false
                      );
    }

    function run($dbi, $argstr, &$request, $basepage) {

        /* ignore fatal on loading */
        /*
        global $ErrorManager;
        $ErrorManager->pushErrorHandler(new WikiMethodCb($this,'_error_handler'));
        */
        // Require the XML_FOAF_Parser class. This is a pear library not included with phpwiki.
        // see doc/README.foaf
        if (findFile('XML/FOAF/Parser.php','missing_ok'))
            require_once 'XML/FOAF/Parser.php';
        //$ErrorManager->popErrorHandler();
        if (!class_exists('XML_FOAF_Parser'))
            return $this->error(_("required pear library XML/FOAF/Parser.php not found in include_path"));

        extract($this->getArgs($argstr, $request));
        // Get our FOAF File from the foaf plugin argument or $_GET['foaf']
        if (empty($foaf)) $foaf = $request->getArg('foaf');
        $chooser = HTML::form(array('method'=>'get','action'=>$request->getURLtoSelf()),
                              HTML::h4(_("FOAF File URI")),
                              HTML::input(array('id'=>'foaf','name'=>'foaf','type'=>'text','size'=>'80','value'=>$foaf)),
                              HTML::br(),
                              //HTML::p("Pretty HTML"),
                              HTML::input(array('id'=>'pretty','name'=>'pretty','type'=>'radio','checked'=>'checked'),_("Pretty HTML")),
                              //HTML::p("Original URL (Redirect)"),
                              HTML::input(array('id'=>'original','name'=>'original','type'=>'radio'),_("Original URL (Redirect)")),
                              HTML::br(),
                              HTML::input(array('type'=>'submit','value'=>_("Parse FOAF")))
                              //HTML::label(array('for'=>'foaf'),"FOAF File URI"),
                              );
        if (empty($foaf)) {
            return $chooser;
        }
        else {
            //Error Checking
            if (substr($foaf,0,7) != "http://") {
                return $this->error(_("foaf must be a URI starting with http://"));
            }
            // Start of output
               if (!empty($original)) {
                $request->redirect($foaf);
            }
            else {
                $foaffile = url_get_contents($foaf);
                if (!$foaffile) {
                    //TODO: get errormsg
                    return HTML(HTML::p("Resource isn't available: Something went wrong, probably a 404!"));
                }
                // Create new Parser object
                $parser = new XML_FOAF_Parser;
                // Parser FOAF into $foaffile
                $parser->parseFromMem($foaffile);
                $a = $parser->toArray();

                $html = HTML(HTML::h1(@$a[0]["name"]),
                            HTML::table(
                                        HTML::thead(),
                                        HTML::tbody(
                                                    @$a[0]["title"] ?
                                                        HTML::tr(HTML::td(_("Title")),
                                                                 HTML::td($a[0]["title"])) : null,
                                                    (@$a[0]["homepage"][0]) ?
                                                        $this->iterateHTML($a[0],"homepage",$a["dc"]) : null,
                                                    (@$a[0]["weblog"][0]) ?
                                                        $this->iterateHTML($a[0],"weblog",$a["dc"]) : null,
                                                    //This seems broken?
                                                    /*
                                                     HTML::tr(HTML::td("Full Name"),
                                                                       (@$a[0]["name"][0]) ?
                                                                       HTML::td(@$a[0]["name"]) :
                                                                       (@$a[0]["firstname"][0] && @$a[0]["surname"][0]) ?
                                                                       HTML::td(@$a[0]["firstname"][0] . " " . @$a[0]["surname"][0])
                                                                       : null
                                                    */
                                                    HTML::tr(HTML::td("Full Name"),
                                                             (@$a[0]["name"][0]) ?
                                                             HTML::td(@$a[0]["name"]) : null
                                                             ),
                                                    (@$a[0]["nick"][0]) ?
                                                    $this->iterateHTML($a[0],"nick",$a["dc"])
                                                    : null,
                                                    (@$a[0]["mboxsha1sum"][0]) ?
                                                    $this->iterateHTML($a[0],"mboxsha1sum",$a["dc"])
                                                    : null,
                                                    (@$a[0]["depiction"][0]) ?
                                                    $this->iterateHTML($a[0],"depiction",$a["dc"])
                                                    : null,
                                                    (@$a[0]["seealso"][0]) ?
                                                    $this->iterateHTML($a[0],"seealso",$a["dc"])
                                                    : null,
                                                    HTML::tr(HTML::td("Source"),
                                                             HTML::td(
                                                                      HTML::a(array('href'=>@$foaf),"RDF")
                                                                      )
                                                             )
                                                    )
                                        )
                             );
                if (DEBUG) {
                    $html->pushContent(HTML::hr(),$chooser);
                }
                return $html;
            }
        }
    }

    /**
     * Renders array elements as HTML. May be used recursively.
     *
     * @param $array Source array
     * @param $index Element Index to use.
     * @todo Make sure it can look more than 1 layer deep
     * @todo Pass in dublincore metadata
     */
    function iterateHTML($array,$index,$dc=NULL) {
        for ($i=0;$i<count($array[$index]);$i++) {
            //Cater for different types
            $string = $array[$index][$i];

            if ($index == "mboxsha1sum") {
                $string = '<a href="http://beta.plink.org/profile/' . $array[$index][$i] . '">'
                    .'<img src="http://beta.plink.org/images/plink.png" alt="Plink - ' . $array[$index][$i] . '" /></a>';
            }
            else if ($index == "depiction") {
                $string = '<img src="' . $array[$index][$i] . '" />';
            }
            else if ((substr($array[$index][$i],0,7) == "http://") || (substr($array[$index][$i],0,7) == "mailto:")) {
                $string = '<a href="' . $array[$index][$i] . '"';

                if (@$dc["description"][$array[$index][$i]]) {
                    $string .= ' title="' . $dc["description"][$array[$index][$i]] . '"';
                }
                $string .= '>';
                if (@$dc["title"][$array[$index][$i]]) {
                    $string .=  $dc["title"][$array[$index][$i]];
                }
                else {
                    $string .= $array[$index][$i];
                }
                $string .= '</a>';
            }
            @$html .= "<tr><td>" . $index . "</td><td>" . $string . "</td></tr>";
        }

        return HTML::raw($html);
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
