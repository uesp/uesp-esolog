ESO_MAX_ATTRIBUTES = 64;
ESO_MAX_LEVEL = 50;
ESO_MAX_CPLEVEL = 16;
ESO_MAX_EFFECTIVELEVEL = 66;


g_EsoBuildClickWallLinkElement = null;
g_EsoBuildItemData = {};

g_EsoBuildItemData.Head = {}
g_EsoBuildItemData.Shoulders = {}
g_EsoBuildItemData.Chest = {}
g_EsoBuildItemData.Hands = {}
g_EsoBuildItemData.Legs = {}
g_EsoBuildItemData.Waist = {}
g_EsoBuildItemData.Feet = {}
g_EsoBuildItemData.Neck = {}
g_EsoBuildItemData.Ring1 = {}
g_EsoBuildItemData.Ring2 = {}
g_EsoBuildItemData.MainHand1 = {}
g_EsoBuildItemData.OffHand1 = {}
g_EsoBuildItemData.MainHand2 = {}
g_EsoBuildItemData.OffHand2 = {}
g_EsoBuildItemData.Poison1 = {}
g_EsoBuildItemData.Poison2 = {}
g_EsoBuildItemData.Food = {}
g_EsoBuildItemData.Potion = {}

g_EsoBuildSetData = {};

g_EsoBuildActiveWeapon = 1;


function GetEsoInputValues()
{
	var inputValues = {};
	
	for (var key in g_EsoInputStats)
	{
		var object = g_EsoInputStats[key];
		
		if (typeof(object) == "object")
		{
			inputValues[key] = {};
			
			for (var key1 in object)
			{
				inputValues[key][key1] = 0;
			}
		}
		else
		{
			inputValues[key] = 0;	
		}		
	}
	
	inputValues.pow = Math.pow;
	inputValues.floor = Math.floor;
	inputValues.round = Math.round;
	inputValues.ceil = Math.ceil;
	
	inputValues.Race = $("#esotbRace").val();
	inputValues.Class = $("#esotbClass").val();
	
	inputValues.Level = parseInt($("#esotbLevel").val());
	if (inputValues.Level > ESO_MAX_LEVEL) inputValues.Level = ESO_MAX_LEVEL;

	inputValues.Attribute.Health = parseInt($("#esotbAttrHea").val());
	inputValues.Attribute.Magicka = parseInt($("#esotbAttrMag").val());
	inputValues.Attribute.Stamina = parseInt($("#esotbAttrSta").val());
	inputValues.Attribute.TotalPoints = inputValues.Attribute.Health + inputValues.Attribute.Magicka + inputValues.Attribute.Stamina;
		
	GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Head);
	GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Shoulders);
	GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Chest);
	GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Hands);
	GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Waist);
	GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Legs);
	GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Feet);
	GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Neck);
	GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Ring1);
	GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Ring2);
	
	if (g_EsoBuildActiveWeapon == 1)
	{
		GetEsoInputItemValues(inputValues, g_EsoBuildItemData.MainHand1);
		GetEsoInputItemValues(inputValues, g_EsoBuildItemData.OffHand1);
		GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Poison1);
	}
	else
	{
		GetEsoInputItemValues(inputValues, g_EsoBuildItemData.MainHand2);
		GetEsoInputItemValues(inputValues, g_EsoBuildItemData.OffHand2);
		GetEsoInputItemValues(inputValues, g_EsoBuildItemData.Poison2);
	}
	
	UpdateEsoItemSetData();
	
	GetEsoInputMundusValues(inputValues);
	GetEsoInputCPValues(inputValues);
	GetEsoInputTargetValues(inputValues);
	
	return inputValues;
}


function GetEsoInputItemValues(inputValues, itemData)
{
	if (itemData == null || itemData.itemId == null || itemData.itemId == "") return false;
	itemData.rawOutput = {};
	
	var traitMatch = itemData.traitDesc.match(/[0-9]+/);
	var traitValue = 0;
	if (traitMatch != null && traitMatch[0] != null) traitValue = parseFloat(traitMatch[0]);
	
	if (itemData.armorType == 1)
	{
		++inputValues.Armor.Light;
		itemData.rawOutput["Armor.Light"] = 1;
	}
	else if (itemData.armorType == 2)
	{
		++inputValues.Armor.Medium;
		itemData.rawOutput["Armor.Medium"] = 1;
	}
	else if (itemData.armorType == 3)
	{
		++inputValues.Armor.Heavy;
		itemData.rawOutput["Armor.Heavy"] = 1;
	}
	
	if (itemData.armorRating > 0)
	{
		var factor = 1;
		var bonusSpellResist = 0;
		
				// Shield expert
		if (itemData.weaponType == 14 && g_EsoCpData['Shield Expert'].isUnlocked)
		{
			factor *= 1.75;
		}
		
		if (itemData.trait == 13)	// Reinforced
		{
			factor *= 1 + traitValue/100;
		}
		else if (itemData.trait == 25) // Armor Nirnhoned
		{
			bonusSpellResist = Math.floor(itemData.armorRating*traitValue/100*factor);
		}
		
		var armorRating = Math.floor(itemData.armorRating * factor);
		
		inputValues.Item.SpellResist += armorRating + bonusSpellResist;
		inputValues.Item.PhysicalResist += armorRating;
		itemData.rawOutput["Item.SpellResist"] = armorRating + bonusSpellResist;
		itemData.rawOutput["Item.PhysicalResist"] = armorRating;
	}
	
	if (itemData.trait == 18) // Divines
	{
		inputValues.Divines += traitValue/100;
		itemData.rawOutput["Divines"] = traitValue/100;
	}
	else if (itemData.trait == 17) //Prosperous
	{
		inputValues.Prosperous += traitValue/100;
		itemData.rawOutput["Prosperous"] = traitValue/100;
	}
	else if (itemData.trait == 12) //Impenetrable
	{
		inputValues.Item.CritResist += traitValue;
		itemData.rawOutput["Item.CritResist"] = traitValue
	}
	else if (itemData.trait == 11) //Sturdy
	{
		inputValues.Sturdy += traitValue/100;
		itemData.rawOutput["Item.Sturdy"] = traitValue/100;
	}
	else if (itemData.trait == 15) //Training
	{
		inputValues.Training += traitValue/100;
		itemData.rawOutput["Training"] = traitValue/100;
	}
	else if (itemData.trait == 21) //Healthy
	{
		inputValues.Item.Health += traitValue;
		itemData.rawOutput["Item.Health"] = traitValue;
	}
	else if (itemData.trait == 22) //Arcane
	{
		inputValues.Item.Magicka += traitValue;
		itemData.rawOutput["Item.Magicka"] = traitValue;
	}
	else if (itemData.trait == 23) //Robust
	{
		inputValues.Item.Stamina += traitValue;
		itemData.rawOutput["Item.Stamina"] = traitValue;
	}	
	else if (itemData.trait == 14) //Well Fitted
	{
		inputValues.Item.SprintCost += traitValue/100;
		inputValues.Item.RollDodgeCost += traitValue/100;
		itemData.rawOutput["Item.SprintCost"] = traitValue/100;
		itemData.rawOutput["Item.RollDodgeCost"] = traitValue/100;
	}

}


function UpdateEsoItemSetData()
{
	g_EsoBuildSetData = {};
	
	for (var key in g_EsoBuildItemData)
	{
		if (g_EsoBuildActiveWeapon == 1 && (key == "MainHand2" || key == "OffHand2" || key == "Poison2")) continue;
		if (g_EsoBuildActiveWeapon == 2 && (key == "MainHand1" || key == "OffHand1" || key == "Poison1")) continue;
		
		var data = g_EsoBuildItemData[key];
		var setName = data.setName;
		
		if (setName == null || setName == "") continue;
		
		if (g_EsoBuildSetData[setName] == null) g_EsoBuildSetData[setName] = 0;
		++g_EsoBuildSetData[setName];
		data.rawOutput["Set." + setName] = 1;
	}
	
}


function GetEsoInputTargetValues(inputValues)
{
	inputValues.Target.Resistance = $("#esotbTargetResistance").val();
	inputValues.Target.PenetrationFlat = $("#esotbTargetPenetrationFlat").val();
	inputValues.Target.PenetrationFactor = $("#esotbTargetPenetrationFactor").val() / 100;
	inputValues.Target.DefenseBonus = $("#esotbTargetDefenseBonus").val() / 100;
	inputValues.Target.AttackBonus = $("#esotbTargetAttackBonus").val() / 100;
}


function GetEsoInputMundusValues(inputValues)
{
	inputValues.Mundus.Name = $("#esotbMundus").val();
	
	if (inputValues.Mundus.Name == "The Lady")
	{
		inputValues.Mundus.PhysicalResist = 1280;
	}
	else if (inputValues.Mundus.Name == "The Lover")
	{
		inputValues.Mundus.SpellResist = 1280;
	}
	else if (inputValues.Mundus.Name == "The Lord")
	{
		inputValues.Mundus.Health = 1280;
	}
	else if (inputValues.Mundus.Name == "The Mage")
	{
		inputValues.Mundus.Magicka = 1280;
	}
	else if (inputValues.Mundus.Name == "The Tower")
	{
		inputValues.Mundus.Stamina = 1280;
	}
	else if (inputValues.Mundus.Name == "The Atronach")
	{
		inputValues.Mundus.MagickaRegen = 210;
	}
	else if (inputValues.Mundus.Name == "The Serpent")
	{
		inputValues.Mundus.StaminaRegen = 210;
	}
	else if (inputValues.Mundus.Name == "The Shadow")
	{
		inputValues.Mundus.CritDamage = 0.12;
	}
	else if (inputValues.Mundus.Name == "The Ritual")
	{
		inputValues.Mundus.HealingDone = 0.10;
	}
	else if (inputValues.Mundus.Name == "The Thief")
	{
		inputValues.Mundus.SpellCrit = 0.11;	//TODO: Absolute values?
		inputValues.Mundus.WeaponCrit = 0.11;
	}
	else if (inputValues.Mundus.Name == "The Warrior")
	{
		inputValues.Mundus.WeaponDamage = 166;
	}
	else if (inputValues.Mundus.Name == "The Apprentice")
	{
		inputValues.Mundus.SpellDamage = 166;
	}
	else if (inputValues.Mundus.Name == "The Steed")
	{
		inputValues.Mundus.HealthRegen = 210;
		inputValues.Mundus.RunSpeed = 0.05;
	}

}


function GetEsoInputCPValues(inputValues)
{
	inputValues.CP.Health = g_EsoCpData.attribute1.points;
	inputValues.CP.Magicka = g_EsoCpData.attribute2.points;
	inputValues.CP.Stamina = g_EsoCpData.attribute3.points;
	inputValues.CP.TotalPoints = parseInt($("#esotbCPTotalPoints").val());
	inputValues.CP.UsedPoints = inputValues.CP.Health + inputValues.CP.Magicka + inputValues.CP.Stamina;
	
	inputValues.CPLevel = Math.floor(inputValues.CP.TotalPoints/10);
	if (inputValues.CPLevel > ESO_MAX_CPLEVEL) inputValues.CPLevel = ESO_MAX_CPLEVEL;
	
	inputValues.EffectiveLevel = inputValues.Level + inputValues.CPLevel;
	if (inputValues.EffectiveLevel > ESO_MAX_EFFECTIVELEVEL) inputValues.EffectiveLevel = ESO_MAX_EFFECTIVELEVEL;

		/* Lord */
	if (inputValues.Armor.Heavy >= 5) ParseEsoCPValue(inputValues, "PhysicalResist", 60624);
	ParseEsoCPValue(inputValues, "DamageShield", 59948);
	ParseEsoCPValue(inputValues, "HADamageResist", 59953);
	ParseEsoCPValue(inputValues, "HealingReceived", 63851);
	
		/* Lady */
	if (inputValues.Armor.Light >= 5) ParseEsoCPValue(inputValues, "PhysicalResist", 60502);
	ParseEsoCPValue(inputValues, "DOTResist", 63850);
	ParseEsoCPValue(inputValues, "PhysicalDamageResist", 63844);
	ParseEsoCPValue(inputValues, "MagickaDamageResist", 63843);
	
		/* Steed */
	if (inputValues.Armor.Medium >= 5) ParseEsoCPValue(inputValues, "PhysicalResist", 59120);
	ParseEsoCPValue(inputValues, "BlockCost", 60649); // TODO: Move?
	ParseEsoCPValue(inputValues, "SpellResist", 62760);
	ParseEsoCPValue(inputValues, "CritResist", 60384);
	
		/* Ritual */
	ParseEsoCPValue(inputValues, "DOTDamage", 63847);
	ParseEsoCPValue(inputValues, "WeaponCritDamage", 59105);
	ParseEsoCPValue(inputValues, "PhysicalPenetration", 61546);
	ParseEsoCPValue(inputValues, "PhysicalDamage", 63868);
	ParseEsoCPValue(inputValues, "WeaponCrit", 59418, "the_ritual", 30);
	
		/* Atronach */
	ParseEsoCPValue(inputValues, "HAWeaponDamage", 60565);
	ParseEsoCPValue(inputValues, "ShieldDamage", 60662);
	ParseEsoCPValue(inputValues, "HABowDamage", 60546);
	ParseEsoCPValue(inputValues, "HAStaffDamage", 60503);
	
		/* Apprentice */
	ParseEsoCPValue(inputValues, "MagickaDamage", 63848);
	ParseEsoCPValue(inputValues, "SpellPenetration", 61555);
	ParseEsoCPValue(inputValues, "SpellCritDamage", 61680);
	ParseEsoCPValue(inputValues, "HealingDone", 59630);
	ParseEsoCPValue(inputValues, "SpellCrit", 59626, "the_apprentice", 30);
	
		/* Shadow */
	ParseEsoCPValue(inputValues, "HealingReduction", 59298);
	ParseEsoCPValue(inputValues, "SneakCost", 61548);
	ParseEsoCPValue(inputValues, "FearDuration", 59353);
	ParseEsoCPValue(inputValues, ["RollDodgeCost", "BreakFreeCost"], 63863);
	
		/* Lover */
	ParseEsoCPValue(inputValues, "StaminaRegen", 59346);
	ParseEsoCPValue(inputValues, "MagickaRegen", 59577);
	ParseEsoCPValue(inputValues, "HealthRegen", 60374);
	ParseEsoCPValue(inputValues, "HARestore", 63854);
	
		/* Tower */
	ParseEsoCPValue(inputValues, "BashCost", 58899);
	ParseEsoCPValue(inputValues, "SprintCost", 64077);
	ParseEsoCPValue(inputValues, "MagickaCost", 63861);
	ParseEsoCPValue(inputValues, "StaminaCost", 63862);
}


function ParseEsoCPValue(inputValues, statIds, abilityId, discId, unlockLevel)
{
	var cpDesc = $("#descskill_" + abilityId);
	if (cpDesc.length == 0) return false;
	
	var text = cpDesc.text();
	var results = text.match(/by ([0-9]+\.?[0-9]*\%?)/);
	if (results.length == 0) return false;
	
	if (discId != null && unlockLevel != null)
	{
		var discPoints = parseInt($("#skills_" + discId).find(".esovcpDiscTitlePoints").text());
		if (discPoints < unlockLevel) return false;
	}
	
	var value = parseFloat(results[1]);
	var lastChar = results[1].slice(-1);
	if (lastChar == "%") value = value/100;

	if (typeof(statIds) == "object")
	{
		for (var i = 0; i < statIds.length; ++i)
		{
			inputValues.CP[statIds[i]] += value;
		}
	}
	else
	{
		inputValues.CP[statIds] += value;
	}
	
	return true;
}


function UpdateEsoComputedStatsList()
{
	var inputValues = GetEsoInputValues();
	var deferredStats = [];
	
	
	for (var statId in g_EsoComputedStats)
	{
		var depends = g_EsoComputedStats[statId].depends;
		
		if (depends != null)
			deferredStats.push(statId);
		else
			UpdateEsoComputedStat(statId, g_EsoComputedStats[statId], inputValues);
	}
	
	for (var i = 0; i < deferredStats.length; ++i)
	{
		var statId = deferredStats[i];
		UpdateEsoComputedStat(statId, g_EsoComputedStats[statId], inputValues);
	}
	
	UpdateEsoReadOnlyStats(inputValues);
}


function UpdateEsoReadOnlyStats(inputValues)
{
	if (inputValues == null) inputValues = GetEsoInputValues();
	
	$("#esotbEffectiveLevel").text(inputValues.EffectiveLevel);
}


function UpdateEsoComputedStat(statId, stat, inputValues)
{
	var stack = [];
	var error = "";
	var computeIndex = 0;
	var round = stat.round;
	
	if (inputValues == null) inputValues = GetEsoInputValues();
	
	var element = $("#esoidStat_" + statId);
	if (element.length == 0) return false;
	
	var valueElement = element.children(".esotbStatValue");
	var computeElements = element.find(".esotbStatComputeValue");
	
	for (var i = 0; i < stat.compute.length; ++i)
	{
		var computeItem = stat.compute[i];
		var nextItem = stat.compute[i+1];
		var itemValue = 0;
		
		if (computeItem == "*")
		{
			if (stack.length >= 2)
				stack.push(stack.pop() * stack.pop());
			else
				error = "ERR";

			if (round == "floor") stack.push(Math.floor(stack.pop()));
			continue;
		}
		else if (computeItem == "+")
		{
			if (stack.length >= 2)
				stack.push(stack.pop() + stack.pop());
			else
				error = "ERR";
			
			continue;
		}
		else if (computeItem == "-")
		{
			if (stack.length >= 2)
				stack.push(-stack.pop() + stack.pop());
			else
				error = "ERR";
			
			continue;
		}
		
		with(inputValues)
		{
			try {
				itemValue = eval(computeItem); 
			} catch (e) {
			    itemValue = "ERR";
			}
			
			stack.push(itemValue);
		}
		
		var prefix = "";
		if (nextItem == "-") prefix = "-";
		if (nextItem == "+") prefix = "+";
		if (nextItem == "*") prefix = "x";
		
		if (!(itemValue % 1 === 0))
		{
			itemValue = Number(itemValue).toFixed(3);
		}
		
		$(computeElements[computeIndex]).text(prefix + itemValue);
		++computeIndex;
	}
	
	if (stack.length <= 0) error = "ERR";
	
	if (error == "")
	{
		var result = stack.pop();
		var display = stat.display;
		
		inputValues[statId] = result;
		
		if (display == "percent")
		{
			result = Math.round(result*1000)/10 + "%";
		}
		
		valueElement.text(result);
	}
	else
	{
		inputValues[statId] = error;
		valueElement.text(error);
	}
}


function CreateEsoComputedStats()
{
	for (var statId in g_EsoComputedStats)
	{
		CreateEsoComputedStat(statId, g_EsoComputedStats[statId]);
	}
	
}


function CreateEsoComputedStat(statId, stat)
{
	var element;
	
	element = $("<div/>").attr("id", "esoidStat_" + statId).
		attr("statid", statId).
		addClass("esotbStatRow").
		appendTo("#esotbStatList");

	$("<div/>").addClass("esotbStatName").
		text(stat.title).
		appendTo(element);
		
	$("<div/>").addClass("esotbStatValue").
		text("?").
		appendTo(element);
	
	$("<div/>").addClass("esotbStatComputeButton").
		html("+").
		appendTo(element);
	
	var computeElement = $("<div/>").addClass("esotbComputeItems").
										attr("style", "display: none;").
										appendTo(element);
		
	CreateEsoComputedStatItems(stat.compute, computeElement);
	
	return element;
}


function CreateEsoComputedStatItems(computeData, parentElement)
{
	
	for (var i = 0; i < computeData.length; ++i)
	{
		var computeItem = computeData[i];
		var nextItem = computeData[i+1];
		
		if (computeItem == "*")
		{
		}
		else if (computeItem == "+")
		{
		}
		else if (computeItem == "-")
		{
		}
		else
		{
			var prefix = "";
			if (nextItem == "+") prefix = "+";
			if (nextItem == "-") prefix = "-";
			if (nextItem == "*") prefix = "x";
			
			$("<div/>").addClass("esotbStatComputeValue").
				text(prefix + "0").
				attr("computeindex", i).
				appendTo(parentElement);
			
			$("<div/>").addClass("esotbStatComputeItem").
				text(computeItem).
				attr("computeindex", i).
				appendTo(parentElement);
		}
	}
	
}


function OnEsoInputChange(e)
{
	var id = $(this).attr("id");
	
	if (id == "esotbLevel") 
	{
		OnEsoLevelChange.call(this, e);
	}
	else if ($(this).hasClass("esotbAttributeInput"))
	{
		OnEsoAttributeChange.call(this, e);
	}
	
	UpdateEsoComputedStatsList();
}


function OnEsoAttributeChange(e)
{
	var $this = $(this);
	var value = $this.val();
	
	if (value > ESO_MAX_ATTRIBUTES) $this.val(ESO_MAX_ATTRIBUTES);
	if (value < 0)  $this.val("0");
	
	var totalValue = parseInt($("#esotbAttrHea").val()) + parseInt($("#esotbAttrMag").val()) + parseInt($("#esotbAttrSta").val());
	
	if (totalValue > ESO_MAX_ATTRIBUTES) 
	{
		totalValue = ESO_MAX_ATTRIBUTES;
		$this.val(ESO_MAX_ATTRIBUTES - totalValue + parseInt(value));
	}
	
	$("#esotbAttrTotal").text(totalValue + " / " + ESO_MAX_ATTRIBUTES);
}


function OnEsoLevelChange(e)
{
	var $this = $(this);
	var value = $this.val();
	
	if (value > 50) $this.val("50");
	if (value < 1)  $this.val("1");
}


function OnEsoCPTotalPointsChange(e)
{
	var $this = $(this);
	var value = $this.val();
	
	if (value < 0) $this.val("0");
}


function OnEsoToggleStatComputeItems(e)
{
	var computeItems = $(this).next(".esotbComputeItems");
	
	if (computeItems.is(":visible"))
		$(this).text("+");
	else
		$(this).text("-");
		
	computeItems.slideToggle();
}


function OnEsoRaceChange(e)
{
	UpdateEsoComputedStatsList();
}


function OnEsoClassChange(e)
{
	UpdateEsoComputedStatsList();
}


function OnEsoMundusChange(e)
{
	UpdateEsoComputedStatsList();
}


function OnEsoClickItem(e)
{
	var $this = $(this);
	var id = $this.attr("id");
	
	SelectEsoItem($this);
}

function OnEsoClickItemIcon(e)
{
	var $this = $(this).parent();
	var id = $this.attr("id");
	
	SelectEsoItem($this);
}


function UnequipEsoItemSlot(slotId, update)
{
	if (g_EsoBuildItemData[slotId] == null) return false;
	
	var element = $("#esotbItem" + slotId);
	var iconElement = $(element).find(".esotbItemIcon");
	var labelElement = $(element).find(".esotbItemLabel");
	
	iconElement.attr("src", "");
	labelElement.text("");
	iconElement.attr("itemid", "");
	iconElement.attr("level", "");
	iconElement.attr("quality", "");
	
	g_EsoBuildItemData[slotId] = {};
	
	if (update == null || update === true) UpdateEsoComputedStatsList();
	return true;
}


function OnEsoSelectItem(itemData, element)
{
	var iconElement = $(element).find(".esotbItemIcon");
	var labelElement = $(element).find(".esotbItemLabel");
	
	var slotId = $(element).attr("slotId");
	if (slotId == null || slotId == "") return false;
	
	if ($.isEmptyObject(itemData))
	{
		iconElement.attr("src", "");
		labelElement.text("");
		iconElement.attr("itemid", "");
		iconElement.attr("level", "");
		iconElement.attr("quality", "");
		g_EsoBuildItemData[slotId] = {};
		
		UpdateEsoComputedStatsList();
		return;
	}
	
	var iconUrl = "http://esoicons.uesp.net" + itemData.icon.replace(".dds", ".png");
	var niceName = itemData.name.charAt(0).toUpperCase() + itemData.name.slice(1);
	
	iconElement.attr("src", iconUrl);
	labelElement.text(niceName);
	
	iconElement.attr("itemid", itemData.itemId);
	iconElement.attr("level", itemData.level);
	iconElement.attr("quality", itemData.quality);
	
	if (itemData.equipType == 6)
	{
		if (slotId == "MainHand1") UnequipEsoItemSlot("OffHand1", false);
		if (slotId == "MainHand2") UnequipEsoItemSlot("OffHand2", false);
	}
	else if (slotId == "OffHand1")
	{
		if (g_EsoBuildItemData["MainHand1"].equipType == 6) UnequipEsoItemSlot("MainHand1", false);
	}
	else if (slotId == "OffHand2")
	{
		if (g_EsoBuildItemData["MainHand2"].equipType == 6) UnequipEsoItemSlot("MainHand2", false);
	}
	
	g_EsoBuildItemData[slotId] = itemData;
	RequestEsoItemData(itemData, element);
}


function RequestEsoItemData(itemData, element)
{	
	if (itemData.itemId == null || itemData.itemId == "") return false;
	if (itemData.level == null || itemData.level == "") return false;
	if (itemData.quality == null || itemData.quality == "") return false;
	
	var queryParams = {
			"table" : "minedItem",
			"id" : itemData.itemId,
			"level" : itemData.level,
			"quality" : itemData.quality,
			"limit" : 1,
	};
	
	$.ajax("http://esolog.uesp.net/exportJson.php", {
			data: queryParams,
		}).
		done(function(data, status, xhr) { OnEsoItemDataReceive(data, status, xhr, element, itemData); }).
		fail(function(xhr, status, errorMsg) { OnEsoItemDataError(xhr, status, errorMsg); });
}


function OnEsoItemDataReceive(data, status, xhr, element, origItemData)
{
	var slotId = $(element).attr("slotId");
	if (slotId == null || slotId == "") return false;
	
	if (data.minedItem != null && data.minedItem[0] != null)
	{
		g_EsoBuildItemData[slotId] = data.minedItem[0];
		UpdateEsoComputedStatsList();
	}
	
}


function OnEsoItemDataError(xhr, status, errorMsg)
{
}


function SelectEsoItem(element)
{
	var equipType = element.attr("equiptype");
	var itemType = element.attr("itemtype");
	var weaponType = element.attr("weapontype");
	
	var data = {
		onSelectItem: OnEsoSelectItem,
		itemType: itemType,
		equipType: equipType,
		weaponType: weaponType,
	};
	
	var rootSearchPopup = UESP.showEsoItemSearchPopup(element, data);
	ShowEsoBuildClickWall(rootSearchPopup);
}


function ShowEsoBuildClickWall(parentElement)
{
	$("#esotbClickWall").show();
	g_EsoBuildClickWallLinkElement = parentElement;
}


function HideEsoBuildClickWall()
{
	$("#esotbClickWall").hide();
	
	if (g_EsoBuildClickWallLinkElement != null)
	{
		g_EsoBuildClickWallLinkElement.hide();
		g_EsoBuildClickWallLinkElement = null;
	}
}


function OnEsoClickBuildWall(e)
{
	HideEsoBuildClickWall();
}


function OnEsoClickComputeItems(e)
{
	var parent = $(this).parent(".esotbStatRow");
	var statId = parent.attr("statid");
	
	if (statId == null || statId == "") return false;
	
	return ShowEsoFormulaPopup(statId);
}


function ConvertEsoFormulaToPrefix(computeItems)
{
	var equation = "";
	var stack = [];
	var lastOperator = "";
	
	for (var key in computeItems)
	{
		var computeItem = computeItems[key];
		var operator = "";
		
		if (computeItem == "*" || computeItem == "+" || computeItem == "-")
		{
			operator = computeItem;
		}
		else
		{
			stack.push(computeItem);
			continue;
		}
		
		if (stack.length < 2)
		{
			equation += " ERR ";
			continue;
		}
		
		var op1 = stack.pop();
		var op2 = stack.pop();
		
		if (operator == "*")
		{
			if (lastOperator == "*")
				stack.push("" + op2 + "" + operator + "(" + op1 + ")");
			else
				stack.push("(" + op2 + ")" + operator + "(" + op1 + ")");
		}
		else
			stack.push("" + op2 + " " + operator + " " + op1 + "");
		
		lastOperator = operator;
	}
	
	if (stack.length != 1) return "ERR";
	return stack.pop();
}


function ShowEsoFormulaPopup(statId)
{
	var formulaPopup = $("#esotbFormulaPopup");
	var stat = g_EsoComputedStats[statId];
	
	if (stat == null) return false;

	var equation = ConvertEsoFormulaToPrefix(stat.compute);
	
	$("#esotbFormulaTitle").text("Complete Formula for " + stat.title);
	$("#esotbFormulaName").text(statId + " = ");
	$("#esotbFormula").text(equation);
	
	formulaPopup.show();
	ShowEsoBuildClickWall(formulaPopup);
	
	return true;
}


function CloseEsoFormulaPopup()
{
	$("#esotbFormulaPopup").hide();
	HideEsoBuildClickWall();
}


function OnEsoClickBuildStatTab(e)
{
	var tabId = $(this).attr("tabid");
	if (tabId == null || tabId == "") return;
	
	$(".esotbStatTabSelected").removeClass("esotbStatTabSelected");
	$(this).addClass("esotbStatTabSelected");
	
	$(".esotbStatBlock:visible").hide();
	$("#" + tabId).show();
}


function OnEsoBuildCpUpdate(e)
{
	UpdateEsoComputedStatsList();
}


function OnEsoItemSearchPopupClose(e)
{
	HideEsoBuildClickWall();
}


function OnEsoItemDetailsClick(e)
{
	var slotId = $(this).parent().attr("slotId");
	if (slotId == null || slotId == "") return;
	ShowEsoItemDetailsPopup(slotId);
}


function ShowEsoItemDetailsPopup(slotId)
{
	var detailsPopup = $("#esotbItemDetailsPopup");
	
	var itemData = g_EsoBuildItemData[slotId];
	if (itemData == null) return false;
	if (itemData.rawOutput == null) return false;
	
	var detailsHtml = "";
	
	for (var key in itemData.rawOutput)
	{
		var value = itemData.rawOutput[key];
		detailsHtml += key + " = " + value + "<br/>";
	}

	$("#esotbItemDetailsTitle").text("Item Details for " + slotId);
	$("#esotbItemDetailsText").html(detailsHtml);
	
	detailsPopup.show();
	ShowEsoBuildClickWall(detailsPopup);
	
	return true;
}


function CloseEsoItemDetailsPopup()
{
	$("#esotbItemDetailsPopup").hide();
	HideEsoBuildClickWall();
}


function esotbOnDocReady()
{
	CreateEsoComputedStats();
	UpdateEsoComputedStatsList();
		
	$("#esotbRace").change(OnEsoRaceChange)
	$("#esotbClass").change(OnEsoClassChange)
	$("#esotbMundus").change(OnEsoMundusChange)
	$("#esotbCPTotalPoints").change(OnEsoCPTotalPointsChange);
	$(".esotbStatComputeButton").click(OnEsoToggleStatComputeItems);
	
	$(".esotbInputValue").on('input', function(e) { OnEsoInputChange.call(this, e); });
	
	//$(".esotbItem").click(OnEsoClickItem)
	$(".esotbItemIcon").click(OnEsoClickItemIcon)
	
	$(".esotbComputeItems").click(OnEsoClickComputeItems);

	$("#esotbItemDetailsCloseButton").click(CloseEsoItemDetailsPopup);
	$("#esotbFormulaCloseButton").click(CloseEsoFormulaPopup);
	$("#esotbClickWall").click(OnEsoClickBuildWall);
	
	$(".esotbStatTab").click(OnEsoClickBuildStatTab);
	
	$(document).on("EsoItemSearchPopupOnClose", OnEsoItemSearchPopupClose);
	
	$(document).on("esocpUpdate", OnEsoBuildCpUpdate);
	
	$(".esotbItemDetailsButton").click(OnEsoItemDetailsClick);
}


$( document ).ready(esotbOnDocReady);