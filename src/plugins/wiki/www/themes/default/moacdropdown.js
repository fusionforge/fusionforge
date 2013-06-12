
function cBrowser(){var userAgent=navigator.userAgent.toLowerCase()
this.version=parseInt(navigator.appVersion)
this.subVersion=parseFloat(navigator.appVersion)
this.ns=((userAgent.indexOf('mozilla')!=-1)&&((userAgent.indexOf('spoofer')==-1)&&(userAgent.indexOf('compatible')==-1)))
this.ns2=(this.ns&&(this.version==2))
this.ns3=(this.ns&&(this.version==3))
this.ns4b=(this.ns&&(this.subVersion<4.04))
this.ns4=(this.ns&&(this.version==4))
this.ns5=(this.ns&&(this.version==5))
this.ie=(userAgent.indexOf('msie')!=-1)
this.ie3=(this.ie&&(this.version==2))
this.ie4=(this.ie&&(this.version==4)&&(userAgent.indexOf('msie 4.')!=-1))
this.ie5=(this.ie&&(this.version==4)&&(userAgent.indexOf('msie 5.0')!=-1))
this.ie55=(this.ie&&(this.version==4)&&(userAgent.indexOf('msie 5.5')!=-1))
this.ie6=(this.ie&&(this.version==4)&&(userAgent.indexOf('msie 6.0')!=-1))
this.op3=(userAgent.indexOf('opera')!=-1)
this.win=(userAgent.indexOf('win')!=-1)
this.mac=(userAgent.indexOf('mac')!=-1)
this.unix=(userAgent.indexOf('x11')!=-1)
this.name=navigator.appName
this.dom=this.ns5||this.ie5||this.ie55||this.ie6}
var bw=new cBrowser()
cDomEvent={e:null,type:'',button:0,key:0,x:0,y:0,pagex:0,pagey:0,target:null,from:null,to:null}
cDomEvent.init=function(e)
{if(window.event)e=window.event
this.e=e
this.type=e.type
this.button=(e.which)?e.which:e.button
this.key=(e.which)?e.which:e.keyCode
this.target=(e.srcElement)?e.srcElement:e.originalTarget
this.currentTarget=(e.currentTarget)?e.currentTarget:e.srcElement
this.from=(e.originalTarget)?e.originalTarget:(e.fromElement)?e.fromElement:null
this.to=(e.currentTarget)?e.currentTarget:(e.toElement)?e.toElement:null
this.x=(e.layerX)?e.layerX:(e.offsetX)?e.offsetX:null
this.y=(e.layerY)?e.layerY:(e.offsetY)?e.offsetY:null
this.screenX=e.screenX
this.screenY=e.screenY
this.pageX=(e.pageX)?e.pageX:e.x+document.body.scrollLeft
this.pageY=(e.pageY)?e.pageY:e.y+document.body.scrollTop}
cDomEvent.getEvent=function(e)
{if(window.event)e=window.event
return{e:e,type:e.type,button:(e.which)?e.which:e.button,key:(e.which)?e.which:e.keyCode,target:(e.srcElement)?e.srcElement:e.originalTarget,currentTarget:(e.currentTarget)?e.currentTarget:e.srcElement,from:(e.originalTarget)?e.originalTarget:(e.fromElement)?e.fromElement:null,to:(e.currentTarget)?e.currentTarget:(e.toElement)?e.toElement:null,x:(e.layerX)?e.layerX:(e.offsetX)?e.offsetX:null,y:(e.layerY)?e.layerY:(e.offsetY)?e.offsetY:null,screenX:e.screenX,screenY:e.screenY,pageX:(e.pageX)?e.pageX:(e.clientX+(document.documentElement.scrollLeft||document.body.scrollLeft)),pageY:(e.pageY)?e.pageY:(e.clientY+(document.documentElement.scrollTop||document.body.scrollTop))}}
cDomEvent.cancelEvent=function(e)
{if(e.preventDefault)
{e.preventDefault()}
e.returnValue=false
e.cancelBubble=true
return false}
cDomEvent.addEvent=function(hElement,sEvent,handler,bCapture)
{if(hElement.addEventListener)
{hElement.addEventListener(sEvent,handler,bCapture)
return true}
else if(hElement.attachEvent)
{return hElement.attachEvent('on'+sEvent,handler)}
else if(document.all||hElement.captureEvents)
{if(hElement.captureEvents)eval('hElement.captureEvents( Event.'+sEvent.toUpperCase()+' )')
eval('hElement.on'+sEvent+' = '+handler)}
else
{alert('Not implemented yet!')}}
cDomEvent.encapsulateEvent=function(hHandler)
{return function(hEvent)
{hEvent=cDomEvent.getEvent(hEvent)
hHandler.call(hEvent.target,hEvent.e)}}
cDomEvent.addEvent2=function(hElement,sEvent,handler,bCapture)
{if(hElement)
{if(hElement.addEventListener)
{hElement.addEventListener(sEvent,cDomEvent.encapsulateEvent(handler),bCapture)
return true}
else if(hElement.attachEvent)
{return hElement.attachEvent('on'+sEvent,cDomEvent.encapsulateEvent(handler))}
else
{alert('Not implemented yet!')}}
else
{}}
cDomEvent.addCustomEvent2=function(hElement,sEvent,handler)
{if(hElement)
{hElement[sEvent]=handler}
else
{}}
cDomEvent.removeEvent=function(hElement,sEvent,handler,bCapture)
{if(hElement.addEventListener)
{hElement.removeEventListener(sEvent,handler,bCapture)
return true}
else if(hElement.attachEvent)
{return hElement.detachEvent('on'+sEvent,handler)}
else if(document.all||hElement.captureEvents)
{eval('hElement.on'+sEvent+' = null')}
else
{alert('Not implemented yet!')}}
function MouseButton()
{if(document.layers)
{this.left=1
this.middle=2
this.right=3}
else if(document.all)
{this.left=1
this.middle=4
this.right=2}
else
{this.left=0
this.middle=1
this.right=2}}
var MB=new MouseButton()
if(document.ELEMENT_NODE==null)
{document.ELEMENT_NODE=1
document.TEXT_NODE=3}
function getSubNodeByName(hNode,sNodeName)
{if(hNode!=null)
{var nNc=0
var nC=0
var hNodeChildren=hNode.childNodes
var hCNode=null
while(nC<hNodeChildren.length)
{hCNode=hNodeChildren.item(nC++)
if((hCNode.nodeType==1)&&(hCNode.nodeName.toLowerCase()==sNodeName))
{return hCNode}}}
return null}
function getPrevNodeSibling(hNode)
{if(hNode!=null)
{do{hNode=hNode.previousSibling}while(hNode!=null&&hNode.nodeType!=1)
return hNode}}
function getNextNodeSibling(hNode)
{if(hNode!=null)
{do{hNode=hNode.nextSibling}while(hNode!=null&&hNode.nodeType!=1)
return hNode}}
function getLastSubNodeByName(hNode,sNodeName)
{if(hNode!=null)
{var nNc=0
var nC=0
var hNodeChildren=hNode.childNodes
var hCNode=null
var nLength=hNodeChildren.length-1
while(nLength>=0)
{hCNode=hNodeChildren.item(nLength)
if((hCNode.nodeType==1)&&(hCNode.nodeName.toLowerCase()==sNodeName))
{return hCNode}
nLength--}}
return null}
function getSubNodeByProperty(hNode,sProperty,sPropValue)
{if(hNode!=null)
{var nNc=0
var nC=0
var hNodeChildren=hNode.childNodes
var hCNode=null
var sAttribute
var hProp
sPropValue=sPropValue.toLowerCase()
while(nC<hNodeChildren.length)
{hCNode=hNodeChildren.item(nC++)
if(hCNode.nodeType==document.ELEMENT_NODE)
{hProp=eval('hCNode.'+sProperty)
if(typeof(sPropValue)!='undefined')
{if(hProp.toLowerCase()==sPropValue)
{return hCNode}}
else
{return hCNode}}
nNc++}}
return null}
function findAttribute(hNode,sAtt)
{sAtt=sAtt.toLowerCase()
for(var nI=0;nI<hNode.attributes.length;nI++)
{if(hNode.attributes.item(nI).nodeName.toLowerCase()==sAtt)
{return hNode.attributes.item(nI).nodeValue}}
return null}
function getSubNodeByAttribute(hNode,sAtt,sAttValue)
{if(hNode!=null)
{var nNc=0
var nC=0
var hNodeChildren=hNode.childNodes
var hCNode=null
var sAttribute
sAttValue=sAttValue.toLowerCase()
while(nC<hNodeChildren.length)
{hCNode=hNodeChildren.item(nC++)
if(hCNode.nodeType==document.ELEMENT_NODE)
{sAttribute=hCNode.getAttribute(sAtt)
if(sAttribute&&sAttribute.toLowerCase()==sAttValue)
return hCNode}
nNc++}}
return null}
function getLastSubNodeByAttribute(hNode,sAtt,sAttValue)
{if(hNode!=null)
{var nNc=0
var nC=0
var hNodeChildren=hNode.childNodes
var hCNode=null
var nLength=hNodeChildren.length-1
while(nLength>=0)
{hCNode=hNodeChildren.item(nLength)
if(hCNode.nodeType==document.ELEMENT_NODE)
{sAttribute=hCNode.getAttribute(sAtt)
if(sAttribute&&sAttribute.toLowerCase()==sAttValue)
return hCNode}
nLength--}}
return null}
function getParentByTagName(hNode,sParentTagName)
{while((hNode.tagName)&&!(/(body|html)/i.test(hNode.tagName)))
{if(hNode.tagName==sParentTagName)
{return hNode}
hNode=hNode.parentNode}
return null}
function getParentByAttribute(hNode,sAtt,sAttValue)
{while((hNode.tagName)&&!(/(body|html)/i.test(hNode.tagName)))
{var sAttr=hNode.getAttribute(sAtt)
if(sAttr!=null&&sAttr.toString().length>0)
{if(sAttValue!==null)
{if(sAttr==sAttValue)
{return hNode}}
else
{return hNode}}
hNode=hNode.parentNode}
return null}
function getParentByProperty(hNode,sProperty,sPropValue)
{while((hNode.tagName)&&!(/(body|html)/i.test(hNode.tagName)))
{var hProp=eval('hNode.'+sProperty)
if(hProp!=null&&hProp.toString().length>0)
{if(sPropValue!==null)
{if(hProp==sPropValue)
{return hNode}}
else
{return hNode}}
hNode=hNode.parentNode}
return null}
function getNodeText(hNode)
{if(hNode==null)
{return''}
var sRes
if(hNode.hasChildNodes())
{sRes=hNode.childNodes.item(0).nodeValue}
else
{sRes=hNode.text}
return sRes}
function cDomExtension(hParent,aSelectors,hInitFunction)
{this.hParent=hParent
this.aSelectors=aSelectors
this.hInitFunction=hInitFunction}
cDomExtensionManager={aExtensions:new Array()}
cDomExtensionManager.register=function(hDomExtension)
{cDomExtensionManager.aExtensions.push(hDomExtension)}
cDomExtensionManager.initSelector=function(hParent,sSelector,hInitFunction)
{var hSelectorRegEx
var hAttributeRegEx
var aSelectorData
var aAttributeData
var sAttribute
hSelectorRegEx=/([a-z0-9_]*)\[?([^\]]*)\]?/i
hAttributeRegEx=/([a-z0-9_]*)([\*\^\$]?)(=?)(([a-z0-9_=]*))/i
if(hSelectorRegEx.test(sSelector)&&!/[@#\.]/.test(sSelector))
{aSelectorData=hSelectorRegEx.exec(sSelector)
if(aSelectorData[1]!='')
{hGroup=hParent.getElementsByTagName(aSelectorData[1].toLowerCase())
for(nI=0;nI<hGroup.length;nI++)
{hGroup[nI].markExt=true}
for(nI=0;nI<hGroup.length;nI++)
{if(!hGroup[nI].markExt)
{continue}
else
{hGroup[nI].markExt=false}
if(aSelectorData[2]=='')
{if(hGroup[nI].tagName.toLowerCase()==aSelectorData[1].toLowerCase())
{hInitFunction(hGroup[nI])}}
else
{aAttributeData=hAttributeRegEx.exec(aSelectorData[2])
if(aAttributeData[1]=='class')
{sAttribute=hGroup[nI].className}
else
{sAttribute=hGroup[nI].getAttribute(aAttributeData[1])}
if(sAttribute!=null&&sAttribute.length>0)
{if(aAttributeData[3]=='=')
{if(aAttributeData[2]=='')
{if(sAttribute==aAttributeData[4])
{hInitFunction(hGroup[nI])}}
else
{switch(aAttributeData[2])
{case'^':if(sAttribute.indexOf(aAttributeData[4])==0)
{hInitFunction(hGroup[nI])}
break
case'$':if(sAttribute.lastIndexOf(aAttributeData[4])==sAttribute.length-aAttributeData[4].length)
{hInitFunction(hGroup[nI])}
break
case'*':if(sAttribute.indexOf(aAttributeData[4])>=0)
{hInitFunction(hGroup[nI])}
break}}}
else
{hInitFunction(hGroup[nI])}}}}
return}}
hSelectorRegEx=/([a-z0-9_]*)([\.#@]?)([a-z0-9_=~]*)/i
hAttributeRegEx=/([a-z0-9_]*)([=~])?([a-z0-9_]*)/i
aSelectorData=hSelectorRegEx.exec(sSelector)
if(aSelectorData[1]!='')
{var hGroup=hParent.getElementsByTagName(aSelectorData[1])
for(nI=0;nI<hGroup.length;nI++)
{hGroup[nI].markExt=true}
for(nI=0;nI<hGroup.length;nI++)
{if(!hGroup[nI].markExt)
{continue}
else
{hGroup[nI].markExt=false}
if(aSelectorData[2]!='')
{switch(aSelectorData[2])
{case'.':if(hGroup[nI].className==aSelectorData[3])
{hInitFunction(hGroup[nI])}
break
case'#':if(hGroup[nI].id==aSelectorData[3])
{hInitFunction(hGroup[nI])}
break
case'@':aAttributeData=hAttributeRegEx.exec(aSelectorData[3])
sAttribute=hGroup[nI].getAttribute(aAttributeData[1])
if(sAttribute!=null&&sAttribute.length>0)
{if(aAttributeData[3]!='')
{if(aAttributeData[2]=='=')
{if(sAttribute==aAttributeData[3])
{hInitFunction(hGroup[nI])}}
else
{if(sAttribute.indexOf(aAttributeData[3])>=0)
{hInitFunction(hGroup[nI])}}}
else
{hInitFunction(hGroup[nI])}}
break}}}}}
cDomExtensionManager.initialize=function()
{var hDomExtension=null
var aSelectors
for(var nKey in cDomExtensionManager.aExtensions)
{aSelectors=cDomExtensionManager.aExtensions[nKey].aSelectors
for(var nKey2 in aSelectors)
{cDomExtensionManager.initSelector(cDomExtensionManager.aExtensions[nKey].hParent,aSelectors[nKey2],cDomExtensionManager.aExtensions[nKey].hInitFunction)}}}
if(window.addEventListener)
{window.addEventListener('load',cDomExtensionManager.initialize,false)}
else if(window.attachEvent)
{window.attachEvent('onload',cDomExtensionManager.initialize)}
function cDomObject(sId)
{if(bw.dom||bw.ie)
{this.hElement=document.getElementById(sId)
this.hStyle=this.hElement.style}}
cDomObject.prototype.getWidth=function()
{return cDomObject.getWidth(this.hElement)}
cDomObject.getWidth=function(hElement)
{if(hElement.currentStyle)
{var nWidth=parseInt(hElement.currentStyle.width)
if(isNaN(nWidth))
{return parseInt(hElement.offsetWidth)}
else
{return nWidth}}
else
{return parseInt(hElement.offsetWidth)}}
cDomObject.prototype.getHeight=function()
{return cDomObject.getHeight(this.hElement)}
cDomObject.getHeight=function(hElement)
{if(hElement.currentStyle)
{var nHeight=parseInt(hElement.currentStyle.height)
if(isNaN(nHeight))
{return parseInt(hElement.offsetHeight)}
else
{return nHeight}}
else
{return parseInt(hElement.offsetHeight)}}
cDomObject.prototype.getLeft=function()
{return cDomObject.getLeft(this.hElement)}
cDomObject.getLeft=function(hElement)
{return parseInt(hElement.offsetLeft)}
cDomObject.prototype.getTop=function()
{return cDomObject.getTop(this.hElement)}
cDomObject.getTop=function(hElement)
{return parseInt(hElement.offsetTop)}
cDomObject.getOffsetParam=function(hElement,sParam,hLimitParent)
{var nRes=0
if(hLimitParent==null)
{hLimitParent=document.body.parentElement}
while(hElement!=hLimitParent)
{nRes+=eval('hElement.'+sParam)
if(!hElement.offsetParent){break}
hElement=hElement.offsetParent}
return nRes}
cDomObject.getScrollOffset=function(hElement,sParam,hLimitParent)
{nRes=0
if(hLimitParent==null)
{hLimitParent=document.body.parentElement}
while(hElement!=hLimitParent)
{nRes+=eval('hElement.scroll'+sParam)
if(!hElement.offsetParent){break}
hElement=hElement.parentNode}
return nRes}
function getDomDocumentPrefix(){if(getDomDocumentPrefix.prefix)
return getDomDocumentPrefix.prefix;var prefixes=["MSXML2","Microsoft","MSXML","MSXML3"];var o;for(var i=0;i<prefixes.length;i++){try{o=new ActiveXObject(prefixes[i]+".DomDocument");return getDomDocumentPrefix.prefix=prefixes[i];}
catch(ex){};}
throw new Error("Could not find an installed XML parser");}
function getXmlHttpPrefix(){if(getXmlHttpPrefix.prefix)
return getXmlHttpPrefix.prefix;var prefixes=["MSXML2","Microsoft","MSXML","MSXML3"];var o;for(var i=0;i<prefixes.length;i++){try{o=new ActiveXObject(prefixes[i]+".XmlHttp");return getXmlHttpPrefix.prefix=prefixes[i];}
catch(ex){};}
throw new Error("Could not find an installed XML parser");}
function XmlHttp(){}
XmlHttp.create=function(){try{if(window.XMLHttpRequest){var req=new XMLHttpRequest();if(req.readyState==null){req.readyState=1;req.addEventListener("load",function(){req.readyState=4;if(typeof req.onreadystatechange=="function")
req.onreadystatechange();},false);}
return req;}
if(window.ActiveXObject){return new ActiveXObject(getXmlHttpPrefix()+".XmlHttp");}}
catch(ex){}
throw new Error("Your browser does not support XmlHttp objects");};function XmlDocument(){}
XmlDocument.create=function(){try{if(document.implementation&&document.implementation.createDocument){var doc=document.implementation.createDocument("","",null);if(doc.readyState==null){doc.readyState=1;doc.addEventListener("load",function(){doc.readyState=4;if(typeof doc.onreadystatechange=="function")
doc.onreadystatechange();},false);}
return doc;}
if(window.ActiveXObject)
return new ActiveXObject(getDomDocumentPrefix()+".DomDocument");}
catch(ex){}
throw new Error("Your browser does not support XmlDocument objects");};if(window.DOMParser&&window.XMLSerializer&&window.Node&&Node.prototype&&Node.prototype.__defineGetter__){Document.prototype.loadXML=function(s){var doc2=(new DOMParser()).parseFromString(s,"text/xml");while(this.hasChildNodes())
this.removeChild(this.lastChild);for(var i=0;i<doc2.childNodes.length;i++){this.appendChild(this.importNode(doc2.childNodes[i],true));}};Document.prototype.__defineGetter__("xml",function(){return(new XMLSerializer()).serializeToString(this);});}
function cAutocomplete(sInputId)
{this.init(sInputId)}
var xmlrpc_url;cAutocomplete.CS_NAME='Autocomplete component'
cAutocomplete.CS_OBJ_NAME='AC_COMPONENT'
cAutocomplete.CS_LIST_PREFIX='ACL_'
cAutocomplete.CS_BUTTON_PREFIX='ACB_'
cAutocomplete.CS_INPUT_PREFIX='AC_'
cAutocomplete.CS_HIDDEN_INPUT_PREFIX='ACH_'
cAutocomplete.CS_INPUT_CLASSNAME='dropdown'
cAutocomplete.CB_AUTOINIT=true
cAutocomplete.CB_AUTOCOMPLETE=false
cAutocomplete.CB_FORCECORRECT=false
cAutocomplete.CB_MATCHSUBSTRING=false
cAutocomplete.CS_SEPARATOR=','
cAutocomplete.CS_ARRAY_SEPARATOR=','
cAutocomplete.CB_MATCHSTRINGBEGIN=true
cAutocomplete.CN_OFFSET_TOP=2
cAutocomplete.CN_OFFSET_LEFT=-1
cAutocomplete.CN_LINE_HEIGHT=19
cAutocomplete.CN_NUMBER_OF_LINES=10
cAutocomplete.CN_HEIGHT_FIX=2
cAutocomplete.CN_CLEAR_TIMEOUT=300
cAutocomplete.CN_SHOW_TIMEOUT=400
cAutocomplete.CN_REMOTE_SHOW_TIMEOUT=1000
cAutocomplete.CN_MARK_TIMEOUT=400
cAutocomplete.hListDisplayed=null
cAutocomplete.nCount=0
cAutocomplete.autoInit=function()
{var nI=0
var hACE=null
var sLangAtt
var nInputsLength=document.getElementsByTagName('INPUT').length
for(nI=0;nI<nInputsLength;nI++)
{if(document.getElementsByTagName('INPUT')[nI].type.toLowerCase()=='text')
{sLangAtt=document.getElementsByTagName('INPUT')[nI].getAttribute('acdropdown')
if(sLangAtt!=null&&sLangAtt.length>0)
{if(document.getElementsByTagName('INPUT')[nI].id==null||document.getElementsByTagName('INPUT')[nI].id.length==0)
{document.getElementsByTagName('INPUT')[nI].id=cAutocomplete.CS_OBJ_NAME+cAutocomplete.nCount}
hACE=new cAutocomplete(document.getElementsByTagName('INPUT')[nI].id)}}}
var nTALength=document.getElementsByTagName('TEXTAREA').length
for(nI=0;nI<nTALength;nI++)
{sLangAtt=document.getElementsByTagName('TEXTAREA')[nI].getAttribute('acdropdown')
if(sLangAtt!=null&&sLangAtt.length>0)
{if(document.getElementsByTagName('TEXTAREA')[nI].id==null||document.getElementsByTagName('TEXTAREA')[nI].id.length==0)
{document.getElementsByTagName('TEXTAREA')[nI].id=cAutocomplete.CS_OBJ_NAME+cAutocomplete.nCount}
hACE=new cAutocomplete(document.getElementsByTagName('TEXTAREA')[nI].id)}}
var nSelectsLength=document.getElementsByTagName('SELECT').length
var aSelect=null
for(nI=0;nI<nSelectsLength;nI++)
{aSelect=document.getElementsByTagName('SELECT')[nI]
sLangAtt=aSelect.getAttribute('acdropdown')
if(sLangAtt!=null&&sLangAtt.length>0)
{if(aSelect.id==null||aSelect.id.length==0)
{aSelect.id=cAutocomplete.CS_OBJ_NAME+cAutocomplete.nCount}
hACE=new cAutocomplete(aSelect.id)
nSelectsLength--
nI--}}}
if(cAutocomplete.CB_AUTOINIT)
{if(window.attachEvent)
{window.attachEvent('onload',cAutocomplete.autoInit)}
else if(window.addEventListener)
{window.addEventListener('load',cAutocomplete.autoInit,false)}}
cAutocomplete.prototype.init=function(sInputId)
{this.bDebug=false
this.sInputId=sInputId
this.sListId=cAutocomplete.CS_LIST_PREFIX+sInputId
this.sObjName=cAutocomplete.CS_OBJ_NAME+'_obj_'+(cAutocomplete.nCount++)
this.hObj=this.sObjName
this.hActiveSelection=null
this.nSelectedItemIdx=-1
this.sLastActiveValue=''
this.sActiveValue=''
this.bListDisplayed=false
this.nItemsDisplayed=0
this.bAssociative=true
this.sHiddenInputId=null
this.bHasButton=false
this.aData=null
this.aSearchData=new Array()
this.bSorted=false
this.nLastMatchLength=0
this.bForceCorrect=cAutocomplete.CB_FORCECORRECT
var sForceCorrect=document.getElementById(this.sInputId).getAttribute('autocomplete_forcecorrect')
if(sForceCorrect!=null&&sForceCorrect.length>0)
{this.bForceCorrect=eval(sForceCorrect)}
this.bMatchBegin=cAutocomplete.CB_MATCHSTRINGBEGIN
var sMatchBegin=document.getElementById(this.sInputId).getAttribute('autocomplete_matchbegin')
if(sMatchBegin!=null&&sMatchBegin.length>0)
{this.bMatchBegin=eval(sMatchBegin)}
this.bMatchSubstring=cAutocomplete.CB_MATCHSUBSTRING
var sMatchSubstring=document.getElementById(this.sInputId).getAttribute('autocomplete_matchsubstring')
if(sMatchSubstring!=null&&sMatchSubstring.length>0)
{this.bMatchSubstring=eval(sMatchSubstring)}
this.bAutoComplete=cAutocomplete.CB_AUTOCOMPLETE
this.bAutocompleted=false
var sAutoComplete=document.getElementById(this.sInputId).getAttribute('autocomplete_complete')
if(sAutoComplete!=null&&sAutoComplete.length>0)
{this.bAutoComplete=eval(sAutoComplete)}
this.formatOptions=null
var sFormatFunction=document.getElementById(this.sInputId).getAttribute('autocomplete_format')
if(sFormatFunction!=null&&sFormatFunction.length>0)
{this.formatOptions=eval(sFormatFunction)}
this.onSelect=null
var sOnSelectFunction=document.getElementById(this.sInputId).getAttribute('autocomplete_onselect')
if(sOnSelectFunction!=null&&sOnSelectFunction.length>0)
{this.onSelect=eval(sOnSelectFunction)}
if(this.getListArrayType()=='url'||this.getListArrayType()=='xmlrpc')
{this.bAssociative=false
this.bRemoteList=true
this.sListURL=this.getListURL()
this.hXMLHttp=XmlHttp.create()
this.bXMLRPC=(this.getListArrayType()=='xmlrpc')}
else
{this.bRemoteList=false}
var sAssociative=document.getElementById(this.sInputId).getAttribute('autocomplete_assoc')
if(sAssociative!=null&&sAssociative.length>0)
{this.bAssociative=eval(sAssociative)}
this.initListArray()
this.initListContainer()
this.initInput()
eval(this.hObj+'= this')}
cAutocomplete.prototype.initInput=function()
{var hInput=document.getElementById(this.sInputId)
hInput.hAutocomplete=this
var hContainer=document.getElementById(this.sListId)
hContainer.hAutocomplete=this
var nWidth=hInput.offsetWidth
if(!nWidth||nWidth==0)
{var hOWInput=hInput.cloneNode(true)
hOWInput.style.position='absolute'
hOWInput.style.top='-1000px'
document.body.appendChild(hOWInput)
var nWidth=hOWInput.offsetWidth
document.body.removeChild(hOWInput)}
var sInputName=hInput.name
var hForm=hInput.form
var bHasButton=false
var sHiddenValue=hInput.value
var sValue=hInput.type.toLowerCase()=='text'?hInput.value:''
var sHasButton=hInput.getAttribute('autocomplete_button')
if(sHasButton!=null&&sHasButton.length>0)
{bHasButton=true}
if(hInput.type.toLowerCase()=='select-one')
{bHasButton=true
if(hInput.selectedIndex>=0)
{sHiddenValue=hInput.options[hInput.selectedIndex].value
sValue=hInput.options[hInput.selectedIndex].text}}
if(hForm)
{var hHiddenInput=document.createElement('INPUT')
hHiddenInput.id=cAutocomplete.CS_HIDDEN_INPUT_PREFIX+this.sInputId
hHiddenInput.type='hidden'
hForm.appendChild(hHiddenInput)
if(this.bAssociative)
{hHiddenInput.name=sInputName
hInput.name=cAutocomplete.CS_INPUT_PREFIX+sInputName}
else
{hHiddenInput.name=cAutocomplete.CS_INPUT_PREFIX+sInputName}
hHiddenInput.value=sHiddenValue
this.sHiddenInputId=hHiddenInput.id}
if(bHasButton)
{this.bHasButton=true
var hInputContainer=document.createElement('DIV')
hInputContainer.className='acinputContainer'
hInputContainer.style.width=nWidth
var hInputButton=document.createElement('INPUT')
hInputButton.id=cAutocomplete.CS_BUTTON_PREFIX+this.sInputId
hInputButton.type='button'
hInputButton.className='button'
hInputButton.tabIndex=hInput.tabIndex+1
hInputButton.hAutocomplete=this
var hNewInput=document.createElement('INPUT')
if(this.bAssociative)
{hNewInput.name=cAutocomplete.CS_INPUT_PREFIX+sInputName}
else
{hNewInput.name=sInputName}
hNewInput.type='text'
hNewInput.value=sValue
hNewInput.style.width=nWidth-22
hNewInput.className=cAutocomplete.CS_INPUT_CLASSNAME
hNewInput.tabIndex=hInput.tabIndex
hNewInput.hAutocomplete=this
hInputContainer.appendChild(hNewInput)
hInputContainer.appendChild(hInputButton)
hInput.parentNode.replaceChild(hInputContainer,hInput)
hNewInput.id=this.sInputId
hInput=hNewInput}
if(hInput.attachEvent)
{hInput.attachEvent('onkeyup',cAutocomplete.onInputKeyUp)
hInput.attachEvent('onkeyup',cAutocomplete.saveCaretPosition)
hInput.attachEvent('onkeydown',cAutocomplete.onInputKeyDown)
hInput.attachEvent('onblur',cAutocomplete.onInputBlur)
hInput.attachEvent('onfocus',cAutocomplete.onInputFocus)
if(hInputButton)
{hInputButton.attachEvent('onclick',cAutocomplete.onButtonClick)}}
else if(hInput.addEventListener)
{hInput.addEventListener('keyup',cAutocomplete.onInputKeyUp,false)
hInput.addEventListener('keyup',cAutocomplete.saveCaretPosition,false)
hInput.addEventListener('keydown',cAutocomplete.onInputKeyDown,false)
hInput.addEventListener('keypress',cAutocomplete.onInputKeyPress,false)
hInput.addEventListener('blur',cAutocomplete.onInputBlur,false)
hInput.addEventListener('focus',cAutocomplete.onInputFocus,false)
if(hInputButton)
{hInputButton.addEventListener('click',cAutocomplete.onButtonClick,false)}}
hInput.setAttribute('autocomplete','OFF')
if(hForm)
{if(hForm.attachEvent)
{hForm.attachEvent('onsubmit',cAutocomplete.onFormSubmit)
if(this.bDebug){this.debug("attachEvent added")}}
else if(hForm.addEventListener)
{hForm.addEventListener('submit',cAutocomplete.onFormSubmit,false)
if(this.bDebug){this.debug("addEventListener")}}}}
cAutocomplete.prototype.initListContainer=function()
{var hInput=document.getElementById(this.sInputId)
var hContainer=document.createElement('DIV')
hContainer.className='autocomplete_holder'
hContainer.id=this.sListId
hContainer.style.zIndex=10000+cAutocomplete.nCount
hContainer.hAutocomplete=this
var hFirstBorder=document.createElement('DIV')
hFirstBorder.className='autocomplete_firstborder'
var hSecondBorder=document.createElement('DIV')
hSecondBorder.className='autocomplete_secondborder'
var hList=document.createElement('UL')
hList.className='autocomplete'
hSecondBorder.appendChild(hList)
hFirstBorder.appendChild(hSecondBorder)
hContainer.appendChild(hFirstBorder)
document.body.appendChild(hContainer)
if(hContainer.attachEvent)
{hContainer.attachEvent('onblur',cAutocomplete.onListBlur)
hContainer.attachEvent('onfocus',cAutocomplete.onListFocus)}
else if(hInput.addEventListener)
{hContainer.addEventListener('blur',cAutocomplete.onListBlur,false)
hContainer.addEventListener('focus',cAutocomplete.onListFocus,false)}
if(hContainer.attachEvent)
{hContainer.attachEvent('onclick',cAutocomplete.onItemClick)}
else if(hContainer.addEventListener)
{hContainer.addEventListener('click',cAutocomplete.onItemClick,false)}}
cAutocomplete.prototype.createList=function()
{var hInput=document.getElementById(this.sInputId)
var hContainer=document.getElementById(this.sListId)
var hList=hContainer.getElementsByTagName('UL')[0]
if(hList)
{hList=hList.parentNode.removeChild(hList)
while(hList.hasChildNodes())
{hList.removeChild(hList.childNodes[0])}}
var hListItem=null
var hListItemLink=null
var hArrKey=null
var sArrEl=null
var hArr=this.aData
var nI=0
var sRealText
for(hArrKey in hArr)
{sArrEl=hArr[hArrKey]
hListItem=document.createElement('LI')
hListItemLink=document.createElement('A')
hListItemLink.setAttribute('itemvalue',hArrKey)
var sArrData=sArrEl.split(cAutocomplete.CS_ARRAY_SEPARATOR)
if(sArrData.length>1)
{this.aData[hArrKey]=sArrData[0]
hListItemLink.setAttribute('itemdata',sArrEl.substring(sArrEl.indexOf(cAutocomplete.CS_ARRAY_SEPARATOR)+1))
sRealText=sArrData[0]}
else
{sRealText=sArrEl}
hListItemLink.href='#'
hListItemLink.appendChild(document.createTextNode(sRealText))
hListItemLink.realText=sRealText
if(nI==this.nSelectedItemIdx)
{this.hActiveSelection=hListItemLink
this.hActiveSelection.className='selected'}
hListItem.appendChild(hListItemLink)
hList.appendChild(hListItem)
this.aSearchData[nI++]=sRealText.toLowerCase()}
var hSecondBorder=hContainer.firstChild.firstChild
hSecondBorder.appendChild(hList)
this.bListUpdated=false}
cAutocomplete.prototype.initListArray=function()
{var hInput=document.getElementById(this.sInputId)
var hArr=null
if(hInput.type.toLowerCase()=='select-one')
{hArr=new Object()
for(var nI=0;nI<hInput.options.length;nI++)
{hArrKey=hInput.options.item(nI).value
sArrEl=hInput.options.item(nI).text
hArr[hArrKey]=sArrEl
if(hInput.options.item(nI).selected)
{this.nSelectedItemIdx=nI}}}
else
{var sAA=hInput.getAttribute('autocomplete_list')
var sAAS=hInput.getAttribute('autocomplete_list_sort')
var sArrayType=this.getListArrayType()
switch(sArrayType)
{case'array':hArr=eval(sAA.substring(6))
break
case'list':hArr=new Array()
var hTmpArray=sAA.substring(5).split('|')
var aValueArr
for(hKey in hTmpArray)
{aValueArr=hTmpArray[hKey].split(cAutocomplete.CS_ARRAY_SEPARATOR)
if(aValueArr.length==1)
{hArr[hKey]=hTmpArray[hKey]
this.bAssociative=false}
else
{hArr[aValueArr[0]]=aValueArr[1]}}
break}
if(sAAS!=null&&eval(sAAS))
{this.bSorted=true
this.aData=hArr.sort()
hArr=hArr.sort()}}
this.setArray(hArr)}
cAutocomplete.prototype.setArray=function(sArray)
{if(typeof sArray=='string')
{this.aData=eval(sArray)}
else
{this.aData=sArray}
this.bListUpdated=true}
cAutocomplete.prototype.setListArray=function(sArray)
{this.setArray(sArray)
this.updateAndShowList()}
cAutocomplete.prototype.getListArrayType=function()
{var hInput=document.getElementById(this.sInputId)
var sAA=hInput.getAttribute('autocomplete_list')
if(sAA!=null&&sAA.length>0)
{if(sAA.indexOf('array:')>=0)
{return'array'}
else if(sAA.indexOf('list:')>=0)
{return'list'}
else if(sAA.indexOf('url:')>=0)
{return'url'}
else if(sAA.indexOf('xmlrpc:')>=0)
{return'xmlrpc'}}}
cAutocomplete.prototype.getListURL=function()
{var hInput=document.getElementById(this.sInputId)
var sAA=hInput.getAttribute('autocomplete_list')
if(sAA!=null&&sAA.length>0)
{if(sAA.indexOf('url:')>=0)
{return sAA.substring(4)}
if(sAA.indexOf('xmlrpc:')>=0)
{return sAA.substring(7)}}}
cAutocomplete.prototype.setListURL=function(sURL)
{this.sListURL=sURL;}
cAutocomplete.prototype.onXmlHttpLoad=function()
{if(this.hXMLHttp.readyState==4)
{var hError=this.hXMLHttp.parseError
if(hError&&hError.errorCode!=0)
{alert(hError.reason)}
else
{this.afterRemoteLoad()}}}
cAutocomplete.prototype.onXMLRPCHttpLoad=function()
{if(this.hXMLHttp.readyState==4)
{var hError=this.hXMLHttp.parseError
if(hError&&hError.errorCode!=0)
{alert(hError.reason)}
else
{this.afterRemoteLoadXMLRPC()}}}
cAutocomplete.prototype.loadXMLRPCListArray=function()
{var sURL=this.sListURL
var xmlrpc_url=data_path+'/RPC2.php'
var aMethodArgs=sURL.split(' ')
var sMethodName=aMethodArgs[0]
var sStartWith=this.getStringForAutocompletion(this.sActiveValue,this.nInsertPoint)
sStartWith=sStartWith.replace(/^\s/,'')
sStartWith=sStartWith.replace(/\s$/,'')
if(sMethodName.indexOf('?')>0)
{sMethodName=sMethodName.replace('/^.+\?/','')
sURL=sURL.replace('/\?.+$/','')}
else
{sURL=xmlrpc_url}
if(sMethodName.length<1)
{var hInput=document.getElementById(this.sInputId)
hInput.value=this.sActiveValue
return}
var sRequest='<?xml version=\'1.0\' encoding="utf-8" ?>\n'
sRequest+='<methodCall><methodName>'+sMethodName+'</methodName>\n'
if(aMethodArgs.length<=1)
{sRequest+='<params/>\n'}
else
{sRequest+='<params>\n'
for(var nI=1;nI<aMethodArgs.length;nI++)
{var sArg=aMethodArgs[nI];if(sArg.indexOf('[S]')>=0)
{sArg=sArg.replace('[S]',sStartWith)}
sRequest+='<param><value><string>'
sRequest+=sArg
sRequest+='</string></value></param>\n'}
sRequest+='</params>\n'}
sRequest+='</methodCall>'
if(this.bDebug){this.debug('url: "'+sURL+'" sRequest: "'+sRequest.substring(20)+'"')}
this.hXMLHttp.open('POST',sURL,true)
var hAC=this
this.hXMLHttp.onreadystatechange=function(){hAC.onXMLRPCHttpLoad()}
this.hXMLHttp.send(sRequest)}
cAutocomplete.prototype.loadListArray=function()
{var sURL=this.sListURL
var sStartWith=this.getStringForAutocompletion(this.sActiveValue,this.nInsertPoint)
sStartWith=sStartWith.replace(/^\s/,'')
sStartWith=sStartWith.replace(/\s$/,'')
if(sURL.indexOf('[S]')>=0)
{sURL=sURL.replace('[S]',sStartWith)}
else
{sURL+=this.sActiveValue}
this.hXMLHttp.open('GET',sURL,true)
var hAC=this
this.hXMLHttp.onreadystatechange=function(){hAC.onXmlHttpLoad()}
this.hXMLHttp.send(null)}
cAutocomplete.prototype.afterRemoteLoad=function()
{var hInput=document.getElementById(this.sInputId)
var hArr=new Array()
var hTmpArray=this.hXMLHttp.responseText.split('|')
var aValueArr
for(hKey in hTmpArray)
{aValueArr=hTmpArray[hKey].split(cAutocomplete.CS_ARRAY_SEPARATOR)
if(aValueArr.length==1)
{hArr[hKey]=hTmpArray[hKey]}
else
{hArr[aValueArr[0]]=hTmpArray[hKey].substr(hTmpArray[hKey].indexOf(cAutocomplete.CS_ARRAY_SEPARATOR)+1)}}
hInput.className=''
hInput.readonly=false
hInput.value=this.sActiveValue
this.setListArray(hArr)}
cAutocomplete.prototype.afterRemoteLoadXMLRPC=function()
{var hInput=document.getElementById(this.sInputId)
var hArr=new Array()
sResult=this.hXMLHttp.responseText
if(this.bDebug){this.debug("response: "+sResult.substring(70,190))}
sResult.replace('\n','');sResult.replace('\r','');var hKey=0
var i=sResult.indexOf('<string>')
while(i>=0){var j
sResult=sResult.substring(i+8)
j=sResult.indexOf('</string>')
hArr[hKey]=sResult.substring(0,j)
hKey+=1
sResult=sResult.substring(j+9)
i=sResult.indexOf('<string>')}
hInput.className=''
hInput.readonly=false
hInput.value=this.sActiveValue
this.setListArray(hArr)}
cAutocomplete.prototype.prepareList=function(bFullList)
{var hInput=document.getElementById(this.sInputId)
this.sActiveValue=hInput.value
var sST=this.getStringForAutocompletion(this.sActiveValue,this.nInsertPoint)
var sLST=this.getStringForAutocompletion(this.sLastActiveValue,this.nInsertPoint)
if(sLST!=sST||bFullList||!this.bListDisplayed||this.bMatchSubstring)
{if(this.bRemoteList)
{hInput.className='search'
this.bXMLRPC?this.loadXMLRPCListArray():this.loadListArray()
return}
this.updateAndShowList(bFullList)}}
cAutocomplete.prototype.updateAndShowList=function(bFullList)
{var hContainer=document.getElementById(this.sListId)
var hList=hContainer.getElementsByTagName('UL')[0]
var hInput=document.getElementById(this.sInputId)
if(this.bListUpdated)
{this.createList()}
var sST=this.bMatchSubstring?this.getStringForAutocompletion(this.sActiveValue,this.nInsertPoint):this.sActiveValue
var sLST=this.bMatchSubstring?this.getStringForAutocompletion(this.sLastActiveValue,this.nInsertPoint):this.sLastActiveValue
if(sST==sLST)
{if(!this.bMatchSubstring)
{bFullList=true}}
this.filterOptions(bFullList)
if(this.nItemsDisplayed==0)
{if(this.bForceCorrect)
{var aPos=this.getInsertPos(this.sActiveValue,this.nInsertPoint,'')
cAutocomplete.markInputRange(hInput,this.nLastMatchLength,aPos[0])}}
this.sLastActiveValue=this.sActiveValue
if(this.nItemsDisplayed>0)
{if(!bFullList||this.bMatchSubstring)
{this.deselectOption()}
if(this.bAutoComplete&&this.nItemsDisplayed==1)
{var sStartWith=this.getStringForAutocompletion(this.sActiveValue,this.nInsertPoint)
var sItemText=hList.getElementsByTagName('LI')[this.nFirstDisplayed].getElementsByTagName('A')[0].realText
if(sStartWith.toLowerCase()==sItemText.toLowerCase())
{this.selectOption(hList.getElementsByTagName('LI')[this.nFirstDisplayed].getElementsByTagName('A')[0])
this.hideOptions()
return}}
if(this.bAutoComplete&&!bFullList)
{this.selectOption(hList.getElementsByTagName('LI')[this.nFirstDisplayed].getElementsByTagName('A')[0])}
this.showList()}
else
{this.clearList()}}
cAutocomplete.prototype.showList=function()
{if(cAutocomplete.hListDisplayed)
{cAutocomplete.hListDisplayed.clearList()}
var hInput=document.getElementById(this.sInputId)
var nTop=cDomObject.getOffsetParam(hInput,'offsetTop')
var nLeft=cDomObject.getOffsetParam(hInput,'offsetLeft')
var hContainer=document.getElementById(this.sListId)
var hList=hContainer.getElementsByTagName('UL')[0]
if(this.bHasButton)
{hContainer.style.width=document.getElementById(this.sInputId).parentNode.offsetWidth}
else
{hContainer.style.width=document.getElementById(this.sInputId).offsetWidth}
var nNumLines=(this.nItemsDisplayed<cAutocomplete.CN_NUMBER_OF_LINES)?this.nItemsDisplayed:cAutocomplete.CN_NUMBER_OF_LINES;hList.style.height=nNumLines*cAutocomplete.CN_LINE_HEIGHT+cAutocomplete.CN_HEIGHT_FIX+'px'
hContainer.style.top=nTop+hInput.offsetHeight+cAutocomplete.CN_OFFSET_TOP+'px'
hContainer.style.left=nLeft+cAutocomplete.CN_OFFSET_LEFT+'px'
hContainer.style.display='none'
hContainer.style.visibility='visible'
hContainer.style.display='block'
cAutocomplete.hListDisplayed=this
this.bListDisplayed=true}
cAutocomplete.prototype.binarySearch=function(sFilter)
{var nLow=0
var nHigh=this.aSearchData.length-1
var nMid
var nTry,nLastTry
var sData
var nLen=sFilter.length
var lastTry
while(nLow<=nHigh)
{nMid=(nLow+nHigh)/2
nTry=(nMid<1)?0:parseInt(nMid)
sData=this.aSearchData[nTry].substr(0,nLen)
if(sData<sFilter)
{nLow=nTry+1
continue}
if(sData>sFilter)
{nHigh=nTry-1
continue}
if(sData==sFilter)
{nHigh=nTry-1
nLastTry=nTry
continue}
return nTry}
if(typeof(nLastTry)!="undefined")
{return nLastTry}
else
{return null}}
cAutocomplete.prototype.getStringForAutocompletion=function(sString,nPos)
{if(sString==null||sString.length==0)
{return''}
if(this.bMatchSubstring)
{var nStartPos=sString.lastIndexOf(cAutocomplete.CS_SEPARATOR,nPos-1)
nStartPos=nStartPos<0?0:nStartPos
var nEndPos=sString.indexOf(cAutocomplete.CS_SEPARATOR,nPos)
nEndPos=nEndPos<0?sString.length:nEndPos
var sStr=sString.substr(nStartPos,nEndPos-nStartPos)
sStr=sStr.replace(/^(\,?)(\s*)(\S*)(\s*)(\,?)$/g,'$3')
return sStr}
else
{return sString}}
cAutocomplete.prototype.insertString=function(sString,nPos,sInsert)
{if(this.bMatchSubstring)
{var nStartPos=sString.lastIndexOf(cAutocomplete.CS_SEPARATOR,nPos-1)
nStartPos=nStartPos<0?0:nStartPos
var nEndPos=sString.indexOf(cAutocomplete.CS_SEPARATOR,nPos)
nEndPos=nEndPos<0?sString.length:nEndPos
var sStr=sString.substr(nStartPos,nEndPos-nStartPos)
sStr=sStr.replace(/^(\,?)(\s*)(\S?[\S\s]*\S?)(\s*)(\,?)$/g,'$1$2'+sInsert+'$4$5')
sStr=sString.substr(0,nStartPos)+sStr+sString.substr(nEndPos)
return sStr}
else
{return sInsert}}
cAutocomplete.prototype.getInsertPos=function(sString,nPos,sInsert)
{nPos=nPos==null?0:nPos
var nStartPos=sString.lastIndexOf(cAutocomplete.CS_SEPARATOR,nPos-1)
nStartPos=nStartPos<0?0:nStartPos
var nEndPos=sString.indexOf(cAutocomplete.CS_SEPARATOR,nPos)
nEndPos=nEndPos<0?sString.length:nEndPos
var sStr=sString.substr(nStartPos,nEndPos-nStartPos)
sStr=sStr.replace(/^(\,?)(\s*)(\S?[\S\s]*\S?)(\s*)(\,?)$/g,'$1$2'+sInsert)
return[nPos,nStartPos+sStr.length]}
cAutocomplete.prototype.filterOptions=function(bShowAll)
{if(this.hActiveSelection&&!bShowAll)
{this.hActiveSelection.className=''}
if(typeof bShowAll=='undefined')
{bShowAll=false}
var hInput=document.getElementById(this.sInputId)
var sStartWith=this.getStringForAutocompletion(this.sActiveValue,this.nInsertPoint)
if(bShowAll)
{sStartWith=''}
var hContainer=document.getElementById(this.sListId)
var hList=hContainer.getElementsByTagName('UL')[0]
var nItemsLength=hList.childNodes.length
var hLinkItem=null
var nCount=0
var hParent=hList.parentNode
var hList=hList.parentNode.removeChild(hList)
var hTItems=hList.childNodes
this.nItemsDisplayed=0
if(sStartWith.length==0)
{for(var nI=0;nI<nItemsLength;nI++)
{if(this.formatOptions)
{hTItems[nI].childNodes[0].innerHTML=this.formatOptions(hTItems[nI].childNodes[0].realText,nI)}
hTItems[nI].style.display='block'}
nCount=nItemsLength
if(nItemsLength>0)
{this.nFirstDisplayed=0
this.nLastDisplayed=nItemsLength-1}
else
{this.nFirstDisplayed=this.nLastDisplayed=-1}
var aPos=this.getInsertPos(this.sActiveValue,this.nInsertPoint,sStartWith)
this.nLastMatchLength=aPos[0]}
else
{this.nFirstDisplayed=this.nLastDisplayed=-1
sStartWith=sStartWith.toLowerCase()
var bEnd=false
if(this.bSorted&&this.bMatchBegin)
{var nStartAt=this.binarySearch(sStartWith)
for(var nI=0;nI<nItemsLength;nI++)
{hTItems[nI].style.display='none'
if(nI>=nStartAt&&!bEnd)
{if(!bEnd&&this.aSearchData[nI].indexOf(sStartWith)!=0)
{bEnd=true
continue}
if(this.formatOptions)
{hTItems[nI].childNodes[0].innerHTML=this.formatOptions(hTItems[nI].childNodes[0].realText,nI)}
hTItems[nI].style.display='block'
nCount++
if(this.nFirstDisplayed<0)
{this.nFirstDisplayed=nI}
this.nLastDisplayed=nI}}}
else
{for(var nI=0;nI<nItemsLength;nI++)
{hTItems[nI].style.display='none'
if((this.bMatchBegin&&this.aSearchData[nI].indexOf(sStartWith)==0)||(!this.bMatchBegin&&this.aSearchData[nI].indexOf(sStartWith)>=0))
{if(this.formatOptions)
{hTItems[nI].childNodes[0].innerHTML=this.formatOptions(hTItems[nI].childNodes[0].realText,nI)}
hTItems[nI].style.display='block'
nCount++
if(this.nFirstDisplayed<0)
{this.nFirstDisplayed=nI}
this.nLastDisplayed=nI}}}
if(nCount>0)
{var aPos=this.getInsertPos(this.sActiveValue,this.nInsertPoint,sStartWith)
this.nLastMatchLength=aPos[0]}}
hParent.appendChild(hList)
this.nItemsDisplayed=nCount}
cAutocomplete.prototype.hideOptions=function()
{var hContainer=document.getElementById(this.sListId)
hContainer.style.visibility='hidden'
hContainer.style.display='none'
this.hListDisplayed=null}
cAutocomplete.prototype.markAutocompletedValue=function()
{var hInput=document.getElementById(this.sInputId)
var sValue=this.hActiveSelection.realText
if(this.bMatchSubstring)
{var aPos=this.getInsertPos(this.sLastActiveValue,this.nInsertPoint,sValue)
var nStartPos=aPos[0]
var nEndPos=aPos[1]}
else
{var nStartPos=this.nInsertPoint
var nEndPos=sValue.length}
this.nStartAC=nStartPos
this.nEndAC=nEndPos
if(this.hMarkRangeTimeout!=null)
{clearTimeout(this.hMarkRangeTimeout)}
this.hMarkRangeTimeout=setTimeout(function(){cAutocomplete.markInputRange2(hInput.id)},cAutocomplete.CN_MARK_TIMEOUT)}
cAutocomplete.prototype.selectOptionByIndex=function(nOptionIndex)
{if(this.bListUpdated)
{this.createList()}
var hContainer=document.getElementById(this.sListId)
var hList=hContainer.getElementsByTagName('UL')[0]
var nItemsLength=hList.childNodes.length
if(nOptionIndex>=0&&nOptionIndex<nItemsLength)
{this.selectOption(hList.childNodes[nOptionIndex].getElementsByTagName('A')[0])}}
cAutocomplete.prototype.selectOptionByValue=function(sValue)
{if(this.bListUpdated)
{this.createList()}
sValue=sValue.toLowerCase()
var hContainer=document.getElementById(this.sListId)
var hList=hContainer.getElementsByTagName('UL')[0]
var nItemsLength=hList.childNodes.length
var nSelectedIndex=-1
for(var nI=0;nI<nItemsLength;nI++)
{if(this.aSearchData[nI].indexOf(sValue)==0)
{nSelectedIndex=nI}}
if(nSelectedIndex>=0)
{this.selectOption(hList.childNodes[nSelectedIndex].getElementsByTagName('A')[0])}}
cAutocomplete.prototype.selectOption=function(hNewOption)
{if(this.hActiveSelection)
{if(this.hActiveSelection==hNewOption)
{return}
else
{this.hActiveSelection.className=''}}
this.hActiveSelection=hNewOption
var hInput=document.getElementById(this.sInputId)
if(this.hActiveSelection!=null)
{if(this.sHiddenInputId!=null)
{if(this.bMatchSubstring)
{document.getElementById(this.sHiddenInputId).value=this.hActiveSelection.getAttribute('itemvalue')}
else
{document.getElementById(this.sHiddenInputId).value=this.hActiveSelection.getAttribute('itemvalue')}}
this.hActiveSelection.className='selected'
if(this.bAutoComplete)
{hInput.value=this.insertString(this.sLastActiveValue,this.nInsertPoint,this.hActiveSelection.realText)
this.bAutocompleted=true
this.markAutocompletedValue()}
else
{var aPos=this.getInsertPos(this.sLastActiveValue,this.nInsertPoint,this.hActiveSelection.realText)
hInput.value=this.insertString(this.sActiveValue,this.nInsertPoint,this.hActiveSelection.realText)
cAutocomplete.setInputCaretPosition(hInput,aPos[1])}
this.sActiveValue=hInput.value
if(this.onSelect)
{this.onSelect()}}
else
{hInput.value=this.sActiveValue
cAutocomplete.setInputCaretPosition(hInput,this.nInsertPoint)}}
cAutocomplete.prototype.deselectOption=function()
{if(this.hActiveSelection!=null)
{this.hActiveSelection.className=''
this.hActiveSelection=null}}
cAutocomplete.prototype.clearList=function()
{this.hideOptions()
this.bListDisplayed=false}
cAutocomplete.prototype.getPrevDisplayedItem=function(hItem)
{if(hItem==null)
{var hContainer=document.getElementById(this.sListId)
hItem=hContainer.getElementsByTagName('UL')[0].childNodes.item(hContainer.getElementsByTagName('UL')[0].childNodes.length-1)}
else
{hItem=getPrevNodeSibling(hItem.parentNode)}
while(hItem!=null)
{if(hItem.style.display=='block')
{return hItem}
hItem=hItem.previousSibling}
return null}
cAutocomplete.prototype.getNextDisplayedItem=function(hItem)
{if(hItem==null)
{var hContainer=document.getElementById(this.sListId)
hItem=hContainer.getElementsByTagName('UL')[0].childNodes.item(0)}
else
{hItem=getNextNodeSibling(hItem.parentNode)}
while(hItem!=null)
{if(hItem.style.display=='block')
{return hItem}
hItem=hItem.nextSibling}
return null}
cAutocomplete.prototype.debug=function(s)
{if(this.bDebug){var hInput=document.getElementById(this.sInputId)
var hContainer=document.createElement('DIV')
hContainer.className='debug'
hContainer.innerHTML=s
hInput.form.appendChild(hContainer)}}
cAutocomplete.onInputKeyDown=function(hEvent)
{if(hEvent==null)
{hEvent=window.event}
var hElement=(hEvent.srcElement)?hEvent.srcElement:hEvent.originalTarget
var hAC=hElement.hAutocomplete
var hContainer=document.getElementById(hAC.sListId)
var hInput=document.getElementById(hAC.sInputId)
var hList=hContainer.getElementsByTagName('UL')[0]
var hEl=getParentByTagName(hElement,'A')
if(hContainer!=null&&hAC.bListDisplayed)
{var hLI=null
var hLINext=null
if((hEvent.keyCode==13)||(hEvent.keyCode==27))
{var bItemSelected=hEvent.keyCode==13?true:false
hAC.clearList()
if(hAC.bDebug){hAC.debug("key "+hEvent.keyCode+" new active selection")}}
if(hEvent.keyCode==38)
{if(hAC.bDebug){hAC.debug("key "+hEvent.keyCode+" up")}
hLINext=hAC.getPrevDisplayedItem(hAC.hActiveSelection)
if(hLINext!=null)
{hAC.selectOption(hLINext.childNodes.item(0))
if(hAC.nItemsDisplayed>cAutocomplete.CN_NUMBER_OF_LINES)
{if(hList.scrollTop<5&&hLINext.offsetTop>hList.offsetHeight)
{hList.scrollTop=hList.scrollHeight-hList.offsetHeight}
if(hLINext.offsetTop-hList.scrollTop<0)
{hList.scrollTop-=hLINext.offsetHeight}}}
else
{hAC.selectOption(null)}}
else if(hEvent.keyCode==40)
{if(hAC.bDebug){hAC.debug("key "+hEvent.keyCode+" down")}
hLINext=hAC.getNextDisplayedItem(hAC.hActiveSelection)
if(hLINext!=null)
{hAC.selectOption(hLINext.childNodes.item(0))
if(hAC.nItemsDisplayed>cAutocomplete.CN_NUMBER_OF_LINES)
{if(hList.scrollTop>0&&hList.scrollTop>hLINext.offsetTop)
{hList.scrollTop=0}
if(Math.abs(hLINext.offsetTop-hList.scrollTop-hList.offsetHeight)<5)
{hList.scrollTop+=hLINext.offsetHeight}}}
else
{hAC.selectOption(null)}}}
if(hInput.form)
{hInput.form.bLocked=true
if(hAC.bDebug){hAC.debug("onInputKeyDown form blocked")}}
if(hEvent.keyCode==13||hEvent.keyCode==27||hEvent.keyCode==38||hEvent.keyCode==40)
{if(hEvent.preventDefault)
{hEvent.preventDefault()}else{if(hAC.bDebug){hAC.debug("no preventDefault return false")}}
hEvent.cancelBubble=true
hEvent.returnValue=false
return false}}
cAutocomplete.onInputKeyPress=function(hEvent)
{if(hEvent.keyCode==13||hEvent.keyCode==38||hEvent.keyCode==40)
{if(hEvent.preventDefault)
{hEvent.preventDefault()}
hEvent.cancelBubble=true
hEvent.returnValue=false
return false}}
cAutocomplete.onInputKeyUp=function(hEvent)
{if(hEvent==null)
{hEvent=window.event}
var hElement=(hEvent.srcElement)?hEvent.srcElement:hEvent.originalTarget
var hAC=hElement.hAutocomplete
var hInput=document.getElementById(hAC.sInputId)
switch(hEvent.keyCode)
{case 8:if(hAC.bAutoComplete&&hAC.bAutocompleted)
{hAC.bAutocompleted=false
return false}
break
case 38:case 40:if(hAC.bListDisplayed)
{if(hEvent.preventDefault)
{hEvent.preventDefault()}
hEvent.cancelBubble=true
hEvent.returnValue=false
return false}
break
case 32:case 46:case 35:case 36:break;default:if(hEvent.keyCode<48)
{if(hEvent.preventDefault)
{hEvent.preventDefault()}
if(hAC.bDebug){hAC.debug("keyUp: hEvent.returnValue = false")}
hEvent.cancelBubble=true
hEvent.returnValue=false
return false}
break}
if(hAC.hMarkRangeTimeout!=null)
{clearTimeout(hAC.hMarkRangeTimeout)}
if(hAC.hShowTimeout)
{clearTimeout(hAC.hShowTimeout)
hAC.hShowTimeout=null}
var nTimeout=hAC.bRemoteList?cAutocomplete.CN_REMOTE_SHOW_TIMEOUT:cAutocomplete.CN_SHOW_TIMEOUT
hAC.hShowTimeout=setTimeout(function(){hAC.prepareList()},nTimeout)
if(hAC.bDebug){hAC.debug("setTimeout "+nTimeout)}}
cAutocomplete.onInputBlur=function(hEvent)
{if(hEvent==null)
{hEvent=window.event}
var hElement=(hEvent.srcElement)?hEvent.srcElement:hEvent.originalTarget
if(hElement.form)
{hElement.form.bLocked=false}
var hAC=hElement.hAutocomplete
if(!hAC.hClearTimeout)
{hAC.hClearTimeout=setTimeout(function(){hAC.clearList()},cAutocomplete.CN_CLEAR_TIMEOUT)}}
cAutocomplete.onInputFocus=function(hEvent)
{if(hEvent==null)
{hEvent=window.event}
var hElement=(hEvent.srcElement)?hEvent.srcElement:hEvent.originalTarget
var hAC=hElement.hAutocomplete
if(hAC.hClearTimeout)
{clearTimeout(hAC.hClearTimeout)
hAC.hClearTimeout=null}}
cAutocomplete.saveCaretPosition=function(hEvent)
{if(hEvent==null)
{hEvent=window.event}
var hElement=(hEvent.srcElement)?hEvent.srcElement:hEvent.originalTarget
var hAC=hElement.hAutocomplete
var hInput=document.getElementById(hAC.sInputId)
if(hEvent.keyCode!=38&&hEvent.keyCode!=40)
{hAC.nInsertPoint=cAutocomplete.getInputCaretPosition(hInput)}}
cAutocomplete.getInputCaretPosition=function(hInput)
{if(typeof hInput.selectionStart!='undefined')
{if(hInput.selectionStart==hInput.selectionEnd)
{return hInput.selectionStart}
else
{return hInput.selectionStart}}
else if(hInput.createTextRange)
{var hSelRange=document.selection.createRange()
if(hInput.tagName.toLowerCase()=='textarea')
{var hSelBefore=hSelRange.duplicate()
var hSelAfter=hSelRange.duplicate()
hSelRange.moveToElementText(hInput)
hSelBefore.setEndPoint('StartToStart',hSelRange)
return hSelBefore.text.length}
else
{hSelRange.moveStart('character',-1*hInput.value.length)
var nLen=hSelRange.text.length
return nLen}}
return null}
cAutocomplete.setInputCaretPosition=function(hInput,nPosition)
{if(hInput.setSelectionRange)
{hInput.setSelectionRange(nPosition,nPosition)}
else if(hInput.createTextRange)
{var hRange=hInput.createTextRange()
hRange.moveStart('character',nPosition)
hRange.moveEnd('character',nPosition)
hRange.collapse(true)
hRange.select()}}
cAutocomplete.markInputRange=function(hInput,nStartPos,nEndPos)
{if(hInput.setSelectionRange)
{hInput.focus()
hInput.setSelectionRange(nStartPos,nEndPos)}
else if(hInput.createTextRange)
{var hRange=hInput.createTextRange()
hRange.collapse(true)
hRange.moveStart('character',nStartPos)
hRange.moveEnd('character',nEndPos-nStartPos)
hRange.select()}}
cAutocomplete.markInputRange2=function(sInputId)
{var hInput=document.getElementById(sInputId)
var nStartPos=hInput.hAutocomplete.nStartAC
var nEndPos=hInput.hAutocomplete.nEndAC
cAutocomplete.markInputRange(hInput,nStartPos,nEndPos)}
cAutocomplete.onListBlur=function(hEvent)
{if(hEvent==null)
{hEvent=window.event}
var hElement=(hEvent.srcElement)?hEvent.srcElement:hEvent.originalTarget
hElement=getParentByProperty(hElement,'className','autocomplete_holder')
var hAC=hElement.hAutocomplete
if(!hAC.hClearTimeout)
{hAC.hClearTimeout=setTimeout(function(){hAC.clearList()},cAutocomplete.CN_CLEAR_TIMEOUT)}}
cAutocomplete.onListFocus=function(hEvent)
{if(hEvent==null)
{hEvent=window.event}
var hElement=(hEvent.srcElement)?hEvent.srcElement:hEvent.originalTarget
hElement=getParentByProperty(hElement,'className','autocomplete_holder')
var hAC=hElement.hAutocomplete
if(hAC.hClearTimeout)
{clearTimeout(hAC.hClearTimeout)
hAC.hClearTimeout=null}}
cAutocomplete.onItemClick=function(hEvent)
{if(hEvent==null)
{hEvent=window.event}
var hElement=(hEvent.srcElement)?hEvent.srcElement:hEvent.originalTarget
var hContainer=getParentByProperty(hElement,'className','autocomplete_holder')
var hEl=getParentByTagName(hElement,'A')
if(hContainer!=null)
{var hAC=hContainer.hAutocomplete
hAC.selectOption(hEl)
document.getElementById(hAC.sInputId).focus()
hAC.clearList()}
if(hEvent.preventDefault)
{hEvent.preventDefault()}
hEvent.cancelBubble=true
hEvent.returnValue=false
return false}
cAutocomplete.onButtonClick=function(hEvent)
{if(hEvent==null)
{hEvent=window.event}
var hElement=(hEvent.srcElement)?hEvent.srcElement:hEvent.originalTarget
var hAC=hElement.hAutocomplete
var hInput=document.getElementById(hAC.sInputId)
if(hInput.disabled)
{return}
if(hAC.bDebug){hAC.debug("onButtonClick")}
hAC.prepareList(true)
var hInput=document.getElementById(hAC.sInputId)
hInput.focus()}
cAutocomplete.onFormSubmit=function(hEvent)
{if(hEvent==null)
{hEvent=window.event}
var hElement=(hEvent.srcElement)?hEvent.srcElement:hEvent.originalTarget
if(hElement.bLocked)
{var hAC=hElement.hAutocomplete
if(hAC.bDebug){hAC.debug("onSubmit: hElement.bLocked")}
hElement.bLocked=false
hEvent.returnValue=false
if(hEvent.preventDefault)
{hEvent.preventDefault()}
return false}}