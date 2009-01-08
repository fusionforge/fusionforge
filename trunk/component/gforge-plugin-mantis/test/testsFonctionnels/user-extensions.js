/**
* storeValue, storeText, storeAttribute and store actions now
* have 'global' equivalents.
* Use storeValueGlobal, storeTextGlobal, storeAttributeGlobal or storeGlobal
* will store the variable globally, making it available it subsequent tests.
*
* See the Reference.html for storeValue, storeText, storeAttribute and store
* for the arguments you should send to the new Global functions.
*
* example of use
* in testA.html:
* +------------------+----------------------+----------------------+
* |storeGlobal       | http://localhost/    | baseURL              |
* +------------------+----------------------+----------------------+
*
* in textB.html (executed after testA.html):
* +------------------+-----------------------+--+
* |open              | ${baseURL}Main.jsp    |  |
* +------------------+-----------------------+--+
*
* Note: Selenium.prototype.replaceVariables from selenium-api.js has been replaced
*       here to make it use global variables if no local variable is found.
*       This might cause issues if you upgraded Selenium in the future and this function
*       has been changed.
*
* @author Guillaume Boudreau
*/
// debug variable can be change with the function doSetDebug
var debug=false;

// set the name server (novaforge server)
var serverName="vmforge.bull.fr";
var serverPort="80";
var homePage="http://"+serverName+":"+serverPort;

// set the timeout for http request
Selenium.DEFAULT_TIMEOUT = 60*10000;

globalStoredVars = new Object();

/*
 * change the debug variable if target==true
 * else the debug variable is set false
 */
Selenium.prototype.doSetDebug = function (target)
{  
	if (target=="true")
	{
		debug=true;
	}
	else
	{
		debug=false;
	}
}

/*
* Globally store the value of a form input in a variable
*/
Selenium.prototype.doStoreValueGlobal = function(target, varName) {
	if (!varName) {
		// Backward compatibility mode: read the ENTIRE text of the page
		// and stores it in a variable with the name of the target
		value = this.page().bodyText();
		globalStoredVars[target] = value;
		return;
	}
	var element = this.page().findElement(target);
	globalStoredVars[varName] = getInputValue(element);
};

/*
* Globally store the text of an element in a variable
*/
Selenium.prototype.doStoreTextGlobal = function(target, varName) {
	var element = this.page().findElement(target);
	globalStoredVars[varName] = getText(element);
};

/*
* Globally store the value of an element attribute in a variable
*/
Selenium.prototype.doStoreAttributeGlobal = function(target, varName) {
	globalStoredVars[varName] = this.page().findAttribute(target);
};

/*
* Globally store the result of a literal value
*/
Selenium.prototype.doStoreGlobal = function(value, varName) {
	globalStoredVars[varName] = value;
};

/*
* Search through str and replace all variable references ${varName} with their
* value in storedVars (or globalStoredVars).
*/
Selenium.prototype.replaceVariables = function(str) {
	var stringResult = str;

	// Find all of the matching variable references
	var match = stringResult.match(/\$\{\w+\}/g);
	if (!match) {
		return stringResult;
	}

	// For each match, lookup the variable value, and replace if found
	for (var i = 0; match && i < match.length; i++) {
		var variable = match[i]; // The replacement variable, with ${}
		var name = variable.substring(2, variable.length - 1); // The replacement variable without ${}
		var replacement = storedVars[name];
		if (replacement != undefined) {
			stringResult = stringResult.replace(variable, replacement);
		}
		var replacement = globalStoredVars[name];
		if (replacement != undefined) {
			stringResult = stringResult.replace(variable, replacement);
		}
	}
	return stringResult;
};

/*
 *  this function call an php script located in  the gforge web
 *  @param method : cant be either "post" or "get"
 *  @param script : a name of the php script
 *  @param data   : it is the data to send (necessary for the post request)   
 *  @param nbElt  : it is the number contained in the responseText to extract
 *                   (the response test is like XXX:STRING1:STRING2:) 
 *  @return       : the value contained the nbelt items in the responseText       
 */ 
function getValueInPhpRequest(method,script,data,nbElt)
{
	var returned=-1;
	var xhr_object = null;
	if(window.XMLHttpRequest) // Firefox
	xhr_object = new XMLHttpRequest();
	else if(window.ActiveXObject) // Internet Explorer
		{
			xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
		}
	else
	{ // XMLHttpRequest non support' par le navigateur
				alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
				return;
	}

	var fileName = homePage+"/test/"+script;

	if(method == "GET" && data != null)
	{
		fileName += "?"+data;
		data      = null;
	}
		if (debug) {alert("before open"+fileName);}
	xhr_object.open(method, fileName, false);
	if (debug) {alert("after open");}
	
  if(method == "POST") 
  {
   xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	}
	
	xhr_object.send(data);
  if (xhr_object.status == "404")
{
			alert ("ERROR can't execute the request : "+fileName );
			retur
	}
  else
	if(xhr_object.readyState == 4)
	{
			if (debug) {alert (xhr_object.responseText +" get the number "+nbElt);}
			if (nbElt > 0) 
			{
				var tmp = xhr_object.responseText.split("|");
				if(typeof(tmp[nbElt]) != "undefined")
				{
					if (debug) {alert (tmp[nbElt]);}
					returned= tmp[nbElt];
				}
			}
      			else
			{
			returned= xhr_object.responseText;
			}	
	}

	if (debug)
		alert ("returned "+returned);
	return returned;
}

/*
 * search in all forms in the html pages
 *   in which there is a hidden tag where the value is egal
 *   to the wanted valued
 * @param doc: this is html document
 * @param tagHiddenName : this is the hidden tag to search
 * @ param seachValue : .. the value has which hidden tag must be egal
 * @ returned the number of the form 
 */         
function SearchAllFomsValue(doc,tagHiddenName,searchValue)
{
	var boolReturn = false ;
	var returned=-1;
	
	for (var j=0;j<doc.forms.length&& !boolReturn;j++)
	{
		if (debug) {alert("form en traitement ní " +j);}
		var form = doc.forms[j] ;
		var elements = form.elements ;
		for(var i=0;i < elements.length && !boolReturn;i++)
		{
			var element = elements[i] ;
			switch (element.type) {
				case "text" :
				case "password" :
				case "textarea" :
				case "hidden" :
				if (element.name==tagHiddenName)
				{
					if (debug)
					{
						alert( "element.value "+element.value+" value "+searchValue);
					}
					if (element.value==searchValue)
					{
						if (debug) {alert( "element.value match");}
						returned=j;
						boolReturn = true ;
					}
				}
				break ;
				default :
				// keep looping
				break ;
			}
		}
	}
	if (debug) {alert("form traite end " +returned);}
	return returned;
}	

/*
 * search in a form in the html pages
 *   whose the action is given the parameter searchaction
 *   to the wanted valued
 * @param doc: this is html document
 * @ param seachAction : .. the value has which hidden tag must be egal
 * @ returned the number of the form 
 */         
function SearchAllFomsAction(doc,searchAction)
{
	
	var returned=-1;
	
	for (var j=0;j<doc.forms.length;j++)
	{
		var form = doc.forms[j] ;

		if (debug) {
      alert("form en traitement ní " +j+" action"+doc.forms[j].action);
      alert("indeof "+doc.forms[j].action.indexOf(searchAction));
    }
		if ( doc.forms[j].action.indexOf(searchAction) != -1 )
		{
      returned=j;
      break;
      }
		
	}
	if (debug) {alert("form traite end " +returned);}
	return returned;
}
	
/*
* Globally store the result of a nb group to sumbit in the page /admin/approve-pending.php
*/
Selenium.prototype.doGetGroupIdApprove = function(value, varName) {
	if (debug)
	{
		alert("nb of forms "+selenium.browserbot.getCurrentWindow().document.forms.length);
	}

	doc=selenium.browserbot.getCurrentWindow().document;
	returned=SearchAllFomsValue(doc,"list_of_groups",value);
	globalStoredVars[varName] =returned;
};

/*
* Globally store the result of a nb group to sumbit in the page /admin/approve-pending.php
*/
Selenium.prototype.doGetGroupIdReject = function(value, varName) {
	if (debug)
	{
		alert("nb of forms "+selenium.browserbot.getCurrentWindow().document.forms.length);
	}

	doc=selenium.browserbot.getCurrentWindow().document;
	returned=SearchAllFomsValue(doc,"group_id",value);
	globalStoredVars[varName] =returned;
};

/*
*R‚cup‚ration num‚ro d'une combo box dans l'‚cran Administration
*projet (ce num‚ro est propre … chaque membre du projet; lorsque la
*chaŒne vaut 'new', cela correspond … l'ajout d'un utilisateur)*
* integer memberno = ReadMemberNo (string "nom Unix user", string "nom
*   Unix projet")  */
Selenium.prototype.doUpdateUserGroup = function(value, varName) {
	if (debug)
	{
		alert("nb of forms "+selenium.browserbot.getCurrentWindow().document.forms.length);
	}
	var returned=-1;
	if (value.length==0)
	{
		var doc=selenium.browserbot.getCurrentWindow().document;
		returned=doc.forms.length-2;
		if (returned<0)
		{
			returned =-1;
		}
	}
else
	{
		// search the use
  var method   = "GET" ;
	var s1       = value;
	var data     = null;
	var returned=-1;
	var fileName = "getUser.php";
	if(s1 != "")
		data = "user="+s1;

	userid=getValueInPhpRequest(method,fileName,data,1);
	if (debug) {alert ("the user id is "+userid );}

		//search the nb form wher the field user id is se
		if (userid != -1)
		{
			var boolReturn = false ;
			doc=selenium.browserbot.getCurrentWindow().document;
			returned=SearchAllFomsValue(doc,"user_id",userid);
		}

	}
	if (debug) {alert(" end " +returned);}
	globalStoredVars[varName] =returned;

};

/*
 * retrieve the number of form to change a role 
 */ 
Selenium.prototype.doUpdateRoleGroup = function(value, varName) 
{
	var returned=-1;
	if (debug)
	{
		alert("nb of forms "+selenium.browserbot.getCurrentWindow().document.forms.length);
	}

	var doc=selenium.browserbot.getCurrentWindow().document;
		returned=doc.forms.length-1;
		if (returned<0)
		{
			returned =-1;
		}
	
	globalStoredVars[varName] =returned;

};

/*
 * call the javascript function : "alert"
 */ 
Selenium.prototype.doDisplayAlert = function(value, varName) {
	alert(value);
};

/*
 * retrieve the id for a user
 * @param value: the user name
 * @param varName : the key to store the id user   
 */ 
Selenium.prototype.doReadUserId = function (value, varName)
{	
  var method   = "GET" ;
  var s1       = value;
	var data     = null;
	var returned=-1;
	var fileName = "getUser.php";
	if(s1 != "")
		data = "user="+s1;

	returned=getValueInPhpRequest(method,fileName,data,1);
	globalStoredVars[varName] =returned;

	
};

/*
 * retrieve the id for a group
 * @param value: the group name
 * @param varName : the key to store  the id group 
 */ 
Selenium.prototype.doReadGroupId = function (value, varName)
{
var method   = "GET" ;
	var fileName = "getGroupId.php";
	var s1       = value;
	var data     = null;
	var returned=-1;
	if(s1 != "")
		data = "unix_group_name="+s1;
	
	returned=getValueInPhpRequest(method,fileName,data,1);
	globalStoredVars[varName] =returned;

};

/*
 * retrieve the id role in a group
 * @param value: the group name 
 *               the role name (separated by a comma)
 * @param varName : the key to store the id role  
 */ 
Selenium.prototype.doReadRoleId = function (value, varName)
{
var method   = "GET" ;
	var fileName = "getRoleId.php";
	var s1       = value;
	var data     = null;
	var returned=-1;
	
	var args= value.split(",")


	if(typeof(args[0]) != "undefined")
	{
		data = "unix_group_name="+args[0];
	}
	if(typeof(args[1]) != "undefined")
	{
		data += "&role_name="+escape(args[1]);
	}

	returned=getValueInPhpRequest(method,fileName,data,2);
  globalStoredVars[varName] =returned;

};
Selenium.prototype.doGetIdMantisProject = function (value, varName)
{
  var method   = "GET" ;
	var fileName = "getMantisProject.php";
  
  var args= value.split(",")
	if(typeof(args[0]) != "undefined")
	{
		data = "unix_group_name="+args[0];
	}
	
	if(typeof(args[1]) != "undefined")
	{
		data += "&mantis_name="+escape(args[1]);
	}
	
  if(typeof(args[2]) != "undefined")
	{
		data += "&url="+escape(args[2]);
	}
  	
	returned=getValueInPhpRequest(method,fileName,data,0);
  globalStoredVars[varName] =returned;

};


/*
 * retrieve the id tracker in a group
 * @param value: the group name 
 *               the tracker name (separated by a comma)
 * @param varName : the key to store the id tracker  
 */ 
Selenium.prototype.doReadTrackerId = function (value, varName)
{
var method   = "GET" ;

var fileName = "getTrackerId.php";
	var s1       = value;
	var data     = null;
	var returned=-1;
	
	var args= value.split(",")


	if(typeof(args[0]) != "undefined")
	{
		data = "unix_group_name="+args[0];
	}
	if(typeof(args[1]) != "undefined")
	{
		data += "&name_tracker="+args[1];
	}


	returned=getValueInPhpRequest(method,fileName,data,2);
    globalStoredVars[varName] =returned;
};

/*
 * retrieve the id forum in a group
 * @param value: the group name 
 *               the forum name (separated by a comma)
 * @param varName : the key to store the id forum 
 */ 
Selenium.prototype.doReadForumId = function (value, varName)
{
  var method   = "GET" ;
	var fileName="getForumId.php";
	var s1       = value;
	var data     = null;
	var returned=-1;
	
	var args= value.split(",")

	if(typeof(args[0]) != "undefined")
	{
		data = "unix_group_name="+args[0];
	}
	if(typeof(args[1]) != "undefined")
	{
		data += "&name_forum="+escape(args[1]);
	}

	returned=getValueInPhpRequest(method,fileName,data,2);
  globalStoredVars[varName] =returned;

};

/*
 * retrieve the id task in a group
 * @param value: the group name 
 *               the task name (separated by a comma)
 * @param varName : the key to store the id task  
 */ 
Selenium.prototype.doReadTaskProjectId = function (value, varName)
{
var method   = "GET" ;
	var fileName="getTaskProjectId.php";
	var s1       = value;
	var data     = null;
	var returned=-1;
	
	var args= value.split(",")
	if(typeof(args[0]) != "undefined")
	{
		data = "unix_group_name="+args[0];
	}
	if(typeof(args[1]) != "undefined")
	{
		data += "&name_task="+escape(args[1]);
	}

	returned=getValueInPhpRequest(method,fileName,data,2);
  globalStoredVars[varName] =returned;
	

};

/**
* Change the password for one user
* parameter : this new password 
*/
Selenium.prototype.doUpdateUserPwd = function (value, varName)
{
	var fileName="change_admin_pwd.php";
	var s1       = value;
	var data     = null;

   method = "POST"
  var args= value.split(",")
	if(typeof(args[0]) != "undefined")
	{
		data = "user="+args[0];
	}
	
	if(typeof(args[1]) != "undefined")
	{
		data += "&passwd="+escape(args[1]);
	}
	getValueInPhpRequest(method,fileName,data,-1);

};
/**
* Change the password for the user "admin"
* parameter : the new password for admin
*/
Selenium.prototype.doUpdateAdminPwd = function (value, varName)
{
var method   = "GET" ;
  dataToSend="admin,"+value;
  if (debug)
    alert(dataToSend);
  this.doUpdateUserPwd(dataToSend,0)  
	
};

/**
* set public informations
*
**/
Selenium.prototype.doSetInfosPublic = function (value, varName)
{
	doc=selenium.browserbot.getCurrentWindow().document;
	var search="/project/admin/editgroupinfo.php";
  if (debug)
    alert("SetInfosPublic search="+search);
  indice=SearchAllFomsAction(doc,search);
  var form = doc.forms[indice] ;
	var elements = form.elements ;
	
  for(var i=0;i < elements.length;i++)
	{
		var element = elements[i] ;
  		
		switch (element.type) {
			case "checkbox" :

				if ((typeof(globalStoredVars[element.name] ) != "undefined"))
				{
	         if (debug)
  				  alert(globalStoredVars[element.name]);
				  
					var value =(globalStoredVars[element.name] == "true" ? true : false)
           if (debug)
            alert ("element "+element.checked+" compare with "+value);
					if (value != element.checked)
					{
						if (debug)
						  alert("click"+element.checked)
						this.doClick(element.name);
					}
					else
						{
							if (debug)
							 alert("not click"+element.checked)
						}
					}
				
				break ;
				default :
				// keep looping
				break ;
			}
		}
};

/**
 *click on the located element, and attach a callback to notify
 *  wait 
 */
Selenium.prototype.doGforceClick = function (locator)
{
  this.doClick(locator);
  return this.doWaitForPageToLoad(this.defaultTimeout);
 };


Selenium.prototype.doSleep = function (value, varName)
{
  var method   = "GET" ;
	var fileName = "sleep.php";
  var s1       = value;
	var data     = null;
	var returned=-1;
	if(s1 != "")
		data = "time="+s1;
	
	returned=getValueInPhpRequest(method,fileName,data,0);
	globalStoredVars[varName] =returned;

}
