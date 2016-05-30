	/* Global namespace */
var UESP = UESP || {};

UESP.esoItemSearchPopup = null;


	/* Class Constructor */
UESP.EsoItemSearchPopup = function () 
{
	this.queryURL = "http://esolog.uesp.net/esoItemSearchPopup.php";
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
	
	$("#esoispInputText").keyup(function (e) {
	    if (e.keyCode == 13) {
	    	self.onSearch();
	    	this.blur();
	    }
	});
	
	$("#esoispResults").scroll(function(e) { self.onResultsScroll(e); })
	
	return rootElement;
}


UESP.EsoItemSearchPopup.prototype.getPopupRootText = function()
{
	return "" +
		"<div id='esoispCloseButton'>x</div>" +
		"<div id='esoispInputs'>" + 
		"	<p>" +
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


UESP.EsoItemSearchPopup.prototype.getSearchQueryParam = function()
{
	var queryParams = {};
	
	var text = $("#esoispInputText").val().trim();
	
	if (text != "") queryParams['text'] = text;
	
	return queryParams;
}


UESP.EsoItemSearchPopup.prototype.onSearch = function() 
{
	var queryParams = this.getSearchQueryParam();
	
	if (this.sendSearchQuery(queryParams))
	{
		$("#esoispResults").text("Searching...");
	}
};


UESP.EsoItemSearchPopup.prototype.sendSearchQuery = function(queryParams)
{
	var self = this;
	
	if ($.isEmptyObject(queryParams)) return false;
	
	$.ajax(this.queryURL, {
			data: queryParams,
		}).
		done(function(data, status, xhr) { self.onSearchResults(data, status, xhr); }).
		fail(function(xhr, status, errorMsg) { self.onSearchError(xhr, status, errorMsg); });
	
	return true;
}


UESP.EsoItemSearchPopup.prototype.onSearchError = function(xhr, status, errorMsg)
{
	$("#esoispResults").text("Error: " + errorMsg);
}


UESP.EsoItemSearchPopup.prototype.onSearchResults = function(data, status, xhr)
{
	data.sort(function(a, b) { return a.name.localeCompare(b.name); });
	
	this.displaySearchResults(data);
}


UESP.EsoItemSearchPopup.prototype.onResultsScroll = function(e) 
{
	//console.log("scroll");
	//e.preventDefault();
	//e.stopPropagation();
}


UESP.EsoItemSearchPopup.prototype.displaySearchResults = function(itemData)
{
	var resultsElement = $("#esoispResults");
	var newResults = "";
	
	for (var i = 0; i < itemData.length; ++i)
	{
		newResults += this.createSearchResult(itemData[i]);
	}
		
	resultsElement.html(newResults);
}


UESP.EsoItemSearchPopup.prototype.createSearchResult = function(itemData)
{
	var resultHtml;
	
	resultHtml = 	"<div class='esoispResultRow'>" +
						"" + itemData.name +
					"</div>";
	
	
	
	return resultHtml;
}


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