// Toolbar JavaScript support functions. Taken from mediawiki 
// $Id: toolbar.js 7686 2010-09-13 12:41:32Z vargenau $

// Some "constants"
var doctype = '<?xml version="1.0" encoding="utf-8"?>\n<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
var cssfile = '<link rel="stylesheet" type="text/css" href="'+data_path+'/themes/default/toolbar.css" />'

// Un-trap us from framesets
if( window.top != window ) window.top.location = window.location;
var pullwin;

// This function generates the actual toolbar buttons with localized text
// We use it to avoid creating the toolbar where javascript is not enabled
// Not all buttons use this helper, some need special javascript treatment.
function addButton(imageFile, speedTip, func, args) {
  var i;
  speedTip=escapeQuotes(speedTip);
  document.write("<a href=\"javascript:"+func+"(");
  for (i=0; i<args.length; i++){
    if (i>0) document.write(",");
    document.write("'"+escapeQuotes(args[i])+"'");
  }
  //width=\"23\" height=\"22\"
  document.write(");\"><img src=\""+imageFile+"\" width=\"18\" height=\"18\" border=\"0\" alt=\""+speedTip+"\" title=\""+speedTip+"\">");
  document.write("</a>");
  return;
}
function addTagButton(imageFile, speedTip, tagOpen, tagClose, sampleText) {
  addButton(imageFile, speedTip, "insertTags", [tagOpen, tagClose, sampleText]);
  return;
}

// This function generates a popup list to select from. 
// In an external window so far, but we really want that as acdropdown pulldown, hence the name.
//   plugins, pagenames, categories, templates. 
// Not with document.write because we cannot use self.opener then.
//function addPulldown(imageFile, speedTip, pages) {
//  addButton(imageFile, speedTip, "showPulldown", pages);
//  return;
//}
// pages is either an array of strings or an array of array(name,value)
function showPulldown(title, pages, okbutton, closebutton, fromid) {
  var height = new String(Math.min(315, 80 + (pages.length * 12))); // 270 or smaller
  var width = 500;
  var h = (screen.height-height)/2;
  var w = (screen.width-width)/2;
  pullwin = window.open('','','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,top='+h+',left='+w+',height='+height+',width='+width);
  pullwin.window.document.writeln(doctype);
  pullwin.window.document.writeln('<html>\n<head>\n<title>'+escapeQuotes(title)+'</title>');
  pullwin.window.document.writeln(cssfile);
  pullwin.window.document.writeln('</head>\n<body>');
  pullwin.window.document.writeln('<p>\nYou can double-click to insert.\n</p>');
  pullwin.window.document.writeln('<form action=\"\"><div id=\"buttons\"><input type=\"button\" value=\"'+okbutton+'\" onclick=\"if(self.opener)self.opener.do_pulldown(document.forms[0].select.value,\''+fromid+'\'); return false;\" /><input type=\"button\" value=\"'+closebutton+'\" onclick=\"self.close(); return false;\" /></div>\n<div>\n<select style=\"margin-top:10px;width:190px;\" name=\"select\" size=\"'+((pages.length>20)?'20':new String(pages.length))+'\" ondblclick=\"if(self.opener)self.opener.do_pulldown(document.forms[0].select.value,\''+fromid+'\'); return false;\">');
  for (i=0; i<pages.length; i++){
    if (typeof pages[i] == 'string')
      pullwin.window.document.write('<option value="'+pages[i]+'">'+escapeQuotes(pages[i])+'</option>\n');
    else  // array=object
      pullwin.window.document.write('<option value="'+pages[i][1]+'">'+escapeQuotes(pages[i][0])+'</option>\n');
  }
  pullwin.window.document.writeln('</select>\n</div>\n</form>\n</body>\n</html>');
  pullwin.window.document.close();
  return false;
}
function do_pulldown(text,fromid) {
    // do special actions dependent on fromid: tb-categories
    if (fromid == 'tb-categories') {
	var txtarea = document.getElementById('edit-content');
	text = unescapeSpecial(text)
	txtarea.value += '\n'+text;
    } else {
	insertTags(text, '', '\n');
    }
    return;
}
function addInfobox(infoText) {
  // if no support for changing selection, add a small copy & paste field
  var clientPC = navigator.userAgent.toLowerCase(); // Get client info
  var is_nav = ((clientPC.indexOf('gecko')!=-1) && (clientPC.indexOf('spoofer')==-1)
                && (clientPC.indexOf('khtml') == -1));
  if(!document.selection && !is_nav) {
    infoText=escapeQuotesHTML(infoText);
    document.write("<form name='infoform' id='infoform'>"+
		   "<input size=80 id='infobox' name='infobox' value=\""+
		   infoText+"\" readonly=\"readonly\"></form>");
  }
}
function escapeQuotes(text) {
  var re=new RegExp("'","g");
  text=text.replace(re,"\\'");
  re=new RegExp('"',"g");
  text=text.replace(re,'&quot;');
  re=new RegExp("\\n","g");
  text=text.replace(re,"\\n");
  return text;
}
function escapeQuotesHTML(text) {
  var re=new RegExp('"',"g");
  text=text.replace(re,"&quot;");
  return text;
}
function unescapeSpecial(text) {
    // IE
    var re=new RegExp('%0A',"g");
    text = text.replace(re,'\n');
    var re=new RegExp('%22',"g");
    text = text.replace(re,'"');
    var re=new RegExp('%27',"g");
    text = text.replace(re,'\'');
    var re=new RegExp('%09',"g");
    text = text.replace(re,'    ');
    var re=new RegExp('%7C',"g");
    text = text.replace(re,'|');
    var re=new RegExp('%5B',"g");
    text = text.replace(re,'[');
    var re=new RegExp('%5D',"g");
    text = text.replace(re,']');
    var re=new RegExp('%5C',"g");
    text = text.replace(re,'\\');
    return text;
}

// apply tagOpen/tagClose to selection in textarea,
// use sampleText instead of selection if there is none
// copied and adapted from phpBB
function insertTags(tagOpen, tagClose, sampleText) {
  //f=document.getElementById('editpage');
  var txtarea = document.getElementById('edit-content');
  // var txtarea = document.editpage.edit[content];
  tagOpen = unescapeSpecial(tagOpen)

  if(document.selection) {
    var theSelection = document.selection.createRange().text;
    if(!theSelection) { theSelection=sampleText;}
    txtarea.focus();
    if(theSelection.charAt(theSelection.length - 1) == " "){// exclude ending space char, if any
      theSelection = theSelection.substring(0, theSelection.length - 1);
      document.selection.createRange().text = tagOpen + theSelection + tagClose + " ";
    } else {
      document.selection.createRange().text = tagOpen + theSelection + tagClose;
    }
    // Mozilla -- this induces a scrolling bug which makes it virtually unusable
  } else if(txtarea.selectionStart || txtarea.selectionStart == '0') {
    var startPos = txtarea.selectionStart;
    var endPos = txtarea.selectionEnd;
    var scrollTop=txtarea.scrollTop;
    var myText = (txtarea.value).substring(startPos, endPos);
    if(!myText) { myText=sampleText;}
    if(myText.charAt(myText.length - 1) == " "){ // exclude ending space char, if any
      subst = tagOpen + myText.substring(0, (myText.length - 1)) + tagClose + " "; 
    } else {
      subst = tagOpen + myText + tagClose; 
    }
    txtarea.value = txtarea.value.substring(0, startPos) + subst + txtarea.value.substring(endPos, txtarea.value.length);
    txtarea.focus();
    var cPos=startPos+(tagOpen.length+myText.length+tagClose.length);
    txtarea.selectionStart=cPos;
    txtarea.selectionEnd=cPos;
    txtarea.scrollTop=scrollTop;
    // All others
  } else {
    // Append at the end: Some people find that annoying
    txtarea.value += tagOpen + sampleText + tagClose;
    //txtarea.focus();
    //var re=new RegExp("\\n","g");
    //tagOpen=tagOpen.replace(re,"");
    //tagClose=tagClose.replace(re,"");
    //document.infoform.infobox.value=tagOpen+sampleText+tagClose;
    txtarea.focus();
  }
  // reposition cursor if possible
  if (txtarea.createTextRange) txtarea.caretPos = document.selection.createRange().duplicate();
}

// JS_SEARCHREPLACE from walterzorn.de
var f, sr_undo, replacewin, undo_buffer=new Array(), undo_buffer_index=0;

function define_f() {
   f=document.getElementById('editpage')
   f.editarea=document.getElementById('edit-content')
   sr_undo=document.getElementById('sr_undo')
   undo_enable(false)
   f.editarea.focus()
}
function undo_enable(bool) {
   if (bool) {
     sr_undo.src=uri_undo_btn
     sr_undo.alt=msg_undo_alt
     sr_undo.disabled = false
   } else {
       sr_undo.src=uri_undo_d_btn
       sr_undo.alt=msg_undo_d_alt
       sr_undo.disabled = true
       if(sr_undo.blur) sr_undo.blur();
   }
}
function replace() {
   var height = 120;
   var width = 600;
   var h = (screen.height-height)/2;
   var w = (screen.width-width)/2;
   replacewin = window.open('','','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,top='+h+',left='+w+',height='+height+',width='+width);
   replacewin.window.document.writeln(doctype);
   replacewin.window.document.writeln('<html>\n<head>\n<title>'+msg_repl_title+'</title>');
   replacewin.window.document.writeln(cssfile);
   replacewin.window.document.writeln('</head>');
   replacewin.window.document.writeln("<body onload=\"if(document.forms[0].searchinput.focus) document.forms[0].searchinput.focus(); return false;\">\n<form action=\"\">\n<center>\n<table>\n<tr>\n<td align=\"right\">"+msg_repl_search+":\n</td>\n<td align=\"left\">\n<input type=\"text\" name=\"searchinput\" size=\"45\" maxlength=\"500\" />\n</td>\n</tr>\n<tr>\n<td align=\"right\">"+msg_repl_replace_with+":\n</td>\n<td align=\"left\">\n<input type=\"text\" name=\"replaceinput\" size=\"45\" maxlength=\"500\" />\n</td>\n</tr>\n<tr>\n<td colspan=\"2\" align=\"center\">\n<input type=\"button\" value=\" "+msg_repl_ok+" \" onclick=\"if(self.opener)self.opener.do_replace(); return false;\" />&nbsp;&nbsp;&nbsp;<input type=\"button\" value=\""+msg_repl_close+"\" onclick=\"self.close(); return false;\" />\n</td>\n</tr>\n</table>\n</center>\n</form>\n</body>\n</html>");
   replacewin.window.document.close();
   return false;
}
function do_replace() {
   var txt = undo_buffer[undo_buffer_index]=f.editarea.value
   var searchinput = new RegExp(replacewin.document.forms[0].searchinput.value,'g')
   var replaceinput = replacewin.document.forms[0].replaceinput.value
   if (searchinput==''||searchinput==null) {
      if (replacewin) replacewin.window.document.forms[0].searchinput.focus();
      return;
   }
   var z_repl=txt.match(searchinput)? txt.match(searchinput).length : 0;
   txt=txt.replace(searchinput,replaceinput);
   searchinput=searchinput.toString().substring(1,searchinput.toString().length-2);
   msg_replfound = msg_replfound.replace('\1', searchinput).replace('\2', z_repl).replace('\3', replaceinput)
   msg_replnot = msg_replnot.replace('%s', searchinput)
   result(z_repl, msg_replfound, txt, msg_replnot);
   replacewin.window.focus();
   replacewin.window.document.forms[0].searchinput.focus();
   return false;
}
function result(count,question,value_txt,alert_txt) {
   if (count>0) {
      if(window.confirm(question)==true) {
         f.editarea.value=value_txt;
         undo_save();
         undo_enable(true);
      }
   } else {
       alert(alert_txt);
   }
}
function do_undo() {
   if(undo_buffer_index==0) return;
   else if(undo_buffer_index>0) {
      f.editarea.value=undo_buffer[undo_buffer_index-1];
      undo_buffer[undo_buffer_index]=null;
      undo_buffer_index--;
      if(undo_buffer_index==0) {
         alert(msg_do_undo);
         undo_enable(false);
      }
   }
}
//save a snapshot in the undo buffer
function undo_save() {
   undo_buffer[undo_buffer_index]=f.editarea.value;
   undo_buffer_index++;
   undo_enable(true);
}
