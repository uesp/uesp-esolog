var g_LastSkillId = 0;
var g_LastSkillInputValues = {};
var MAX_SKILL_COEF = 6;

var g_EsoSkillSearchText = "";
var g_EsoSkillSearchLastIndex = -1;


var RAWDATA_KEYS = 
[
 		"abilityId",
		"name",
		"type",
		"skillTypeName",
		"cost",
		"rank",
		"maxRank",
		"learnedLevel",
		"target",
		"duration",
		"minRange",
		"maxRange",
		"radius",
		"castTime",
		"channelTime",
		"angleDistance",
		"isPassive",
		"skillType",
		"isPlayer",
		
		"numCoefVars",
		"coefDescription",
		"type1",
		"a1",
		"b1",
		"c1",
		"R1",
		"avg1",
		"type2",
		"a2",
		"b2",
		"c2",
		"R2",
		"avg2",
		"type3",
		"a3",
		"b3",
		"c3",
		"R3",
		"avg3",
		"type4",
		"a4",
		"b4",
		"c4",
		"R4",
		"avg4",
		"type5",
		"a5",
		"b5",
		"c5",
		"R5",
		"avg5",
		"type6",
		"a6",
		"b6",
		"c6",
		"R6",
		"avg6",

		"description",
		"baseName",
		"effectLines",
		"icon",
		"raceType",
		"classType",
		"skillLine",
		"prevSkill",
		"nextSkill",
		"nextSkill2",
		"skillIndex",
		"isChanneled",
		"mechanic",
		"upgradeLines",
];

var RAWDATA_IGNORE_KEYS =
{
		"id" : true,
		"texture" : true,
};

var ROMAN_NUMERALS = 
{
		1 : 'I',
		2 : 'II',
		3 : 'III',
		4 : 'IV',
		5 : 'V',
		6 : 'VI',
		7 : 'VII',
		8 : 'VIII',
		9 : 'IX',
		10 : 'X',
		11 : 'XI',
		12 : 'XII',
};


function EsoConvertDescToHTML(desc)
{
	return EsoConvertDescToHTMLClass(desc, "esovsWhite");
}


function EsoConvertDescToHTMLClass(desc, className)
{
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}([a-zA-Z _0-9\.\+\-\:\;\n\r\t\$\*\(\)\#\?]*)\|r/g, '<div class="' + className + '">$1</div>');
	newDesc = newDesc.replace(/\n/g, '<br />');
	return newDesc;
}


function EsoConvertDescToText(desc)
{
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}([a-zA-Z _0-9\.\+\-\:\;\n\r\t\$\*\(\)\#\?]*)\|r/g, '$1');
	//newDesc = newDesc.replace(/\n/g, '<br />');
	return newDesc;
}


function GetRomanNumeral(value)
{
	if (value <= 0) return '';
	
	var roman = ROMAN_NUMERALS[value]
	if (roman != null) return roman;
	
	return value.tostring();
}


function GetEsoSkillTooltipHtml(skillData)
{
	if (skillData == null) return "";

	var output = "<div class='esovsSkillTooltip'>\n";
	
	var mechanic = skillData['mechanic'];
	var abilityId = skillData['abilityId'];
	var safeName = skillData['name'];
	var rank = skillData['rank'];
	var maxRank = skillData['maxRank'];
	var desc = GetEsoSkillDescription(abilityId, null, true);
	var channelTime = skillData['channelTime'] / 1000;
	var castTime = skillData['castTime'] / 1000;
	var radius = skillData['radius'] / 100;
	var duration = skillData['duration'] / 1000;
	var target = skillData['target'];
	var angleDistance = skillData['angleDistance'];
	var minRange = skillData['minRange'];
	var maxRange = skillData['maxRange'];
	var cost = skillData['cost'];
	var radius = skillData['radius'] / 100;
	var castTimeStr = castTime + " seconds";
	var skillType = skillData['type'];
	var learnedLevel = skillData['learnedLevel'];
	var effectLines = skillData['effectLines'];
	var skillLine = skillData['skillLine'];
	var area = "";
	var range = "";
	var rankStr = "";
	var realRank = rank;
	
	if (skillType != 'Passive')
	{
		if (realRank > 4) realRank -= 4;
		if (realRank > 4) realRank -= 4;
	}
	
	if (maxRank > 1 && realRank > 0) rankStr = " " + GetRomanNumeral(realRank);
	
	if (minRange > 0 && maxRange > 0)
		range = (minRange/100) + " - " + (maxRange/100) + " meters"
	else if (minRange <= 0 && maxRange > 0)
		range = (maxRange/100) + " meters"
	else if (minRange > 0 && maxRange <= 0)
		range = "Under " + (minRange/100) + " meters"
	
	if (angleDistance > 0) area = (radius/100) + " x " + (angleDistance/50) + " meters"
		
	output += "<div class='esovsSkillTooltipTitle'>" + safeName + rankStr + "</div>\n";
	output += "<img src='http://esolog.uesp.net/resources/skill_divider.png' class='esovsSkillTooltipDivider' />";
	
	if (skillType != 'Passive')
	{
		var realCost = ComputeEsoSkillCost(cost, null);
		var costStr = "" + realCost + " ";
		var costClass = "";
		
		if (mechanic == 0)
		{
			costStr += "Magicka";
			costClass = "esovsMagicka";
		}
		else if (mechanic == 6)
		{
			costStr += "Stamina";
			costClass = "esovsStamina";
		}
		else
		{
			costStr = cost;
		}
		
		if (channelTime > 0) 
		{
			output += "<div class='esovsSkillTooltipValue'>" + channelTime + " seconds</div>";
			output += "<div class='esovsSkillTooltipName'>Channel Time</div>";
			castTimeStr = "";
		}
		else if (castTime <= 0)
		{
			castTimeStr = "Instant";
		}
				
		if (castTimeStr != '')
		{
			output += "<div class='esovsSkillTooltipValue'>" + castTimeStr + "</div>";
			output += "<div class='esovsSkillTooltipName'>Cast Time</div>";			
		}
		
		if (target != '')
		{
			output += "<div class='esovsSkillTooltipValue'>" + target +"</div>";
			output += "<div class='esovsSkillTooltipName'>Target</div>";			
		}
		
		if (area != '')
		{
			output += "<div class='esovsSkillTooltipValue'>" + area + "</div>";
			output += "<div class='esovsSkillTooltipName'>Area</div>";			
		}
		
		if (radius > 0)
		{
			output += "<div class='esovsSkillTooltipValue'>" + radius + " meters</div>";
			output += "<div class='esovsSkillTooltipName'>Radius</div>";			
		}
		
		if (range != "")
		{
			output += "<div class='esovsSkillTooltipValue'>" + range + "</div>";
			output += "<div class='esovsSkillTooltipName'>Range</div>";			
		}
		
		if (duration > 0)
		{
			output += "<div class='esovsSkillTooltipValue'>" + duration + " seconds</div>";
			output += "<div class='esovsSkillTooltipName'>Duration</div>";			
		}
		
		if (cost != '')
		{
			output += "<div class='esovsSkillTooltipValue " + costClass + "' id='esovsSkillTooltipCost'>" + costStr + "</div>";
			output += "<div class='esovsSkillTooltipName'>Cost</div>";			
		}
		
		output += "<img src='http://esolog.uesp.net/resources/skill_divider.png' class='esovsSkillTooltipDivider' />";
	}

	output += "<div id='esovsSkillTooltipDesc' class='esovsSkillTooltipDesc'>" + desc + "</div>\n";
	if (effectLines != "") output += " <div class='esovsSkillTooltipEffectLines'><b>NEW EFFECT</b><br/>" + effectLines + "</div>";
	
	if (learnedLevel > 0)
	{
		if (skillLine != "")
			output += "<div class='esovsSkillTooltipLevel'>Unlocked at " + skillLine + " Rank " + learnedLevel + "</div>\n";
		else
			output += "<div class='esovsSkillTooltipLevel'>Unlocked at Rank " + learnedLevel + "</div>\n";
	}
	
	output += "</div>";
	
	return output;
}


function EsoShowPopupSkillTooltip(skillData, parent)
{
	var popupElement = $("#esovsPopupSkillTooltip");
	
	if (popupElement.length == 0)
	{
		$("body").append('<div id="esovsPopupSkillTooltip"></div>');
		popupElement = $("#esovsPopupSkillTooltip");
	}
		
	if (skillData == null)
	{
		popupElement.hide();
		return;
	}
	
	var output = GetEsoSkillTooltipHtml(skillData);
	
	popupElement.html(output);
	popupElement.show();
	
	AdjustEsoSkillPopupTooltipPosition(popupElement, $(parent));
}


function AdjustEsoSkillPopupTooltipPosition(tooltip, parent)
{
	if (tooltip == null) return;
	if (tooltip[0] == null) return;
	if (parent == null) return;
	
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var toolTipWidth = tooltip.width();
    var toolTipHeight = tooltip.height();
    var elementHeight = parent.height();
    var elementWidth = parent.width();
     
    var top = parent.offset().top - toolTipHeight/2 + elementHeight/2;
    var left = parent.offset().left + parent.outerWidth() + 3;
     
    tooltip.offset({ top: top, left: left });
     
    var viewportTooltip = tooltip[0].getBoundingClientRect();
     
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
    	left = left - toolTipWidth - parent.width() - 28;
    }
     
    tooltip.offset({ top: top, left: left });
    viewportTooltip = tooltip[0].getBoundingClientRect();
     
    if (viewportTooltip.left < 0 )
    {
    	var el = $('<i/>').css('display','inline').insertBefore(parent[0]);
        var realOffset = el.offset();
        el.remove();
         
        left = realOffset.left - toolTipWidth - 3;
        tooltip.offset({ top: top, left: left });
    }
     
}


function EsoViewSkillShowTooltip(skillData)
{
	var element = $('#esovsSkillTooltipRoot');
		
	if (skillData == null) 
	{
		element.html("<div class='esovsSkillTooltip'>Click on a skill to the right to view tooltip.</div>");
		g_LastSkillId = -1;
		return;
	}
			
	g_LastSkillId = skillData.abilityId;
	element.html(GetEsoSkillTooltipHtml(skillData));
}


function OnEsoSkillBlockClick(event)
{
	var skillId = $(this).attr('skillid');
	if (skillId == null || skillId == "") return;
	
	EsoViewSkillShowTooltip(g_SkillsData[skillId]);
	UpdateEsoSkillTooltipDescription();
	UpdateEsoSkillTooltipCost();
	UpdateEsoSkillRawData();
	UpdateEsoSkillCoefData();
	UpdateSkillLink();
}


function EsoSkillShowSkillLine(skillLine)
{
	var id = skillLine.replace(/[ '"]/g, '_');
	
	$(".esovsSkillContentBlock:visible").hide();
	$("#" + id).show();
}


function OnEsoSkillTypeTitleClick(event, noUpdate)
{
	var currentSkillType = $(".esovsSkillTypeTitle.esovsSkillTypeTitleHighlight");
	var currentSkillLine = $(".esovsSkillLineTitle.esovsSkillLineTitleHighlight");
	
	if ($(this)[0] == currentSkillType[0]) return;
	
	$(".esovsSkillType:visible").slideUp();
	currentSkillType.removeClass("esovsSkillTypeTitleHighlight");
	currentSkillLine.removeClass("esovsSkillLineTitleHighlight");
	
	$(this).next(".esovsSkillType").slideDown();
	$(this).addClass("esovsSkillTypeTitleHighlight");
	
	var firstSkillLine = $(this).next(".esovsSkillType").children(".esovsSkillLineTitle").first();
	firstSkillLine.addClass("esovsSkillLineTitleHighlight");
	
	var skillType = $(this).text();
	var skillLine = firstSkillLine.text();
	
	if (noUpdate !== false)
	{
		EsoSkillShowSkillLine(skillLine);
		UpdateEsoAllSkillDescription();
		UpdateEsoAllSkillCost();
		UpdateEsoSkillRawData();
		UpdateEsoSkillCoefData();
		UpdateSkillLink();
	}
}


function OnEsoSkillLineTitleClick(event, noUpdate)
{
	var currentSkillLine = $(".esovsSkillLineTitle.esovsSkillLineTitleHighlight");
	
	if ($(this)[0] == currentSkillLine[0]) return;
	currentSkillLine.removeClass("esovsSkillLineTitleHighlight");
	
	$(this).addClass("esovsSkillLineTitleHighlight");
	
	var skillLine = $(this).text();
	
	if (noUpdate !== false)
	{
		EsoSkillShowSkillLine(skillLine);
		UpdateEsoAllSkillDescription();
		UpdateEsoAllSkillCost();
		UpdateEsoSkillRawData();
		UpdateEsoSkillCoefData();
		UpdateSkillLink();
	}
}


function OnEsoSkillBlockPlusClick(event)
{
	$(this).parent().next('.esovsAbilityBlockList').slideToggle();
}


function GetEsoSkillInputValues()
{
	var magicka = parseInt($('#esovsInputMagicka').val());
	var stamina = parseInt($('#esovsInputStamina').val());
	var health = parseInt($('#esovsInputHealth').val());
	var spellDamage = parseInt($('#esovsInputSpellDamage').val());
	var weaponDamage = parseInt($('#esovsInputWeaponDamage').val());
	var level = ParseEsoLevel($('#esovsInputLevel').val());
	
	g_LastSkillInputValues = { magicka: magicka,
			 stamina: stamina,
			 health: health,
			 spellDamage: spellDamage,
			 weaponDamage: weaponDamage,
			 maxStat: Math.max(stamina, magicka),
			 maxDamage: Math.max(spellDamage, weaponDamage),
			 level: level
		};
	
	return g_LastSkillInputValues; 
}


function ComputeEsoSkillValue(values, type, a, b, c)
{
	var value = 0;
	
	a = parseFloat(a);
	b = parseFloat(b);
	c = parseFloat(c);

	if (type == -2) // Health
	{
		value = a * values.health + c;
	}
	else if (type == 0) // Magicka
	{
		value = a * values.magicka + b * values.spellDamage + c;
	}
	else if (type == 6) // Stamina
	{
		value = a * values.stamina + b * values.weaponDamage + c;
	}
	else if (type == 10) // Ultimate
	{
		value = a * values.maxStat + b * values.maxDamage + c;
	}
	else if (type == -50) // Ultimate Soul Tether
	{
		value = a * values.maxStat + b * values.spellDamage + c;
	}
	else if (type == -56) // Spell + Weapon Damage
	{
		value = a * values.spellDamage + b * values.weaponDamage + c;
	}
	else if (type == -51)
	{
		return '(' + a + ' * LIGHTARMOR)';
	}
	else if (type == -52)
	{
		return '(' + a + ' * MEDIUMARMOR)';
	}
	else if (type == -53)
	{
		return '(' + a + ' * HEAVYARMOR)';
	}
	else if (type == -54)
	{
		return '(' + a + ' * DAGGER)';
	}
	else if (type == -55)
	{
		return '(' + a + ' * ARMORTYPES)';
	}
	else
	{
		return '?';
	}
	
	return Math.round(value);
}


function GetEsoSkillDescription(skillId, inputValues, useHtml)
{
	var output = "";
	var skillData = g_SkillsData[skillId];
	
	if (skillData == null) return "";
	
	var coefDesc = skillData['coefDescription'];
	
	if (coefDesc == null || coefDesc == "") 
	{
		if (useHtml) return EsoConvertDescToHTML(skillData['description']);
		return EsoConvertDescToText(skillData['description']);
	}
	
	if (inputValues == null) inputValues = GetEsoSkillInputValues()
	
	for (var i = 1; i <= MAX_SKILL_COEF; ++i)
	{
		var type  = skillData['type' + i];
		if (type == -1) continue;
		
		var a = skillData['a' + i];
		var b = skillData['b' + i];
		var c = skillData['c' + i];
		var srcString = "$" + i;
		
		var value = ComputeEsoSkillValue(inputValues, type, a, b, c);
		coefDesc = coefDesc.replace(srcString, value);
	}
	
	if (useHtml)
	{
		output = EsoConvertDescToHTML(coefDesc);
	}
	else
	{
		var effectLines = skillData['effectLines'];
		if (effectLines != "") coefDesc += " <div class='esovsAbilityBlockEffectLines'>" + effectLines + "</div>";
		output = EsoConvertDescToText(coefDesc)
	}
	
	return output;
}


function UpdateEsoSkillDescription(skillId, descElement, inputValues, useHtml)
{
	var html = GetEsoSkillDescription(skillId, inputValues, useHtml); 
	descElement.html(html);
}


function UpdateEsoSkillTooltipDescription()
{
	if (g_LastSkillId <= 0) return;
	UpdateEsoSkillDescription(g_LastSkillId, $("#esovsSkillTooltipDesc"), GetEsoSkillInputValues(), true)
}


function ComputeEsoSkillCost(maxCost, level)
{
	if (!g_SkillUseUpdate10Cost) return ComputeEsoSkillCostOld(maxCost, level)
	maxCost = parseInt(maxCost);
	
	if (level == null) 
	{
		var inputValues = GetEsoSkillInputValues()
		level = inputValues.level;
	}
	
	if (level < 1) level = 1;
	if (level >= 66) return maxCost;
	
	return Math.round(maxCost * level / 72.0 + maxCost / 12.0);
}


function ComputeEsoSkillCostOld(maxCost, level)
{
	maxCost = parseInt(maxCost);
	
	if (level == null) 
	{
		var inputValues = GetEsoSkillInputValues()
		level = inputValues.level;
	}
	
	if (level < 1) level = 1;
	if (level >= 66) return maxCost;
	
	if (level >= 1 && level <= 50) return Math.round(maxCost * level * 25.0 / 1624.0 + maxCost * 75.0 / 812.0);
	return Math.round(maxCost * level / 116.0 + maxCost / 2.32);
}


function UpdateEsoSkillTooltipCost()
{		
	if (g_LastSkillId <= 0) return;
	UpdateEsoSkillCost(g_LastSkillId, $("#esovsSkillTooltipCost"), GetEsoSkillInputValues());
}


function UpdateEsoSkillCost(skillId, costElement, inputValues)
{
	var skillData = g_SkillsData[skillId];
	if (skillData == null) return;
	
	var mechanic = skillData['mechanic'];
	if (mechanic != 0 && mechanic != 6) return;
	
	var passive = skillData['isPassive'];
	if (passive != 0) return;
	
	var baseCost = parseInt(skillData['cost']);
	var cost = ComputeEsoSkillCost(baseCost, inputValues.level);
	
	var costStr = "" + cost + " ";
	
	if (mechanic == 0)
		costStr += "Magicka";
	else if (mechanic == 6)
		costStr += "Stamina";
	
	costElement.text(costStr);
}


function UpdateEsoSkillCost_ForEach(index, element)
{
	var skillId = $(this).attr('skillid');
	if (skillId == null || skillId == '') return;
	
	UpdateEsoSkillCost(skillId, $(element), g_LastSkillInputValues);
}


function UpdateEsoSkillDescription_ForEach(index, element)
{
	var skillId = $(this).attr('skillid');
	if (skillId == null || skillId == '') return;
	
	UpdateEsoSkillDescription(skillId, $(element), g_LastSkillInputValues, false);
}


function UpdateEsoAllSkillDescription()
{
	var inputValues = GetEsoSkillInputValues();
	$(".esovsSkillContentBlock:visible .esovsAbilityBlockDesc").each(UpdateEsoSkillDescription_ForEach);
}


function UpdateEsoAllSkillCost()
{
	var inputValues = GetEsoSkillInputValues();
	$(".esovsSkillContentBlock:visible .esovsAbilityBlockCost").each(UpdateEsoSkillCost_ForEach);
}


function UpdateEsoSkillRawData(skillId)
{
	var rawDataElement = $("#esovsRawDataContent");
	
	UpdateEsoSkillRawDataLink();
		
	if (skillId == null) skillId = g_LastSkillId;
	var skillData = g_SkillsData[skillId];
	
	if (skillData == null) 
	{
		rawDataElement.html("");
		return;
	}
	
	var output = "";
	var keysOutput = {};

	for (var i in RAWDATA_KEYS)
	{
		var key = RAWDATA_KEYS[i];
		
		if (RAWDATA_IGNORE_KEYS[key] || keysOutput[key]) continue;
		keysOutput[key] = true;
		
		var value = skillData[key];
		
		output += "<div class='esovsRawDataRow'>";
		output += "<div class='esovsRawDataName'>" + key + "</div> ";
		output += "<div class='esovsRawDataValue'>" + value + "</div> ";
		output += "</div>";
	}
	
	var keys = Object.keys(skillData);
	
	for (var i in keys) 
	{
		var key = keys[i];
		
		if (RAWDATA_IGNORE_KEYS[key] || keysOutput[key]) continue;
		keysOutput[key] = true;
		
		var value = skillData[key];
		
		output += "<div class='esovsRawDataRow'>";
		output += "<div class='esovsRawDataName'>" + key + "</div> ";
		output += "<div class='esovsRawDataValue'>" + value + "</div> ";
		output += "</div>";
	}
	
	rawDataElement.html(output);
}


function GetEsoSkillCoefDataHtml(skillData, i)
{
	var type  = skillData['type' + i];
	if (type == -1) return "";
	
	var output = "<div class='esovsSkillCoefRow'>";
	var a = parseFloat(skillData['a' + i]).toFixed(5);
	var b = parseFloat(skillData['b' + i]).toFixed(5);
	var c = parseFloat(skillData['c' + i]).toFixed(5);
	var R = parseFloat(skillData['R' + i]).toFixed(5);
	var bOp = '+';
	var cOp = '+';
	var typeString = "";
	var srcString = "<b>$" + i + "</b>";
	var ratio = '';
	
	if (b < 0) { bOp = '-'; b = -b; }
	if (c < 0) { cOp = '-'; c = -c; }
	
	if (type == -2) // Health
	{
		output += srcString + " = " + a + " Health " + cOp + " " + c;
		typeString = "Health";
	}
	else if (type == 0) // Magicka
	{
		output += srcString + " = " + a + " Magicka " + bOp + " " + b + " SpellDamage " + cOp + " " + c;
		typeString = "Magicka";
		ratio = (b/a).toFixed(2);
	}
	else if (type == 6) // Stamina
	{
		output += srcString + " = " + a + " Stamina " + bOp + " " + b + " WeaponDamage " + cOp + " " + c;
		typeString = "Stamina";
		ratio = (b/a).toFixed(2);
	}
	else if (type == 10) // Ultimate
	{
		output += srcString + " = " + a + " MaxStat " + bOp + " " + b + " MaxDamage " + cOp + " " + c;
		typeString = "Ultimate";
		ratio = (b/a).toFixed(2);
	}
	else if (type == -50) // Ultimate Soul Tether
	{
		output += srcString + " = " + a + " MaxStat " + bOp + " " + b + " SpellDamage " + cOp + " " + c;
		typeString = "Ultimate (no weapon damage)";
		ratio = (b/a).toFixed(2);
	}
	else if (type == -51) // Light Armor
	{
		output += srcString + " = " + a + " LightArmor " + cOp + " " + c;
		typeString = "Light Armor #";
	}
	else if (type == -52) // Medium Armor
	{
		output += srcString + " = " + a + " MediumArmor " + cOp + " " + c;
		typeString = "Medium Armor #";
	}
	else if (type == -53) // Heavy Armor
	{
		output += srcString + " = " + a + " HeavyArmor " + cOp + " " + c;
		typeString = "Heavy Armor #";
	}
	else if (type == -54) // Dagger
	{
		output += srcString + " = " + a + " Dagger " + cOp + " " + c;
		typeString = "Dagger #";
	}
	else if (type == -55) // Armor Types
	{
		output += srcString + " = " + a + " ArmorTypes " + cOp + " " + c;
		typeString = "Armor Type #";
	}
	else if (type == -56) // Spell + Weapon Damage
	{
		output += srcString + " = " + a + " SpellDamage " + bOp + " " + b + " WeaponDamage " + cOp + " " + c;
		typeString = "Spell + Weapon Damage";
	}
	else
	{
		output += srcString + " = ?";
		typeString = "Unknown Type " + type;
	}
	
	output += "</div>";
	output += "<div class='esovsSkillCoefRowDetail'>" + typeString + ", R<sup>2</sup> = " + R;
	if (ratio != "") output += ", Ratio = " + ratio;
	output += "</div>";
	
	return output;
}


function UpdateEsoSkillCoefData(skillId)
{
	var skillCoefElement = $("#esovsSkillCoefContent");
	
	if (skillId == null) skillId = g_LastSkillId;
	var skillData = g_SkillsData[skillId];
	
	if (skillData == null || skillData['numCoefVars'] <= 0) 
	{
		skillCoefElement.html("No known skill coefficients.");
		return;
	}
	
	var output = "";
	
	var numCoefVars = skillData['numCoefVars'];
	var skillDesc = EsoConvertDescToHTMLClass(skillData['coefDescription'], 'esovsBold'); 
	output += "Showing " + numCoefVars + " known skill coefficients:<p />";
		
	for (var i = 1; i <= MAX_SKILL_COEF; ++i)
	{
		output += GetEsoSkillCoefDataHtml(skillData, i);
	}
	
	output += "<div class='esovsSkillCoefDesc'>" + skillDesc + "</div>";
	
	skillCoefElement.html(output);
}


function UpdateEsoSkillRawDataLink(skillId)
{
	var linkElement = $("#esovsRawDataSkillLink");
	
	if (skillId == null) skillId = g_LastSkillId;

	if (skillId == null || skillId <= 0)
	{
		linkElement.removeAttr("href");
		return;
	}
	
	linkElement.attr("href", "http://esoitem.uesp.net/viewlog.php?action=view&record=minedSkills&id=" + skillId);
}


function ParseEsoLevel(level)
{
	if (level == null) return 66;
	
	if ($.isNumeric(level)) 
	{
		level =  parseInt(level);
		if (level < 1) return 1;
		if (level > 66) return 66;
	}
	
	var vetRank = level.match(/^\s*[v](\d+)/i);
	
	if (vetRank != null) 
	{
		level = parseInt(vetRank[1]) + 50;
		
		if (isNaN(level)) return 66;
		if (level > 66) return 66;
		if (level < 1) return 1;
		return level;
	}
	
	var cpLevel = level.match(/^\s*CP(\d+)/i);
	
	if (cpLevel != null) 
	{
		level =  Math.floor(parseInt(cpLevel[1])/10) + 50;
		
		if (isNaN(level)) return 66;
		if (level > 66) return 66;
		if (level < 1) return 1;
		return level;
	}
		
	level = parseInt(level);
	if (isNaN(level)) return 1;
	return level;
}


function FormatEsoLevel(level)
{
	if (level <= 0 || level > 66) return level;
	if (level <= 50) return level;

	//return "v" + (level - 50);
	return "CP" + (level - 50)*10;
}


function OnChangeEsoSkillData(dataName)
{
	var rangeControl = $('#esovsControl' + dataName);
	var inputControl = $('#esovsInput'   + dataName);
	var value = this.value;
	
	if (!$(this).is(rangeControl))
	{
		if (dataName == "Level") value = ParseEsoLevel(value);
		rangeControl.val(value);
	}
	
	if (!$(this).is(inputControl))
	{
		if (dataName == "Level") value = FormatEsoLevel(value);
		inputControl.val(value);
	}	
	
	UpdateEsoSkillTooltipDescription();
	UpdateEsoSkillTooltipCost();
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
	UpdateSkillLink();
}


function OnToggleSkillCoef(event)
{
	var object = $("#esovsSkillCoefContent");
	var isVisible = object.is(":visible");
	
	object.slideToggle();
	
	if (isVisible)
		$(this).text("Show Skill Coefficients");
	else
		$(this).text("Hide Skill Coefficients");
}


function OnToggleRawDataCoef(event)
{
	var object = $("#esovsRawDataContent");
	var isVisible = object.is(":visible");
	
	object.slideToggle();
	
	if (isVisible)
		$(this).text("Show Raw Data");
	else
		$(this).text("Hide Raw Data");
}


function FindNextEsoSkillText()
{
	var searchText = g_EsoSkillSearchText.toLowerCase();
	
	for (var i = g_EsoSkillSearchLastIndex + 1; i < g_SkillSearchIds.length; ++i)
	{
		var id = g_SkillSearchIds[i];
		var skillData = g_SkillsData[id];
		
		var index = skillData['name'].toLowerCase().indexOf(searchText);
		if (index < 0) index = skillData['description'].toLowerCase().indexOf(searchText);
		
		if (index >= 0 && skillData.__isOutput)
		{
			return { id : id, index: i };
		}
	}
	
	return null;
}


function SelectEsoSkillLine(skillType, skillLine)
{
	var currentSkillType = $(".esovsSkillTypeTitle.esovsSkillTypeTitleHighlight");
	var currentSkillLine = $(".esovsSkillLineTitle.esovsSkillLineTitleHighlight");
	var forceLineChange = false;
	
	skillType = skillType.toUpperCase();
	
	if (currentSkillType.text() != skillType)
	{
		var newSkillType = $(".esovsSkillTypeTitle:contains('" + skillType + "')");
		
		$(".esovsSkillType:visible").slideUp();
		currentSkillType.removeClass("esovsSkillTypeTitleHighlight");
		currentSkillLine.removeClass("esovsSkillLineTitleHighlight");
		
		newSkillType.next(".esovsSkillType").slideDown();
		newSkillType.addClass("esovsSkillTypeTitleHighlight");
		
		forceLineChange = true;
	}
	
	if (forceLineChange || currentSkillLine.text() != skillLine)
	{
		var newSkillLine = $(".esovsSkillLineTitle:contains('" + skillLine + "')");
		
		currentSkillLine.removeClass("esovsSkillLineTitleHighlight");
		newSkillLine.addClass("esovsSkillLineTitleHighlight");
		
		EsoSkillShowSkillLine(skillLine);
	}
	
}


function DoesEsoSkillBlockExist(id)
{
	var objects = $(".esovsAbilityBlock[skillid='" + id + "']");
	return objects.length != 0;
}


function IsScrolledIntoView($elem)
{
    var $window = $(window);

    var docViewTop = $window.scrollTop();
    var docViewBottom = docViewTop + $window.height();

    var elemTop = $elem.offset().top;
    var elemBottom = elemTop + $elem.height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}


function HighlightEsoSkill(id)
{
	var skillData = g_SkillsData[id];
	if (skillData == null) return false;
	
	var skillTypeName = skillData['skillTypeName'];
	var splitName = skillTypeName.split("::");
	var skillType = splitName[0];
	var skillLine = splitName[1];
	
	if (skillType == null || skillLine == null) return false;
	
	SelectEsoSkillLine(skillType, skillLine);
	EsoViewSkillShowTooltip(skillData);
	UpdateEsoSkillTooltipDescription();
	UpdateEsoSkillTooltipCost();
	UpdateEsoSkillRawData();
	UpdateEsoSkillCoefData();
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
	UpdateSkillLink();
	
	var abilityBlock = $(".esovsAbilityBlock[skillid='" + id + "']");
	
	if (!abilityBlock.is(':visible'))
	{
		abilityBlock.parent(".esovsAbilityBlockList").slideDown();
	}
	
	abilityBlock.addClass("esovsSearchHighlight");
	
	if (!IsScrolledIntoView(abilityBlock))
	{
		/*
		abilityBlock[0].scrollIntoView({
		    behavior: "smooth",
		    block: "end"
		}); */
	}
	
	return true;
}


function UpdateSkillLink()
{
	var linkElement = $("#esovsLinkBlock");
	var inputValues = GetEsoSkillInputValues();
	var params = "";
	
	params += "id=" + g_LastSkillId;
	params += "&level=" + inputValues.level;
	params += "&health=" + inputValues.health;
	params += "&magicka=" + inputValues.magicka;
	params += "&stamina=" + inputValues.stamina;
	params += "&spelldamage=" + inputValues.spellDamage;
	params += "&weapondamage=" + inputValues.weaponDamage;
	
	if (g_SkillShowAll) params += "&showall";
	
	linkElement.attr("href", "?" + params);
}


function DoEsoSkillSearch(text)
{
	var newSearch = false;
	
	if (text != g_EsoSkillSearchText) 
	{
		g_EsoSkillSearchText = text;
		g_EsoSkillSearchLastIndex = -1;
		newSearch = true;
	}
	
	$(".esovsSearchHighlight").removeClass("esovsSearchHighlight");
	
	var result = FindNextEsoSkillText();
	
	if (result == null)
	{
		g_EsoSkillSearchLastIndex = -1;
		$("#esovsSearchResult").text("No matches found!");
		return false;
	}
	
	g_EsoSkillSearchLastIndex = result.index;
	$("#esovsSearchResult").text("Found match! Search again for next match...");
	
	HighlightEsoSkill(result.id);
	return true;
}


function OnSkillSearch(event)
{
	var text = $("#esovsSearchText").val().trim();
	DoEsoSkillSearch(text);
}


function OnHoverEsoIcon(e)
{
	var parentBlock = $(this).parent(".esovsAbilityBlock");
	var skillid = parentBlock.attr("skillid");
	
	if (skillid == null || skillid == "") return;
	
	var skillData = g_SkillsData[parseInt(skillid)];
	EsoShowPopupSkillTooltip(skillData, $(this)[0]);
}


function OnLeaveEsoIcon(e)
{
	var popupElement = $("#esovsPopupSkillTooltip");
	popupElement.hide();
}


function esovsOnDocReady()
{
	$('.esovsSkillTypeTitle').click(OnEsoSkillTypeTitleClick);
	$('.esovsSkillLineTitle').click(OnEsoSkillLineTitleClick);
	
	$('.esovsAbilityBlock').click(OnEsoSkillBlockClick);
	$('.esovsAbilityBlockPlus').click(OnEsoSkillBlockPlusClick);
	
	$('#esovsControlLevel').on('input', function(e) { OnChangeEsoSkillData.call(this, 'Level'); });
	$('#esovsInputLevel').on('input', function(e) { OnChangeEsoSkillData.call(this, 'Level');	});
	
	$('#esovsControlMagicka').on('input', function(e) { OnChangeEsoSkillData.call(this, 'Magicka'); });
	$('#esovsInputMagicka').on('input', function(e) { OnChangeEsoSkillData.call(this, 'Magicka');	});
	
	$('#esovsControlStamina').on('input', function(e) { OnChangeEsoSkillData.call(this, 'Stamina'); });
	$('#esovsInputStamina').on('input', function(e) { OnChangeEsoSkillData.call(this, 'Stamina');	});
	
	$('#esovsControlHealth').on('input', function(e) { OnChangeEsoSkillData.call(this, 'Health'); });
	$('#esovsInputHealth').on('input', function(e) { OnChangeEsoSkillData.call(this, 'Health');	});
	
	$('#esovsControlSpellDamage').on('input', function(e) { OnChangeEsoSkillData.call(this, 'SpellDamage'); });
	$('#esovsInputSpellDamage').on('input', function(e) { OnChangeEsoSkillData.call(this, 'SpellDamage');	});
	
	$('#esovsControlWeaponDamage').on('input', function(e) { OnChangeEsoSkillData.call(this, 'WeaponDamage'); });
	$('#esovsInputWeaponDamage').on('input', function(e) { OnChangeEsoSkillData.call(this, 'WeaponDamage');	});

	$("#esovsSkillCoefButton").click(OnToggleSkillCoef);
	$("#esovsRawDataButton").click(OnToggleRawDataCoef);
	
	$("#esovsSearchText").on("keypress", function(e) {
			if ( e.keyCode == 13 ) OnSkillSearch(e); 
		});
	$("#esovsSearchButton").click(OnSkillSearch);
	
	$(".esovsAbilityBlockIcon").hover(OnHoverEsoIcon, OnLeaveEsoIcon);
	$(".esovsAbilityBlockPassiveIcon").hover(OnHoverEsoIcon, OnLeaveEsoIcon);
	
	var highlightSkill = $(".esovsSearchHighlight");
	
	if (highlightSkill.length == 0)
		$(".esovsAbilityBlock").first().trigger('click');
	else
		highlightSkill.trigger('click');
	
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
}


$( document ).ready(esovsOnDocReady);	