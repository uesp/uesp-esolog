	/* Global namespace */
var UESP = UESP || {};

UESP.esoItemSearchPopup = null;


	/* Class Constructor */
UESP.EsoItemSearchPopup = function () 
{
	this.rootElement = this.create();
};


UESP.EsoItemSearchPopup.prototype.create = function()
{
	var self = this;
	
	var rootElement = $("<div />").
				attr("id", "esoispRoot").
				attr("style", "display:none;").
				html(this.getPopupRootText()).				
				appendTo("body");
	
	$("#esoispCloseButton").click(function() { self.onClose(); });
	$("#esoispSearchButton").click(function() { self.onSearch(); });
	
	return rootElement;
}


UESP.EsoItemSearchPopup.prototype.getPopupRootText = function()
{
	return "" +
		"<div id='esoispCloseButton'></div>" +
		"<div id='esoispInputs'>" + 
		"	Text <input id='esoispInputText' type='text' name='text' value=''>" +
		"	<button id='esoispSearchButton' class='esoispButton'>Search...</button>" +
		"</div>" +
		"<div id='esoispResults'></div>" + 
		"";
}


UESP.EsoItemSearchPopup.prototype.show = function() 
{
	this.rootElement.show();
};


UESP.EsoItemSearchPopup.prototype.hide = function() 
{
	this.rootElement.hide();
};


UESP.EsoItemSearchPopup.prototype.onClose = function() 
{
	this.hide();
	$(document).trigger("EsoItemSearchPopupOnClose");
};


UESP.EsoItemSearchPopup.prototype.onSearch = function() 
{
	
};



	/* Main function entrance */
UESP.showEsoItemSearchPopup = function()
{
	if (UESP.esoItemSearchPopup == null)
	{
		UESP.esoItemSearchPopup = new UESP.EsoItemSearchPopup();
	}
		
	UESP.esoItemSearchPopup.show();
	
	return UESP.esoItemSearchPopup.rootElement;
}