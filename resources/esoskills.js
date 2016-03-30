var g_LastSkillId = 0;
var g_LastSkillInputValues = {};
var MAX_SKILL_COEF = 6;


function EsoConvertDescToHTML(desc)
{
	return EsoConvertDescToHTMLClass(desc, "esovsWhite");
}


function EsoConvertDescToHTMLClass(desc, className)
{
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}([a-zA-Z _0-9\.\+\-\:\;\n\r\t$\?]*)\|r/g, '<div class="' + className + '">$1</div>');
	newDesc = newDesc.replace(/\n/g, '<br />');
	return newDesc;
}


function EsoConvertDescToText(desc)
{
	var newDesc = desc.replace(/\|c[a-fA-F0-9]{6}([a-zA-Z _0-9\.\+\-\:\;\n\r\t$\?]*)\|r/g, '$1');
	//newDesc = newDesc.replace(/\n/g, '<br />');
	return newDesc;
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
	
	element.html("");
	
	g_LastSkillId = skillData.abilityId;
	
	var output = "<div class='esovsSkillTooltip'>\n";
	var safeName = skillData['name'];
	var desc = EsoConvertDescToHTML(skillData['description']);
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
	var area = "";
	var range = "";
	
	if (minRange > 0 && maxRange > 0)
		range = (minRange/100) + " - " + (maxRange/100) + " meters"
	else if (minRange <= 0 && maxRange > 0)
		range = (maxRange/100) + " meters"
	else if (minRange > 0 && maxRange <= 0)
		range = "Under " + (minRange/100) + " meters"
	
	if (angleDistance > 0) area = (radius/100) + " x " + (angleDistance/50) + " meters"
		
	output += "<div class='esovsSkillTooltipTitle'>" + safeName + "</div>\n";
	output += "<img src='resources/skill_divider.png' class='esovsSkillTooltipDivider' />";
	
	if (skillType != 'Passive')
	{
		if (channelTime > 0) 
		{
			output += "<div class='esovsSkillTooltipValue'>" + channelTime + " seconds</div>";
			output += "<div class='esovsSkillTooltipName'>Channel Time</div>";			
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
			output += "<div class='esovsSkillTooltipValue' id='esovsSkillTooltipCost'>" + cost + "</div>";
			output += "<div class='esovsSkillTooltipName'>Cost</div>";			
		}
		
		output += "<img src='resources/skill_divider.png' class='esovsSkillTooltipDivider' />";
	}

	output += "<div id='esovsSkillTooltipDesc' class='esovsSkillTooltipDesc'>" + desc + "</div>\n";
	if (effectLines != "") output += " <div class='esovsSkillTooltipEffectLines'>" + effectLines + "</div>";
	
	if (learnedLevel > 0)
	{
		output += "<div class='esovsSkillTooltipLevel'>Unlocked at Rank " + learnedLevel + "</div>\n";
	}
	
	output += "</div>";
	
	element.html(output);
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
}


function EsoSkillShowSkillLine(skillLine)
{
	var id = skillLine.replace(/[ '"]/g, '_');
	
	$(".esovsSkillContentBlock:visible").hide();
	$("#" + id).show();
}


function OnEsoSkillTypeTitleClick(event)
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
	
	EsoSkillShowSkillLine(skillLine);
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
	UpdateEsoSkillRawData();
	UpdateEsoSkillCoefData();
}


function OnEsoSkillLineTitleClick(event)
{
	var currentSkillLine = $(".esovsSkillLineTitle.esovsSkillLineTitleHighlight");
	
	if ($(this)[0] == currentSkillLine[0]) return;
	currentSkillLine.removeClass("esovsSkillLineTitleHighlight");
	
	$(this).addClass("esovsSkillLineTitleHighlight");
	
	var skillLine = $(this).text();
	
	EsoSkillShowSkillLine(skillLine);
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
	UpdateEsoSkillRawData();
	UpdateEsoSkillCoefData();
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
	else
	{
		return '?';
	}
	
	return Math.round(value);
}


function UpdateEsoSkillDescription(skillId, descElement, inputValues, useHtml)
{
	var skillData = g_SkillsData[skillId];
	if (skillData == null) return;
	
	var coefDesc = skillData['coefDescription'];
	if (coefDesc == null || coefDesc == "") return;
	
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
		if (value == null) continue;
		
		coefDesc = coefDesc.replace(srcString, value);
	}
	
	var effectLines = skillData['effectLines'];
	if (effectLines != "") coefDesc += " <div class='esovsAbilityBlockEffectLines'>" + effectLines + "</div>";	
	
	if (useHtml)
		descElement.html(EsoConvertDescToHTML(coefDesc));
	else
		descElement.html(EsoConvertDescToText(coefDesc));
}


function UpdateEsoSkillTooltipDescription()
{
	if (g_LastSkillId <= 0) return;
	UpdateEsoSkillDescription(g_LastSkillId, $("#esovsSkillTooltipDesc"), GetEsoSkillInputValues(), true)
}


function ComputeEsoSkillCost(baseCost, level)
{
	if (level < 1) level = 1;
	if (level >= 66) return baseCost;
	
	if (level >= 1 && level <= 50) return Math.round(baseCost * level / 65.5367 + baseCost / 10.7466);
	return Math.round(baseCost * level / 110.942 + baseCost / 2.46882);
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
	else
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
	var keys = Object.keys(skillData);
	//keys = keys.sort();
	
	for (var index in keys) 
	{
		var key = keys[index];
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
	if ($.isNumeric(level)) return parseInt(level);
	
	var vetRank = level.match(/^\s*[vV](\d+)/);
	if (vetRank == null) return parseInt(level);
	
	return parseInt(vetRank[1]) + 50;
}


function FormatEsoLevel(level)
{
	if (level <= 0 || level > 66) return level;
	if (level < 50) return level;

	return "v" + (level - 50);
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

	$(".esovsAbilityBlock").first().trigger('click');
	
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
	
	$("#esovsSkillCoefButton").click(OnToggleSkillCoef);
	$("#esovsRawDataButton").click(OnToggleRawDataCoef);
}


$( document ).ready(esovsOnDocReady);	