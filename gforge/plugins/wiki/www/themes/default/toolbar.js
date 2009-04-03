// Toolbar JavaScript support functions. Taken from mediawiki 
// $Id: toolbar.js 6204 2008-08-26 15:12:03Z vargenau $

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
  height = new String(Math.min(315, 80 + (pages.length * 12))); // 270 or smaller
  pullwin = window.open('','','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,height='+height+',width=200');
  pullwin.window.document.write('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><title>'+escapeQuotes(title)+'</title><style type=\"text/css\"><'+'!'+'-- body {font-family:Tahoma,Arial,Helvetica,sans-serif;font-size:10pt;background-color:#dddddd;} input { font-weight:bold;margin-left:2px;margin-right:2px;} option {font-size:9pt} #buttons { background-color:#dddddd;padding-right:10px;width:180px;} --'+'></style></head>');
  pullwin.window.document.write('\n<body bgcolor=\"#dddddd\"><form action=\"\"><div id=\"buttons\"><input type=\"button\" value=\"'+okbutton+'\" onclick=\"if(self.opener)self.opener.do_pulldown(document.forms[0].select.value,\''+fromid+'\'); return false;\"><input type=\"button\" value=\"'+closebutton+'\" onClick=\"self.close(); return false;\"></div>\n<select style=\"margin-top:10px;width:190px;\" name=\"select\" size=\"'+((pages.length>20)?'20':new String(pages.length))+'\" onDblClick=\"if(self.opener)self.opener.do_pulldown(document.forms[0].select.value,\''+fromid+'\'); return false;\">');
  for (i=0; i<pages.length; i++){
    if (typeof pages[i] == 'string')
      pullwin.window.document.write('<option value="'+pages[i]+'">'+escapeQuotes(pages[i])+'</option>\n');
    else  // array=object
      pullwin.window.document.write('<option value="'+pages[i][1]+'">'+escapeQuotes(pages[i][0])+'</option>\n');
  }
  pullwin.window.document.write('</select></form></body></html>');
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
   replacewin = window.open('','','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,height=90,width=450');
   replacewin.window.document.write('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><title>'+msg_repl_title+"</title><style type=\"text/css\"><'+'!'+'-- body, input {font-family:Tahoma,Arial,Helvetica,sans-serif;font-size:10pt;font-weight:bold;} td {font-size:9pt}  --'+'></style></head><body bgcolor=\"#dddddd\" onload=\"if(document.forms[0].searchinput.focus) document.forms[0].searchinput.focus(); return false;\"><form action=\"\"><center><table><tr><td align=\"right\">"+msg_repl_search+":</td><td align=\"left\"><input type=\"text\" name=\"searchinput\" size=\"45\" maxlength=\"500\"></td></tr><tr><td align=\"right\">"+msg_repl_replace_with+":</td><td align=\"left\"><input type=\"text\" name=\"replaceinput\" size=\"45\" maxlength=\"500\"></td></tr><tr><td colspan=\"2\" align=\"center\"><input type=\"button\" value=\" "+msg_repl_ok+" \" onclick=\"if(self.opener)self.opener.do_replace(); return false;\">&nbsp;&nbsp;&nbsp;<input type=\"button\" value=\""+msg_repl_close+"\" onclick=\"self.close(); return false;\"></td></tr></table></center></form></body></html>");
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
