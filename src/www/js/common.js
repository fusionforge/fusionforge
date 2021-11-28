/**
 * FusionForge
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

function admin_window(adminurl) {
	var AdminWin = window.open( adminurl, 'AdminWindow','scrollbars=yes,resizable=yes, toolbar=yes, height=400, width=400, top=2, left=2');
	AdminWin.focus();
}

function help_window(helpurl) {
	var HelpWin = window.open( helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=600');
}

function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

function toggledisplay(a,list) {
	var elem = document.getElementById(list);
	var open = '/images/folderArrowOpen.png';
	var close = '/images/folderArrowClosed.png';
	if (elem.style.display=='none') {
		elem.style.display = 'block';
		a.title = 'Click to display only admins';
		a.src = open;
	} else {
		elem.style.display='none';
		a.title = 'Click to display all members';
		a.src = close;
	}
}

function switch2edit (a,show,edit) {
	var elemshow = document.getElementById(show);
	var elemedit = document.getElementById(edit);
	if (elemedit.style.display=='none') {
		elemedit.style.display = 'block';
		elemshow.style.display = 'none';
		a.style.display='none';
	}
 }

function switch2display (a,bt,disp,i) {
	var elembt1 = document.getElementById(bt + '1_' + i);
	var elemdisp1 = document.getElementById(disp + '1_' + i);
	var elembt2 = document.getElementById(bt + '2_' + i);
	var elemdisp2 = document.getElementById(disp + '2_' + i);
	
	if (elemdisp1.style.display=='none') {
		elembt1.style.display = 'inline';
		elemdisp1.style.display = 'block';
		elembt2.style.display = 'none';
		elemdisp2.style.display='none';
	} else {
		elembt1.style.display = 'none';
		elemdisp1.style.display = 'none';
		elembt2.style.display = 'inline';
		elemdisp2.style.display='block';
	}
}

function checkAllArtifacts(val) {
	var al=document.artifactList;
	var len = al.elements.length;
	for(var i=0; i<len; i++) {
		if (al.elements[i].name=='artifact_id_list[]') {
			al.elements[i].checked=val;
		}
	}
}

function checkAllTasks(val) {
	var al=document.taskList;
	var len = al.elements.length;
	for(var i=0; i<len; i++) {
		if (al.elements[i].name=='project_task_id_list[]') {
			al.elements[i].checked=val;
		}
	}
}

function flipAll(formObj) {
	var isFirstSet = -1;
	var fldObj;
	for (var i=0; i < formObj.length; i++) {
		fldObj = formObj.elements[i];
		if ((fldObj.type == 'checkbox') && (fldObj.name.substring(0,2) == 'p[')) {
			if (isFirstSet == -1) {
				isFirstSet = (fldObj.checked) ? true : false;
			}
			fldObj.checked = (isFirstSet) ? false : true;
		}
	}
}

function submitOrder() {
	var order = document.getElementById('order_span').getAttribute('sortdir');
	var myRand = Math.round(Math.random()*(1000000));
	document.getElementById('img_order').src = '/tracker/setorder.php?' + 'order=' + order + '&rand=' + myRand;
}
