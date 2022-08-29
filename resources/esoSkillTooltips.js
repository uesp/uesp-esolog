
window.ConvertEsoSkillRawDescToHtml = function(rawDesc)
{
	var newDesc = rawDesc.replace(/&/g, "&amp;")
						.replace(/</g, "&lt;")
						.replace(/>/g, "&gt;")
						.replace(/"/g, "&quot;")
						.replace(/'/g, "&#039;");
	
	newDesc = newDesc.replace(/\|c[a-fA-F0-9]{6}([^|]*)\|r/g, '<div class="esovsBold">$1</div>');
	newDesc = newDesc.replace(/(&lt;&lt;[0-9]+&gt;&gt;)/g, '<div class="esovsBold">$1</div>');
	
	return newDesc;
}


window.CountEsoSkillTooltips = function(abilityId)
{
	var skillData = g_SkillsData[abilityId];
	if (skillData == null || abilityId <= 0) return 0;
	if (skillData.tooltips == null) return 0;
	var count = 0;
	
	for (var i in skillData.tooltips)
	{
		++count;
	}
	
	return count;
}


window.GetEsoSkillDamageTypeText = function(damageType)
{
	switch (parseInt(damageType))
	{
		case 0: return "None";
		case 1: return "Generic";
		case 2: return "Physical";
		case 3: return "Flame";
		case 4: return "Shock";
		case 5: return "Oblivion";
		case 6: return "Frost";
		case 7: return "Earth";
		case 8: return "Magic";
		case 9: return "Drown";
		case 10: return "Disease";
		case 11: return "Poison";
		case 12: return "Bleed";
	}
	
	return "Unknown " + damageType;
}


window.IsEsoSkillTooltipRawTypeDuration = function(rawType)
{
	return (rawType == 20 || rawType == 31 || rawType == 44 || rawType == 48 || rawType == 56 || rawType == 79 || rawType == 93);
}


window.GetEsoSkillTooltipRawTypeText = function(rawType)
{
	switch (parseInt(rawType))
	{
		case 3: return "Name";
		case 8: return "Ability Name";
		case 16: return "Resource";
		
		case 17:
		case 18: return "Damage";
		
		case 19:
		case 57: return "Frequency";
		
		case 20:
		case 31:
		case 48:
		case 56:
		case 79:
		case 93: return "Duration";
		
		case 44: return "Cooldown";
		case 21: return "Damage Reduction";
		case 22: return "Armor";
		case 49: return "Damage/Tick";
		
		case 51:
		case 52: return "Damage Short";
		
		case 53: return "Heal/Tick";
		case 54: return "Resource";
		case 55: return "Stat %";
		case 58: return "Absolute Value";
		case 73: return "Armor %";
		case 90: return "Derived";
		case 91: return "Distance";
		case 92: return "Derived .0%";
		case 96: return "Derived %";
		case 97: return "Derived 10%";
		case 104: return "Derived 25%";
		case 105: return "Derived %";
	}
	
	return "Unknown " + rawType;
}


window.GetEsoSkillTooltipFlagText = function(tooltip)
{
	var flags = [];
	
	if (tooltip == null) return "";
	
	if (tooltip.isDmgShield == 1) flags.push("Damage Shield");
	
	if (tooltip.isDmg == 1)
	{
		if (tooltip.dmgType > 0)
			flags.push(GetEsoSkillDamageTypeText(tooltip.dmgType) + " Damage");
		else
			flags.push("Damage");
	}
	
	if (tooltip.isHeal == 1) flags.push("Heal");
	
	if (tooltip.isAOE == 1)
	{
		flags.push("AOE");
	}
	else
	{
		flags.push("Single Target");
	}
	
	if (tooltip.isDOT == 1)
	{
		if (tooltip.isDmg == 1)
			flags.push("DOT");
		else if (tooltip.isHeal == 1)
			flags.push("HOT");
		else
			flags.push("Over Time");
	}
	else
	{
		if (tooltip.isDmg == 1)
			flags.push("Direct Damage");
		else if (tooltip.isHeal == 1)
			flags.push("Direct Heal");
		else
			flags.push("Direct");
	}
	
	if (tooltip.isMelee == 1) flags.push("Melee");
	if (tooltip.isFlameAOE == 1) flags.push("FlameAOE");
	if (tooltip.isElfBane == 1) flags.push("ElfBane");
	if (tooltip.hasRankMod == 1) flags.push("RankMod");
	
	return flags.join(", ");
}


window.CreateEsoSkillTooltipRawOutputHtml = function(skillData, tooltipIndex)
{
	if (skillData.rawTooltipOutput == null) return "";
	
	var tooltip = skillData.tooltips[tooltipIndex];
	var tooltipData = skillData.rawTooltipOutput[tooltipIndex];
	if (tooltipData == null) return "";
	
	var mods = tooltipData.mods;
	var baseValue = tooltipData.baseValue;
	var extraDamage = tooltipData.extraDamage;
	var finalValue = tooltipData.finalValue;
	var finalDesc = tooltip.finalValue;
	var rankFactor = tooltipData.rankFactor;
	var spellDamage = tooltipData.spellDamage;
	var spellDamageMods = tooltipData.spellDamageMods;
	var weaponDamage = tooltipData.weaponDamage;
	var weaponDamageMods = tooltipData.weaponDamageMods;
	var output = "";
	
	if (rankFactor)
	{
		var rank = skillData.rank % 4;
		if (rank == 0) rank = 4;
		output += "<div class='esovsSkillCoefRowDetail'>Skill Rank " + rank + " Modifier = " + Math.floor(rankFactor*1000 - 1000)/10 + "%</div>";
	}
	
	if (spellDamage && spellDamageMods)
	{
		output += "<div class='esovsSkillCoefRowDetail'>Spell Damage Used = " + spellDamage + " (" + spellDamageMods.join(" + ") + ")</div>";
	}
	
	if (weaponDamage && weaponDamageMods)
	{
		output += "<div class='esovsSkillCoefRowDetail'>Weapon Damage Used = " + weaponDamage + " (" + weaponDamageMods.join(" + ") + ")</div>";
	}
	
	if (tooltip.coefType == -75)
	{
		if (skillData.rawTooltipOutput != null && skillData.rawTooltipOutput[tooltipIndex] != null && skillData.rawTooltipOutput[tooltipIndex].baseValue != null)
			output += "<div class='esovsSkillCoefRowDetail'>Base Value = " + skillData.rawTooltipOutput[tooltipIndex].baseValue;
		else
			output += "<div class='esovsSkillCoefRowDetail'>Base Value = " + baseValue;
	}
	else
	{
		output += "<div class='esovsSkillCoefRowDetail'>Base Value = " + baseValue;
	}
	
	var duration = + tooltip.finalDuration;
	if (isNaN(duration)) duration = + tooltip.duration;
	
	if ((tooltip.rawType == 49 || tooltip.rawType == 53) && duration > 0 && tooltip.tickTime > 0)
	{
		var numTicks = Math.floor((duration + +tooltip.tickTime) / +tooltip.tickTime);
		var totalDamage = Math.floor(baseValue * numTicks);
		output += " x " + numTicks + " Ticks = " + totalDamage + " Overall";
	}
	
	if (finalValue == baseValue || (tooltip.coefType == -75 && parseFloat(finalValue) == parseFloat(baseValue)))
	{
		output += " (unmodified)</div>";
		return output;
	}
	
	output += "</div>";
	
	if (mods && mods.length > 0)
	{
		output += "<div class='esovsSkillCoefRowDetail'>Modifiers: " + mods.join(" + ") + "</div>";
	}
	
	if (extraDamage)
	{
		output += "<div class='esovsSkillCoefRowDetail'>Extra Damage: " + extraDamage + "</div>";
	}
	
	return output;
}


window.CreateEsoSkillCoefContentForIndexHtml = function(skillData, tooltipIndex, includeTooltipData)
{
	var tooltip = skillData.tooltips[tooltipIndex];
	if (tooltip == null) return "";
	if (tooltip.coefType == -1) return "";
	
	var a = parseFloat(tooltip.a).toPrecision(5);
	var b = parseFloat(tooltip.b).toPrecision(5);
	var c = parseFloat(tooltip.c).toPrecision(5);
	var r = parseFloat(tooltip.r).toPrecision(5);
	var bOp = '+';
	var cOp = '+';
	var typeString = "";
	var srcString = "<b>&lt;&lt;" + tooltipIndex + "&gt;&gt;</b>";
	var ratio = 0;
	var flags = GetEsoSkillTooltipFlagText(tooltip);
	var rawTypeText = GetEsoSkillTooltipRawTypeText(tooltip.rawType);
	var finalDesc = tooltip.finalValue;
	var hasOutputInitialLine = false;
	var output = "<div class='esovsSkillCoefRow'>";
	
	if (tooltip.r == 1) r = "1";
	
	if (includeTooltipData && finalDesc)
	{
		if ((g_LastSkillInputValues != null && g_LastSkillInputValues.LightArmor != null) || (tooltip.coefType != -51 && tooltip.coefType != -52 && tooltip.coefType != -53 && tooltip.coefType != -54 && tooltip.coefType != -55))
		{
			output += srcString + " = " + EsoConvertDescToText(finalDesc);
			if (tooltip.coefType == -75) output += " &nbsp; &nbsp; Constant (" + rawTypeText + ")";
			output += "</div>";
			output += "<div class='esovsSkillCoefRowDetail'>";
			srcString = "";
			hasOutputInitialLine = true;
		}
	}
	
	if (b < 0) { bOp = '-'; b = -b; }
	if (c < 0) { cOp = '-'; c = -c; }
	
	switch(parseInt(tooltip.coefType))
	{
	case -2:	// Health
	case 32:	// Update 34
		output += srcString + " = " + a + " Health ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Health";
		break;
	case 0:		// Magicka
	case 1:		// Update 34
		output += srcString + " = " + a + " Magicka ";
		if (b != 0) output += bOp + " " + b + " SpellDamage ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Magicka";
		ratio = (b/a).toFixed(2);
		break;
	case 6:		// Stamina
	case 4:		// Update 34
		output += srcString + " = " + a + " Stamina ";
		if (b != 0) output += bOp + " " + b + " WeaponDamage ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Stamina";
		ratio = (b/a).toFixed(2);
		break;
	case 8:		// Update 34
	case 10:	// Ultimate
		output += srcString + " = " + a + " MaxStat ";
		if (b != 0) output += bOp + " " + b + " MaxDamage ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Ultimate";
		ratio = (b/a).toFixed(2);
		break;
	case  -50:	// Ultimate Soul Tether
		output += srcString + " = " + a + " MaxStat ";
		if (b != 0) output += bOp + " " + b + " SpellDamage ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Ultimate (no weapon damage)";
		ratio = (b/a).toFixed(2);
		break;
	case -51:	// Light Armor
		output += srcString + " = " + a + " LightArmor ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Light Armor #";
		break;
	case -52:	// Medium Armor
		output += srcString + " = " + a + " MediumArmor ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Medium Armor #";
		break;
	case -53:	// Heavy Armor
		output += srcString + " = " + a + " HeavyArmor ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Heavy Armor #";
		break;
	case -54:	// Dagger
		output += srcString + " = " + a + " Dagger ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Dagger #";
		break;
	case -55:	// Armor Types
		output += srcString + " = " + a + " ArmorTypes " ;
		if (c != 0) output += cOp + " " + c;
		typeString = "Armor Type #";
		break;
	case -56:	// Spell + Weapon Damage
	case 4:
		output += srcString + " = " + a + " SpellDamage ";
		if (b != 0) output += bOp + " " + b + " WeaponDamage ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Spell + Weapon Damage";
		break;
	case -57:	// Assassination Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " AssassinSkills";
		typeString = "Assassination Skills Slotted";
		break;
	case -58:	// Fighters Guild Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " FightersGuildSkills";
		typeString = "Fighters Guild Skills Slotted";
		break;
	case -59:	// Draconic Power Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " DraconicPowerSkills";
		typeString = "Draconic Power Skills Slotted";
		break;
	case -60:	// Shadow Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " ShadowSkills";
		typeString = "Shadow Skills Slotted";
		break;
	case -61:	// Siphoning Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " SiphoningSkills";
		typeString = "Siphoning Skills Slotted";
		break;
	case -62:	// Sorcerer Skills Slotted 
		a = Math.round(a);
		output += srcString + " = " + a + " SorcererSkills";
		typeString = "Sorcerer Skills Slotted";
		break;
	case -63:	// Mages Guild Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " MagesGuildSkills";
		typeString = "Mages Guild Skills Slotted";
		break;
	case -64:	// Support Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " SupportSkills";
		typeString = "Support Skills Slotted";
		break;
	case -65:	// Animal Companion Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " AnimalCompanionSkills";
		typeString = "Animal Companion Skills Slotted";
		break;	
	case -66:	// Green Balance Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " GreenBalanceSkills";
		typeString = "Green Balance Skills Slotted";
		break;
	case -67:	// Winters Embrace Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " WintersEmbraceSkills";
		typeString = "Winter's Embrace Slotted";
		break;
	case -68:	// Magicka with Health Cap
		b = Math.round(b * 100);
		output += srcString + " = " + a + " Magicka    (Capped at " + b + "% Health)";
		typeString = "Magicka with Health Cap";
		break;
	case -69:	// Bone Tyrant Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " BoneTyrantSkills";
		typeString = "Bone Tyrant Slotted";
		break;
	case -70:	// Grave Lord Skills Slotted
		a = Math.round(a);
		output += srcString + " = " + a + " GraveLordSkills";
		typeString = "Grave Lord Slotted";
		break;
	case -71:	// Spell Damage Capped
		output += srcString + " = " + a + " SpellDamage ";
		if (b != 0) output += bOp + " " + b;
		output += "    (Capped at " + c + "%)";
		typeString = "Spell Damage Capped";
		break;
	case -72:	// Magicka and Weapon Damage
		output += srcString + " = " + a + " Magicka ";
		if (b != 0) output += bOp + " " + b + " WeaponDamage ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Magicka and Weapon Damage";
		ratio = (b/a).toFixed(2);
		break;
	case -73:	// Magicka Capped
		output += srcString + " = " + a + " Magicka ";
		if (b != 0) output += bOp + " " + b + " SpellDamage ";
		output += "    (Capped at " + c + "%)";
		typeString = "Magicka Capped";
		ratio = (b/a).toFixed(2);
		break;
	case -74:	// Weapon Power?
		output += srcString + " = " + a + " WeaponPower ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Weapon Power";
		break;
	case -75:	// Constant
		typeString = "Constant";
		if (!hasOutputInitialLine)
		{
			if (skillData.rawTooltipOutput != null && skillData.rawTooltipOutput[tooltipIndex] != null && skillData.rawTooltipOutput.finvalValue != null)
				output += srcString + " = " + skillData.rawTooltipOutput[tooltipIndex].finvalValue;
			else
				output += srcString + " = " + tooltip.value;
			
			output += " &nbsp; &nbsp; " + typeString + " (" + rawTypeText + ")";
		}
		break;
	case -76:	// Health or Spell Damage
		output += srcString + " = max(" + a + " SpellDamage, " + b + " Health) ";
		if (c != 0) output += cOp + " " + c;
		typeString = "Health or Spell Damage";
		break;
	case -79:	// Health or Weapon/Spell Damage
		output += srcString + " = max(" + a + " SpellDamage + " + b + " WeaponDamage, " + c + " Health)";
		//if (c != 0) output += cOp + " " + c;
		typeString = "Health or Spell/Weapon Damage";
		break;
	case -77: // Max Resistance
		output += srcString + " = " + a + " MaxResist " + cOp + " " + c;
		typeString = "Max Resistance";
		break;
	case -78:	// Magicka and Light Armor (Health Capped)
		output += srcString + " = " + a + " Magicka " + bOp + " " + b + " LightArmor ";
		c = Math.round(c * 100);
		output += srcString + "    (Capped at " + c + "% Health)";
		typeString = "Magicka and Light Armor (Health Capped)";
		break;
	default:
		output += srcString + " = ?";
		typeString = "Unknown Type " + tooltip.coefType;
		break;
	}
	
	output += "</div>";
	
	if (tooltip.coefType != -75)
	{
		output += "<div class='esovsSkillCoefRowDetail'>" + typeString + " (" + rawTypeText + ")";
		output += ", R<sup>2</sup> = " + r;
		if (ratio != "") output += ", Ratio = " + ratio;
		output += "</div>";
		if (flags != "") output += "<div class='esovsSkillCoefRowDetail'>" + flags + "</div>";
	}
	
	if ((tooltip.rawType == 49 || tooltip.rawType == 53) && tooltip.duration > 0)
	{
		var duration = Math.round(tooltip.duration / 100) / 10;
		var finalDuration = Math.round(tooltip.finalDuration / 100) / 10;
		var startTime = Math.round(tooltip.startTime / 100) / 10;
		var tickLength = Math.round(tooltip.tickTime) / 1000;
		
		if (isNaN(finalDuration)) finalDuration = duration;
		
		output += "<div class='esovsSkillCoefRowDetail'>";
		output += "Duration: " + finalDuration + " secs";
		if (startTime > 0) output += ", Delay: " + startTime + " secs";
		if (tickLength > 0) output += ", Tick: " + tickLength + " secs";
		output += "</div>";
	}
	
	if (tooltip.cooldown > 0)
	{
		var cooldown = Math.round(tooltip.cooldown) / 1000;
		output += "<div class='esovsSkillCoefRowDetail'>";
		output += "Cooldown: " + cooldown + " secs";
		output += "</div>";
	}
	
	if (includeTooltipData && skillData.rawTooltipOutput)
	{
		output += CreateEsoSkillTooltipRawOutputHtml(skillData, tooltipIndex);
	}
	
	return output;
}


window.GetEsoSkillCoefContentHtml2 = function(abilityId)
{
	var output = "";
	var skillData = g_SkillsData[abilityId];
	
	if (skillData == null || abilityId <= 0 || skillData.tooltips == null || skillData.tooltips[1] == null) return "No known skill coefficients.";
	
	var rawDesc = ConvertEsoSkillRawDescToHtml(skillData.rawDescription);
	var numTooltips = CountEsoSkillTooltips(abilityId);
	
	output += "Showing " + numTooltips + " skill coefficients:<p/>";
		
	for (var tooltipIndex = 1; tooltipIndex <= numTooltips; ++tooltipIndex)
	{
		output += CreateEsoSkillCoefContentForIndexHtml(skillData, tooltipIndex, true);
	}
	
	output += "<div class='esovsSkillCoefDesc'>" + rawDesc + "</div>";
	return output;
}


window.GetEsoSkillTooltipWeaponDamage2 = function(tooltip, skillData, inputValues, weaponDamageTypes)
{
	var weaponDamage = inputValues.WeaponDamage;
	var skillWeaponValues = null;
	var isChannel = (skillData.castTime > 0 || skillData.channelTime > 0);
	var skillLine = skillData.skillLine.toLowerCase();
	var skillBaseName = skillData.baseName.toLowerCase();
	
	var damageType = 'base';
	if (tooltip.isDmg == 1) damageType = GetEsoSkillDamageTypeText(tooltip.dmgType).toLowerCase();
	
		// Order of spell/weapon checks: Class, Channel, Maelstrom, Healing, AOE, DOT, Direct, Range, Melee, DirectRange, EnemyTarget
	if (inputValues.SkillWeaponDamage == null)
	{
	}
	else if (skillData.classType != "")
	{
		skillWeaponValues = inputValues.SkillWeaponDamage.Class;
		weaponDamageTypes.push("Class");
	}
	else
	{
		skillWeaponValues = inputValues.SkillWeaponDamage;
	}
	
	if (skillWeaponValues == null)
	{
	}
	else if (isChannel)
	{
		skillWeaponValues = skillWeaponValues.Channel;
		weaponDamageTypes.push("Channel/Cast");
	}
	
	if (inputValues.useMaelstromDamage && tooltip.isDOT == 1 && tooltip.isDmg == 1 && skillWeaponValues != null)
	{
		skillWeaponValues = skillWeaponValues.Maelstrom;
		weaponDamageTypes.push("Maelstrom");
	}
	
	if (tooltip.isHeal == 1 && skillWeaponValues != null)
	{
		skillWeaponValues = skillWeaponValues.Healing;
		weaponDamageTypes.push("Healing");
	}
	
	if (tooltip.isAOE == 1 && skillWeaponValues != null)
	{
		skillWeaponValues = skillWeaponValues.AOE;
		weaponDamageTypes.push("AOE");
	}
	
	if (tooltip.isDOT == 1 && skillWeaponValues != null)
	{
		skillWeaponValues = skillWeaponValues.DOT;
		weaponDamageTypes.push("DOT");
	}
	else if (tooltip.isDOT == 0 && skillWeaponValues != null)
	{
		skillWeaponValues = skillWeaponValues.Direct;
		weaponDamageTypes.push("Direct");
	}
	
	if (tooltip.isMelee == 1 && skillWeaponValues != null)
	{
		skillWeaponValues = skillWeaponValues.Melee;
		weaponDamageTypes.push("Melee");
	}
	else if (tooltip.isMelee == 0 && skillWeaponValues != null)
	{
		skillWeaponValues = skillWeaponValues.Range;
		weaponDamageTypes.push("Ranged");
	}
	
	if (tooltip.isMelee == 0 && tooltip.isDOT == 0 && skillWeaponValues != null)
	{
		skillWeaponValues = skillWeaponValues.DirectRange;
		weaponDamageTypes.push("Direct-Ranged");
	}
	
	if (tooltip.isDmg == 1 && tooltip.isAOE == 0 && skillWeaponValues != null && skillWeaponValues.EnemyTarget != null)
	{
		skillWeaponValues = skillWeaponValues.EnemyTarget;
		weaponDamageTypes.push("Enemy-Target");
	}
	
	if (skillWeaponValues != null)
	{
		var typeWeaponValues = skillWeaponValues.base;
		
		if (skillWeaponValues[skillLine] != null)
		{
			typeWeaponValues = skillWeaponValues[skillLine];
			weaponDamageTypes.push(skillLine);
		}
		else if (skillWeaponValues[skillBaseName] != null)
		{
			typeWeaponValues = skillWeaponValues[skillBaseName];
			weaponDamageTypes.push(skillBaseName);
		}
		
		weaponDamage = typeWeaponValues.base;
		
		if (typeWeaponValues[damageType] != null)
		{
			weaponDamage = typeWeaponValues[damageType];
			if (damageType != 'base') weaponDamageTypes.push(damageType);
		}
		
		if (weaponDamage == null) weaponDamage = inputValues.WeaponDamage;
	}
	
		/* Skill specific weapon damage bonuses */
	if (inputValues.SkillWeaponDamage && inputValues.SkillWeaponDamage.SkillWeaponDamages)
	{
		var skillBonusWeaponDamage = inputValues.SkillWeaponDamage.SkillWeaponDamages[skillData.name];
		if (skillBonusWeaponDamage == null) skillBonusWeaponDamage = inputValues.SkillWeaponDamage.SkillWeaponDamages[skillData.baseName];
		
		if (skillBonusWeaponDamage != null && skillBonusWeaponDamage != 0)
		{
			if (inputValues.SkillWeaponDamage.WeaponDamageFactor) skillBonusWeaponDamage = Math.round(skillBonusWeaponDamage * inputValues.SkillWeaponDamage.WeaponDamageFactor);
			weaponDamage += skillBonusWeaponDamage;
			weaponDamageTypes.push("Bonus Skill");
		}
	}
	
	return weaponDamage;
}


window.GetEsoSkillTooltipSpellDamage2 = function(tooltip, skillData, inputValues, spellDamageTypes)
{
	var spellDamage = inputValues.SpellDamage;
	var skillSpellValues = null;
	var isChannel = (skillData.castTime > 0 || skillData.channelTime > 0);
	var skillLine = skillData.skillLine.toLowerCase();
	var skillBaseName = skillData.baseName.toLowerCase();
	
	var damageType = 'base';
	if (tooltip.isDmg == 1) damageType = GetEsoSkillDamageTypeText(tooltip.dmgType).toLowerCase();
	
		// Order of spell/weapon checks: Class, Channel, Maelstrom, Healing, AOE, DOT, Direct, Range, Melee, DirectRange, EnemyTarget	
	if (inputValues.SkillSpellDamage == null)
	{
	}
	else if (skillData.classType != "")
	{
		skillSpellValues = inputValues.SkillSpellDamage.Class;
		spellDamageTypes.push("Class");
	}
	else
	{
		skillSpellValues = inputValues.SkillSpellDamage;
	}
	
	if (skillSpellValues == null)
	{
	}
	else if (isChannel)
	{
		skillSpellValues = skillSpellValues.Channel;
		spellDamageTypes.push("Channel/Cast");
	}
	
	if (inputValues.useMaelstromDamage && tooltip.isDOT == 1 && tooltip.isDmg == 1 && skillSpellValues != null)
	{
		skillSpellValues = skillSpellValues.Maelstrom;
		spellDamageTypes.push("Maelstrom");
	}
	
	if (tooltip.isHeal == 1 && skillSpellValues != null)
	{
		skillSpellValues = skillSpellValues.Healing;
		spellDamageTypes.push("Healing");
	}
	
	if (tooltip.isAOE == 1 && skillSpellValues != null)
	{
		skillSpellValues = skillSpellValues.AOE;
		spellDamageTypes.push("AOE");
	}
	
	if (tooltip.isDOT == 1 && skillSpellValues != null)
	{
		skillSpellValues = skillSpellValues.DOT;
		spellDamageTypes.push("DOT");
	}
	else if (tooltip.isDOT == 0 && skillSpellValues != null)
	{
		skillSpellValues = skillSpellValues.Direct;
		spellDamageTypes.push("Direct");
	}
	
	if (tooltip.isMelee == 1 && skillSpellValues != null)
	{
		skillSpellValues = skillSpellValues.Melee;
		spellDamageTypes.push("Melee");
	}
	else if (tooltip.isMelee == 0 && skillSpellValues != null)
	{
		skillSpellValues = skillSpellValues.Range;
		spellDamageTypes.push("Ranged");
	}
	
	if (tooltip.isMelee == 0 && tooltip.isDOT == 0 && skillSpellValues != null)
	{
		skillSpellValues = skillSpellValues.DirectRange;
		spellDamageTypes.push("Direct-Ranged");
	}
	
	if (tooltip.isDmg == 1 && tooltip.isAOE == 0 && skillSpellValues != null && skillSpellValues.EnemyTarget != null)
	{
		skillSpellValues = skillSpellValues.EnemyTarget;
		spellDamageTypes.push("Enemy-Target");
	}
	
	if (skillSpellValues != null)
	{
		var typeSpellValues = skillSpellValues.base;
		
		if (skillSpellValues[skillLine] != null)
		{
			typeSpellValues = skillSpellValues[skillLine];
			spellDamageTypes.push(skillLine);
		}
		else if (skillSpellValues[skillBaseName] != null)
		{
			typeSpellValues = skillSpellValues[skillBaseName];
			spellDamageTypes.push(skillBaseName);
		}
		
		spellDamage = typeSpellValues.base;
		
		if (typeSpellValues[damageType] != null)
		{
			spellDamage = typeSpellValues[damageType];
			if (damageType != 'base') spellDamageTypes.push(damageType);
		}
		
		if (spellDamage == null) spellDamage = inputValues.SpellDamage;
	}
	
		/* Skill specific spell damage bonuses */
	if (inputValues.SkillSpellDamage && inputValues.SkillSpellDamage.SkillSpellDamages)
	{
		var skillBonusSpellDamage = inputValues.SkillSpellDamage.SkillSpellDamages[skillData.name];
		if (skillBonusSpellDamage == null) skillBonusSpellDamage = inputValues.SkillSpellDamage.SkillSpellDamages[skillData.baseName];
		
		if (skillBonusSpellDamage != null && skillBonusSpellDamage != 0)
		{	
			if (inputValues.SkillSpellDamage.SpellDamageFactor) skillBonusSpellDamage = Math.round(skillBonusSpellDamage * inputValues.SkillSpellDamage.SpellDamageFactor);
			spellDamage += skillBonusSpellDamage;
			spellDamageTypes.push("Bonus Skill");
		}
	}
	
	return spellDamage;
}


window.MakeEsoSkillTooltipDamageRawOutput = function(rawData)
{
	var output = "";
	
	if (rawData.modDuration    != null && rawData.modDuration    != 0) output += " + " + RoundEsoSkillPercent(rawData.modDuration) + " sec ";
	if (rawData.skillLineDamageDone != null && rawData.skillLineDamageDone != 0) output += " + " + RoundEsoSkillPercent(rawData.skillLineDamageDone*100) + "% Skill Line ";
	if (rawData.skillDamageDone != null && rawData.skillDamageDone != 0) output += " + " + RoundEsoSkillPercent(rawData.skillDamageDone*100) + "% Skill ";
	if (rawData.mainDamageDone != null && rawData.mainDamageDone != 0) output += " + " + RoundEsoSkillPercent(rawData.mainDamageDone*100) + "% " + rawData.damageType;
	if (rawData.aoeDamageDone  != null && rawData.aoeDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.aoeDamageDone*100) + "% AOE";
	if (rawData.singleTargetDamageDone  != null && rawData.singleTargetDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.singleTargetDamageDone*100) + "% SingleTarget";
	if (rawData.directDamageDone  != null && rawData.directDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.directDamageDone*100) + "% Direct";
	if (rawData.dotDamageDone  != null && rawData.dotDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.dotDamageDone*100) + "% DOT";
	if (rawData.channelDamageDone  != null && rawData.channelDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.channelDamageDone*100) + "% Channel";
	if (rawData.bleedDamageDone  != null && rawData.bleedDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.bleedDamageDone*100) + "% Bleed";
	if (rawData.petDamageDone  != null && rawData.petDamageDone  != 0) output += " + " + RoundEsoSkillPercent(rawData.petDamageDone*100) + "% Pet";
	if (rawData.damageDone     != null && rawData.damageDone     != 0) output += " + " + RoundEsoSkillPercent(rawData.damageDone*100) + "% All";
	if (rawData.magickaAbilityDamageDone != null && rawData.magickaAbilityDamageDone != 0) output += " + " + RoundEsoSkillPercent(rawData.magickaAbilityDamageDone*100) + "% Magicka";
	if (rawData.overloadDamage != null && rawData.overloadDamage != 0) output += " + " + RoundEsoSkillPercent(rawData.overloadDamage*100) + "% Overload";
	if (rawData.LADamage != null && rawData.LADamage != 0) output += " + " + RoundEsoSkillPercent(rawData.LADamage*100) + "% LA";
	if (rawData.HADamage != null && rawData.HADamage != 0) output += " + " + RoundEsoSkillPercent(rawData.HADamage*100) + "% HA";
	if (rawData.empower != null && rawData.empower != 0) output += " + " + RoundEsoSkillPercent(rawData.empower*100) + "% Empower";
	if (rawData.skillDotDamage != null && rawData.skillDotDamage != 0) output += " + " + RoundEsoSkillPercent(rawData.skillDotDamage*100) + "% SkillDot";
	if (rawData.vulnerability != null && rawData.vulnerability != 0) output += " + " + RoundEsoSkillPercent(rawData.vulnerability*100) + "% Vulnerability";
	
	if (rawData.skillDirectDamage != null && rawData.skillDirectDamage != 0) output += " + " + RoundEsoSkillPercent(rawData.skillDirectDamage) + " SkillDirect";
	if (rawData.skillLineDirectDamage != null && rawData.skillLineDirectDamage != 0) output += " + " + RoundEsoSkillPercent(rawData.skillLineDirectDamage) + " SkillLineDirect";
	if (rawData.twinSlashInitialDamage != null && rawData.twinSlashInitialDamage != 0) output += " + " + RoundEsoSkillPercent(rawData.twinSlashInitialDamage) + " StingingSlashes";
	if (rawData.flatOverloadDamage != null && rawData.flatOverloadDamage > 0) output += " + " + RoundEsoSkillPercent(rawData.flatOverloadDamage) + " Overload";
	if (rawData.flatBashDamage != null && rawData.flatBashDamage > 0) output += " + " + RoundEsoSkillPercent(rawData.flatBashDamage) + " Flat Bash Damage";
	if (rawData.skillFlatDamage != null && rawData.skillFlatDamage > 0) output += " + " + RoundEsoSkillPercent(rawData.skillFlatDamage) + " Flat";
	
	if (output == "")
		output = "" + rawData.baseDamage + " " + rawData.damageType + " Damage (unmodified)";
	else
		output = "" + rawData.baseDamage + " " + rawData.damageType + " Damage " + output + " = " + rawData.finalDamage + " final";
	
	return output;
}


window.MakeEsoSkillTooltipHealingRawOutput = function(rawData)
{
	var output = "";
	var percent = "";
	
	if (rawData.display == "%") percent = "%";
	
	if (rawData.healDone != null && rawData.healDone != 0) output += " + " + RoundEsoSkillPercent(rawData.healDone*100) + "% " + rawData.healId;
	if (rawData.dotHeal  != null && rawData.dotHeal  != 0) output += " + " + RoundEsoSkillPercent(rawData.dotHeal*100) + "% DOT";
	if (rawData.aoeHeal  != null && rawData.aoeHeal  != 0) output += " + " + RoundEsoSkillPercent(rawData.aoeHeal*100) + "% AOE";
	if (rawData.singleTargetHeal  != null && rawData.singleTargetHeal  != 0) output += " + " + RoundEsoSkillPercent(rawData.singleTargetHeal*100) + "% SingleTarget";
	if (rawData.skillHealingDone != null && rawData.skillHealingDone != 0) output += " + " + RoundEsoSkillPercent(rawData.skillHealingDone*100) + "% SkillLine";
	
	if (output == "")
		output = "" + rawData.baseHeal + percent + " Health (unmodified)";
	else
		output = "" + rawData.baseHeal + percent + " Health " + output + " = " + rawData.finalHeal + percent + " final";
	
	return output;
}


window.MakeEsoSkillTooltipDamageShieldRawOutput = function(rawData)
{
	var output = '';
	var percent = '';
	
	if (rawData.type == "%") percent = "%";
	
	if (rawData.shieldBonus != null && rawData.shieldBonus != 0) output += " + " + RoundEsoSkillPercent(rawData.shieldBonus*100) + "% ";
	
	if (output == "")
		output = "" + rawData.baseShield + percent + " (unmodified)";
	else
		output = "" + rawData.baseShield + percent + " " + output + " = " + rawData.finalShield + percent + " final";
	
	if (rawData.maxShield > 0) output += " (" + rawData.maxShield + " cap)";
	
	return output;
}


window.SetEsoSkillTooltipRawOutputValue = function(skillData, tooltipIndex, key, value)
{
	if (skillData.rawTooltipOutput == null) skillData.rawTooltipOutput = {};
	if (skillData.rawTooltipOutput[tooltipIndex] == null) skillData.rawTooltipOutput[tooltipIndex] = {};
	
	skillData.rawTooltipOutput[tooltipIndex][key] = value;
}


window.AddEsoSkillTooltipRawOutputMod = function(skillData, tooltipIndex, name, value, displayType)
{
	var output = "";
	
	if (skillData.rawTooltipOutput == null) skillData.rawTooltipOutput = {};
	if (skillData.rawTooltipOutput[tooltipIndex] == null) skillData.rawTooltipOutput[tooltipIndex] = {};
	if (skillData.rawTooltipOutput[tooltipIndex]['mods'] == null) skillData.rawTooltipOutput[tooltipIndex]['mods'] = [];
	
	tooltipIndex = parseInt(tooltipIndex);
	value = parseFloat(value);
	
	if (displayType == '%') 
	{
		value = RoundEsoSkillPercent(value * 100);
	}
	else
	{
		displayType = '';
	}
	
	if (value > 0)
	{
		output = "" + value + displayType + " " + name;
		skillData.rawTooltipOutput[tooltipIndex]['mods'].push(output);
	}
	else if (value < 0)
	{
		output = "" + value + displayType + " " + name;
		skillData.rawTooltipOutput[tooltipIndex]['mods'].push(output);
	}
}


window.ModifyEsoSkillTooltipDamageValue2 = function(baseDamage, tooltip, skillData, inputValues)
{
	if (tooltip.dmgType == 0) return baseDamage;
	if (inputValues.Damage == null) return baseDamage;
	
	var damageType = GetEsoSkillDamageTypeText(tooltip.dmgType);
	var valueFactor = 1.0;
	var valueFlat = 0
	var addedDotDamageDone = false;
	var skillLineName = skillData.skillLine.replace(' ', '_');
	var isPet = false;
	
	var newRawOutput = {};
	newRawOutput.damageType = damageType;
	newRawOutput.baseDamage = baseDamage;
	
		// Damage Type Factor
	if (inputValues.Damage != null && inputValues.Damage[damageType] != null)
	{
		valueFactor += +inputValues.Damage[damageType];
		newRawOutput.mainDamageDone = inputValues.Damage[damageType];
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, damageType + " Damage", inputValues.Damage[damageType], '%');
	}
	
		// Skill Factor
	if (inputValues.SkillDamage != null && inputValues.SkillDamage[skillData.baseName] != null)
	{
		valueFactor += inputValues.SkillDamage[skillData.baseName];
		newRawOutput.skillDamageDone = inputValues.SkillDamage[skillData.baseName];
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Skill", inputValues.SkillDamage[skillData.baseName], '%');
	}
	else if (inputValues.SkillDamage != null && inputValues.SkillDamage[skillData.name] != null)
	{
		valueFactor += inputValues.SkillDamage[skillData.name];
		newRawOutput.skillDamageDone = inputValues.SkillDamage[skillData.name];
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Skill", inputValues.SkillDamage[skillData.name], '%');
	}
	
		// Skill Line Factor
	if (inputValues.SkillLineDamage != null && inputValues.SkillLineDamage[skillLineName] != null)
	{
		valueFactor += inputValues.SkillLineDamage[skillLineName];
		newRawOutput.skillLineDamageDone = inputValues.SkillLineDamage[skillLineName];
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "SkillLine", inputValues.SkillLineDamage[skillLineName], '%');
	}
	
		// DOT Damage Modifiers
	if (tooltip.isDOT == 1)
	{
		var durationTooltip = skillData.tooltips[+tooltip.idx + 1];
		if (durationTooltip && durationTooltip.rawType == 19) durationTooltip = skillData.tooltips[+tooltip.idx + 2];
		
		if (durationTooltip && IsEsoSkillTooltipRawTypeDuration(durationTooltip.rawType))
		{
			var modDuration = 0;
			var oldDuration = parseFloat(durationTooltip.value);	// Should be tooltip.duration ?
			var durationSrcs = [];
			
			if (tooltip.startTime > 0) oldDuration -= tooltip.startTime / 1000;
			
			tooltip.origDuration = tooltip.duration;
			tooltip.finalDuration = tooltip.duration;
			
			if (inputValues.SkillDuration && inputValues.SkillDuration[skillData.baseName])
			{
				modDuration = inputValues.SkillDuration[skillData.baseName];
				durationSrcs.push("Skill Duration");
			}
			
			var tickLength = 2;
			if (tooltip.tickTime > 0) tickLength = +tooltip.tickTime / 1000;
			
			var newDuration = oldDuration * (1 + modDuration);
			if (modDuration >= 1) newDuration = oldDuration + modDuration;
			
				// TODO: Check
			if (tooltip.isElfBane == 1 && inputValues.ElfBaneDuration)
			{
				newDuration += inputValues.ElfBaneDuration;
				durationSrcs.push("Elf Bane");
			}
			
			if (newDuration != oldDuration)
			{
				var oldTicks = Math.floor(oldDuration/tickLength + 0.01) + 1;
				var newTicks = Math.floor(newDuration/tickLength + 0.01) + 1;
				
				if (oldTicks != newTicks && !isNaN(oldTicks) && !isNaN(newTicks) && oldTicks > 0)
				{
					//baseDamage = Math.round(baseDamage * newTicks / oldTicks);	// Damage is modified elsewhere 
					newRawOutput.modDuration = Math.floor((newDuration - oldDuration));
					newDuration = Math.floor(newDuration);
					AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "DOT Ticks (" + durationSrcs.join(" + ") + ")", (newTicks - oldTicks), '');
				}
				else
				{
					newDuration = Math.floor(newDuration);
				}
				
				tooltip.newTicks = newTicks;
				tooltip.finalDuration = parseFloat(newDuration)*1000;
				
				if (tooltip.startTime > 0) newDuration += tooltip.startTime / 1000;
				newRawOutput.dotDuration = newDuration;
			}
			
		}
		
		if (inputValues.Damage.Dot != 0)
		{
			valueFactor += +inputValues.Damage.Dot;
			newRawOutput.dotDamageDone = inputValues.Damage.Dot;
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "DOT", inputValues.Damage.Dot, '%');
			addedDotDamageDone = true;
		}
		
		if (inputValues.DotDamageDone && inputValues.DotDamageDone[damageType])
		{
			valueFactor += +inputValues.DotDamageDone[damageType];
			newRawOutput.dotDamageDone += inputValues.DotDamageDone[damageType];
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, damageType + " DOT", inputValues.DotDamageDone[damageType], '%');
			addedDotDamageDone = true;
		}
		
		if (inputValues.SkillDotDamage && inputValues.SkillDotDamage[skillData.baseName])
		{
			valueFactor += inputValues.SkillDotDamage[skillData.baseName];
			newRawOutput.skillDotDamage = inputValues.SkillDotDamage[skillData.baseName];
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Skill DOT", inputValues.SkillDotDamage[skillData.baseName], '%');
		}
	}
		// Direct Damage Modifiers
	else
	{
		if (inputValues.Damage.Direct != 0)
		{
			valueFactor += +inputValues.Damage.Direct;
			newRawOutput.directDamageDone = inputValues.Damage.Direct;
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "DirectDamage", inputValues.Damage.Direct, '%');
		}
		
		if (inputValues.SkillDirectDamage && inputValues.SkillDirectDamage[skillData.baseName])
		{
			valueFlat += inputValues.SkillDirectDamage[skillData.baseName];
			newRawOutput.skillDirectDamage = inputValues.SkillDirectDamage[skillData.baseName];
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Skill DirectDamage", inputValues.SkillDirectDamage[skillData.baseName], '%');
		}
		
		if (inputValues.SkillDirectDamage && inputValues.SkillDirectDamage[skillData.skillLine])
		{
			valueFlat += inputValues.SkillDirectDamage[skillData.skillLine];
			newRawOutput.skillLineDirectDamage = inputValues.SkillDirectDamage[skillData.skillLine];
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "SkillLine DirectDamage", inputValues.SkillDirectDamage[skillData.skillLine], '%');
		}
	}
	
		// TODO: Check double factor for dots that are channels?
	if (skillData.channelTime > 0)
	{
		if (inputValues.Damage.Channel && !addedDotDamageDone)
		{
			valueFactor += +inputValues.Damage.Channel;
			newRawOutput.channelDamageDone = inputValues.Damage.Channel;
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, " Channel", inputValues.Damage.Channel, '%');
		}
		
		if (inputValues.ChannelDamageDone && inputValues.ChannelDamageDone[damageType] && !addedDotDamageDone)
		{
			valueFactor += +inputValues.ChannelDamageDone[damageType];
			newRawOutput.channelDamageDone += inputValues.ChannelDamageDone[damageType];
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, damageType + " Channel", inputValues.ChannelDamageDone[damageType], '%');
		}
	}
	
		// Damage Done
	if (inputValues.Damage.All != null) 
	{
		valueFactor += Math.floor(inputValues.Damage.All*100)/100;
		newRawOutput.damageDone = inputValues.Damage.All;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Damage", inputValues.Damage.All, '%');
	}
	
		// Old Empower effect
	//if (inputValues.Damage.Empower != null && tooltip.isDOT == 0 && skillData.mechanic != 10) baseFactor += Math.round(inputValues.Damage.Empower*100)/100;
	
		// Single Target
	if (tooltip.isAOE == 0 && inputValues.Damage.SingleTarget != null) 
	{
		valueFactor += Math.floor(inputValues.Damage.SingleTarget*100)/100;
		newRawOutput.singleTargetDamageDone = +inputValues.Damage.SingleTarget;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "SingleTarget", inputValues.Damage.SingleTarget, '%');
	}
	
		// AOE
	if (tooltip.isAOE == 1 && inputValues.Damage.AOE != null && inputValues.Damage.AOE != 0)
	{
		valueFactor += Math.floor(inputValues.Damage.AOE*100)/100;
		newRawOutput.aoeDamageDone = +inputValues.Damage.AOE;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "AOE", inputValues.Damage.AOE, '%');
	}
	
		// Flame AOE special case
	if (tooltip.isAOE == 1 && tooltip.dmgType == 3 && inputValues.FlameAOEDamageDone)
	{
		valueFactor += Math.floor(inputValues.FlameAOEDamageDone*100)/100;
		if (newRawOutput.aoeDamageDone == null) newRawOutput.aoeDamageDone = 0;
		newRawOutput.aoeDamageDone += inputValues.FlameAOEDamageDone;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "FlameAOE", inputValues.FlameAOEDamageDone, '%');
	}
	
		// Magicka Damage Done
	if (IsEsoSkillMechanicMagicka(skillData.mechanic) && inputValues.MagickaAbilityDamageDone)
	{
		valueFactor += Math.floor(inputValues.MagickaAbilityDamageDone*100)/100;
		newRawOutput.magickaAbilityDamageDone = inputValues.MagickaAbilityDamageDone;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Magicka", inputValues.MagickaAbilityDamageDone, '%');
	}
	
		// Pet Damage Done
	overridePet = ESO_SKILL_PETDAMAGE_OVERRIDES[skillData.name];
	if (overridePet == null) overridePet = ESO_SKILL_PETDAMAGE_OVERRIDES[skillData.baseName];
	if (overridePet == null) overridePet = ESO_SKILL_PETDAMAGE_OVERRIDES[skillData.abilityId];
	if (overridePet == null) overridePet = ESO_SKILL_PETDAMAGE_OVERRIDES[skillData.baseAbilityId];
	
	if (overridePet != null)
	{
		var overrideValue = overridePet;
		if (typeof(overridePet) == "object") overrideValue = overridePet[+tooltip.idx];
		if (overrideValue === true) isPet = true;
	}
	
	if (isPet && inputValues.Damage.Pet)
	{
		valueFactor += Math.floor(inputValues.Damage.Pet*100)/100;
		newRawOutput.petDamageDone = inputValues.Damage.Pet;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Pet", inputValues.Damage.Pet, '%');
	}
	
		// Twin Slashes special case
	if (skillData.baseName == "Twin Slashes")
	{
		if (inputValues.TwinSlashInitialDamage)
		{
			valueFlat += inputValues.TwinSlashInitialDamage;
			newRawOutput.twinSlashInitialDamage = inputValues.TwinSlashInitialDamage;
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Twin Slashes", inputValues.TwinSlashInitialDamage, '');
		}
	}
	
		// Overload special case
	if (skillData.baseName == "Overload" && inputValues.FlatOverloadDamage != null && inputValues.FlatOverloadDamage > 0)
	{
		valueFlat += inputValues.FlatOverloadDamage;
		newRawOutput.flatOverloadDamage = inputValues.FlatOverloadDamage;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Overload", inputValues.FlatOverloadDamage, '');
	}
	
	if (inputValues.SkillFlatDamage && inputValues.SkillFlatDamage[skillData.baseName])
	{
		valueFlat += inputValues.SkillFlatDamage[skillData.baseName];
		newRawOutput.skillFlatDamage = inputValues.SkillFlatDamage[skillData.baseName];
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Flat", inputValues.SkillFlatDamage[skillData.baseName], '');
	}
	
		// Pummeling Goliath special case (TODO: This might fail if tooltip changes)
	if (skillData.name == "Pummeling Goliath" && inputValues.Damage.ExtraBashDamage > 0 && tooltip.dmgType == 2 && tooltip.coefType == 10)
	{
		valueFlat += inputValues.Damage.ExtraBashDamage;
		newRawOutput.extraDamage = inputValues.Damage.ExtraBashDamage;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Extra Damage", "+" + inputValues.Damage.ExtraBashDamage, '');
	}
	
		// Overload special case
	if (skillData.baseName == "Overload")
	{
				// Empower
		if (tooltip.dmgType == 4 && tooltip.isAOE == 0 && inputValues.Damage.Empower)
		{
			valueFactor += inputValues.Damage.Empower;
			newRawOutput.empower = inputValues.Damage.Empower;
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Empower", "+" + inputValues.Damage.Empower, '%');
		}
		else if (tooltip.dmgType == 4 && tooltip.isAOE == 1 && inputValues.Damage.Empower)
		{
			valueFactor += inputValues.Damage.Empower;
			newRawOutput.Empower = inputValues.Damage.Empower;
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Empower", "+" + inputValues.Damage.Empower, '%');
		}
				// Single target LA
		if (tooltip.dmgType == 4 && tooltip.isAOE == 0 && inputValues.Damage.LADamage)
		{
			valueFactor += inputValues.Damage.LADamage;
			newRawOutput.LADamage = inputValues.Damage.LADamage;
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "LA Damage", "+" + inputValues.Damage.LADamage, '%');
			
		}		// AOE HA
		else if (tooltip.dmgType == 4 && tooltip.isAOE == 1 && inputValues.Damage.HADamage)
		{
			valueFactor += inputValues.Damage.HADamage;
			newRawOutput.HADamage = inputValues.Damage.HADamage;
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "HA Damage", "+" + inputValues.Damage.HADamage, '%');
		}
	}
	
		// Power bash special case
	if (skillData.baseName == "Power Bash" && tooltip.isDmg && tooltip.dmgType == 2 && inputValues.Damage.ExtraBashDamage)
	{
		valueFlat += inputValues.Damage.ExtraBashDamage;
		newRawOutput.flatBashDamage = inputValues.Damage.flatBashDamage;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Flat Bash Damage", "+" + inputValues.Damage.ExtraBashDamage, '');
	}
	
		// Vulnerability
	if (inputValues.Vulnerability)
	{
		valueFactor += inputValues.Vulnerability;
		newRawOutput.Vulnerability = inputValues.Vulnerability;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Vulnerability", "+" + inputValues.Vulnerability, '%');
	}
	
	var finalDamage = Math.floor(baseDamage * valueFactor + valueFlat);
	
	newRawOutput.finalDamage = finalDamage
	skillData.rawOutput["Tooltip Damage " + tooltip.idx] = MakeEsoSkillTooltipDamageRawOutput(newRawOutput);
	
	return finalDamage;
}


window.ModifyEsoSkillTooltipHealValue2 = function(baseHealing, tooltip, skillData, inputValues)
{
	if (inputValues == null) return baseHealing;
	if (inputValues.Healing == null) return baseHealing;
	if (inputValues.Healing.Done == null) return baseHealing;
	
	var newRawOutput = {};
	var matchCount = 0;
	var healingFactor = 1;
	var skillLineName = skillData.skillLine.replace(' ', '_');
	
	newRawOutput.healId = "Done";
	newRawOutput.baseHeal = baseHealing;
	
		// Healing Done
	if (inputValues.Healing.Done)
	{
		healingFactor += inputValues.Healing.Done;
		newRawOutput.healDone = inputValues.Healing.Done;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Done", inputValues.Healing.Done, '%');
	}
	
		// AOE
	if (tooltip.isAOE == 1 && inputValues.Healing.AOE != null && inputValues.Healing.AOE != 0)
	{
		newRawOutput.aoeHeal = inputValues.Healing.AOE;
		healingFactor += inputValues.Healing.AOE;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "AOE", inputValues.Healing.AOE, '%');
	}
		// Single Target
	else if (tooltip.isAOE == 0 && inputValues.Healing.SingleTarget != null && inputValues.Healing.SingleTarget != 0)
	{
		newRawOutput.singleTargetHeal = inputValues.Healing.SingleTarget;
		healingFactor += inputValues.Healing.SingleTarget;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "SingleTarget", inputValues.Healing.SingleTarget, '%');
	}
	
		// DOT
	if (tooltip.isDOT == 1 && inputValues.Healing.DOT != null && inputValues.Healing.DOT != 0)
	{
		newRawOutput.dotHeal = inputValues.Healing.DOT;
		healingFactor += inputValues.Healing.DOT;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "HOT", inputValues.Healing.DOT, '%');
	}
	
		// No Direct Heal modifiers?
	
		// Skill Line
	var skillHealing = inputValues.SkillHealing[skillLineName];
	
	if (inputValues.SkillHealing != null && skillHealing != null)
	{
		healingFactor += skillHealing;
		newRawOutput.skillHealingDone = skillHealing;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "SkillLine", skillHealing, '%');
	}
	
	var finalHealing = Math.floor(baseHealing * healingFactor);
	
	newRawOutput.display = '';
	if (tooltip.rawType == 96 || tooltip.rawType == 97 || tooltip.rawType == 55 || tooltip.rawType == 92) newRawOutput.display = '%';
	
	newRawOutput.finalHeal = finalHealing;
	skillData.rawOutput["Tooltip Healing " + tooltip.idx] = MakeEsoSkillTooltipHealingRawOutput(newRawOutput);
	
	return finalHealing;
}


window.ModifyEsoSkillTooltipDamageShieldValue2 = function(baseDamageShield, tooltip, skillData, inputValues)
{
	if (inputValues == null) return baseDamageShield;
	if (inputValues.DamageShield == null) return baseDamageShield;
	
	var newRawOutput = {};
	var shieldFactor = 1;
	
	newRawOutput.baseShield = baseDamageShield;
	newRawOutput.maxShield = -1;
	
	newRawOutput.type = 'flat';
	if (tooltip.rawType == 55 || tooltip.rawType == 96 || tooltip.rawType == 55 || tooltip.rawType == 92) newRawOutput.type = '%';
	
	if (inputValues.DamageShield)
	{
		shieldFactor += inputValues.DamageShield
		newRawOutput.shieldBonus = inputValues.DamageShield;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "DamageShield", inputValues.DamageShield, '%');
	}
	
	var finalDamageShield = Math.floor(baseDamageShield * shieldFactor);
	
		/* Cap shield if required */
	if (newRawOutput.type == "flat" && tooltip.coefType == -68 && tooltip.b > 0 && inputValues.Health)
	{
		var maxShield = Math.floor(tooltip.b * inputValues.Health);
		newRawOutput.maxShield = maxShield;
		if (finalDamageShield > maxShield) finalDamageShield = maxShield;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Cap", maxShield, '%');
	}
	
	newRawOutput.finalShield = finalDamageShield;
	skillData.rawOutput["Tooltip Damage Shield " + tooltip.idx] = MakeEsoSkillTooltipDamageShieldRawOutput(newRawOutput);
	
	return finalDamageShield;
}


window.ModifyEsoSkillTooltipHealingReductionValue2 = function(baseReduction, tooltip, skillData, inputValues)
{
		// No known sources of healing reduction since CP2 in update 29 was introduced
	return baseReduction;
}


	/* Index values represent the tooltip index of the duration within the tooltip you wish to have modified.
	 * 		"Name" : true,					// All durations in tooltip are modified
	 * 		"Name" : { 1: true, 2:true },	// Only the specified tooltip indexes are modified
	 * 1 = First tooltip index 
	 * */
window.ESO_SKILLTOOLTIP_DURATION_MATCHINDEXES = {
	
		// Dragonknight's Ardent Flame Searing Heat passive
	"Dragonknight Standard": { 3: true },
	"Searing Strike": { 2: true, 3: true },
	"Fiery Breath": { 2: true, 3: true },
	
		// Dragonknight's Earthen Heart Eternal Mountain passive
	"Magma Armor" : { 3 : true },
	"Stonefist" : { 2 : true, 4: true },
	"Obsidian Shard" : false,
	"Stone Giant" : { 2 : true, 4: true, 6 : true },
	"Molten Weapons" : { 2 : true },
	"Obsidian Shield" : { 3 : true },
	"Igneous Shield" : { 4 : true },
	"Petrify" : { 1 : true },
	
		// Mages Guild Everlasting Magick passive
	"Meteor" : { 5 : true },
	"Ice Comet" : { 7 : true },
	"Fire Rune" : { 1 : true },
	"Scalding Rune" : { 1 : true, 3 : true, 4 : true },
	"Magelight" : { 1 : true },
	"Entropy" : { 1 : true, 2 : true },
	"Structured Entropy" : { 1 : true, 2 : true, 3: true },
	"Equilibrium" : false,
	"Balance" : { 1 : true },
	"Spell Symmetry" : { 1 : true },
	
		// Templar Dawn's Wrath Enduring Rays passive
	"Nova" : { 3 : true },
	"Solar Disturbance" : { 3 : true, 5 : true },
	"Sun Fire" : { 2 : true, 3 : true },
	"Reflective Light" : { 2 : true, 3 : true, 5 : true },
	"Solar Barrage" : { 3 : true },
	"Eclipse" : { 1 : true },
	
		// Nightblade's Shadow Dark Veil passive
	"Summon Shade" : { 1: true },
	"Aspect of Terror": { 1: true },
	"Dark Cloak": { 3: true, 4: true},
};



window.TestFindEsoPlayerSkillWithName = function(skillName)
{
	for (var abilityId in g_SkillsData)
	{
		var skillData = g_SkillsData[abilityId];
		if (skillData.isPlayer && skillData.name == skillName) return skillData;
	}
	
	return null;
}


window.TestEsoSkillTooltipDurationMatchIndexes = function()
{
	var errorCount = 0;
	var totalCount = 0;
	
	console.log("Testing all ESO skill tooltip duration match indexes...");
	
	for (var skillName in ESO_SKILLTOOLTIP_DURATION_MATCHINDEXES)
	{
		var matchIndexes = ESO_SKILLTOOLTIP_DURATION_MATCHINDEXES[skillName];
		var skillData = TestFindEsoPlayerSkillWithName(skillName);
		
		if (skillData == null)
		{
			console.log("TestEsoSkillTooltipDurationMatchIndexes: Failed to find skill matching '" + skillName + "'!", matchIndexes);
			++errorCount;
			continue;
		}
		
		var matchKeys = Object.keys(matchIndexes);
		var nextMatchIndex = matchKeys[0];
		
		for (var tooltipIndex in skillData.tooltips)
		{
			var tooltip = skillData.tooltips[tooltipIndex];
			var isMatched = false;
			
			if (matchIndexes === true)
			{
				isMatched = true;
			}
			else
			{
				for (var matchIndex in matchIndexes)
				{
					if (matchIndex == tooltip.idx)
					{
						isMatched = true;
						break;
					}
				}
			}
			
			++totalCount;
			if (!isMatched) continue;
			
			if (tooltip.coefType == -75)
			{
				if (tooltip.rawType != 44 && tooltip.rawType != 20 && tooltip.rawType != 93 && tooltip.rawType != 90 && tooltip.rawType != 16)
				{
					console.log("TestEsoSkillTooltipDurationMatchIndexes: Invalid tooltip raw coefficient type " + tooltip.idx + ":" + tooltip.rawType + " found!", skillName, matchIndexes, skillData);
					++errorCount;
				}
				else if (tooltip.rawType == 90 || tooltip.rawType == 16)
				{
						// Do nothing
				}
				else if (!tooltip.value.match(/[0-9.]+[%]? (?:second|minute)[s]?/))
				{
					console.log("TestEsoSkillTooltipDurationMatchIndexes: Tooltip value doesn't match a duration " + tooltip.idx + ":" + tooltip.value + "!", skillName, matchIndexes, skillData);
					++errorCount;
				}
			}
			else if (tooltip.coefType == 0 || tooltip.coefType == 6 || tooltip.coefType == 10)
			{
				if (tooltip.isDmg == 0 || tooltip.isDOT == 0)
				{
					console.log("TestEsoSkillTooltipDurationMatchIndexes: Tooltip is not a damage or dot value" + tooltip.idx + ":" + tooltip.value + "!", skillName, matchIndexes, skillData);
					++errorCount;
				}
			}
			else
			{
				console.log("TestEsoSkillTooltipDurationMatchIndexes: Invalid tooltip coefficient type " + tooltip.idx + ":" + tooltip.coefType + " found!", skillName, matchIndexes, skillData);
				++errorCount;
			}
		}
	}
	
	console.log("Tested " + totalCount + " duration match indexes with " + errorCount + " errors!");
}


window.ModifyEsoSkillTooltipConstantDurationValue2 = function(baseDuration, tooltip, skillData, inputValues)
{
	var modDuration = 0;
	var finalDuration = baseDuration;
	
	var durationData = ESO_SKILLTOOLTIP_DURATION_MATCHINDEXES[skillData.name];
	if (durationData == null) durationData = ESO_SKILLTOOLTIP_DURATION_MATCHINDEXES[skillData.baseName];
	if (durationData == null) return baseDuration;
	
	if (typeof(durationData) == "object") durationData = durationData[+tooltip.idx];
	if (durationData == null || durationData !== true) return baseDuration;
	
	if (inputValues.SkillDuration != null && inputValues.SkillDuration[skillData.baseName] != null) modDuration = +inputValues.SkillDuration[skillData.baseName];
	if (modDuration == 0) return baseDuration;
	
	if (modDuration >= 1)
	{
		finalDuration = Math.floor(baseDuration + modDuration);
		skillData.rawOutput["Tooltip Duration #" + tooltip.idx] = "" + baseDuration + " Base + " + modDuration + " = " + finalDuration;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Skill Duration", modDuration, '');
	}
	else
	{
		if (tooltip.startTime > 0)
			//finalDuration = Math.floor(((baseDuration - tooltip.startTime/1000) * (1 + modDuration) + tooltip.startTime/1000)*10)/10;
			finalDuration = Math.floor(baseDuration * (1 + modDuration));
		else
			finalDuration = Math.floor(baseDuration * (1 + modDuration));
		
		skillData.rawOutput["Tooltip Duration #" + tooltip.idx] = "" + baseDuration + " Base x " + Math.floor(modDuration*100) + "% = " + finalDuration;
		AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Skill Duration", modDuration, '%');
	}
	
	return finalDuration;
}


window.ModifyEsoSkillTooltipConstantElfBaneValue2 = function(baseDuration, tooltip, skillData, inputValues)
{
	if (tooltip.isElfBane != 1) return baseDuration;
	if (inputValues == null) return baseDuration;
	if (inputValues.ElfBaneDuration == null || inputValues.ElfBaneDuration == 0) return baseDuration;
	
	var modDuration = 0;
	var finalDuration = baseDuration + inputValues.ElfBaneDuration;
	
		// Now g_EsoSkillElfBaneSkills only controls the base skill duration, not the description durations
	//var elfBaneSkill = g_EsoSkillElfBaneSkills[skillData.abilityId];
	//if (elfBaneSkill == null) elfBaneSkill = g_EsoSkillElfBaneSkills[skillData.displayId];
	//if (elfBaneSkill == null) elfBaneSkill = g_EsoSkillElfBaneSkills[skillData.baseAbilityId];
	//if (elfBaneSkill == null) return baseDuration;
	
	finalDuration = Math.round(finalDuration * 10) / 10;
	skillData.rawOutput["Tooltip Flame DOT Duration (Elf Bane)"] = "" + baseDuration + " + " + inputValues.ElfBaneDuration + " = " + finalDuration + " secs";
	AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Elf Bane", inputValues.ElfBaneDuration, '');
	
	return finalDuration;
}


window.ModifyEsoSkillTooltipConstantValue2 = function(value, tooltip, skillData, inputValues)
{
	var origValue = value;
	value = parseFloat(value);
	if (isNaN(value)) value = origValue;
	
	if (IsEsoSkillTooltipRawTypeDuration(tooltip.rawType))
	{
		value = ModifyEsoSkillTooltipConstantDurationValue2(value, tooltip, skillData, inputValues);
		value = ModifyEsoSkillTooltipConstantElfBaneValue2(value, tooltip, skillData, inputValues);
	}
	else if (tooltip.isHeal)
	{
		value = ModifyEsoSkillTooltipValue2(value, tooltip, skillData, inputValues);
	}
	else if (tooltip.isDmg)
	{
		value = ModifyEsoSkillTooltipValue2(value, tooltip, skillData, inputValues);
	}
	return value;
}


window.ModifyEsoSkillTooltipValue2 = function(value, tooltip, skillData, inputValues)
{
	if (tooltip.isDmg  == 1) value = ModifyEsoSkillTooltipDamageValue2(value, tooltip, skillData, inputValues);
	if (tooltip.isHeal == 1) value = ModifyEsoSkillTooltipHealValue2(value, tooltip, skillData, inputValues);
	if (tooltip.isDmgShield == 1) value = ModifyEsoSkillTooltipDamageShieldValue2(value, tooltip, skillData, inputValues);
	
		// Does nothing at the moment
	//value = ModifyEsoSkillTooltipHealingReductionValue2(value, tooltip, skillData, inputValues);
	
	return value;
}


window.ComputeEsoSkillTooltipConstantCoefDescription2 = function(tooltip, skillData, inputValues)
{
	SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "baseValue", tooltip.value);
	
	var value = ModifyEsoSkillTooltipConstantValue2(tooltip.value, tooltip, skillData, inputValues);
	
	SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "finalValue", value);
	
	tooltipDesc = tooltip.value.replace(/[0-9]+(?:\.[0-9]+)?/, '|cffffff' + value + '|r');
	if (tooltipDesc == tooltip.value) tooltipDesc = '|cffffff' + value + '|r';
	
	tooltip.finalValue = tooltipDesc;
	
	return tooltipDesc;
}


//Math.floor((Math.floor(Math.fround(0.02)*12000) + Math.floor(Math.fround(0.21)*1000))*1.033)*5
window.ComputeEsoSkillTooltipCoefDescription2 = function(tooltip, skillData, inputValues)
{
	if (tooltip.coefType == -75) return ComputeEsoSkillTooltipConstantCoefDescription2(tooltip, skillData, inputValues);
	
	if (Math.fround == null) Math.fround = function(v) { return v; };
	
	var tooltipDesc = '';
	var a = Math.fround(parseFloat(tooltip.a));
	var b = Math.fround(parseFloat(tooltip.b));
	var c = Math.fround(parseFloat(tooltip.c));
	var spellDamageTypes = [];
	var weaponDamageTypes = [];
	var rankFactor = 1;
	var dotFactor = 1;
	
	if (tooltip.hasRankMod == 1 && skillData.isPassive == 0 && skillData.isPlayer == 1)
	{
		var rank = skillData.rank % 4;
		
		if (rank == 2)
			rankFactor = 1.011;
		else if (rank == 3)
			rankFactor = 1.022;
		else if (rank == 4 || rank == 0)
			rankFactor = 1.033;
	}
	
	var spellDamage  = GetEsoSkillTooltipSpellDamage2(tooltip, skillData, inputValues, spellDamageTypes);
	var weaponDamage = GetEsoSkillTooltipWeaponDamage2(tooltip, skillData, inputValues, weaponDamageTypes);
	
	var includeSpellRawOutput = 0;
	var includeWeaponRawOutput = 0;
	if (spellDamage != inputValues.SpellDamage) includeSpellRawOutput = 1;
	if (weaponDamage != inputValues.WeaponDamage) includeWeaponRawOutput = 1;
	
	var magicka = parseInt(inputValues.Magicka);
	var stamina = parseInt(inputValues.Stamina);
	var health  = parseInt(inputValues.Health);
	
	var maxStat   = Math.max(magicka, stamina);
	var maxDamage = Math.max(spellDamage, weaponDamage);
	
	var value = 0;
	
	switch (parseInt(tooltip.coefType))
	{
	case -2:	// Health
	case 32:	// Update 34
		value = Math.floor(a * health) + c;
		
			// Special case for equilibrim and morphs
		if (skillData.baseName == "Equilibrium" && tooltip.idx == 1 && inputValues != null && inputValues.SkillLineCost != null && inputValues.SkillLineCost.Mages_Guild_Cost != 0)
		{
			skillData.rawOutput["$1 Health Cost"] = "" + Math.round(value) + " Base + " + (inputValues.SkillLineCost.Mages_Guild_Cost*100) + "% Mages Guild Cost";
			value = value * (1 + inputValues.SkillLineCost.Mages_Guild_Cost);
			AddEsoSkillTooltipRawOutputMod(skillData, tooltip.idx, "Mages Guild Cost", inputValues.SkillLineCost.Mages_Guild_Cost, '%');
		}
		
		break;
	case 0:		// Magicka
	case 1:		// Update 34
		value = Math.floor(a * magicka) + Math.floor(b * spellDamage) + c;
		++includeSpellRawOutput;
		break;
	case 6:		// Stamina
	case 4:		// Update 34
		value = Math.floor(a * stamina) + Math.floor(b * weaponDamage) + c;
		++includeWeaponRawOutput;
		break;
	case 10:	// Ultimate
	case 8:		// Update 34
		value = Math.floor(a * maxStat) + Math.floor(b * maxDamage) + c;
		++includeSpellRawOutput;
		++includeWeaponRawOutput;
		break;
	case -50:	// Ultimate Soul Tether
		value = Math.floor(a * maxStat) + Math.floor(b * spellDamage) + c;
		++includeSpellRawOutput;
		break;
	case -51:	// Light Armor
		if (inputValues.LightArmor == null) 
		{
			if (c == 0) 
				value = '(' + a.toPrecision(5) + ' * LightArmor)';
			else
				value = '(' + a.toPrecision(5) + ' * LightArmor + ' + c.toPrecision(5) + ')';
		}
		else
		{
			value = a * inputValues.LightArmor + c;
		}
		break;
	case -52:	// Medium Armor
		if (inputValues.MediumArmor == null) 
		{
			if (c == 0) 
				value = '(' + a + ' * MediumArmor)';
			else
				value = '(' + a.toPrecision(5) + ' * MediumArmor + ' + c.toPrecision(5) + ')';
		}
		else
		{
			value = a * inputValues.MediumArmor + c;
		}
		break;
	case -53:	// Heavy Armor
		if (inputValues.HeavyArmor == null) 
		{
			if (c == 0)	
				value = '(' + a.toPrecision(5) + ' * HeavyArmor)';
			else
				value = '(' + a.toPrecision(5) + ' * HeavyArmor + ' + c.toPrecision(5) + ')';
		}
		else 
		{
			value = a * inputValues.HeavyArmor + c;
		}
		break;
	case -54:	// Daggers
		if (inputValues.DaggerWeapon == null) 
			value = '(' + a.toPrecision(5) + ' * Dagger)';
		else
			value = a * inputValues.DaggerWeapon;
		break;
	case -55:	// Armor Types
		if (inputValues.ArmorTypes == null) 
			value = '(' + a.toPrecision(5) + ' * ArmorTypes)';
		else
			value = a * inputValues.ArmorTypes;
		break;
	case -56:	// Spell + Weapon Damage
	case 4:
		value = Math.floor(a * spellDamage) + Math.floor(b * weaponDamage) + c;
		++includeSpellRawOutput;
		++includeWeaponRawOutput;
		break;
	case -57:
		value = a * inputValues.AssassinSkills;
		break;
	case -58:
		value = a * inputValues.FightersGuildSkills;
		break;
	case -59:
		value = a * inputValues.DraconicPowerSkills;
		break;
	case -60:
		value = a * inputValues.ShadowSkills;
		break;
	case -61:
		value = a * inputValues.SiphoningSkills;
		break;
	case -62:
		value = a * inputValues.SorcererSkills;
		break;
	case -63:
		value = a * inputValues.MagesGuildSkills;
		break;
	case -64:
		value = a * inputValues.SupportSkills;
		break;
	case -65:
		value = a * inputValues.AnimalCompanionSkills;
		break;
	case -66:
		value = a * inputValues.GreenBalanceSkills;
		break;
	case -67:
		value = a * inputValues.WintersEmbraceSkills;
		break;
	case -68:	// Magicka with Capped Health
		value = Math.floor(a * magicka);
		var maxValue = Math.floor(b * health);
		if (value > maxValue) value = maxValue;
		break;
	case -69:
		value = a * inputValues.BoneTyrantSkills;
		break;
	case -70:
		value = a * inputValues.GraveLordSkills;
		break;
	case -71:	// Capped Spell Damage
		value = Math.floor(a * spellDamage) + b;
		if (value > c) value = c;
		break;
	case -72:	// Magicka and Weapon Damage
		value = Math.floor(a * magicka) + Math.floor(b * weaponDamage) + c;
		++includeWeaponRawOutput;
		break;
	case -73:	// Capped Magicka and Spell Damage
		var value1 = Math.floor(a * magicka);
		var value2 = Math.floor(b * spellDamage);
		var halfMax = c/2;
		
		if (value1 > halfMax) value1 = halfMax;
		if (value2 > halfMax) value2 = halfMax;
		
		value = value1 + value2;
		if (value > c) value = c;
		break;
	case -74:	// Weapon Power
		value = Math.floor(a * inputValues.WeaponPower) + c;
		break;
	case -75:	// Constant (should be handled before this point) 
		value = tooltip.value;
		break;
	case -76:	// Health or Spell Damage
		var value1 = Math.floor(a * inputValues.SpellDamage) + c;
		var value2 = Math.floor(b * inputValues.Health) + c;
		value = Math.max(value1, value2);
		break;
	case -79:	// Health or Spell/Weapon Damage
		var value1 = Math.floor(a * inputValues.SpellDamage) + Math.floor(b * inputValues.WeaponDamage);
		var value2 = Math.floor(c * inputValues.Health);
		value = Math.max(value1, value2);
		break;
	case -77:	// Max Resistance
		value = Math.floor(a * Math.max(inputValues.SpellResist, inputValues.PhysicalResist)) + c;
		break;
	case -78:	// Magicka and Light Armor (Health Capped)
		if (inputValues.LightArmor == null) 
		{
			value = Math.floor(a * magicka);
		}
		else
		{
			value = Math.floor(a * magicka) * (1 + b*inputValues.LightArmor);	//TODO: Check rounding order
		}
		
		var maxValue = Math.floor(c * health);
		if (value > maxValue) value = maxValue;
		break;
	default:
		value = '?';
		break;
	}
	
	if (rankFactor > 1)
	{
		value = Math.floor(value * rankFactor);
		SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "rankFactor", rankFactor);
	}
	
	if (typeof(value) == "number")
	{
		if (tooltip.rawType == 92)
			value = Math.floor(value*10)/10;
		else
			value = Math.floor(value);
		
		if (value < 0) value = 0;
	}
	
	SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "baseValue", value);
	
	if (includeSpellRawOutput >= 2)
	{
		//skillData.rawOutput["&lt;&lt;" + tooltip.idx + "&gt;&gt; Spell Damage Used"] = "" + spellDamage + " " + spellDamageTypes.join("+") + "";
		SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "spellDamage", spellDamage);
		SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "spellDamageMods", spellDamageTypes);
	}
	else
	{
		//delete skillData.rawOutput["&lt;&lt;" +  tooltip.idx + "&gt;&gt; Spell Damage Used"];
	}
	
	if (includeWeaponRawOutput >= 2)
	{
		//skillData.rawOutput["&lt;&lt;" +  tooltip.idx + "&gt;&gt; Weapon Damage Used"] = "" + weaponDamage + " " + weaponDamageTypes.join("+") + "";
		SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "weaponDamage", weaponDamage);
		SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "weaponDamageMods", weaponDamageTypes);
	}
	else
	{
		//delete skillData.rawOutput["&lt;&lt;" +  tooltip.idx + "&gt;&gt; Weapon Damage Used"];
	}
	
	value = ModifyEsoSkillTooltipValue2(value, tooltip, skillData, inputValues);
	
	if ((tooltip.rawType == 49 || tooltip.rawType == 53) && tooltip.duration > 0)
	{
		var duration = +tooltip.duration;
		/*
		tooltip.origDuration = duration;
		
		if (inputValues.SkillDuration && inputValues.SkillDuration[skillData.baseName] != null)
		{
			modDuration = +inputValues.SkillDuration[skillData.baseName];
			newDuration = 0;
			
			if (modDuration >= 1) 
			{
				newDuration = duration + modDuration*1000;
				SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "modDuration", modDuration);
			}
			else 
			{
				newDuration = Math.floor(duration * (1 + modDuration));
				SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "modDuration", modDuration);
			}
			
			duration = newDuration;
		}
		
		if (inputValues.ElfBaneDuration && (g_EsoSkillElfBaneSkills[skillId] || g_EsoSkillElfBaneSkills[displayId]))
		{
			var origDuration = duration;
			newDuration += inputValues.ElfBaneDuration*1000;
			SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "elfBaneDuration", inputValues.ElfBaneDuration);
			duration = newDuration;
		}
		
		tooltip.finalDuration = duration;
		//SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "finalDuration", duration);
		*/
		
		if (tooltip.newTicks != null)
		{
			dotFactor = tooltip.newTicks;
			delete tooltip.newTicks;
		}
		else if (tooltip.tickTime > 0)
		{
			dotFactor = (duration + +tooltip.tickTime) / +tooltip.tickTime;
		}
		else
		{
			dotFactor = duration / 1000;
		}
		
		if (dotFactor != 1)
		{
			value = Math.floor(Math.floor(value) * dotFactor);
			SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "dotFactor", dotFactor);
		}
	}
	
	SetEsoSkillTooltipRawOutputValue(skillData, tooltip.idx, "finalValue", value);
	
	tooltipDesc = tooltip.value.replace(/([0-9]+(?:\.[0-9]+)?)/, '|cffffff' + value + '|r');
	tooltip.finalValue = tooltipDesc;
	return tooltipDesc;
}


window.CreateEsoSkillTooltipRawDescription2 = function(skillData, inputValues)
{
	var descHeader = skillData.descHeader;
	var rawDesc = skillData.rawDescription;
	
	if (rawDesc == "") rawDesc = skillData.description;
	
	for (var tooltipIndex in skillData.tooltips)
	{
		var tooltip = skillData.tooltips[tooltipIndex];
		var tooltipDesc = ComputeEsoSkillTooltipCoefDescription2(tooltip, skillData, inputValues);
		var tooltipStr = "<<" + tooltipIndex + ">>";
		
		rawDesc = rawDesc.replaceAll(tooltipStr, tooltipDesc);
	}
	
	if (descHeader)
	{
		if (descHeader.charAt(0) != '|')
			rawDesc = '|cffffff' + descHeader + "|r\n" + rawDesc;
		else
			rawDesc = descHeader + "\n" + rawDesc;
	}
	
	return rawDesc;
}


window.GetEsoSkillDescription2 = function(abilityId, inputValues, useHtml, noEffectLines, outputRaw)
{
	var skillData = g_SkillsData[abilityId];
	if (skillData == null || abilityId <= 0) return "";
	
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	skillData.rawTooltipOutput = {};
	
	if (inputValues == null) inputValues = GetEsoSkillInputValues();
	
	inputValues.useMaelstromDamage = false;
	if (IsEsoSkillValidForMaelstromDWEnchant(skillData)) inputValues.useMaelstromDamage = true;
	
	if (skillData.rawOutput == null) skillData.rawOutput = {};
	if (skillData.rawTooltipOutput == null) skillData.rawTooltipOutput = {};
	
	var rawDesc = CreateEsoSkillTooltipRawDescription2(skillData, inputValues);
	var output = "";
	
	rawDesc = UpdateEsoSkillRapidStrikesDescription(skillData, rawDesc, inputValues);
	rawDesc = UpdateEsoSkillUppercutDescription(skillData, rawDesc, inputValues);
	rawDesc = UpdateEsoSkillScatterShotDescription(skillData, rawDesc, inputValues);
	rawDesc = UpdateEsoSkillVolleyDescription(skillData, rawDesc, inputValues);
	
	if (useHtml)
	{
		output = EsoConvertDescToHTML(rawDesc);
	}
	else
	{
		var effectLines = skillData['effectLines'];
		if (effectLines != "" && noEffectLines !== true) rawDesc += " <div class='esovsAbilityBlockEffectLines'>" + effectLines + "</div>";
		
		if (outputRaw !== true)
			output = EsoConvertDescToText(rawDesc);
		else
			output = rawDesc;
	}
	
	skillData.lastDesc = output;
	return output;
}


window.TestEsoSkillTooltipCompareDescriptions = function()
{
	var inputValues = GetEsoSkillInputValues();
	var compareCount = 0;
	var errorCount = 0;
	var exactCount = 0;
	var mismatchCount = 0;
	var maxErrorAllowed = 0.01;
	var avgErrorSum = 0;
	var avgErrorCount = 0;
	var prevTooltipSetting = USE_V2_TOOLTIPS;
	
	USE_V2_TOOLTIPS = false; // Must be off or we'll get all V2 descriptions
	
	console.log("Testing all player skill description tooltip generation between V1 and V2...");
	
	for (var abilityId in g_SkillsData)
	{
		var skillData = g_SkillsData[abilityId];
		
		if (skillData.isPlayer == 0) continue;
		if (skillData.isCustom === true) continue;
		if (skillData.isPassive == 0 && skillData.rank > 1) continue;
		
		var desc1 = GetEsoSkillDescription(abilityId, inputValues);
		var desc2 = GetEsoSkillDescription2(abilityId, inputValues);
		
		desc1 = desc1.replace("  ", " ").replace("  ", " ");
		desc2 = desc2.replace("  ", " ").replace("  ", " ");
		
		++compareCount;
		
		if (desc1.toLowerCase() == desc2.toLowerCase())
		{
			++exactCount;
			continue;
		}
		
		++mismatchCount;
		
		var matchDesc = desc1.replace("(", "\\(").replace(")", "\\)").replace("*", "\\*").replace(".", "\\.");
		matchDesc = matchDesc.replace(/a ([0-9]+(?:\.[0-9]+)?)/g, "a[n]? $1");
		matchDesc = matchDesc.replace(/[0-9]+(?:\.[0-9]+)?/g, "(.*)");
		matchDesc = new RegExp(matchDesc, "i");
		
		var match1 = desc1.match(matchDesc);
		var match2 = desc2.match(matchDesc);
		
		if (match1 == null || match2 == null)
		{
			console.log("" + abilityId + ": Failed to match one of the skill descriptions!", match1, match2, matchDesc, skillData.name, desc1, desc2, skillData);
			continue;
		}
		
		if (match1.length != match2.length) 
		{
			console.log("" + abilityId + ": Number matches of both skills are not the same size!", match1, match2, matchDesc, skillData.name, desc1, desc2, skillData);
			continue;
		}
		
		for (var i = 1; i < match1.length; ++i)
		{
			var value1 = parseFloat(match1[i]);
			var value2 = parseFloat(match2[i]);
			var avg = (value1 + value2) / 2;
			var diff = Math.abs(value1 - value2);
			var error = diff/avg;
			
			avgErrorSum += error;
			++avgErrorCount;
			
			if (error > maxErrorAllowed)
			{
				console.log("" + abilityId + ":" + i + ": Number comparison exceeds maximum allowed error!", value1, value2, skillData.name, desc1, desc2, skillData);
				++errorCount;
			}
		}
		
		//console.log("" + abilityId + ": Description Mismatch", skillData.name, desc1, desc2, skillData);
	}
	
	var avgError = (avgErrorSum / avgErrorCount * 100).toFixed(2);
	console.log("Compared " + compareCount + " player skill descriptions.");
	console.log("Found " + exactCount + " exact matches with " + mismatchCount + " close mismatches and " + errorCount + " mismatches above the allowed error of " + (maxErrorAllowed*100) + "%!");
	console.log("Average error of " + avgErrorCount + " mismatched skill tooltip values is " + avgError + "%");
	
	USE_V2_TOOLTIPS = prevTooltipSetting;
}


