// Ajax Javascript support functions, based on moacdropdown XmlHttp
// Written from scratch by Reini Urban
// $Id: ajax.js 7142 2009-09-17 10:50:20Z rurban $

function showHide( id ) {
    this.init( id )
}

showHide.prototype.onXmlHttpLoad = function( ) {
    if( this.hXMLHttp.readyState == 4 ) {
        var hError = this.hXMLHttp.parseError;
        var img = document.getElementById(this.id+'-img');
        if( hError && hError.errorCode != 0 ) {
            alert( hError.reason );
        } else {
            // insert external, same-domain XML tree into id-body as HTML
            // we get this from any page&format=xml
            var body = document.getElementById(this.id+'-body');
            var newbody = this.hXMLHttp.responseXML;
            if (newbody != null) {
                // DOM quirks with text/xml and DOCTYPE xhtml
                // msie: newbody = document, newbody.firstChild.nodeName = xml
                if (newbody.firstChild && newbody.firstChild.nodeName == 'xml')
                    newbody = newbody.firstChild.nextSibling.nextSibling;
                // gecko + chrome no xml: skip only firstChild = DOCTYPE html
                if (newbody.firstChild && newbody.firstChild.nodeName == 'html')
                    newbody = newbody.childNodes[1];
                if (newbody == null) {
                    alert("showHideDone "+this.id+"\nno xml children from "+this.hXMLHttp.responseText);
                }
                // We cannot just insert the responseXML into the DOM.
                // well gecko can, but the others not.
                // So convert the XML tree it on the fly into HTML nodes.
                // I never saw this before. I needed that, so I think I
                // invented that sort of rich mashup.
                var hContainer = CreateHtmlFromXml(newbody);
                hContainer.className = 'wikitext';
                body.appendChild( hContainer );
                body.style.display = 'block';
            } else {
                alert("showHideDone "+this.id+"\nerror no xml from "+this.hXMLHttp.responseText);
            }
        }
        if (img) {
            if (!folderArrowPath) folderArrowPath = stylepath + 'images/';
            img.src = folderArrowPath + '/folderArrowOpen.png';
        }
    }
}

showHide.prototype.init = function (id) {
    this.id = id;
    this.hXMLHttp = XmlHttp.create()
    var hAC = this
    this.hXMLHttp.onreadystatechange = function() { hAC.onXmlHttpLoad() }
}

var cShowHide;

/* recursive xml => html converter. 
   This might need a attribute type checker in a bad world. 
   e.g. disable all on* events */
function CreateHtmlFromXml (xml) {
    if (xml == null) {
        return document.createElement('xml');
    }
    var xmltype = xml.nodeName;
    var html;
    if (xmltype == '#text') {
        html = document.createTextNode( xml.nodeValue );
        html.nodeValue = xml.nodeValue;
        if (xml.attributes && (xml.attributes != null))
            for (var i=0; i < xml.attributes.length; i++) {
                html.setAttribute( xml.attributes[i].name, xml.attributes[i].value );
            }
    } else {
        html = document.createElement( xmltype );
        if (xml.nodeValue)
            html.nodeValue = xml.nodeValue;
        if (xml.attributes && (xml.attributes != null))
            for (var i=0; i < xml.attributes.length; i++) {
                html.setAttribute( xml.attributes[i].name, xml.attributes[i].value );
            }
        if (xml.hasChildNodes())
            for (var i=0; i < xml.childNodes.length; i++) {
                html.appendChild( CreateHtmlFromXml(xml.childNodes[i]) );
            }
    }
    return html;
}

// if body is empty, load page in background into id+"-body" and show/hide id
function showHideAsync(uri, id) {
    var body = document.getElementById(id+'-body');
    if (!body) {
        alert("Error: id="+id+'-body'+" missing.");
        return;
    }
    if (body.hasChildNodes()) {
        //alert("showHideAsync "+uri+" "+id+"\nalready loaded");
        showHideFolder(id);
    }
    else {
        if (!folderArrowPath) folderArrowPath = stylepath + 'images/';
        //alert("showHideAsync "+uri+" "+id+"\nloading...");
        var img = document.getElementById(id+'-img');
        if (img)
            img.src = folderArrowPath + '/folderArrowLoading.gif';
        cShowHide = new showHide(id)
        cShowHide.hXMLHttp.open( 'GET', uri, true )
        cShowHide.hXMLHttp.send( null )
    }
}

function showHideDone(id) {
    // insert tree into id-body
    var body = document.getElementById(id+'-body');
    body.parentNode.replaceChild(cShowHide.hXMLHttp.responseText, body);
    alert("showHideDone "+id+"\ngot "+cShowHide.hXMLHttp.responseText);
    showHideFolder(id);
}

// hide after 0.4 secs
function showHideDelayed(id) {
    window.setTimeout("doshowHide("+id+")",400);
}

function doshowHide(id) {
    document.getElementById(id).style.display = "none";
    var highlight = document.getElementById("LSHighlight");
    if (highlight) {
        highlight.removeAttribute("id");
    }
}
