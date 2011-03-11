/**
 * This source code was taken from SORI (Simple OSLC Reference Implementation)
 * at http://sourceforge.net/apps/mediawiki/oslc-tools/index.php?title=SORI_Overview
 *
 *  Copyright 2010 IBM 
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *  
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