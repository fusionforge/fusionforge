/*
	Copyright (c) 2004-2005, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/

dojo.provide("dojo.widget.html.Button2");
dojo.require("dojo.lang");
dojo.require("dojo.html");
dojo.require("dojo.style");

dojo.require("dojo.widget.HtmlWidget");
dojo.require("dojo.widget.Button2");

dojo.widget.html.Button2 = function(){
	// call superclass constructors
	dojo.widget.HtmlWidget.call(this);
	dojo.widget.Button2.call(this);
}
dojo.inherits(dojo.widget.html.Button2, dojo.widget.HtmlWidget);
dojo.lang.extend(dojo.widget.html.Button2, dojo.widget.Button2.prototype);
dojo.lang.extend(dojo.widget.html.Button2, {

	templatePath: dojo.uri.dojoUri("src/widget/templates/HtmlButton2Template.html"),
	templateCssPath: dojo.uri.dojoUri("src/widget/templates/HtmlButton2Template.css"),
	
	// button images
	inactiveImg: "src/widget/templates/images/pill-button-blue_benji-",
	activeImg: "src/widget/templates/images/pill-button-seagreen_benji-",
	pressedImg: "src/widget/templates/images/pill-button-purple_benji-",
	disabledImg: "src/widget/templates/images/pill-button-gray_benji-",
	width2height: 1.0/3.0,

	// attach points
	containerNode: null,
	leftImage: null,
	centerImage: null,
	rightImage: null,

	fillInTemplate: function(args, frag){
		if(this.caption != ""){
			this.containerNode.appendChild(document.createTextNode(this.caption));
		}
		dojo.html.disableSelection(this.containerNode);
		if ( this.disabled ) {
			dojo.html.prependClass(this.domNode, "dojoButtonDisabled");
		}
		
		// after the browser has had a little time to calculate the size needed
		// for the button contents, size the button
		dojo.lang.setTimeout(this, this.sizeMyself, 0);
	},

	onResized: function(){
		this.sizeMyself();
	},

	sizeMyself: function(e){
		this.height = dojo.style.getOuterHeight(this.containerNode);
		this.containerWidth = dojo.style.getOuterWidth(this.containerNode);
		var endWidth= this.height * this.width2height;

		this.containerNode.style.left=endWidth+"px";

		this.leftImage.height = this.rightImage.height = this.centerImage.height = this.height;
		this.leftImage.width = this.rightImage.width = endWidth+1;
		this.centerImage.width = this.containerWidth;
		this.centerImage.style.left=endWidth+"px";
		this._setImage(this.disabled ? this.disabledImg : this.inactiveImg);
			
		this.domNode.style.height=this.height + "px";
		this.domNode.style.width= (this.containerWidth+2*endWidth) + "px";
	},

	onMouseOver: function(e){
		if( this.disabled ){ return; }
		dojo.html.prependClass(this.domNode, "dojoButtonHover");
		this._setImage(this.activeImg);
	},

	onMouseDown: function(e){
		if( this.disabled ){ return; }
		dojo.html.prependClass(this.domNode, "dojoButtonDepressed");
		dojo.html.removeClass(this.domNode, "dojoButtonHover");
		this._setImage(this.pressedImg);
	},
	onMouseUp: function(e){
		if( this.disabled ){ return; }
		dojo.html.prependClass(this.domNode, "dojoButtonHover");
		dojo.html.removeClass(this.domNode, "dojoButtonDepressed");
		this._setImage(this.activeImg);
	},

	onMouseOut: function(e){
		if( this.disabled ){ return; }
		dojo.html.removeClass(this.domNode, "dojoButtonHover");
		this._setImage(this.inactiveImg);
	},

	buttonClick: function(e){
		if( !this.disabled && this.onClick ) { this.onClick(e); }
	},

	_setImage: function(prefix){
		this.leftImage.src=dojo.uri.dojoUri(prefix + "l.gif");
		this.centerImage.src=dojo.uri.dojoUri(prefix + "c.gif");
		this.rightImage.src=dojo.uri.dojoUri(prefix + "r.gif");
	},
	
	_toggleMenu: function(menuId){
		var menu = dojo.widget.getWidgetById(menuId);
		if ( !menu ) { return; }

		if ( menu.open && !menu.isShowing) {
			var x = dojo.style.getAbsoluteX(this.domNode, true);
			var y = dojo.style.getAbsoluteY(this.domNode, true) + this.height;
			menu.open(x, y, null, this.domNode);
		} else if ( menu.close && menu.isShowing ){
			menu.close();
		} else {
			menu.toggle();
		}
	}
});

/**** DropDownButton - push the button and a menu shows up *****/
dojo.widget.html.DropDownButton2 = function(){
	// call constructors of superclasses
	dojo.widget.DropDownButton2.call(this);
	dojo.widget.html.Button2.call(this);
}
dojo.inherits(dojo.widget.html.DropDownButton2, dojo.widget.html.Button2);
dojo.lang.extend(dojo.widget.html.DropDownButton2, dojo.widget.DropDownButton2.prototype);

dojo.lang.extend(dojo.widget.html.DropDownButton2, {

	downArrow: "src/widget/templates/images/whiteDownArrow.gif",
	disabledDownArrow: "src/widget/templates/images/whiteDownArrow.gif",

	fillInTemplate: function(args, frag){
		dojo.widget.html.DropDownButton2.superclass.fillInTemplate.call(this, args, frag);

		// draw the arrow
		var arrow = document.createElement("img");
		arrow.src = dojo.uri.dojoUri(this.disabled ? this.disabledDownArrow : this.downArrow);
		dojo.html.setClass(arrow, "downArrow");
		this.containerNode.appendChild(arrow);
	},

	onClick: function (e){
		if( this.disabled ){ return; }
		this._toggleMenu(this.menuId);
	}
});

/**** ComboButton - left side is normal button, right side shows menu *****/
dojo.widget.html.ComboButton2 = function(){
	// call constructors of superclasses
	dojo.widget.html.Button2.call(this);
	dojo.widget.ComboButton2.call(this);
}
dojo.inherits(dojo.widget.html.ComboButton2, dojo.widget.html.Button2);
dojo.lang.extend(dojo.widget.html.ComboButton2, dojo.widget.ComboButton2.prototype);
dojo.lang.extend(dojo.widget.html.ComboButton2, {

	templatePath: dojo.uri.dojoUri("src/widget/templates/HtmlComboButton2Template.html"),

	// attach points
	leftPart: null,
	rightPart: null,
	arrowBackgroundImage: null,

	// constants
	splitWidth: 1,		// pixels between left&right part of button
	arrowWidth: 10,		// width of segment holding down arrow

	sizeMyself: function(e){
		this.height = dojo.style.getOuterHeight(this.containerNode);
		this.containerWidth = dojo.style.getOuterWidth(this.containerNode);
		var endWidth= this.height/3;

		// left part
		this.leftImage.height = this.rightImage.height = this.centerImage.height = 
			this.arrowBackgroundImage.height = this.height;
		this.leftImage.width = endWidth+1;
		this.centerImage.width = this.containerWidth;
		this.leftPart.style.height = this.height + "px";
		this.leftPart.style.width = endWidth + this.containerWidth + "px";
		this._setImageL(this.disabled ? this.disabledImg : this.inactiveImg);

		// right part
		this.arrowBackgroundImage.width=this.arrowWidth;
		this.rightImage.width = endWidth+1;
		this.rightPart.style.height = this.height + "px";
		this.rightPart.style.width = this.arrowWidth + endWidth + "px";
		this._setImageR(this.disabled ? this.disabledImg : this.inactiveImg);

		// outer container
		this.domNode.style.height=this.height + "px";
		var totalWidth = this.containerWidth+this.splitWidth+this.arrowWidth+2*endWidth;
		this.domNode.style.width= totalWidth + "px";
	},

	/** functions on left part of button**/
	leftOver: function(e){
		if( this.disabled ){ return; }
		dojo.html.prependClass(this.leftPart, "dojoButtonHover");
		this._setImageL(this.activeImg);
	},

	leftDown: function(e){
		if( this.disabled ){ return; }
		dojo.html.prependClass(this.leftPart, "dojoButtonDepressed");
		dojo.html.removeClass(this.leftPart, "dojoButtonHover");
		this._setImageL(this.pressedImg);
	},
	leftUp: function(e){
		if( this.disabled ){ return; }
		dojo.html.prependClass(this.leftPart, "dojoButtonHover");
		dojo.html.removeClass(this.leftPart, "dojoButtonDepressed");
		this._setImageL(this.activeImg);
	},

	leftOut: function(e){
		if( this.disabled ){ return; }
		dojo.html.removeClass(this.leftPart, "dojoButtonHover");
		this._setImageL(this.inactiveImg);
	},

	leftClick: function(e){
		if ( !this.disabled && this.onClick ) {
			this.onClick(e);
		}
	},

	_setImageL: function(prefix){
		this.leftImage.src=dojo.uri.dojoUri(prefix + "l.gif");
		this.centerImage.src=dojo.uri.dojoUri(prefix + "c.gif");
	},

	/*** functions on right part of button ***/
	rightOver: function(e){
		if( this.disabled ){ return; }
		dojo.html.prependClass(this.rightPart, "dojoButtonHover");
		this._setImageR(this.activeImg);
	},

	rightDown: function(e){
		if( this.disabled ){ return; }
		dojo.html.prependClass(this.rightPart, "dojoButtonDepressed");
		dojo.html.removeClass(this.rightPart, "dojoButtonHover");
		this._setImageR(this.pressedImg);
	},
	rightUp: function(e){
		if( this.disabled ){ return; }
		dojo.html.prependClass(this.rightPart, "dojoButtonHover");
		dojo.html.removeClass(this.rightPart, "dojoButtonDepressed");
		this._setImageR(this.activeImg);
	},

	rightOut: function(e){
		if( this.disabled ){ return; }
		dojo.html.removeClass(this.rightPart, "dojoButtonHover");
		this._setImageR(this.inactiveImg);
	},

	rightClick: function(e){
		if( this.disabled ){ return; }
		this._toggleMenu(this.menuId);
	},

	_setImageR: function(prefix){
		this.arrowBackgroundImage.src=dojo.uri.dojoUri(prefix + "c.gif");
		this.rightImage.src=dojo.uri.dojoUri(prefix + "r.gif");
	}
});
