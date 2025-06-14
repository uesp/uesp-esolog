window.g_LastSkillInputValues = {};
window.MAX_SKILL_COEF = 6;

window.g_SkillDisplayType = "summary";
window.g_EsoSkillSearchText = "";
window.g_EsoSkillSearchLastIndex = -1;

window.g_EsoSkillDragData = {};

window.g_EsoSkillUpdateEnable = true;

window.g_EsoSkillIsMobile = false;

window.g_LastSkillId = -1;
window.g_LastSkillData = null;

window.USE_V2_TOOLTIPS = true;


window.ESO_SKILL_TYPES = {
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


window.ESO_MECHANIC_TYPES = {
		0  : "",
		1  : "Magicka",
		2  : "Werewolf",
		4  : "Stamina",
		8  : "Ultimate",
		16 : "Mount Stamina",
		32 : "Health",
		64 : "Daedric",
};


window.RAWDATA_KEYS = 
[
 		"displayId",
		"name",
		"type",
		"skillTypeName",
		"cost",
		"costTime",
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
		"mechanicTime",
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
		
		152778 : 1,		// Armor bonuses/penalties (Update 29)
		150185 : 1,
		150181 : 1,
		152780 : 1,
		150184 : 1,
		
		43056 : 1,
		//41920 : 1,  	// Bat Swarm not free?
		42358 : 1,
		
		32455 : 1,		// Werewolf Transformation 1
		
		103632 : 1, 	// Update 18
		103793 : 1,
		
		32634 : 1,		// Update 20: Devour
		
		116096 : 1,		// Update 22: Volendrung
		116093 : 1,
		116094 : 1,
		117979 : 1,
		116095 : 1,
		117985 : 1,
};


window.EsoConvertDescToHTML = function(desc)
{
	//return EsoConvertDescToHTMLClass(desc, "esovsWhite");
	return EsoConvertDescToHTMLColor(desc);
}


window.EsoConvertDescToHTMLClass = function(desc, className)
{
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}\|c[a-fA-F0-9]{6}([^|]*)\|r\|r/g, '<div class="' + className + '">$1</div>'); 
	newDesc = newDesc.replace(/\|c[a-fA-F0-9]{6}([^|]*)\|r/g, '<div class="' + className + '">$1</div>');
	newDesc = newDesc.replace(/\|c[a-fA-F0-9]{6}\|([^|]*)\|r/g, '<div class="' + className + '">$1</div>');
	newDesc = newDesc.replace(/\n/g, '<br />');
	return newDesc;
}


window.EsoConvertDescToHTMLColor = function(desc)
{
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}\|c([a-fA-F0-9]{6})([^|]*)\|r\|r/g, '<div style="color:#$1; display:inline;">$2</div>'); 
	newDesc = newDesc.replace(/\|c([a-fA-F0-9]{6})([^|]*)\|r/g, '<div style="color:#$1; display:inline;">$2</div>');
	newDesc = newDesc.replace(/\|c([a-fA-F0-9]{6})\|([^|]*)\|r/g, '<div style="color:#$1; display:inline;">$2</div>');
	newDesc = newDesc.replace(/\n/g, '<br />');
	return newDesc;
}


window.EsoConvertDescToText = function(desc)
{
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}\|c[a-fA-F0-9]{6}([^|]*)\|r\|r/g, '$1');
	newDesc = newDesc.replace(/\|c[a-fA-F0-9]{6}([^|]*)\|r/g, '$1');
	newDesc = newDesc.replace(/\|c[a-fA-F0-9]{6}\|([^|]*)\|r/g, '$1');
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


window.GetEsoSkillMechanicText = function(mechanic)
{
	var text = "";
	
	if (g_SkillsVersion == '' || parseInt(g_SkillsVersion) >= 34)
	{
		if (mechanic == 1)
		{
			text = "Magicka";
		}
		else if (mechanic == 2)
		{
		}
		else if (mechanic == 4)
		{
			text = "Stamina";
		}
		else if (mechanic == 8)
		{
			text = "Ultimate";
		}
		else if (mechanic == 16)
		{
		}
		else if (mechanic == 32)
		{
			text = "Health";
		}
		else if (mechanic == 64)
		{
		}
		else if (mechanic == null)
		{
			text = "";
		}
		else 
		{
			text = "";
		}
	}
	else
	{
		if (mechanic == 0)
		{
			text = "Magicka";
		}
		else if (mechanic == 6)
		{
			text = "Stamina";
		}
		else if (mechanic == 10)
		{
			text = "Ultimate";
		}
		else if (mechanic == -2)
		{
			text = "Health";
		}
		else if (mechanic == null)
		{
			text = "";
		}
		else 
		{
			text = "";
		}
	}
	
	return text;
}


window.GetEsoSkillTooltipFormatCostClass = function(mechanic)
{
	var classStr = "";
	
	if (g_SkillsVersion == '' || parseInt(g_SkillsVersion) >= 34)
	{
		if (mechanic == 1)
		{
			classStr += "esovsMagicka";
		}
		else if (mechanic == 2)
		{
		}
		else if (mechanic == 4)
		{
			classStr += "esovsStamina";
		}
		else if (mechanic == 8)
		{
			classStr += "esovsUltimate";
		}
		else if (mechanic == 16)
		{
		}
		else if (mechanic == 32)
		{
			classStr += "esovsHealth";
		}
		else if (mechanic == 64)
		{
		}
		else if (mechanic == null)
		{
			classStr = "";
		}
		else 
		{
			classStr = "";
		}
	}
	else
	{
		if (mechanic == 0)
		{
			classStr += "esovsMagicka";
		}
		else if (mechanic == 6)
		{
			classStr += "esovsStamina";
		}
		else if (mechanic == 10)
		{
			classStr += "esovsUltimate";
		}
		else if (mechanic == -2)
		{
			classStr += "esovsHealth";
		}
		else if (mechanic == null)
		{
			classStr = "";
		}
		else 
		{
			classStr = "";
		}
	}
	
	return classStr;
}


window.GetEsoSkillTooltipFormatCostString = function(mechanic, cost, skillData)
{
	var costStr = "" + cost + " ";
		
	if (g_SkillsVersion == '' || parseInt(g_SkillsVersion) >= 34)
	{
		if (mechanic == 1)
		{
			costStr += "Magicka";
		}
		else if (mechanic == 2)
		{
			costStr += "Werewolf";
		}
		else if (mechanic == 4)
		{
			costStr += "Stamina";
		}
		else if (mechanic == 8)
		{
			costStr += "Ultimate";
		}
		else if (mechanic == 16)
		{
			costStr += "Mount Stamina";
		}
		else if (mechanic == 32)
		{
			costStr += "Health";
		}
		else if (mechanic == 64)
		{
			costStr += "Daedric";
		}
		else if (mechanic == null)
		{
			costStr = "";
		}
		else 
		{
			costStr = skillData['cost'];
		}
	}
	else
	{
		if (mechanic == 0)
		{
			costStr += "Magicka";
		}
		else if (mechanic == 6)
		{
			costStr += "Stamina";
		}
		else if (mechanic == 10)
		{
			costStr += "Ultimate";
		}
		else if (mechanic == -2)
		{
			costStr += "Health";
		}
		else if (mechanic == null)
		{
			costStr = "";
		}
		else 
		{
			costStr = skillData['cost'];
		}
	}
	
	return costStr;
}


window.CreateEsoSkillCostTooltipHtml = function(skillData)
{
	var output = "";
	
	var mechanics = skillData['mechanic'].split(',');
	var costs = skillData['cost'].split(',');
	var costTimes;
	var mechanicTimes;
	var chargeFreqs = skillData['chargeFreq'].split(',');
	
	if (skillData['costTime'] == null) 
		costTimes = costs;
	else
		costTimes = skillData['costTime'].split(',');;
		
	if (skillData['mechanicTime'] == null) 
		mechanicTimes = mechanics;
	else
		mechanicTimes = skillData['mechanicTime'].split(',');;
	
	for (var i = 0; i < mechanics.length; ++i)
	{
		var mechanic = parseInt(mechanics[i]);
		var mechanicTime = parseInt(mechanicTimes[i]);
		var cost = parseInt(costs[i]);
		var costTime = parseInt(costTimes[i]);
		var chargeFreq = parseInt(chargeFreqs[i]) / 1000;
		
		if (isNaN(cost)) cost = 0;
		if (isNaN(costTime)) costTime = 0;
		if (isNaN(mechanicTime)) mechanicTime = 0;
		if (isNaN(chargeFreq)) chargeFreq = 0;
		
		var realCost = ComputeEsoSkillCost(cost, null, null, mechanic, skillData);
		var realCostTime = ComputeEsoSkillCost(costTime, null, null, mechanicTime, skillData);
		var costStr = GetEsoSkillTooltipFormatCostString(mechanic, realCost, skillData);
		var costTimeStr = GetEsoSkillTooltipFormatCostString(mechanicTime, realCostTime, skillData);
		var costClass = GetEsoSkillTooltipFormatCostClass(mechanic);
		var costClassTime = GetEsoSkillTooltipFormatCostClass(mechanicTime);
		
		if (chargeFreq > 0 && costTime > 0)
		{
			output += "<div mechanic='" + mechanicTime + "' class='esovsSkillTooltipValue " + costClassTime + " esovsSkillTooltipCost' >" + costTimeStr + "<div class='esovsChargeFreq'>&nbsp;/ " + chargeFreq + "s</div></div>";
			output += "<div class='esovsSkillTooltipName'>Cost</div>";
		}
		else if (cost > 0)
		{
			output += "<div mechanic='" + mechanic + "' class='esovsSkillTooltipValue " + costClass + " esovsSkillTooltipCost' >" + costStr + "</div>";
			output += "<div class='esovsSkillTooltipName'>Cost</div>";
		}
		
	}
	
	return output;
}


window.GetEsoSkillTooltipHtml = function(skillData)
{
	if (skillData == null) return "";
	
	var output = "<div class='esovsSkillTooltip'>\n";
	var skillData1 = skillData;
	
	if (skillData['useCraftedDesc'] && skillData['craftAbilityId'])
	{
		var repData = g_SkillsData[skillData['craftAbilityId']];
		if (repData) skillData1 = repData;
	}
	
	var mechanic = skillData['mechanic'];
	var abilityId = skillData['abilityId'];
	var safeName = skillData['name'];
	var rank = skillData['rank'];
	var maxRank = skillData['maxRank'];
	var desc = GetEsoSkillDescription(abilityId, null, true);
	var channelTime = skillData1['channelTime'] / 1000;
	var castTime = skillData1['castTime'] / 1000;
	var radius = skillData1['radius'] / 100;
	var duration = Math.floor(GetEsoSkillDurationData(skillData1, null) / 100) / 10;
	var target = skillData1['target'];
	var angleDistance = skillData1['angleDistance'];
	var minRange = skillData1['minRange'];
	var maxRange = skillData1['maxRange'];
	var cost = skillData1['cost'];
	var radius = skillData1['radius'] / 100;
	var castTimeStr = castTime + " seconds";
	var skillType = skillData['type'];
	var learnedLevel = skillData['learnedLevel'];
	var effectLines = skillData['effectLines'];
	var skillLine = skillData['skillLine'];
	var isToggle = skillData['isToggle'];
	var area = "";
	var range = "";
	var rankStr = "";
	var realRank = rank;
	var chargeFreqMS = parseInt(skillData1['chargeFreq']);
	
	if (skillData['craftName']) safeName = skillData['craftName'];
	
	if (skillType != 'Passive')
	{
		if (realRank > 4) realRank -= 4;
		if (realRank > 4) realRank -= 4;
	}
	
	if (maxRank > 1 && realRank > 0) rankStr = " " + GetRomanNumeral(realRank);
	if (skillData['isCrafted']) rankStr = "";
	
	if (minRange > 0 && maxRange > 0)
		range = (minRange/100) + " - " + (maxRange/100) + " meters"
	else if (minRange <= 0 && maxRange > 0)
		range = (maxRange/100) + " meters"
	else if (minRange > 0 && maxRange <= 0)
		range = "Under " + (minRange/100) + " meters"
	
	if (angleDistance > 0) area = (radius) + " x " + (angleDistance/50) + " meters"
	
	output += "<div class='esovsSkillTooltipTitle'>" + safeName + rankStr + "</div>\n";
	output += "<img src='//esolog-static.uesp.net/resources/skill_divider.png' class='esovsSkillTooltipDivider' />";
	
	if (skillType != 'Passive')
	{
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
		
		if (target != null && target != '')
		{
			output += "<div class='esovsSkillTooltipValue'>" + target +"</div>";
			output += "<div class='esovsSkillTooltipName'>Target</div>";
		}
		
		if (area != null && area != '')
		{
			output += "<div class='esovsSkillTooltipValue'>" + area + "</div>";
			output += "<div class='esovsSkillTooltipName'>Area</div>";
		}
		
		if (radius > 0)
		{
			output += "<div class='esovsSkillTooltipValue'>" + radius + " meters</div>";
			output += "<div class='esovsSkillTooltipName'>Radius</div>";
		}
		
		if (range != null && range != "")
		{
			output += "<div class='esovsSkillTooltipValue'>" + range + "</div>";
			output += "<div class='esovsSkillTooltipName'>Range</div>";
		}
		
		if (duration > 0)
		{
			output += "<div class='esovsSkillTooltipValue' id='esovsSkillTooltipDuration'>" + duration + " seconds</div>";
			output += "<div class='esovsSkillTooltipName'>Duration</div>";
		}
		else if (isToggle == 1)
		{
			output += "<div class='esovsSkillTooltipValue' id='esovsSkillTooltipDuration'>Toggle</div>";
			output += "<div class='esovsSkillTooltipName'>Duration</div>";
		}
		
		if (cost != '')
		{
			output += CreateEsoSkillCostTooltipHtml(skillData1);
		}
		
		output += "<img src='//esolog-static.uesp.net/resources/skill_divider.png' class='esovsSkillTooltipDivider' />";
	}

	output += "<div id='esovsSkillTooltipDesc' class='esovsSkillTooltipDesc'>" + desc + "</div>\n";
	if (effectLines != null && effectLines != "") output += " <div class='esovsSkillTooltipEffectLines'><b>NEW EFFECT</b><br/>" + effectLines + "</div>";
	
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
	g_LastSkillData = skillData;
	element.html(GetEsoSkillTooltipHtml(skillData));
}


window.EsoUpdateSkillTooltip = function()
{
	EsoViewSkillShowTooltip(g_LastSkillData);
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
	var armor = parseInt($('#esovsInputArmor').val());
	
	if (isNaN(health)) health = 20000;
	if (isNaN(magicka)) magicka = 20000;
	if (isNaN(stamina)) stamina = 20000;
	if (isNaN(spellDamage)) spellDamage = 2000;
	if (isNaN(weaponDamage)) weaponDamage = 2000;
	if (isNaN(level)) level = 66;
	if (isNaN(armor)) armor = 11000;
	
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
			 BoneTyrantSkills: 0,
			 GraveLordSkills: 0,
			 WeaponPower: 0,
			 DotDamageDone: {},
			 ChannelDamageDone: {},
			 SpellResist: armor,
			 PhysicalResist: armor,
			 HeraldoftheTomeSkills: 0,
			 SoldierofApocryphaSkills: 0,
			 Damage: {},
			 DamageShield: 0,
			 SkillHealing: {},
			 SkillDamage: {},
			 Healing: {
				 Done: 0,
			 },
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
	var isHealing = false;
	var isAOE = false;
	var isMelee = true;
	var skillLine = skillData['skillLine'].toLowerCase();
	var skillBaseName = skillData['baseName'].toLowerCase();
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
	
	if (IsEsoSkillParameterDot(skillData, valueIndex)) isDot = true;
	
	var aoeMatchRegex = new RegExp("\\|c[a-fA-F0-9]{6}\\$" + valueIndex + "\\|r [A-Za-z]+ Damage( to enemies| to nearby enemies)", "i");
	var aoeMatchResults = coefDesc.match(aoeMatchRegex);
	if (skillData['radius'] > 0 || skillData['angleDistance'] > 0 || (aoeMatchResults != null && aoeMatchResults[1] != null)) isAOE = true;
	
	var healingMatchRegex = new RegExp("\\|c[a-fA-F0-9]{6}\\$" + valueIndex + "\\|r(?:%|) Health", "i");
	var healingMatchResults = coefDesc.match(healingMatchRegex);
	if (healingMatchResults != null) isHealing = true;
	
	if (skillData.maxRange > 700) isMelee = false;
	
	//EsoSkillLog("ComputeEsoSkillValue", skillData.name, valueIndex, damageType, isDot, values.useMaelstromDamage);
	
	 	// Order of spell/weapon checks: Class, Channel, Maelstrom, Healing, AOE, DOT, Range, Melee
	
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
	
	if (isHealing && skillWeaponValues != null && skillSpellValues != null) 
	{
		skillWeaponValues = skillWeaponValues['Healing'];
		skillSpellValues  = skillSpellValues['Healing'];
		SpellDamageType.push("Healing");
		WeaponDamageType.push("Healing");
	}
	
	if (isDot && skillWeaponValues != null && skillSpellValues != null) 
	{
		skillWeaponValues = skillWeaponValues['DOT'];
		skillSpellValues  = skillSpellValues['DOT'];
		SpellDamageType.push("DOT");
		WeaponDamageType.push("DOT");
	}
	
	if (isMelee && skillWeaponValues != null && skillSpellValues != null) 
	{
		skillWeaponValues = skillWeaponValues['Melee'];
		skillSpellValues  = skillSpellValues['Melee'];
		SpellDamageType.push("Melee");
		WeaponDamageType.push("Melee");
	}
	else if (!isMelee && skillWeaponValues != null && skillSpellValues != null) 
	{
		skillWeaponValues = skillWeaponValues['Range'];
		skillSpellValues  = skillSpellValues['Range'];
		SpellDamageType.push("Range");
		WeaponDamageType.push("Range");
	}
	
	if (skillWeaponValues != null) 
	{
		typeWeaponValues = skillWeaponValues['base'];
		
		if (skillWeaponValues[skillLine] != null) 
		{
			typeWeaponValues = skillWeaponValues[skillLine];
			WeaponDamageType.push(skillLine);
		}
		else if (skillWeaponValues[skillBaseName] != null) 
		{
			typeWeaponValues = skillWeaponValues[skillBaseName];
			WeaponDamageType.push(skillBaseName);
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
		else if (skillSpellValues[skillBaseName] != null) 
		{
			typeSpellValues = skillSpellValues[skillBaseName];
			SpellDamageType.push(skillBaseName);
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
	
	if (IsEsoSkillMechanicHealth(type)) // Health
	{
		value = a * values.Health + c;
		
			// Special case for equilibrim and morphs
		if (skillData.baseName == "Equilibrium" && valueIndex == 1 && g_LastSkillInputValues != null && g_LastSkillInputValues.SkillLineCost != null && g_LastSkillInputValues.SkillLineCost.Mages_Guild_Cost != 0)
		{
			skillData.rawOutput["$1 Health Cost"] = "" + Math.round(value) + " Base + " + (g_LastSkillInputValues.SkillLineCost.Mages_Guild_Cost*100) + "% Mages Guild Cost";
			value = value * (1 + g_LastSkillInputValues.SkillLineCost.Mages_Guild_Cost);
		}
	}
	else if (IsEsoSkillMechanicMagicka(type)) // Magicka
	{
		value = a * values.Magicka + b * SpellDamage + c;
		++includeSpellRawOutput;
	}
	else if (IsEsoSkillMechanicStamina(type)) // Stamina
	{
		value = a * values.Stamina + b * WeaponDamage + c;
		++includeWeaponRawOutput;
	}
	else if (IsEsoSkillMechanicUltimate(type)) // Ultimate
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
	else if (type == -56 || type == 4) // Spell + Weapon Damage
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
	else if (type == -69)
	{
		value = a * values.BoneTyrantSkills;
	}
	else if (type == -70)
	{
		value = a * values.GraveLordSkills;
	}
	else if (type == -71)
	{
		value = a * values.SpellDamage + b;
		maxValue = c;
		if (value > maxValue) value = maxValue;
	}	
	else if (type == -72)
	{
		value = a * values.Magicka + b * values.WeaponDamage + c;
		++includeSpellRawOutput;
		++includeWeaponRawOutput;
	}
	else if (type == -73)
	{
		var value1 = a * values.Magicka;
		var value2 = b * values.SpellDamage;
		maxValue = c;
		var halfMax = maxValue/2;
		
		if (value1 > halfMax) value1 = halfMax;
		if (value2 > halfMax) value2 = halfMax;
		
		value = value1 + value2;
		if (value > maxValue) value = maxValue;
	}	
	else if (type == -74)
	{
		value = a * values.WeaponPower + c;
	}
	else if (type == -75)
	{
		value = c;
	}
	else if (type == -76)
	{
		var value1 = Math.floor(a * values.SpellDamage) + c;
		var value2 = Math.floor(b * values.Health) + c;
		value = Math.max(value1, value2);
	}
	else if (type == -79)
	{
		var value1 = Math.floor(a * values.SpellDamage) + Math.floor(b * values.WeaponDamage);
		var value2 = Math.floor(c * values.Health);
		value = Math.max(value1, value2);
	}
	else if (type == -77)
	{
		value = Math.floor(a * Math.max(values.SpellResist, values.PhysicalResist)) + c;
	}
	else if (type == -80)
	{
		value = a * values.HeraldoftheTomeSkills;
	}
	else if (type == -81)
	{
		value = a * values.SoldierofApocryphaSkills;
	}
	else
	{
		return '?';
	}
	
	if (includeSpellRawOutput  >= 2) 
		skillData.rawOutput["$" + valueIndex + " Spell Damage Used"] = "" + SpellDamage + " " + SpellDamageType.join("+") + "";
	else
		delete skillData.rawOutput["$" + valueIndex + " Spell Damage Used"];
		
	if (includeWeaponRawOutput >= 2) 
		skillData.rawOutput["$" + valueIndex + " Weapon Damage Used"] = "" + WeaponDamage + " " + WeaponDamageType.join("+") + "";
	else
		delete skillData.rawOutput["$" + valueIndex + " Weapon Damage Used"];
	
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


	/* Index values represent the index of the duration within the tooltip you wish to have modified.
	 * 0 = First duration in toolip.  */
window.ESO_SKILL_DURATION_MATCHINDEXES = {
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
		"Stonefist" : [ 0, 1 ],
		"Stone Giant" : [ 0, 1 ],
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
					
					if (modDuration >= 1)
					{
						newDuration = Math.floor(parseFloat(p2) + modDuration);
						skillData.rawOutput["Tooltip Duration #" + matchIndex] = "" + p2 + " Base + " + modDuration + " = " + newDuration + p4;
					}
					else
					{
						newDuration = Math.floor(parseFloat(p2) * (1 + modDuration)*10)/10;
						skillData.rawOutput["Tooltip Duration #" + matchIndex] = "" + p2 + " Base x " + Math.floor(modDuration*100) + "% = " + newDuration + p4;
					}
					
					return p1 + newDuration + p3 + p4;
				});
	}
	
	return newDesc;
}


window.GetEsoSkillDescription = function(skillId, inputValues, useHtml, noEffectLines, outputRaw)
{
	if (USE_V2_TOOLTIPS && window.g_EsoSkillHasV2Tooltips && window.GetEsoSkillDescription2) return GetEsoSkillDescription2(skillId, inputValues, useHtml, noEffectLines, outputRaw);
	
	var output = "";
	var skillData = g_SkillsData[skillId];
	if (skillData == null) return "";
	
	var descHeader = skillData['descHeader'];
	var coefDesc = skillData['coefDescription'];
	if (coefDesc == "") coefDesc = skillData['description'];
	if (descHeader) coefDesc = '|cffffff' + descHeader + "|r\n" + coefDesc;
	
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
	coefDesc = UpdateEsoSkillHealingReductionDescription(skillData, coefDesc, inputValues);
	coefDesc = UpdateEsoSkillDamageShieldDescription(skillData, coefDesc, inputValues);	
	coefDesc = UpdateEsoSkillDurationDescription(skillData, coefDesc, inputValues);
	coefDesc = UpdateEsoSkillBleedDamageDescription(skillData, coefDesc, inputValues);
	coefDesc = UpdateEsoSkillElfBaneDurationDescription(skillData, coefDesc, inputValues);
	coefDesc = UpdateEsoSkillRapidStrikesDescription(skillData, coefDesc, inputValues);
	coefDesc = UpdateEsoSkillUppercutDescription(skillData, coefDesc, inputValues);
	coefDesc = UpdateEsoSkillScatterShotDescription(skillData, coefDesc, inputValues);
	coefDesc = UpdateEsoSkillVolleyDescription(skillData, coefDesc, inputValues);
	
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
			match: /( a damage shield for nearby allies that absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /( own damage absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /( a damage shield for you and your pets that absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(damage shield that absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(damage shield that absorbs up to \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(You gain a damage shield after the attack, absorbing \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(absorb up to \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
		},
		{
			type: "flat",
			match: /(absorbing up to \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
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
			match: /(nearby allies gain a damage shield for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of their Max Health)/gi,
		},
		{
			type: "%",
			match: /(damage shield that absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
		},
		{
			type: "%",
			match: /(damage shield equal to \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
		},
		{
			type: "flat",
			match: /(own damage shield absorbs \|c[a-fA-F0-9]{6})([0-9]+)(\|r damage)/gi,
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
		var matchIndex = 0;
		
		newDesc = newDesc.replace(matchData.match, function(match, p1, p2, p3, offset, string)
		{			
			var modDamageShield = parseFloat(p2);
			
				// TODO: Technically not the same as the skill coefficient index.
			matchIndex = matchIndex + 1;
			
			newRawOutput = {};
			newRawOutput.baseShield = p2;
			newRawOutput.shieldBonus = inputValues.DamageShield;
			newRawOutput.type = matchData.type;
			newRawOutput.maxShield = -1;
			
			modDamageShield *= 1 + newRawOutput.shieldBonus;
			modDamageShield = Math.floor(modDamageShield);
			
				/* Cap shield if possible */
			if (newRawOutput.type == "flat" && inputValues.Health && skillData["type" + matchIndex] == -68 && skillData["b" + matchIndex])
			{
				var maxShield = Math.floor(skillData["b" + matchIndex] * inputValues.Health);
				newRawOutput.maxShield = maxShield;
				if (modDamageShield > maxShield) modDamageShield = maxShield;
			}
			
			newRawOutput.finalShield = modDamageShield;
			rawOutput.push(newRawOutput);
			
			return p1 + modDamageShield + p3;
		});
	}
	
	for (var i = 0; i < rawOutput.length; ++i)
	{
		var rawData = rawOutput[i];
		var output = "";
		var percent = "";
		if (rawData.type == "%") percent = "%";
		
		if (rawData.shieldBonus != null && rawData.shieldBonus != 0) output += " + " + RoundEsoSkillPercent(rawData.shieldBonus*100) + "% ";
		
		if (output == "")
			output = "" + rawData.baseShield + percent + " (unmodified)";
		else
			output = "" + rawData.baseShield + percent + " " + output + " = " + rawData.finalShield + percent + " final";
		
		if (newRawOutput.maxShield > 0) output += " (" + newRawOutput.maxShield + " cap)";
		
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
		match: /(Heals you or an ally near the enemy for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(heals one other injured target for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(and nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(you and allies are healed for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		isDOT: true,
		match: /(healing you or up to \|c[a-fA-F0-9]{6}[0-9]+\|r nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r over)/gi,
	},
	{
		healId: "Done",
		match: /(healing you or other \|c[a-fA-F0-9]{6}[0-9]+\|r nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage)/gi,
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
		match: /(, restoring \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(restoring \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your missing Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(to heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your missing Health)/gi,
	},
	{
		healId: "Done",
		isDOT: true,
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
		match: /(heal yourself or up to \|c[a-fA-F0-9]{6}[0-9]+\|r allies near the enemy for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
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
		match: /(and your fully-charged Heavy Attacks to restore \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
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
		match: /(Light Attacks heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r\.)/gi,
	},
	{
		healId: "Done",
		isDOT: true,
		match: /(Heavy Attacks heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r every)/gi,
	},
	{
		healId: "Done",
		match: /(causing your Light Attacks to restore \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
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
		healId2: "Received",  // TODO: ?
		match: /(healing you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",  // TODO: ?
		match: /(heals you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage done)/gi,
	},
	{
		healId: "Done",
		match: /(healing you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r and the clannfear)/gi,
	},
	{
		healId: "Done",
		match: /(and the clannfear for \|c[a-fA-F0-9]{6})([0-9]+)(\|r\.)/gi,
	},
	{
		healId: "Done",
		match: /(heals for \|c[a-fA-F0-9]{6})([0-9]+)(\|r health)/gi,
	},
	{
		healId: "Done",
		match: /(heals you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r if the enemy dies)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",  // TODO: ?
		match: /(heal you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r health)/gi,
	},
	{
		healId: "Done",
		match: /(causing it to heal the matriarch and up to \|c[a-fA-F0-9]{6}[0-9]+\|r other friendly targets for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(causing it to heal the winged twilight and \|c[a-fA-F0-9]{6}[0-9]+\|r other friendly target for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(heals you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the total damage)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(to heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your missing Health)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",
		match: /(to heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r, )/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(and healing you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage caused)/gi,
	}, 
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(and heals for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage dealt)/gi,
	},
	{
		healId: "Done",
		match: /(heals its target for \|c[a-fA-F0-9]{6})([0-9]+)(\|r)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(healing for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(healing you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage done)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(healing for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage dealt)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(heals you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage caused)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",
		match: /(you heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you or an ally for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you or an ally in front of you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(healing you or a nearby ally for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(and healing \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(healed for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the shield's remaining strength)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(heal yourself or a nearby ally to the target for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage inflicted by the final hit)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(healing you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(Heals you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the Disease Damage done)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(to heal you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage caused)/gi,
	}, 
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(You heal yourself for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the amount of healing done)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(healing for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of their Max Health)/gi,
	},
	{
		healId: "Done",
		match: /(and heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",
		match: /(healing yourself for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(heal you and your allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		isDOT : true,
		match: /(heal you and your allies in the area for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health over)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",
		match: /(you heal yourself for \|c[a-fA-F0-9]{6})([0-9]+)(\|r and)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(the target is healed for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of their Max Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(Heals you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(you heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
	},
	{
		healId: "Done",
		match: /(Heals \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health to you and allies)/gi,
	},
	{
		healId: "Done",		// Soul Tether: Healing Taken?
		healId2: "Received",
		match: /(you siphon \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health from them)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(healing you or a nearby ally for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage inflicted)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(healing you or .*? other nearby allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage inflicted)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(healing you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage inflicted)/gi,
	},
	{
		healId: "Done",
		match: /(healing an ally in front of you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(heals you and your allies for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(your Light Attacks restore \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(fully charged Heavy Attacks restore \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(Bargain with darkness to restore \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",
		match: /(heals you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(You heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage done)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(You heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of all damage)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(healing you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your missing Health)/gi,
	},
	{
		healId: "Done",
		healId2: "Received",
		match: /(healing you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r each time)/gi,
	},
	{
		display: "%",
		healId: "Done",
		healId2: "Received",
		match: /(You heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage inflicted)/gi,
	},
	{
		healId: "Done",
		match: /(healing yourself or a wounded ally in front of you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(Heals a nearby ally for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your max Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(healing you and the clannfear for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Maximum Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(healing you and the clannfear for \|c[a-fA-F0-9]{6})([0-9]+)(\|r\.)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(to heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of your Max Health)/gi,
	},
	{
		display: "%",
		healId: "Done",
		isDOT: true,
		match: /(to heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r every)/gi,
	},
	{
		healId: "Done",
		match: /(you heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r\.)/gi,
	},
	{
		healId: "Done",
		match: /(you heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r,)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(heal you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage done)/gi,
	},
	{
		healId: "Done",
		match: /(heal you and your allies around you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(heal you and your group members for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(heals you and your group members for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(you are healed for \|c[a-fA-F0-9]{6})([0-9]+)(\|r the next time)/gi,
	},
	{
		healId: "Done",
		match: /(you are healed for \|c[a-fA-F0-9]{6})([0-9]+)(\|r Health)/gi,
	},
	{
		healId: "Done",
		match: /(you heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r when Rally ends)/gi,
	},
	{
		healId: "Done",
		isDOT: true,
		match: /(are healed for \|c[a-fA-F0-9]{6})([0-9]+)(\|r every)/gi,
	},
	{
		healId: "Done",
		match: /(and heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r\.)/gi,
	},
	{
		healId: "Done",
		isDOT: true,
		match: /(and healing you for \|c[a-fA-F0-9]{6})([0-9]+)(\|r every)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(you heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the total Health cost you spent)/gi,
	},
	{
		display: "%",
		healId: "Done",
		match: /(you heal for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the total Health cost you spent)/gi,
	},
	{		//TODO: Check
		healId: "Done",
		match: /(you are healed for \|c[a-fA-F0-9]{6})([0-9]+)(\|r% of the damage)/gi,
	},
		
];


ESO_SKILL_HEALINGOVERRIDES =
{
		"Cauterize" : {
			isAOE : false,
			isDOT : true,
		},
		"Dragon Blood" : {
			isAOE : false,
			isDOT : false,
		},
		"Coagulating Blood" : {
			isAOE : false,
			isDOT : false,
		},
		"Inhale" : {
			isAOE : false,
			isDOT : false,
		},
		"Bitter Harvest" : {
			isAOE : false,
			isDOT : true,
		},
		"Restoring Tether" : {
			isAOE : true,
			isDOT : true,
		},
		"Grim Focus" : {
			isAOE : false,
			isDOT : false,
		},
		"Soul Siphon" : {
			1 : {
				isAOE : true,
				isDOT : false,
			},
			2 : {
				isAOE : true,
				isDOT : true,
			},
		},
		"Soul Tether" : {
			isAOE : false,
			isDOT : true,
		},
		"Funnel Health" : {
			isAOE : true,
			isDOT : true,
		},
		"Sap Essence" : {
			isAOE : true,
			isDOT : true,
		},
		"Surge" : {
			isAOE : false,
			isDOT : false,
		},
		"Puncturing Sweep" : {
			isAOE : false,
			isDOT : false,
		},
		"Purifying Light" : {
			isAOE : true,
			isDOT : true,
		},
		"Living Dark" : {
			isAOE : false,
			isDOT : false,
		},
		"Ritual of Rebirth" : {
			1 : {
				isAOE : true,
				isDOT : false,
			},
			2 : {
				isAOE : false,
				isDOT : false,
			},
		},
		"Cleansing Ritual" : {
			1 : {
				isAOE : true,
				isDOT : false,
			},
			2 : {
				isAOE : false,
				isDOT : false,
			},
		},
		"Secluded Grove" : {
			1 : {
				isAOE : false,
				isDOT : false,
			},
			2 : {
				isAOE : true,
				isDOT : true,
			},
		},
		"Healing Seed" : {
			1 : {
				isAOE : true,
				isDOT : false,
			},
			2 : {
				isAOE : false,
				isDOT : true,
			},
		},
		"Budding Seeds" : {
			1 : {
				isAOE : true,
				isDOT : false,
			},
			2 : {
				isAOE : true,
				isDOT : true,
			},
			3 : {
				isAOE : false,
				isDOT : true,
			},
		},
		"Living Vines" : {
			1 : {
				isAOE : false,
				isDOT : false,
			},
			2 : {
				isAOE : false,
				isDOT : false,
			},
		},
		"Lotus Flower" : {
			isAOE : false,
			isDOT : false,
		},
		"Artic Wind" : {
			1 : {
				isAOE : false,
				isDOT : false,
			},
			2 : {
				isAOE : false,
				isDOT : true,
			},
			3 : {
				isAOE : false,
				isDOT : false,
			},
		},
		"Lacerate" : {
			isAOE : false,
			isDOT : false,
		},
		"Panacea" : {
			isAOE : false,
			isDOT : true,
		},
		"Grand Healing" : {
			1 : {
				isAOE : true,
				isDOT : false,
			},
			2 : {
				isAOE : true,
				isDOT : true,
			},
		},
		"Regeneration" : {
			isAOE : false,
			isDOT : true,
		},
		"Radiating Regeneration" : {
			isAOE : true,
			isDOT : true,
		},
		"Blessing of Protection" : {
			isAOE : true,
			isDOT : false,
		},
		"Healing Ward" : {
			isAOE : false,
			isDOT : true,
		},
		"Force Siphon" : {
			isAOE : false,
			isDOT : false,
		},
		"Blood Scion" : {
			isAOE : false,
			isDOT : false,
		},
		"Meditate" : {
			isAOE : false,
			isDOT : true,
		},
		"Blood Altar" : {
			isAOE : false,
			isDOT : false,
		},
		"Energy Orb" : {
			1 : {
				isAOE : true,
				isDOT : true,
			},
			2 : {
				isAOE : true,
				isDOT : false,
			},
		},
		"Burning Embers" : {
			isAOE : false,
			isDOT : false,
		},
};


window.UpdateEsoSkillHealingDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	var isAOE = false;
	var isDOT = false;
	
	if (inputValues == null) return newDesc;
	if (inputValues.Healing == null) return newDesc;
	
	var rawOutput = [];
	var newRawOutput = {};
	var matchCount = 0;
	var skillLineName = skillData.skillLine.replace(' ', '_');
	
	for (var i = 0; i < ESO_SKILL_HEALINGMATCHES.length; ++i)
	{
		var matchData = ESO_SKILL_HEALINGMATCHES[i];
		var healingFactor = 1;
		var overrideData = ESO_SKILL_HEALINGOVERRIDES[skillData['name']];
		if (overrideData == null) overrideData = ESO_SKILL_HEALINGOVERRIDES[skillData['baseName']];
		
		isAOE = false;
		if ((skillData.target == "Ground" || skillData.target == "Area") && skillData.radius > 0) isAOE = true;
		if (matchData.isAOE != null) isAOE = matchData.isAOE;
		
		isDOT = false;
		if (skillData.duration > 0) isDOT = true;
		if (matchData.isDOT != null) isDOT = matchData.isDOT;
		
		if (overrideData != null)
		{
			if (overrideData[matchCount + 1] != null)
			{
				overrideData = overrideData[matchCount + 1];
			}
			
			if (overrideData.isDOT != null) isDOT = overrideData.isDOT;
			if (overrideData.isAOE != null) isAOE = overrideData.isAOE;
		}
		
		newDesc = newDesc.replace(matchData.match, function(match, p1, p2, p3, offset, string)
		{
			++matchCount;
		
			if (inputValues.Healing[matchData.healId] == null) return match;
			
			var modHealing = parseFloat(p2);
			
			newRawOutput = {};
			newRawOutput.healId = matchData.healId;
			newRawOutput.baseHeal = p2;
			newRawOutput.healDone = inputValues.Healing[matchData.healId];
			
			healingFactor += inputValues.Healing[matchData.healId];
			
			if (isAOE && inputValues.Healing.AOE != null && inputValues.Healing.AOE != 0)
			{
				newRawOutput.aoeHeal = inputValues.Healing.AOE;
				healingFactor += inputValues.Healing.AOE;
			}
			else if (!isAOE && inputValues.Healing.SingleTarget != null && inputValues.Healing.SingleTarget != 0)
			{
				newRawOutput.singleTargetHeal = inputValues.Healing.SingleTarget;
				healingFactor += inputValues.Healing.SingleTarget;
			}
			
			if (isDOT && inputValues.Healing.DOT != null && inputValues.Healing.DOT != 0)
			{
				newRawOutput.dotHeal = inputValues.Healing.DOT;
				healingFactor += inputValues.Healing.DOT;
			}
			
			var skillHealing = inputValues.SkillHealing[skillLineName];
			
			if (inputValues.SkillHealing != null && skillHealing != null)
			{
				healingFactor += skillHealing;
				newRawOutput.skillHealingDone = skillHealing;
			}
			
			modHealing *= healingFactor;
			modHealing = Math.round(modHealing);
			
			newRawOutput.display = matchData.display;
			newRawOutput.finalHeal = modHealing;
			rawOutput.push(newRawOutput);
			
			return p1 + modHealing + p3;
		});
	}
	
	for (var i = 0; i < rawOutput.length; ++i)
	{
		var rawData = rawOutput[i];
		var output = "";
		var percent = "";
		
		if (newRawOutput.display == "%") percent = "%";
				
		if (rawData.healDone != null && rawData.healDone != 0) output += " + " + RoundEsoSkillPercent(rawData.healDone*100) + "% " + rawData.healId;
		if (rawData.dotHeal  != null && rawData.dotHeal  != 0) output += " + " + RoundEsoSkillPercent(rawData.dotHeal*100) + "% DOT";
		if (rawData.aoeHeal  != null && rawData.aoeHeal  != 0) output += " + " + RoundEsoSkillPercent(rawData.aoeHeal*100) + "% AOE";
		if (rawData.singleTargetHeal  != null && rawData.singleTargetHeal  != 0) output += " + " + RoundEsoSkillPercent(rawData.singleTargetHeal*100) + "% SingleTarget";
		if (rawData.skillHealingDone != null && rawData.skillHealingDone != 0) output += " + " + RoundEsoSkillPercent(rawData.skillHealingDone*100) + "% SkillLine";
		//TODO: healId2?
		
		if (output == "")
			output = "" + rawData.baseHeal + percent + " Health (unmodified)";
		else
			output = "" + rawData.baseHeal + percent + " Health " + output + " = " + rawData.finalHeal + percent + " final";
		
		skillData.rawOutput["Tooltip Healing " + (i+1)] = output;
	}
	
	return newDesc;
}


ESO_SKILL_HEALINGREDUCTIONMATCHES = 
	[
		{
			display: "%",
			match: /(reducing their healing received by \|c[a-fA-F0-9]{6})([0-9]+)(\|r%)/gi,
		},
		{
			display: "%",
			match: /(reducing their healing received and Health Recovery by \|c[a-fA-F0-9]{6})([0-9]+)(\|r%)/gi,
		},
		{
			display: "%",
			match: /(reducing their healing received by [0-9]+% and Health Recovery by \|c[a-fA-F0-9]{6})([0-9]+)(\|r%)/gi,
		},
		{
			display: "%",
			match: /(reducing your healing received and Health Recovery by \|c[a-fA-F0-9]{6})([0-9]+)(\|r%)/gi,
		},
];


window.UpdateEsoSkillHealingReductionDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	
	if (inputValues == null) return newDesc;
	if (inputValues.Healing == null) return newDesc;
	if (inputValues.Healing.Reduction == null) return newDesc;
	if (inputValues.Healing.Reduction == 0) return newDesc;
	
	var rawOutput = [];
	var newRawOutput = {};
	
	for (var i = 0; i < ESO_SKILL_HEALINGREDUCTIONMATCHES.length; ++i)
	{
		var matchData = ESO_SKILL_HEALINGREDUCTIONMATCHES[i];
		var reductionFactor = inputValues.Healing.Reduction;
		
		newDesc = newDesc.replace(matchData.match, function(match, p1, p2, p3, offset, string)
		{
			var modHealing = parseFloat(p2);
			
			newRawOutput = {};
			newRawOutput.baseHeal = p2;
			newRawOutput.reductionFactor = reductionFactor;
			
			modHealing *= (1 + reductionFactor);
			modHealing = Math.round(modHealing);
			
			newRawOutput.display = matchData.display;
			newRawOutput.finalHeal = modHealing;
			rawOutput.push(newRawOutput);
			
			return p1 + modHealing + p3;
		});
	}
	
	for (var i = 0; i < rawOutput.length; ++i)
	{
		var rawData = rawOutput[i];
		var output = "";
		var percent = "";
		
		if (newRawOutput.display == "%") percent = "%";
		output = "" + rawData.baseHeal + percent + " Healing " + " = " + rawData.finalHeal + percent + " final";
		
		skillData.rawOutput["Tooltip Healing Reduction " + (i+1)] = output;
	}
	
	return newDesc;
}


ESO_SKILL_DAMAGEMATCHES = 
[
	{
		damageId: "Magic",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Magic Damage)( over| each| every| to them every| to them over| to enemies in the target area every|to nearby enemies each second for| to nearby enemies every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Magic", // Blazing Shield
		match: /(When the shield expires it explodes outward, dealing )(\|c[a-fA-F0-9]{6})([^|]*)(\|r% of the Damage)(X|)(X|)(X|)(X|)/gi,
	},
	{
		damageId: "Physical",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Physical Damage)( over| each| every| to them every| to them over| to enemies in the target area every| to nearby enemies each second for| to nearby enemies every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Flame",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Flame Damage)( over| each| every| to them every| to them over| to enemies in the target area every| to nearby enemies each second for| to nearby enemies every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Shock",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Shock Damage)( over| each| every| to them every| to them over| to enemies in the target area every| to nearby enemies each second for| to nearby enemies every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Frost",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Cold Damage)( over| each| every| to them every| to them over| to enemies in the target area every| to nearby enemies each second for| to nearby enemies every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Frost",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Frost Damage)( over| each| every| to them every| to them over| to enemies in the target area every| to nearby enemies each second for| to nearby enemies every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Poison",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Poison Damage)( over| each| every| to them every| to them over| to enemies in the target area every| to nearby enemies each second for| to nearby enemies every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Disease",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Disease Damage)( over| each| every| to them every| to them over| to enemies in the target area every| to nearby enemies each second for| to nearby enemies every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
	{
		damageId: "Bleed",
		match: /(additional |)(\|c[a-fA-F0-9]{6})([^|]*)(\|r Bleed Damage)( over| each| every| to them every| to them over| to enemies in the target area every| to nearby enemies each second for| to nearby enemies every|)( \|c[a-fA-F0-9]{6}|)([^|]*|)(\|r second|)/gi,
	},
];


/*
 * True means that particular skill/number is an AOE effect. Indexes start at 1 (first damage number in tooltip).
 */
ESO_SKILL_TARGETYPE_OVERRIDES = 
{
		16536 : true,	// Meteor
		29012 : true,  	// Dragon Leap
		"Lotus Fan" : { 2 : true },	// Lotus Fan
		"Dark Shade" : { 2 : true },	// Dark Shade
		24326 : { 2 : true },	// Daedric Curse
		"Crystal Blast" : { 2 : true }, // Crystal Blast
		18718 : { 3 : true }, 	// Mages' Fury
		"Unstable Core" : { 2 : true }, // Unstable Core
		"Deep Slash" : { 2 : true }, // Deep Slash
		"Brutal Pounce" : { 2 : true }, // Brutal Pounce
		39475 : { 3 : true }, // Inner Fire
		
		"Spiked Armor" : false, 	// Spiked Armor
		"Hardened Armor" : false, 	// Hardened Armor
		"Volatile Armor" : { 2 : false }, 	// Volatile Armor
		61902 : false, 	// Grim Focus
		23634 : { 2 : false }, 	// Summon Storm Atronach
		23304 : { 1 : false }, 	// Summon Unstable Familiar
		24613 : { 1 : false },	// Summon Winged Twilight
		24785 : { 1 : false },	// Overload
		85986 : { 1 : false, 3 : false },	// Feral Guardian
		86135 : false,	// Crystallized Slab
		86175 : false,	// Frozen Gate
		"Clouding Swarm" : { 2 : false }, // Clouding Swarm
		35750 : false,	// Trap Beast
		103483 : false,	// Imbue Weapon
		85986 : { 3 : false, 4 : false },	// Trapping Webs
		103483 : { 1: false }, // Imbue Weapon
		"Blastbones" : true,
		85982 : { 1 : false, 2 : true, 3 : false },	// Feral Guardian
		
			// Passives
		"Heavy Weapons" : false,		// Heavy Weapons
		"Twin Blade and Blunt" : false,	// Twin Blade and Blunt
		
			// Bugged Cases?
		"Burning Light" : "none",		// Burning Light, Not affected by either?
		"Reflective Light" : { 1 : true, 2 : false }, 	// Reflective Light, #2 should be AOE?
		"Force Pulse" : { 4 : "none" },			// Force Pulse, Not affected by either?
		
		
};


/*
 * Skill ID/Name followed by:
 * 		True : All damages in the ability are pet damage
 * 		False: No damages in the ability are pet damage
 * 		array(1 : true, 2: false, ...)		Only the specified match indexes are considered
 */
ESO_SKILL_PETDAMAGE_OVERRIDES = 
{
		"Summon Storm Atronach" : true,
		"Summon Unstable Familiar" : true,
		"Summon Winged Twilight" : true,
		"Summon Shade" : true,
		"Frozen Colossus" : true,
		"Skeletal Mage" : true,
		"Feral Guardian" : true,
};


ESO_SKILL_DOT_OVERRIDES = 
{
		"Puncturing Strikes" : false,
		"Biting Jabs" : false,
		"Puncturing Sweep" : false,
		"Flurry" : false,
		"Rapid Strikes" : false,
		"Bloodthirst" : false,
		"Bound Armaments" : false,
};


window.IsEsoSkillMechanicUltimate = function(mechanic)
{
	if (g_SkillsVersion == '' || parseInt(g_SkillsVersion) >= 34)
	{
		return mechanic == 8;
	}
	
	return mechanic == 10;
}


window.IsEsoSkillMechanicMagicka = function(mechanic)
{
	if (g_SkillsVersion == '' || parseInt(g_SkillsVersion) >= 34)
	{
		return mechanic == 1;
	}
	
	return mechanic == 0;
}


window.IsEsoSkillMechanicStamina = function(mechanic)
{
	if (g_SkillsVersion == '' || parseInt(g_SkillsVersion) >= 34)
	{
		return mechanic == 4;
	}
	
	return mechanic == 6;
}


window.IsEsoSkillMechanicHealth = function(mechanic)
{
	if (g_SkillsVersion == '' || parseInt(g_SkillsVersion) >= 34)
	{
		return mechanic == 32;
	}
	
	return mechanic == -2;
}


window.UpdateEsoSkillDamageDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	if (inputValues == null) return newDesc;
	if (inputValues.Damage == null) return newDesc;
	
	var rawOutput = [];
	var newRawOutput = {};
	var target = "";
	var skillLineName = skillData.skillLine.replace(' ', '_');
	var overrideAoe = null;
	var overrideDot = null;
	var overridePet = null;
	
	if (skillData.target) target = skillData.target.toLowerCase();
	
	var isDot = false;
	
	if (skillData.channelTime > 0) isDot = true;
	if (inputValues.Damage.Dot == null || isNaN(inputValues.Damage.Dot)) isDot = false;
	
	overrideDot = ESO_SKILL_DOT_OVERRIDES[skillData.name];
	if (overrideDot == null) overrideDot = ESO_SKILL_DOT_OVERRIDES[skillData.abilityId];
	if (overrideDot == null) overrideDot = ESO_SKILL_DOT_OVERRIDES[skillData.baseAbilityId];
	if (overrideDot != null) isDot = overrideDot;
	
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	
	overrideAoe = ESO_SKILL_TARGETYPE_OVERRIDES[skillData.name];
	if (overrideAoe == null) overrideAoe = ESO_SKILL_TARGETYPE_OVERRIDES[skillData.abilityId];
	if (overrideAoe == null) overrideAoe = ESO_SKILL_TARGETYPE_OVERRIDES[skillData.baseAbilityId];
	
	overridePet = ESO_SKILL_PETDAMAGE_OVERRIDES[skillData.name];
	if (overridePet == null) overridePet = ESO_SKILL_PETDAMAGE_OVERRIDES[skillData.baseName];
	if (overridePet == null) overridePet = ESO_SKILL_PETDAMAGE_OVERRIDES[skillData.abilityId];
	if (overridePet == null) overridePet = ESO_SKILL_PETDAMAGE_OVERRIDES[skillData.baseAbilityId];
	
	skillData.baseTooltips = {};
	
	for (var i = 0; i < ESO_SKILL_DAMAGEMATCHES.length; ++i)
	{
		var matchData = ESO_SKILL_DAMAGEMATCHES[i];
		var matchIndex = 0;
		
		newDesc = newDesc.replace(matchData.match, function(match, p1, p2, p3, p4, p5, p6, p7, p8, offset, string) 
		{
			var addedDotDamageDone = false;
			var thisEffectIsDot = false;
			matchIndex = matchIndex + 1;
			
			var modDamage = parseFloat(p3);
			skillData.baseTooltips[matchIndex] = modDamage;
			
			if (inputValues.Damage[matchData.damageId] == null) return match;
			
			var baseFactor = 1;
			var isAOE = false;
			var isSingleTarget = false;
			var isPet = false;
			
			if (target == "area" || target == "cone" || target == "self" || target == "ground") isAOE = true;
			if (target == "enemy") isSingleTarget = true;
			
			if (overrideAoe != null)
			{
				var overrideValue = overrideAoe;
				if (typeof(overrideAoe) == "object") overrideValue = overrideAoe[matchIndex];
				
				if (overrideValue === true)
				{
					isAOE = true;
					isSingleTarget = false;
				}
				else if (overrideValue === false)
				{
					isAOE = false;
					isSingleTarget = true;
				}
				else if (overrideValue === "none")
				{
					isAOE = false;
					isSingleTarget = false;
				}
			}
			
			if (overridePet != null)
			{
				var overrideValue = overridePet;
				if (typeof(overridePet) == "object") overrideValue = overridePet[matchIndex];
				if (overrideValue === true) isPet = true;
			}
			
			newRawOutput = {};
			newRawOutput.damageId = matchData.damageId;
			newRawOutput.baseDamage = p3;
			newRawOutput.mainDamageDone = inputValues.Damage[matchData.damageId];
			
			if (inputValues.SkillDamage != null && inputValues.SkillDamage[skillData.baseName] != null)
			{
				baseFactor += inputValues.SkillDamage[skillData.baseName];
				newRawOutput.skillDamageDone = inputValues.SkillDamage[skillData.baseName];
			}
			else if (inputValues.SkillDamage != null && inputValues.SkillDamage[skillData.name] != null)
			{
				baseFactor += inputValues.SkillDamage[skillData.name];
				newRawOutput.skillDamageDone = inputValues.SkillDamage[skillData.name];
			}
			
			if (inputValues.SkillLineDamage != null && inputValues.SkillLineDamage[skillLineName] != null)
			{
				baseFactor += inputValues.SkillLineDamage[skillLineName];
				newRawOutput.skillLineDamageDone = inputValues.SkillLineDamage[skillLineName];
			}
			
			if ((isDot || p5 != "") && overrideDot !== false)
			{
				thisEffectIsDot = (p5 != "");
				
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
				
				if (inputValues.DotDamageDone && inputValues.DotDamageDone[matchData.damageId])
				{
					baseFactor += +inputValues.DotDamageDone[matchData.damageId];
					newRawOutput.dotDamageDone += inputValues.DotDamageDone[matchData.damageId];
					addedDotDamageDone = true;
				}
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
			
				//TODO: Double factor for dots that are channels?
			if (skillData.channelTime > 0 && inputValues.ChannelDamageDone && inputValues.ChannelDamageDone[matchData.damageId] && !addedDotDamageDone)
			{
				baseFactor += +inputValues.ChannelDamageDone[matchData.damageId];
				newRawOutput.channelDamageDone = inputValues.ChannelDamageDone[matchData.damageId];
			}
			
			var amountAll = 0;
			
			if (inputValues.Damage.All != null) amountAll += Math.round(inputValues.Damage.All*100)/100;
			
					// Empower if not ultimate (check both mechanic types, pre and post update 34
			if (inputValues.Damage.Empower != null && !thisEffectIsDot && !IsEsoSkillMechanicUltimate(skillData.mechanic)) amountAll += Math.round(inputValues.Damage.Empower*100)/100;
			
			if (isSingleTarget && inputValues.Damage.SingleTarget != null) 
			{
				baseFactor += Math.round(inputValues.Damage.SingleTarget*100)/100;
				newRawOutput.singleTargetDamageDone = +inputValues.Damage.SingleTarget;
			}
			
			if (isAOE && inputValues.Damage.AOE != null && inputValues.Damage.AOE != 0) 
			{
				baseFactor += Math.round(inputValues.Damage.AOE*100)/100;
				newRawOutput.aoeDamageDone = +inputValues.Damage.AOE;
			}
			
			if (g_EsoSkillFlameAOESkills && inputValues.FlameAOEDamageDone)
			{
				var flameData = g_EsoSkillFlameAOESkills[skillData.abilityId];
				if (flameData == null) flameData = g_EsoSkillFlameAOESkills[skillData.displayId];
				
				if (flameData)
				{
					if (flameData.length === 0 || flameData[matchIndex])
					{
						baseFactor += Math.round(inputValues.FlameAOEDamageDone*100)/100;
						if (newRawOutput.aoeDamageDone == null) newRawOutput.aoeDamageDone = 0;
						newRawOutput.aoeDamageDone += inputValues.FlameAOEDamageDone;
					}
				}
			}
			
			if (IsEsoSkillMechanicMagicka(skillData.mechanic) && inputValues.MagickaAbilityDamageDone)
			{
				baseFactor += Math.round(inputValues.MagickaAbilityDamageDone*100)/100;
				newRawOutput.magickaAbilityDamageDone = inputValues.MagickaAbilityDamageDone;
			}
			
			if (isPet && inputValues.Damage.Pet)
			{
				baseFactor += Math.round(inputValues.Damage.Pet*100)/100;
				newRawOutput.PetDamageDone = inputValues.Damage.Pet;
			}
			
				// Overload special case
			if (skillData.baseName == "Overload" && inputValues.Damage.Overload != null && inputValues.Damage.Overload != 0)
			{
				baseFactor += inputValues.Damage.Overload;
				newRawOutput.overloadDamage = inputValues.Damage.Overload;
			}
			
			if (thisEffectIsDot && inputValues.SkillDotDamage && inputValues.SkillDotDamage[skillData.baseName])
			{
				baseFactor += inputValues.SkillDotDamage[skillData.baseName];
				newRawOutput.skillDotDamage = inputValues.SkillDotDamage[skillData.baseName];
			}
			
			if (amountAll != 0)	baseFactor += amountAll;
			newRawOutput.damageDone = amountAll;
			
			modDamage *= baseFactor;
			
			if (!thisEffectIsDot && inputValues.SkillDirectDamage && inputValues.SkillDirectDamage[skillData.baseName])
			{
				modDamage += inputValues.SkillDirectDamage[skillData.baseName];
				newRawOutput.skillDirectDamage = inputValues.SkillDirectDamage[skillData.baseName];
			}
			
			if (!thisEffectIsDot && inputValues.SkillDirectDamage && inputValues.SkillDirectDamage[skillData.skillLine])
			{
				modDamage += inputValues.SkillDirectDamage[skillData.skillLine];
				newRawOutput.skillLineDirectDamage = inputValues.SkillDirectDamage[skillData.skillLine];
			}
			
			if (skillData.baseName == "Twin Slashes" && inputValues.TwinSlashInitialDamage) {
				modDamage += inputValues.TwinSlashInitialDamage;
				newRawOutput.twinSlashInitialDamage = inputValues.TwinSlashInitialDamage;
			}
			
				// Overload special case
			if (skillData.baseName == "Overload" && inputValues.FlatOverloadDamage != null && inputValues.FlatOverloadDamage > 0)
			{
				modDamage += inputValues.FlatOverloadDamage;
				newRawOutput.flatOverloadDamage = inputValues.FlatOverloadDamage;
			}
			
			if (inputValues.SkillFlatDamage && inputValues.SkillFlatDamage[skillData.baseName])
			{
				modDamage += inputValues.SkillFlatDamage[skillData.baseName];
				newRawOutput.skillFlatDamage = inputValues.SkillFlatDamage[skillData.baseName];
			}
			
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
		if (rawData.aoeDamageDone  != null && rawData.aoeDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.aoeDamageDone*100) + "% AOE";
		if (rawData.singleTargetDamageDone  != null && rawData.singleTargetDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.singleTargetDamageDone*100) + "% SingleTarget";
		if (rawData.directDamageDone  != null && rawData.directDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.directDamageDone*100) + "% Direct";
		if (rawData.dotDamageDone  != null && rawData.dotDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.dotDamageDone*100) + "% DOT";
		if (rawData.channelDamageDone  != null && rawData.channelDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.channelDamageDone*100) + "% Channel";
		if (rawData.PetDamageDone  != null && rawData.PetDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.PetDamageDone*100) + "% Pet";
		if (rawData.damageDone     != null && rawData.damageDone     != 0) output += " + " + RoundEsoSkillPercent(rawData.damageDone*100) + "% All";
		if (rawData.magickaAbilityDamageDone != null && rawData.magickaAbilityDamageDone != 0) output += " + " + RoundEsoSkillPercent(rawData.magickaAbilityDamageDone*100) + "% Magicka";
		if (rawData.overloadDamage != null && rawData.overloadDamage != 0) output += " + " + RoundEsoSkillPercent(rawData.overloadDamage*100) + "% Overload";
		if (rawData.skillDotDamage != null && rawData.skillDotDamage != 0) output += " + " + RoundEsoSkillPercent(rawData.skillDotDamage*100) + "% SkillDot";
		if (rawData.skillDirectDamage != null && rawData.skillDirectDamage != 0) output += " + " + RoundEsoSkillPercent(rawData.skillDirectDamage) + " SkillDirect";
		if (rawData.skillLineDirectDamage != null && rawData.skillLineDirectDamage != 0) output += " + " + RoundEsoSkillPercent(rawData.skillLineDirectDamage) + " SkillLineDirect";
		if (rawData.twinSlashInitialDamage != null && rawData.twinSlashInitialDamage != 0) output += " + " + RoundEsoSkillPercent(rawData.twinSlashInitialDamage) + " StingingSlashes";
		if (rawData.flatOverloadDamage != null && rawData.flatOverloadDamage > 0) output += " + " + RoundEsoSkillPercent(rawData.flatOverloadDamage) + " Overload";
		if (rawData.skillFlatDamage != null && rawData.skillFlatDamage > 0) output += " + " + RoundEsoSkillPercent(rawData.skillFlatDamage) + " Flat";
		
		if (output == "")
			output = "" + rawData.baseDamage + " " + rawData.damageId + " Damage (unmodified)";
		else
			output = "" + rawData.baseDamage + " " + rawData.damageId + " Damage " + output + " = " + rawData.finalDamage + " final";
		
		skillData.rawOutput["Tooltip Damage " + (i+1)] = output;
	}
	
	return newDesc;
}

//and causing them to bleed for an additional 3699 Physical Damage over 10 seconds
//apply a bleed dealing 4410 Physical Damage
//bleed for an additional 5115 Physical Damage (Twin Slashes)
//bleed enemies for 4410 Physical Damage
//Light Attacks apply a bleed for 5888 Physical Damage
ESO_SKILL_BLEEDMATCHES = 
[
		/(bleed dealing \|c[a-fA-F0-9]{6})([^|]*)(\|r Physical Damage)/,
		/(bleed for an additional \|c[a-fA-F0-9]{6})([^|]*)(\|r Physical Damage)/,
		/(bleed enemies for \|c[a-fA-F0-9]{6})([^|]*)(\|r Physical Damage)/,
		/(bleed for \|c[a-fA-F0-9]{6})([^|]*)(\|r Physical Damage)/,
		/(bleed dealing \|c[a-fA-F0-9]{6})([^|]*)(\|r Bleed Damage)/,
		/(bleed for an additional \|c[a-fA-F0-9]{6})([^|]*)(\|r Bleed Damage)/,
		/(bleed enemies for \|c[a-fA-F0-9]{6})([^|]*)(\|r Bleed Damage)/,
		/(bleed for \|c[a-fA-F0-9]{6})([^|]*)(\|r Bleed Damage)/,
];


window.UpdateEsoSkillBleedDamageDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	var displayId = skillData['displayId'];
	var skillId = skillData['abilityId'];
	
	if (inputValues == null) return newDesc;
	
	if (!inputValues.BleedDamage &&
			(!inputValues.DotDamageDone || !inputValues.DotDamageDone.Bleed)) return newDesc;
	
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	
	for (var i = 0; i < ESO_SKILL_BLEEDMATCHES.length; ++i)
	{
		var match = ESO_SKILL_BLEEDMATCHES[i];
		var matchIndex = 0;
		
		newDesc = newDesc.replace(match, function(match, p1, p2, p3, offset, string)
				{
					var newDamage = +p2;
					var output = "";
					var modDamage = 0;
					var flatDamage = 0;
					
					matchIndex++;
					
					if (inputValues.BleedDamage)
					{
						modDamage += inputValues.BleedDamage;
						output = "" + p2 + " + " + RoundEsoSkillPercent(inputValues.BleedDamage*100) + "% Bleed";
					}
					
					if (inputValues.DotDamageDone && inputValues.DotDamageDone.Bleed)
					{
						modDamage += inputValues.DotDamageDone.Bleed;
						output = "" + p2 + " + " + RoundEsoSkillPercent(inputValues.DotDamageDone.Bleed*100) + "% BleedDOT";
					}
					
					if (modDamage != 0)
					{
						newDamage = Math.floor((newDamage + flatDamage) * (1 + modDamage));
					}
					
					output += " = " + newDamage + " final";
					skillData.rawOutput["Bleed Damage " + matchIndex] = output;
			
					return p1 + newDamage + p3;
				});		
	}
	
	return newDesc;
}


// 1537 Flame Damage every 1 second for 15 seconds
// 5605 Flame Damage over 8.5 seconds
// 613 Flame Damage to nearby enemies each second for 9 seconds.
ESO_SKILL_ELFBANEDURATIONMATCHES = 
[
		/( Flame Damage every \|c[a-fA-F0-9]{6}[^|]*\|r second for )(\|c[a-fA-F0-9]{6})([^|]*)(\|r seconds)/gi,
		/( Flame Damage over )(\|c[a-fA-F0-9]{6})([^|]*)(\|r seconds)/gi,
		/( Flame Damage to nearby enemies each second for )(\|c[a-fA-F0-9]{6})([^|]*)(\|r seconds)/gi,
];


window.UpdateEsoSkillElfBaneDurationDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	var displayId = skillData['displayId'];
	var skillId = skillData['abilityId'];
	var elfBaneSkill = null;
	
	if (inputValues == null) return newDesc;
	if (inputValues.ElfBaneDuration == null || inputValues.ElfBaneDuration == 0) return newDesc;
	
	elfBaneSkill = g_EsoSkillElfBaneSkills[skillId];
	if (elfBaneSkill == null) elfBaneSkill = g_EsoSkillElfBaneSkills[displayId];
	if (elfBaneSkill == null) return newDesc;
	
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	
	for (var i = 0; i < ESO_SKILL_ELFBANEDURATIONMATCHES.length; ++i)
	{
		var match = ESO_SKILL_ELFBANEDURATIONMATCHES[i];
		
		newDesc = newDesc.replace(match, function(match, p1, p2, p3, p4, offset, string) 
				{
					var newDuration = +p3 + inputValues.ElfBaneDuration;
					
					if (elfBaneSkill == 2 && skillData['newDuration'])
					{	
						newDuration = +skillData['newDuration'] / 1000;
						newDuration = newDuration.toFixed(1);
						skillData.rawOutput["Tooltip Flame Damage Duration (Elf Bane)"] = "Ability Duration of " + newDuration + " secs";
					}
					else
					{
						newDuration = newDuration.toFixed(1);
						skillData.rawOutput["Tooltip Flame Damage Duration (Elf Bane)"] = "" + p3 + " + " + inputValues.ElfBaneDuration + " = " + newDuration + " secs";	
					}
					
					return p1 + p2 + newDuration + p4;
				});
	}
	
	return newDesc;
}

window.UpdateEsoSkillUppercutDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	
	if (inputValues == null || inputValues['SkillDamage'] == null) return newDesc;
	if (skillData['baseName'] != "Uppercut") return newDesc;
	
	var aoeAmount = inputValues['SkillDamage']['Cleave_AOE'];
	if (aoeAmount == null || aoeAmount == 0) return newDesc;
	
	var numbers = newDesc.match(/([0-9]+)/g);
	if (numbers == null) return newDesc;
	
	var baseDmg = parseFloat(numbers[0]);
	if (baseDmg == null) return newDesc;
	
	var damage = Math.floor(aoeAmount * baseDmg);
	var extraDesc = "\n\nAdds |cffffff" + damage + "|r Physical Damage to all nearby enemies.";
	
	newDesc += extraDesc;
	
	return newDesc;
}


window.UpdateEsoSkillScatterShotDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	
	if (inputValues == null || inputValues['SkillDamage'] == null) return newDesc;
	if (skillData['baseName'] != "Scatter Shot") return newDesc;
	
	var dotAmount = inputValues['SkillDamage']['Scatter_Shot_DOT'];
	if (dotAmount == null || dotAmount == 0) return newDesc;
	
	var numbers = newDesc.match(/([0-9]+)/g);
	if (numbers == null) return newDesc;
	
	var baseDmg = parseFloat(numbers[0]);
	if (baseDmg == null) return newDesc;
	
	var damage = Math.floor(dotAmount * baseDmg);
	var extraDesc = "\n\nAdds |cffffff" + damage + "|r Poison Damage every |cffffff2|r seconds for |cffffff4-12|r seconds.";
	
	newDesc += extraDesc;
	
	return newDesc;
}


window.UpdateEsoSkillVolleyDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	
	if (inputValues == null || inputValues['SkillDamage'] == null) return newDesc;
	if (skillData['baseName'] != "Volley") return newDesc;
	
	var dmgAmount = inputValues['SkillDamage']['Volley_Tick'];
	var tickAmount = inputValues['SkillDamage']['Volley_DmgTick'];
	if (dmgAmount == null || dmgAmount == 0) return newDesc;
	if (tickAmount == null || tickAmount == 0) return newDesc;
	
	var numbers = newDesc.match(/([0-9]+)/g);
	if (numbers == null) return newDesc;
	
	var baseDmg = parseFloat(numbers[0]);
	var duration = parseInt(numbers[2]);
	if (baseDmg == null || duration == null || duration <= 0) return newDesc;
	
	duration++;		// Ticks at start and end of duration
	var dmgTicks = [];
	var tickDamage = baseDmg;
	var maxTick = 9;	// The set tooltip says 8 but it seems to be 9 ticks on PTS 35
	
	for (var i = 0; i < duration; ++i)
	{
		if (i < maxTick)
			tickDamage = baseDmg + dmgAmount + tickAmount * i;
		else
			tickDamage = baseDmg + dmgAmount + tickAmount * 8;
			
		dmgTicks.push(tickDamage);
	}
	
	dmgTicks.join(", ");
	var extraDesc = "\n\nTick Damage: " + dmgTicks.join(", ");
	
	newDesc += extraDesc;
	
	return newDesc;
}


window.UpdateEsoSkillRapidStrikesDescription = function (skillData, skillDesc, inputValues)
{
	var newDesc = skillDesc;
	var extraDesc = "";
	var name = skillData['name'];
	var hitExtraDmg = 0.03;
	var finalExtraDmg = 3.00;
	var skipExtraDmgParse = false;
	var finalDmgIndex = 1;
	var numHits = 4;	// Used to be 5 at one point
	
	if (inputValues == null) return newDesc;
	
	if (name == "Rapid Strikes") {
		// Do nothing
	}
	else if (name == "Bloodthirst") {
		hitExtraDmg = 0;
		skipExtraDmgParse = true;
		finalDmgIndex = 2;
	}
	else if (name == "Flurry") {
		hitExtraDmg = 0;
		skipExtraDmgParse = true;
	}
	else {
		return newDesc;
	}
	
	extraDesc = "\n\n";
	
	var numbers = newDesc.match(/([0-9]+)/g);
	if (numbers == null) return newDesc;
	
	var baseDmg = parseFloat(numbers[0]);
	var modDmg = 1;
	if (baseDmg == null) return newDesc;
	
	if (skillData.baseTooltips && skillData.baseTooltips[1]) {
		modDmg = baseDmg / skillData.baseTooltips[1];
		baseDmg = skillData.baseTooltips[1];
	}
	
	if (!skipExtraDmgParse) {
		if (numbers[1] != null) hitExtraDmg = parseFloat(numbers[1]) / 100;
		if (numbers[2] != null) finalExtraDmg = parseFloat(numbers[2]) / 100;
	}
	else {
		if (numbers[finalDmgIndex] != null) finalExtraDmg = parseFloat(numbers[finalDmgIndex]) / 100;
	}
	
	var hit1 = Math.floor(baseDmg * modDmg);
	var hit2 = Math.floor(baseDmg * (hitExtraDmg*1 + modDmg));
	var hit3 = Math.floor(baseDmg * (hitExtraDmg*2 + modDmg));
	var hit4 = Math.floor(baseDmg * (hitExtraDmg*3 + modDmg));
	var hit5 = Math.floor(baseDmg * (hitExtraDmg + modDmg) + baseDmg * finalExtraDmg * (1 + hitExtraDmg));
	
	var totalDmg = hit1 + hit2 + hit3 + hit4 + hit5;
	if (numHits == 4) totalDmg = hit1 + hit2 + hit3 + hit4;
	
	if (numHits == 4) 
		extraDesc += "Hits: |cffffff" + hit1 + "|r, |cffffff" + hit2 + "|r, |cffffff" + hit3 + "|r, |cffffff" + hit4 + "|r";
	else
		extraDesc += "Hits: |cffffff" + hit1 + "|r, |cffffff" + hit2 + "|r, |cffffff" + hit3 + "|r, |cffffff" + hit4 + "|r";
	
	extraDesc += "  (Total |cffffff" + totalDmg + "|r)";
	
	return newDesc + extraDesc;
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
	return skillLine.replace(/["' ]/g, "_");
}



window.g_EsoSkill_DamageShieldSkills = {
		"Hardened Armor" : 1,
		"Obsidian Shield" : 1,
		"Conjured Ward" : 1,
		"Sun Shield" : 1,
		"Annulment" : 1,
		"Brawler" : 1,
		"Defensive Posture" : 1,
		"Shield Assault" : 1,
		"Steadfast Ward" : 1,
		"Bone Shield" : 1,
};


window.AddEsoSkillCostMods = function(modValues, newMods)
{
	if (newMods == null) return;
	
	if (Array.isArray(newMods))
	{
		for (var i of newMods)
		{
			var value = parseFloat(i);
			if (!isNaN(value)) modValues.push(value);
		}
	}
	else
	{
		var value = parseFloat(newMods);
		if (!isNaN(value)) modValues.push(value);
	}
}


window.ComputeEsoCostModFactor = function (costMods)
{
	var factor = 1;
	
	if (costMods == null) return factor;
	
	for (var mod of costMods)
	{
		factor = Math.ceil(factor * (1 + mod) * 100) / 100;
	}
	
	return factor;
}


window.MakeEsoSkillCostOutputLine = function(value, name, factor, percent)
{
	var output = "";
	
	if (factor == 0)
	{
		if (percent == "%") value *= 100;
		
		if (value > 0)
		{
			output = " + " + Math.round(value) + percent + " " + name;
		}
		else if (value < 0)
		{
			output = " - " + Math.abs(Math.round(value)) + percent + " " + name;
		}
	}
	else
	{
		
		if (value > 1)
		{
			output = " + " + Math.round(value*factor - factor) + percent + " " + name;
		}
		else if (value < 1)
		{
			output = " - " + Math.abs(Math.round(value*factor - factor)) + percent + " " + name;
		}
	}
	
	return output;
}


window.ComputeEsoSkillCostExtra = function (cost, level, inputValues, mechanic, skillData)
{
	if (skillData == null) return cost;
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	
	var costMods = [];
	var baseCost = cost;
	var CPFactor = 1;
	var FlatCost = 0;
	var SkillFactor = 1;
	var CostFactor = 1;
	var output = "";
	var skillLineId = CreateEsoSkillLineId(skillData.skillLine) + "_Cost";
	var skillNameId = CreateEsoSkillLineId(skillData.baseName) + "_Cost";
	var skillNameId2 = CreateEsoSkillLineId(skillData.name) + "_Cost";
	var mechanicText = GetEsoSkillMechanicText(mechanic);
	
		/* DK World in Ruins Passive */
	if (window.g_EsoSkillPoisonSkills && inputValues.PoisonStaminaCost)
	{
		var poisonData = g_EsoSkillPoisonSkills[skillData.abilityId];
		if (poisonData == null) poisonData = g_EsoSkillPoisonSkills[skillData.displayId];
		
		if (poisonData)
		{
			CostFactor *= 1 + inputValues.PoisonStaminaCost;
			AddEsoSkillCostMods(costMods, inputValues.StatHistory.Skill.PoisonStaminaCost);
			output += MakeEsoSkillCostOutputLine(inputValues.PoisonStaminaCost, "Poison Stamina Cost", 100, "%");
		}
	}
	
	if (inputValues.HealingAbilityCost)
	{
		var skillDesc = skillData.description;
		
		for (var i = 0; i < ESO_SKILL_HEALINGMATCHES.length; ++i)
		{
			var matchData = ESO_SKILL_HEALINGMATCHES[i].match;
			
			if (skillDesc.match(matchData) != null)
			{
				CostFactor *= 1 + inputValues.HealingAbilityCost;
				AddEsoSkillCostMods(costMods, inputValues.StatHistory.Set.HealingAbilityCost);
				output += MakeEsoSkillCostOutputLine(inputValues.HealingAbilityCos, "Healing Ability Cost", 100, "%");
				break;
			}
		}
		
	}
	
	if (skillData.skillType != 2 && inputValues.NonWeaponAbilityCost != null && inputValues.NonWeaponAbilityCost != 0) {
		var factor = parseFloat(inputValues.NonWeaponAbilityCost);
		CostFactor *= 1 + factor;
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Set.NonWeaponAbilityCost);
		output += MakeEsoSkillCostOutputLine(factor, "Non-Weapon Ability Cost", 0, "%");
	}
	
	if (inputValues.SkillLineCost != null && inputValues.SkillLineCost['Regular_Ability_Cost'] && skillLineId != "Vampire_Cost")
	{
		var factor = parseFloat(inputValues.SkillLineCost['Regular_Ability_Cost']);
		CostFactor *= 1 + factor;
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Vampire.Regular_Ability_Cost);
		output += MakeEsoSkillCostOutputLine(factor, "Regular Ability Cost", 0, "%");
	}
	
	if (inputValues.SkillLineCost != null && inputValues.SkillLineCost[skillLineId] != null && skillData.type == "Active")
	{
		var SkillLineFactor = parseFloat(inputValues.SkillLineCost[skillLineId]);
		CostFactor *= 1 + SkillLineFactor;
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.SkillCost[skillLineId]);
		output += MakeEsoSkillCostOutputLine(SkillLineFactor, "SkillLine", 0, "%");
	}
		/* Skill line cost modifiers generally do not affect ultimates (manually add ultimates as needed) */
	else if (inputValues.SkillLineCost != null && inputValues.SkillLineCost[skillLineId] != null && skillData.type == "Ultimate")
	{
		if (skillData.skillLine == "Vampire")
		{
			var SkillLineFactor = parseFloat(inputValues.SkillLineCost[skillLineId]);
			CostFactor *= 1 + SkillLineFactor;
			AddEsoSkillCostMods(costMods, inputValues.StatHistory.SkillCost[skillLineId]);
			output += MakeEsoSkillCostOutputLine(SkillLineFactor, "SkillLine", 0, "%");
		}
	} 
	
	if (inputValues.SkillLineCost != null && inputValues.SkillLineCost[skillNameId] != null)
	{
		var SkillLineFactor = parseFloat(inputValues.SkillLineCost[skillNameId]);
		CostFactor *= 1 + SkillLineFactor;
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.SkillCost[skillNameId]);
		output += MakeEsoSkillCostOutputLine(SkillLineFactor, "SkillCost", 0, "%");
	}
	
	if (inputValues.SkillLineCost != null && inputValues.SkillLineCost[skillNameId2] != null && skillNameId != skillNameId2)
	{
		var SkillLineFactor = parseFloat(inputValues.SkillLineCost[skillNameId2]);
		CostFactor *= 1 + SkillLineFactor;
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.SkillCost[skillNameId2]);
		output += MakeEsoSkillCostOutputLine(SkillLineFactor, "SkillCost", 0, "%");
	}
	
	if ((g_EsoSkill_DamageShieldSkills[skillData['name']] != null || g_EsoSkill_DamageShieldSkills[skillData['baseName']] != null) && inputValues.DamageShieldCost != null && inputValues.DamageShieldCost != 0)
	{
		var DSFactor = parseFloat(inputValues.DamageShieldCost);
		CostFactor *= 1 + DSFactor;
		AddEsoSkillCostMods(costMods, inputValues.DamageShieldCost);
		output += MakeEsoSkillCostOutputLine(inputValues.DamageShieldCost, "DamageShieldCost", 100, "%");
	}
	
	if (IsEsoSkillMechanicMagicka(mechanic) && inputValues.MagickaCost != null)
	{
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.CP.MagickaCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Skill.MagickaCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Set.MagickaCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Buff.MagickaCost);
		
		if (inputValues.MagickaCost.Item  != null) FlatCost    += inputValues.MagickaCost.Item;
		if (inputValues.MagickaCost.CP    != null) SkillFactor *= 1 + inputValues.MagickaCost.CP;
		if (inputValues.MagickaCost.Set   != null) SkillFactor *= 1 + inputValues.MagickaCost.Set;
		if (inputValues.MagickaCost.Skill != null) SkillFactor *= 1 + inputValues.MagickaCost.Skill;
		if (inputValues.MagickaCost.Buff  != null) SkillFactor *= 1 + inputValues.MagickaCost.Buff;
	}
	else if (IsEsoSkillMechanicStamina(mechanic) && inputValues.StaminaCost != null)
	{
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.CP.StaminaCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Skill.StaminaCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Set.StaminaCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Buff.StaminaCost);
		
		if (inputValues.StaminaCost.Item  != null) FlatCost    += inputValues.StaminaCost.Item;
		if (inputValues.StaminaCost.CP    != null) SkillFactor *= 1 + inputValues.StaminaCost.CP;
		if (inputValues.StaminaCost.Set   != null) SkillFactor *= 1 + inputValues.StaminaCost.Set;
		if (inputValues.StaminaCost.Skill != null) SkillFactor *= 1 + inputValues.StaminaCost.Skill;
		if (inputValues.StaminaCost.Buff  != null) SkillFactor *= 1 + inputValues.StaminaCost.Buff;
	}
	else if (IsEsoSkillMechanicUltimate(mechanic) && inputValues.UltimateCost != null)
	{
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.CP.UltimateCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Skill.UltimateCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Set.UltimateCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Buff.UltimateCost);
		
		//if (inputValues.UltimateCost.CP    != null) CPFactor    += inputValues.UltimateCost.CP;		//Old?
		if (inputValues.UltimateCost.Item  != null) FlatCost    += inputValues.UltimateCost.Item;
		if (inputValues.UltimateCost.CP    != null) SkillFactor *= 1 + inputValues.UltimateCost.CP;
		if (inputValues.UltimateCost.Skill != null) SkillFactor *= 1 + inputValues.UltimateCost.Skill;
		if (inputValues.UltimateCost.Set   != null) SkillFactor *= 1 + inputValues.UltimateCost.Set;
		if (inputValues.UltimateCost.Buff  != null) SkillFactor *= 1 + inputValues.UltimateCost.Buff;
	}
	else if (IsEsoSkillMechanicHealth(mechanic) && inputValues.HealthCost != null)
	{
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.CP.HealthCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Skill.HealthCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Set.HealthCost);
		AddEsoSkillCostMods(costMods, inputValues.StatHistory.Buff.HealthCost);
		
		if (inputValues.HealthCost.Item  != null) FlatCost    +=  inputValues.HealthCost.Item;
		if (inputValues.HealthCost.CP    != null) SkillFactor *= 1 + inputValues.HealthCost.CP;
		if (inputValues.HealthCost.Skill != null) SkillFactor *= 1 + inputValues.HealthCost.Skill;
		if (inputValues.HealthCost.Set   != null) SkillFactor *= 1 + inputValues.HealthCost.Set;
		if (inputValues.HealthCost.Buff  != null) SkillFactor *= 1 + inputValues.HealthCost.Buff;
	}
	
	output += MakeEsoSkillCostOutputLine(CPFactor, "CP", 100, "%");
	output += MakeEsoSkillCostOutputLine(FlatCost, "Flat", 0, "");
	output += MakeEsoSkillCostOutputLine(SkillFactor, "Skill", 100, "%");
	
	SkillFactor *= CostFactor; 
	
	var newFactor = ComputeEsoCostModFactor(costMods);
	SkillFactor = newFactor;
	
	cost = Math.trunc((cost * CPFactor + FlatCost) * SkillFactor);
	if (cost < 0) cost = 0;
	
	if (output == "") 
		output = " (unmodified)";
	else
		output += " = " + cost + " Final";
	
	skillData.rawOutput[mechanicText + " Ability Cost"] = "" + baseCost + " Base " + output;
	skillData.modifiedCost = cost;
	return cost;
}


window.ComputeEsoSkillCost = function (maxCost, level, inputValues, mechanic, skillData)
{
	if (!g_SkillUseUpdate10Cost) return ComputeEsoSkillCostOld(maxCost, level, inputValues, skillData)
	if (inputValues == null) inputValues = GetEsoSkillInputValues();
	
	var cost = parseInt(maxCost);
	
	if (IsEsoSkillMechanicMagicka(mechanic) || IsEsoSkillMechanicStamina(mechanic) || IsEsoSkillMechanicHealth(mechanic))
	{
		if (maxCost == 0) return 0;
		if (level == null) level = inputValues.EffectiveLevel;
		if (level < 1) level = 1;
		if (level >= 66) level = 66;
		
		cost = Math.round(cost * level / 72.0 + cost / 12.0);
		if (cost < 0) cost = 0;
	}
	
	return ComputeEsoSkillCostExtra(cost, level, inputValues, mechanic, skillData);
}


window.ComputeEsoSkillCostOld = function (maxCost, level, inputValues, skillData)
{
	if (inputValues == null) inputValues = GetEsoSkillInputValues();
	
	var cost = parseInt(maxCost);
	
	if (skillData != null && (IsEsoSkillMechanicMagicka(skillData.mechanic) || IsEsoSkillMechanicStamina(skillData.mechanic)))
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
	
	return ComputeEsoSkillCostExtra(cost, level, inputValues, skillData.mechanic, skillData);
}


 window.UpdateEsoSkillTooltipCost = function()
{	
	if (g_LastSkillId <= 0) return;
	
	$(".esovsSkillTooltipCost").each(function()
	{
		var skillData = g_SkillsData[g_LastSkillId];
		if (skillData == null) return;
		
		var mechanics = skillData['mechanic'].split(',');
		var costs = skillData['cost'].split(',');
		var chargeFreqs = skillData['chargeFreq'].split(',');
		var costTimes;
		var mechanicTimes;
		
		var thisMechanic = parseInt($(this).attr("mechanic"));
		
		if (skillData['mechanicTime'] != null)
			mechanicTimes = skillData['mechanicTime'].split(',');
		else
			mechanicTimes = mechanics;
		
		if (skillData['costTime'] != null)
			costTimes = skillData['costTime'].split(',');
		else
			costTimes = costs;
		
		for (var i = 0; i < mechanics.length; ++i)
		{
			var mechanic = parseInt(mechanics[i]);
			var mechanicTime = parseInt(mechanicTimes[i]);
			var baseCost = parseInt(costs[i]);
			var baseCostTime = parseInt(costTimes[i]);
			var chargeFreq = parseInt(chargeFreqs[i]) / 1000;
			
			if (mechanic == thisMechanic || mechanicTime == thisMechanic)
			{
				var costText = "";
				var costHtml = "";
				
				if (chargeFreq > 0 && baseCostTime > 0)
				{
					costText = ComputeEsoSkillCost(baseCostTime, g_LastSkillInputValues.EffectiveLevel, g_LastSkillInputValues, mechanicTime, skillData);
					costHtml = GetEsoSkillTooltipFormatCostString(mechanicTime, costText, skillData);
					
					costHtml += "<div class='esovsChargeFreq'>&nbsp;/ " + chargeFreq + "s</div>";
					costText += " / " + chargeFreq + "s";
				}
				else if (baseCost > 0)
				{
					costText = ComputeEsoSkillCost(baseCost, g_LastSkillInputValues.EffectiveLevel, g_LastSkillInputValues, mechanic, skillData);
					costHtml = GetEsoSkillTooltipFormatCostString(mechanic, costText, skillData);
				}
				else
				{
					costHtml = "";
				}
				
				$(this).html(costHtml);
			}
		}
		
	});
	
}
 
 
 window.GetEsoSkillCostByMechanic = function(skillId, thisMechanic, inputValues, useHtml = true)
{
	var skillData = g_SkillsData[skillId];
	if (skillData == null) return "";
	
	var passive = skillData['isPassive'];
	if (passive != 0) return "";
	
	if (inputValues == null) inputValues = g_LastSkillInputValues;
	
	var mechanics = skillData['mechanic'].split(',');
	var costs = skillData['cost'].split(',');
	var chargeFreqs = skillData['chargeFreq'].split(',');
	var mechanicTimes;
	var costTimes;
	
	if (skillData['mechanicTime'] != null)
		mechanicTimes = skillData['mechanicTime'].split(',');
	else
		mechanicTimes = mechanics;
	
	if (skillData['costTime'] != null)
		costTimes = skillData['costTime'].split(',');
	else
		costTimes = costs;
	
	var costHtmls = [];
	var costTexts = [];
	
	for (var i = 0; i < mechanics.length; ++i)
	{
		var mechanic = parseInt(mechanics[i]);
		var mechanicTime = parseInt(mechanicTimes[i]);
		var baseCost = parseInt(costs[i]);
		var baseCostTime = parseInt(costTimes[i]);
		var chargeFreq = parseInt(chargeFreqs[i]) / 1000;
		
		if (mechanic != thisMechanic && mechanicTime != thisMechanic) continue;
		
		if (isNaN(baseCost)) baseCost = 0;
		if (isNaN(baseCostTime)) baseCostTime = 0;
		if (isNaN(chargeFreq)) chargeFreq = 0;
		
		if (!IsEsoSkillMechanicUltimate(mechanic) && !IsEsoSkillMechanicMagicka(mechanic) && !IsEsoSkillMechanicStamina(mechanic) && !IsEsoSkillMechanicHealth(mechanic) &&
				!IsEsoSkillMechanicUltimate(mechanicTime) && !IsEsoSkillMechanicMagicka(mechanicTime) && !IsEsoSkillMechanicStamina(mechanicTime) && !IsEsoSkillMechanicHealth(mechanicTime)) continue;
		
		var costText = "";
		var costHtml = "";
		
		if (chargeFreq > 0 && baseCostTime > 0)
		{
			costText = ComputeEsoSkillCost(baseCostTime, inputValues.EffectiveLevel, inputValues, mechanicTime, skillData);
			costHtml = GetEsoSkillTooltipFormatCostString(mechanicTime, costText, skillData);
			
			costHtml += "<div class='esovsChargeFreq'>&nbsp;/ " + chargeFreq + "s</div>";
			costText += " / " + chargeFreq + "s";
		}
		else
		{
			costText = ComputeEsoSkillCost(baseCost, inputValues.EffectiveLevel, inputValues, mechanic, skillData);
			costHtml = GetEsoSkillTooltipFormatCostString(mechanic, costText, skillData);
		}
		
		costHtmls.push(costHtml);
		costTexts.push(costText);
	}
	
	skillData.newCost = costTexts.join(", ");
	skillData.newCostText = costTexts.join(", ");
	skillData.newCostHtml = costHtmls.join(", ");
	
	return useHtml ? skillData.newCostHtml : skillData.newCostText;
 }


window.GetEsoSkillCost = function(skillId, inputValues, useHtml = true)
{
	var skillData = g_SkillsData[skillId];
	if (skillData == null) return "";
	
	var passive = skillData['isPassive'];
	if (passive != 0) return "";
	
	if (inputValues == null) inputValues = g_LastSkillInputValues;
	
	var mechanics = skillData['mechanic'].split(',');
	var costs = skillData['cost'].split(',');
	var chargeFreqs = skillData['chargeFreq'].split(',');
	var mechanicTimes;
	var costTimes;
	
	var costHtmls = [];
	var costTexts = [];
	
	if (skillData['mechanicTime'] != null)
		mechanicTimes = skillData['mechanicTime'].split(',');
	else
		mechanicTimes = mechanics;
	
	if (skillData['costTime'] != null)
		costTimes = skillData['costTime'].split(',');
	else
		costTimes = costs;
	
	for (var i = 0; i < mechanics.length; ++i)
	{
		var mechanic = parseInt(mechanics[i]);
		var mechanicTime = parseInt(mechanicTimes[i]);
		var baseCost = parseInt(costs[i]);
		var baseCostTime = parseInt(costTimes[i]);
		var chargeFreq = parseInt(chargeFreqs[i]) / 1000;
		
		if (isNaN(baseCost)) baseCost = 0;
		if (isNaN(baseCostTime)) baseCostTime = 0;
		if (isNaN(chargeFreq)) chargeFreq = 0;
		
		if (!IsEsoSkillMechanicUltimate(mechanic) && !IsEsoSkillMechanicMagicka(mechanic) && !IsEsoSkillMechanicStamina(mechanic) && !IsEsoSkillMechanicHealth(mechanic) &&
				!IsEsoSkillMechanicUltimate(mechanicTime) && !IsEsoSkillMechanicMagicka(mechanicTime) && !IsEsoSkillMechanicStamina(mechanicTime) && !IsEsoSkillMechanicHealth(mechanicTime)) continue;
		
		var costText = "";
		var costHtml = "";
		
		if (chargeFreq > 0 && baseCostTime > 0)
		{
			costText = ComputeEsoSkillCost(baseCostTime, inputValues.EffectiveLevel, inputValues, mechanicTime, skillData);
			costHtml = GetEsoSkillTooltipFormatCostString(mechanicTime, costText, skillData);
			
			costHtml += "<div class='esovsChargeFreq'>&nbsp;/ " + chargeFreq + "s</div>";
			costText += " / " + chargeFreq + "s";
		}
		else
		{
			costText = ComputeEsoSkillCost(baseCost, inputValues.EffectiveLevel, inputValues, mechanic, skillData);
			costHtml = GetEsoSkillTooltipFormatCostString(mechanic, costText, skillData);
		}
		
		costHtmls.push(costHtml);
		costTexts.push(costText);
	}
	
	skillData.newCost = costTexts.join(", ");
	skillData.newCostText = costTexts.join(", ");
	skillData.newCostHtml = costHtmls.join(", ");
	
	return useHtml ? skillData.newCostHtml : skillData.newCostText;
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
	
	return GetEsoSkillDurationData(skillData, inputValues);
}


window.GetEsoSkillDurationData = function(skillData, inputValues)
{
	var modDuration = 0;
	
	if (skillData == null) return "";
	
	var displayId = skillData['displayId'];
	var newDuration = +skillData.duration;
	
	if (skillData.isToggle == 1) return "Toggle";
	if (skillData.duration == 0) return 0;
	
	if (inputValues == null) inputValues = g_LastSkillInputValues;
	
	if (inputValues.ElfBaneDuration && (g_EsoSkillElfBaneSkills[skillId] || g_EsoSkillElfBaneSkills[displayId]))
	{
		var origDuration = newDuration;
		newDuration += inputValues.ElfBaneDuration*1000;
		skillData.rawOutput["Elf Bane Duration"] = "" + (Math.floor(origDuration/100)/10) + " + " + inputValues.ElfBaneDuration + " secs = " + (Math.floor(newDuration/100)/10) + " secs";
	}
	
	if (inputValues.SkillDuration && inputValues.SkillDuration[skillData.baseName] != null)
	{
		modDuration = +inputValues.SkillDuration[skillData.baseName];
		
		if (modDuration >= 1)
		{
			newDuration = newDuration + modDuration*1000;
			skillData.rawOutput["Duration"] = "" + (Math.floor(skillData.duration/100)/10) + " Base + " + modDuration + " secs = " + (Math.floor(newDuration/100)/10) + " secs";
		}
		else
		{
			newDuration = Math.floor(newDuration * (1 + modDuration));
			skillData.rawOutput["Duration"] = "" + (Math.floor(skillData.duration/100)/10) + " Base x " + Math.floor(modDuration*100) + "% = " + (Math.floor(newDuration/100)/10) + " secs";
		}
	}
	
		// Modify Elude duration based on Medium Armor worn
	if (skillData.baseAbilityId == 29556 && skillData.name == "Elude" && inputValues.MediumArmor > 0)
	{
		newDuration += skillData.a1 * inputValues.MediumArmor * 1000;
	}
	
	skillData.newDuration = newDuration;
	return newDuration;
}


window.UpdateEsoSkillCost = function(skillId, costElement, inputValues)
{
	var mechanic = costElement.attr("mechanic");
	var costStr = "";
	
	if (mechanic == null || mechanic == "")
	{
		costStr = GetEsoSkillCost(skillId, inputValues, true);
	}
	else
	{
		costStr = GetEsoSkillCostByMechanic(skillId, mechanic, inputValues, true);
	}
	
	costElement.html(costStr);
}


window.UpdateEsoSkillDuration = function(skillId, durationElement, inputValues)
{
	var duration = GetEsoSkillDuration(skillId, inputValues);
	var durationStr = "";
	
	if (duration > 0)
		durationStr = "" + Math.round(duration/100)/10 + " seconds";
	else if (isNaN(duration))
		durationStr = duration;
	
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
	
	if ($(this).hasClass("esovsCraftedSkill")) return;
	
	UpdateEsoSkillDescription(skillId, $(element), g_LastSkillInputValues, false);
}


window.UpdateEsoAllSkillDescription = function()
{
	var inputValues = GetEsoSkillInputValues();
	$(".esovsSkillContentBlock:visible").find(".esovsAbilityBlockDesc").each(UpdateEsoSkillDescription_ForEach);
}


window.UpdateEsoAllSkillCost = function(onlyVisible)
{
	var inputValues = GetEsoSkillInputValues();
	
	if (onlyVisible == null) onlyVisible = true;
	
	if (onlyVisible)
		$(".esovsSkillContentBlock:visible").find(".esovsAbilityBlockCost").each(UpdateEsoSkillCost_ForEach);
	else
		$(".esovsSkillContentBlock").find(".esovsAbilityBlockCost").each(UpdateEsoSkillCost_ForEach);
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
	
	if (IsEsoSkillMechanicHealth(type)) // Health
	{
		output += srcString + " = " + a + " Health " + cOp + " " + c;
		typeString = "Health";
	}
	else if (IsEsoSkillMechanicMagicka(type)) // Magicka
	{
		output += srcString + " = " + a + " Magicka " + bOp + " " + b + " SpellDamage " + cOp + " " + c;
		typeString = "Magicka";
		ratio = (b/a).toFixed(2);
	}
	else if (IsEsoSkillMechanicStamina(type)) // Stamina
	{
		output += srcString + " = " + a + " Stamina " + bOp + " " + b + " WeaponDamage " + cOp + " " + c;
		typeString = "Stamina";
		ratio = (b/a).toFixed(2);
	}
	else if (IsEsoSkillMechanicUltimate(type)) // Ultimate
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
	else if (type == -56 || type == 4) // Spell + Weapon Damage
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
	else if (type == -69)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " BoneTyrantSkills";
		typeString = "Bone Tyrant Slotted";
	}
	else if (type == -70)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " GraveLordSkills";
		typeString = "Grave Lord Slotted";
	}
	else if (type == -71)
	{
		output += srcString + " = " + a + " SpellDamage " + bOp + " " + b + "    (Capped at " + c + "%)";
		typeString = "Spell Damage Capped";
	}
	else if (type == -72)
	{
		output += srcString + " = " + a + " Magicka " + bOp + " " + b + " WeaponDamage " + cOp + " " + c;
		typeString = "Magicka and Weapon Damage";
		ratio = (b/a).toFixed(2);
	}
	else if (type == -73)
	{
		output += srcString + " = " + a + " Magicka " + bOp + " " + b + " SpellDamage    (Capped at " + c + "%)";
		typeString = "Magicka Capped";
	}
	else if (type == -74)
	{
		output += srcString + " = " + a + " WeaponPower " + cOp + " " + c;
		typeString = "Weapon Power";
	}
	else if (type == -75)
	{
		output += srcString + " = Constant"
		typeString = "Constant";
	}
	else if (type == -76)
	{
		output += srcString + " = max(" + a + " Spell Damage, " + b + " Health) "+ cOp + " " + c;
		typeString = "Health or Spell Damage";
	}
	else if (type == -79)
	{
		output += srcString + " = max(" + a + " Spell Damage + " + b + " Weapon Damage, "+ cOp + " " + c + "Health)";
		typeString = "Health or Spell/Weapon Damage";
	}
	else if (type == -77)
	{
		output += srcString + " = " + a + " MaxResist " + cOp + " " + c;
		typeString = "Max Resistance";
	}
	else if (type == -80)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " HeraldoftheTomeSkills";
		typeString = "Herald of the Tome Slotted";
	}
	else if (type == -81)
	{
		a = Math.round(a);
		output += srcString + " = " + a + " SoldierofApocryphaSkills";
		typeString = "Soldier of Apocrypha Slotted";
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


window.GetEsoSkillCoefContentHtml = function(skillId)
{
	if (USE_V2_TOOLTIPS && window.g_EsoSkillHasV2Tooltips && window.GetEsoSkillCoefContentHtml2) return GetEsoSkillCoefContentHtml2(skillId);
	
	var skillData = g_SkillsData[skillId];
	if (skillData == null) return "No known skill coefficients.";
	//if (skillData.isCrafted) return GetEsoCraftedSkillCoefContentHtml(skillId);
	if (skillData['numCoefVars'] <= 0) return "No known skill coefficients.";
	
	var output = "";
	
	var numCoefVars = skillData['numCoefVars'];
	var skillDesc = EsoConvertDescToHTMLClass(skillData['coefDescription'], 'esovsBold'); 
	output += "Showing " + numCoefVars + " known skill coefficients:<p />";
		
	for (var i = 1; i <= MAX_SKILL_COEF; ++i)
	{
		output += GetEsoSkillCoefDataHtml(skillData, i);
	}
	
	output += "<div class='esovsSkillCoefDesc'>" + skillDesc + "</div>";
	return output;
}


window.UpdateEsoSkillCoefData = function(skillId)
{
	var skillCoefElement = $("#esovsSkillCoefContent");
	
	if (skillId == null) skillId = g_LastSkillId;
	
	var output = GetEsoSkillCoefContentHtml(skillId);
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
	
	table = 'minedSkills';
	if (g_SkillsVersion != '') table += g_SkillsVersion;
	query = "action=view&record=" + table + "&id=" + skillId;
	
	linkElement.attr("href", "//esoitem.uesp.net/viewlog.php?" + query);
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
	//var startTime = performance.now();
	
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
	
	UpdateEsoSkillTooltipDuration();
	UpdateEsoSkillTooltipCost();
	UpdateEsoSkillTooltipDescription();
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
	UpdateSkillLink();
	
	UpdateEsoHiddenSkillFormValues();
	
	//var diffTime = performance.now() - startTime;
	//console.log("OnChangeEsoSkillData", diffTime);
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
			var abilityBlock = $("#esovsSkillContent .esovsAbilityBlock[skillid=" + id + "]").first();
			var skillLine = abilityBlock.attr("skilline") || "";
			var classType = abilityBlock.attr("classtype") || "";
			
				// Don't search skills hidden in the build editor (class skills)
			if (classType != "")
			{
				var classSkillsBlock = $("#esovsSkillTree .esovsSkillType[skilltypeid='" + classType + "']");
				var classTitleBlock = classSkillsBlock.prev();
				if (classTitleBlock.length == 0 || classTitleBlock.is(":hidden")) continue;
			}
			
				// Don't search skill lines hidden in the build editor (Vampire and Werewolf)
			if (skillLine != "")
			{
				var skillLineBlock = $("#esovsSkillTree .esovsSkillLineTitle[skilllineid='" + skillLine + "']");
				if (skillLineBlock.length == 0 || skillLineBlock.css('display') == "none") continue;
			}
			
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
	var abilityParent = abilityBlock.parent(".esovsAbilityBlockList");
	
	//$(".esovsSearchHighlight").removeClass("esovsSearchHighlight").parent(".esovsAbilityBlockList").slideUp();
	$(".esovsSearchHighlight").removeClass("esovsSearchHighlight");
	
	if (!abilityBlock.is(':visible'))
	{
		$(".esovsAbilityBlockList:visible").slideUp();
		abilityParent.slideDown();
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


window.UpdateSkillCoefHistoryLink = function ()
{
	var linkElement = $("#esovsSkillHistoryLink");
	
	linkElement.attr("href", "//esolog.uesp.net/viewSkillCoef.php?abilityid=" + g_LastSkillId);
}


window.UpdateSkillLink = function ()
{
	var linkElement = $("#esovsLinkBlock");
	var inputValues = GetEsoSkillInputValues();
	var params = "";
	
	if (g_LastSkillId > 0 && g_LastSkillId != 33693) params += "id=" + g_LastSkillId;
	if (inputValues.EffectiveLevel != 66 && inputValues.EffectiveLevel != "CP160") params += "&level=" + inputValues.EffectiveLevel;
	if (inputValues.Health != 20000) params += "&health=" + inputValues.Health;
	if (inputValues.Magicka != 20000) params += "&magicka=" + inputValues.Magicka;
	if (inputValues.Stamina != 20000) params += "&stamina=" + inputValues.Stamina;
	if (inputValues.SpellDamage != 2000) params += "&spelldamage=" + inputValues.SpellDamage;
	if (inputValues.WeaponDamage != 2000) params += "&weapondamage=" + inputValues.WeaponDamage;
	if (inputValues.SpellResist != 11000) params += "&armor=" + inputValues.SpellResist;
	
	if (window.g_SkillsVersion && g_SkillsVersion != "") params += "&version=" + g_SkillsVersion;
	if (g_SkillShowAll) params += "&showall=1";
	
	linkElement.attr("href", "?" + params);
	
	UpdateSkillCoefHistoryLink();
	UpdateEsoHiddenSkillFormValues();
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
	if (origRank == rank) return true;
	
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
	
	$(".esovsSkillTypeTitle:contains('ARCANIST')").hide();
	$(".esovsSkillTypeTitle:contains('DRAGONKNIGHT')").hide();
	$(".esovsSkillTypeTitle:contains('NIGHTBLADE')").hide();
	$(".esovsSkillTypeTitle:contains('SORCERER')").hide();
	$(".esovsSkillTypeTitle:contains('TEMPLAR')").hide();
	$(".esovsSkillTypeTitle:contains('WARDEN')").hide();
	$(".esovsSkillTypeTitle:contains('NECROMANCER')").hide();
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
		//RemoveSkillBarAbility(origSkillId1);
		//RemoveSkillBarAbility(origSkillId2);
		ChangeEsoSkillBarAbility(origSkillId1, abilityId);
		ChangeEsoSkillBarAbility(origSkillId2, abilityId);
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
	
	if (IsEsoSkillFree(origAbilityId)) PurchaseEsoSkill(origAbilityId);
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
		var skillId1 = g_EsoSkillPassiveData[skillId].abilityId;
		
		if (skill == null) continue;
		
		if (skill.raceType != "") 
		{
			deleteSkillIds.push(skillId);
			if (skillId != skillId1) deleteSkillIds.push(skillId1);
		}
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


window.EnableEsoRaceSkills = function(raceName, purchaseAll)
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
	
	var skillElements = $("#" + raceId).children(".esovsAbilityBlock");

	skillElements.each(function() {
		var skillId = $(this).attr("skillid");
		var lastSkill = $(this).next(".esovsAbilityBlockList").children(".esovsAbilityBlock").last();
		var lastSkillId = lastSkill.attr("skillid");
		
		if (purchaseAll || ESO_FREE_PASSIVES[lastSkillId]) PurchaseEsoSkill(lastSkillId);
	});	

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
	
	RemoveSkillBarAbility(sourceAbilityId, skillBar);
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
	
	if (oldSrc != null) 
	{
		currentImage.attr("src", oldSrc);
		currentImage.removeAttr("oldsrc");
	}
	
	console.log("OnSkillBarDroppableOut", currentImage.attr("skillIndex"));
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
	
	//console.log("OnSkillBarDroppableOver", currentImage.attr("skillIndex"));
}


window.OnSkillBarDraggableStart = function (event, ui) 
{ 
	var $this = $(this);
	var src = $this.attr("src");
	var abilityId = $this.attr("skillid");
	
	//if (abilityId == null || abilityId <= 0) return false;
	
	$(".ui-draggable-dragging").addClass('esovsSkillDraggableBad').addClass('esovsSkillDraggable');
	
	$("#esovsPopupSkillTooltip").hide();
	//console.log("OnSkillBarDraggableStart");
	
	//$("#esovsSkillBar").find(".esovsSkillBarIcon").removeAttr("oldsrc");
}


window.OnSkillBarDroppableAccept = function (draggable)
{
	var $this = $(this);
	draggable = $(draggable);
	
	//console.log("OnSkillBarAccept", $this, draggable, realDraggable);
	
	var sourceAbilityId = -1;
	var sourceSkillIndex = -1;
	var skillBarIndex = -1;
	
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
		skillBarIndex = parseInt(barImage.attr("skillbar"));
	}
	else
	{
		return false;
	}
	
	var skillData = g_SkillsData[sourceAbilityId];
	if (skillData == null) return false;
	
		/* Werewolf active skills only allowed on the werewolf skill bar */
	if (skillData.skillLine == "Werewolf")
	{
				// In update 29 Werewolf active skills are allowed on any bar and their passive effect seems to work.
		//if (skillData.type != "Ultimate" && skillBarIndex != 4) return false;
	} 
	
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
	var skillBarParent = $this.parent(".esovsSkillBar"); 
		
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
		var element = g_EsoSkillDestructionElement;
		if (!skillBarParent.hasClass("esovsSkillBarHighlight")) element = g_EsoSkillDestructionOffHandElement;
		
		//console.log("Destruction skill swap", sourceAbilityId);
		
		if (g_EsoSkillDestructionData[sourceAbilityId][element] != null)
		{
			sourceAbilityId = g_EsoSkillDestructionData[sourceAbilityId][element];
			
			if (g_SkillsData[sourceAbilityId] != null)
			{
				sourceIconUrl = "//esoicons.uesp.net" + g_SkillsData[sourceAbilityId]['icon'];
				sourceIconUrl.replace("\.dds", ".png");
			}
			
			//console.log("Switched destruction skill to element", element, sourceAbilityId);
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


window.ChangeEsoSkillBarAbility = function(oldAbilityId, newAbilityId, skillBar)
{
	var skillData = g_SkillsData[newAbilityId];
	if (skillData == null) return;
	
	if (skillBar == null)
	{
		ChangeEsoSkillBarAbility(oldAbilityId, newAbilityId, 1);
		ChangeEsoSkillBarAbility(oldAbilityId, newAbilityId, 2);
		ChangeEsoSkillBarAbility(oldAbilityId, newAbilityId, 3);
		ChangeEsoSkillBarAbility(oldAbilityId, newAbilityId, 4);
		return;
	}
	
	var skillBarIcons  = $("#esovsSkillBar .esovsSkillBar[skillbar='" + skillBar + "']").find(".esovsSkillBarIcon[skillid='" + oldAbilityId + "']");
	var skillBarIcons2 = $("#esovsSkillBar .esovsSkillBar[skillbar='" + skillBar + "']").find(".esovsSkillBarIcon[origskillid='" + oldAbilityId + "']");
	
	var iconUrl = "//esoicons.uesp.net" + skillData.icon;
	iconUrl.replace("\.dds", ".png");
	
	skillBarIcons.attr("skillid", newAbilityId);
	skillBarIcons.attr("src", iconUrl);
	
	skillBarIcons2.attr("skillid", newAbilityId);
	skillBarIcons2.attr("src", iconUrl);
	
	if (window.EsoBuildCombatChangeSkillId) EsoBuildCombatChangeSkillId(oldAbilityId, newAbilityId);
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
	skillBars1.attr("src", "//esoicons.uesp.net/blank.png");
	skillBars1.attr("draggable", "true");
	
	skillBars2.attr("skillid", "0");
	skillBars2.attr("origskillid", "0");
	skillBars2.attr("src", "//esoicons.uesp.net/blank.png");
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
	/*
	g_EsoSkillPassiveData = {};
	g_EsoSkillActiveData = {};
	g_EsoSkillPointsUsed = 0;
	
	g_EsoSkillUpdateEnable = false;
	
	$("#esovsSkillBar .esovsSkillBarIcon").attr("skillid", "0").
		attr("src", "//esoicons.uesp.net/blank.png").
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
	UpdateEsoSkillTotalPoints(); */
	
	g_EsoSkillUpdateEnable = false;
	
	$("#esovsSkillContent .esovsSkillContentBlock").each(function(i, e) {
		var skillLine = $(this).attr("id");
		EsoResetSkillLine(skillLine, true);
	});
	
	g_EsoSkillUpdateEnable = true;
	g_EsoSkillActiveData = {};
	g_EsoSkillPointsUsed = 0;
	
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
	var skillType = $(this).parent(".esovsSkillContentBlock").attr("skilltype");
	
	g_EsoSkillUpdateEnable = false;
	
	skillElements.each(function() {
		var skillId = $(this).attr("skillid");
		ResetEsoPurchasedSkill(skillId);
	});
	
	if (skillType == "Racial") RemoveEsoRaceSkillsFromPassiveData();
	
	g_EsoSkillUpdateEnable = true;
	UpdateEsoSkillTotalPoints();
}


window.EsoResetSkillLine = function (skillLine, noUpdate)
{
	var skillLineId = CreateEsoSkillLineId(skillLine);
	var skillLineBlock = $("#esovsSkillContent").children("#" + skillLineId);
	var skillElements = skillLineBlock.children(".esovsAbilityBlock").not(".esovsAbilityBlockNotPurchase");
	var skillType = skillLineBlock.attr("skilltype");
	
	if (noUpdate !== true) g_EsoSkillUpdateEnable = false;
	
	skillElements.each(function() {
		var skillId = $(this).attr("skillid");
		ResetEsoPurchasedSkill(skillId);
	});
	
	if (skillType == "Racial") RemoveEsoRaceSkillsFromPassiveData();
	
	if (noUpdate !== true) 
	{
		g_EsoSkillUpdateEnable = true;
		UpdateEsoSkillTotalPoints();
	}
}


window.EsoSkillLog = function ()
{
	if (console == null) return;
	if (console.log == null) return;
	
	console.log.apply(console, arguments);
}


window.OnClickEsoSkillBarIcon = function(e)
{
	e.stopPropagation();
}

window.OnClickDocument = function()
{
	OnLeaveEsoSkillBarIcon();
}


window.CompareScriptByName = function (a, b)
{
	return ('' + a.name).localeCompare(b.name);
};


window.MakeEsoCraftedSkillClassListHtml = function(selectedClassId)
{
	var output = "";
	
	output = "<select class='esovsAbilityScriptClassList'>";
	output += "<option value='7' " + (selectedClassId == 7 ? "selected" : "") + ">Arcanist</option>";
	output += "<option value='1' " + (selectedClassId == 1 ? "selected" : "") + ">Dragonknight</option>";
	output += "<option value='5' " + (selectedClassId == 5 ? "selected" : "") + ">Necromancer</option>";
	output += "<option value='3' " + (selectedClassId == 3 ? "selected" : "") + ">Nightblade</option>";
	output += "<option value='2' " + (selectedClassId == 2 ? "selected" : "") + ">Sorcerer</option>";
	output += "<option value='6' " + (selectedClassId == 6 ? "selected" : "") + ">Templar</option>";
	output += "<option value='4' " + (selectedClassId == 4 ? "selected" : "") + ">Warden</option>";
	
	output += "</select>";
	
	return output;
};


window.MakeEsoCraftedSkillSlotHtml = function(craftedSkill, skillData, slots, slotIndex, selectedScriptId)
{
	output = '<div class="esovsAbilityBlockScript" slotindex="' + slotIndex + '">';
	
	if (slotIndex == 1)
		output += '<div class="esovsAbilityBlockScriptTitle">Focus Script</div>';
	else if (slotIndex == 2)
		output += '<div class="esovsAbilityBlockScriptTitle">Signature Script</div>';
	else if (slotIndex == 3)
		output += '<div class="esovsAbilityBlockScriptTitle">Affix Script</div>';
	else
		output += '<div class="esovsAbilityBlockScriptTitle">Scribing Slot ' + slotIndex + '</div>';
	
	var namesSorted = [];
	var i;
	
	for (i in slots)
	{
		var scriptId = slots[i];
		var scriptData = g_EsoCraftedScripts[scriptId];
		if (scriptData == null) continue;
		namesSorted.push(scriptData);
	}
	
	namesSorted.sort(CompareScriptByName);
	
		// TODO: Sort by name
	for (i in namesSorted)
	{
		//var scriptId = slots[i];
		//var scriptData = g_EsoCraftedScripts[scriptId];
		var scriptData = namesSorted[i];
		if (scriptData == null) continue;
		
		var scriptId = scriptData.id;
		var selectedClass = "";
		var extraStyle = "";
		
		var imgUrl = "//esoicons.uesp.net/" + scriptData['icon'];
		
		if (scriptId == selectedScriptId) 
			selectedClass = "esovsAbilityScriptSelected";
		else
			extraStyle = "display:none;";
		
		var classList = "";
		
		if (scriptId == 31) classList = MakeEsoCraftedSkillClassListHtml(craftedSkill['classId']);
		
		output += '<div class="esovsAbilityScript ' + selectedClass + '" scriptid="' + scriptId + '" style="' + extraStyle + '">';
		output += '<img src="' + imgUrl + '">';
		output += '<div class="esovsAbilityScriptName">' + scriptData['name'] + '</div> ' + classList + " ";
		output += '<div class="esovsAbilityScriptDesc"> -- ' + scriptData['description'] + ' ' + scriptData['hint'] + '</div>';
		output += '</div>';
	}
	
	output += '</div>';
	//output += '<hr/>';
	return output;
};


window.MakeEsoCraftedSkillHtml = function(craftedSkill, skillData)
{
	var output = "";
	
	var abilityId = skillData['id'];
	var craftedId = craftedSkill['id'];
	var skillType = ESO_SKILL_TYPES[skillData['skillType']];
	var skillLine = skillData['skillLine'];
	var raceType = skillData['raceType'];
	var classType = skillData['classType'];
	var iconUrl = "//esoicons.uesp.net/" + skillData['texture'];
	var skillName = craftedSkill['name'];
	var skillCost = skillData['cost'];
	var skillMechanic = skillData['mechanic'];
	var skillDesc = craftedSkill['description'] + ' ' + craftedSkill['hint'];
	var learnedLevel = skillData['learnedLevel'];
	
	skillData['useCraftedDesc'] = true;
	craftedSkill['baseDescription'] = craftedSkill['description'];
	
	if (learnedLevel <= 0) learnedLevel = '';
	
	if (skillMechanic == '' || skillMechanic == 0) 
	{
		skillMechanic = skillData['baseMechanic'];
		skillData['mechanic'] = skillData['baseMechanic'];
	}
	
	var mechanicText = ESO_MECHANIC_TYPES[skillMechanic] ? ESO_MECHANIC_TYPES[skillMechanic] : "";
	skillCost += " " + mechanicText;
	skillData['cost'] = skillCost;
	
	craftedSkill['scriptId1'] = craftedSkill['slots1'][0];
	craftedSkill['scriptId2'] = craftedSkill['slots2'][0];
	craftedSkill['scriptId3'] = craftedSkill['slots3'][0];
	
	//var scriptData1 = craftedSkill['datas'][craftedSkill['scriptId1']];
	//var repSkillData = 
	
	UpdateEsoCraftedSkillData(craftedSkill);
	
	output += '<div class="esovsAbilityBlock esovsAbilityBlockHover esovsCraftedAbility" craftedid="' + craftedId + '" morph="0" skillid="' + abilityId + '" origskillid="' + abilityId + '" rank="4" origrank="4" maxrank="4" isfree="0" abilitytype="Active" skilltype="' + skillType + '" skilline="' + skillLine + '" classtype="' + classType + '" racetype="' + raceType + '">';
	output += '<img loading="lazy" class="esovsAbilityBlockPlus" src="//esolog-static.uesp.net/resources/pointsplus_up.png">';
	
	output += '<div class="esovsAbilityBlockIcon ui-draggable ui-draggable-handle">';
	output += '<img loading="lazy" alt="" src="' + iconUrl + '">';
	output += '<div class="esovsAbilityBlockIconLevel">' + learnedLevel + '</div>';
	output += '</div>';
	
	output += '<div class="esovsAbilityBlockTitle">';
	output += '<div class="esovsAbilityBlockTitleLabel">';
	output += '<div class="esovsAbilityBlockName">' + skillName + '</div>';
	output += '<div mechanic="' + skillMechanic + '" class="esovsAbilityBlockCost esovsCraftedSkill" skillid="' + abilityId + '">' + skillCost + '</div>';
	output += '</div>';
	output += '<div class="esovsAbilityBlockDesc esovsCraftedSkill" skillid="' + abilityId + '">' + skillDesc + '</div>';
	output += '</div>';
	
	output += '</div>';
	
	var selSlot1 = craftedSkill['scriptId1'];
	var selSlot2 = craftedSkill['scriptId2'];
	var selSlot3 = craftedSkill['scriptId3'];
	
	output += '<div class="esovsAbilityBlockList" craftedid="' + craftedId + '" style="display: none;">';
	
	output += MakeEsoCraftedSkillSlotHtml(craftedSkill, skillData, craftedSkill['slots1'], 1, selSlot1);
	output += MakeEsoCraftedSkillSlotHtml(craftedSkill, skillData, craftedSkill['slots2'], 2, selSlot2);
	output += MakeEsoCraftedSkillSlotHtml(craftedSkill, skillData, craftedSkill['slots3'], 3, selSlot3);
	
	output += "</div>";
	
	return output;
};


window.AddEsoCraftedSkills = function()
{
	if (window.g_EsoCraftedSkills == null) return;
	
	$(".esovsCraftedAbility").remove();
	
	for (var craftedId in g_EsoCraftedSkills)
	{
		var craftedSkill = g_EsoCraftedSkills[craftedId];
		var abilityId = parseInt(craftedSkill['abilityId']);
		var skillData = g_SkillsData[abilityId];
		
		if (skillData == null)
		{
			console.log("AddEsoCraftedSkills: Missing skill data!", craftedSkill);
			continue;
		}
		
		var skillLine = skillData['skillLine'];
		var skillLineId = skillLine.replace(/[ '"]/g, '_');;
		var skillBlock = $("#" + skillLineId);
		var firstSkillBlock = skillBlock.children(".esovsAbilityBlock").eq(1);
		
		var html = MakeEsoCraftedSkillHtml(craftedSkill, skillData)
		
		console.log("AddEsoCraftedSkills: Done!", craftedSkill, skillData, skillBlock, firstSkillBlock, html);
		
		firstSkillBlock.before(html);
	}
	
	$('.esovsCraftedAbility.esovsAbilityBlock').click(OnEsoSkillBlockClick);
	$('.esovsCraftedAbility .esovsAbilityBlockPlusSelect').click(OnEsoSkillBlockPlusSelectClick);
	$('.esovsCraftedAbility .esovsAbilityBlockMinusSelect').click(OnEsoSkillBlockMinusSelectClick);
	$('.esovsCraftedAbility .esovsAbilityBlockPlus').click(OnEsoSkillBlockPlusClick);
	$(".esovsCraftedAbility .esovsAbilityBlockSelect").click(OnAbilityBlockPurchase);
	
	$(".esovsCraftedAbility .esovsAbilityBlockIcon").hover(OnHoverEsoIcon, OnLeaveEsoIcon);
	$(".esovsCraftedAbility .esovsAbilityBlockPassiveIcon").hover(OnHoverEsoPassiveIcon, OnLeaveEsoIcon);
	$(".esovsCraftedAbility .esovsSkillBarIcon").hover(OnHoverEsoSkillBarIcon, OnLeaveEsoSkillBarIcon);
	
	$(".esovsAbilityScript").click(OnEsoScriptBlockClick);
	
	$(".esovsAbilityScriptClassList").click(OnEsoScriptClassListClick);
	$(".esovsAbilityScriptClassList").on("change", OnEsoScriptClassListChange);
};


window.OnEsoScriptClassListChange = function(e)
{
	var $this = $(this);
	var classId = $this.val();
	var $parent = $this.parent(".esovsAbilityScript");
	var scriptId = $parent.attr('scriptid');
	var slotIndex = $parent.parent().attr('slotindex');
	var craftedId = $parent.parent().parent().attr('craftedid');
	var abilityId = $parent.parent().parent().prev().attr('skillid');
	
	console.log("OnEsoScriptClassListChange", classId);
	
	EsoViewSkillShowTooltip(g_SkillsData[abilityId]);
	
	if ($parent.hasClass("esovsAbilityScriptSelected"))
	{
		if ($parent.hasClass("esovsAbilityScriptToggled"))
		{
			$parent.siblings(".esovsAbilityScript").slideUp();
			$parent.removeClass("esovsAbilityScriptToggled");
		}
	}
	
	$parent.siblings(".esovsAbilityScript").removeClass("esovsAbilityScriptSelected").removeClass("esovsAbilityScriptToggled");
	$parent.addClass("esovsAbilityScriptSelected");
	$parent.siblings(".esovsAbilityScript").slideUp();
	
	var craftedSkill = g_EsoCraftedSkills[craftedId];
	
	if (craftedSkill)
	{
		craftedSkill["scriptId" + slotIndex] = scriptId;
		craftedSkill["classId"] = classId;
		
		UpdateEsoCraftedSkillData(craftedSkill);
		EsoUpdateSkillTooltip();
		UpdateEsoSkillCoefData();
	}
	
}


window.OnEsoScriptClassListClick = function(e)
{
	e.preventDefault();
	return false;
}


window.OnEsoScriptBlockClick = function(e)
{
	var $this = $(this);
	var scriptId = $this.attr('scriptid');
	var slotIndex = $this.parent().attr('slotindex');
	var craftedId = $this.parent().parent().attr('craftedid');
	var abilityId = $this.parent().parent().prev().attr('skillid');
	
	EsoViewSkillShowTooltip(g_SkillsData[abilityId]);
	
	if ($this.hasClass("esovsAbilityScriptSelected"))
	{
		if ($this.hasClass("esovsAbilityScriptToggled"))
		{
			$this.siblings(".esovsAbilityScript").slideUp();
			$this.removeClass("esovsAbilityScriptToggled");
		}
		else
		{
			$this.siblings(".esovsAbilityScript").slideDown();
			$this.addClass("esovsAbilityScriptToggled");
		}
		
		return;
	}
	
	$this.siblings(".esovsAbilityScript").removeClass("esovsAbilityScriptSelected").removeClass("esovsAbilityScriptToggled");
	$this.addClass("esovsAbilityScriptSelected");
	$this.siblings(".esovsAbilityScript").slideUp();
	
	var craftedSkill = g_EsoCraftedSkills[craftedId];
	
	if (craftedSkill)
	{
		craftedSkill["scriptId" + slotIndex] = scriptId;
		UpdateEsoCraftedSkillData(craftedSkill);
		EsoUpdateSkillTooltip();
		UpdateEsoSkillCoefData();
	}
};


window.UpdateEsoCraftedSkillData = function(craftedSkill)
{
	var scriptData1 = craftedSkill['datas'][craftedSkill['scriptId1']] ? craftedSkill['datas'][craftedSkill['scriptId1']] : {};
	var scriptData2 = craftedSkill['datas'][craftedSkill['scriptId2']] ? craftedSkill['datas'][craftedSkill['scriptId2']] : {};
	var scriptData3 = craftedSkill['datas'][craftedSkill['scriptId3']] ? craftedSkill['datas'][craftedSkill['scriptId3']] : {};
	
	var classId = craftedSkill['classId'];
	if (classId == null || classId <= 0) classId = 1;
	
	var repId = scriptData1['abilityId'];
	if (repId <= 0) repId = craftedSkill.abilityId;
	
	var skillData = g_SkillsData[craftedSkill.abilityId];
	var skillData1 = g_SkillsData[repId];
	
	if (craftedSkill['scriptId2'] == 31)
	{
		scriptData2 = craftedSkill['datas'][classId * 1000];
		if (scriptData2 == null) scriptData2 = {};
	}
	
	var desc1 = scriptData1['description'] ? scriptData1['description'] : "";
	var desc2 = scriptData2['description'] ? scriptData2['description'] : "";
	var desc3 = scriptData3['description'] ? scriptData3['description'] : "";
	
	craftedSkill['description'] = craftedSkill['baseDescription'] + "\n" + desc1 + "\n" + desc2 + "\n" + desc3;
	craftedSkill['craftDesc1'] = desc1;
	craftedSkill['craftDesc2'] = desc2;
	craftedSkill['craftDesc3'] = desc3;
	
	if (skillData)
	{
		skillData['useCraftedDesc'] = true;
		skillData['craftAbilityId'] = repId;
		skillData['craftName'] = scriptData1['name'];
		skillData['craftDesc1'] = desc1;
		skillData['craftDesc2'] = desc2;
		skillData['craftDesc3'] = desc3;
		skillData['craftClassId'] = classId;
		skillData['craftId'] = craftedSkill['id'];
		skillData['scriptId1'] = craftedSkill['scriptId1'];
		skillData['scriptId2'] = craftedSkill['scriptId2'];
		skillData['scriptId3'] = craftedSkill['scriptId3'];
	}
	
	if (skillData1)
	{
		skillData1['useCraftedDesc'] = true;
		skillData1['craftAbilityId'] = repId;
		skillData1['craftName'] = scriptData1['name'];
		skillData1['craftDesc1'] = desc1;
		skillData1['craftDesc2'] = desc2;
		skillData1['craftDesc3'] = desc3;
		skillData1['craftClassId'] = classId;
		skillData1['craftId'] = craftedSkill['id'];
		skillData1['scriptId1'] = craftedSkill['scriptId1'];
		skillData1['scriptId2'] = craftedSkill['scriptId2'];
		skillData1['scriptId3'] = craftedSkill['scriptId3'];
	}
	
	return craftedSkill['description'];
};


window.UpdateEsoHiddenSkillFormValues = function()
{
	g_SkillShowAll = $("#esovsInputShowAll").is(":checked") ? 1 : 0;
	$("#evsHiddenShowAll").val(g_SkillShowAll);
	$("#evsHiddenHighlightId").val(g_LastSkillId);
	$("#evsHiddenLevel").val($("#esovsInputLevel").val());
	$("#evsHiddenHealth").val($("#esovsInputHealth").val());
	$("#evsHiddenMagicka").val($("#esovsInputMagicka").val());
	$("#evsHiddenStamina").val($("#esovsInputStamina").val());
	$("#evsHiddenArmor").val($("#esovsInputArmor").val());
	$("#evsHiddenSpellDamage").val($("#esovsInputSpellDamage").val());
	$("#evsHiddenWeaponDamage").val($("#esovsInputWeaponDamage").val());
}


window.OnEsoVersionFormSubmit = function(e)
{
	var $this = $(this);
	
	//console.log("OnEsoVersionFormSubmit");
	UpdateEsoHiddenSkillFormValues();
	
	var level = $("#evsHiddenLevel").val();
	var highlightId = parseInt($("#evsHiddenHighlightId").val());
	var health = parseInt($("#evsHiddenHealth").val());
	var magicka = parseInt($("#evsHiddenMagicka").val());
	var stamina = parseInt($("#evsHiddenStamina").val());
	var spellDamage = parseInt($("#evsHiddenSpellDamage").val());
	var weaponDamage = parseInt($("#evsHiddenWeaponDamage").val());
	var armor = parseInt($("#evsHiddenArmor").val());
	var showAll = parseInt($("#evsHiddenShowAll").val());
	var displayType = $("#evsHiddenDisplay").val();
	var debug = parseInt($("#evsHiddenDebug").val());
	
	if (level == 66 || level == "CP160") $("#evsHiddenLevel").prop("disabled", true);
	if (highlightId <= 0 || highlightId == 33963) $("#evsHiddenHighlightId").prop("disabled", true);
	if (health == 20000) $("#evsHiddenHealth").prop("disabled", true);
	if (magicka == 20000) $("#evsHiddenMagicka").prop("disabled", true);
	if (stamina == 20000) $("#evsHiddenStamina").prop("disabled", true);
	if (spellDamage == 2000) $("#evsHiddenSpellDamage").prop("disabled", true);
	if (weaponDamage == 2000) $("#evsHiddenWeaponDamage").prop("disabled", true);
	if (armor == 11000) $("#evsHiddenArmor").prop("disabled", true);
	if (showAll == 0) $("#evsHiddenShowAll").prop("disabled", true);
	if (displayType == "summary") $("#evsHiddenDisplay").prop("disabled", true);
	if (debug == 0) $("#evsHiddenDebug").prop("disabled", true);
	
	return true;
}


window.OnEsoInputFormDefault = function()
{
	$("#esovsControlLevel").val(66);
	$("#esovsInputLevel").val("CP160");
	$("#esovsControlHealth").val("20000");
	$("#esovsInputHealth").val("20000");
	$("#esovsControlMagicka").val("20000");
	$("#esovsInputMagicka").val("20000");
	$("#esovsControlStamina").val("20000");
	$("#esovsInputStamina").val("20000");
	$("#esovsControlSpellDamage").val("2000");
	$("#esovsInputSpellDamage").val("2000");
	$("#esovsControlWeaponDamage").val("2000");
	$("#esovsInputWeaponDamage").val("2000");
	$("#esovsControlArmor").val("11000");
	$("#esovsInputArmor").val("11000");
	$("#esovsInputShowAll").prop("checked", false);
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
	
	$('#esovsControlArmor').on('input', function(e) { OnChangeEsoSkillData.call(this, 'Armor'); });
	$('#esovsInputArmor').on('input', function(e) { OnChangeEsoSkillData.call(this, 'Armor');	});
	
	$('#esovsInputShowAll').on('input', function(e) { UpdateEsoHiddenSkillFormValues(); UpdateSkillLink(); });
	
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
		
		$(".esovsSkillBarIcon").click(OnClickEsoSkillBarIcon);
		$("document").click(OnClickDocument);
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
	
	$(".esovsSkillBarItem").not(".esovsCombatSkillBarItem").droppable({
			drop: OnSkillBarDroppable, 
			accept: OnSkillBarDroppableAccept,
			out: OnSkillBarDroppableOut,
			//over: function(event, ui) { setTimeout(OnSkillBarDroppableOver.bind(this, event, ui), 0); },
			over: OnSkillBarDroppableOver,
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
	
	$("#evsVersionForm").on("submit", OnEsoVersionFormSubmit);
	$("#esovsInputDefault").on("click", OnEsoInputFormDefault);
	
		//TODO: Remove once implemented server side
	AddEsoCraftedSkills();
	
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
	
	OnLeaveEsoSkillBarIcon();
}


$( document ).ready(esovsOnDocReady);