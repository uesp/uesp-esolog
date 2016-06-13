var g_LastSkillId = 0;
var g_LastSkillInputValues = {};
var MAX_SKILL_COEF = 6;

var g_EsoSkillSearchText = "";
var g_EsoSkillSearchLastIndex = -1;

var g_EsoSkillDragData = {};

var g_EsoSkillUpdateEnable = true;


ESO_SKILL_TYPES = {
		0 : "",
		1 : "Class",
		2 : "Weapon",
		3 : "Armor",
		4 : "World",
		5 : "Guild",
		6 : "Alliance War",
		7 : "Racial",
		8 : "Craft",
		9 : "Champion",	
};


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
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}([^|]*)\|r/g, '<div class="' + className + '">$1</div>');
	newDesc = newDesc.replace(/\n/g, '<br />');
	return newDesc;
}


function EsoConvertDescToText(desc)
{
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}([^|]*)\|r/g, '$1');
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
		var realCost = ComputeEsoSkillCost(cost, null, null, skillData);
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
		else if (mechanic == 10)
		{
			costStr += "Ultimate";
			costClass = "esovsUltimate";
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
	
	if (g_EsoSkillDragData.isDragging)
	{
		popupElement.hide();
		return;
	}
	
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
	
	var firstSkillLine = $(this).next(".esovsSkillType").children(".esovsSkillLineTitle").not(".esovsSkillLineDisabled").first();
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


function OnEsoSkillBlockPlusSelectClick(e)
{
	var $openList = $('.esovsAbilityBlockList:visible');
	var $parent = $(this).parent();
	var $element = $parent.next('.esovsAbilityBlockList');
		
	if ($openList[0] == $element[0])
	{
		$element.slideUp();
		return false;
	}
	
	$openList.slideUp();
	$element.slideToggle();
	
	var offsetTop = $element.offset().top;
	var offsetBottom = offsetTop + $element.height();
	var scrollTop = $element.parent().scrollTop();
	var scrollBottom = $element.parent().height();
	
	if (offsetTop > scrollBottom)
	{
		$element.parent().animate({
			scrollTop: offsetTop,
	    });
	}
	
	e.preventDefault();
	e.stopPropagation();
	return false;
}


function OnEsoSkillBlockMinusSelectClick(event)
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
	
	g_LastSkillInputValues = { Magicka: magicka,
			 Stamina: stamina,
			 Health: health,
			 SpellDamage: spellDamage,
			 WeaponDamage: weaponDamage,
			 MaxStat: Math.max(stamina, magicka),
			 MaxDamage: Math.max(spellDamage, weaponDamage),
			 EffectiveLevel: level
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
		value = a * values.Health + c;
	}
	else if (type == 0) // Magicka
	{
		value = a * values.Magicka + b * values.SpellDamage + c;
	}
	else if (type == 6) // Stamina
	{
		value = a * values.Stamina + b * values.WeaponDamage + c;
	}
	else if (type == 10) // Ultimate
	{
		value = a * values.MaxStat + b * values.MaxDamage + c;
	}
	else if (type == -50) // Ultimate Soul Tether
	{
		value = a * values.MaxStat + b * values.SpellDamage + c;
	}
	else if (type == -56) // Spell + Weapon Damage
	{
		value = a * values.SpellDamage + b * values.WeaponDamage + c;
	}
	else if (type == -51)
	{
		if (values.LightArmor == null) return '(' + a + ' * LIGHTARMOR)';
		value = a * values.LightArmor;
	}
	else if (type == -52)
	{
		if (values.MediumArmor == null) return '(' + a + ' * MEDIUMARMOR)';
		value = a * values.MediumArmor;
	}
	else if (type == -53)
	{
		if (values.HeavyArmor == null) return '(' + a + ' * HEAVYARMOR)';
		value = a * values.HeavyArmor;
	}
	else if (type == -54)
	{
		if (values.DaggerWeapon == null) return '(' + a + ' * DAGGER)';
		value = a * values.DaggerWeapon;
	}
	else if (type == -55)
	{
		if (values.ArmorTypes == null) return '(' + a + ' * ARMORTYPES)';
		value = a * values.ArmorTypes;
	}
	else
	{
		return '?';
	}
	
	value = Math.round(value);
	
	if (value < 0) return 0;
	return value;
}


function GetEsoSkillDescription(skillId, inputValues, useHtml, noEffectLines)
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
	
	coefDesc = UpdateEsoSkillDamageDescription(skillData, coefDesc, inputValues);
	coefDesc = UpdateEsoSkillHealingDescription(skillData, coefDesc, inputValues);
	
	if (useHtml)
	{
		output = EsoConvertDescToHTML(coefDesc);
	}
	else
	{
		var effectLines = skillData['effectLines'];
		if (effectLines != "" && noEffectLines !== true) coefDesc += " <div class='esovsAbilityBlockEffectLines'>" + effectLines + "</div>";
		output = EsoConvertDescToText(coefDesc)
	}
	
	skillData.lastDesc = output;
	return output;
}


ESO_SKILL_HEALINGMATCHES = 
[
	{
		healId: "Done",
		match: /(healing yourself or a wounded ally for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(heals one other injured target for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(healing you and nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(healing nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(additional \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(restoring \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(healing you and nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r every)/,
	},
	{
		healId: "Done",
		match: /(healing them for an additional \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/,
	},
	{
		healId: "Done",
		healId2: "Received",  // TODO: ?
		match: /(Each reflected spell heals you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/,
	},
	{
		healId: "Done",
		healId2: "Received",  // TODO: ?
		match: /(heal yourself for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		healId2: "Received",  // TODO: ?
		match: /(You also heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(healing you and your allies in the target area for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(healing you or up to 2 nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/,
	},
	{
		healId: "Done",
		match: /(heals for an immediate \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(healing you and your allies in front of you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(Each attack against the enemy restores \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		healId2: "Taken",	//TODO: Sap Essence?
		match: /(healing you and your allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/,
	},
	{
		healId: "Done",
		match: /(healing them for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(healing themselves for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	{
		healId: "Done",
		match: /(healing yourself and nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/,
	},
	

];                       


function UpdateEsoSkillHealingDescription(skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	if (inputValues.Healing == null) return newDesc;
	
	var rawOutput = [];
	var newRawOutput = {};
	
	for (var i = 0; i < ESO_SKILL_HEALINGMATCHES.length; ++i)
	{
		var matchData = ESO_SKILL_HEALINGMATCHES[i];
		
		newDesc = newDesc.replace(matchData.match, function(match, p1, p2, p3, offset, string)
		{
			if (inputValues.Healing[matchData.healId] == null) return string;
			
			var modHealing = parseFloat(p2);
			
			newRawOutput = {};
			newRawOutput.healId = matchData.healId;
			newRawOutput.baseHeal = p2;
			newRawOutput.healDone = inputValues.Healing[matchData.healId];
			
			modHealing *= 1 + inputValues.Healing[matchData.healId];
			modHealing = Math.round(modHealing);
			
			newRawOutput.finalHeal = modHealing;
			rawOutput.push(newRawOutput);
			
			return p1 + modHealing + p3;
		});
	}
	
	for (var i = 0; i < rawOutput.length; ++i)
	{
		var rawData = rawOutput[i];
		var output = "";
				
		if (rawData.healDone != null && rawData.healDone != 0) output += " + " + (rawData.healDone*100) + "% " + rawData.healId;
		//TODO: healId2?
		
		if (output == "")
			output = "" + rawData.baseHeal + " Health (unmodified)";
		else
			output = "" + rawData.baseHeal + " Health " + output + " = " + rawData.finalHeal + " final";
		
		skillData.rawOutput["Tooltip Healing " + (i+1)] = output;
	}
	
	return newDesc;
}


ESO_SKILL_DAMAGEMATCHES = 
[
	{
		damageId: "Magic",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Magic Damage)( over|)/gi,
	},
	{
		damageId: "Physical",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Physical Damage)( over|)/gi,
	},
	{
		damageId: "Flame",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Flame Damage)( over|)/gi,
	},
	{
		damageId: "Shock",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Shock Damage)( over|)/gi,
	},
	{
		damageId: "Cold",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Cold Damage)( over|)/gi,
	},
	{
		damageId: "Poison",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Poison Damage)( over|)/gi,
	},
	{
		damageId: "Disease",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Disease Damage)( over|)/gi,
	},
];


function UpdateEsoSkillDamageDescription(skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	if (inputValues.Damage == null) return newDesc;
	
	var rawOutput = [];
	var newRawOutput = {};
	
	var isDot = false;
	if (skillData.channelTime > 0) isDot = true;
	if (inputValues.Damage.Dot == null || isNaN(inputValues.Damage.Dot)) isDot = false;
	
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	
	for (var i = 0; i < ESO_SKILL_DAMAGEMATCHES.length; ++i)
	{
		var matchData = ESO_SKILL_DAMAGEMATCHES[i];
		
		newDesc = newDesc.replace(matchData.match, function(match, p1, p2, p3, p4, p5, offset, string) 
		{
			if (inputValues.Damage[matchData.damageId] == null) return string;
			
			var modDamage = parseFloat(p3);
			
			newRawOutput = {};
			newRawOutput.damageId = matchData.damageId;
			newRawOutput.baseDamage = p3;
			newRawOutput.mainDamageDone = inputValues.Damage[matchData.damageId];
					
			if (isDot || p1 != "" || p5 != "") 
			{
				modDamage *= 1 + inputValues.Damage.Dot + inputValues.Damage[matchData.damageId];
				newRawOutput.dotDamageDone = inputValues.Damage.Dot;
			}
			else
			{
				modDamage *= 1 + inputValues.Damage[matchData.damageId];
			}
			
			if (inputValues.Damage.All != null)
			{
				modDamage *= 1 + inputValues.Damage.All;
				
				newRawOutput.damageDone = inputValues.Damage.All;
				newRawOutput.finalDamage = Math.round(modDamage);
			}
			
			rawOutput.push(newRawOutput);
			modDamage = Math.round(modDamage);
			return p1 + p2 + modDamage + p4 + p5;
		});
	}
	
	for (var i = 0; i < rawOutput.length; ++i)
	{
		var rawData = rawOutput[i];
		var output = "";
				
		if (rawData.mainDamageDone != null && rawData.mainDamageDone != 0) output += " + " + (rawData.mainDamageDone*100) + "% " + rawData.damageId;
		if (rawData.dotDamageDone  != null && rawData.dotDamageDone  != 0) output += " + " + (rawData.dotDamageDone*100) + "% DoT";
		if (rawData.damageDone     != null && rawData.damageDone     != 0) output += " + " + (rawData.damageDone*100) + "% All";
		
		if (output == "")
			output = "" + rawData.baseDamage + " " + rawData.damageId + " Damage (unmodified)";
		else
			output = "" + rawData.baseDamage + " " + rawData.damageId + " Damage " + output + " = " + rawData.finalDamage + " final";
		
		skillData.rawOutput["Tooltip Damage " + (i+1)] = output;
	}
	
	return newDesc;
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


function CreateEsoSkillLineId(skillLine)
{
	return skillLine.replace(/ /g, "_");
}


function ComputeEsoSkillCostExtra(cost, level, inputValues, skillData)
{
	if (skillData == null) return cost;
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	
	var baseCost = cost;
	var mechanic = skillData.mechanic;
	var CPFactor = 1;
	var FlatCost = 0;
	var SkillFactor = 1;
	var skillLineId = CreateEsoSkillLineId(skillData.skillLine) + "_Cost";
	
	if (mechanic == 0 && inputValues.MagickaCost != null)
	{
		if (inputValues.MagickaCost.CP    != null) CPFactor    -= inputValues.MagickaCost.CP;
		if (inputValues.MagickaCost.Item  != null) FlatCost    += inputValues.MagickaCost.Item;
		if (inputValues.MagickaCost.Set   != null) SkillFactor -= inputValues.MagickaCost.Set;
		if (inputValues.MagickaCost.Skill != null) SkillFactor -= inputValues.MagickaCost.Skill;
		if (inputValues.MagickaCost.Buff  != null) SkillFactor -= inputValues.MagickaCost.Buff;
	}
	else if (mechanic == 6 && inputValues.StaminaCost != null)
	{
		if (inputValues.StaminaCost.CP    != null) CPFactor    -= inputValues.StaminaCost.CP;
		if (inputValues.StaminaCost.Item  != null) FlatCost    += inputValues.StaminaCost.Item;
		if (inputValues.StaminaCost.Set   != null) SkillFactor -= inputValues.StaminaCost.Set;
		if (inputValues.StaminaCost.Skill != null) SkillFactor -= inputValues.StaminaCost.Skill;
		if (inputValues.StaminaCost.Buff  != null) SkillFactor -= inputValues.StaminaCost.Buff;
	}
	else if (mechanic == 10 && inputValues.UltimateCost != null)
{
		if (inputValues.UltimateCost.CP    != null) CPFactor    -= inputValues.UltimateCost.CP;
		if (inputValues.UltimateCost.Item  != null) FlatCost    += inputValues.UltimateCost.Item;
		if (inputValues.UltimateCost.Skill != null) SkillFactor -= inputValues.UltimateCost.Skill;
		if (inputValues.UltimateCost.Set   != null) SkillFactor -= inputValues.UltimateCost.Set;
		if (inputValues.UltimateCost.Buff  != null) SkillFactor -= inputValues.UltimateCost.Buff;
	}
	
	var output = "";
	if (CPFactor != 1) output += " - " + Math.round(1000 - CPFactor*1000)/10 + "% CP";
	if (FlatCost != 0) output += " - " + FlatCost + " Flat";
	if (SkillFactor != 1) output += " - " + Math.round(1000 - SkillFactor*1000)/10 + "% Skill";
	
	if (inputValues.SkillLineCost != null && inputValues.SkillLineCost[skillLineId] != null)
	{
		var SkillLineFactor = parseFloat(inputValues.SkillLineCost[skillLineId]);
		SkillFactor -= SkillLineFactor;
		
		if (SkillLineFactor != 0) output += " - " + Math.round(SkillLineFactor*1000)/10 + "% SkillLine";
	}
				
	cost = Math.floor((cost * CPFactor - FlatCost) * SkillFactor);
	if (cost < 0) cost = 0;
	
	if (output == "") 
		output = " (unmodified)";
	else
		output += " = " + cost + " Final";
	
	skillData.rawOutput["Ability Cost"] = "" + baseCost + " Base " + output;
	
	return cost;
}


function ComputeEsoSkillCost(maxCost, level, inputValues, skillData)
{
	if (!g_SkillUseUpdate10Cost) return ComputeEsoSkillCostOld(maxCost, level, inputValues, skillData)
	if (inputValues == null) inputValues = GetEsoSkillInputValues();
	
	var cost = parseInt(maxCost);
	
	if (skillData != null && (skillData.mechanic == 0 || skillData.mechanic == 6))
	{
		if (maxCost == 0) return 0;
		if (level == null) level = inputValues.EffectiveLevel;
		if (level < 1) level = 1;
		if (level >= 66) level = 66;
		
		cost = Math.round(cost * level / 72.0 + cost / 12.0);
		if (cost < 0) cost = 0;
	}
	
	return ComputeEsoSkillCostExtra(cost, level, inputValues, skillData);
}


function ComputeEsoSkillCostOld(maxCost, level, inputValues, skillData)
{
	if (inputValues == null) inputValues = GetEsoSkillInputValues();
	
	var cost = parseInt(maxCost);
	
	if (skillData != null && (skillData.mechanic == 0 || skillData.mechanic == 6))
	{
		if (level == null) level = inputValues.EffectiveLevel;
		if (level < 1) level = 1;
		if (level >= 66) return cost;
		
		if (level >= 1 && level <= 50) 
			cost =  Math.round(cost * level * 25.0 / 1624.0 + cost * 75.0 / 812.0);
		else
			cost = Math.round(cost * level / 116.0 + cost / 2.32);
		
		if (cost < 0) return 0;
	}
	
	return ComputeEsoSkillCostExtra(cost, level, inputValues, skillData);
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
	if (mechanic != 0 && mechanic != 6 && mechanic != 10) return;
	
	var passive = skillData['isPassive'];
	if (passive != 0) return;
	
	var baseCost = parseInt(skillData['cost']);
	var cost = ComputeEsoSkillCost(baseCost, inputValues.EffectiveLevel, inputValues, skillData);
	
	var costStr = "" + cost + " ";
	
	if (mechanic == 0)
		costStr += "Magicka";
	else if (mechanic == 6)
		costStr += "Stamina";
	else if (mechanic == 10)
		costStr += "Ultimate";
	
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


function UpdateEsoAllSkillCost(onlyVisible)
{
	var inputValues = GetEsoSkillInputValues();
	
	if (onlyVisible == null) onlyVisible = true;
	
	if (onlyVisible)
		$(".esovsSkillContentBlock:visible .esovsAbilityBlockCost").each(UpdateEsoSkillCost_ForEach);
	else
		$(".esovsSkillContentBlock .esovsAbilityBlockCost").each(UpdateEsoSkillCost_ForEach);
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
		return level;
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
	params += "&level=" + inputValues.EffectiveLevel;
	params += "&health=" + inputValues.Health;
	params += "&magicka=" + inputValues.Magicka;
	params += "&stamina=" + inputValues.Stamina;
	params += "&spelldamage=" + inputValues.SpellDamage;
	params += "&weapondamage=" + inputValues.WeaponDamage;
	
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


function OnHoverEsoSkillBarIcon(e)
{
	var skillid = $(this).attr("skillid");
	if (skillid == null || skillid == "") return;
	
	var skillData = g_SkillsData[parseInt(skillid)];
	EsoShowPopupSkillTooltip(skillData, $(this)[0]);
}


function OnLeaveEsoSkillBarIcon(e)
{
	var popupElement = $("#esovsPopupSkillTooltip");
	popupElement.hide();
}


function UpdateEsoSkillPassiveData(origAbilityId, abilityId, rank)
{
	rank = parseInt(rank); 
		
	if (g_EsoSkillPassiveData[origAbilityId] == null)
	{
		if (rank <= 0) return true;
		g_EsoSkillPassiveData[origAbilityId] = {};
		g_EsoSkillPassiveData[origAbilityId].rank = 0;
	}
	
	if (rank <= 0)
	{
		g_EsoSkillPointsUsed -= parseInt(g_EsoSkillPassiveData[origAbilityId].rank);
		delete g_EsoSkillPassiveData[origAbilityId];
		
		if (g_EsoSkillUpdateEnable) UpdateEsoSkillTotalPoints();
		return true;
	}
	
	g_EsoSkillPointsUsed += rank - parseInt(g_EsoSkillPassiveData[origAbilityId].rank);
	
	g_EsoSkillPassiveData[origAbilityId].rank = rank;
	g_EsoSkillPassiveData[origAbilityId].abilityId = abilityId;
	g_EsoSkillPassiveData[origAbilityId].baseAbilityId = origAbilityId;
	g_EsoSkillPassiveData[origAbilityId].skillDesc = GetEsoCurrentSkillDescription(abilityId);
	
	if (g_EsoSkillUpdateEnable) UpdateEsoSkillTotalPoints();
	return true;
}


function UpdateEsoSkillActiveData(origAbilityId, abilityId, rank, abilityType, morph)
{
	var origPoints = 0;
	var newPoints = 1;
	
	if (g_EsoSkillActiveData[origAbilityId] == null)
	{
		if (rank <= 0) return true;
		g_EsoSkillActiveData[origAbilityId] = {};
		g_EsoSkillActiveData[origAbilityId].rank = 0;
	}
	else
	{
		origPoints = 1;
		if (g_EsoSkillActiveData[origAbilityId].morph > 0) ++origPoints;
	}
	
	if (rank <= 0)
	{
		g_EsoSkillPointsUsed -= origPoints;
		delete g_EsoSkillActiveData[origAbilityId];
		
		if (g_EsoSkillUpdateEnable) UpdateEsoSkillTotalPoints();
		return true;
	}
	
	if (morph > 0) ++newPoints;
	g_EsoSkillPointsUsed += newPoints - origPoints;
	
	g_EsoSkillActiveData[origAbilityId].abilityType = abilityType;
	g_EsoSkillActiveData[origAbilityId].morph = morph;
	g_EsoSkillActiveData[origAbilityId].rank = rank;
	g_EsoSkillActiveData[origAbilityId].abilityId = abilityId;
	g_EsoSkillActiveData[origAbilityId].baseAbilityId = origAbilityId;
	g_EsoSkillActiveData[origAbilityId].skillDesc = GetEsoCurrentSkillDescription(abilityId);
	
	if (g_EsoSkillUpdateEnable) UpdateEsoSkillTotalPoints();
	return true;
}


function OnAbilityBlockPurchase(e)
{
	var skillId = $(this).attr("skillid");
	var parentSkillId = $(this).parent().prev(".esovsAbilityBlock").attr("skillid");
	
	if (skillId <= 0)
		ResetEsoPurchasedSkill(parentSkillId);
	else
		PurchaseEsoSkill(skillId);
	
	$(this).parent().slideUp();
}


function EnableEsoClassSkills(className)
{
	className = className.toUpperCase();
	var classElement = $(".esovsSkillTypeTitle:contains('" + className + "')");
	
	RemovePurchasedEsoClassSkills();
	
	$(".esovsSkillTypeTitle:contains('DRAGONKNIGHT')").hide();
	$(".esovsSkillTypeTitle:contains('NIGHTBLADE')").hide();
	$(".esovsSkillTypeTitle:contains('SORCERER')").hide();
	$(".esovsSkillTypeTitle:contains('TEMPLAR')").hide();
	$(".esovsSkillContentBlock").hide();
	$(".esovsSkillType").hide();
	$(".esovsSkillLineTitleHighlight").removeClass("esovsSkillLineTitleHighlight");
	$(".esovsSkillTypeTitleHighlight").removeClass("esovsSkillTypeTitleHighlight");
		
	var skillTypes = classElement.next(".esovsSkillType");
	var firstSkillLine = skillTypes.find(".esovsSkillLineTitle").first();
	
	var skillLine = firstSkillLine.text();
	skillLine = skillLine.replace(/ /g, "_");
	
	firstSkillLine.addClass("esovsSkillLineTitleHighlight");
	
	classElement.show();
	skillTypes.show();
	$("#" + skillLine).show();
}


function PurchaseEsoSkill(abilityId)
{
	var skillElement = $(".esovsAbilityBlockList").
				children(".esovsAbilityBlock[skillid='" + abilityId + "']");
	if (skillElement.length == 0) return;
	
	var displayBlock = skillElement.parent().prev(".esovsAbilityBlock");
	if (displayBlock.length == 0) return;
	
	var passiveIconDisplayBlock = displayBlock.children(".esovsAbilityBlockPassiveIcon");
	var iconDisplayBlock = displayBlock.children(".esovsAbilityBlockIcon");
	var titleDisplayBlock = displayBlock.children(".esovsAbilityBlockTitle");
		
	var rank = skillElement.attr("rank");
	var morph = skillElement.attr("morph");
	var origAbilityId = displayBlock.attr("origskillid");
	var abilityType = skillElement.attr("abilitytype");
		
	var origRank = displayBlock.attr("rank");
	var origSkillId1 = displayBlock.attr("skillid");
	var origSkillId2 = displayBlock.attr("origskillid");
	
	var origPurchased = !displayBlock.hasClass("esovsAbilityBlockNotPurchase");
	if (!origPurchased) origRank = 0;
	
	if (origPurchased && abilityId != origSkillId1)
	{
		RemoveSkillBarAbility(origSkillId1);
		RemoveSkillBarAbility(origSkillId2);
		UpdateEsoSkillBarData();
	}
	
	displayBlock.removeClass('esovsAbilityBlockNotPurchase');
	iconDisplayBlock.attr("draggable", "true");
	displayBlock.attr("skillid", abilityId);
	displayBlock.attr("rank", rank);
	
	iconDisplayBlock.html(skillElement.children(".esovsAbilityBlockIcon").html());
	titleDisplayBlock.html(skillElement.children(".esovsAbilityBlockTitle").html());
	passiveIconDisplayBlock.html(skillElement.children(".esovsAbilityBlockPassiveIcon").html());
			
	if (abilityType == "Passive")
		UpdateEsoSkillPassiveData(origAbilityId, abilityId, rank);
	else
		UpdateEsoSkillActiveData(origAbilityId, abilityId, rank, abilityType, morph);
}


function ResetEsoPurchasedSkill(abilityId)
{
	var skillElement = $(".esovsSkillContentBlock").
				children(".esovsAbilityBlock[skillid='" + abilityId + "']");
	if (skillElement.length == 0) return;
	
	var displayBlock = skillElement;
	var passiveIconDisplayBlock = displayBlock.children(".esovsAbilityBlockPassiveIcon");
	var iconDisplayBlock = displayBlock.children(".esovsAbilityBlockIcon");
	var titleDisplayBlock = displayBlock.children(".esovsAbilityBlockTitle");
	var selectBlock = displayBlock.next(".esovsAbilityBlockList").children(".esovsAbilityBlock").eq(1);
	
	var rank = skillElement.attr("rank");
	var morph = skillElement.attr("morph");
	var origAbilityId = selectBlock.attr("origskillid");
	var abilityType = skillElement.attr("abilitytype");
	
	RemoveSkillBarAbility(origAbilityId);
	RemoveSkillBarAbility(abilityId);
	UpdateEsoSkillBarData();

	displayBlock.addClass('esovsAbilityBlockNotPurchase');
	iconDisplayBlock.attr("draggable", "false");
	displayBlock.attr("skillid", origAbilityId);	
	
	iconDisplayBlock.html(selectBlock.children(".esovsAbilityBlockIcon").html());
	titleDisplayBlock.html(selectBlock.children(".esovsAbilityBlockTitle").html());
	passiveIconDisplayBlock.html(selectBlock.children(".esovsAbilityBlockPassiveIcon").html());
			
	if (abilityType == "Passive")
		UpdateEsoSkillPassiveData(origAbilityId, origAbilityId, -1);
	else
		UpdateEsoSkillActiveData(origAbilityId, origAbilityId, -1, abilityType, morph);
 
}


function RemovePurchasedEsoClassSkills()
{
	var skillElements = $(".esovsSkillContentBlock").
				children(".esovsAbilityBlock[skilltype='Class']").
				not(".esovsAbilityBlockNotPurchase");
	
	var initialUpdate = g_EsoSkillUpdateEnable;
	g_EsoSkillUpdateEnable = false;	
	
	skillElements.each(function(){
		var skillId = $(this).attr("skillid");
		ResetEsoPurchasedSkill(skillId);		
	});
	
	g_EsoSkillUpdateEnable = initialUpdate;
	
	UpdateEsoSkillBarData();
	UpdateEsoSkillTotalPoints();
}


function RemovePurchasedEsoRaceSkills()
{
	var skillElements = $(".esovsSkillContentBlock").
				children(".esovsAbilityBlock[skilltype='Racial']").
				not(".esovsAbilityBlockNotPurchase");
	
	var initialUpdate = g_EsoSkillUpdateEnable;
	g_EsoSkillUpdateEnable = false;

	skillElements.each(function() {
		var skillId = $(this).attr("skillid");
		ResetEsoPurchasedSkill(skillId);		
	});
	
	g_EsoSkillUpdateEnable = initialUpdate;
	
	UpdateEsoSkillBarData();
	UpdateEsoSkillTotalPoints();
}


function EnableEsoRaceSkills(raceName)
{
	var raceId = raceName + " Skills";
	var raceElement = $(".esovsSkillLineTitle:contains('" + raceId + "')")
	var classElement = $(".esovsSkillTypeTitle:contains('RACIAL')");
	
	RemovePurchasedEsoRaceSkills();
	
	classElement.next(".esovsSkillType").find(".esovsSkillLineTitle").hide().addClass("esovsSkillLineDisabled");
	$(".esovsSkillLineTitleHighlight").removeClass("esovsSkillLineTitleHighlight");
	$(".esovsSkillContentBlock").hide();
	$(".esovsSkillType").hide();

	raceElement.addClass("esovsSkillLineTitleHighlight");
	
	raceId = raceId.replace(/ /g, "_");

	classElement.next(".esovsSkillType").show();
	raceElement.show();
	raceElement.removeClass("esovsSkillLineDisabled");
	$("#" + raceId).show();	
}


function SetEsoSkillBarSelect(skillBarIndex)
{
	if (skillBarIndex == 1)
	{
		$("#esovsSkillBar1").addClass("esovsSkillBarHighlight");
		$("#esovsSkillBar2").removeClass("esovsSkillBarHighlight");
	}
	else if (skillBarIndex == 2)
	{
		$("#esovsSkillBar1").removeClass("esovsSkillBarHighlight");
		$("#esovsSkillBar2").addClass("esovsSkillBarHighlight");
	}
}


function OnSkillBarSelect(e)
{
	var skillBar = $(this).attr("skillbar");
	
	SetEsoSkillBarSelect(skillBar);
	
	console.log("Switch to skill bar " + skillBar);
	$(document).trigger("EsoSkillBarSwap", [ skillBar ]);
}


function OnAbilityDragStart(e)
{
	var $parent = $(this).parent();
	var abilityId = $parent.attr("skillid");
	if (abilityId == null || abilityId <= 0) return false;
	
	g_EsoSkillDragData = {};
	g_EsoSkillDragData.isDragging = true;
	g_EsoSkillDragData.abilityId = abilityId;
	g_EsoSkillDragData.skillBar = -1;
	g_EsoSkillDragData.skillIndex = -1;
	g_EsoSkillDragData.origElement = null;
	g_EsoSkillDragData.origAbilityId = $parent.attr("origskillid");;
	g_EsoSkillDragData.abilityType =  $parent.attr("abilitytype");;
	g_EsoSkillDragData.iconUrl = $(this).children("img").attr("src");
	g_EsoSkillDragData.fromSkillBar = false;
	g_EsoSkillDragData.wasDropped = false;
	
	e.originalEvent.dataTransfer.effectAllowed = "copy";
	
	var popupElement = $("#esovsPopupSkillTooltip");
	popupElement.hide();
	
	return true;
}


function OnSkillBarDragStart(e)
{
	var $this = $(this);
	var abilityId = $this.attr("skillid");
	if (abilityId == null || abilityId <= 0) return false;
	
	g_EsoSkillDragData = {};
	g_EsoSkillDragData.skillBar = $this.attr("skillbar");
	g_EsoSkillDragData.skillIndex = $this.attr("skillIndex");
	g_EsoSkillDragData.origElement = $this;
	g_EsoSkillDragData.isDragging = true;
	g_EsoSkillDragData.abilityId = abilityId;
	g_EsoSkillDragData.origAbilityId = $this.attr("origskillid");
	g_EsoSkillDragData.iconUrl = $(this).attr("src");
	g_EsoSkillDragData.fromSkillBar = true;
	g_EsoSkillDragData.wasDropped = false;
	
	if (g_EsoSkillDragData.skillIndex < 6)
		g_EsoSkillDragData.abilityType = "Active";
	else
		g_EsoSkillDragData.abilityType = "Ultimate";
	
	e.originalEvent.dataTransfer.effectAllowed = "copy";
	
	var popupElement = $("#esovsPopupSkillTooltip");
	popupElement.hide();
	
	return true;
}


function OnSkillBarDragOver(e)
{
	var $this = $(this);
	var skillBar = $this.attr("skillbar");
	var skillIndex = $this.attr("skillindex");
	
	if (g_EsoSkillDragData.skillBar == skillBar && g_EsoSkillDragData.skillIndex == skillIndex) return true;
	
	if (skillIndex == 6 && g_EsoSkillDragData.abilityType != "Ultimate") return true;
	if (skillIndex  < 6 && g_EsoSkillDragData.abilityType == "Ultimate") return true;
	
	return false;
}


function OnSkillBarDrop(e)
{
	var $this = $(this);
	var skillBar = $this.attr("skillbar");
	var skillIndex = $this.attr("skillindex");
	
	g_EsoSkillDragData.wasDropped = true;
		
	if (g_EsoSkillDragData.origElement != null)
	{
		var swapId = $this.attr("skillid");
		var swapOrigId = $this.attr("origskillid");
		
		if (g_EsoSkillDragData.skillBar == skillBar && g_EsoSkillDragData.skillIndex == skillIndex) return;
		
		if (swapId != g_EsoSkillDragData.abilityId && swapOrigId != g_EsoSkillDragData.origAbilityId && g_EsoSkillDragData.skillBar != skillBar)
		{
			RemoveSkillBarAbility(g_EsoSkillDragData.abilityId, skillBar);
			RemoveSkillBarAbility(g_EsoSkillDragData.origAbilityId, skillBar);
			RemoveSkillBarAbility(swapId, g_EsoSkillDragData.skillBar);
			RemoveSkillBarAbility(swapOrigId, g_EsoSkillDragData.skillBar);
		}
		
		g_EsoSkillDragData.origElement.attr("origskillid", swapOrigId);
		g_EsoSkillDragData.origElement.attr("skillid", swapId);
		g_EsoSkillDragData.origElement.attr("src", $this.attr("src"));
	}
	else 
	{
		RemoveSkillBarAbility(g_EsoSkillDragData.abilityId, skillBar);
		RemoveSkillBarAbility(g_EsoSkillDragData.origAbilityId, skillBar);
	}
	
	$(this).attr("origskillid", g_EsoSkillDragData.origAbilityId);
	$(this).attr("skillid", g_EsoSkillDragData.abilityId);
	$(this).attr("src", g_EsoSkillDragData.iconUrl);
	$(this).attr("draggable", "true");
	
	g_EsoSkillDragData.isDragging = false;
	UpdateEsoSkillBarData();
}


function RemoveSkillBarAbility(abilityId, skillBar)
{
	if (skillBar == null)
	{
		RemoveSkillBarAbility(abilityId, 1);
		RemoveSkillBarAbility(abilityId, 2);
		return;
	}
	
	var skillBars1 = $("#esovsSkillBar" + skillBar).find(".esovsSkillBarIcon[skillid='" + abilityId + "']");
	var skillBars2 = $("#esovsSkillBar" + skillBar).find(".esovsSkillBarIcon[origskillid='" + abilityId + "']");
	
	skillBars1.attr("skillid", "0");
	skillBars1.attr("origskillid", "0");
	skillBars1.attr("src", "");
	skillBars1.attr("draggable", "true");
	
	skillBars2.attr("skillid", "0");
	skillBars2.attr("origskillid", "0");
	skillBars2.attr("src", "");
	skillBars2.attr("draggable", "true");
}


function OnSkillBarDragEnd(e)
{
	g_EsoSkillDragData.isDragging = false;
	
	if (g_EsoSkillDragData.fromSkillBar && !g_EsoSkillDragData.wasDropped) 
	{
		RemoveSkillBarAbility(g_EsoSkillDragData.abilityId, g_EsoSkillDragData.skillBar );
		UpdateEsoSkillBarData();		
	}
}


function UpdateEsoSkillBarData()
{
	g_EsoSkillBarData = [];
	g_EsoSkillBarData[0] = [];
	g_EsoSkillBarData[1] = [];
	
	UpdateEsoSkillBarSkill(1, 1);
	UpdateEsoSkillBarSkill(1, 2);
	UpdateEsoSkillBarSkill(1, 3);
	UpdateEsoSkillBarSkill(1, 4);
	UpdateEsoSkillBarSkill(1, 5);
	UpdateEsoSkillBarSkill(1, 6);
	
	UpdateEsoSkillBarSkill(2, 1);
	UpdateEsoSkillBarSkill(2, 2);
	UpdateEsoSkillBarSkill(2, 3);
	UpdateEsoSkillBarSkill(2, 4);
	UpdateEsoSkillBarSkill(2, 5);
	UpdateEsoSkillBarSkill(2, 6);
	
	if (g_EsoSkillUpdateEnable)	$(document).trigger("EsoSkillBarUpdate");
}


function UpdateEsoSkillBarSkill(skillBar, skillIndex)
{
	var iconElement = $("#esovsSkillBar" + skillBar).find(".esovsSkillBarIcon[skillindex='" + skillIndex + "']");
	var skillId = iconElement.attr("skillid");
	
	var newData = {};
	
	if (skillId == 0)
	{
		newData.skillId = skillId;
	}
	else
	{
		newData.skillId = skillId;
		newData.origSkillId =  iconElement.attr("origskillid");;
		newData.skillType = skillIndex == 6 ? "Ultimate" : "Active";
		newData.skillDesc = GetEsoCurrentSkillDescription(skillId);
	}
	
	g_EsoSkillBarData[skillBar-1][skillIndex-1] = newData;
}


function GetEsoCurrentSkillDescription(abilityId)
{
	 return $(".esovsAbilityBlockDesc[skillid='" + abilityId + "']").text();
}


function UpdateEsoSkillTotalPoints()
{
	var element = $("#esovsSkillPoints");
	element.text(g_EsoSkillPointsUsed);
	
	if (g_EsoSkillUpdateEnable)	$(document).trigger("EsoSkillUpdate");
}


function OnEsoSkillReset(e)
{
	g_EsoSkillPassiveData = {};
	g_EsoSkillActiveData = {};
	g_EsoSkillPointsUsed = 0;
	
	g_EsoSkillUpdateEnable = false;
	
	$(".esovsSkillBarIcon").attr("skillid", "0").
		attr("src", "").
		attr("draggable", "false").
		attr("origskillid", "0");
	
	$(".esovsSkillContentBlock").children(".esovsAbilityBlock").each(function(i, e) {
		var selectBlock = $(this).next(".esovsAbilityBlockList").children(".esovsAbilityBlock").eq(1);
		var displayBlock = $(this);
		var passiveIconDisplayBlock = displayBlock.children(".esovsAbilityBlockPassiveIcon");
		var iconDisplayBlock = displayBlock.children(".esovsAbilityBlockIcon");
		var titleDisplayBlock = displayBlock.children(".esovsAbilityBlockTitle");
		var skillId = selectBlock.attr("skillid");

		displayBlock.addClass('esovsAbilityBlockNotPurchase');
		iconDisplayBlock.attr("draggable", "false");
		displayBlock.attr("skillid", skillId);
	
		iconDisplayBlock.html(selectBlock.children(".esovsAbilityBlockIcon").html());
		titleDisplayBlock.html(selectBlock.children(".esovsAbilityBlockTitle").html());
		passiveIconDisplayBlock.html(selectBlock.children(".esovsAbilityBlockPassiveIcon").html());
	});
	
	g_EsoSkillUpdateEnable = true;
	
	UpdateEsoSkillBarData();
	UpdateEsoSkillTotalPoints();
}


function OnEsoSkillLinePurchaseAll()
{
	var skillElements = $(this).parent().children(".esovsAbilityBlock");
	
	g_EsoSkillUpdateEnable = false;
	
	skillElements.each(function() {
		var skillId = $(this).attr("skillid");
		var lastSkill = $(this).next(".esovsAbilityBlockList").children(".esovsAbilityBlock").last();
		var lastSkillId = lastSkill.attr("skillid");
		PurchaseEsoSkill(lastSkillId);
	});
	
	g_EsoSkillUpdateEnable = true;
	UpdateEsoSkillTotalPoints();
}


function OnEsoSkillLineResetAll()
{
	var skillElements = $(this).parent().children(".esovsAbilityBlock").not(".esovsAbilityBlockNotPurchase");
	
	g_EsoSkillUpdateEnable = false;
	
	skillElements.each(function() {
		var skillId = $(this).attr("skillid");
		ResetEsoPurchasedSkill(skillId);
	});
	
	g_EsoSkillUpdateEnable = true;
	UpdateEsoSkillTotalPoints();
}


function esovsOnDocReady()
{
	$('.esovsSkillTypeTitle').click(OnEsoSkillTypeTitleClick);
	$('.esovsSkillLineTitle').click(OnEsoSkillLineTitleClick);
	
	$('.esovsAbilityBlock').click(OnEsoSkillBlockClick);
	$('.esovsAbilityBlockPlus').click(OnEsoSkillBlockPlusClick);
	$('.esovsAbilityBlockPlusSelect').click(OnEsoSkillBlockPlusSelectClick);
	$('.esovsAbilityBlockMinusSelect').click(OnEsoSkillBlockMinusSelectClick);
	
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
	$(".esovsSkillBarIcon").hover(OnHoverEsoSkillBarIcon, OnLeaveEsoSkillBarIcon);
	
	$(".esovsAbilityBlockSelect").click(OnAbilityBlockPurchase);
	
	$(".esovsSkillBar").click(OnSkillBarSelect)
	
	$(".esovsAbilityBlockIcon").on('dragstart', OnAbilityDragStart);
	$(".esovsSkillBarIcon").on('dragstart', OnSkillBarDragStart);
	$(".esovsSkillBarIcon").on('dragover', OnSkillBarDragOver);
	$(".esovsSkillBarIcon").on('drop', OnSkillBarDrop);
	$(document).on('dragend', OnSkillBarDragEnd);
	
	$("#esovsSkillReset").click(OnEsoSkillReset);
	
	$(".esovsSkillLinePurchaseAll").click(OnEsoSkillLinePurchaseAll);
	$(".esovsSkillLineResetAll").click(OnEsoSkillLineResetAll);
	
	var highlightSkill = $(".esovsSearchHighlight");
	
	if (highlightSkill.length == 0)
		$(".esovsAbilityBlock").first().trigger('click');
	else
		highlightSkill.trigger('click');
	
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
}


$( document ).ready(esovsOnDocReady);