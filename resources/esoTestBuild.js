ESO_MAX_ATTRIBUTES = 64;
ESO_MAX_LEVEL = 50;
ESO_MAX_CPLEVEL = 16;
ESO_MAX_EFFECTIVELEVEL = 66;

g_EsoBuildClickWallLinkElement = null;


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
	
	GetEsoInputMundusValues(inputValues);
	GetEsoInputCPValues(inputValues);
	GetEsoInputTargetValues(inputValues);
	
	return inputValues;
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
	inputValues.CP.Health = parseInt($("#esovcpDiscHea").text());
	inputValues.CP.Magicka = parseInt($("#esovcpDiscMag").text());
	inputValues.CP.Stamina = parseInt($("#esovcpDiscSta").text());
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
	//ParseEsoCPValue(inputValues, "CritResist", 64079, "the_steed");	//Spell Armor
	
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


function OnEsoSelectItem(itemData, element)
{
	var iconElement = $(element).find(".esotbItemIcon");
	var labelElement = $(element).find(".esotbItemLabel");
	
	var iconUrl = "http://esoicons.uesp.net" + itemData.icon.replace(".dds", ".png");
	var niceName = itemData.name.charAt(0).toUpperCase() + itemData.name.slice(1);
	
	iconElement.attr("src", iconUrl);
	labelElement.text(niceName);
	
	$(element).attr("itemid", itemData.itemId);
	$(element).attr("level", itemData.level);
	$(element).attr("quality", itemData.quality);
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
	
	var root = UESP.showEsoItemSearchPopup(element, data);
	ShowEsoBuildClickWall(root);
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
	
	$(".esotbItem").click(OnEsoClickItem)
	$(".esotbComputeItems").click(OnEsoClickComputeItems);

	$("#esotbFormulaCloseButton").click(CloseEsoFormulaPopup);
	$("#esotbClickWall").click(OnEsoClickBuildWall);
	
	$(".esotbStatTab").click(OnEsoClickBuildStatTab);
	
	$(document).on("EsoItemSearchPopupOnClose", OnEsoItemSearchPopupClose);
	
	$(document).on("esocpUpdate", OnEsoBuildCpUpdate);
	
}


$( document ).ready(esotbOnDocReady);