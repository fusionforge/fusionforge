<?php
	global $groups_enabled,$WORK_DAY_START_HOUR,$WORK_DAY_END_HOUR;
?><script type="text/javascript">
<!-- <![CDATA[
// do a little form verifying
function validate_and_submit () {
  for(i=0;i < document.getElementsByName("part").length;i++) {
        document.getElementsByName("part")[i].selected = true;
  }
  if ( document.editentryform.name.value == "" ) {
    document.editentryform.name.select ();
<?php
    if ( empty ( $GLOBALS['EVENT_EDIT_TABS'] ) ||
      $GLOBALS['EVENT_EDIT_TABS'] == 'Y' ) { ?>
    showTab ( "details" );
<?php } ?>
    document.editentryform.name.focus ();
    alert ( "<?php etranslate("You have not entered a Brief Description")?>." );
    return false;
  }
  // Leading zeros seem to confuse parseInt()
  if ( document.editentryform.hour.value.charAt ( 0 ) == '0' )
    document.editentryform.hour.value = document.editentryform.hour.value.substring ( 1, 2 );
  if ( document.editentryform.timetype.selectedIndex == 1 ) {
    h = parseInt ( document.editentryform.hour.value );
    m = parseInt ( document.editentryform.minute.value );
<?php if ($GLOBALS["TIME_FORMAT"] == "12") { ?>
    if ( document.editentryform.ampm[1].checked ) {
      // pm
      if ( h < 12 )
        h += 12;
    } else {
      // am
      if ( h == 12 )
        h = 0;
    }
<?php } ?>
    if ( h >= 24 || m > 59 ) {
<?php
      if ( empty ( $GLOBALS['EVENT_EDIT_TABS'] ) ||
        $GLOBALS['EVENT_EDIT_TABS'] == 'Y' ) { ?>
        showTab ( "details" );
<?php } ?>
      alert ( "<?php etranslate ("You have not entered a valid time of day")?>." );
      document.editentryform.hour.select ();
      document.editentryform.hour.focus ();
      return false;
    }
    // Ask for confirmation for time of day if it is before the user's
<?php
      if ( empty ( $GLOBALS['EVENT_EDIT_TABS'] ) ||
        $GLOBALS['EVENT_EDIT_TABS'] == 'Y' ) { ?>
        showTab ( "details" );
<?php } ?>
    // preference for work hours.
    <?php if ($GLOBALS["TIME_FORMAT"] == "24") {
      echo "if ( h < $WORK_DAY_START_HOUR  ) {";
    }  else {
      echo "if ( h < $WORK_DAY_START_HOUR && document.editentryform.ampm[0].checked ) {";
    }
    ?>
    if ( ! confirm ( "<?php etranslate ("The time you have entered begins before your preferred work hours.  Is this correct?")?> "))
      return false;
  }
  }
  // is there really a change?
  changed = false;
  form=document.editentryform;
  for ( i = 0; i < form.elements.length; i++ ) {
    field = form.elements[i];
    switch ( field.type ) {
      case "radio":
      case "checkbox":
        if ( field.checked != field.defaultChecked )
          changed = true;
        break;
      case "text":
//      case "textarea":
        if ( field.value != field.defaultValue )
          changed = true;
        break;
      case "select-one":
//      case "select-multiple":
        for( j = 0; j < field.length; j++ ) {
          if ( field.options[j].selected != field.options[j].defaultSelected )
            changed = true;
        }
        break;
    }
  }
  if ( changed ) {
    form.entry_changed.value = "yes";
  }
//Add code to make HTMLArea code stick in TEXTAREA
 if (typeof editor != "undefined") editor._textArea.value = editor.getHTML();
  // would be nice to also check date to not allow Feb 31, etc...
  document.editentryform.submit ();
  return true;
}

function selectDate (  day, month, year, current, evt ) {
  // get currently selected day/month/year
  monthobj = eval ( 'document.editentryform.' + month );
  curmonth = monthobj.options[monthobj.selectedIndex].value;
  yearobj = eval ( 'document.editentryform.' + year );
  curyear = yearobj.options[yearobj.selectedIndex].value;
  date = curyear;

		if (document.getElementById) {
    mX = evt.clientX   + 40;
    mY = evt.clientY  + 120;
  }
  else {
    mX = evt.pageX + 40;
    mY = evt.pageY +130;
  }
	var MyPosition = 'scrollbars=no,toolbar=no,left=' + mX + ',top=' + mY + ',screenx=' + mX + ',screeny=' + mY ;
  if ( curmonth < 10 )
    date += "0";
  date += curmonth;
  date += "01";
  url = "datesel.php?form=editentryform&fday=" + day +
    "&fmonth=" + month + "&fyear=" + year + "&date=" + date;
  var colorWindow = window.open(url,"DateSelection","width=300,height=200,"  + MyPosition);
}

<?php if ( $groups_enabled == "Y" ) { 
?>function selectUsers () {
  // find id of user selection object
  var listid = 0;
  for ( i = 0; i < document.editentryform.elements.length; i++ ) {
    if ( document.editentryform.elements[i].name == "participants[]" )
      listid = i;
  }
  url = "usersel.php?form=editentryform&listid=" + listid + "&users=";
  // add currently selected users
  for ( i = 0, j = 0; i < document.editentryform.elements[listid].length; i++ ) {
    if ( document.editentryform.elements[listid].options[i].selected ) {
      if ( j != 0 )
	       url += ",";
      j++;
      url += document.editentryform.elements[listid].options[i].value;
    }
  }
  //alert ( "URL: " + url );
  // open window
  window.open ( url, "UserSelection",
    "width=500,height=500,resizable=yes,scrollbars=yes" );
}
<?php } ?>

<?php	// This function is called when the event type combo box 
	// is changed. If the user selectes "untimed event" or "all day event",
	// the times & duration fields are hidden.
	// If they change their mind & switch it back, the original 
	// values are restored for them
?>function timetype_handler () {
  var i = document.editentryform.timetype.selectedIndex;
  var val = document.editentryform.timetype.options[i].text;
  //alert ( "val " + i + " = " + val );
  // i == 1 when set to timed event
  if ( i != 1 ) {
    // Untimed/All Day
    makeInvisible ( "timeentrystart" );
    if ( document.editentryform.duration_h ) {
      makeInvisible ( "timeentryduration" );
    } else {
      makeInvisible ( "timeentryend" );
    }
  } else {
    // Timed Event
    makeVisible ( "timeentrystart" );
    if ( document.editentryform.duration_h ) {
      makeVisible ( "timeentryduration" );
    } else {
      makeVisible ( "timeentryend" );
    }
  }
}

function rpttype_handler () {
  var i = document.editentryform.rpttype.selectedIndex;
  var val = document.editentryform.rpttype.options[i].text;
  //alert ( "val " + i + " = " + val );
  //i == 0 when event does not repeat
  if ( i != 0 ) {
    // none (not repeating)
    makeVisible ( "rptenddate" );
    makeVisible ( "rptfreq" );
    if ( i == 2 ) {
      makeVisible ( "rptday" );
    } else {
      makeInvisible ( "rptday" );
    }
  } else {
    // Timed Event
    makeInvisible ( "rptenddate" );
    makeInvisible ( "rptfreq" );
    makeInvisible ( "rptday" );
  }
}

<?php //see the showTab function in includes/js/visible.php for common code shared by all pages
	//using the tabbed GUI.
?>
var tabs = new Array();
tabs[0] = "details";
tabs[1] = "participants";
tabs[2] = "pete";

var sch_win;

function getUserList () {
  var listid = 0;
  for ( i = 0; i < document.editentryform.elements.length; i++ ) {
    if ( document.editentryform.elements[i].name == "participants[]" )
      listid = i;
  }
  return listid;
}

// Show Availability for the first selection
function showSchedule () {
  //var agent=navigator.userAgent.toLowerCase();
  //var agent_isIE=(agent.indexOf("msie") > -1);
  var myForm = document.editentryform;
  var userlist = myForm.elements[getUserList()];
  var delim = '';
  var users = '';
  var cols = <?php echo $WORK_DAY_END_HOUR - $WORK_DAY_START_HOUR ?>;
  //var w = 140 + ( cols * 31 );
  var w = 760;
  var h = 180;
  for ( i = 0; i < userlist.length; i++ ) {
    if (userlist.options[i].selected) {
      users += delim + userlist.options[i].value;
      delim = ',';
      h += 18;
    }
  }
  if (users == '') {
    alert("<?php etranslate("Please add a participant")?>" );
    return false;
  }
  var features = 'width='+ w +',height='+ h +',resizable=yes,scrollbars=no';
  var url = 'availability.php?users=' + users + 
           '&year='  + myForm.year.value + 
           '&month=' + myForm.month.value + 
           '&day='   + myForm.day.options[myForm.day.selectedIndex].text;

  if (sch_win != null && !sch_win.closed) {
     h = h + 30;
     sch_win.location.replace( url );
     sch_win.resizeTo(w,h);
  } else {
     sch_win = window.open( url, "showSchedule", features );
  }
}

function listRole(project){

    var xhr=null;
    
    document.getElementById("hideRole").innerHTML = "";
    document.getElementById("hideUser").innerHTML = "";
    document.getElementById("hidebRole").innerHTML = "";
    document.getElementById("hidebUser").innerHTML = "";
    
    if (window.XMLHttpRequest) { 
        xhr = new XMLHttpRequest();
    }
    else if (window.ActiveXObject) 
    {
        xhr = new ActiveXObject("Microsoft.XMLHTTP");
    }
    //on définit l'appel de la fonction au retour serveur
    xhr.onreadystatechange = function() { genRole(xhr,project); };
    
    
    //on appelle le fichier reponse.txt
    xhr.open("GET", "list_roles_projects.php?groupe="+project, true);
    xhr.send(null);
}

function genRole(xhr,project)
{
    if (xhr.readyState==4) 
    {
    	var docXML= xhr.responseXML;
    	var items = docXML.getElementsByTagName("role");
    	var text = "<select name=\"role\">\n"+
    	           "<option value=\"null\"> </option>\n";
    	for (i=0;i<items.length;i++)
    	{
         text=text+"<option value=\""+project+"."+items.item(i).firstChild.data+"\" onclick=\"listUser('"+items.item(i).firstChild.data+"','"+project+"')\">"
         +items.item(i).firstChild.data+"</option>\n";

    	}
    	text=text+"</select>\n"+
    	         "</td>\n";
    	document.getElementById("hideRole").innerHTML = text;
    	text = "<input type=\"button\" value=\"<?php etranslate("Add Group") ?>\" onclick=\"addRole()\" />\n";
    	document.getElementById("hidebRole").innerHTML = text;
    }
}

function listUser(role,project){

    var xhr=null;
    
    document.getElementById("hideUser").innerHTML = "";
    document.getElementById("hidebUser").innerHTML = "";
    
    if (window.XMLHttpRequest) { 
        xhr = new XMLHttpRequest();
    }
    else if (window.ActiveXObject) 
    {
        xhr = new ActiveXObject("Microsoft.XMLHTTP");
    }
    //on définit l'appel de la fonction au retour serveur
    xhr.onreadystatechange = function() { genUser(xhr,role,project); };
    
    
    //on appelle le fichier reponse.txt
    xhr.open("GET", "list_users_role.php?groupe="+project+"&role="+role, true);
    xhr.send(null);
}

function genUser(xhr,role,project)
{
    if (xhr.readyState==4) 
    {
    	var docXML= xhr.responseXML;
    	var items = docXML.getElementsByTagName("user");
    	var text = "<select MULTIPLE>\n";
    	for (i=0;i<items.length;i++)
    	{
    	   var attrib = items.item(i).attributes;
         text=text+"<option name=\"user\" value=\""+items.item(i).firstChild.data+"\">"+attrib[0].value+" " +attrib[1].value+" ("+items.item(i).firstChild.data+")"+"</option>\n";

    	}
    	text=text+"</select>\n"+
    	         "</td>\n";
    	document.getElementById("hideUser").innerHTML = text;
    	text = "<input type=\"button\" value=\"<?php etranslate("Add User") ?>\" onclick=\"addUser()\" />\n";
    	document.getElementById("hidebUser").innerHTML = text;
    }
}

function addUser()
{
    var text = document.getElementById("selected").innerHTML;
    var test = 0;
    if(document.getElementById("selected").innerHTML == "\n"){
      test = 1;
      text = "<select name=\"participants[]\" MULTIPLE>\n";
    }else{
      text = text.substring(0,text.indexOf("</select>",0));
    }
    for(i=0;i<document.getElementsByName("user").length;i++){
      if(document.getElementsByName("user")[i].selected && text.indexOf(document.getElementsByName("user")[i].value,0)==-1){
        text=text+"<option name=\"part\" value=\""+document.getElementsByName("user")[i].value+"\">"+document.getElementsByName("user")[i].firstChild.data+"</option>\n";
      }
    }
    text = text+"</select>\n";
    document.getElementById("selected").innerHTML=text;
    
    text = "<input type=\"button\" value=\"<?php etranslate("Delete") ?>\" onclick=\"del()\" />\n";
    document.getElementById("hidebDel").innerHTML = text;
}

function addRole()
{
    var text = document.getElementById("selected").innerHTML;
    if(document.getElementById("selected").innerHTML == "\n"){
      text = "<select name=\"participants[]\" MULTIPLE>\n";
    }else{
      text = text.substring(0,text.indexOf("</select>",0));
    }
    if(text.indexOf(document.getElementsByName("role")[0].value,0)==-1 && document.getElementsByName("role")[0].value != "null"){
      text=text+"<option name=\"part\" value=\""+document.getElementsByName("role")[0].value+"\">"+document.getElementsByName("role")[0].value+"</option>\n";
    }
    text = text+"</select>\n";
    document.getElementById("selected").innerHTML=text;
    
    text = "<input type=\"button\" value=\"<?php etranslate("Delete") ?>\" onclick=\"del()\" />\n";
    document.getElementById("hidebDel").innerHTML = text;
}

function addGroup()
{
    var text = document.getElementById("selected").innerHTML;
    if(document.getElementById("selected").innerHTML == "\n"){
      text = "<select name=\"participants[]\" MULTIPLE>\n";
    }else{
      text = text.substring(0,text.indexOf("</select>",0));
    }
    if(text.indexOf(document.getElementsByName("projects")[0].value,0)==-1 && document.getElementsByName("projects")[0].value != "null"){
      text=text+"<option name=\"part\" value=\""+document.getElementsByName("projects")[0].value+"\">"+document.getElementsByName("projects")[0].value+"</option>\n";
    }
    text = text+"</select>\n";
    document.getElementById("selected").innerHTML=text;
    
    text = "<input type=\"button\" value=\"<?php etranslate("Delete") ?>\" onclick=\"del()\" />\n";
    document.getElementById("hidebDel").innerHTML = text;
}

function addAll(value){
    var text = document.getElementById("selected").innerHTML;
    if(document.getElementById("selected").innerHTML == "\n"){
      text = "<select name=\"participants[]\" MULTIPLE>\n";
    }else{
      text = text.substring(0,text.indexOf("</select>",0));
    }
    if(text.indexOf(value,0)==-1){
      text=text+"<option name=\"part\" value=\""+value+"\">"+value+"</option>\n";
    }
    text = text+"</select>\n";
    document.getElementById("selected").innerHTML=text;
    
    text = "<input type=\"button\" value=\"<?php etranslate("Delete") ?>\" onclick=\"del()\" />\n";
    document.getElementById("hidebDel").innerHTML = text;
}

function del(){
  for(i=1;i < document.getElementsByName("part").length;i++) {
    if(document.getElementsByName("part")[i].selected == true){
        document.getElementById("partlist").options[i] = null;
    }
  }
  if(document.getElementsByName("part").length < 1){
    document.getElementById("selected").innerHTML="\n";
    document.getElementById("hidebDel").innerHTML = "\n";
  }
}

var aSelected = new Array();
function selec(obj){
  var found = false;
  
  if(aSelected.length == 0){
    for(i=0;i < document.getElementsByName("part").length;i++) {
        aSelected[aSelected.length] = document.getElementsByName("part")[i].value;
    }
  }
  
  
  for(i=0;i < aSelected.length;i++) {
    if(obj.value == aSelected[i]) {
      found = true;
      for(j=i;j < aSelected.length-1;j++) {
        aSelected[j] = aSelected[j+1];
      }
      delete aSelected[aSelected.length-1];
      break;
    }
  }
  if( !found ) {
    aSelected[aSelected.length] = obj.value;
  }
  for(i=0;i < document.getElementsByName("part").length;i++) {
    found = false;
    for(j=0;j < aSelected.length;j++) {
      if(document.getElementsByName("part")[i].value == aSelected[j]){
        found = true;
        document.getElementsByName("part")[i].selected = true;
      }
    }
    if(!found){
      document.getElementsByName("part")[i].selected = false;
    }
  }
}
//]]> -->
</script>
