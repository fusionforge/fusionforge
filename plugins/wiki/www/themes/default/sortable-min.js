var image_path="/images/";var image_up="sort_up.gif";var image_down="sort_down.gif";var image_none="sort_none.gif";var europeandate=true;var alternate_row_colors=true;addEvent(window,"load",sortables_init);var SORT_COLUMN_INDEX;var thead=false;function sortables_init(){if(!document.getElementsByTagName){return}tbls=document.getElementsByTagName("table");for(ti=0;ti<tbls.length;ti++){thisTbl=tbls[ti];if(((" "+thisTbl.className+" ").indexOf("sortable")!=-1)&&(thisTbl.id)){ts_makeSortable(thisTbl)}}}function ts_makeSortable(d){if(d.rows&&d.rows.length>0){if(d.tHead&&d.tHead.rows.length>0){var e=d.tHead.rows[d.tHead.rows.length-1];thead=true}else{var e=d.rows[0]}}if(!e){return}for(var c=0;c<e.cells.length;c++){var b=e.cells[c];var a=ts_getInnerText(b);if(b.className!="unsortable"&&b.className.indexOf("unsortable")==-1){b.innerHTML='<a href="#" class="sortheader" onclick="ts_resortTable(this, '+c+');return false;">'+a+'<span class="sortarrow">&nbsp;&nbsp;<img src="'+image_path+image_none+'" alt="&darr;"/></span></a>'}}if(alternate_row_colors){alternate(d)}}function ts_getInnerText(d){if(typeof d=="string"){return d}if(typeof d=="undefined"){return d}if(d.innerText){return d.innerText}var e="";var c=d.childNodes;var a=c.length;for(var b=0;b<a;b++){switch(c[b].nodeType){case 1:e+=ts_getInnerText(c[b]);break;case 3:e+=c[b].nodeValue;break}}return e}function ts_resortTable(g,l){var n;for(var p=0;p<g.childNodes.length;p++){if(g.childNodes[p].tagName&&g.childNodes[p].tagName.toLowerCase()=="span"){n=g.childNodes[p]}}var a=ts_getInnerText(n);var b=g.parentNode;var c=l||b.cellIndex;var o=getParent(b,"TABLE");if(o.rows.length<=1){return}var h="";var e=0;while(h==""&&e<o.tBodies[0].rows.length){var h=ts_getInnerText(o.tBodies[0].rows[e].cells[c]);h=trim(h);if(h.substr(0,4)=="<!--"||h.length==0){h=""}e++}if(h==""){return}sortfn=ts_sort_caseinsensitive;if(h.match(/^\d\d[\/\.-][a-zA-z][a-zA-Z][a-zA-Z][\/\.-]\d\d\d\d$/)){sortfn=ts_sort_date}if(h.match(/^\d\d[\/\.-]\d\d[\/\.-]\d\d\d{2}?$/)){sortfn=ts_sort_date}if(h.match(/^-?[�$�ۢ�]\d/)){sortfn=ts_sort_numeric}if(h.match(/^\d+ *(B|KB|MB)$/)){sortfn=ts_sort_numeric}if(h.match(/^-?(\d+[,\.]?)+(E[-+][\d]+)?%?$/)){sortfn=ts_sort_numeric}SORT_COLUMN_INDEX=c;var d=new Array();var f=new Array();for(k=0;k<o.tBodies.length;k++){for(e=0;e<o.tBodies[k].rows[0].length;e++){d[e]=o.tBodies[k].rows[0][e]}}for(k=0;k<o.tBodies.length;k++){if(!thead){for(j=1;j<o.tBodies[k].rows.length;j++){f[j-1]=o.tBodies[k].rows[j]}}else{for(j=0;j<o.tBodies[k].rows.length;j++){f[j]=o.tBodies[k].rows[j]}}}f.sort(sortfn);if(n.getAttribute("sortdir")=="down"){ARROW='&nbsp;&nbsp;<img src="'+image_path+image_down+'" alt="&darr;"/>';f.reverse();n.setAttribute("sortdir","up")}else{ARROW='&nbsp;&nbsp;<img src="'+image_path+image_up+'" alt="&uarr;"/>';n.setAttribute("sortdir","down")}for(e=0;e<f.length;e++){if(!f[e].className||(f[e].className&&(f[e].className.indexOf("sortbottom")==-1))){o.tBodies[0].appendChild(f[e])}}for(e=0;e<f.length;e++){if(f[e].className&&(f[e].className.indexOf("sortbottom")!=-1)){o.tBodies[0].appendChild(f[e])}}var m=document.getElementsByTagName("span");for(var p=0;p<m.length;p++){if(m[p].className=="sortarrow"){if(getParent(m[p],"table")==getParent(g,"table")){m[p].innerHTML='&nbsp;&nbsp;<img src="'+image_path+image_none+'" alt="&darr;"/>'}}}n.innerHTML=ARROW;alternate(o)}function getParent(b,a){if(b==null){return null}else{if(b.nodeType==1&&b.tagName.toLowerCase()==a.toLowerCase()){return b}else{return getParent(b.parentNode,a)}}}function sort_date(b){dt="00000000";if(b.length==11){mtstr=b.substr(3,3);mtstr=mtstr.toLowerCase();switch(mtstr){case"jan":var a="01";break;case"feb":var a="02";break;case"mar":var a="03";break;case"apr":var a="04";break;case"may":var a="05";break;case"jun":var a="06";break;case"jul":var a="07";break;case"aug":var a="08";break;case"sep":var a="09";break;case"oct":var a="10";break;case"nov":var a="11";break;case"dec":var a="12";break}dt=b.substr(7,4)+a+b.substr(0,2);return dt}else{if(b.length==10){if(europeandate==false){dt=b.substr(6,4)+b.substr(0,2)+b.substr(3,2);return dt}else{dt=b.substr(6,4)+b.substr(3,2)+b.substr(0,2);return dt}}else{if(b.length==8){yr=b.substr(6,2);if(parseInt(yr)<50){yr="20"+yr}else{yr="19"+yr}if(europeandate==true){dt=yr+b.substr(3,2)+b.substr(0,2);return dt}else{dt=yr+b.substr(0,2)+b.substr(3,2);return dt}}}}return dt}function ts_sort_date(d,c){dt1=sort_date(ts_getInnerText(d.cells[SORT_COLUMN_INDEX]));dt2=sort_date(ts_getInnerText(c.cells[SORT_COLUMN_INDEX]));if(dt1==dt2){return 0}if(dt1<dt2){return -1}return 1}function ts_sort_numeric(d,c){var e=ts_getInnerText(d.cells[SORT_COLUMN_INDEX]);e=clean_num(e);var f=ts_getInnerText(c.cells[SORT_COLUMN_INDEX]);f=clean_num(f);return compare_numeric(e,f)}function compare_numeric(d,c){var e=parseFloat(d);e=(isNaN(e)?0:e);var f=parseFloat(c);f=(isNaN(f)?0:f);return e-f}function ts_sort_caseinsensitive(d,c){var e=ts_getInnerText(d.cells[SORT_COLUMN_INDEX]).toLowerCase();var f=ts_getInnerText(c.cells[SORT_COLUMN_INDEX]).toLowerCase();if(e==f){return 0}if(e<f){return -1}return 1}function ts_sort_default(d,c){var e=ts_getInnerText(d.cells[SORT_COLUMN_INDEX]);var f=ts_getInnerText(c.cells[SORT_COLUMN_INDEX]);if(e==f){return 0}if(e<f){return -1}return 1}function addEvent(e,d,b,a){if(e.addEventListener){e.addEventListener(d,b,a);return true}else{if(e.attachEvent){var c=e.attachEvent("on"+d,b);return c}else{alert("Handler could not be removed");return false}}}function clean_num(a){a=a.replace(new RegExp(/[^-?0-9.]/g),"");return a}function trim(a){return a.replace(/^\s+|\s+$/g,"")}function alternate(e){var c=e.getElementsByTagName("tbody");for(var b=0;b<c.length;b++){var d=c[b].getElementsByTagName("tr");for(var a=0;a<d.length;a++){if((a%2)==0){if(!(d[a].className.indexOf("odd")==-1)){d[a].className=d[a].className.replace("odd","even")}else{if(d[a].className.indexOf("even")==-1){d[a].className+=" even"}}}else{if(!(d[a].className.indexOf("even")==-1)){d[a].className=d[a].className.replace("even","odd")}else{if(d[a].className.indexOf("odd")==-1){d[a].className+=" odd"}}}}}};