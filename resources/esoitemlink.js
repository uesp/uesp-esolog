var ESO_ITEM_QUALITIES = {
		'-1': "",
		0: "Trash",
		1: "Normal",
		2: "Fine",
		3: "Superior",
		4: "Epic",
		5: "Legendary",
};


var ESO_ITEM_TRAITS = {
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
		0: "None",
		2: "Charged",
		5: "Defending",
		4: "Infused",
		9: "Intricate",
		10: "Ornate",
		1: "Power",
		3: "Precise",
		7: "Sharpened",
		6: "Training",
		8: "Weighted",
		25: "Nirnhoned",
		26: "Nirnhoned",
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
	if (level > 50) return 'V' + (level - 50);
	return '' + level;
}


function GetEsoItemFullLevelText(level)
{
	if (level > 50) return 'Rank V' + (level - 50);
	return 'Level ' + level;
}


function GetEsoItemFullLevelHtml(level)
{
	if (level > 50) return "<img src='resources/eso_item_veteranicon.png' /> RANK <div id='esoil_itemlevel'>" + (level - 50) + "</div>"
	return "LEVEL <div id='esoil_itemlevel'>" + level + "</div>";
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
	
	console.log("FindEsoItemData(): Didn't find item #" + firstItem.itemId + "(level " + level + ", quality " + quality + ") in data!");
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
	
	if (type == 1) return "DAMAGE <div id='esoil_itemleft'>" + itemData.weaponPower + "</div>";
	if (type == 2) return "ARMOR <div id='esoil_itemleft'>" + itemData.armorRating + "</div>";
	
	return "";
}


function GetEsoItemEnchantBlockHtml(itemData)
{
	var output = "";
	
	
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
	if (itemData.setName == null) return "";
	var setName = itemData.setName.toUpperCase();
	if (setName == "") return "";
	
	var setMaxEquipCount = itemData.setMaxEquipCount;
	var setBonusCount = parseInt(itemData.setBonusCount);
	var output = "<div class='esoil_white esoil_small'>PART OF THE " + setName + " SET (" + setMaxEquipCount + "/" + setMaxEquipCount + " ITEMS)</div>";
	
	for (var i = 1; i <= setBonusCount && i <= 5; i += 1)
	{
		var setCount = itemData['setBonusCount'  + i];
		var setDesc = itemData['setBonusDesc'  + i]
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


function UpdateEsoItemData(level, quality)
{
	var itemData = FindEsoItemData(level, quality);
	if (itemData == null) return false;
	
	$('#esoil_itemname').text(itemData.itemName);
	$('#esoil_itemname').removeClass("esoil_quality_None esoil_quality_Normal esoil_quality_Fine esoil_quality_Superior esoil_quality_Epic esoil_quality_Legendary");
	$('#esoil_itemname').addClass("esoil_quality_" + GetEsoItemQualityText(itemData['quality']));
	
	$('#esoil_itemlevelblock').html(GetEsoItemFullLevelHtml(itemData['level']));
	$('#esoil_levelheader').text(GetEsoItemLevelHeaderText(level, quality));
	
	if (itemData['value'] > 0)
	{
		$('#esoil_itemvalue').text(itemData['value']);
		$('#esoil_itemvalueblock').show();
	}
	else
	{
		$('#esoil_itemvalue').text(itemData['value']);
		$('#esoil_itemvalueblock').hide();
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
	if (itemData.enchantId1 > 0)
		$('#esoil_itemimagelink').attr('href', "itemLinkImage.php?itemid=" + itemData.itemId + "&level=" + itemData.level + "&quality=" + itemData.quality + "&enchantid=" + itemData.enchantId1 + "&enchantintlevel=" + itemData.enchantIntLevel1 + "&enchantinttype=" + itemData.enchantIntType1);
	else
		$('#esoil_itemimagelink').attr('href', "itemLinkImage.php?itemid=" + itemData.itemId + "&level=" + itemData.level + "&quality=" + itemData.quality + "");
}


function MakeEsoItemIconImageLink(itemData)
{
	var icon = itemData.icon;
	if (icon == null || icon == "") icon = "unknown.png";
	
	icon = icon.replace(/dds$/, 'png');
	icon = icon.replace('/^\//', '');
	
	return "http://content3.uesp.net/eso/gameicons/" + icon;
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
	var d7 = firstItem['enchantId2'];
	var d8 = firstItem['enchantIntType2'];
	var d9 = firstItem['enchantIntLevel2'];
	var d10 = 0;
	var d11 = 0;
	var d12 = 0;
	var d13 = 0;
	var d14 = 0;
	var d15 = 0;
	var d16 = firstItem['style']; //Style
	var d17 = firstItem['isCrafted']; //Crafted
	var d18 = firstItem['isBound']; //Bound
	var d19 = firstItem['charges']; //Charges
	var d20 = 0; //PotionData
	var itemName = firstItem['name'];
	
	$link = "|H0:item:" + d1 + ":" + d2 + ":" + d3 + ":" + d4 + ":" + d5 + ":" + d6 + ":" + d7 + ":" + d8 + ":" + d9 + ":" + d10 + ":" + d11 + ":" + d12 + ":" + d13 + ":" + d14 + ":" + d15 + ":" + d16 + ":" + d17 + ":" + d18 + ":" + d19 + ":" + d20 + "|h[" + itemName + "]|h";
	
	return $link;
}


function UpdateAllItemData()
{
	var firstItem = allItemData[0];
	firstItem['link'] = MakeItemLink(firstItem, firstItem);
	
	for (var i = 1; i < allItemData.length; i++)
	{
		var itemData = allItemData[i];
		
		if (itemData['level'] == null) itemData['level'] = firstItem['level'];
		if (itemData['quality'] == null) itemData['quality'] = firstItem['quality'];
		if (itemData['internalLevel'] == null) itemData['internalLevel'] = firstItem['internalLevel'];
		if (itemData['internalSubtype'] == null) itemData['internalSubtype'] = firstItem['internalSubtype'];
		
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
	console.log("ready");
});
	
	