// Common Javascript support functions.
// $Id: wikicommon.js 7686 2010-09-13 12:41:32Z vargenau $

/* Globals:
var data_path = '/phpwiki';
var pagename  = 'HomePage';
var script_url= '/wiki';
var stylepath = data_path+'/themes/MonoBook/';
var folderArrowPath = data_path+'/themes/default/images';
var use_path_info = true;
*/

function escapeQuotes(text) {
  var re=new RegExp("'","g");
  text=text.replace(re,"\\'");
  re=new RegExp('"',"g");
  text=text.replace(re,'&quot;');
  re=new RegExp("\\n","g");
  text=text.replace(re,"\\n");
  return text;
}

function WikiURL(page) {
    if (typeof page == "undefined")
        page = pagename;
    if (use_path_info) {
        return script_url + '/' + escapeQuotes(page) + '?';
    } else {
        return script_url + '?pagename=' + escapeQuotes(page) + '&';
    }
}

function flipAll(formObj) {
  var isFirstSet = -1;
  for (var i=0; i < formObj.length; i++) {
      fldObj = formObj.elements[i];
      if ((fldObj.type == 'checkbox') && (fldObj.name.substring(0,2) == 'p[')) {
         if (isFirstSet == -1)
           isFirstSet = (fldObj.checked) ? true : false;
         fldObj.checked = (isFirstSet) ? false : true;
       }
   }
}

function toggletoc(a, open, close, toclist) {
  var toc=document.getElementById(toclist)
  if (toc.style.display=='none') {
    toc.style.display='block'
    a.title='"._("Click to hide the TOC")."'
    a.src = open
  } else {
    toc.style.display='none';
    a.title='"._("Click to display")."'
    a.src = close
  }
}

// Global external objects used by this script.
/*extern ta, stylepath, skin */

// add any onload functions in this hook (please don't hard-code any events in the xhtml source)
var doneOnloadHook;

if (!window.onloadFuncts) {
	var onloadFuncts = [];
}

function addOnloadHook(hookFunct) {
	// Allows add-on scripts to add onload functions
	onloadFuncts[onloadFuncts.length] = hookFunct;
}

function hookEvent(hookName, hookFunct) {
	if (window.addEventListener) {
		window.addEventListener(hookName, hookFunct, false);
	} else if (window.attachEvent) {
		window.attachEvent("on" + hookName, hookFunct);
	}
}

// Todo: onloadhook to re-establish folder state in pure js, no cookies. same for toc.
function showHideFolder(id) {
    var div = document.getElementById(id+'-body');
    if ( div == null) return;
    var img = document.getElementById(id+'-img');
    var expires = new Date(); // 30 days
    expires.setTime(expires.getTime() + (30 * 24 * 60 * 60 * 1000));
    var suffix = " expires="+expires.toGMTString();   //+"; path="+data_path;
    //todo: invalidate cache header
    if ( div.style.display == 'none' ) {
        div.style.display = 'block';
        img.src = folderArrowPath + '/folderArrowOpen.png';
        document.cookie = "folder_"+id+"=Open;"+suffix;
    } else {
        div.style.display = 'none';
        img.src = folderArrowPath + '/folderArrowClosed.png';
        document.cookie = "folder_"+id+"=Closed;"+suffix;
    }
}

function setupshowHideFolder() {
    var ids = ["p-tb", "p-tbx", "p-tags", "p-rc" /*,"toc"*/];
    for (var i = 0; i < ids.length; i++) {
        if (ids[i]) {
            var id = ids[i];
            var cookieStr = "folder_"+id+"=";
            var cookiePos = document.cookie.indexOf(cookieStr);
            if (cookiePos > -1) {
                var body = document.getElementById(id+'-body');
                if (body) body.style.display = document.cookie.charAt(cookiePos + cookieStr.length) == "C" ? 'block' : 'none';
                showHideFolder(id);
            }
        }
    }
}

hookEvent("load", setupshowHideFolder);

function runOnloadHook() {
	// don't run anything below this for non-dom browsers
	if (doneOnloadHook || !(document.getElementById && document.getElementsByTagName)) {
		return;
	}

	// set this before running any hooks, since any errors below
	// might cause the function to terminate prematurely
	doneOnloadHook = true;

	sortables_init();

	// Run any added-on functions
	for (var i = 0; i < onloadFuncts.length; i++) {
		onloadFuncts[i]();
	}
}

//note: all skins should call runOnloadHook() at the end of html output,
//      so the below should be redundant. It's there just in case.
hookEvent("load", runOnloadHook);

//hookEvent("load", mwSetupToolbar);

// This script was provided for free by
// http://www.howtocreate.co.uk/tutorials/javascript/domcss
// See http://www.howtocreate.co.uk/jslibs/termsOfUse.html
function getAllSheets() {
  if( !window.ScriptEngine && navigator.__ice_version ) { return document.styleSheets; }
  if( document.getElementsByTagName ) { var Lt = document.getElementsByTagName('link'), St = document.getElementsByTagName('style');
  } else if( document.styleSheets && document.all ) { var Lt = document.all.tags('LINK'), St = document.all.tags('STYLE');
  } else { return []; } for( var x = 0, os = []; Lt[x]; x++ ) {
    var rel = Lt[x].rel ? Lt[x].rel : Lt[x].getAttribute ? Lt[x].getAttribute('rel') : '';
    if( typeof( rel ) == 'string' && rel.toLowerCase().indexOf('style') + 1 ) { os[os.length] = Lt[x]; }
  } for( var x = 0; St[x]; x++ ) { os[os.length] = St[x]; } return os;
}
function changeStyle() {
  for( var x = 0, ss = getAllSheets(); ss[x]; x++ ) {
    if( ss[x].title ) { ss[x].disabled = true; }
    for( var y = 0; y < arguments.length; y++ ) {
     if( ss[x].title == arguments[y] ) { ss[x].disabled = false; }
} } }
function PrinterStylesheet() {
  changeStyle('Printer');
}
