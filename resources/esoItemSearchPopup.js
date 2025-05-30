	/* Global namespace */
if (window.UESP == null)
{
	window.UESP = {};
}

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
		//"17" : "Prosperous",
		"17" : "Invigorating",
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
		
		"31" : "Bloodthirsty",	// Update 18
		"29" : "Harmony",
		"33" : "Infused",
		"27" : "Intricate",
		"32" : "Protective",
		"28" : "Swift",
		"30" : "Triune",
		
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
		
		// Update 30 Companion traits
		"34": "Quickened",
		"35": "Prolific",
		"36": "Focused",
		"37": "Shattering",
		"38": "Aggressive",
		"39": "Soothing",
		"40": "Augmented",
		"41": "Bolstered",
		"42": "Vigorous",
		
		"43": "Quickened",
		"44": "Prolific",
		"45": "Focused",
		"46": "Shattering",
		"47": "Aggressive",
		"48": "Soothing",
		"49": "Augmented",
		"50": "Bolstered",
		"51": "Vigorous",
		
		"52": "Quickened",
		"53": "Prolific",
		"54": "Focused",
		"55": "Shattering",
		"56": "Aggressive",
		"57": "Soothing",
		"58": "Augmented",
		"59": "Bolstered",
		"60": "Vigorous",
};


UESP.ESO_ARMOR_TYPES = 
{
		"-1" : "",
		"0"  : "",
		"1"  : "Light",
		"2"  : "Medium",
		"3"  : "Heavy",	
};


UESP.ESO_WEAPON_TYPES =
{
		"-1" : "",
		"0" : "",
		"1" : "Axe",
		"2" : "Hammer",
		"3" : "Sword",
		"4" : "2H Sword",
		"5" : "2H Axe",
		"6" : "2H Hammer",
		"7" : "Prop",
		"8" : "Bow",
		"9" : "Restoration Staff",
		"10" : "Rune",
		"11" : "Dagger",
		"12" : "Fire Staff",
		"13" : "Frost Staff",
		"14" : "Shield",
		"15" : "Lightning Staff",
};


UESP.ESO_ITEMQUALITYLEVEL_INTTYPEMAP = 
{
		 1 : [1,  30,  31,  32,  33,  34],
		 4 : [1,  25,  26,  27,  28,  29],
		 6 : [1,  20,  21,  22,  23,  24],
		50 : [1,  20,  21,  22,  23,  24], //?
		51 : [1, 125, 135, 145, 155, 165],
		52 : [1, 126, 136, 146, 156, 166],
		53 : [1, 127, 137, 147, 157, 167],
		54 : [1, 128, 138, 148, 158, 168],
		55 : [1, 129, 139, 149, 159, 169],
		56 : [1, 130, 140, 150, 160, 170],
		57 : [1, 131, 141, 151, 161, 171],
		58 : [1, 132, 142, 152, 162, 172],
		59 : [1, 133, 143, 153, 163, 173],
		60 : [1, 134, 144, 154, 164, 174],
		61 : [1, 236, 237, 238, 239, 240],
		62 : [1, 254, 255, 256, 257, 258],
		63 : [1, 272, 273, 274, 275, 276],
		64 : [1, 290, 291, 292, 293, 294],
		65 : [1, 308, 309, 310, 311, 312],
		66 : [1, 366, 367, 368, 369, 370],
};

UESP.ESO_ITEMQUALITYLEVEL_INTTYPEMAP_JEWELRY = 
{
		 1 : [1,  30,  31,  32,  33,  34],
		 4 : [1,  25,  26,  27,  28,  29],
		 6 : [1,  20,  21,  22,  23,  24],
		50 : [1,  20,  21,  22,  23,  24],  //?
		51 : [1, 125, 135, 145, 155, 165],
		52 : [1, 126, 136, 146, 156, 166],
		53 : [1, 127, 137, 147, 157, 167],
		54 : [1, 128, 138, 148, 158, 168],
		55 : [1, 129, 139, 149, 159, 169],
		56 : [1, 130, 140, 150, 160, 170],
		57 : [1, 131, 141, 151, 161, 171],
		58 : [1, 132, 142, 152, 162, 172],
		59 : [1, 133, 143, 153, 163, 173],
		60 : [1, 134, 144, 154, 164, 174],
		61 : [1, 236, 237, 238, 239, 240],
		62 : [1, 254, 255, 256, 257, 258],
		63 : [1, 272, 273, 274, 275, 276],
		64 : [1, 290, 291, 292, 293, 294],
		65 : [1, 308, 309, 310, 311, 312],
		// 66 : [1, 365, 359, 362, 363, 364], // Old values
		66 : [1, 366, 367, 368, 369, 370],
};


	/* Class Constructor */
UESP.EsoItemSearchPopup = function () 
{
	this.ROW_LIMIT = 150;
	
	this.iconURL = "//esoicons.uesp.net";
	this.queryURL = "//esolog.uesp.net/esoItemSearchPopup.php";
	this.rootElement = this.create();
	this.sourceElement = null;
	this.isItemEnabled = true;
	this.onlyMaxLevel = false;
	this.onSelectItem = null;
	this.searchResults = [];
	
	this.itemLevel = "1";
	this.itemQuality = "1";
	this.itemIntLevel = "1";
	this.itemIntType = "1";
	this.itemType = null;
	this.equipType = null;
	this.equipTypes = [];
	this.weaponType = "-1";
	this.armorType = "-1";
	this.itemTrait = "-1";
	this.foodQuality = "-1";
	this.foodType = "4,12";
	this.xoffset = 0;
	this.yoffset = 0;
	this.version = "";
	this.searchType = "contains";
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
	$("#esoispDisableButton").click(function() { self.onDisableToggle(); });
	$("#esoispLevel").on("input", function(e) { self.onLevelChange(e); });
	$("#esoispLevelSlider").on("input", function(e) { self.onLevelSlideChange(e); });
	$("#esoispQuality").change(function(e) { self.onQualityChange(e); });
	$("#esoispArmorTrait").change(function(e) { self.onArmorTraitChange(e); });
	$("#esoispArmorType").change(function(e) { self.onArmorTypeChange(e); });
	$("#esoispJewelryTrait").change(function(e) { self.onJewelryTraitChange(e); });
	$("#esoispWeaponTrait").change(function(e) { self.onWeaponTraitChange(e); });
	$("#esoispWeaponType1").change(function(e) { self.onWeaponType1Change(e); });
	$("#esoispWeaponType2").change(function(e) { self.onWeaponType2Change(e); });
	$("#esoispFoodQuality").change(function(e) { self.onFoodQualityChange(e); });
	$("#esoispFoodType").change(function(e) { self.onFoodTypeChange(e); });
	$("#esoispSearchType").change(function(e) { self.onSearchTypeChange(e); });
	
	$("#esoispInputText").keyup(function (e) {
	    if (e.keyCode == 13) {
	    	self.onSearch();
	    	this.blur();
	    }
	});
	
	$("#esoispResults").scroll(function(e) { self.onResultsScroll(e); })
	
	return rootElement;
}


UESP.EsoItemSearchPopup.prototype.parseLevel = function(level)
{
	if (level == null) return 66;
	
	var vetRank = level.match(/^\s*V(\d+)|\s*VR(\d+)/i);
	var cpLevel = level.match(/^\s*CP(\d+)/i);
	
	if ($.isNumeric(level))
	{
		level =  parseInt(level);
	}
	else if (vetRank != null)
	{
		level = parseInt(vetRank[1]) + 50;
	}
	else if (cpLevel != null)
	{
		level =  Math.floor(parseInt(cpLevel[1])/10) + 50;
	}
	else
	{
		level = parseInt(level);
	}
	
	if (isNaN(level)) return 66;
	if (level > 66) return 66;
	if (level < 1) return 1;
	return level;
}


UESP.EsoItemSearchPopup.prototype.findInternalLevelType = function(level, quality, equipType)
{
	level = this.parseLevel(level);
	var result = { type: 1, level: level };
	var typeMap = UESP.ESO_ITEMQUALITYLEVEL_INTTYPEMAP;
	
	if (result.level > 50) result.level = 50;
	if (result.level <  1) result.level = 1;
	
	if (equipType == 12 || equipType == 2) typeMap = UESP.ESO_ITEMQUALITYLEVEL_INTTYPEMAP_JEWELRY;
	
	for (var l in typeMap)
	{
		var levelData = typeMap[l];
		
		if (level <= l)
		{
			if (levelData[quality] == null) return result;
			result.type = levelData[quality];
			break;
		}
	}
	
	return result;
}


UESP.EsoItemSearchPopup.prototype.formatLevel = function(level)
{
	if (level <= 50) return level;
	return "CP" + (level - 50)*10;
}


UESP.EsoItemSearchPopup.prototype.getPopupRootText = function()
{
	return "" +
		"<div id='esoispCloseButton'>x</div>" +
		"<div id='esoispTitle'>Select Item</div>" + 
		"<div id='esoispInputs'>" + 
		"	<p>" +
		"	<div class='esoispInputLabel'>Find Text</div> " +
		"	<div id='esoispSearchTypeRoot'>" +
		"		<select id='esoispSearchType' name='searchtype'>" +
		"			<option value='contains' selected>Contains Text</option>" +
		"			<option value='startswith'>Starts With Text</option>" +
		"			<option value='setstarts'>Set Name Starts With</option>" +
		"			<option value='setcontains'>Set Name Contains</option>" +
		"		</select>" +
		"		<input id='esoispInputText' type='text' name='text' value=''>" +
		"	</div>" +
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
		"		<option value='31'>Bloodthirsty</option>" +
		"		<option value='29'>Harmony</option>" +
		"		<option value='21'>Healthy</option>" +
		"		<option value='33'>Infused</option>" +
		"		<option value='32'>Protective</option>" +
		"		<option value='23'>Robust</option>" +
		"		<option value='28'>Swift</option>" +
		"		<option value='30'>Triune</option>" +
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
		"		<option value='17'>Invigorating</option>" +
		"		<option value='25'>Nirnhoned</option>" +
		"		<option value='13'>Reinforced</option>" +
		"		<option value='11'>Sturdy</option>" +
		"		<option value='15'>Training</option>" +
		"		<option value='14'>Well Fitted</option>" +
		"	</select>" +
		"	<div class='esoispInputLabel' id='esoispFoodQualityLabel'>Quality</div> <select id='esoispFoodQuality' type='text' name='foodQuality'>" +
		"		<option value='-1'>Any</option>" +
		"		<option value='1'>Normal</option>" +
		"		<option value='2'>Fine</option>" +
		"		<option value='3'>Superior</option>" +
		"		<option value='4'>Epic</option>" +
		"		<option value='5'>Legendary</option>" +
		"	</select>" +
		"	<div class='esoispInputLabel' id='esoispFoodTypeLabel'>Food Type</div> <select id='esoispFoodType' type='text' name='foodType'>" +
		"		<option value='4,12'>Any</option>" +
		"		<option value='4'>Food</option>" +
		"		<option value='12'>Drink</option>" +
		"	</select>" +
		"	<br/>" +
		"	<div id='esoispAnyLevelRoot' style='display: none;'><input id='esoispAnyLevel' type='checkbox' name='anylevel' value='1'> Any</div>" + 
		"	<div class='esoispInputLabel'>Level</div> <input id='esoispLevel' type='text' name='level' value='CP160'>" +
		"	<input id='esoispLevelSlider' type='range' min='1' max='66' value='66'><br/>" + 
		"	<button id='esoispUneqipButton' class='esoispButton'>Unequip</button>" +
		"	<button id='esoispDisableButton' class='esoispButton'>Disable</button>" +
		"	<div id='esoispResultText'></div>" + 
		"	<button id='esoispSearchButton' class='esoispButton'>Search...</button>" +
		"</div>" +
		"<div id='esoispResults'></div>" + 
		"";
}


UESP.EsoItemSearchPopup.prototype.adjustPosition = function()
{
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var toolTipWidth = this.rootElement.width();
    var toolTipHeight = this.rootElement.height();
    var elementHeight = this.sourceElement.height();
    var elementWidth = this.sourceElement.width();
     
    var top = this.sourceElement.offset().top - 150 + this.yoffset;
    var left = this.sourceElement.offset().left + this.sourceElement.outerWidth() + 3 + this.xoffset;
     
    this.rootElement.offset({ top: top, left: left });
     
    var viewportTooltip = this.rootElement[0].getBoundingClientRect();
     
    if (viewportTooltip.bottom > windowHeight) 
    {
    	var deltaHeight = viewportTooltip.bottom - windowHeight + 10;
        top = top - deltaHeight
    }
    else if (viewportTooltip.top < 0)
    {
    	var deltaHeight = viewportTooltip.top - 10;
        top = top - deltaHeight
    }
         
    if (viewportTooltip.right > windowWidth) 
    {
    	left = left - toolTipWidth - this.rootElement.width() - 28;
    }
     
    this.rootElement.offset({ top: top, left: left });
    viewportTooltip = this.rootElement[0].getBoundingClientRect();
     
    if (viewportTooltip.left < 0 )
    {
    	var el = $('<i/>').css('display','inline').insertBefore(this.rootElement[0]);
        var realOffset = el.offset();
        el.remove();
         
        left = realOffset.left - toolTipWidth - 3;
        this.rootElement.offset({ top: top, left: left });
    }
     
}


UESP.EsoItemSearchPopup.prototype.update = function()
{
	this.updateLevelQuality();
	
	if (this.itemType == 1) // Weapons
	{
		$("#esoispAnyLevelRoot").hide();
		$("#esoispQuality").show();
		$("#esoispQualityLabel").show();
		$("#esoispArmorType").hide();
		$("#esoispArmorTypeLabel").hide();
		$("#esoispJewelryTrait").hide();
		$("#esoispJewelryTraitLabel").hide();
		$("#esoispWeaponTrait").show();
		$("#esoispWeaponTraitLabel").show();
		$("#esoispFoodType").hide();
		$("#esoispFoodTypeLabel").hide();
		$("#esoispFoodQuality").hide();
		$("#esoispFoodQualityLabel").hide();
		$("#esoispWeaponTrait").val(this.itemTrait);
		
		if ($("#esoispWeaponTrait").val() == null) 
		{
			this.itemTrait = -1;
			$("#esoispWeaponTrait").val(-1);
		}
				
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
			
			if ($("#esoispWeaponType2").val() == null)
			{
				this.weaponType = -1;
				$("#esoispWeaponTrait2").val(-1);
			}
			
			if ($("#esoispArmorTrait").val() == null)
			{
				$("#esoispArmorTrait").val(-1);
			}
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
			
			if ($("#esoispWeaponType1").val() == null)
			{
				this.weaponType = -1;
				$("#esoispWeaponTrait1").val(-1);
			}
		}
		
	}
	else if (this.itemType == 2) // Armor
	{
		$("#esoispAnyLevelRoot").hide();
		$("#esoispQuality").show();
		$("#esoispQualityLabel").show();
		$("#esoispWeaponType1").hide();
		$("#esoispWeaponTypeLabel1").hide();
		$("#esoispWeaponType2").hide();
		$("#esoispWeaponTypeLabel2").hide();
		$("#esoispFoodType").hide();
		$("#esoispFoodTypeLabel").hide();
		$("#esoispFoodQuality").hide();
		$("#esoispFoodQualityLabel").hide();
		
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
			
			if ($("#esoispJewelryTrait").val() == null)
			{
				this.itemType = -1;
				$("#esoispJewelryTrait").val(-1);
			}
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
			
			if ($("#esoispArmorTrait").val() == null)
			{
				this.itemTrait = -1;
				$("#esoispArmorTrait").val(-1);
			}
			
			if ($("#esoispArmorType").val() == null)
			{
				this.armorType = -1;
				$("#esoispArmorType").val(-1);
			}
		}
	}
	else if (this.itemType == "4,12" || this.itemType == 4 || this.itemType == 12)
	{
		$("#esoispAnyLevelRoot").show();
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
		$("#esoispFoodType").show();
		$("#esoispFoodTypeLabel").show();
		$("#esoispFoodQuality").show();
		$("#esoispFoodQualityLabel").show();
	}
	else
	{
		$("#esoispAnyLevelRoot").hide();
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
		$("#esoispFoodType").hide();
		$("#esoispFoodTypeLabel").hide();
		$("#esoispFoodQuality").hide();
		$("#esoispFoodQualityLabel").hide();
	}
	
	if (this.version != "")
	{
		$("#esoispLevel").val("CP160");
		$("#esoispLevelSlider").val(66);
		//$('#esoispQuality').val(5);
		
		$('#esoispLevel').prop('readonly', true);
		$('#esoispLevelSlider').prop('disabled', true);
		//$('#esoispQuality').prop('disabled', true);
	}
	else
	{
		if (this.onlyMaxLevel)
		{
			$("#esoispLevel").val("CP160");
			$("#esoispLevelSlider").val(66);
			$('#esoispLevel').prop('readonly', true);
			$('#esoispLevelSlider').prop('disabled', true);
		}
		else
		{
			$('#esoispLevel').prop('readonly', false);
			$('#esoispLevelSlider').prop('disabled', false);
			//$('#esoispQuality').prop('disabled', false);
		}
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


UESP.EsoItemSearchPopup.prototype.onDisableToggle = function()
{
	this.hide();
	$(document).trigger("EsoItemSearchPopupOnClose");
	
	this.isItemEnabled = !this.isItemEnabled;
	
	if (this.onSelectItem) this.onSelectItem({toggleEnable: true, isEnabled:this. isItemEnabled }, this.sourceElement);
}


UESP.EsoItemSearchPopup.prototype.getSearchQueryParam = function()
{
	var queryParams = {};
	
	var text = $("#esoispInputText").val().trim();
	
	queryParams['anylevel'] = null;
	if ($("#esoispAnyLevelRoot").is(":visible")) queryParams['anylevel'] = $("#esoispAnyLevel").is(":checked") ? 1 : 0;
	
	if (text != "") queryParams['text'] = text;
	if (this.itemType != null) queryParams['type'] = this.itemType;
	if (this.equipType != null) queryParams['equiptype'] = this.equipType;
	if (this.weaponType >= 0) queryParams['weapontype'] = this.weaponType;
	if (this.armorType != null) queryParams['armortype'] = this.armorType;
	if (this.itemTrait >= 0) queryParams['trait'] = this.itemTrait;
	if (this.version != "") queryParams['version'] = this.version;
	if (this.searchType != "") queryParams['searchtype'] = this.searchType;
	
	this.updateLevelQuality();
	
	if (this.itemType == 4 || this.itemType == 12 || this.itemType == "4,12")
	{
		queryParams['type'] = this.foodType;
		queryParams['level'] = this.parseLevel(this.itemLevel);
		if (this.foodQuality > 0) queryParams['quality'] = this.foodQuality;
		//queryParams['quality'] = this.itemQuality;
	}
	else
	{
		queryParams['intlevel'] = this.itemIntLevel;
		queryParams['inttype'] = this.itemIntType;
	}
	
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
	$("#esoispResultText").text("");
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
	
	this.updateLevelQuality();
	
	itemData.level = this.parseLevel(this.itemLevel);
	itemData.quality = this.itemQuality;
	itemData.internalLevel = this.itemIntLevel;
	itemData.internalSubtype  = this.itemIntType;
	
	if (this.onSelectItem) this.onSelectItem(itemData, this.sourceElement);
	
	this.onClose();
	return true;
}


UESP.EsoItemSearchPopup.prototype.displaySearchResults = function(itemData)
{
	var resultsElement = $("#esoispResults");
	var newResults = "";
	var self = this;
	var totalRowCount = -1;
	
	this.searchResults = itemData;
	
	if (itemData.length == 0)
	{
		
		return;
	}
	
	for (var i = 0; i < itemData.length; ++i)
	{
		if (itemData[i].type < 0)
		{
			totalRowCount = itemData[i].rowCount;
			
			if (totalRowCount == 0)
			{
				
				if (this.itemTrait <= 0)
					newResults += "No items found matching the input values!";
				else
					newResults += "No items found matching the input values!<br/><br/>If you can't find the desired trait choose any trait and then \"Transmute\" it.";
			}
			else if (totalRowCount > this.ROW_LIMIT)
			{
				var rowCount = totalRowCount - this.ROW_LIMIT;
				newResults += "Found " + rowCount + " more results! Try a more specific search query.";
			}
			
			continue;
		}
		
		newResults += this.createSearchResult(itemData[i], i);
	}
	
	resultsElement.html(newResults);
	
	$("#esoispResultText").text("Showing " + (itemData.length - 1) + " of " + totalRowCount + " results");
	
	$(".esoispResultRow").click(function(e) { self.onResultClick(e, $(this)); });
	$('#esoispResults .eso_item_link').hover(OnEsoItemLinkEnter, OnEsoItemLinkLeave);
}


UESP.EsoItemSearchPopup.prototype.updateLevelQuality = function()
{
	this.itemLevel = $("#esoispLevel").val();
	this.itemQuality = $("#esoispQuality").val();
	
	var intLevelType = this.findInternalLevelType(this.itemLevel, this.itemQuality, this.equipType);
	
	this.itemIntLevel = intLevelType.level;
	this.itemIntType  = intLevelType.type;
}


UESP.EsoItemSearchPopup.prototype.createSearchResult = function(itemData, itemIndex)
{
	var resultHtml;
	var iconUrl = this.getIconUrl(itemData.icon);
	var niceName = itemData.name.charAt(0).toUpperCase() + itemData.name.slice(1);
	var details = "";
	var detailList = [];
		
	if (this.itemType == 2)
	{
		var traitName = UESP.ESO_TRAIT_TYPES[itemData.trait];
		var armorName = UESP.ESO_ARMOR_TYPES[itemData.armorType]
		
		if (armorName != null && armorName != "") detailList.push(armorName);
		if (traitName != null && traitName != "") detailList.push(traitName);
	}
	else if (this.itemType == 1)
	{
		var traitName = UESP.ESO_TRAIT_TYPES[itemData.trait];
		var weaponName = UESP.ESO_WEAPON_TYPES[itemData.weaponType];
		
		if (weaponName != null && weaponName != "") detailList.push(weaponName);
		if (traitName != null && traitName != "") detailList.push(traitName);
		
	}
	else if (this.itemType == "4,12")
	{
		if (itemData.type == 4)
			detailList.push("Food");
		else if (itemData.type == 12)
			detailList.push("Drink");
		
		detailList.push("Level " + itemData.level);
	}
	
	if (detailList.length > 0) details = "(" + detailList.join(", ") + ")";
	
	var quality = Number(itemData.quality);
	if (isNaN(quality)) quality = this.itemQuality;
	
	var itemLinkData = 	"itemid='" + itemData.itemId + "' " + 
						"intlevel='" + this.itemIntLevel + "' " +
						"inttype='" + this.itemIntType + "' " +
						"itemindex='"+ itemIndex + "' " +
						"";
	var itemQualityClass = "eso_item_link_q" + quality;
	
	if (this.version != "")
	{
		itemLinkData += " version='" + this.version + "' ";
	}
	
	resultHtml = 	"<div class='esoispResultRow eso_item_link " + itemQualityClass + "' " + itemLinkData + ">" + 
						"<img class='esoispResultIcon' src='" + iconUrl + "'>" +
						"" + niceName +
						"<div class='esoispResultDetails'>" + details + "</div>" + 
					"</div>";
	
	return resultHtml;
}


UESP.EsoItemSearchPopup.prototype.getIconUrl = function(rawIcon)
{
	var iconName = rawIcon.replace(".dds", ".png");
	if (iconName == "") return this.iconURL + "/unknown.png";
	return this.iconURL + iconName;
}


UESP.EsoItemSearchPopup.prototype.updateDisableButton = function()
{
	if (this.isItemEnabled)
		$("#esoispDisableButton").text("Disable");
	else
		$("#esoispDisableButton").text("Enable");
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
	else if (this.itemType == "30")
	{
		title = "Select Poison";
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
		
	$("#esoispLevel").val(this.formatLevel(newLevel));
	//$(".esoispResultRow").attr("level", newLevel);
	
	this.updateLevelQuality();
	
	$(".esoispResultRow").attr("intlevel", this.itemIntLevel);
	$(".esoispResultRow").attr("inttype", this.itemIntType);
}


UESP.EsoItemSearchPopup.prototype.onLevelChange = function(e)
{
	var newLevel = this.parseLevel($("#esoispLevel").val());
	$("#esoispLevelSlider").val(newLevel);
	
	this.updateLevelQuality();
	
	$(".esoispResultRow").attr("intlevel", this.itemIntLevel);
	$(".esoispResultRow").attr("inttype", this.itemIntType);
}


UESP.EsoItemSearchPopup.prototype.onQualityChange = function(e)
{
	var newQuality = $("#esoispQuality").val();
	
	var itemQualityClass = "eso_item_link_q" + this.itemQuality;
	$(".esoispResultRow").removeClass(itemQualityClass);
	itemQualityClass = "eso_item_link_q" + newQuality;
	$(".esoispResultRow").not(".eso_item_link_q6").addClass(itemQualityClass);
		
	this.updateLevelQuality();
	
	$(".esoispResultRow").attr("intlevel", this.itemIntLevel);
	$(".esoispResultRow").attr("inttype", this.itemIntType);
}


UESP.EsoItemSearchPopup.prototype.onFoodQualityChange = function(e)
{
	this.foodQuality = $("#esoispFoodQuality").val();
}


UESP.EsoItemSearchPopup.prototype.onFoodTypeChange = function(e)
{
	this.foodType = $("#esoispFoodType").val();
}


UESP.EsoItemSearchPopup.prototype.onArmorTypeChange = function(e)
{
	this.armorType = $("#esoispArmorType").val();
}


UESP.EsoItemSearchPopup.prototype.onSearchTypeChange = function(e)
{
	this.searchType = $("#esoispSearchType").val();
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


UESP.EsoItemSearchPopup.prototype.getEquipTypeGroup = function(equipType)
{
	if (equipType == null || equipType == "") "None";
	
	if (equipType == 30) return "Poison";
	if (equipType == 2 || equipType == 12) return "Jewelry";
	if (equipType == "5,6" || equipType == 6 || equipType == 5) return "MainHand";
	if (equipType == "5,7" || equipType == 7) return "OffHand";
	if (equipType == 1 || equipType == 3 || equipType == 4 || equipType == 8 || equipType == 9 || equipType == 10 || equipType == 13) return "Armor";
	
	return "Other";
}


UESP.EsoItemSearchPopup.prototype.areEquipTypesSameGroup = function(equipType1, equipType2)
{
	return this.getEquipTypeGroup(equipType1) == this.getEquipTypeGroup(equipType2);
}


UESP.EsoItemSearchPopup.prototype.display = function(sourceElement, data)
{
	var clearResults = false;
	var clearSettings = false;
	
	data = data || {};
	
	this.version = data.version || "";
	this.sourceElement = sourceElement;
	this.onSelectItem = data.onSelectItem;
	this.isItemEnabled = data.isEnabled;
	this.onlyMaxLevel = data.onlyMaxLevel;
	
	this.xoffset = 0;
	this.yoffset = 0;
	
	if (data.xoffset != null) this.xoffset = data.xoffset;
	if (data.yoffset != null) this.yoffset = data.yoffset;
	
	if (this.itemType != data.itemType)
	{
		this.itemType = data.itemType;
		clearResults = true;
		clearSettings = true;
	}
	
	if (this.equipType != data.equipType)
	{
		if (!this.areEquipTypesSameGroup(this.equipType, data.equipType))
		{
			clearSettings = true;
		}
		
		this.equipType = data.equipType;
		
		if (this.equipType == null)
			this.equipTypes = [];
		else
			this.equipTypes = data.equipType.split(",");
		
		clearResults = true;
	}
	
	if (clearSettings)
	{
		this.itemTrait = "-1";
		this.armorType = "-1";
		this.weaponType = "-1";
	}
	
	if (clearResults)
	{
		this.searchResults = [];
		$("#esoispResults").text("");
	}
	
	this.update();
	this.updateTitle();
	this.updateDisableButton();
	this.show();
	this.adjustPosition();
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
