//
//  This script was created
//  by Mircho Mirev
//  mo /mo@momche.net/
//
//	:: feel free to use it BUT
//	:: if you want to use this code PLEASE send me a note
//	:: and please keep this disclaimer intact
//

//define the cEvent object
var cDomEvent = {
	e 		: null,
	type	: '',
	button	: 0,
	key		: 0,
	x		: 0,
	y		: 0,
	pagex	: 0,
	pagey	: 0,
	target	: null,
	from	: null,
	to		: null
}

cDomEvent.init = function( e )
{
	if( window.event ) e = window.event
	this.e = e
	this.type = e.type
	this.button = ( e.which ) ? e.which : e.button
	this.key = ( e.which ) ? e.which : e.keyCode
	this.target = ( e.srcElement ) ? e.srcElement : e.originalTarget
	this.currentTarget = ( e.currentTarget ) ? e.currentTarget : e.srcElement
	this.from = ( e.originalTarget ) ? e.originalTarget : ( e.fromElement ) ? e.fromElement : null
	this.to  = ( e.currentTarget ) ? e.currentTarget : ( e.toElement ) ? e.toElement : null
	this.x = ( e.layerX ) ? e.layerX : ( e.offsetX ) ? e.offsetX : null
	this.y = ( e.layerY ) ? e.layerY : ( e.offsetY ) ? e.offsetY : null
	this.screenX = e.screenX
	this.screenY = e.screenY
	this.pageX = ( e.pageX ) ? e.pageX : e.x + document.body.scrollLeft
	this.pageY = ( e.pageY ) ? e.pageY : e.y + document.body.scrollTop
}

cDomEvent.getEvent = function( e )
{
	if( window.event ) e = window.event
	return 	{
				e: e,
				type: e.type,
				button: ( e.which ) ? e.which : e.button,
				key: ( e.which ) ? e.which : e.keyCode,
				target: ( e.srcElement ) ? e.srcElement : e.originalTarget,
				currentTarget: ( e.currentTarget ) ? e.currentTarget : e.srcElement,
				from: ( e.originalTarget ) ? e.originalTarget : ( e.fromElement ) ? e.fromElement : null,
				to: ( e.currentTarget ) ? e.currentTarget : ( e.toElement ) ? e.toElement : null,
				x: ( e.layerX ) ? e.layerX : ( e.offsetX ) ? e.offsetX : null,
				y: ( e.layerY ) ? e.layerY : ( e.offsetY ) ? e.offsetY : null,
				screenX: e.screenX,
				screenY: e.screenY,
				pageX: ( e.pageX ) ? e.pageX : ( e.clientX + ( document.documentElement.scrollLeft || document.body.scrollLeft ) ),
				pageY: ( e.pageY ) ? e.pageY : ( e.clientY + ( document.documentElement.scrollTop || document.body.scrollTop ) )
			}
}

cDomEvent.cancelEvent = function( e )
{
	if( e.preventDefault )
	{
		e.preventDefault()
	}
	e.returnValue = false
	e.cancelBubble = true
	return false
}

cDomEvent.addEvent = function( hElement, sEvent, handler, bCapture )
{
	if( hElement.addEventListener )
	{
		hElement.addEventListener( sEvent, handler, bCapture )
		return true
	}
	else if( hElement.attachEvent )
	{
		return hElement.attachEvent( 'on'+sEvent, handler )
	}
	else if( document.all || hElement.captureEvents )
	{
		if( hElement.captureEvents ) eval( 'hElement.captureEvents( Event.'+sEvent.toUpperCase()+' )' )
		eval( 'hElement.on'+sEvent+' = '+handler )
	}
	else
	{
		alert('Not implemented yet!')
	}
}

cDomEvent.encapsulateEvent = function( hHandler )
{
	return function ( hEvent )
	{
		hEvent = cDomEvent.getEvent( hEvent )
		hHandler.call( hEvent.target, hEvent.e )
	}
}

cDomEvent.addEvent2 = function( hElement, sEvent, handler, bCapture )
{
	if( hElement )
	{
		if( hElement.addEventListener )
		{
			hElement.addEventListener( sEvent, cDomEvent.encapsulateEvent( handler ), bCapture )
			return true
		}
		else if( hElement.attachEvent )
		{
			return hElement.attachEvent( 'on'+sEvent, cDomEvent.encapsulateEvent( handler ) )
		}
		else
		{
			alert('Not implemented yet!')
		}
	}
	else
	{
		//alert( 'wrong' )
	}
}

cDomEvent.addCustomEvent2 = function( hElement, sEvent, handler )
{
	if( hElement )
	{
		hElement[ sEvent ] = handler
	}
	else
	{
		//alert( 'wrong' )
	}
}

cDomEvent.removeEvent = function( hElement, sEvent, handler, bCapture )
{
	if( hElement.addEventListener )
	{
		hElement.removeEventListener( sEvent, handler, bCapture )
		return true
	}
	else if( hElement.attachEvent )
	{
		return hElement.detachEvent( 'on'+sEvent, handler )
	}
	else if( document.all || hElement.captureEvents )
	{
		eval( 'hElement.on'+sEvent+' = null' )
	}
	else
	{
		alert('Not implemented yet!')
	}
}


//Mouse button mapper object
function MouseButton()
{
	if( document.layers )
	{
		this.left = 1
		this.middle = 2
		this.right = 3
	}
	else if( document.all )
	{
		this.left = 1
		this.middle = 4
		this.right = 2
	}
	else //hopefully this is mozilla case
	{
		this.left = 0
		this.middle = 1
		this.right = 2
	}
}

var MB = new MouseButton()
