window.g_LastSkillId = 0;
window.g_LastSkillInputValues = {};
window.MAX_SKILL_COEF = 6;

window.g_SkillDisplayType = "summary";
window.g_EsoSkillSearchText = "";
window.g_EsoSkillSearchLastIndex = -1;

window.g_EsoSkillDragData = {};

window.g_EsoSkillUpdateEnable = true;

window.g_EsoSkillIsMobile = false;


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


window.RAWDATA_KEYS = 
[
 		"displayId",
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

window.RAWDATA_IGNORE_KEYS =
{
		"id" : true,
		"texture" : true,
};

window.ROMAN_NUMERALS = 
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


window.ESO_FREE_PASSIVES = {
		78219 : 1,
		74580 : 1,
		45542 : 1,
		47276 : 1,
		47288 : 1,
		46727 : 1,
		44590 : 1,
		47282 : 1,
		46758 : 1,
		44625 : 1,
		36582 : 1,
		36247 : 1,
		36588 : 1,
		35965 : 1,
		36312 : 1,
		36063 : 1,
		36626 : 1,
		33293 : 1,
		84680 : 1,
		36008 : 1,
		
		43056 : 1,
		//41920 : 1,  // Bat Swarm not free?
		42358 : 1,
		
		103632 : 1, // Update 18
		103793 : 1,
};


window.EsoConvertDescToHTML = function(desc)
{
	return EsoConvertDescToHTMLClass(desc, "esovsWhite");
}


window.EsoConvertDescToHTMLClass = function(desc, className)
{
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}([^|]*)\|r/g, '<div class="' + className + '">$1</div>');
	newDesc = newDesc.replace(/\n/g, '<br />');
	return newDesc;
}


window.EsoConvertDescToText = function(desc)
{
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}([^|]*)\|r/g, '$1');
	//newDesc = newDesc.replace(/\n/g, '<br />');
	return newDesc;
}


window.GetRomanNumeral = function(value)
{
	if (value <= 0) return '';
	
	var roman = ROMAN_NUMERALS[value]
	if (roman != null) return roman;
	
	return value.tostring();
}


window.GetEsoSkillTooltipHtml = function(skillData)
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
	var duration = Math.floor(GetEsoSkillDuration(abilityId, null) / 100) / 10;
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
	
	if (angleDistance > 0) area = (radius) + " x " + (angleDistance/50) + " meters"
		
	output += "<div class='esovsSkillTooltipTitle'>" + safeName + rankStr + "</div>\n";
	output += "<img src='//esolog.uesp.net/resources/skill_divider.png' class='esovsSkillTooltipDivider' />";
	
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
			output += "<div class='esovsSkillTooltipValue' id='esovsSkillTooltipDuration'>" + duration + " seconds</div>";
			output += "<div class='esovsSkillTooltipName'>Duration</div>";			
		}
		
		if (cost != '')
		{
			output += "<div class='esovsSkillTooltipValue " + costClass + "' id='esovsSkillTooltipCost'>" + costStr + "</div>";
			output += "<div class='esovsSkillTooltipName'>Cost</div>";			
		}
		
		output += "<img src='//esolog.uesp.net/resources/skill_divider.png' class='esovsSkillTooltipDivider' />";
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


window.EsoShowPopupSkillTooltip = function(skillData, parent)
{
	var popupElement = $("#esovsPopupSkillTooltip");
	
	if (g_EsoSkillDragData.isDragging || $(".ui-draggable-dragging").length > 0)
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


window.AdjustEsoSkillPopupTooltipPosition = function (tooltip, parent)
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


window.EsoViewSkillShowTooltip = function(skillData)
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


window.OnEsoSkillBlockClickMobile = function(event)
{
	var skillId = $(this).attr('skillid');
	if (skillId == null || skillId == "") return;
	
	var skillData = g_SkillsData[skillId];
	
	EsoViewSkillShowTooltip(skillData);
	EsoShowPopupSkillTooltip(skillData, $(this)[0]);
	
	UpdateEsoSkillTooltipDescription();
	UpdateEsoSkillTooltipCost();
	UpdateEsoSkillRawData();
	UpdateEsoSkillCoefData();
	UpdateSkillLink();
}


window.OnEsoSkillBlockClick = function(event)
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


window.EsoSkillShowSkillLine = function(skillLine)
{
	var id = skillLine.replace(/[ '"]/g, '_');
	
	$(".esovsSkillContentBlock:visible").hide();
	$("#" + id).show();
}


window.OnEsoSkillTypeTitleClick = function (event, noUpdate)
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


window.OnEsoSkillLineTitleClick = function (event, noUpdate)
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


window.OnEsoSkillBlockPlusClick = function (event)
{
	$(this).parent().next('.esovsAbilityBlockList').slideToggle();
}


window.OnEsoSkillBlockPlusSelectClick = function (e)
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


window.OnEsoSkillBlockMinusSelectClick = function (event)
{
	$(this).parent().next('.esovsAbilityBlockList').slideToggle();
}


window.GetEsoSkillInputValues = function ()
{
	var magicka = parseInt($('#esovsInputMagicka').val());
	var stamina = parseInt($('#esovsInputStamina').val());
	var health = parseInt($('#esovsInputHealth').val());
	var spellDamage = parseInt($('#esovsInputSpellDamage').val());
	var weaponDamage = parseInt($('#esovsInputWeaponDamage').val());
	var level = ParseEsoLevel($('#esovsInputLevel').val());
	
	if (isNaN(health)) health = 20000;
	if (isNaN(magicka)) magicka = 20000;
	if (isNaN(stamina)) stamina = 20000;
	if (isNaN(spellDamage)) spellDamage = 2000;
	if (isNaN(weaponDamage)) weaponDamage = 2000;
	if (isNaN(level)) level = 66;
	
	g_LastSkillInputValues = { Magicka: magicka,
			 Stamina: stamina,
			 Health: health,
			 SpellDamage: spellDamage,
			 WeaponDamage: weaponDamage,
			 MaxStat: Math.max(stamina, magicka),
			 MaxDamage: Math.max(spellDamage, weaponDamage),
			 EffectiveLevel: level,
			 AssassinSkills: 0,
			 FightersGuildSkills: 0,
			 DraconicPowerSkills: 0,
			 ShadowSkills: 0,
			 SiphoningSkills: 0,
			 SorcererSkills: 0,
			 MagesGuildSkills: 0,
			 SupportSkills: 0,
			 AnimalCompanionSkills: 0,
			 GreenBalanceSkills: 0,
			 WintersEmbraceSkills: 0,
		};
	
	return g_LastSkillInputValues; 
}


window.IsEsoSkillParameterDot = function (skillData, valueIndex)
{
	if (skillData.baseName == "Drain Essence" && valueIndex == 1) return true;
	return false;
}


window.ComputeEsoSkillValue = function (values, type, a, b, c, coefDesc, valueIndex, skillData)
{
	var value = 0;
	var SpellDamage = values.SpellDamage;
	var WeaponDamage = values.WeaponDamage;
	var MaxDamage = values.MaxDamage;
	var matchRegex = new RegExp("\\|c[a-fA-F0-9]{6}\\$" + valueIndex + "\\|r ([A-Za-z]+) Damage( over| each| every| to a target enemy over|)", "i");
	var matchResults = coefDesc.match(matchRegex);
	var damageType = "base";
	var isDot = false;
	var skillLine = skillData['skillLine'].toLowerCase();
	var skillWeaponValues = null;
	var skillSpellValues = null;
	var typeWeaponValues = null;
	var typeSpellValues = null;
	var SpellDamageType = [];
	var WeaponDamageType = [];
	var includeSpellRawOutput = 0;
	var includeWeaponRawOutput = 0;
	
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	
	if (matchResults != null && matchResults[1] != null) damageType = matchResults[1].toLowerCase();
	if (matchResults != null && matchResults[2] != null && matchResults[2] != "") isDot = true;
	if (damageType == "frost") damageType = "cold";
	
	if (IsEsoSkillParameterDot(skillData, valueIndex)) isDot = true;
	
	//EsoSkillLog("ComputeEsoSkillValue", skillData.name, valueIndex, damageType, isDot, values.useMaelstromDamage);
	
	if (values.SkillWeaponDamage == null || values.SkillSpellDamage == null)
	{
	}
	else if (skillData['classType'] != "")
	{
		skillWeaponValues = values.SkillWeaponDamage['Class'];
		skillSpellValues  = values.SkillSpellDamage['Class'];
		SpellDamageType.push("Class");
		WeaponDamageType.push("Class");
	}
	else
	{
		skillWeaponValues = values.SkillWeaponDamage;
		skillSpellValues  = values.SkillSpellDamage;
	}
	
	if (skillWeaponValues == null || skillSpellValues == null)
	{
	}
	else if (skillData['castTime'] > 0 || skillData['channelTime'] > 0)
	{
		skillWeaponValues = skillWeaponValues['Channel'];
		skillSpellValues  = skillSpellValues['Channel'];
		SpellDamageType.push("Channel/Cast Time");
		WeaponDamageType.push("Channel/Cast Time");
	}
	
	if (values.useMaelstromDamage && isDot && skillWeaponValues != null && skillSpellValues != null)
	{
		skillWeaponValues = skillWeaponValues['Maelstrom'];
		skillSpellValues  = skillSpellValues['Maelstrom'];
		SpellDamageType.push("Maelstrom");
		WeaponDamageType.push("Maelstrom");
	}
	
	if (skillWeaponValues != null) 
	{
		typeWeaponValues = skillWeaponValues['base'];
		
		if (skillWeaponValues[skillLine] != null) 
		{
			typeWeaponValues = skillWeaponValues[skillLine];
			WeaponDamageType.push(skillLine);
		}
		
		WeaponDamage = typeWeaponValues['base'];

		if (typeWeaponValues[damageType] != null)
		{
			WeaponDamage = typeWeaponValues[damageType];
			if (damageType != 'base') WeaponDamageType.push(damageType);
		}
		
		if (WeaponDamage == null) WeaponDamage = values.WeaponDamage;
	}
	
	if (skillSpellValues != null) 
	{
		typeSpellValues = skillSpellValues['base'];
		
		if (skillSpellValues[skillLine] != null) 
		{
			typeSpellValues = skillSpellValues[skillLine];
			SpellDamageType.push(skillLine);
		}
		
		SpellDamage = typeSpellValues['base'];

		if (typeSpellValues[damageType] != null)
		{
			SpellDamage = typeSpellValues[damageType];
			if (damageType != 'base') SpellDamageType.push(damageType);
		}
		
		if (SpellDamage == null) SpellDamage = values.SpellDamage;
	}
	
	MaxDamage = Math.max(SpellDamage, WeaponDamage);
	
	if (SpellDamage != values.SpellDamage) includeSpellRawOutput = 1;
	if (WeaponDamage != values.WeaponDamage) includeWeaponRawOutput = 1;
	
	a = parseFloat(a);
	b = parseFloat(b);
	c = parseFloat(c);

	if (type == -2) // Health
	{
		value = a * values.Health + c;
		
			// Special case for equilibrim and morphs
		if (skillData.baseName == "Equilibrium" && valueIndex == 1 && g_LastSkillInputValues != null && g_LastSkillInputValues.SkillLineCost != null && g_LastSkillInputValues.SkillLineCost.Mages_Guild_Cost != 0)
		{
			skillData.rawOutput["$1 Health Cost"] = "" + Math.round(value) + " Base + " + (g_LastSkillInputValues.SkillLineCost.Mages_Guild_Cost*100) + "% Mages Guild Cost";
			value = value * (1 + g_LastSkillInputValues.SkillLineCost.Mages_Guild_Cost);
		}
	}
	else if (type == 0) // Magicka
	{
		value = a * values.Magicka + b * SpellDamage + c;
		++includeSpellRawOutput;
	}
	else if (type == 6) // Stamina
	{
		value = a * values.Stamina + b * WeaponDamage + c;
		++includeWeaponRawOutput;
	}
	else if (type == 10) // Ultimate
	{
		value = a * values.MaxStat + b * MaxDamage + c;
		++includeSpellRawOutput;
		++includeWeaponRawOutput;
	}
	else if (type == -50) // Ultimate Soul Tether
	{
		value = a * values.MaxStat + b * SpellDamage + c;
		++includeSpellRawOutput;
	}
	else if (type == -56) // Spell + Weapon Damage
	{
		value = a * SpellDamage + b * WeaponDamage + c;
		++includeSpellRawOutput;
		++includeWeaponRawOutput;
	}
	else if (type == -57) // Assassination Skills Slotted
	{
		value = a * values.AssassinSkills;
	}
	else if (type == -58) // Fighters Guild Skills Slotted
	{
		value = a * values.FightersGuildSkills;
	}
	else if (type == -59)
	{
		value = a * values.DraconicPowerSkills;
	}
	else if (type == -60)
	{
		value = a * values.ShadowSkills;
	}
	else if (type == -61)
	{
		value = a * values.SiphoningSkills;
	}
	else if (type == -62)
	{
		value = a * values.SorcererSkills;
	}
	else if (type == -63)
	{
		value = a * values.MagesGuildSkills;
	}
	else if (type == -64)
	{
		value = a * values.SupportSkills;
	}
	else if (type == -65)
	{
		value = a * values.AnimalCompanionSkills;
	}
	else if (type == -66)
	{
		value = a * values.GreenBalanceSkills;
	}
	else if (type == -67)
	{
		value = a * values.WintersEmbraceSkills;
	}
	else if (type == -68)
	{
		value = a * values.Magicka;
		maxValue = b * values.Health;
		if (value > maxValue) value = maxValue;
	}
	else if (type == -51)
	{
		if (values.LightArmor == null) 
		{
			if (c == 0)	return '(' + a + ' * LIGHTARMOR)';
			return '(' + c + ' + ' + a + ' * LIGHTARMOR)';
		}
		value = a * values.LightArmor + c;
	}
	else if (type == -52)
	{
		if (values.MediumArmor == null) 
		{
			if (c == 0)	return '(' + a + ' * MEDIUMARMOR)';
			return '(' + c + ' + ' + a + ' * MEDIUMARMOR)';
		}
		
		value = a * values.MediumArmor + c;
	}
	else if (type == -53)
	{
		if (values.HeavyArmor == null) 
		{
			if (c == 0)	return '(' + a + ' * HEAVYARMOR)';
			return '(' + c + ' + ' + a + ' * HEAVYARMOR)';
		}
		
		value = a * values.HeavyArmor + c;
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
	
	if (includeSpellRawOutput  >= 2) skillData.rawOutput["$" + valueIndex + " Spell Damage Used"] = "" + SpellDamage + " " + SpellDamageType.join("+") + "";
	if (includeWeaponRawOutput >= 2) skillData.rawOutput["$" + valueIndex + " Weapon Damage Used"] = "" + WeaponDamage + " " + WeaponDamageType.join("+") + "";
	
	value = Math.round(value);
	
	if (value < 0) return 0;
	return value;
}


window.IsEsoSkillValidForMaelstromDWEnchant = function (skillData)
{
	if (skillData.baseName == "Soul Strike") return true;
	if (skillData.baseName == "Rapid Fire") return true;
	
	if (skillData.baseName == "Twin Slashes") return true;
	if (skillData.baseName == "Poison Arrow") return true;
	if (skillData.baseName == "Searing Strike") return true;
	if (skillData.name == "Agony") return true;
	if (skillData.name == "Prolonged Suffering") return true;
	if (skillData.baseName == "Cripple") return true;
	if (skillData.baseName == "Trap Beast") return true;
	
	if (skillData.baseName == "Sun Fire") return true;
	if (skillData.baseName == "Soul Trap") return true;
	if (skillData.baseName == "Radiant Destruction") return true;
	if (skillData.baseName == "Destructive Touch") return true;
	if (skillData.baseName == "Entropy") return true;
	if (skillData.name == "Scalding Rune") return true;
	if (skillData.baseName == "Inner Fire") return true;
	
	if (skillData.baseName == "Drain Essence") return true;
	
		//Ultimates
	//Rapid Fire (Toxic Barrage / Ballista)
	//Soul Strike (Soul Assault / Shatter Soul)
	
		//Stamina
	//Searing Strike (Venomous Claw / Burning Embers)
	//Agony (Prolonged Suffering)
	//Cripple (Debilitate / Crippling Grasp)
	//Twin Slashes (Rending Slashes / Blood Craze)
	//Poison Arrow (Venom Arrow / Poison Injection)
	//Trap Beast (Rearming Trap / Lightweight Beast Trap)
	
		//Magicka
	//Sun Fire (Vampire's Bane / )
	//Radiant Destruction? (Radiant Glory / Radiant Oppression)
	//Destructive Touch (Destructive Clench / Destructive Reach)
	//Entropy (Degeneration / Structured Entropy)
	//Scalding Rune
	//Inner Fire? (Inner Rage / Inner Beast)
	
		//AoE effects that might work?
	//Soul Trap? (Soul Splitting Trap / Consuming Trap)
	//Reflective Light?
	
	return false;
}


ESO_SKILL_DURATION_MATCHINDEXES = {
		"Veiled Strike": [ 0 ],
		"Surprise Attack": [ 0, 1 ],
		"Concealed Weapon": [ 0 ],
		"Shadow Cloak": [ 0 ],
		"Shadowy Disguise": [ 0 ],
		"Dark Cloak": [ 0 ],
		"Aspect of Terror": [ 0 ],
		"Mass Hysteria": [ 0 ],
		"Manifestation of Terror": [ 0 ],
		"Summon Shade": [ 0 ],
		"Dark Shade": [ 0 ],
		"Shadow Image": [ 0 ],
};


window.UpdateEsoSkillDurationDescription = function(skillData, coefDesc, inputValues)
{
	var modDuration = 0;
	var newDesc = coefDesc;
	
	if (inputValues == null) inputValues = g_LastSkillInputValues;
	
	if (inputValues.SkillDuration != null && inputValues.SkillDuration[skillData.baseName] != null) 
	{
		modDuration = +inputValues.SkillDuration[skillData.baseName];
	}
	
	if (modDuration == 0) return newDesc;
	
	var durationData = ESO_SKILL_DURATION_MATCHINDEXES[skillData.name]
	if (durationData == null) return newDesc;
	
	for (var i = 0; i < durationData.length; i++) 
	{
		var durationIndex = durationData[i] + 1;
		var matchIndex = 0;
		
		newDesc = newDesc.replace(/(\|c[a-fA-F0-9]{6})([0-9\.]+)(\|r)( seconds| minutes| minute)/gi, function(match, p1, p2, p3, p4, offset, string)
				{
					++matchIndex; 
					if (durationIndex != matchIndex) return p1 + p2 + p3 + p4;
					
					var newDuration = Math.floor(parseFloat(p2) * (1 + modDuration)*10)/10;

					skillData.rawOutput["Tooltip Duration #" + matchIndex] = "" + p2 + " Base x " + Math.floor(modDuration*100) + "% = " + newDuration + p4;
					
					return p1 + newDuration + p3 + p4;
				});
	}
	
	return newDesc;
}


window.GetEsoSkillDescription = function(skillId, inputValues, useHtml, noEffectLines, outputRaw)
{
	var output = "";
	var skillData = g_SkillsData[skillId];
	if (skillData == null) return "";
	
	var coefDesc = skillData['coefDescription'];
	
	if (inputValues == null) inputValues = GetEsoSkillInputValues()
	inputValues.useMaelstromDamage = false;
	
	if (IsEsoSkillValidForMaelstromDWEnchant(skillData))
	{
		inputValues.useMaelstromDamage = true;
		//if (skillData.rawOutput != null) skillData.rawOutput["Maelstrom DW Enchant"] = "+" + inputValues.Damage.MaelstromDamage + " Weapon/Spell Damage";
	}
	
	if (coefDesc == null || coefDesc == "") 
	{
		coefDesc = skillData.description;
	}
	else
	{
		for (var i = 1; i <= MAX_SKILL_COEF; ++i)
		{
			var type  = skillData['type' + i];
			if (type == -1) continue;
			
			var a = skillData['a' + i];
			var b = skillData['b' + i];
			var c = skillData['c' + i];
			var srcString = "$" + i;
						
			var value = ComputeEsoSkillValue(inputValues, type, a, b, c, coefDesc, i, skillData);
			coefDesc = coefDesc.replace(srcString, value);
		}
	}
	
	coefDesc = UpdateEsoSkillDamageDescription(skillData, coefDesc, inputValues);
	coefDesc = UpdateEsoSkillHealingDescription(skillData, coefDesc, inputValues);
	coefDesc = UpdateEsoSkillDamageShieldDescription(skillData, coefDesc, inputValues);	
	coefDesc = UpdateEsoSkillDurationDescription(skillData, coefDesc, inputValues);
	
	if (useHtml)
	{
		output = EsoConvertDescToHTML(coefDesc);
	}
	else
	{
		var effectLines = skillData['effectLines'];
		if (effectLines != "" && noEffectLines !== true) coefDesc += " <div class='esovsAbilityBlockEffectLines'>" + effectLines + "</div>";
		
		if (outputRaw !== true) 
			output = EsoConvertDescToText(coefDesc);
		else
			output = coefDesc;
	}
	
	skillData.lastDesc = output;
	return output;
}


ESO_SKILL_DAMAGESHIELDMATCHES = 
[
		{
			type: "flat",
			match: /( a damage shield for you and nearby allies that absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /( a damage shield for you and your pets that absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(You also gain a damage shield that absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(You gain a damage shield after the attack, absorbing \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(Fully charged heavy frost attacks grant a damage shield that absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(Surround yourself with a net of magic negation to absorb up to \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(gaining a damage shield that absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(Invoke defensive tactics to protect yourself and nearby allies with wards that each absorb up to \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(absorbing up to \|c[a-fA-F0-9]{6})([0-9]+)(\|r Damage from the next spell projectile cast at you)/gi,
		},
		{
			type: "flat",
			match: /(with a ward to absorb \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "%",
			match: /(Surround yourself with a whirlwind of bones, absorbing damage equivalent to \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
		},
		{
			type: "%",
			match: /(absorbing damage to allies equal to \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of their Max Health)/gi,
		},
		{
			type: "%",
			match: /(Also absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
		},
		{
			type: "%",
			match: /(granting a damage shield equal to \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
		},
		{
			type: "%",
			match: /(You also gain a damage shield equal to \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
		},
		{
			type: "%",
			match: /(nearby allies gain a damage shield for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of their Max Health)/gi,
		},
];


window.UpdateEsoSkillDamageShieldDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	if (inputValues == null) return newDesc;
	if (inputValues.DamageShield == null || inputValues.DamageShield == 0) return newDesc;
	
	var rawOutput = [];
	var newRawOutput = {};
	
	for (var i = 0; i < ESO_SKILL_DAMAGESHIELDMATCHES.length; ++i)
	{
		var matchData = ESO_SKILL_DAMAGESHIELDMATCHES[i];
		
		newDesc = newDesc.replace(matchData.match, function(match, p1, p2, p3, offset, string)
		{			
			var modDamageShield = parseFloat(p2);
			
			newRawOutput = {};
			newRawOutput.baseShield = p2;
			newRawOutput.shieldBonus = inputValues.DamageShield;
			
			modDamageShield *= 1 + newRawOutput.shieldBonus;
			modDamageShield = Math.floor(modDamageShield);
			
			newRawOutput.finalShield = modDamageShield;
			rawOutput.push(newRawOutput);
			
			return p1 + modDamageShield + p3;
		});
	}
	
	for (var i = 0; i < rawOutput.length; ++i)
	{
		var rawData = rawOutput[i];
		var output = "";
		
		if (rawData.shieldBonus != null && rawData.shieldBonus != 0) output += " + " + RoundEsoSkillPercent(rawData.shieldBonus*100) + "% ";
		
		if (output == "")
			output = "" + rawData.baseShield + " (unmodified)";
		else
			output = "" + rawData.baseShield + " " + output + " = " + rawData.finalShield + " final";
		
		skillData.rawOutput["Tooltip Damage Shield " + (i+1)] = output;
	}
	
	return newDesc;
}


ESO_SKILL_HEALINGMATCHES = 
[
	{
		healId: "Done",
		match: /(healing yourself or a wounded ally for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(heals one other injured target for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you and nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you or up to 2 nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r over)/gi,
	},
	{
		healId: "Done",
		match: /(healing nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(additional \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(restoring \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(restoring \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your missing Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you and nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r every)/gi,
	},
	{
		healId: "Done",
		match: /(healing them for an additional \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",  // TODO: ?
		match: /(Each reflected spell heals you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",  // TODO: ?
		match: /(heal yourself for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",  // TODO: ?
		match: /(You also heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you and your allies in the target area for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you and allies in the area for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/gi,
	},
	{
		healId: "Done",
		match: /(healing you or up to .*? nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/gi,
	},
	{
		healId: "Done",
		match: /(instantly healing the most injured friendly target for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(The forest continues to heal you and your allies in the area for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/gi,
	},
	{
		healId: "Done",
		match: /(and your fully charged Heavy Attacks to restore \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(heals for an immediate \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you and your allies in front of you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(Each attack against the enemy restores \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		healId2: "Taken",	//TODO: Sap Essence?
		match: /(healing you and your allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/gi,
	},
	{
		healId: "Done",
		match: /(healing them for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing themselves for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing yourself and nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(that heals you and all allies in your frontal cone for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you and alllies in the area for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/gi,
	},
	{
		healId: "Done",
		match: /(Allies may activate the Healing Seed synergy to heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/gi,
	},
	{
		healId: "Done",
		match: /(heal the target for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(causing Light Attacks to restore \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(and your Heavy Attacks to restore \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(continue to receive \|c[a-fA-F0-9]{6})([0-9]+)(\|r healing)/gi,
	},
	{
		healId: "Done",
		match: /(to heal you and all allies in the area for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/gi,
	},
	{
		healId: "Done",
		match: /(healing you and them for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(Also heals you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage done)/gi,
	},
	{
		healId: "Done",
		match: /(heals for \|c[a-fA-F0-9]{6})([0-9]+)(\|r health)/gi,
	},
	{
		healId: "Done",
		match: /(causing it to heal the matriarch and up to \|c[a-fA-F0-9]{6}[0-9]+\|r other friendly targets for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(causing it to heal the winged twilight and \|c[a-fA-F0-9]{6}[0-9]+\|r other friendly target for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	//causing it to heal the winged twilight and 1 other friendly target for 3200 Health
	//causing it to heal the matriarch and up to 2 other friendly targets for 4000 Health
	
];                     


window.UpdateEsoSkillHealingDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	var isAoE = false;
	
	if (inputValues == null) return newDesc;
	if (inputValues.Healing == null) return newDesc;
	
	if ((skillData.target == "Ground" || skillData.target == "Area") && skillData.radius > 0) isAoE = true;
	
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
			
			if (isAoE && inputValues.Healing.AOE != null && inputValues.Healing.AOE != 0)
			{
				newRawOutput.aoeHeal = inputValues.Healing.AOE;
				
				modHealing *= 1 + inputValues.Healing.AOE;
				modHealing = Math.round(modHealing);
			}
			
			if (inputValues.SkillHealing != null && inputValues.SkillHealing[skillData.skillLine] != null)
			{
				modHealing *= 1 + inputValues.SkillHealing[skillData.skillLine];
				modHealing = Math.round(modHealing);
				newRawOutput.skillHealingDone = inputValues.SkillHealing[skillData.skillLine]; 
			}
			
			newRawOutput.finalHeal = modHealing;
			rawOutput.push(newRawOutput);
			
			return p1 + modHealing + p3;
		});
	}
	
	for (var i = 0; i < rawOutput.length; ++i)
	{
		var rawData = rawOutput[i];
		var output = "";
				
		if (rawData.healDone != null && rawData.healDone != 0) output += " + " + RoundEsoSkillPercent(rawData.healDone*100) + "% " + rawData.healId;
		if (rawData.aoeHeal  != null && rawData.aoeHeal  != 0) output += " + " + RoundEsoSkillPercent(rawData.aoeHeal*100) + "% AOE";
		if (rawData.skillHealingDone != null && rawData.skillHealingDone != 0) output += " + " + RoundEsoSkillPercent(rawData.skillHealingDone*100) + "% SkillLine";
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
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Magic Damage)( over| each| every| to enemies in the target area every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Magic", // Blazing Shield
		match: /(When the shield expires it explodes outward, dealing )(\|c[a-fA-F0-9]{6})([^|]*)(\|r% of the Damage)(X|)(X|)(X|)(X|)/gi,
	},
	{
		damageId: "Physical",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Physical Damage)( over| each| every| to enemies in the target area every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Flame",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Flame Damage)( over| each| every| to enemies in the target area every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Shock",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Shock Damage)( over| each| every| to enemies in the target area every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Cold",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Cold Damage)( over| each| every| to enemies in the target area every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Cold",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Frost Damage)( over| each| every| to enemies in the target area every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Poison",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Poison Damage)( over| each| every| to enemies in the target area every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Disease",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Disease Damage)( over| each| every| to enemies in the target area every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
];


window.UpdateEsoSkillDamageDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	if (inputValues == null) return newDesc;
	if (inputValues.Damage == null) return newDesc;
	
	var rawOutput = [];
	var newRawOutput = {};
	var target = "";
	var skillLineName = skillData.skillLine + ' Damage';
	
	if (skillData.target) target = skillData.target.toLowerCase();
	
	var isDot = false;
	
	if (skillData.channelTime > 0) isDot = true;
	if (inputValues.Damage.Dot == null || isNaN(inputValues.Damage.Dot)) isDot = false;
	
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	
	for (var i = 0; i < ESO_SKILL_DAMAGEMATCHES.length; ++i)
	{
		var matchData = ESO_SKILL_DAMAGEMATCHES[i];
		var thisEffectIsDot = isDot;
		
		newDesc = newDesc.replace(matchData.match, function(match, p1, p2, p3, p4, p5, p6, p7, p8, offset, string) 
		{
			if (inputValues.Damage[matchData.damageId] == null) return string;
			
			var modDamage = parseFloat(p3);
			var baseFactor = 1;
			
			newRawOutput = {};
			newRawOutput.damageId = matchData.damageId;
			newRawOutput.baseDamage = p3;
			newRawOutput.mainDamageDone = inputValues.Damage[matchData.damageId];

			if (inputValues.SkillDamage != null && inputValues.SkillDamage[skillData.baseName] != null)
			{
				baseFactor += inputValues.SkillDamage[skillData.baseName];
				newRawOutput.skillDamageDone = inputValues.SkillDamage[skillData.baseName]; 
			}
			
			if (inputValues.SkillLineDamage != null && inputValues.SkillLineDamage[skillLineName] != null)
			{
				baseFactor += inputValues.SkillLineDamage[skillLineName];
				newRawOutput.skillLineDamageDone = inputValues.SkillLineDamage[skillLineName]; 
			}
			
			//if (isDot || p1 == "additional " || p5 != "")
			if (isDot || p5 != "")
			{
				thisEffectIsDot = true;
				
				if (p5 == " over" && p7 != "" && inputValues.SkillDuration != null && inputValues.SkillDuration[skillData.baseName] != null)
				{
					var modDuration = inputValues.SkillDuration[skillData.baseName];
					var oldDuration = parseFloat(p7);
					var newDuration = oldDuration * (1 + modDuration);
					if (modDuration >= 1) newDuration = oldDuration + modDuration;
					
					var oldTicks = Math.floor(oldDuration/2) + 1;
					var newTicks = Math.floor(newDuration/2) + 1;
					
					if (oldTicks != newTicks && !isNaN(oldTicks) && !isNaN(newTicks))
					{
						modDamage = Math.round(modDamage * newTicks / oldTicks);
						newRawOutput.modDuration = Math.floor((newDuration - oldDuration)*10)/10;
						p7 = Math.floor(newDuration*10)/10;
					}
					else
					{
						p7 = Math.floor(newDuration*10)/10;
					}
				}
				
				baseFactor += +inputValues.Damage.Dot + +newRawOutput.mainDamageDone;
				newRawOutput.dotDamageDone = inputValues.Damage.Dot;
			}
			else if (inputValues.Damage.Direct != null && inputValues.Damage.Direct !== 0)
			{
				baseFactor += +inputValues.Damage.Direct + +newRawOutput.mainDamageDone;
				newRawOutput.directDamageDone = inputValues.Damage.Direct;
			}
			else
			{
				baseFactor += +newRawOutput.mainDamageDone;
			}
			
			var amountAll = 0;
			
			if (inputValues.Damage.All != null) amountAll += Math.round(inputValues.Damage.All*100)/100;
			if (inputValues.Damage.Empower != null && !thisEffectIsDot && skillData.mechanic != 10) amountAll += Math.round(inputValues.Damage.Empower*100)/100;
			
			if (inputValues.Damage.SingleTarget != null) 
			{
				if (target == "enemy") 
				{
							/* Special case for overload */
					if (skillData.baseAbilityId != 24785)
					{
						baseFactor += Math.round(inputValues.Damage.SingleTarget*100)/100;
						newRawOutput.singleTargetDamageDone = +inputValues.Damage.SingleTarget;
					}
				}
			}
			
			if (inputValues.Damage.AOE != null) 
			{
				if (target == "area" || target == "cone" || target == "self" || target == "ground")
				{
							/* Special case for overload */
					if (skillData.baseAbilityId != 24785)
					{
						baseFactor += Math.round(inputValues.Damage.AOE*100)/100;
						newRawOutput.aoeDamageDone = +inputValues.Damage.AOE;
					}
				}
			}
			
			modDamage *= baseFactor;
			
			if (amountAll != 0)	modDamage *= 1 + amountAll;
			newRawOutput.damageDone = amountAll;
			newRawOutput.finalDamage = Math.round(modDamage);
			
			rawOutput.push(newRawOutput);
			modDamage = Math.round(modDamage);
			return p1 + p2 + modDamage + p4 + p5 + p6 + p7 + p8;
		});
	}
		
	for (var i = 0; i < rawOutput.length; ++i)
	{
		var rawData = rawOutput[i];
		var output = "";
				
		if (rawData.modDuration    != null && rawData.modDuration    != 0) output += " + " + RoundEsoSkillPercent(rawData.modDuration) + " sec ";
		if (rawData.skillLineDamageDone != null && rawData.skillLineDamageDone != 0) output += " + " + RoundEsoSkillPercent(rawData.skillLineDamageDone*100) + "% Skill Line ";
		if (rawData.skillDamageDone != null && rawData.skillDamageDone != 0) output += " + " + RoundEsoSkillPercent(rawData.skillDamageDone*100) + "% Skill ";
		if (rawData.mainDamageDone != null && rawData.mainDamageDone != 0) output += " + " + RoundEsoSkillPercent(rawData.mainDamageDone*100) + "% " + rawData.damageId;
		if (rawData.aoeDamageDone  != null && rawData.aoeDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.aoeDamageDone*100) + "% AoE";
		if (rawData.singleTargetDamageDone  != null && rawData.singleTargetDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.singleTargetDamageDone*100) + "% Target";
		if (rawData.directDamageDone  != null && rawData.directDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.directDamageDone*100) + "% Direct";
		if (rawData.dotDamageDone  != null && rawData.dotDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.dotDamageDone*100) + "% DoT";
		if (rawData.damageDone     != null && rawData.damageDone     != 0) output += " + " + RoundEsoSkillPercent(rawData.damageDone*100) + "% All";
		
		if (output == "")
			output = "" + rawData.baseDamage + " " + rawData.damageId + " Damage (unmodified)";
		else
			output = "" + rawData.baseDamage + " " + rawData.damageId + " Damage " + output + " = " + rawData.finalDamage + " final";
		
		skillData.rawOutput["Tooltip Damage " + (i+1)] = output;
	}
	
	return newDesc;
}


window.RoundEsoSkillPercent = function (value)
{
	return Math.round(value*10)/10;
}


window.UpdateEsoSkillDescription = function (skillId, descElement, inputValues, useHtml)
{
	var html = GetEsoSkillDescription(skillId, inputValues, useHtml);
	descElement.html(html);
}


window.UpdateEsoSkillTooltipDescription = function ()
{
	if (g_LastSkillId <= 0) return;
	UpdateEsoSkillDescription(g_LastSkillId, $("#esovsSkillTooltipDesc"), GetEsoSkillInputValues(), true)
}


window.CreateEsoSkillLineId = function (skillLine)
{
	if (skillLine == null) return "";
	return skillLine.replace(/ /g, "_");
}


window.ComputeEsoSkillCostExtra = function (cost, level, inputValues, skillData)
{
	if (skillData == null) return cost;
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	
	var baseCost = cost;
	var mechanic = skillData.mechanic;
	var CPFactor = 1;
	var FlatCost = 0;
	var SkillFactor = 1;
	var skillLineId = CreateEsoSkillLineId(skillData.skillLine) + "_Cost";
	var skillNameId = CreateEsoSkillLineId(skillData.baseName) + "_Cost";
	
	if (mechanic == 0 && inputValues.MagickaCost != null)
	{
		if (inputValues.MagickaCost.CP    != null) CPFactor    += inputValues.MagickaCost.CP;
		if (inputValues.MagickaCost.Item  != null) FlatCost    += inputValues.MagickaCost.Item;
		if (inputValues.MagickaCost.Set   != null) SkillFactor += inputValues.MagickaCost.Set;
		if (inputValues.MagickaCost.Skill != null) SkillFactor += inputValues.MagickaCost.Skill;
		if (inputValues.MagickaCost.Buff  != null) SkillFactor += inputValues.MagickaCost.Buff;
	}
	else if (mechanic == 6 && inputValues.StaminaCost != null)
	{
		if (inputValues.StaminaCost.CP    != null) CPFactor    += inputValues.StaminaCost.CP;
		if (inputValues.StaminaCost.Item  != null) FlatCost    += inputValues.StaminaCost.Item;
		if (inputValues.StaminaCost.Set   != null) SkillFactor += inputValues.StaminaCost.Set;
		if (inputValues.StaminaCost.Skill != null) SkillFactor += inputValues.StaminaCost.Skill;
		if (inputValues.StaminaCost.Buff  != null) SkillFactor += inputValues.StaminaCost.Buff;
	}
	else if (mechanic == 10 && inputValues.UltimateCost != null)
	{
		if (inputValues.UltimateCost.CP    != null) CPFactor    += inputValues.UltimateCost.CP;
		if (inputValues.UltimateCost.Item  != null) FlatCost    += inputValues.UltimateCost.Item;
		if (inputValues.UltimateCost.Skill != null) SkillFactor += inputValues.UltimateCost.Skill;
		if (inputValues.UltimateCost.Set   != null) SkillFactor += inputValues.UltimateCost.Set;
		if (inputValues.UltimateCost.Buff  != null) SkillFactor += inputValues.UltimateCost.Buff;
	}
	
	var output = "";
	if (CPFactor != 1) output += " + " + Math.round(CPFactor*1000 - 1000)/10 + "% CP";
	if (FlatCost != 0) output += " + " + FlatCost + " Flat";
	if (SkillFactor != 1) output += " + " + Math.round(SkillFactor*1000 - 1000)/10 + "% Skill";
		
	if (inputValues.SkillLineCost != null && inputValues.SkillLineCost[skillLineId] != null && skillData.type == "Active")
	{
		var SkillLineFactor = parseFloat(inputValues.SkillLineCost[skillLineId]);
		SkillFactor += SkillLineFactor;
		
		if (SkillLineFactor != 0) output += " + " + Math.round(SkillLineFactor*1000)/10 + "% SkillLine";
	}
		/* Skill line cost modifiers generally do not affect ultimates (manually add ultimates as needed) */
	else if (inputValues.SkillLineCost != null && inputValues.SkillLineCost[skillLineId] != null && skillData.type == "Ultimate")
	{
		if (skillData.skillLine == "Vampire")
		{
			var SkillLineFactor = parseFloat(inputValues.SkillLineCost[skillLineId]);
			SkillFactor += SkillLineFactor;
		
			if (SkillLineFactor != 0) output += " + " + Math.round(SkillLineFactor*1000)/10 + "% SkillLine";
		}
	} 
	
	if (inputValues.SkillLineCost != null && inputValues.SkillLineCost[skillNameId] != null)
	{
		var SkillLineFactor = parseFloat(inputValues.SkillLineCost[skillNameId]);
		SkillFactor += SkillLineFactor;
		
		if (SkillLineFactor != 0) output += " + " + Math.round(SkillLineFactor*1000)/10 + "% SkillCost";
	}
				
	//cost = Math.floor((cost * CPFactor + FlatCost) * SkillFactor);
	cost = Math.ceil((cost * CPFactor + FlatCost) * SkillFactor);
	if (cost < 0) cost = 0;
	
	if (output == "") 
		output = " (unmodified)";
	else
		output += " = " + cost + " Final";
	
	skillData.rawOutput["Ability Cost"] = "" + baseCost + " Base " + output;
	skillData.modifiedCost = cost;
	return cost;
}


window.ComputeEsoSkillCost = function (maxCost, level, inputValues, skillData)
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


window.ComputeEsoSkillCostOld = function (maxCost, level, inputValues, skillData)
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


 window.UpdateEsoSkillTooltipCost = function()
{		
	if (g_LastSkillId <= 0) return;
	UpdateEsoSkillCost(g_LastSkillId, $("#esovsSkillTooltipCost"), GetEsoSkillInputValues());
}


window.GetEsoSkillCost = function(skillId, inputValues)
{
	var skillData = g_SkillsData[skillId];
	if (skillData == null) return "";
	
	var mechanic = skillData['mechanic'];
	if (mechanic != 0 && mechanic != 6 && mechanic != 10) return "";
	
	var passive = skillData['isPassive'];
	if (passive != 0) return "";
	
	if (inputValues == null) inputValues = g_LastSkillInputValues;
	
	var baseCost = parseInt(skillData['cost']);
	var cost = ComputeEsoSkillCost(baseCost, inputValues.EffectiveLevel, inputValues, skillData);
	
	var costStr = "" + cost + " ";
	
	if (mechanic == 0)
		costStr += "Magicka";
	else if (mechanic == 6)
		costStr += "Stamina";
	else if (mechanic == 10)
		costStr += "Ultimate";
	
	return costStr;
}


window.UpdateEsoSkillTooltipDuration = function()
{		
	if (g_LastSkillId <= 0) return;
	UpdateEsoSkillDuration(g_LastSkillId, $("#esovsSkillTooltipDuration"), GetEsoSkillInputValues());
}


window.GetEsoSkillDuration = function(skillId, inputValues)
{
	var skillData = g_SkillsData[skillId];
	if (skillData == null) return "";
	
	if (skillData.duration == 0) return 0;
	
	if (inputValues == null) inputValues = g_LastSkillInputValues;
	if (inputValues.SkillDuration == null) return skillData.duration;
	if (inputValues.SkillDuration[skillData.baseName] == null) return skillData.duration;
	
	var modDuration = +inputValues.SkillDuration[skillData.baseName];
	var newDuration = 0;
	
	if (modDuration >= 1) 
	{
		newDuration = +skillData.duration + modDuration*1000;
		skillData.rawOutput["Duration"] = "" + (Math.floor(skillData.duration/100)/10) + " Base + " + modDuration + " secs = " + (Math.floor(newDuration/100)/10) + " secs";
	}
	else 
	{
		newDuration = Math.floor(+skillData.duration * (1 + modDuration));
		skillData.rawOutput["Duration"] = "" + (Math.floor(skillData.duration/100)/10) + " Base x " + Math.floor(modDuration*100) + "% = " + (Math.floor(newDuration/100)/10) + " secs";
	}	
	
	return newDuration;
}


window.UpdateEsoSkillCost = function(skillId, costElement, inputValues)
{
	var costStr = GetEsoSkillCost(skillId, inputValues);
	
	costElement.text(costStr);
}


window.UpdateEsoSkillDuration = function(skillId, durationElement, inputValues)
{
	var duration = GetEsoSkillDuration(skillId, inputValues);
	var durationStr = "";

	if (duration > 0) durationStr = "" + Math.round(duration/100)/10 + " seconds";

	durationElement.text(durationStr);
}


window.UpdateEsoSkillCost_ForEach = function (index, element)
{
	var skillId = $(this).attr('skillid');
	if (skillId == null || skillId == '') return;
	
	UpdateEsoSkillCost(skillId, $(element), g_LastSkillInputValues);
}


window.UpdateEsoSkillDescription_ForEach = function (index, element)
{
	var skillId = $(this).attr('skillid');
	if (skillId == null || skillId == '') return;
	
	UpdateEsoSkillDescription(skillId, $(element), g_LastSkillInputValues, false);
}


window.UpdateEsoAllSkillDescription = function()
{
	var inputValues = GetEsoSkillInputValues();
	$(".esovsSkillContentBlock:visible .esovsAbilityBlockDesc").each(UpdateEsoSkillDescription_ForEach);
}


window.UpdateEsoAllSkillCost = function(onlyVisible)
{
	var inputValues = GetEsoSkillInputValues();
	
	if (onlyVisible == null) onlyVisible = true;
	
	if (onlyVisible)
		$(".esovsSkillContentBlock:visible .esovsAbilityBlockCost").each(UpdateEsoSkillCost_ForEach);
	else
		$(".esovsSkillContentBlock .esovsAbilityBlockCost").each(UpdateEsoSkillCost_ForEach);
}


window.UpdateEsoSkillRawData = function(skillId)
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
		if (key == "displayId") key = "abilityId";
		
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
		if (key == "abilityId") key = "interalAbilityId";
		
		output += "<div class='esovsRawDataRow'>";
		output += "<div class='esovsRawDataName'>" + key + "</div> ";
		output += "<div class='esovsRawDataValue'>" + value + "</div> ";
		output += "</div>";
	}
	
	rawDataElement.html(output);
}


window.GetEsoSkillCoefDataHtml = function(skillData, i)
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
	else if (type == -57) // Assassination Skills Slotted
	{
		a = Math.round(a);
		output += srcString + " = " + a + " AssassinSkills";
		typeString = "Assassination Skills Slotted";
	}
	else if (type == -58) // Fighters Guild Skills Slotted
	{
		a = Math.round(a);
		output += srcString + " = " + a + " FightersGuildSkills";
		typeString = "Fighters Guild Skills Slotted";
	}
	else if (type == -59)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " DraconicPowerSkills";
		typeString = "Draconic Power Skills Slotted";
	}
	else if (type == -60)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " ShadowSkills";
		typeString = "Shadow Skills Slotted";
	}
	else if (type == -61)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " SiphoningSkills";
		typeString = "Siphoning Skills Slotted";
	}
	else if (type == -62)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " SorcererSkills";
		typeString = "Sorcerer Skills Slotted";
	}
	else if (type == -63)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " MagesGuildSkills";
		typeString = "Mages Guild Skills Slotted";
	}
	else if (type == -64)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " SupportSkills";
		typeString = "Support Skills Slotted";
	}
	else if (type == -65)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " AnimalCompanionSkills";
		typeString = "Animal Companion Skills Slotted";
	}	
	else if (type == -66)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " GreenBalanceSkills";
		typeString = "Green Balance Skills Slotted";
	}
	else if (type == -67)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " WintersEmbraceSkills";
		typeString = "Winter's Embrace Slotted";
	}
	else if (type == -68)
	{
		b = Math.round(b * 100);
		output += srcString + " = " + a + " Magicka    (Capped at " + b + "% Health)";
		typeString = "Magicka with Health Cap";
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


window.UpdateEsoSkillCoefData = function(skillId)
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


window.UpdateEsoSkillRawDataLink = function(skillId)
{
	var linkElement = $("#esovsRawDataSkillLink");
	
	if (skillId == null) skillId = g_LastSkillId;

	if (skillId == null || skillId <= 0)
	{
		linkElement.removeAttr("href");
		return;
	}
	
	linkElement.attr("href", "//esoitem.uesp.net/viewlog.php?action=view&record=minedSkills&id=" + skillId);
}


window.ParseEsoLevel = function (level)
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


window.FormatEsoLevel = function (level)
{
	if (level <= 0 || level > 66) return level;
	if (level <= 50) return level;

	//return "v" + (level - 50);
	return "CP" + (level - 50)*10;
}


window.OnChangeEsoSkillData = function (dataName)
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
	UpdateEsoSkillTooltipDuration();
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
	UpdateSkillLink();
}


window.OnToggleSkillCoef = function (event)
{
	var object = $("#esovsSkillCoefContent");
	var isVisible = object.is(":visible");
	
	object.slideToggle();
	
	if (isVisible)
		$(this).text("Show Skill Coefficients");
	else
		$(this).text("Hide Skill Coefficients");
}


window.OnToggleRawDataCoef = function (event)
{
	var object = $("#esovsRawDataContent");
	var isVisible = object.is(":visible");
	
	object.slideToggle();
	
	if (isVisible)
		$(this).text("Show Raw Data");
	else
		$(this).text("Hide Raw Data");
}


window.FindNextEsoSkillText = function ()
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


window.SelectEsoSkillLine = function (skillType, skillLine)
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


window.DoesEsoSkillBlockExist = function (id)
{
	var objects = $(".esovsAbilityBlock[skillid='" + id + "']");
	return objects.length != 0;
}


window.IsScrolledIntoView = function ($elem)
{
    var $window = $(window);

    var docViewTop = $window.scrollTop();
    var docViewBottom = docViewTop + $window.height();

    var elemTop = $elem.offset().top;
    var elemBottom = elemTop + $elem.height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}


window.HighlightEsoSkill = function (id)
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


window.UpdateSkillLink = function ()
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


window.DoEsoSkillSearch = function (text)
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


window.OnSkillSearch = function (event)
{
	var text = $("#esovsSearchText").val().trim();
	DoEsoSkillSearch(text);
}


window.OnHoverEsoIcon = function (e)
{
	var parentBlock = $(this).parent(".esovsAbilityBlock");
	var skillid = parentBlock.attr("skillid");
	
	if (skillid == null || skillid == "") return;
	
	var skillData = g_SkillsData[parseInt(skillid)];
	EsoShowPopupSkillTooltip(skillData, $(this)[0]);
}


window.OnHoverEsoPassiveIcon = function (e)
{
	var parentBlock = $(this).parent(".esovsAbilityBlock");
	var skillid = parentBlock.attr("skillid");
	
	if (skillid == null || skillid == "") return;
	
	var parentBlockAttr = parentBlock.parents(".esovsSkillContentBlock").attr("id");
	var raceType = "";
	if (parentBlockAttr) raceType = parentBlockAttr.replace("_Skills", "").replace("_", " ");
	var learnedLevel = $(this).find(".esovsAbilityBlockIconLevel").text();
	
	var skillData = g_SkillsData[parseInt(skillid)];
	
	if (raceType != null && raceType != "") 
	{
		skillData['raceType'] = raceType;
		skillData['skillLine'] = "" + raceType + " Skills";
	}
	
	if (learnedLevel > 0) skillData['learnedLevel'] = learnedLevel;
		
	EsoShowPopupSkillTooltip(skillData, $(this)[0]);
}


window.OnLeaveEsoIcon = function (e)
{
	var popupElement = $("#esovsPopupSkillTooltip");
	popupElement.hide();
}


window.OnHoverEsoSkillBarIcon = function(e)
{
	var skillid = $(this).attr("skillid");
	if (skillid == null || skillid == "") return;
	
	var skillData = g_SkillsData[parseInt(skillid)];
	EsoShowPopupSkillTooltip(skillData, $(this)[0]);
}


window.OnLeaveEsoSkillBarIcon = function(e)
{
	var popupElement = $("#esovsPopupSkillTooltip");
	popupElement.hide();
}


window.IsEsoSkillFree = function (skillId)
{
	return ESO_FREE_PASSIVES[skillId] != null
}


window.UpdateEsoSkillPassiveData = function (origAbilityId, abilityId, rank)
{
	//EsoSkillLog("UpdateEsoSkillPassiveData", origAbilityId, abilityId, rank);
		
	rank = parseInt(rank); 
		
	if (g_EsoSkillPassiveData[origAbilityId] == null)
	{
		if (rank <= 0) return true;
		g_EsoSkillPassiveData[origAbilityId] = {};
		g_EsoSkillPassiveData[origAbilityId].rank = 0;
	}
	
	var origRank = parseInt(g_EsoSkillPassiveData[origAbilityId].rank);
	
	if (rank <= 0)
	{
		g_EsoSkillPointsUsed -= origRank;
		if (origRank >= 1 && rank < 1 && IsEsoSkillFree(origAbilityId)) g_EsoSkillPointsUsed += 1;
		
		delete g_EsoSkillPassiveData[origAbilityId];
		
		if (g_EsoSkillUpdateEnable) UpdateEsoSkillTotalPoints();
		return true;
	}
	
	g_EsoSkillPointsUsed += rank - origRank;
	if (origRank < 1 && rank >= 1 && IsEsoSkillFree(origAbilityId)) g_EsoSkillPointsUsed -= 1;
	
	g_EsoSkillPassiveData[origAbilityId].rank = rank;
	g_EsoSkillPassiveData[origAbilityId].abilityId = abilityId;
	g_EsoSkillPassiveData[origAbilityId].baseAbilityId = origAbilityId;
	g_EsoSkillPassiveData[origAbilityId].skillDesc = GetEsoCurrentSkillDescription(abilityId);
	
	if (g_EsoSkillUpdateEnable) UpdateEsoSkillTotalPoints();
	return true;
}


window.UpdateEsoSkillActiveData = function (origAbilityId, abilityId, rank, abilityType, morph)
{
	var origPoints = 0;
	var newPoints = 1;
	var isFree = IsEsoSkillFree(origAbilityId);
	
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
		if (isFree) origPoints -= 1;
	}
	
	if (rank <= 0)
	{
		g_EsoSkillPointsUsed -= origPoints;
		delete g_EsoSkillActiveData[origAbilityId];
		
		if (g_EsoSkillUpdateEnable) UpdateEsoSkillTotalPoints();
		return true;
	}
	
	if (morph > 0) ++newPoints;
	if (isFree) newPoints -= 1;
	
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


window.OnAbilityBlockPurchase = function (e)
{
	$("#esovsPopupSkillTooltip").hide();
	
	var skillId = $(this).attr("skillid");
	var parentSkillId = $(this).parent().prev(".esovsAbilityBlock").attr("skillid");
	
	if (skillId <= 0)
		ResetEsoPurchasedSkill(parentSkillId);
	else
		PurchaseEsoSkill(skillId);
	
	$(this).parent().slideUp();
}


window.OnEsoSkillIconBlockClickMobile = function (event)
{
	$("#esovsPopupSkillTooltip").hide();
	
	var $this = $(this).parent();
	var $parent = $this.parent();
	var skillId = $this.attr("skillid");
	var parentSkillId = $parent.prev(".esovsAbilityBlock").attr("skillid");
	
	if (skillId <= 0)
		ResetEsoPurchasedSkill(parentSkillId);
	else
		PurchaseEsoSkill(skillId);
	
	$parent.slideUp();
}


window.EnableEsoClassSkills = function(className)
{
	className = className.toUpperCase();
	var classElement = $(".esovsSkillTypeTitle:contains('" + className + "')");
	
	RemovePurchasedEsoClassSkills();
	
	$(".esovsSkillTypeTitle:contains('DRAGONKNIGHT')").hide();
	$(".esovsSkillTypeTitle:contains('NIGHTBLADE')").hide();
	$(".esovsSkillTypeTitle:contains('SORCERER')").hide();
	$(".esovsSkillTypeTitle:contains('TEMPLAR')").hide();
	$(".esovsSkillTypeTitle:contains('WARDEN')").hide();
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


window.PurchaseEsoSkill = function(abilityId)
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


window.ResetEsoPurchasedSkill = function(abilityId)
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


window.RemovePurchasedEsoClassSkills = function ()
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
	
	RemoveEsoClassSkillsFromPassiveData();
	
	g_EsoSkillUpdateEnable = initialUpdate;
	
	UpdateEsoSkillBarData();
	UpdateEsoSkillTotalPoints();
}


window.RemoveEsoRaceSkillsFromPassiveData = function()
{
	var deleteSkillIds = [];
	
	for (var skillId in g_EsoSkillPassiveData)
	{
		var skill = g_SkillsData[skillId]
		
		if (skill == null) continue;
		if (skill.raceType != "") deleteSkillIds.push(skillId);
	}
	
	for (var index in deleteSkillIds)
	{
		var skillId = deleteSkillIds[index];
		delete g_EsoSkillPassiveData[skillId];
	}
}


window.RemoveEsoClassSkillsFromPassiveData = function()
{
	var deletePassiveIds = [];
	var deleteActiveIds = [];
	
	for (var skillId in g_EsoSkillPassiveData)
	{
		var skill = g_SkillsData[skillId]
		
		if (skill == null) continue;
		if (skill.classType != "") deletePassiveIds.push(skillId);
	}
	
	for (var skillId in g_EsoSkillActiveData)
	{
		var skill = g_SkillsData[skillId]
		
		if (skill == null) continue;
		if (skill.classType != "") deleteActiveIds.push(skillId);
	}	
	
	for (var index in deletePassiveIds)
	{
		var skillId = deleteSkillIds[index];
		delete g_EsoSkillPassiveData[skillId];
	}
	
	for (var index in deleteActiveIds)
	{
		var skillId = deleteSkillIds[index];
		delete g_EsoSkillActiveData[skillId];
	}
}


window.RemovePurchasedEsoRaceSkills = function()
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
	
	RemoveEsoRaceSkillsFromPassiveData();	
	
	g_EsoSkillUpdateEnable = initialUpdate;
	
	UpdateEsoSkillBarData();
	UpdateEsoSkillTotalPoints();
}


window.EnableEsoRaceSkills = function(raceName)
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


window.SetEsoSkillBarSelect = function(skillBarIndex, weaponBarIndex)
{
	$(".esovsSkillBar").removeClass("esovsSkillBarHighlight");
	
	if (skillBarIndex == 1)
	{
		$(".esovsSkillBar[skillbar='1']").addClass("esovsSkillBarHighlight");
	}
	else if (skillBarIndex == 2)
	{
		$(".esovsSkillBar[skillbar='2']").addClass("esovsSkillBarHighlight");
	}
	else if (skillBarIndex == 3)
	{
		$(".esovsSkillBar[skillbar='3']").addClass("esovsSkillBarHighlight");
	}
	else if (skillBarIndex == 4)
	{
		$(".esovsSkillBar[skillbar='4']").addClass("esovsSkillBarHighlight");
	}
}


window.OnSkillBarSelect = function(e)
{
	var skillBar = $(this).attr("skillbar");
	var weaponBar = $(this).attr("activeweaponbar");
	
	SetEsoSkillBarSelect(skillBar, weaponBar);
	
	$(document).trigger("EsoSkillBarSwap", [ skillBar, weaponBar ]);
}


window.OnAbilityDragStart = function (e)
{
	//EsoSkillLog("OnAbilityDragStart", e);
	
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
	
		// Fix for Firefox not dragging?
	e.originalEvent.dataTransfer.setData('text/html', "nothing");
	e.originalEvent.dataTransfer.effectAllowed = "copy";
	//e.originalEvent.dataTransfer.dropEffect = "copy";
	
	var popupElement = $("#esovsPopupSkillTooltip");
	popupElement.hide();
	
	return true;
}


window.OnSkillBarDragStart = function (e)
{
	//EsoSkillLog("OnSkillBarDragStart", e);
	
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
	
	e.originalEvent.dataTransfer.setData('text/html',  "nothing");
	e.originalEvent.dataTransfer.effectAllowed = "copy";
	//e.originalEvent.dataTransfer.dropEffect = "copy";
	
	var popupElement = $("#esovsPopupSkillTooltip");
	popupElement.hide();
	
	return true;
}


window.OnSkillBarDragOver = function (e)
{
	var $this = $(this);
	
	if (!$this.hasClass("esovsSkillBarIcon"))
	{
		$this = $(this).find(".esovsSkillBarIcon");
		if ($this.length == 0) return true;
	}
		
	//EsoSkillLog("OnSkillBarDragOver", e, $this);
	
	var skillBar = $this.attr("skillbar");
	var skillIndex = $this.attr("skillindex");
	
	if (g_EsoSkillDragData.skillBar == skillBar && g_EsoSkillDragData.skillIndex == skillIndex) { return true; }
	
	if (skillIndex == 6 && g_EsoSkillDragData.abilityType != "Ultimate") { return true; }
	if (skillIndex  < 6 && g_EsoSkillDragData.abilityType == "Ultimate") { return true; }
	
	e.preventDefault();
	return false;
}


window.OnSkillBarDrop = function (e)
{
	var $this = $(this);
	
	if (!$this.hasClass("esovsSkillBarIcon"))
	{
		$this = $(this).find(".esovsSkillBarIcon");
		if ($this.length == 0) return false;
	}
	
	//EsoSkillLog("OnSkillBarDrop", e, $this);
		
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
	
	$this.attr("origskillid", g_EsoSkillDragData.origAbilityId);
	$this.attr("skillid", g_EsoSkillDragData.abilityId);
	$this.attr("src", g_EsoSkillDragData.iconUrl);
	$this.attr("draggable", "true");
	
	g_EsoSkillDragData.isDragging = false;
	UpdateEsoSkillBarData();
	
		// Fix for Firefox
	 e.preventDefault();
	 e.stopPropagation();
}


window.OnSkillBarRevertDraggable = function (droppableObj)
{
	//console.log("OnSkillBarRevertDraggable", $(this), droppableObj);
	
	if (droppableObj !== false) return false;
	
	var sourceBlockIcon = $(this);
	if (!sourceBlockIcon.hasClass("esovsSkillBarIcon")) return false;
	
	var sourceAbilityId = sourceBlockIcon.attr("skillid");
	var sourceOrigAbilityId = sourceBlockIcon.attr("origskillid");
	var sourceIconUrl = sourceBlockIcon.attr("src");
	var skillBar = sourceBlockIcon.attr("skillBar");
	
	if (sourceAbilityId <= 0) return false;
	
	RemoveSkillBarAbility(sourceAbilityId, skillBar );
	UpdateEsoSkillBarData();		
	
	return false;
}


window.OnSkillBarDroppableOut = function (event, ui)
{
	var $this = $(this);
	var realDraggable = $(".ui-draggable-dragging");
	var currentImage = $this.find("img");
	var oldSrc = currentImage.attr("oldsrc");
	
	realDraggable.addClass("esovsSkillDraggableBad");
	realDraggable.removeClass("esovsSkillDraggableGood");
	
	if (oldSrc != null) currentImage.attr("src", oldSrc);
	
	//console.log("OnSkillBarDroppableOut");
}


window.OnSkillBarDroppableOver = function (event, ui)
{
	var $this = $(this);
	var realDraggable = $(".ui-draggable-dragging");
	var currentImage = $this.find("img");
	var src = currentImage.attr("src");
	var newSrc = "";
	
	realDraggable.removeClass("esovsSkillDraggableBad");
	realDraggable.addClass("esovsSkillDraggableGood");
	
	if (realDraggable.hasClass("esovsAbilityBlockIcon"))
	{
		newSrc = realDraggable.find("img").attr("src");
	}
	else if (realDraggable.hasClass("esovsSkillBarIcon"))
	{
		newSrc = realDraggable.attr("src");
	}
	
	if (src == null) src == "";
	currentImage.attr("oldsrc", src);
	if (newSrc) currentImage.attr("src", newSrc);
	
	//console.log("OnSkillBarDroppableOver");
}


window.OnSkillBarDraggableStart = function (event, ui) 
{ 
	$(".ui-draggable-dragging").addClass('esovsSkillDraggableBad').addClass('esovsSkillDraggable');
	
	$("#esovsPopupSkillTooltip").hide();
	//console.log("OnSkillBarDraggableStart");
	
	$("#esovsSkillBar").find(".esovsSkillBarIcon").removeAttr("oldsrc");
}


window.OnSkillBarDroppableAccept = function (draggable)
{
	var $this = $(this);
	draggable = $(draggable);
		
	//console.log("OnSkillBarAccept", $this, draggable, realDraggable);
	
	var sourceAbilityId = -1;
	var sourceSkillIndex = -1;
	
	if (draggable.hasClass("esovsAbilityBlockIcon"))
	{
		var sourceSkill = draggable.parent(".esovsAbilityBlock");
		sourceAbilityId = sourceSkill.attr("skillid");
	}
	else if (draggable.hasClass("esovsSkillBarIcon"))
	{
		sourceAbilityId = parseInt(draggable.attr("skillid"));
	}
	else
	{
		return false;
	}
	
	if ($this.hasClass('esovsSkillBarItem'))
	{
		var barImage = $this.find("img");
		sourceSkillIndex = barImage.attr("skillindex");
	}
	else
	{
		return false;
	}
	
	var skillData = g_SkillsData[sourceAbilityId];
	
	if (skillData == null) return false;
	
	if (skillData.type == "Ultimate" && sourceSkillIndex != 6) return false;
	if (skillData.type != "Ultimate" && sourceSkillIndex == 6) return false; 
	
	return true;
}


window.OnSkillBarDroppable = function (event, ui)
{
	var $this = $(this);
	
	//console.log("OnSkillBarDroppable", $(this), ui, event);
	
	var sourceBlockIcon = $(ui.draggable);
	var sourceSkill = sourceBlockIcon.parent(".esovsAbilityBlock");
	
	var sourceAbilityId = -1;
	var sourceOrigAbilityId = -1;
	var sourceIconUrl = -1;
	var isSkillSwap = false;
	
	if (sourceSkill.length > 0) 
	{
		sourceAbilityId = sourceSkill.attr("skillid");
		sourceOrigAbilityId = sourceSkill.attr("origskillid");
		sourceIconUrl = sourceBlockIcon.find("img").attr("src");
	}
	else if (sourceBlockIcon.hasClass("esovsSkillBarIcon"))
	{
		sourceAbilityId = sourceBlockIcon.attr("skillid");
		sourceOrigAbilityId = sourceBlockIcon.attr("origskillid");
		sourceIconUrl = sourceBlockIcon.attr("src");
		isSkillSwap = true;
	}
	
	if (sourceAbilityId <= 0) return false;
	
		/* Fix for destruction skills elemental versions */
	if (g_EsoSkillDestructionData && g_EsoSkillDestructionData[sourceAbilityId] != null)
	{
		//console.log("Destruction skill swap", sourceAbilityId);
		
		if (g_EsoSkillDestructionData[sourceAbilityId][g_EsoSkillDestructionElement] != null)
		{
			sourceAbilityId = g_EsoSkillDestructionData[sourceAbilityId][g_EsoSkillDestructionElement];
			
			if (g_SkillsData[sourceAbilityId] != null)
			{
				sourceIconUrl = "//esoicons.uesp.net" + g_SkillsData[sourceAbilityId]['icon'];
				sourceIconUrl.replace("\.dds", ".png");
			}
			
			//console.log("Switched destruction skill to element", g_EsoSkillDestructionElement, sourceAbilityId);
		}
	}
	
	if (!$this.hasClass("esovsSkillBarIcon"))
	{
		$this = $(this).find(".esovsSkillBarIcon");
		if ($this.length == 0) return false;
	}
	
	var skillBar = $this.attr("skillbar");
	var skillIndex = $this.attr("skillindex");
		
	if (isSkillSwap)
	{
		var swapId = $this.attr("skillid");
		var swapOrigId = $this.attr("origskillid");
		var sourceSkillBar = sourceBlockIcon.attr("skillbar");;
		var sourceSkillIndex = sourceBlockIcon.attr("skillindex");
		
		if (sourceSkillBar == skillBar && sourceSkillIndex == skillIndex) return;
		
		if (swapId != sourceAbilityId && swapOrigId != sourceOrigAbilityId && sourceSkillBar != skillBar)
		{
			RemoveSkillBarAbility(sourceAbilityId, skillBar);
			RemoveSkillBarAbility(sourceOrigAbilityId, skillBar);
			RemoveSkillBarAbility(swapId, sourceSkillBar);
			RemoveSkillBarAbility(swapOrigId, sourceSkillBar);
		}
		
		sourceBlockIcon.attr("origskillid", swapOrigId);
		sourceBlockIcon.attr("skillid", swapId);
		sourceBlockIcon.attr("src", $this.attr("oldsrc"));
	}
	else 
	{
		RemoveSkillBarAbility(sourceAbilityId, skillBar);
		RemoveSkillBarAbility(sourceOrigAbilityId, skillBar);
	}
	
	$this.attr("origskillid", sourceOrigAbilityId);
	$this.attr("skillid", sourceAbilityId);
	$this.attr("src", sourceIconUrl);
	$this.attr("draggable", "true");
	
	$("#esovsSkillBar").find(".esovsSkillBarIcon").removeAttr("oldsrc");
	
	UpdateEsoSkillBarData();
	
		// Fix for Firefox
	event.preventDefault();
	event.stopPropagation();
}


window.RemoveSkillBarAbility = function (abilityId, skillBar)
{
	if (skillBar == null)
	{
		RemoveSkillBarAbility(abilityId, 1);
		RemoveSkillBarAbility(abilityId, 2);
		RemoveSkillBarAbility(abilityId, 3);
		RemoveSkillBarAbility(abilityId, 4);
		return;
	}
	
	var skillBars1 = $("#esovsSkillBar .esovsSkillBar[skillbar='" + skillBar + "']").find(".esovsSkillBarIcon[skillid='" + abilityId + "']");
	var skillBars2 = $("#esovsSkillBar .esovsSkillBar[skillbar='" + skillBar + "']").find(".esovsSkillBarIcon[origskillid='" + abilityId + "']");
	
	skillBars1.attr("skillid", "0");
	skillBars1.attr("origskillid", "0");
	skillBars1.attr("src", "");
	skillBars1.attr("draggable", "true");
	
	skillBars2.attr("skillid", "0");
	skillBars2.attr("origskillid", "0");
	skillBars2.attr("src", "");
	skillBars2.attr("draggable", "true");
}


window.OnSkillBarDragEnd = function (e)
{
	//EsoSkillLog("OnSkillBarDragEnd", e);
	
	g_EsoSkillDragData.isDragging = false;
	
	if (g_EsoSkillDragData.fromSkillBar && !g_EsoSkillDragData.wasDropped) 
	{
		RemoveSkillBarAbility(g_EsoSkillDragData.abilityId, g_EsoSkillDragData.skillBar );
		UpdateEsoSkillBarData();		
	}
}


window.UpdateEsoSkillBarData = function ()
{
	g_EsoSkillBarData = [];
	
	g_EsoSkillBarData[0] = [];
	g_EsoSkillBarData[1] = [];
	g_EsoSkillBarData[2] = [];
	g_EsoSkillBarData[3] = [];
	
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
	
	UpdateEsoSkillBarSkill(3, 1);
	UpdateEsoSkillBarSkill(3, 2);
	UpdateEsoSkillBarSkill(3, 3);
	UpdateEsoSkillBarSkill(3, 4);
	UpdateEsoSkillBarSkill(3, 5);
	UpdateEsoSkillBarSkill(3, 6);
	
	UpdateEsoSkillBarSkill(4, 1);
	UpdateEsoSkillBarSkill(4, 2);
	UpdateEsoSkillBarSkill(4, 3);
	UpdateEsoSkillBarSkill(4, 4);
	UpdateEsoSkillBarSkill(4, 5);
	UpdateEsoSkillBarSkill(4, 6);
	
	if (g_EsoSkillUpdateEnable)	$(document).trigger("EsoSkillBarUpdate");
}


window.UpdateEsoSkillBarSkill = function (skillBar, skillIndex)
{
	var iconElement = $("#esovsSkillBar .esovsSkillBar[skillbar='" + skillBar + "']").find(".esovsSkillBarIcon[skillindex='" + skillIndex + "']");
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


window.GetEsoCurrentSkillDescription = function (abilityId)
{
	 return $(".esovsAbilityBlockDesc[skillid='" + abilityId + "']").text();
}


window.UpdateEsoSkillTotalPoints = function ()
{
	var element = $("#esovsSkillPoints");
	element.text(g_EsoSkillPointsUsed);
	
	if (g_EsoSkillUpdateEnable)	$(document).trigger("EsoSkillUpdate");
}


window.OnEsoSkillReset = function (e)
{
	g_EsoSkillPassiveData = {};
	g_EsoSkillActiveData = {};
	g_EsoSkillPointsUsed = 0;
	
	g_EsoSkillUpdateEnable = false;
	
	$("#esovsSkillBar .esovsSkillBarIcon").attr("skillid", "0").
		attr("src", "").
		attr("draggable", "false").
		attr("origskillid", "0");
	
	$("#esovsSkillContent .esovsSkillContentBlock").children(".esovsAbilityBlock").each(function(i, e) {
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


window.OnEsoSkillLinePurchaseAll = function ()
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


window.OnEsoSkillLineResetAll = function ()
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


window.EsoSkillLog = function ()
{
	if (console == null) return;
	if (console.log == null) return;
	
	console.log.apply(console, arguments);
}


window.esovsOnDocReady = function ()
{
		/* TODO: Need better way to detect mobile view */
	if (window.skin == "minerva") g_EsoSkillIsMobile = true;
	
	$('.esovsSkillTypeTitle').click(OnEsoSkillTypeTitleClick);
	$('.esovsSkillLineTitle').click(OnEsoSkillLineTitleClick);
	
	if (g_EsoSkillIsMobile)
	{
		$('.esovsAbilityBlock').click(OnEsoSkillBlockClickMobile);
		$('.esovsAbilityBlockPlus').click(OnEsoSkillBlockPlusClick);
		$('.esovsAbilityBlockPlusSelect').click(OnEsoSkillBlockPlusSelectClick);
		$('.esovsAbilityBlockMinusSelect').click(OnEsoSkillBlockMinusSelectClick);
		$('.esovsAbilityBlockList').find('.esovsAbilityBlockIcon').click(OnEsoSkillIconBlockClickMobile);
		$('.esovsAbilityNone').click(OnAbilityBlockPurchase);
	}
	else
	{
		$('.esovsAbilityBlock').click(OnEsoSkillBlockClick);
		$('.esovsAbilityBlockPlusSelect').click(OnEsoSkillBlockPlusSelectClick);
		$('.esovsAbilityBlockMinusSelect').click(OnEsoSkillBlockMinusSelectClick);
		$('.esovsAbilityBlockPlus').click(OnEsoSkillBlockPlusClick);
		$(".esovsAbilityBlockSelect").click(OnAbilityBlockPurchase);
	}
	
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
	
	if (g_EsoSkillIsMobile)
	{
		$(".esovsAbilityBlockIcon").click(function(e) {
			setTimeout(function() {	OnHoverEsoIcon.bind(this, e); }, 250); 
			e.preventDefault(); 
			e.stopPropagation(); 
			return false; });
	
		$(".esovsAbilityBlockPassiveIcon").click(function(e) {
			setTimeout(function() { OnHoverEsoPassiveIcon.bind(this, e); }, 250);
			e.preventDefault(); 
			e.stopPropagation(); 
			return false; });
		
		$(".esovsSkillBarItem").click(function(e) { 
			setTimeout(function() { OnHoverEsoSkillBarIcon.bind(this, e); }, 250); 
			e.preventDefault(); 
			e.stopPropagation(); 
			return false; });
	}
	else
	{
		$(".esovsAbilityBlockIcon").hover(OnHoverEsoIcon, OnLeaveEsoIcon);
		$(".esovsAbilityBlockPassiveIcon").hover(OnHoverEsoPassiveIcon, OnLeaveEsoIcon);
		$(".esovsSkillBarIcon").hover(OnHoverEsoSkillBarIcon, OnLeaveEsoSkillBarIcon);
	}
		
	$(".esovsSkillBar").click(OnSkillBarSelect)
	
	//$(".esovsAbilityBlockIcon").on('dragstart', OnAbilityDragStart);
	//$(".esovsSkillBarIcon").on('dragstart', OnSkillBarDragStart);
	//$(".esovsSkillBarItem").on('dragover', OnSkillBarDragOver);
	//$(".esovsSkillBarItem").on('drop', OnSkillBarDrop);
	//$(document).on('dragend', OnSkillBarDragEnd);
	
	$(".esovsSkillContentBlock").children(".esovsAbilityBlock").children(".esovsAbilityBlockIcon").draggable({ 
			//containment: $('#esovsRightBlock'),
			//appendTo: $('#esovsRightBlock'),
			containment: false,
			appendTo: $('body'),
			helper: 'clone',
			start: OnSkillBarDraggableStart,
			classes: { },
	});
	
	$(".esovsSkillBarIcon").draggable({ 
			//containment: $('#esovsRightBlock'),
			//appendTo: $('#esovsRightBlock'),
			containment: false,
			appendTo: $('body'),
			helper: 'clone',
			revert: OnSkillBarRevertDraggable,
			start: OnSkillBarDraggableStart,
			classes: { },
	});
	
	$(".esovsSkillBarItem").droppable({ 
			drop: OnSkillBarDroppable, 
			accept: OnSkillBarDroppableAccept,
			out: OnSkillBarDroppableOut,
			over: function(event, ui) { setTimeout(OnSkillBarDroppableOver.bind(this, event, ui), 0); },
			classes: {
				//'ui-droppable-hover': 'esovsSkillAcceptHover'
			},
	});		
	
	$("#esovsSkillReset").click(OnEsoSkillReset);
	
	$(".esovsSkillLinePurchaseAll").click(OnEsoSkillLinePurchaseAll);
	$(".esovsSkillLineResetAll").click(OnEsoSkillLineResetAll);
	
	var highlightSkill = $(".esovsSearchHighlight");
	
	if (g_SkillDisplayType == "summary")
	{
		if (highlightSkill.length == 0)
			$(".esovsAbilityBlock").first().trigger('click');
		else
			highlightSkill.trigger('click');
	}
	
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
}


$( document ).ready(esovsOnDocReady);