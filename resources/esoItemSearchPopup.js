	/* Global namespace */
var UESP = UESP || {};

UESP.esoItemSearchPopup = null;

UESP.ESO_EQUIP_TYPES = {
		"-1" : "",
		"0"  : "",
		"1"  : "Head",
		"2"  : "Neck",
		"3"  : "Chest",
		"4"  : "Shoulders",
		"5"  : "One Hand",
		"6"  : "Two Hand",
		"7"  : "Off Hand",
		"8"  : "Waist",
		"9"  : "Legs",
		"10" : "Feet",
		"11" : "Costume",
		"12" : "Ring",
		"13" : "Hand",
		"14" : "Main Hand",
		"15" : "Poison",
};

UESP.ESO_TRAIT_TYPES = {
		"-1" : "",
		"0"	 : "",
		"18" : "Divines",
		"17" : "Prosperous",
		"12" : "Impenetrable",
		"16" : "Infused",
		"20" : "Intricate",
		"19" : "Ornate",
		"13" : "Reinforced",
		"11" : "Sturdy",
		"15" : "Training",
		"14" : "Well Fitted",
		"22" : "Arcane",
		"21" : "Healthy",
		"24" : "Ornate",
		"23" : "Robust",
		"0"  : "",
		"2"  : "Charged",
		"5"  : "Defending",
		"4"  : "Infused",
		"9"  : "Intricate",
		"10" : "Ornate",
		"1"  : "Powered",
		"3"  : "Precise",
		"7"  : "Sharpened",
		"6"  : "Training",
		"8"  : "Decisive",
		"25" : "Nirnhoned",
		"26" : "Nirnhoned",
};


UESP.ESO_ARMOR_TYPES = {
		"-1" : "",
		"0"  : "",
		"1"  : "Light",
		"2"  : "Medium",
		"3"  : "Heavy",	
};


	/* Class Constructor */
UESP.EsoItemSearchPopup = function () 
{
	this.ROW_LIMIT = 100;
	
	this.iconURL = "http://esoicons.uesp.net";
	this.queryURL = "http://esolog.uesp.net/esoItemSearchPopup.php";
	this.rootElement = this.create();
	this.sourceElement = null;
	this.onSelectItem = null;
	this.searchResults = [];
	
	this.itemLevel = "1";
	this.itemQuality = "1";
	this.itemType = null;
	this.equipType = null;
	this.equipTypes = [];
	this.weaponType = "-1";
	this.armorType = "-1";
	this.itemTrait = "-1";
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
	$("#esoispUneqipButton").click(function() { self.onUnequip(); });
	$("#esoispLevel").on("input", function(e) { self.onLevelChange(e); });
	$("#esoispQuality").change(function(e) { self.onQualityChange(e); });
	$("#esoispArmorTrait").change(function(e) { self.onArmorTraitChange(e); });
	$("#esoispArmorType").change(function(e) { self.onArmorTypeChange(e); });
	$("#esoispJewelryTrait").change(function(e) { self.onJewelryTraitChange(e); });
	$("#esoispWeaponTrait").change(function(e) { self.onWeaponTraitChange(e); });
	$("#esoispWeaponType1").change(function(e) { self.onWeaponType1Change(e); });
	$("#esoispWeaponType2").change(function(e) { self.onWeaponType2Change(e); });
	$("#esoispLevelSlider").on("input", function(e) { self.onLevelSlideChange(e); });
	
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
		"<div id='esoispTitle'>Select Item</div>" + 
		"<div id='esoispInputs'>" + 
		"	<p>" +
		"	<div class='esoispInputLabel'>Text</div> <input id='esoispInputText' type='text' name='text' value=''>" +
		"	<div class='esoispInputLabel' id='esoispQualityLabel'>Quality</div> <select id='esoispQuality' type='text' name='quality'>" +
		"		<option value='1'>Normal</option>" +
		"		<option value='2'>Fine</option>" +
		"		<option value='3'>Superior</option>" +
		"		<option value='4'>Epic</option>" +
		"		<option value='5'>Legendary</option>" +
		"	</select>" +
		"	<div class='esoispInputLabel' id='esoispJewelryTraitLabel'>Jewelry Traits</div> <select id='esoispJewelryTrait' type='text' name='jewelryTrait'>" +
		"		<option value='-1'>Any</option>" +
		"		<option value='0'>None</option>" +
		"		<option value='22'>Arcane</option>" +
		"		<option value='21'>Healthy</option>" +
		"		<option value='23'>Robust</option>" +
		"	</select>" +
		"	<div class='esoispInputLabel' id='esoispWeaponTraitLabel'>Weapon Traits</div> <select id='esoispWeaponTrait' type='text' name='WeaponTrait'>" +
		"		<option value='-1'>Any</option>" +
		"		<option value='0'>None</option>" +
		"		<option value='2'>Charged</option>" +
		"		<option value='8'>Decisive</option>" +
		"		<option value='5'>Defending</option>" +
		"		<option value='4'>Infused</option>" +
		"		<option value='26'>Nirnhoned</option>" +
		"		<option value='1'>Powered</option>" +
		"		<option value='3'>Precise</option>" +
		"		<option value='7'>Sharpened</option>" +
		"		<option value='6'>Training</option>" +
		"	</select>" +
		"	<div class='esoispInputLabel' id='esoispArmorTypeLabel'>Armor Types</div> <select id='esoispArmorType' type='text' name='ArmorType'>" +
		"		<option value='-1'>Any</option>" +
		"		<option value='1'>Light</option>" +
		"		<option value='2'>Medium</option>" +
		"		<option value='3'>Heavy</option>" +
		"	</select>" +
		"	<div class='esoispInputLabel' id='esoispWeaponTypeLabel1'>Weapon Types</div> <select id='esoispWeaponType1' type='text' name='WeaponType1'>" +
		"		<option value='-1'>Any</option>" +
		"		<option value='5'>2H Axe</option>" +
		"		<option value='6'>2H Mace</option>" +
		"		<option value='4'>2H Sword</option>" +
		"		<option value='1'>Axe</option>" +
		"		<option value='8'>Bow</option>" +
		"		<option value='11'>Dagger</option>" +
		"		<option value='12'>Fire Staff</option>" +
		"		<option value='13'>Frost Staff</option>" +
		"		<option value='2'>Hammer</option>" +
		"		<option value='15'>Lightning Staff</option>" +
		"		<option value='9'>Restoration Staff</option>" +
		"		<option value='3'>Sword</option>" +
		"	</select>" +
		"	<div class='esoispInputLabel' id='esoispWeaponTypeLabel2'>Weapon Types</div> <select id='esoispWeaponType2' type='text' name='WeaponType2'>" +
		"		<option value='-1'>Any</option>" +
		"		<option value='1'>Axe</option>" +
		"		<option value='11'>Dagger</option>" +
		"		<option value='2'>Hammer</option>" +
		"		<option value='14'>Shield</option>" +
		"		<option value='3'>Sword</option>" +
		"	</select>" +
		"	<div class='esoispInputLabel' id='esoispArmorTraitLabel'>Armor Traits</div> <select id='esoispArmorTrait' type='text' name='armorTrait'>" +
		"		<option value='-1'>Any</option>" +
		"		<option value='0'>None</option>" +
		"		<option value='18'>Divines</option>" +
		"		<option value='12'>Impenetrable</option>" +
		"		<option value='16'>Infused</option>" +
		"		<option value='25'>Nirnhoned</option>" +
		"		<option value='17'>Prosperous</option>" +
		"		<option value='13'>Reinforced</option>" +
		"		<option value='11'>Sturdy</option>" +
		"		<option value='15'>Training</option>" +
		"		<option value='14'>Well Fitted</option>" +
		"	</select>" +
		"	<br/>" +
		"	<div class='esoispInputLabel'>Level</div> <input id='esoispLevel' type='text' name='level' value='66' readonly='readonly'>" +
		"	<input id='esoispLevelSlider' type='range' min='1' max='66' value='66'><br/>" + 
		"	<button id='esoispUneqipButton' class='esoispButton'>Unequip Item</button>" +
		"	<button id='esoispSearchButton' class='esoispButton'>Search...</button>" +
		"</div>" +
		"<div id='esoispResults'></div>" + 
		"";
}


UESP.EsoItemSearchPopup.prototype.update = function()
{
	this.itemLevel = $("#esoispLevel").val();
	this.itemQuality = $("#esoispQuality").val();
	
	if (this.itemType == 1) // Weapons
	{
		$("#esoispQuality").show();
		$("#esoispQualityLabel").show();
		$("#esoispArmorType").hide();
		$("#esoispArmorTypeLabel").hide();
		$("#esoispJewelryTrait").hide();
		$("#esoispJewelryTraitLabel").hide();
		$("#esoispWeaponTrait").show();
		$("#esoispWeaponTraitLabel").show();
		$("#esoispWeaponTrait").val(this.itemTrait);
		
		
		if (this.equipTypes.indexOf("7") >= 0)
		{
			$("#esoispArmorTrait").show();
			$("#esoispArmorTraitLabel").show();
			$("#esoispArmorTrait").val(this.itemTrait);
			$("#esoispWeaponType1").hide();
			$("#esoispWeaponTypeLabel1").hide();
			$("#esoispWeaponType2").show();
			$("#esoispWeaponTypeLabel2").show();
			$("#esoispWeaponType2").val(this.weaponType);
		}
		else
		{
			$("#esoispWeaponType2").hide();
			$("#esoispWeaponTypeLabel2").hide();
			$("#esoispWeaponType1").show();
			$("#esoispWeaponTypeLabel1").show();
			$("#esoispWeaponType1").val(this.weaponType);
			$("#esoispArmorTrait").hide();
			$("#esoispArmorTraitLabel").hide();
		}
		
	}
	else if (this.itemType == 2) // Armor
	{
		$("#esoispQuality").show();
		$("#esoispQualityLabel").show();
		$("#esoispWeaponType1").hide();
		$("#esoispWeaponTypeLabel1").hide();
		$("#esoispWeaponType2").hide();
		$("#esoispWeaponTypeLabel2").hide();
		
		if (this.equipType == 2 || this.equipType == 12)
		{
			$("#esoispJewelryTrait").show();
			$("#esoispJewelryTraitLabel").show();
			$("#esoispJewelryTrait").val(this.itemTrait);
			$("#esoispWeaponTrait").hide();
			$("#esoispWeaponTraitLabel").hide();
			$("#esoispArmorTrait").hide();
			$("#esoispArmorTraitLabel").hide();
			$("#esoispArmorType").hide();
			$("#esoispArmorTypeLabel").hide();
		}
		else
		{
			$("#esoispJewelryTrait").hide();
			$("#esoispJewelryTraitLabel").hide();
			$("#esoispWeaponTrait").hide();
			$("#esoispWeaponTraitLabel").hide();
			$("#esoispArmorTrait").show();
			$("#esoispArmorTraitLabel").show();
			$("#esoispArmorType").show();
			$("#esoispArmorTypeLabel").show();
			$("#esoispArmorTrait").val(this.itemTrait);
			$("#esoispArmorType").val(this.armorType);
		}
	}
	else if (this.itemType == "4,12")
	{
		$("#esoispQuality").hide();
		$("#esoispQualityLabel").hide();
		$("#esoispJewelryTrait").hide();
		$("#esoispJewelryTraitLabel").hide();
		$("#esoispWeaponTrait").hide();
		$("#esoispWeaponTraitLabel").hide();
		$("#esoispArmorTrait").hide();
		$("#esoispArmorTraitLabel").hide();
		$("#esoispWeaponType1").hide();
		$("#esoispWeaponTypeLabel1").hide();
		$("#esoispWeaponType2").hide();
		$("#esoispWeaponTypeLabel2").hide();
		$("#esoispArmorType").hide();
		$("#esoispArmorTypeLabel").hide();	
	}
	else
	{
		$("#esoispQuality").show();
		$("#esoispQualityLabel").show();
		$("#esoispJewelryTrait").hide();
		$("#esoispJewelryTraitLabel").hide();
		$("#esoispWeaponTrait").hide();
		$("#esoispWeaponTraitLabel").hide();
		$("#esoispArmorTrait").hide();
		$("#esoispArmorTraitLabel").hide();
		$("#esoispWeaponType1").hide();
		$("#esoispWeaponTypeLabel1").hide();
		$("#esoispWeaponType2").hide();
		$("#esoispWeaponTypeLabel2").hide();
		$("#esoispArmorType").hide();
		$("#esoispArmorTypeLabel").hide();
	}
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


UESP.EsoItemSearchPopup.prototype.onUnequip = function()
{
	this.hide();
	$(document).trigger("EsoItemSearchPopupOnClose");
	
	if (this.onSelectItem) this.onSelectItem({}, this.sourceElement);
};


UESP.EsoItemSearchPopup.prototype.getSearchQueryParam = function()
{
	var queryParams = {};
	
	var text = $("#esoispInputText").val().trim();
	
	if (text != "") queryParams['text'] = text;
	if (this.itemType != null) queryParams['type'] = this.itemType;
	if (this.equipType != null) queryParams['equiptype'] = this.equipType;
	if (this.weaponType >= 0) queryParams['weapontype'] = this.weaponType;
	if (this.armorType != null) queryParams['armortype'] = this.armorType;
	if (this.itemTrait >= 0) queryParams['trait'] = this.itemTrait;
	queryParams['level'] = this.itemLevel;
	queryParams['quality'] = this.itemQuality;
	
	return queryParams;
}


UESP.EsoItemSearchPopup.prototype.onSearch = function() 
{
	var queryParams = this.getSearchQueryParam();
	
	if (this.sendSearchQuery(queryParams))
	{
		$("#esoispResults").text("Searching...");
		this.searchResults = [];
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


UESP.EsoItemSearchPopup.prototype.onResultClick = function(e, element)
{
	var itemId = element.attr("itemid");
	if (itemId == null || itemId == "") return false;
	
	var itemIndex = element.attr("itemindex");
	if (itemIndex == null || itemIndex == "") return false;
	
	var itemData = this.searchResults[itemIndex];
	if (itemData == null) return false;
	
	this.itemLevel = $("#esoispLevel").val();
	this.itemQuality = $("#esoispQuality").val();
	itemData.level = this.itemLevel;
	itemData.quality = this.itemQuality;
	
	if (this.onSelectItem) this.onSelectItem(itemData, this.sourceElement);
	
	this.onClose();
	return true;
}


UESP.EsoItemSearchPopup.prototype.displaySearchResults = function(itemData)
{
	var resultsElement = $("#esoispResults");
	var newResults = "";
	var self = this;
	
	this.searchResults = itemData;
	
	if (itemData.length == 0)
	{
		
		return;
	}
	
	for (var i = 0; i < itemData.length; ++i)
	{
		if (itemData[i].type < 0)
		{
			var rowCount = itemData[i].rowCount;
			
			if (rowCount == 0)
			{
				newResults += "No items found matching the input values!";
			}
			else if (rowCount > this.ROW_LIMIT)
			{
				rowCount -= 100;
				newResults += "Found " + rowCount + " more results! Try a more specific search query.";
			}
			
			continue;
		}
		
		newResults += this.createSearchResult(itemData[i], i);
	}
		
	resultsElement.html(newResults);
	
	$(".esoispResultRow").click(function(e) { self.onResultClick(e, $(this)); });
	$('#esoispResults .eso_item_link').hover(OnEsoItemLinkEnter, OnEsoItemLinkLeave);
}


UESP.EsoItemSearchPopup.prototype.createSearchResult = function(itemData, itemIndex)
{
	var resultHtml;
	var iconUrl = this.getIconUrl(itemData.icon);
	var niceName = itemData.name.charAt(0).toUpperCase() + itemData.name.slice(1);
	var details = "";
		
	if (this.itemType == 2)
	{
		var detailList = [];
		var traitName = UESP.ESO_TRAIT_TYPES[itemData.trait];
		var armorName = UESP.ESO_ARMOR_TYPES[itemData.armorType]
		
		if (armorName != null && armorName != "") detailList.push(armorName);
		if (traitName != null && traitName != "") detailList.push(traitName);
		
		details = "(" + detailList.join(", ") + ")";
	}
	
	this.itemLevel = $("#esoispLevel").val();
	this.itemQuality = $("#esoispQuality").val();
	
	var quality = Number(itemData.quality);
	if (isNaN(quality)) quality = this.itemQuality;
	
	var itemLinkData = 	"itemid='" + itemData.itemId + "' " + 
						"level='" + this.itemLevel + "' " +
						"quality='" + this.itemQuality + "' " +
						"itemindex='"+ itemIndex + "' " +
						"";
	var itemQualityClass = "eso_item_link_q" + quality;
	
	resultHtml = 	"<div class='esoispResultRow eso_item_link " + itemQualityClass + "' " + itemLinkData + ">" + 
						"<img class='esoispResultIcon' src='" + iconUrl + "'>" +
						"" + niceName +
						"<div class='esoispResultDetails'>" + details + "</div>" + 
					"</div>";
	
	return resultHtml;
}


UESP.EsoItemSearchPopup.prototype.getIconUrl = function(rawIcon)
{
	return this.iconURL + rawIcon.replace(".dds", ".png");
}


UESP.EsoItemSearchPopup.prototype.updateTitle = function()
{
	var titleElement = $("#esoispTitle");
	var title = "Select Item";
	
	if (this.itemType == 2)	// Armor
	{
		var equipType = UESP.ESO_EQUIP_TYPES[this.equipType];
		if (equipType != null) title = "Select Item for " + equipType + " Slot";
	}
	else if (this.itemType == 1) // Weapons
	{
		if (this.equipTypes.indexOf("7") >= 0)
			title = "Select Off Hand Weapon";
		else
			title = "Select Main Hand Weapon";
	}
	else if (this.itemType == 20) // Weapon Glyphs
	{
		title = "Select Weapon Enchantment";
	}
	else if (this.itemType == 21) // Armor Glyphs
	{
		title = "Select Armor Enchantment";
	}
	else if (this.itemType == 26) // Jewelry Glyphs
	{
		title = "Select Jewelry Enchantment";
	}
	else if (this.itemType == "4,12")
	{
		title = "Select Food or Drink";
	}
	else if (this.itemType == "4")
	{
		title = "Select Food";
	}
	else if (this.itemType == "12")
	{
		title = "Select Drink";
	}
	else if (this.itemType == "7")
	{
		title = "Select Potion";
	}
	else
	{
		title = "Select Item";
	}
	
	titleElement.text(title);
}


UESP.EsoItemSearchPopup.prototype.onLevelSlideChange = function(e)
{
	var newLevel = $("#esoispLevelSlider").val();
		
	$("#esoispLevel").val(newLevel);
	$(".esoispResultRow").attr("level", newLevel);
	this.itemLevel = newLevel;
}


UESP.EsoItemSearchPopup.prototype.onLevelChange = function(e)
{
	var newLevel = $("#esoispLevel").val();
	$(".esoispResultRow").attr("level", newLevel);
	this.itemLevel = newLevel;
}


UESP.EsoItemSearchPopup.prototype.onQualityChange = function(e)
{
	var newQuality = $("#esoispQuality").val();
	
	var itemQualityClass = "eso_item_link_q" + this.itemQuality;
	$(".esoispResultRow").removeClass(itemQualityClass);
	itemQualityClass = "eso_item_link_q" + newQuality;
	$(".esoispResultRow").addClass(itemQualityClass);
	
	$(".esoispResultRow").attr("quality", newQuality);
	this.itemQuality = newQuality;
}


UESP.EsoItemSearchPopup.prototype.onArmorTypeChange = function(e)
{
	this.armorType = $("#esoispArmorType").val();
}


UESP.EsoItemSearchPopup.prototype.onArmorTraitChange = function(e)
{
	$("#esoispWeaponTrait").val("-1");
	$("#esoispWeaponType1").val("-1");
	$("#esoispWeaponType2").val("-1");
	this.itemTrait = $("#esoispArmorTrait").val();
}


UESP.EsoItemSearchPopup.prototype.onJewelryTraitChange = function(e)
{
	this.itemTrait = $("#esoispJewelryTrait").val();
}


UESP.EsoItemSearchPopup.prototype.onWeaponTraitChange = function(e)
{
	$("#esoispArmorTrait").val("-1");
	this.itemTrait = $("#esoispWeaponTrait").val();
}


UESP.EsoItemSearchPopup.prototype.onWeaponType1Change = function(e)
{
	$("#esoispArmorTrait").val("-1");
	this.weaponType = $("#esoispWeaponType1").val();
}


UESP.EsoItemSearchPopup.prototype.onWeaponType2Change = function(e)
{
	$("#esoispArmorTrait").val("-1");
	this.weaponType = $("#esoispWeaponType2").val();
}


UESP.EsoItemSearchPopup.prototype.display = function(sourceElement, data)
{
	var clearResults = false;
	
	data = data || {};
		
	this.sourceElement = sourceElement;
	this.onSelectItem = data.onSelectItem;
	
	if (this.itemType != data.itemType)
	{
		this.itemType = data.itemType;
		clearResults = true;
	}
	
	if (this.equipType != data.equipType)
	{
		this.equipType = data.equipType;
		
		if (this.equipType == null)
			this.equipTypes = [];
		else
			this.equipTypes = data.equipType.split(",");
		
		clearResults = true;
	}
	
	if (this.weaponType != data.weaponType)
	{
		this.weaponType = data.weaponType;
		clearResults = true;
	}
	
	if (clearResults)
	{
		this.itemTrait = "-1";
		this.armorType = "-1";
		this.searchResults = [];
		$("#esoispResults").text("");
		//$("#esoispInputText").val("");
	}
	
	this.update();
	this.updateTitle();
	this.show();
}


	/* Main function entrance */
UESP.showEsoItemSearchPopup = function(sourceElement, data)
{

	if (UESP.esoItemSearchPopup == null)
	{
		UESP.esoItemSearchPopup = new UESP.EsoItemSearchPopup();
	}
	
	UESP.esoItemSearchPopup.display(sourceElement, data);
	
	return UESP.esoItemSearchPopup;
}