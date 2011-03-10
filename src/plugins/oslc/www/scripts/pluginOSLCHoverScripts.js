/**
 * FusionForge OSLC plugin.
 *
 * Copyright 2011, Sabri LABBENE - INSTITUT TELECOM
 * http://fusionforge.org
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
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
var oslcPrefix;
var rdfPrefix;
var divId;

function getPrefix(xmlDoc,ns) {
	var attrs = xmlDoc.documentElement.attributes;
	for( var i=0; i<attrs.length; i++ ) {
		if( attrs[i].nodeValue == ns ) {
			var name = attrs[i].nodeName;
			var pos = name.indexOf(":");
			return ( name.substring(pos+1) );
		}
	}
}
function hover(uri,id){
	divId = id;
	var req = new XMLHttpRequest();  
	req.open('GET', uri, true);  
	req.setRequestHeader('Accept', 'application/x-oslc-compact+xml');
	req.onreadystatechange = function (aEvt) {  
		if (req.readyState == 4) {  
			if(req.status == 200) {  
				//debugger;
				var xmlDoc = req.responseXML;
				oslcPrefix = getPrefix(xmlDoc, 'http://open-services.net/ns/core#');
				rdfPrefix = getPrefix(xmlDoc, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
				var smPreview = xmlDoc.documentElement.getElementsByTagName(oslcPrefix + ':smallPreview')[0];
				if( smPreview ) {
					var Preview = smPreview.getElementsByTagName(oslcPrefix + ':Preview')[0];
					if(Preview){
						var oslcDoc = Preview.getElementsByTagName(oslcPrefix + ':document')[0];
						if( oslcDoc ) {
							var url = oslcDoc.getAttribute(rdfPrefix + ':ressource');
							if( oslcDoc ) {
								var div = document.getElementById(divId);
								if( div ) {
									var elmHintWidth = smPreview.getElementsByTagName(oslcPrefix + ':hintWidth')[0];
									var divWidth = elmHintWidth.textContent;
									var elmHintHeight = smPreview.getElementsByTagName(oslcPrefix + ':hintHeight')[0];
									var divHeight = elmHintHeight.textContent;
									div.innerHTML = '<'+'object type="text/html" data="'+url+'" width="'+divWidth+'" height="'+divHeight+'" style="background-color:#ffffee; border-style:solid;border-width:2px;"><\/object>';
								}
							}
						}
					}
				}   
			}
		}
	};  
	req.send(null); 
}
function closeHover() { 
	if( divId ) {
		var elmDiv = document.getElementById(divId);
		if( elmDiv ) {
			elmDiv.innerHTML = '';
			elmDiv.width = null;
			elmDiv.height = null;
		}
	}
}