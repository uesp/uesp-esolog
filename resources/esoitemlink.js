window.ESO_ITEM_QUALITIES = {
		'-1': "",
		0: "Trash",
		1: "Normal",
		2: "Fine",
		3: "Superior",
		4: "Epic",
		5: "Legendary",
		6: "Mythic",
};


window.ESO_ITEM_TRAITS = {
		'-1': "",
		18: "Divines",
		17: "Exploration",
		12: "Impenetrable",
		16: "Infused",
		20: "Intricate",
		19: "Ornate",
		13: "Reinforced",
		11: "Sturdy",
		15: "Training",
		14: "Well Fitted",
		22: "Arcane",
		21: "Health",
		24: "Ornate",
		23: "Robust",
		
		31: "Bloodthirsty",	// Update 18
		29: "Harmony",
		33: "Infused",
		27: "Intricate",
		32: "Protective",
		28: "Swift",
		30: "Triune",
		
		0: "None",
		2: "Charged",
		5: "Defending",
		4: "Infused",
		9: "Intricate",
		10: "Ornate",
		1: "Powered",
		3: "Precise",
		7: "Sharpened",
		6: "Training",
		8: "Weighted",
		25: "Nirnhoned",
		26: "Nirnhoned",
		
		// Update 30 Companion traits
		34: "Quickened",
		35: "Prolific",
		36: "Focused",
		37: "Shattering",
		38: "Aggressive",
		39: "Soothing",
		40: "Augmented",
		41: "Bolstered",
		42: "Vigorous",
		
		43: "Quickened",
		44: "Prolific",
		45: "Focused",
		46: "Shattering",
		47: "Aggressive",
		48: "Soothing",
		49: "Augmented",
		50: "Bolstered",
		51: "Vigorous",
		
		52: "Quickened",
		53: "Prolific",
		54: "Focused",
		55: "Shattering",
		56: "Aggressive",
		57: "Soothing",
		58: "Augmented",
		59: "Bolstered",
		60: "Vigorous",
};


function GetEsoItemTraitText(trait)
{
	var traitText = ESO_ITEM_TRAITS[trait];
	if (traitText == null) return '' + trait;
	return traitText;
}


function GetEsoItemQualityText(quality)
{
	var qualityText = ESO_ITEM_QUALITIES[quality];
	if (qualityText == null) return '' + quality;
	return qualityText; 
}


function GetEsoItemLevelHeaderText(level, quality)
{
	return GetEsoItemQualityText(quality) + " " + GetEsoItemFullLevelText(level);
}


function GetEsoItemLevelText(level)
{
	if (itemRawVersion >= 10) 
	{
		if (level > 50) return '50 CP' + ((level - 50)*10);
		return '' + level;
	}
	
	if (level > 50) return 'V' + (level - 50);
	return '' + level;
}


function GetEsoItemFullLevelText(level)
{
	if (itemRawVersion >= 10) 
	{
		if (level > 50) return 'Level 50 CP' + ((level - 50)*10);
		return 'Level ' + level;
	}
	
	if (level > 50) return 'Rank V' + (level - 50);
	return 'Level ' + level;
}


function GetEsoItemFullLevelHtml(level)
{
	if (itemRawVersion >= 10) 
	{
		if (level > 50) return "LEVEL <div id='esoil_itemlevel'>50</div>";
		return "LEVEL <div id='esoil_itemlevel'>" + level + "</div>";
	}
	
	if (level > 50) return "<img src='//esoitem.uesp.net/resources/eso_item_veteranicon.png' /> RANK <div id='esoil_itemlevel'>" + (level - 50) + "</div>"
	return "LEVEL <div id='esoil_itemlevel'>" + level + "</div>";
}


function GetEsoItemCPHtml(level)
{
	if (itemRawVersion >= 10) 
	{
		if (level <= 50) return "";
		return "<img src='//esoitem.uesp.net/resources/champion_icon.png' class='esoil_cpimg'>CP <div id='esoil_itemlevel'>" + ((level - 50)*10) + "</div>"
	}

	return "";
}


function MergeEsoItemData(itemData1, itemData2)
{
	var newObject = jQuery.extend({}, itemData1);
	
	for (var key in itemData2)
	{
		if (newObject[key] == null) newObject[key] = itemData2[key];
	}
	
	return newObject;
}


function FindEsoItemData(level, quality)
{
	var firstItem = allItemData[0];
	
	for (var i = 0; i < allItemData.length; i++)
	{
		var itemData = allItemData[i];
		if (itemData['level'] == level && itemData['quality'] == quality) return MergeEsoItemData(itemData, firstItem);
	}
	
	return null;
}


function FormatEsoDescriptionText(text)
{
	if (text == null || text == "") return "";
	var output = text.replace(/ by ([0-9\-\.]+)/g, " by <div class='esoil_white'>$1</div>");
	output = output.replace(/Adds ([0-9\-\.]+)/g, "Adds <div class='esoil_white'>$1</div>");
	output = output.replace(/for ([0-9\-\.]+)%/g, "for <div class='esoil_white'>$1</div>%");
	output = output.replace(/\|c([0-9a-fA-F]{6})([a-zA-Z \-0-9\.]+)\|r/g, "<div style='color:#$1;display:inline;'>$2</div>");
	output = output.replace(/\n/g, "<br />");
	return output;
}


function GetEsoItemLeftBlockHtml(itemData)
{
	var type = itemData.type;
	
	if (type == 1) {
		if (itemData.equipType == 7) // shield
			return "ARMOR <div id='esoil_itemleft'>" + itemData.armorRating + "</div>";
		else
			return "DAMAGE <div id='esoil_itemleft'>" + itemData.weaponPower + "</div>";
	}
	
	if (type == 2) {
		if (itemData.equipType == 2 || itemData.equipType == 12) // neck/ring
			return "";
		else
			return "ARMOR <div id='esoil_itemleft'>" + itemData.armorRating + "</div>";
	}
	
	return "";
}


function GetEsoItemEnchantBlockHtml(itemData)
{
	var output = "";
	
		/* TODO: Temp fix for potions showing enchantments/sets */
	if (itemData.type == 7) return "";
	
	if (itemData.enchantName1 != null && itemData.enchantDesc1 != "")
	{
		var enchantName = itemData.enchantName1.toUpperCase();
		var enchantDesc = FormatEsoDescriptionText(itemData.enchantDesc1);
		output += "<div class='esoil_white esoil_small'>" + enchantName + "</div><br/>" + enchantDesc;
	}
	
	if (itemData.enchantName2 != null && itemData.enchantDesc2 != "")
	{
		var enchantName = itemData.enchantName2.toUpperCase();
		var enchantDesc = FormatEsoDescriptionText(itemData.enchantDesc2);
		output += "<br style='margin-top:0.7em; margin-bottom: 0.7em;'/><div class='esoil_white esoil_small'>" + enchantName + "</div><br/>" + enchantDesc;
	}
	
	if (output == "" && itemData.enchantName != null && itemData.enchantDesc != "")
	{
		var enchantName = itemData.enchantName.toUpperCase();
		var enchantDesc = FormatEsoDescriptionText(itemData.enchantDesc);
		output += "<div class='esoil_white esoil_small'>" + enchantName + "</div><br/>" + enchantDesc;
	}
	
	return output;
}


function GetEsoItemTraitBlockHtml(itemData)
{
	if (itemData.trait == null) return "";
	var trait = itemData.trait;
	var traitDesc = FormatEsoDescriptionText(itemData.traitDesc);
	var traitName = GetEsoItemTraitText(trait).toUpperCase();
	
	if (trait <= 0) return "";
	return "<div class='esoil_white esoil_small'>" + traitName + "</div><br />" + traitDesc;
}


function GetEsoItemSetBlockHtml(itemData)
{
		/* TODO: Temp fix for potions showing enchantments/sets */
	if (itemData.type == 7) return "";
	
	if (itemData.setName == null) return "";
	var setName = itemData.setName.toUpperCase();
	if (setName == "") return "";
	
	var setMaxEquipCount = itemData.setMaxEquipCount;
	var setBonusCount = parseInt(itemData.setBonusCount);
	var output = "<div class='esoil_white esoil_small'>PART OF THE " + setName + " SET (" + setMaxEquipCount + "/" + setMaxEquipCount + " ITEMS)</div>";
	
	for (var i = 1; i <= 12; i += 1)
	{
		var setCount = itemData['setBonusCount'  + i];
		var setDesc = itemData['setBonusDesc'  + i]
		if (setDesc == null || setDesc == "") continue;
		
		setDesc = FormatEsoDescriptionText(setDesc);
		output += "<br />" + setDesc;
	}
	
	return output;
}


function GetEsoItemAbilityBlockHtml(itemData)
{
	if (itemData.abilityName == null) return "";
	var ability = itemData.abilityName.toUpperCase();
	var abilityDesc = FormatEsoDescriptionText(itemData.abilityDesc);
	var cooldown = parseInt(itemData.abilityCooldown) / 1000;
	
	if (abilityDesc == "") return "";
	return "<div class='esoil_white esoil_small'>" + ability + "</div> " + abilityDesc + " (" + cooldown + " second cooldown)";
}


function GetEsoItemTraitAbilityBlockHtml(itemData)
{
	if (itemData.traitAbilityDesc == null) return "";
	var abilityDesc = itemData.traitAbilityDesc.toUpperCase();
	var cooldown = parseInt(itemData.traitCooldown) / 1000;
	
	if (abilityDesc == "") return "";
	return abilityDesc + " (" + cooldown + " second cooldown)";
}


function UpdateEsoItemValue(text, display)
{
	if (itemRawVersion >= 10)
	{
		$('#esoil_itemnewvalue').text(text);
		
		if (display)
			$('#esoil_itemnewvalueblock').show();
		else
			$('#esoil_itemnewvalueblock').hide();
	}
	else
	{
		$('#esoil_itemoldvalue').text(text);
		
		if (display)
			$('#esoil_itemrightblock').show();
		else
			$('#esoil_itemrightblock').hide();
	}
}


function GetItemLevelBlockDisplay(level, itemType)
{
	level = parseInt(level);
	itemType = parseInt(itemType);
	
	if (level <= 0) return "none";
	
	switch (itemType)
	{
		case 61:	// Furnishing
		case 29:	// Recipe
		case 59:	// Dye Stamp
		case 60:	// Master Writ
		case 44:	// Style Material
		case 31:	// Reagent
		case 10:	// Ingredient
		case 52:	// Rune
		case 53:
		case 41:	// Blacksmith Temper
		case 43:
		case 34:
		case 39:
		case 18:
		case 55:
		case 57:
		case 47: 	// PVP Repair
		case 36:	// BS Material
		case 35:	// BS Raw Material
		case 40:
		case 54:
		case 16:
		case 8:
		case 17:
		case 6:
		case 19:
		case 48:
		case 5:
		case 46:
		case 42:
		case 38:
		case 37:
		case 0:
		case -1:
		case 58:	// Poison Base
		case 33:	// Potion Base
		case 45:
		case 51:
			return "none";
			
		case 2:		// Armor/Weapons
		case 1:
		case 12: 	// Drink
		case 4: 	// Food
		case 26:	// Glyph
		case 3:
		case 20:
		case 21:
		case 30:	// Poison
		case 7:		// Potion
		case 51:
		case 9:		// Repair Kit
			return "inline-block";
	}
	
	return "inline-block";
}


function UpdateEsoItemData(level, quality)
{
	var itemData = FindEsoItemData(level, quality);
	if (itemData == null) return false;
	
	$('#esoil_itemname').text(itemData.name.toUpperCase());
	$('#esoil_itemname').removeClass("esoil_quality_None esoil_quality_Normal esoil_quality_Fine esoil_quality_Superior esoil_quality_Epic esoil_quality_Legendary esoil_quality_Mythic");
	$('#esoil_itemname').addClass("esoil_quality_" + GetEsoItemQualityText(itemData['quality']));
	
	$('#esoil_itemlevelblock').css("display", GetItemLevelBlockDisplay(level, itemData.type));
	
	$('#esoil_itemlevelblock').html(GetEsoItemFullLevelHtml(itemData['level']));
	$('#esoil_levelheader').text(GetEsoItemLevelHeaderText(level, quality));
	
	if (itemRawVersion >= 10) 
	{
		var output = GetEsoItemCPHtml(itemData['level']);
		
		if (output == "")
			$('#esoil_itemrightblock').hide();
		else
			$('#esoil_itemrightblock').html(output).show();
	}
	
	if (itemData['value'] > 0)
	{
		UpdateEsoItemValue(itemData['value'], true);
	}
	else
	{
		UpdateEsoItemValue(itemData['value'], true);
	}
	
	var leftBlockHtml = GetEsoItemLeftBlockHtml(itemData);
	
	if (leftBlockHtml != "")
		$('#esoil_itemleftblock').html(leftBlockHtml).show();
	else
		$('#esoil_itemleftblock').hide();
		
	var enchantBlockHtml = GetEsoItemEnchantBlockHtml(itemData);
	$('#esoil_itemenchantblock').html(enchantBlockHtml);
	
	var traitBlockHtml = GetEsoItemTraitBlockHtml(itemData);
	$('#esoil_itemtraitblock').html(traitBlockHtml);
		
	var abilityBlockHtml = GetEsoItemAbilityBlockHtml(itemData);
	$('#esoil_itemabilityblock').html(abilityBlockHtml);
		
	var traitAbilityBlockHtml = GetEsoItemTraitAbilityBlockHtml(itemData);
	$('#esoil_itemtraitabilityblock').html(traitAbilityBlockHtml);
		
	var setBlockHtml = GetEsoItemSetBlockHtml(itemData);
	$('#esoil_itemsetblock').html(setBlockHtml);
	
	$('#esoil_itemicon').attr('src', MakeEsoItemIconImageLink(itemData));
	
	UpdateEsoItemRawData(itemData);
	UpdateEsoItemImageLink(itemData);
	UpdateEsoItemSimilarData(itemData);
	return true;
}


function UpdateEsoItemImageLink(itemData)
{
	var itemLinkURL = "";
	
	if (itemData.enchantId1 > 0)
		itemLinkURL = "itemLinkImage.php?itemid=" + itemData.itemId + "&level=" + itemData.level + "&quality=" + itemData.quality + "&enchantid=" + itemData.enchantId1 + "&enchantintlevel=" + itemData.enchantIntLevel1 + "&enchantinttype=" + itemData.enchantIntType1;
	else if (itemData.version != "")
		itemLinkURL = "itemLinkImage.php?itemid=" + itemData.itemId + "&level=" + itemData.level + "&quality=" + itemData.quality + "&version=" + itemData.version;
	else
		itemLinkURL = "//esoitem.uesp.net/item-" + itemData.itemId + "-" + itemData.level + "-" + itemData.quality + ".png";
	
	$('#esoil_itemimagelink').attr('href', itemLinkURL);
}


function MakeEsoItemIconImageLink(itemData)
{
	var icon = itemData.icon;
	if (icon == null || icon == "") icon = "unknown.png";
	
	icon = icon.replace(/dds$/, 'png');
	icon = icon.replace('/^\//', '');
	
	return "//esoicons.uesp.net/" + icon;
}


function UpdateEsoItemRawData(itemData)
{
	for (key in itemData)
	{
		var value = itemData[key];
		var element = $('#esoil_rawdata_' + key);
		if (element.size() == 0) continue;
		//if (value == -1 || value == 0) continue;
		
		if (key == 'icon')
			element.html("<img id='esoil_rawdata_iconimage' src='" +  MakeEsoItemIconImageLink(itemData) + "' /> " + value);
		else
			element.text(value);
	}
}


function UpdateEsoItemSimilarData(itemData)
{
	var firstItem = allItemData[0];
	var output = "";
	
	for (var i = 0; i < allItemData.length; i++)
	{
		var item = allItemData[i];
		if (itemData['id'] == item['id']) continue; 
		
		if (itemData['level'] == item['level'] && itemData['quality'] == item['quality']) 
		{
			if (firstItem.enchantId1 > 0)
				output += "<li><a href='itemLink.php?itemid=" + itemData['itemId'] + "&intlevel=" + item['internalLevel'] + "&inttype=" + item['internalSubtype'] + "&enchantid=" + firstItem.enchantId1 + "&enchantintlevel=" + firstItem.enchantIntLevel1 + "&enchantinttype=" + firstItem.enchantIntType1 + "'>Internal Type " + item['internalSubtype'] + ":" + item['internalLevel'] + "</a></li>";
			else
				output += "<li><a href='itemLink.php?itemid=" + itemData['itemId'] + "&intlevel=" + item['internalLevel'] + "&inttype=" + item['internalSubtype'] + "'>Internal Type " + item['internalSubtype'] + ":" + item['internalLevel'] + "</a></li>";
		}
	}
	
	if (output == "") output = "<li>No similar items found.</li>";
	$('#esoil_itemsimilarlist').html(output);
}


function MakeItemLink(itemData, firstItem)
{
	var d1 = firstItem['itemId'];
	var d2 = itemData['internalSubtype'];
	var d3 = itemData['internalLevel'];
	var d4 = firstItem['enchantId1'];
	var d5 = firstItem['enchantIntType1'];
	var d6 = firstItem['enchantIntLevel1'];
	var d7 = 0;
	var d8 = 0;
	var d9 = 0;
	var d10 = 0;
	var d11 = 0;
	var d12 = 0;
	var d13 = 0;
	var d14 = 0;
	var d15 = 0;
	var d16 = firstItem['style']; //Style
	var d17 = firstItem['isCrafted']; //Crafted
	var d18 = firstItem['isBound']; //Bound
	var d19 = 0; //Stolen
	var d20 = firstItem['charges']; //Charges
	var d21 = 0; //PotionData
	var itemName = firstItem['name'];
	
	$link = "|H0:item:" + d1 + ":" + d2 + ":" + d3 + ":" + d4 + ":" + d5 + ":" + d6 + ":" + d7 + ":" + d8 + ":" + d9 + ":" + d10 + ":" + d11 + ":" + d12 + ":" + d13 + ":" + d14 + ":" + d15 + ":" + d16 + ":" + d17 + ":" + d18 + ":" + d19 + ":" + d20 + ":" + d21 + "|h|h";
	
	return $link;
}


function UpdateAllItemData()
{
	var firstItem = allItemData[0];
	if (firstItem == null) return;
	
	firstItem['link'] = MakeItemLink(firstItem, firstItem);
	
	for (var i = 1; i < allItemData.length; i++)
	{
		var itemData = allItemData[i];
		
		if (itemData['level'] == null) itemData['level'] = firstItem['level'];
		if (itemData['quality'] == null) itemData['quality'] = firstItem['quality'];
		if (itemData['internalLevel'] == null) itemData['internalLevel'] = firstItem['internalLevel'];
		if (itemData['internalSubtype'] == null) itemData['internalSubtype'] = firstItem['internalSubtype'];
		if (itemData['version'] == null) itemData['version'] = firstItem['version'];
		
		itemData['link'] = MakeItemLink(itemData, firstItem);
	}
}


$( document ).ready(function() {

	$('#esoil_levelcontrol').on('input', function(e) { 
		$('#esoil_leveltext').val(GetEsoItemFullLevelText(this.value));
		UpdateEsoItemData(this.value, $('#esoil_qualitycontrol').val()); 
	});
	
	$('#esoil_qualitycontrol').on('input', function(e) { 
		$('#esoil_qualitytext').val(GetEsoItemQualityText(this.value));
		UpdateEsoItemData($('#esoil_levelcontrol').val(), this.value); 
	});
	
	UpdateAllItemData();
});
	
	
