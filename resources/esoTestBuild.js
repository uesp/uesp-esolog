/*
 * TODO:
 * 		- Description of input types (plain, percent, special, etc...)
 */

ESO_TESTBUILD_SHOWALLRAWINPUTS = false;

ESO_MAX_ATTRIBUTES = 64;
ESO_MAX_LEVEL = 50;
ESO_MAX_CPLEVEL = 16;
ESO_MAX_EFFECTIVELEVEL = 66;


g_EsoBuildClickWallLinkElement = null;
g_EsoBuildItemData = {};
g_EsoBuildEnchantData = {};
g_EsoBuildSetData = {};
g_EsoBuildToggledSetData = {};

g_EsoBuildItemData.Head = {};
g_EsoBuildItemData.Shoulders = {};
g_EsoBuildItemData.Chest = {};
g_EsoBuildItemData.Hands = {};
g_EsoBuildItemData.Legs = {};
g_EsoBuildItemData.Waist = {};
g_EsoBuildItemData.Feet = {};
g_EsoBuildItemData.Neck = {};
g_EsoBuildItemData.Ring1 = {};
g_EsoBuildItemData.Ring2 = {};
g_EsoBuildItemData.MainHand1 = {};
g_EsoBuildItemData.OffHand1 = {};
g_EsoBuildItemData.MainHand2 = {};
g_EsoBuildItemData.OffHand2 = {};
g_EsoBuildItemData.Poison1 = {};
g_EsoBuildItemData.Poison2 = {};
g_EsoBuildItemData.Food = {};
g_EsoBuildItemData.Potion = {};

g_EsoBuildEnchantData.Head = {};
g_EsoBuildEnchantData.Shoulders = {};
g_EsoBuildEnchantData.Chest = {};
g_EsoBuildEnchantData.Hands = {};
g_EsoBuildEnchantData.Legs = {};
g_EsoBuildEnchantData.Waist = {};
g_EsoBuildEnchantData.Feet = {};
g_EsoBuildEnchantData.Neck = {};
g_EsoBuildEnchantData.Ring1 = {};
g_EsoBuildEnchantData.Ring2 = {};
g_EsoBuildEnchantData.MainHand1 = {};
g_EsoBuildEnchantData.OffHand1 = {};
g_EsoBuildEnchantData.MainHand2 = {};
g_EsoBuildEnchantData.OffHand2 = {};

g_EsoBuildActiveWeapon = 1;
g_EsoFormulaInputValues = {};
g_EsoInputStatSources = {};


ESO_SETEFFECT_MATCHES = [
	{
		statId: "SpellCrit",
		match: /Adds ([0-9]+) Spell Critical/i,
	},
	{
		statId: "WeaponCrit",
		match: /Adds ([0-9]+) Weapon Critical/i,
	},
	{
		statId: "Health",
		match: /Adds ([0-9]+) Maximum Health/i,
	},
	{
		statId: "Magicka",
		match: /Adds ([0-9]+) Maximum Magicka/i,
	},
	{
		statId: "Stamina",
		match: /Adds ([0-9]+) Maximum Stamina/i,
	},
	{
		statId: "SpellDamage",
		match: /Increase Spell Damage by ([0-9]+)/i,
	},
	{
		statId: "SpellDamage",
		match: /increases Weapon and Spell Damage by ([0-9]+)/i,
	},
	{
		statId: "SpellDamage",
		match: /Adds ([0-9]+) Spell Damage/i,
	},
	{
		statId: "WeaponDamage",
		match: /increases Weapon and Spell Damage by ([0-9]+)/i,
	},
	{
		statId: "WeaponDamage",
		match: /Increase Weapon Damage by ([0-9]+)/i,
	},
	{
		statId: "WeaponDamage",
		match: /Adds ([0-9]+) Weapon Damage/i,
	},
	{
		statId: "HealthRegen",
		match: /Adds ([0-9]+) Health Recovery/i,
	},
	{
		statId: "MagickaRegen",
		match: /Adds ([0-9]+) Magicka Recovery/i,
	},
	{
		statId: "StaminaRegen",
		match: /Adds ([0-9]+) Stamina Recovery/i,
	},
	{
		statId: "PhysicalResist",
		match: /Adds ([0-9]+) Physical Resistance/i,
	},
	{
		statId: "SpellResist",
		match: /Adds ([0-9]+) Spell Resistance/i,
	},
	{
		statId: "HealingTaken",
		display: '%',
		match: /Adds ([0-9]+\.?[0-9]*)% Healing Taken/i,
	},	
	{
		statId: "HealingTaken",
		display: '%',
		match: /Group members within [0-9]+m gain ([0-9]+\.?[0-9]*)% increased effect from heals/i,
	},	
	{
		statId: "HealingTaken",
		display: '%',
		match: /When you are healed, gain ([0-9]+\.?[0-9]*)% additional healing/i,
	},	
	{
		statId: "CritDamage",
		display: '%',
		match: /Critical Damage increases by ([0-9]+\.?[0-9]*)%/i,
	},	
	{
		statId: "CritResist",
		display: '%',
		match: /Reduces damage from Critical Hits by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "MagickaCost",
		display: '%',
		match: /Reduce the Magicka cost of abilities by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "MagickaCost",
		display: '%',
		match: /Reduce all costs by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "MagickaCost",
		display: '%',
		match: /Reduce Magicka costs for up to [0-9]+ group members by ([0-9]+\.?[0-9]*)%/i,
	},	
	{
		statId: "StaminaCost",
		display: '%',
		match: /Reduce the Stamina cost of abilities by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "StaminaCost",
		display: '%',
		match: /Reduces the costs of Stamina abilities by ([0-9]+\.?[0-9]*)%/i,
	},	
	{
		statId: "StaminaCost",
		display: '%',
		match: /Reduce all costs by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "UltimateCost",
		display: '%',
		match: /Reduce cost of Ultimate abilities by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "PhysicalPenetration",
		match: /Adds ([0-9]+) Physical Penetration/i,
	},
	{
		statId: "Spell Penetration",
		match: /Adds ([0-9]+) Spell Penetration/i,
	},
	{
		statId: "SnareDuration",
		display: '%',
		match: /Snares on you have ([0-9]+)% shorter duration/i,
	},
	{
		statId: "PlayerDamageResist",
		display: '%',
		match: /Reduce damage taken from players by ([0-9]+)%/i,
	},
	{
		statId: "HARestore",
		display: '%',
		match: /Increases the Magicka and Stamina restoration benefit from the Constitution Passive ability by ([0-9]+)%/i,
	},
	{
		statId: "RollDodgeDuration",
		match: /After roll dodging, continue to dodge attacks for an additional ([0-9]+\.?[0-9]*) seconds/i,
	},
	{
		statId: "NegativeEffectDuration",
		display: '%',
		match: /Reduce the duration of negative effects on you by ([0-9]+)%/i,
	},
	{
		statId: "SprintCost",
		display: '%',
		match: /Reduces Stamina cost for sprinting and crouching by ([0-9]+)%/i,
	},
	{
		statId: "SprintCost",
		display: '%',
		match: /Sprint cost reduced by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "SprintCost",
		display: '%',
		match: /Sprint costs ([0-9]+\.?[0-9]*)% less/i,
	},	
	{
		statId: "SneakCost",
		display: '%',
		match: /Reduces Stamina cost for sprinting and crouching by ([0-9]+)%/i,
	},
	{
		statId: "SneakCost",
		display: '%',
		match: /Reduces crouch cost by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "SneakCost",
		display: '%',
		match: /Reduce Sneak cost by ([0-9]+\.?[0-9]*)%/i,
	},	
	{
		statId: "BowRange",
		match: /Increase range of bow attacks by ([0-9]+) meters/i,
	},
	{
		statId: "LAHADamage",
		display: '%',
		category: "Skill",
		match: /Light attack and heavy attack damage increased by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "HADamage",
		match: /Your fully charged Heavy Attacks do an additional ([0-9]+\.?[0-9]*) damage/i,
	},
	{
		statId: "HADamage",
		display: '%',
		category: "Skill",
		match: /Your fully charged heavy attacks deal ([0-9]+\.?[0-9]*)% additional damage/i,
	},
	{
		statId: "FireEffectDuration",
		match: /Increases duration of your damaging fire effects by ([0-9]+\.?[0-9]*) seconds/i,
	},
	{
		statId: "RunSpeed",
		display: '%',
		match: /movement speed increased by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "BlockMitigation",
		display: '%',
		match: /Increase your block mitigation by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "BlockMitigation",
		display: '%',
		match: /Increase block mitigation by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		category: "Skill",
		statId: "Magicka",
		display: '%',
		match: /Increase Maximum Magicka by ([0-9]+\.?[0-9]*)%./i,
	},
	{
		statId: "BowAbilityCost",
		display: '%',
		match: /Reduce cost of bow abilities by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "BowAbilityDamage",
		display: '%',
		match: /Reduce cost of bow abilities by [0-9]+\.?[0-9]*% and increase their damage by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "HealingDone",
		display: '%',
		match: /Increases healing done by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "HealingDone",
		display: '%',
		match: /Increase healing done by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "Health",
		match: /Increase Max Health for up to 12 group members by ([0-9]+\.?[0-9]*)/i,
	},
	{
		category: "Skill",
		statId: "StaminaRecovery",
		display: '%',
		match: /Increase Max Health for up to 12 group members by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		category: "Skill",
		statId: "StaminaRecovery",
		display: '%',
		match: /Increase Max Health for up to 12 group members by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "ResurrectTime",
		display: '%',
		match: /decrease time to resurrect an ally by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "BossDamageResist",
		display: '%',
		match: /you to take ([0-9]+\.?[0-9]*)% less damage from Boss/i,
	},
	{
		statId: "SneakRange",
		match: /Reduce the range you can be detected while hidden by ([0-9]+\.?[0-9]*) meters/i,
	},
	{
		statId: "SneakRange",
		match: /Decrease detection radius by ([0-9]+\.?[0-9]*) meters/i,
	},
	{
		statId: "SneakDetectRange",
		category: "Skill",
		display: '%',
		match: /Stealth detection radius increased by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "SneakDetectRange",
		match: /Increases stealth detection radius by 2 meters/i,
	},	
	{
		statId: "BreakFreeCost",
		display: '%',
		match: /Reduce the cost of Break Free by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "TwiceBornStar",
		match: /Allows you to have two Mundus Stone Boons at the same time/i,
	},
	
		// Optionally toggled effects
	{
		id: "Orgnum's Scales",
		setBonusCount: 4,
		toggle: true,
		enabled: false,
		category: "Skill",
		statId: "HealthRegen",
		display: '%',		
		match: /If below 60% Health, increase Health Recovery by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		id: "Permafrost",
		setBonusCount: 4,
		toggle: true,
		enabled: false,
		statId: "HealthRegen",
		match: /Gain ([0-9]+\.?[0-9]*) Health Recovery while you have a Damage Shield/i,
	},
	{
		id: "Willow's Path",
		setBonusCount: 4,
		toggle: true,
		enabled: false,
		category: "Skill",
		statId: "HealthRegen",
		display: '%',
		match: /Increase all recovery in combat by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		id: "Willow's Path",
		setBonusCount: 4,
		toggle: true,
		enabled: false,
		category: "Skill",
		statId: "MagickaRegen",
		display: '%',
		match: /Increase all recovery in combat by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		id: "Willow's Path",
		setBonusCount: 4,
		toggle: true,
		enabled: false,
		category: "Skill",
		statId: "StaminaRegen",
		display: '%',
		match: /Increase all recovery in combat by ([0-9]+\.?[0-9]*)%/i,
	},
	
		// Other Effects
	{
		statId: "OtherEffects",
		match: /Increase Max Health for up to 12 group members by ([0-9]+\.?[0-9]*)/i,
	},
	{
		statId: "OtherEffects",
		display: '%',
		match: /increase the damage of your bow abilities against players by ([0-9]+\.?[0-9]*)%/i,
	},
	
];

	
ESO_ENCHANT_ARMOR_MATCHES = [
	{
		statId: "Health",
		match: /Adds ([0-9]+) Maximum Health/i,
	},
	{
		statId: "Magicka",
		match: /Adds ([0-9]+) Maximum Magicka/i,
	},
	{
		statId: "Stamina",
		match: /Adds ([0-9]+) Maximum Stamina/i,
	},
	{
		statId: "BashDamage",
		match: /Increase bash damage by ([0-9]+)/i,
	},
	{
		statId: "PhysicalResist",
		match: /Adds ([0-9]+) Physical Resistance/i,
	},
	{
		statId: "SpellResist",
		match: /Adds ([0-9]+) Spell Resistance/i,
	},
	{
		statId: "DiseaseResist",
		match: /Adds ([0-9]+) Disease Resistance/i,
	},
	{
		statId: "PoisonResist",
		match: /Adds ([0-9]+) Poison Resistance/i,
	},
	{
		statId: "FireResist",
		match: /Adds ([0-9]+) Flame Resistance/i,
	},
	{
		statId: "ColdResist",
		match: /Adds ([0-9]+) Cold Resistance/i,
	},
	{
		statId: "ShockResist",
		match: /Adds ([0-9]+) Shock Resistance/i,
	},
	{
		statId: "HealthRegen",
		match: /Adds ([0-9]+) Health Recovery/i,
	},
	{
		statId: "MagickaRegen",
		match: /Adds ([0-9]+) Magicka Recovery/i,
	},
	{
		statId: "StaminaRegen",
		match: /Adds ([0-9]+) Stamina Recovery/i,
	},
	{
		statId: "SpellDamage",
		match: /Adds ([0-9]+) Spell Damage/i,
	},
	{
		statId: "WeaponDamage",
		match: /Adds ([0-9]+) Weapon Damage/i,
	},
	{
		statId: "PotionDuration",
		match: /Increase the duration of potion effects by ([0-9]+\.?[0-9]*) seconds/i,
	},
	{
		statId: "PotionCooldown",
		match: /Reduce the cooldown of potions below this item's level by ([0-9]+\.?[0-9]*) seconds/i,
	},
	{
		statId: "StaminaCost",
		match: /Reduce Stamina cost of abilities by ([0-9]+\.?[0-9]*)/i,
	},
	{
		statId: "MagickaCost",
		match: /Reduce Magicka cost of abilities by ([0-9]+\.?[0-9]*)/i,
	},
	{
		statId: "MagickaCost",
		match: /Reduce Magicka cost of spells by ([0-9]+\.?[0-9]*)/i,
	},
	{
		statId: "BashCost",
		match: /Reduce cost of bash by ([0-9]+\.?[0-9]*)/i,
	},
	{
		statId: "BlockCost",
		match: /Reduce cost of blocking by ([0-9]+\.?[0-9]*)/i,
	},
	
];


ESO_ENCHANT_WEAPON_MATCHES = [
	{
		modValue: 0.5,
		statId: "WeaponDamage",
		match: /grants ([0-9]+) additional Weapon Damage/i,
	},
	{
		statId: "WeaponDamage",
		match: /increases Weapon Damage by ([0-9]+)/i,
	},
	{
		statId: "WeaponDamage",
		match: /increase Weapon Damage by ([0-9]+)/i,
	},
	{
		statId: "WeaponDamage",
		match: /gain ([0-9]+) Weapon Damage/i,
	},
	{
		statId: "SpellDamage",
		match: /gain ([0-9]+) Spell Damage/i,
	},
	{
		statId: "SpellDamage",
		match: /grants ([0-9]+) additional Spell Damage/i,
	},
	{
		category: "Set",
		statId: "SpellCrit",
		match: /grants ([0-9]+) additional Spell Critical/i,
	},
	{
		statId: "Health",
		match: /grants ([0-9]+) Max Health/i,
	},
	{
		statId: "Stamina",
		match: /grants ([0-9]+) Max Stamina/i,
	},
	{
		statId: "Magicka",
		match: /grants ([0-9]+) Max Magicka/i,
	},
	{
		statId: "Health",
		match: /increase Max Health by ([0-9]+)/i,
	},
	{
		statId: "Stamina",
		match: /increase Max Stamina by ([0-9]+)/i,
	},
	{
		statId: "Magicka",
		match: /increase Max Magicka by ([0-9]+)/i,
	},
	{
		statId: "Health",
		match: /Max Health increased by ([0-9]+)/i,
	},
	{
		statId: "Stamina",
		match: /Max Stamina increased by ([0-9]+)/i,
	},
	{
		statId: "Magicka",
		match: /Max Magicka increased by ([0-9]+)/i,
	},
	{
		statId: "Health",
		match: /Max Health is increased by ([0-9]+)/i,
	},
	{
		statId: "Stamina",
		match: /Max Stamina is increased by ([0-9]+)/i,
	},
	{
		statId: "Magicka",
		match: /Max Magicka is increased by ([0-9]+)/i,
	},
  	{
		statId: "OtherEffects",
		match: /Deals ([0-9]+) Magic Damage/i,
	},
	{
		statId: "",
		match: /and restores ([0-9]+) Health/i,
	},
	{
		statId: "",
		match: /and restores ([0-9]+) Magicka/i,
	},
	{
		statId: "",
		match: /and restores ([0-9]+) Stamina/i,
	},
	{
		statId: "OtherEffects",
		match: /Reduce's targets armor by ([0-9]+) for restores [0-9]+ seconds/i,
	},
	{
		statId: "OtherEffects",
		match: /Deals ([0-9]+) unresistable damage/i,
	},
	{
		statId: "OtherEffects",
		match: /Deals ([0-9]+) flame damage/i,
	},
	{
		statId: "OtherEffects",
		match: /Deals ([0-9]+) disease damage/i,
	},
	{
		statId: "OtherEffects",
		match: /Deals ([0-9]+) cold damage/i,
	},
	{
		statId: "OtherEffects",
		match: /Deals ([0-9]+) poison damage/i,
	},
	{
		statId: "OtherEffects",
		match: /Deals ([0-9]+) shock damage/i,
	},
	{
		statId: "OtherEffects",
		match: /Deals ([0-9]+) Magic Damage to Undead and Daedra/i,
	},
	{
		statId: "OtherEffects",
		match: /Grants a ([0-9]+) point Damage Shield for [0-9]+ seconds/i,
	},
	{
		statId: "OtherEffects",
		match: /Reduce target Weapon Damage and Spell Damage by ([0-9]+) for [0-9]+ seconds/i,
	},
];


ESO_ABILITYDESC_MATCHES = [
	{
		statId: "Health",
		match: /Max Health by ([0-9]+)/i,
	},                  
	{
		statId: "Magicka",
		match: /Max Magicka by ([0-9]+)/i,
	},
	{
		statId: "Magicka",
		match: /Max Magicka and Max Stamina by ([0-9]+)/i,
	},
	{
		statId: "Stamina",
		match: /Max Stamina by ([0-9]+)/i,
	},
	{
		statId: "HealthRegen",
		match: /Health Recovery by ([0-9]+)/i,
	},
	{
		statId: "MagickaRegen",
		match: /Magicka Recovery by ([0-9]+)/i,
	},
	{
		statId: "MagickaRegen",
		match: /Magicka and Stamina Recovery by ([0-9]+)/i,
	},
	{
		statId: "StaminaRegen",
		match: /Stamina Recovery by ([0-9]+)/i,
	},
	{
		statId: "StaminaRegen",
		match: /Stamina and Magicka Recovery by ([0-9]+)/i,
	},
];


function GetEsoInputValues(mergeComputedStats)
{
	var inputValues = {};
	if (mergeComputedStats == null) mergeComputedStats = false;
	
	g_EsoInputStatSources = {};
	
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
	
	GetEsoInputItemValues(inputValues, "Head");
	GetEsoInputItemValues(inputValues, "Shoulders");
	GetEsoInputItemValues(inputValues, "Chest");
	GetEsoInputItemValues(inputValues, "Hands");
	GetEsoInputItemValues(inputValues, "Waist");
	GetEsoInputItemValues(inputValues, "Legs");
	GetEsoInputItemValues(inputValues, "Feet");
	GetEsoInputItemValues(inputValues, "Neck");
	GetEsoInputItemValues(inputValues, "Ring1");
	GetEsoInputItemValues(inputValues, "Ring2");
	
	GetEsoInputGeneralValues(inputValues, "Food", "Food");
	GetEsoInputGeneralValues(inputValues, "Buff", "Potion");
	
	if (g_EsoBuildActiveWeapon == 1)
	{
		GetEsoInputItemValues(inputValues, "MainHand1");
		GetEsoInputItemValues(inputValues, "OffHand1");
		GetEsoInputItemValues(inputValues, "Poison1");
	}
	else
	{
		GetEsoInputItemValues(inputValues, "MainHand2");
		GetEsoInputItemValues(inputValues, "OffHand2");
		GetEsoInputItemValues(inputValues, "Poison2");
	}
	
	inputValues.ArmorTypes = 0;
	if (inputValues.ArmorLight  > 0) ++inputValues.ArmorTypes;
	if (inputValues.ArmorMedium > 0) ++inputValues.ArmorTypes;
	if (inputValues.ArmorHeavy  > 0) ++inputValues.ArmorTypes;
	AddEsoInputStatSource("ArmorTypes", { source: "Worn Armor", value: inputValues.ArmorTypes });
	
	UpdateEsoItemSets();
	GetEsoInputSetValues(inputValues);
	
	GetEsoInputMundusValues(inputValues);
	GetEsoInputCPValues(inputValues);
	GetEsoInputTargetValues(inputValues);
	GetEsoInputMiscValues(inputValues);
	
	if (mergeComputedStats === true) 
	{
		for (var name in g_EsoComputedStats)
		{
			inputValues[name] = g_EsoComputedStats[name].value;
		}
	}
	
	return inputValues;
}


function GetEsoInputSetValues(inputValues)
{
	for (var setName in g_EsoBuildSetData)
	{
		var setData = g_EsoBuildSetData[setName];
		GetEsoInputSetDataValues(inputValues, setData);
	}
}


function GetEsoInputSetDataValues(inputValues, setData)
{
	if (setData == null || setData.count <= 0) return;
	setData.rawOutput = [];
	
	for (var i = 0; i < 5; ++i)
	{
		var setBonusCount = parseInt(setData.items[0]['setBonusCount' + (i+1)]);
		if (setBonusCount > setData.count) continue;
		
		var setDesc = setData.averageDesc[i];
		
		GetEsoInputSetDescValues(inputValues, setDesc, setBonusCount, setData);
	}
}


function GetEsoInputSetDescValues(inputValues, setDesc, setBonusCount, setData)
{
	var foundMatch = false;
	var addFinalEffect = false;
	
	if (setBonusCount < 0 || setDesc == "") return;
	
	for (var i = 0; i < ESO_SETEFFECT_MATCHES.length; ++i)
	{
		var matchData = ESO_SETEFFECT_MATCHES[i];
		var matches = setDesc.match(matchData.match);
		if (matches == null) continue;
		
			/* Ignore toggled effects that aren't on */
		if (matchData.toggle === true)
		{
			if (!IsEsoBuildToggledSetEnabled(matchData.id)) continue;
		}
		
		foundMatch = true;
		
		if (matchData.statId == "OtherEffects")
		{
			addFinalEffect = true;
		}
		else
		{
			var statValue = Math.floor(parseFloat(matches[1]));
			if (isNaN(statValue)) statValue = 1;
			
			var category = matchData.category || "Set";
			
			var display = matchData.display || "";
			if (display == "%") statValue = statValue/100;
		
			inputValues[category][matchData.statId] += statValue;
			AddEsoItemRawOutput(setData, category + "." + matchData.statId, statValue);
			AddEsoInputStatSource(category + "." + matchData.statId, { set: setData, setBonusCount: setBonusCount, value: statValue });
		}
	}
	
	if (!foundMatch || addFinalEffect)
	{
		AddEsoInputStatSource("OtherEffects", { other: true, set: setData, setBonusCount: setBonusCount, value: setDesc });
		AddEsoItemRawOutput(setData, "OtherEffects", setDesc);
	}
	
}


function GetEsoEnchantData(slotId)
{
	var itemData = null;
	var enchantData = {};
	
	if (g_EsoBuildEnchantData[slotId] == null) return null;
	
	if ($.isEmptyObject(g_EsoBuildEnchantData[slotId]))
		itemData = g_EsoBuildItemData[slotId];
	else
		itemData = g_EsoBuildEnchantData[slotId];
	
	if (itemData == null) return null;
	
	enchantData.itemId = itemData.itemId;
	enchantData.internalLevel = itemData.internalLevel;
	enchantData.internalSubtype = itemData.internalSubtype;
	enchantData.enchantId = itemData.enchantId;
	enchantData.enchantLevel = itemData.enchantLevel;
	enchantData.enchantSubtype = itemData.enchantSubtype;
	enchantData.enchantName = itemData.enchantName;
	enchantData.enchantDesc = itemData.enchantDesc;
	
	return enchantData;
}


function GetEsoInputGeneralValues(inputValues, outputId, slotId)
{
	var itemData = g_EsoBuildItemData[slotId];
	if (itemData == null || itemData.itemId == null || itemData.itemId == "") return false;
	
	itemData.rawOutput = {};
	
	GetEsoInputAbilityDescValues(inputValues, outputId, itemData, slotId);
}


function GetEsoInputAbilityDescValues(inputValues, outputId, itemData, slotId)
{
	var rawDesc = RemoveEsoDescriptionFormats(itemData.abilityDesc);
	if (rawDesc == "") return;
	
	for (var i = 0; i < ESO_ABILITYDESC_MATCHES.length; ++i)
	{
		var matchData = ESO_ABILITYDESC_MATCHES[i];
		var matches = rawDesc.match(matchData.match);
		if (matches == null) continue;
		
		var statValue = Math.floor(parseFloat(matches[1]));
		
		inputValues[outputId][matchData.statId] += statValue;
		AddEsoItemRawOutput(itemData, outputId + "." + matchData.statId, statValue);
		AddEsoInputStatSource(outputId + "." + matchData.statId, { item: itemData, value: statValue, slotId: slotId });
	}
}


function AddEsoItemRawOutput(itemData, statId, value)
{
	if (itemData.rawOutput[statId] == null) 
	{
		itemData.rawOutput[statId] = "";
	}
	
	itemData.rawOutput[statId] += value;
}


function GetEsoInputItemValues(inputValues, slotId)
{
	var itemData = g_EsoBuildItemData[slotId];
	if (itemData == null || itemData.itemId == null || itemData.itemId == "") return false;
	
	itemData.rawOutput = {};
	
	var traitMatch = itemData.traitDesc.match(/[0-9]+.?[0-9]*/);
	var traitValue = 0;
	if (traitMatch != null && traitMatch[0] != null) traitValue = parseFloat(traitMatch[0]);
	
	if (itemData.armorType == 1)
	{
		++inputValues.ArmorLight;
		AddEsoItemRawOutput(itemData, "ArmorLight", 1);
		AddEsoInputStatSource("ArmorLight", { item: itemData, value: 1, slotId:slotId });
	}
	else if (itemData.armorType == 2)
	{
		++inputValues.ArmorMedium;
		AddEsoItemRawOutput(itemData, "ArmorMedium", 1);
		AddEsoInputStatSource("ArmorMedium", { item: itemData, value: 1, slotId:slotId });
	}
	else if (itemData.armorType == 3)
	{
		++inputValues.ArmorHeavy;
		AddEsoItemRawOutput(itemData, "ArmorHeavy", 1);
		AddEsoInputStatSource("ArmorHeavy", { item: itemData, value: 1, slotId:slotId });
	}
	
	if (itemData.armorRating > 0)
	{
		var factor = 1;
		var bonusResist = 0;
		
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
			bonusResist = Math.floor(itemData.armorRating*traitValue/100*factor);
		}
		
		var armorRating = Math.floor(itemData.armorRating * factor) + bonusResist;
		
		inputValues.Item.SpellResist += armorRating;
		inputValues.Item.PhysicalResist += armorRating;
		
		AddEsoItemRawOutput(itemData, "Item.SpellResist", armorRating);
		AddEsoItemRawOutput(itemData, "Item.PhysicalResist", armorRating);
		
		AddEsoInputStatSource("Item.SpellResist", { item: itemData, value: armorRating, slotId:slotId });
		AddEsoInputStatSource("Item.PhysicalResist", { item: itemData, value: armorRating, slotId:slotId });
	}
	
	if (itemData.weaponPower > 0)
	{
		var weaponPower = parseFloat(itemData.weaponPower);
		
		if (slotId == "OffHand1" || slotId == "OffHand2") 
		{
			weaponPower = Math.floor(weaponPower * 0.287);
		}
		
		if (itemData.trait == 26)	// Weapon nirnhoned
		{
			weaponPower = Math.floor(weaponPower * (1 + traitValue/100));
		}
		
		inputValues.Item.WeaponDamage += weaponPower;
		inputValues.Item.SpellDamage += weaponPower;
		
		AddEsoItemRawOutput(itemData, "Item.WeaponDamage", weaponPower);
		AddEsoItemRawOutput(itemData, "Item.SpellDamage", weaponPower);
		
		AddEsoInputStatSource("Item.WeaponDamage", { item: itemData, value: weaponPower, slotId:slotId });
		AddEsoInputStatSource("Item.SpellDamage", { item: itemData, value: weaponPower, slotId:slotId });
	}
	
	if (itemData.trait == 18) // Divines
	{
		inputValues.Item.Divines += traitValue/100;
		AddEsoItemRawOutput(itemData, "Item.Divines", traitValue/100);
		AddEsoInputStatSource("Item.Divines", { item: itemData, value: traitValue/100, slotId:slotId });
	}
	else if (itemData.trait == 17) //Prosperous
	{
		inputValues.Item.Prosperous += traitValue/100;
		AddEsoItemRawOutput(itemData, "Item.Prosperous", traitValue/100);
		AddEsoInputStatSource("Item.Prosperous", { item: itemData, value: traitValue/100, slotId:slotId });
	}
	else if (itemData.trait == 12) //Impenetrable
	{
		inputValues.Item.CritResist += traitValue;
		AddEsoItemRawOutput(itemData, "Item.CritResist", traitValue);
		AddEsoInputStatSource("Item.CritResist", { item: itemData, value: traitValue, slotId:slotId });
	}
	else if (itemData.trait == 11) //Sturdy
	{
		inputValues.Item.Sturdy += traitValue/100;
		AddEsoItemRawOutput(itemData, "Item.Sturdy", traitValue/100);
		AddEsoInputStatSource("Item.Sturdy", { item: itemData, value: traitValue/100, slotId:slotId });
	}
	else if (itemData.trait == 15 || itemData == 6) //Training
	{
		inputValues.Item.Training += traitValue/100;
		AddEsoItemRawOutput(itemData, "Item.Training", traitValue/100);
		AddEsoInputStatSource("Item.Training", { item: itemData, value: traitValue/100, slotId:slotId });
	}
	else if (itemData.trait == 21) //Healthy
	{
		inputValues.Item.Health += traitValue;
		itemData.rawOutput["Item.Health"] = traitValue;
		AddEsoItemRawOutput(itemData, "Item.Health", traitValue);
		AddEsoInputStatSource("Item.Health", { item: itemData, value: traitValue, slotId:slotId });
	}
	else if (itemData.trait == 22) //Arcane
	{
		inputValues.Item.Magicka += traitValue;
		AddEsoItemRawOutput(itemData, "Item.Magicka", traitValue);
		AddEsoInputStatSource("Item.Magicka", { item: itemData, value: traitValue, slotId:slotId });
	}
	else if (itemData.trait == 23) //Robust
	{
		inputValues.Item.Stamina += traitValue;
		AddEsoItemRawOutput(itemData, "Item.Stamina", traitValue);
		AddEsoInputStatSource("Item.Stamina", { item: itemData, value: traitValue, slotId:slotId });
	}	
	else if (itemData.trait == 14) //Well Fitted
	{
		inputValues.Item.SprintCost += traitValue/100;
		inputValues.Item.RollDodgeCost += traitValue/100;
		AddEsoItemRawOutput(itemData, "Item.SprintCost", traitValue/100);
		AddEsoItemRawOutput(itemData, "Item.RollDodgeCost", traitValue/100);
		AddEsoInputStatSource("Item.SprintCost", { item: itemData, value: traitValue/100, slotId:slotId });
		AddEsoInputStatSource("Item.RollDodgeCost", { item: itemData, value: traitValue/100, slotId:slotId });
	}
	else if (itemData.trait == 7) //Sharpened
	{
		inputValues.Item.SpellPenetration += traitValue/100;
		inputValues.Item.PhysicalPenetration += traitValue/100;
		AddEsoItemRawOutput(itemData, "Item.SpellPenetration", traitValue/100);
		AddEsoItemRawOutput(itemData, "Item.PhysicalPenetration", traitValue/100);
		AddEsoInputStatSource("Item.SpellPenetration", { item: itemData, value: traitValue/100, slotId:slotId });
		AddEsoInputStatSource("Item.PhysicalPenetration", { item: itemData, value: traitValue/100, slotId:slotId });
	}
	else if (itemData.trait == 3) //Precise
	{
		inputValues.Item.SpellCrit += traitValue/100;
		inputValues.Item.WeaponCrit += traitValue/100;
		AddEsoItemRawOutput(itemData, "Item.SpellCrit", traitValue/100);
		AddEsoItemRawOutput(itemData, "Item.WeaponCrit", traitValue/100);
		AddEsoInputStatSource("Item.SpellCrit", { item: itemData, value: traitValue/100, slotId:slotId });
		AddEsoInputStatSource("Item.WeaponCrit", { item: itemData, value: traitValue/100, slotId:slotId });
	}
	else if (itemData.trait == 5) //Defending
	{
		inputValues.Item.Defending += traitValue/100;
		AddEsoItemRawOutput(itemData, "Item.Defending", traitValue/100);
		AddEsoInputStatSource("Item.Defending", { item: itemData, value: traitValue/100, slotId:slotId });
	}
	else if (itemData.trait == 2) //Charged
	{
	}
	else if (itemData.trait == 4) //Infused
	{
	}
	else if (itemData.trait == 1) //Powered
	{
		inputValues.Item.HealingDone += traitValue/100;
		AddEsoItemRawOutput(itemData, "Item.HealingDone", traitValue/100);
		AddEsoInputStatSource("Item.HealingDone", { item: itemData, value: traitValue/100, slotId:slotId });
	}
	else if (itemData.trait == 8) //Decisive
	{
		// TODO?
	}
	
	GetEsoInputItemEnchantValues(inputValues, slotId);
}


function IsEsoItemArmor(itemData)
{
	if (itemData.type == 1 && itemData.weaponType == 14) return true;
	if (itemData.type != 2) return false;
	return true;
}


function IsEsoItemWeapon(itemData)
{
	if (itemData.type != 1) return false;
	if (itemData.weaponType == 14) return false;
	return true;
}


function GetEsoInputItemEnchantValues(inputValues, slotId)
{
	var itemData = g_EsoBuildItemData[slotId];
	if (itemData == null || itemData.itemId == null || itemData.itemId == "") return false;
	
	var enchantData = GetEsoEnchantData(slotId);
	if (enchantData == null) return false;
	if (enchantData.enchantDesc == "") return true;
	
	var enchantFactor = 1;
	
		// Infused
	if (itemData.trait == 16 || itemData.trait == 4)
	{
		var rawDesc = RemoveEsoDescriptionFormats(itemData.traitDesc);
		var results = rawDesc.match(/by ([0-9]+\.?[0-9]*\%?)/);
		
		if (results != null && results.length !== 0) 
		{
			var infusedFactor = 1 + parseFloat(results[1])/100;
			if (isNaN(infusedFactor) || infusedFactor < 1) infusedFactor = 1;
			enchantFactor = enchantFactor * infusedFactor;
		}
	}
	
	if (slotId == "Waist" || slotId == "Feet" || slotId == "Shoulders" || slotId == "Hands")
	{
		enchantFactor = enchantFactor * 0.4044;
	}
	
	if (IsEsoItemWeapon(itemData))
	{
		GetEsoInputItemEnchantWeaponValues(inputValues, slotId, itemData, enchantData, enchantFactor);
	}
	else if (IsEsoItemArmor(itemData))
	{
		GetEsoInputItemEnchantArmorValues(inputValues, slotId, itemData, enchantData, enchantFactor);
	}
	
	
	return true;
}


function GetEsoInputItemEnchantArmorValues(inputValues, slotId, itemData, enchantData, enchantFactor)
{
	var rawDesc = RemoveEsoDescriptionFormats(enchantData.enchantDesc);
	
	for (var i = 0; i < ESO_ENCHANT_ARMOR_MATCHES.length; ++i)
	{
		var matchData = ESO_ENCHANT_ARMOR_MATCHES[i];
		var matches = rawDesc.match(matchData.match);
		if (matches == null) continue;
		
		var statValue = Math.floor(parseFloat(matches[1]) * enchantFactor);
		
		inputValues.Item[matchData.statId] += statValue;
		AddEsoItemRawOutput(itemData, "Item." + matchData.statId, statValue);
		AddEsoInputStatSource("Item." + matchData.statId, { item: itemData, enchant: enchantData, value: statValue, slotId: slotId });
	}
}


function RemoveEsoDescriptionFormats(text)
{
	if (text == null) return "";
	return text.replace(/\|c[a-fA-F0-9]{6}([^|]*)\|r/g, '$1');
}


function ReplaceEsoWeaponMatch(match, p1, offset, string, enchantFactor)
{
	var newValue = Math.floor(parseFloat(p1) * enchantFactor);
	return match.replace(p1, newValue);
}


function GetEsoInputItemEnchantWeaponValues(inputValues, slotId, itemData, enchantData, enchantFactor)
{
	var rawDesc = RemoveEsoDescriptionFormats(enchantData.enchantDesc);
	var addFinalEffect = false;
	
	for (var i = 0; i < ESO_ENCHANT_WEAPON_MATCHES.length; ++i)
	{
		var matchData = ESO_ENCHANT_WEAPON_MATCHES[i];
		var matches = rawDesc.match(matchData.match);
		if (matches == null) continue;
		
		var modValue = matchData.modValue || 1;
		
		if (matchData.statId == "")
		{
			rawDesc = rawDesc.replace(matchData.match, function(match, p1, offset, string) { return ReplaceEsoWeaponMatch(match, p1, offset, string, enchantFactor); });
			
		}
		else if (matchData.statId == "OtherEffects")
		{
			rawDesc = rawDesc.replace(matchData.match, function(match, p1, offset, string) { return ReplaceEsoWeaponMatch(match, p1, offset, string, enchantFactor); });
			addFinalEffect = true;
		}
		else
		{
			var statValue = Math.floor(parseFloat(matches[1]) * enchantFactor * modValue);
			var category = matchData.category || "Item";
			
			inputValues[category][matchData.statId] += statValue;
			AddEsoItemRawOutput(itemData, category + "." + matchData.statId, statValue);
			AddEsoInputStatSource(category + "." + matchData.statId, { item: itemData, enchant: enchantData, value: statValue, slotId: slotId });
		}
	}
	
	if (addFinalEffect) 
	{
		AddEsoInputStatSource("OtherEffects", { other: true, item: itemData, enchant: enchantData, value: rawDesc, slotId: slotId });
		AddEsoItemRawOutput(itemData, "WeaponEnchant", rawDesc);
	}
}


function UpdateEsoItemSets()
{
	g_EsoBuildSetData = {};
	
	for (var key in g_EsoBuildItemData)
	{
		if (g_EsoBuildActiveWeapon == 1 && (key == "MainHand2" || key == "OffHand2" || key == "Poison2")) continue;
		if (g_EsoBuildActiveWeapon == 2 && (key == "MainHand1" || key == "OffHand1" || key == "Poison1")) continue;
		
		var data = g_EsoBuildItemData[key];
		var setName = data.setName;
		
		if (setName == null || setName == "") continue;
		
		if (g_EsoBuildSetData[setName] == null) 
		{
			g_EsoBuildSetData[setName] = {
					name: setName,
					count: 0,
					items: [],
			};
		}
		
		++g_EsoBuildSetData[setName].count;
		g_EsoBuildSetData[setName].items.push(data);
		AddEsoItemRawOutput(data, "Set." + setName, 1);
		AddEsoInputStatSource("Set." + setName, { set: setName, item: data });
	}
	
	ComputeEsoBuildAllSetData();
	UpdateEsoBuildToggledSetData();
}


function GetEsoInputTargetValues(inputValues)
{
	inputValues.Target.Resistance = parseFloat($("#esotbTargetResistance").val());
	inputValues.Target.PenetrationFlat = parseFloat($("#esotbTargetPenetrationFlat").val());
	inputValues.Target.PenetrationFactor = parseFloat($("#esotbTargetPenetrationFactor").val()) / 100;
	inputValues.Target.DefenseBonus = parseFloat($("#esotbTargetDefenseBonus").val()) / 100;
	inputValues.Target.AttackBonus = parseFloat($("#esotbTargetAttackBonus").val()) / 100;
}


function GetEsoInputMiscValues(inputValues)
{
	inputValues.Misc.SpellCost = parseFloat($("#esotbMiscSpellCost").val());
}


function GetEsoInputMundusValues(inputValues)
{
	inputValues.Mundus.Name = $("#esotbMundus").val();
	GetEsoInputMundusNameValues(inputValues, inputValues.Mundus.Name);
	
	if (IsTwiceBornStarEnabled())
	{
		inputValues.Mundus.Name2 = $("#esotbMundus2").val();
		GetEsoInputMundusNameValues(inputValues, inputValues.Mundus.Name2);
	}
}


function GetEsoInputMundusNameValues(inputValues, mundusName)
{
	
	if (mundusName == "The Lady")
	{
		inputValues.Mundus.PhysicalResist = 1280;
		AddEsoInputStatSource("Mundus.PhysicalResist", { mundus: mundusName, value: inputValues.Mundus.PhysicalResist });
	}
	else if (mundusName == "The Lover")
	{
		inputValues.Mundus.SpellResist = 1280;
		AddEsoInputStatSource("Mundus.SpellResist", { mundus: mundusName, value: inputValues.Mundus.SpellResist });
	}
	else if (mundusName == "The Lord")
	{
		inputValues.Mundus.Health = 1280;
		AddEsoInputStatSource("Mundus.Health", { mundus: mundusName, value: inputValues.Mundus.Health });
	}
	else if (mundusName == "The Mage")
	{
		inputValues.Mundus.Magicka = 1280;
		AddEsoInputStatSource("Mundus.Magicka", { mundus: mundusName, value: inputValues.Mundus.Magicka });
	}
	else if (mundusName == "The Tower")
	{
		inputValues.Mundus.Stamina = 1280;
		AddEsoInputStatSource("Mundus.Stamina", { mundus: mundusName, value: inputValues.Mundus.Stamina });
	}
	else if (mundusName == "The Atronach")
	{
		inputValues.Mundus.MagickaRegen = 210;
		AddEsoInputStatSource("Mundus.MagickaRegen", { mundus: mundusName, value: inputValues.Mundus.MagickaRegen });
	}
	else if (mundusName == "The Serpent")
	{
		inputValues.Mundus.StaminaRegen = 210;
		AddEsoInputStatSource("Mundus.StaminaRegen", { mundus: mundusName, value: inputValues.Mundus.StaminaRegen });
	}
	else if (mundusName == "The Shadow")
	{
		inputValues.Mundus.CritDamage = 0.12;
		AddEsoInputStatSource("Mundus.CritDamage", { mundus: mundusName, value: inputValues.Mundus.CritDamage });
	}
	else if (mundusName == "The Ritual")
	{
		inputValues.Mundus.HealingDone = 0.10;
		AddEsoInputStatSource("Mundus.HealingDone", { mundus: mundusName, value: inputValues.Mundus.HealingDone });
	}
	else if (mundusName == "The Thief")
	{
		inputValues.Mundus.SpellCrit = 0.11;	//TODO: Absolute values?
		inputValues.Mundus.WeaponCrit = 0.11;
		AddEsoInputStatSource("Mundus.SpellCrit", { mundus: mundusName, value: inputValues.Mundus.SpellCrit });
		AddEsoInputStatSource("Mundus.WeaponCrit", { mundus: mundusName, value: inputValues.Mundus.WeaponCrit });
	}
	else if (mundusName == "The Warrior")
	{
		inputValues.Mundus.WeaponDamage = 166;
		AddEsoInputStatSource("Mundus.WeaponDamage", { mundus: mundusName, value: inputValues.Mundus.WeaponDamage });
	}
	else if (mundusName == "The Apprentice")
	{
		inputValues.Mundus.SpellDamage = 166;
		AddEsoInputStatSource("Mundus.SpellDamage", { mundus: mundusName, value: inputValues.Mundus.SpellDamage });
	}
	else if (mundusName == "The Steed")
	{
		inputValues.Mundus.HealthRegen = 210;
		inputValues.Mundus.RunSpeed = 0.05;
		AddEsoInputStatSource("Mundus.HealthRegen", { mundus: mundusName, value: inputValues.Mundus.HealthRegen });
		AddEsoInputStatSource("Mundus.RunSpeed", { mundus: mundusName, value: inputValues.Mundus.RunSpeed });
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
	if (inputValues.ArmorHeavy >= 5) ParseEsoCPValue(inputValues, "PhysicalResist", 60624);
	ParseEsoCPValue(inputValues, "DamageShield", 59948);
	ParseEsoCPValue(inputValues, "HADamageResist", 59953);
	ParseEsoCPValue(inputValues, "HealingReceived", 63851);
	
		/* Lady */
	if (inputValues.ArmorLight >= 5) ParseEsoCPValue(inputValues, "PhysicalResist", 60502);
	ParseEsoCPValue(inputValues, "DOTResist", 63850);
	ParseEsoCPValue(inputValues, "PhysicalDamageResist", 63844);
	ParseEsoCPValue(inputValues, "MagickaDamageResist", 63843);
	
		/* Steed */
	if (inputValues.ArmorMedium >= 5) ParseEsoCPValue(inputValues, "PhysicalResist", 59120);
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
	
	var cpName = cpDesc.prev().text();
	
	var text = RemoveEsoDescriptionFormats(cpDesc.text());
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
			AddEsoInputStatSource("CP." + statIds[i], { cp: cpName, abilityId: abilityId, value: value });
		}
	}
	else
	{
		inputValues.CP[statIds] += value;
		AddEsoInputStatSource("CP." + statIds, { cp: cpName, abilityId: abilityId, value: value });
	}
	
	return true;
}


function AddEsoInputStatSource(statId, data)
{
	if (g_EsoInputStatSources[statId] == null) g_EsoInputStatSources[statId] = [];
	
	data.origStatId = statId;
	g_EsoInputStatSources[statId].push(data);
	
	var statIds = statId.split(".");
	
	if (statIds.length > 1)
	{
		var firstStatId = statIds.shift();
		//if (firstStatId == "Armor") return;
		
		var newStatId = statIds.join(".");
		if (g_EsoInputStatSources[newStatId] == null) g_EsoInputStatSources[newStatId] = [];
		g_EsoInputStatSources[newStatId].push(data);
	}
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
	UpdateEsoBuildMundusList2();
	UpdateEsoBuildSetInfo();
	UpdateEsoBuildToggleSets();
	UpdateEsoBuildItemLinkSetCounts();
}


function UpdateEsoReadOnlyStats(inputValues)
{
	if (inputValues == null) inputValues = GetEsoInputValues();
	
	$("#esotbEffectiveLevel").text(inputValues.EffectiveLevel);
}


function UpdateEsoComputedStat(statId, stat, inputValues, saveResult)
{
	var stack = [];
	var error = "";
	var computeIndex = 0;
	var round = stat.round;
	
	if (saveResult == null) saveResult = true;
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
		if (nextItem == "+" && itemValue >= 0) prefix = "+";
		if (nextItem == "*") prefix = "x";
		
		if (!(itemValue % 1 === 0))
		{
			itemValue = Number(itemValue).toFixed(3);
		}
		
		if (saveResult === true)
		{
			$(computeElements[computeIndex]).text(prefix + itemValue);
		}
		
		++computeIndex;
	}
	
	if (stack.length <= 0) error = "ERR";
	
	if (error === "")
	{
		var result = stack.pop();
		var display = stat.display;
		var displayResult = result;
		
		if (display == "percent") displayResult = Math.round(result*1000)/10 + "%";
		
		if (saveResult === true)
		{
			inputValues[statId] = result;
			stat.value = result;
			valueElement.text(displayResult);
		}
		
		return result;
	}
	
	if (saveResult === true)
	{
		inputValues[statId] = error;
		stat.value = error;
		valueElement.text(error);
	}
	
	return error;
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
	var newRace = $(this).val();
	EnableEsoRaceSkills(newRace);
	UpdateEsoComputedStatsList();
}


function OnEsoClassChange(e)
{
	var newClass = $(this).val();
	EnableEsoClassSkills(newClass);
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
	
	iconElement.attr("src", g_EsoGearIcons[slotId] || "");
	labelElement.text("");
	iconElement.attr("itemid", "");
	iconElement.attr("level", "");
	iconElement.attr("quality", "");
	iconElement.attr("setcount", "");
		
	g_EsoBuildItemData[slotId] = {};
	
	UnequipEsoEnchantSlot(slotId, false);
	
	if (update == null || update === true) UpdateEsoComputedStatsList();
	return true;
}


function UnequipEsoEnchantSlot(slotId, update)
{
	if (g_EsoBuildEnchantData[slotId] == null) return false;
	
	var element = $("#esotbItem" + slotId);
	var iconElement = $(element).find(".esotbItemIcon");
	var labelElement = $(element).find(".esotbItemLabel");
	
	iconElement.attr("enchantid", "");
	iconElement.attr("enchantlevel", "");
	iconElement.attr("enchantquality", "");
	iconElement.attr("enchantintlevel", "");
	iconElement.attr("enchantinttype", "");

	g_EsoBuildEnchantData[slotId] = {};
	
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
		UnequipEsoItemSlot(slotId, true);
		return;
	}
	
	var iconUrl = "http://esoicons.uesp.net" + itemData.icon.replace(".dds", ".png");
	var niceName = itemData.name.charAt(0).toUpperCase() + itemData.name.slice(1);
	
	iconElement.attr("src", iconUrl);
	labelElement.text(niceName);
	
	iconElement.attr("itemid", itemData.itemId);
	iconElement.attr("level", itemData.level);
	iconElement.attr("quality", itemData.quality);
	iconElement.attr("setcount", "0");
	
	if (itemData.equipType == 6)
	{
		if (slotId == "MainHand1") UnequipEsoItemSlot("OffHand1", false);
		if (slotId == "MainHand2") UnequipEsoItemSlot("OffHand2", false);
	}
	else if (slotId == "OffHand1")
	{
		if (g_EsoBuildItemData["MainHand1"].equipType == 6) UnequipEsoItemSlot("MainHand1", false);
		if (g_EsoBuildEnchantData["OffHand1"].type == 21 && itemData.weaponType != 14) UnequipEsoEnchantSlot("OffHand1", false);
		if (g_EsoBuildEnchantData["OffHand1"].type == 20 && itemData.weaponType == 14) UnequipEsoEnchantSlot("OffHand1", false);
	}
	else if (slotId == "OffHand2")
	{
		if (g_EsoBuildItemData["MainHand2"].equipType == 6) UnequipEsoItemSlot("MainHand2", false);
		if (g_EsoBuildEnchantData["OffHand2"].type == 21 && itemData.weaponType != 14) UnequipEsoEnchantSlot("OffHand2", false);
		if (g_EsoBuildEnchantData["OffHand2"].type == 20 && itemData.weaponType == 14) UnequipEsoEnchantSlot("OffHand2", false);
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
	
	if (itemData.type == 4 || itemData.type == 12)
	{
		queryParams.level = null;
		queryParams.quality = null;
	}
	
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


function OnEsoFormulaInputChange(e)
{
	var statId = $(this).attr("statid");
	if (statId == null || statId == "") return;
	
	var display = $(this).attr("display") || "";
	var value = $(this).val();
	if (value === "") value = 0;
	if (display == "%") value = parseFloat(value)/100.0;
	
	SetEsoInputValue(statId, value, g_EsoFormulaInputValues);
	
	var computeStatId = $("#esotbFormulaPopup").attr("statid");
	if (computeStatId == null || computeStatId == "") return;
	
	var stat = g_EsoComputedStats[computeStatId];
	if (stat == null) return;
	
	var newValue = UpdateEsoComputedStat(computeStatId, stat, g_EsoFormulaInputValues, false);
	$("#esotbFormInputInputResult").val(newValue);
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
	formulaPopup.attr("statid", statId);
	$("#esotbFormulaInputs").html(MakeEsoFormulaInputs(statId));
	
	$(".esotbFormInputInput").on("input", OnEsoFormulaInputChange);
	
	formulaPopup.show();
	ShowEsoBuildClickWall(formulaPopup);
	
	return true;
}


function MakeEsoFormulaInputs(statId)
{
	var output = "";
	var stat = g_EsoComputedStats[statId];
	if (stat == null) return "";
	
	var FUNCTIONS = { "floor" : 1, "round" : 1, "ceil" : 1, "pow" : 1 };
	
	var inputValues = GetEsoInputValues(true);
	var inputNames = {};
	
	g_EsoFormulaInputValues = inputValues;
	
	for (var i = 0; i < stat.compute.length; ++i)
	{
		var computeItem = stat.compute[i];
		var variables = computeItem.match(/[a-zA-Z]+[a-zA-Z_0-9\.]*/g);
		if (variables == null) continue;
		
		for (var j = 0; j < variables.length; ++j)
		{
			var name = variables[j];
			
			if (FUNCTIONS[name] != null) continue;
			
			if (inputNames[name] == null) inputNames[name] = 0;
			++inputNames[name];
		}
	}
	
	for (var name in inputNames)
	{	
		var value = GetEsoInputValue(name, inputValues);
		var statDetails = g_EsoInputStatDetails[name] || {};
		var extraAttr = "";
		var suffixText = "";
		
		if (statDetails.display == '%')
		{
			extraAttr = "display='%' ";	
			suffixText = '%';
			value = Math.round(value*1000)/10;
		}
			
		output += "<div class='esotbFormulaInput'>";
		output += "<div class='esotbFormInputName'>" + name + "</div>";
		output += "<input type='text' class='esotbFormInputInput' statid='" + name + "' value='" + value + "' size='5' " + extraAttr + "> ";
		output += suffixText;
		output += "</div>";
	}
	
	output += "<div class='esotbFormulaInput'>";
	output += "<div class='esotbFormInputResult'>" + stat.title + "</div>";
	output += "<input type='text' class='esotbFormInputInputResult' id='esotbFormInputInputResult' statid='" + stat.title + "' value='" + stat.value + "' size='5' readonly='readonly'>";
	output += "</div>";
	
	return output;
}


function SetEsoInputValue(name, value, inputValues)
{
	var ids = name.split(".");
	var data = inputValues;
	var newData = {};
	var lastId = "";
	
	for (var i = 0; i < ids.length; ++i)
	{
		lastId = ids[i];
		newData = data[ids[i]];
		if (newData == null) return false;
		
		if (typeof(newData) != "object") break;
		data = newData;
	}
	
	if (typeof(newData) == "object") return false;
	data[lastId] = parseFloat(value);	
	return true;
}


function GetEsoInputValue(name, inputValues)
{
	var ids = name.split(".");
	var data = inputValues;
	var newData = {};
	
	for (var i = 0; i < ids.length; ++i)
	{
		newData = data[ids[i]];
		if (newData == null) break;
		
		if (typeof(newData) != "object") break;
		data = newData;
	}
	
	if (typeof(newData) != "object") return newData;
	return 0;
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
	
	if (tabId == "esotbStatBlock5")
	{
		UpdateEsoBuildRawInputs();		
	}
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
		var statDetails = g_EsoInputStatDetails[key] || {};
		var value = itemData.rawOutput[key];
		var suffix = "";
		
		if (statDetails.display == '%') 
		{
			suffix = "%";
			value = value * 100;
		}
		detailsHtml += key + " = " + value + suffix + "<br/>";
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


function OnEsoItemEnchantClick(e)
{
	var parent = $(this).parent();
	
	SelectEsoItemEnchant(parent);
	
}


function SelectEsoItemEnchant(element)
{
	var slotId = element.attr("slotid");
	if (slotId == null || slotId == "") return false;
	
	var itemData = g_EsoBuildItemData[slotId];
	if (itemData == null || $.isEmptyObject(itemData)) return false;
	
	var equipType = element.attr("equiptype");
	var itemType = element.attr("itemtype");
	var enchantType = 0;
	
	if (itemType == 1) // Weapon
	{
		if (itemData.weaponType == 14)
			enchantType = 21;
		else
			enchantType = 20;
	}
	else if (itemType == 2) // Armor
	{
		if (equipType == 2 || equipType == 12)
			enchantType = 26;
		else
			enchantType = 21;
	}

	if (enchantType == 0) return false;
	
	var data = {
		onSelectItem: OnEsoSelectItemEnchant,
		itemType: enchantType,
	};
	
	var rootSearchPopup = UESP.showEsoItemSearchPopup(element, data);
	ShowEsoBuildClickWall(rootSearchPopup);
	
	return true;
}


function OnEsoSelectItemEnchant(itemData, element)
{
	var iconElement = $(element).find(".esotbItemIcon");
	var labelElement = $(element).find(".esotbItemLabel");
	
	var slotId = $(element).attr("slotId");
	if (slotId == null || slotId == "") return false;
	
	if ($.isEmptyObject(itemData))
	{
		iconElement.attr("enchantid", "");
		iconElement.attr("enchantlevel", "");
		iconElement.attr("enchantquality", "");
		iconElement.attr("enchantintlevel", "");
		iconElement.attr("enchantinttype", "");
		g_EsoBuildEnchantData[slotId] = {};
		
		UpdateEsoComputedStatsList();
		return;
	}
		
	iconElement.attr("enchantid", itemData.itemId);
	iconElement.attr("enchantlevel", itemData.level);
	iconElement.attr("enchantquality", itemData.quality);
	
	g_EsoBuildEnchantData[slotId] = itemData;
	RequestEsoEnchantData(itemData, element);
}


function RequestEsoEnchantData(itemData, element)
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
		done(function(data, status, xhr) { OnEsoEnchantDataReceive(data, status, xhr, element, itemData); }).
		fail(function(xhr, status, errorMsg) { OnEsoEnchantDataError(xhr, status, errorMsg); });
}


function OnEsoEnchantDataReceive(data, status, xhr, element, origItemData)
{
	var slotId = $(element).attr("slotId");
	if (slotId == null || slotId == "") return false;
	
	if (data.minedItem != null && data.minedItem[0] != null)
	{
		g_EsoBuildEnchantData[slotId] = data.minedItem[0];
		
		var iconElement = $(element).find(".esotbItemIcon");
		iconElement.attr("enchantintlevel", data.minedItem[0].internalLevel);
		iconElement.attr("enchantinttype", data.minedItem[0].internalSubtype);
		
		UpdateEsoComputedStatsList();
	}
	
}


function OnEsoEnchantDataError(xhr, status, errorMsg)
{
}


function OnEsoWeaponSelect1(e)
{
	$("#esotbWeaponSelect1").addClass("esotbWeaponSelect");
	$("#esotbItemMainHand1").addClass("esotbWeaponSelect");
	$("#esotbItemOffHand1").addClass("esotbWeaponSelect");
	$("#esotbItemPoison1").addClass("esotbWeaponSelect");
		
	$("#esotbWeaponSelect2").removeClass("esotbWeaponSelect");
	$("#esotbItemMainHand2").removeClass("esotbWeaponSelect");
	$("#esotbItemOffHand2").removeClass("esotbWeaponSelect");
	$("#esotbItemPoison2").removeClass("esotbWeaponSelect");
	
	g_EsoBuildActiveWeapon = 1;
	UpdateEsoComputedStatsList();
}


function OnEsoWeaponSelect2(e)
{
	$("#esotbWeaponSelect1").removeClass("esotbWeaponSelect");
	$("#esotbItemMainHand1").removeClass("esotbWeaponSelect");
	$("#esotbItemOffHand1").removeClass("esotbWeaponSelect");
	$("#esotbItemPoison1").removeClass("esotbWeaponSelect");
		
	$("#esotbWeaponSelect2").addClass("esotbWeaponSelect");
	$("#esotbItemMainHand2").addClass("esotbWeaponSelect");
	$("#esotbItemOffHand2").addClass("esotbWeaponSelect");
	$("#esotbItemPoison2").addClass("esotbWeaponSelect");
	
	g_EsoBuildActiveWeapon = 2;
	UpdateEsoComputedStatsList();
}


function UpdateEsoBuildRawInputs()
{
	var rawInputs = $("#esotbRawInputs");
	var output = "";
	var keys = Object.keys(g_EsoInputStatSources).sort();
	
	for (var i = 0; i < keys.length; ++i)
	{
		var key = keys[i];
		var statSource = g_EsoInputStatSources[key];
		
		if (HasEsoBuildRawInputSources(statSource))
		{
			output += GetEsoBuildRawInputSourcesHtml(key, statSource);
		}
	}
	
	rawInputs.html(output);
}


function HasEsoBuildRawInputSources(sourceData)
{
	if (sourceData == null) return false;
	
	for (var i = 0; i < sourceData.length; ++i)
	{
		if (sourceData[i].value != null && sourceData[i].value != 0) return true;
	}	
	
	return false;
}


function GetEsoBuildRawInputSourcesHtml(sourceName, sourceData)
{
	if (sourceData.length <= 0) return "";
	if (!ESO_TESTBUILD_SHOWALLRAWINPUTS && sourceName.indexOf(".") >= 0) return "";
	
	var output = "<div class='esotbRawInputItem'>";
	var sourceValue = "";
	
	output += "<div class='esotbRawInputName'>" + sourceName + ":</div>";
	
	for (var i = 0; i < sourceData.length; ++i)
	{
		output += GetEsoBuildRawInputSourceItemHtml(sourceData[i]);
	}
	
	output += "</div>";
	return output;
}


function GetEsoBuildRawInputSourceItemHtml(sourceItem)
{
	var output = "<div class='esotbRawInputValue'>";
	var value = sourceItem.value;
	var statDetails = g_EsoInputStatDetails[sourceItem.origStatId] || {};
	
	if (value == 0) return "";
	if (statDetails.display == '%') value = "" + (Math.round(value * 1000)/10) + "%";
		
	if (sourceItem.slotId != null && sourceItem.item != null && sourceItem.enchant != null)
	{
		if (sourceItem.other)
			output += "" + value + ": Enchantment on " + sourceItem.item.name + " in " + sourceItem.slotId + " equip slot";
		else
			output += "" + value + ": " + sourceItem.enchant.enchantName + " on " + sourceItem.item.name + " in " + sourceItem.slotId + " equip slot";
	}
	else if (sourceItem.slotId != null && sourceItem.item != null)
	{
		output += "" + value + ": " + sourceItem.item.name + " in " + sourceItem.slotId + " equip slot";
	}
	else if (sourceItem.abilityId != null && sourceItem.cp != null)
	{
		output += "" + value + ": " + sourceItem.cp + " CP ability (abilityId " + sourceItem.abilityId + ")";
	}	
	else if (sourceItem.set != null)
	{
		if (sourceItem.other)
			output += "" + value + ": " + sourceItem.set.name + " set bonus #" + sourceItem.setBonusCount + "";
		else
			output += "" + value + ": " + sourceItem.set.name + " set bonus #" + sourceItem.setBonusCount + "";
	}
	else if (sourceItem.mundus != null)
	{
		output += "" + value + ": " + sourceItem.mundus + " mundus stone";
	}		
	else if (sourceItem.source != null)
	{
		output += "" + value + ": " + sourceItem.source;
	}
	else
	{
		output += "" + value + ": Unknown";
	}
	
	output += "</div>";
	return output;
}


function ComputeEsoBuildAllSetData()
{
	for (var setName in g_EsoBuildSetData)
	{
		var setData = g_EsoBuildSetData[setName];
		ComputeEsoBuildSetData(setData);
	}
}


function ComputeEsoBuildSetData(setData)
{
	setData.parsedNumbers = [];
	setData.averageNumbers = [];
	setData.averageDesc = [];
	
	for (var i = 0; i < setData.items.length; ++i)
	{
		var item = setData.items[i];
		ComputeEsoBuildSetDataItem(setData, item);
	}
	
	ComputeEsoBuildSetDataAverages(setData);
	UpdateEsoBuildSetDesc(setData);
}


function UpdateEsoBuildSetDesc(setData)
{
	setData.averageDesc = [];
	if (setData.items.length == 0) return;
	
	for (var i = 0; i < setData.averageNumbers.length; ++i)
	{
		var numbers = setData.averageNumbers[i];
		var rawDesc = RemoveEsoDescriptionFormats(setData.items[0]["setBonusDesc" + (i+1)]);
		var matchCounter = 0;
		
		if (rawDesc == null)
		{
			setData.averageDesc[i] = "";
			continue;
		}
		
		setData.averageDesc[i] = rawDesc.replace(/[0-9]+\.?[0-9]*/g, function(match) {
			++matchCounter;
			return numbers[matchCounter-1] || "";
		});
	}
}


function ComputeEsoBuildSetDataAverages(setData)
{
	var sums = [];
	setData.averageNumbers = [];
	
	for (var i = 0; i < setData.parsedNumbers.length; ++i)
	{
		if (setData.parsedNumbers[i] == null) continue;
		var thisSum = [];
		var counts = [];
		
		for (var j = 0; j < setData.parsedNumbers[i].length; ++j)
		{
			if (setData.parsedNumbers[i][j] == null) continue;
			
			for (var k = 0; k < setData.parsedNumbers[i][j].length; ++k)
			{
				if (thisSum[k] == null) 
				{
					thisSum[k] = parseFloat(setData.parsedNumbers[i][j][k]);
					counts[k] = 1;
				}
				else
				{
					thisSum[k] += parseFloat(setData.parsedNumbers[i][j][k]);
					++counts[k];
				}
			}	
		}
		
		setData.averageNumbers[i] = [];
		
		for (var j = 0; j < thisSum.length; ++j)
		{
			if (counts[j] != 0)
				setData.averageNumbers[i][j] = Math.floor(thisSum[j] / counts[j]);
			else
				setData.averageNumbers[i][j] = 0;
		}
	}
	
}


function ComputeEsoBuildSetDataItem(setData, item)
{
	ParseEsoBuildSetDesc(setData, 0, item.setBonusDesc1);
	ParseEsoBuildSetDesc(setData, 1, item.setBonusDesc2);
	ParseEsoBuildSetDesc(setData, 2, item.setBonusDesc3);
	ParseEsoBuildSetDesc(setData, 3, item.setBonusDesc4);
	ParseEsoBuildSetDesc(setData, 4, item.setBonusDesc5);
}


function ParseEsoBuildSetDesc(setData, descIndex, description)
{
	var rawDesc = RemoveEsoDescriptionFormats(description);
	var results = rawDesc.match(/[0-9]+\.?[0-9]*/g);
	
	if (setData.parsedNumbers[descIndex] == null) setData.parsedNumbers[descIndex] = [];
	
	setData.parsedNumbers[descIndex].push(results);
}


function IsTwiceBornStarEnabled()
{
	if (g_EsoInputStatSources.TwiceBornStar == null) return false;
	if (g_EsoInputStatSources.TwiceBornStar[0] == null) return false;
	if (g_EsoInputStatSources.TwiceBornStar[0].value == 1) return true;
	return false;
}


function UpdateEsoBuildMundusList2()
{
	var isEnabled = IsTwiceBornStarEnabled();
	
	if (isEnabled)
	{
		$("#esotbMundus2").prop("disabled", false);
	}
	else
	{
		$("#esotbMundus2").val("none");
		$("#esotbMundus2").prop("disabled", "disabled");
	}
}


function UpdateEsoBuildSetInfo()
{
	var setInfoElement = $("#esotbSetInfo");
	var output = GetEsoBuildSetInfoHtml(); 
		
	setInfoElement.html(output);
}


function GetEsoBuildSetInfoHtml()
{
	var output = "";
	
	for (var setName in g_EsoBuildSetData)
	{
		var setData = g_EsoBuildSetData[setName];
		
		var wornItems = setData.count;
		if (wornItems <= 0) continue;
		
		output += "<div class='esotbSetInfoSet'>";
		output += "<h4>" + setName + "</h4>";
		
		output += "<div class='esotbSetInfoRow'>Worn Set Items = " + wornItems + "</div>";
		
		for (var name in setData.rawOutput)
		{
			var statDetails = g_EsoInputStatDetails[name] || {};
			var value = setData.rawOutput[name];
			
			if (statDetails.display == '%') value = "" + Math.floor(value * 1000)/10 + "%";
			
			output += "<div class='esotbSetInfoRow'>" + name + " = " + value + "</div>";
		}
		
		output += "</div>";
	}
	
	return output;
}


function CreateEsoBuildToggledSetData()
{
	g_EsoBuildToggledSetData = {};
	
	for (var i = 0; i < ESO_SETEFFECT_MATCHES.length; ++i)
	{
		var setEffectData = ESO_SETEFFECT_MATCHES[i];
		if (setEffectData.toggle !== true) continue;
		
		var id = setEffectData.id;
		
		if (g_EsoBuildToggledSetData[id] == null) 
		{
			g_EsoBuildToggledSetData[id] = {};
			g_EsoBuildToggledSetData[id].statIds = [];
		}
		
		g_EsoBuildToggledSetData[id].id = id;
		g_EsoBuildToggledSetData[id].setBonusCount = setEffectData.setBonusCount;
		g_EsoBuildToggledSetData[id].desc = "";
		g_EsoBuildToggledSetData[id].valid = false;
		g_EsoBuildToggledSetData[id].enabled = setEffectData.enabled;
		g_EsoBuildToggledSetData[id].statIds.push(setEffectData.statId);
		
		if (g_EsoBuildSetData[id] != null && g_EsoBuildSetData[id].averageDesc != null &&
				g_EsoBuildSetData[id].averageDesc[setEffectData.setBonusCount] != null)
		{
			g_EsoBuildToggledSetData[id].desc = g_EsoBuildSetData[id].averageDesc[setEffectData.setBonusCount];
		}
	}
	
}


function IsEsoBuildToggledSetEnabled(setId)
{
	if (g_EsoBuildToggledSetData[setId] == null) return false;
	return g_EsoBuildToggledSetData[setId].enabled;
}


function SetEsoBuildToggledSetValid(setId, valid)
{
	if (g_EsoBuildToggledSetData[setId] != null) g_EsoBuildToggledSetData[setId].valid = valid;
}


function SetEsoBuildToggledSetDesc(setId, desc)
{
	if (g_EsoBuildToggledSetData[setId] != null) g_EsoBuildToggledSetData[setId].desc = desc;
}


function SetEsoBuildToggledSetEnable(setId, enable)
{
	if (g_EsoBuildToggledSetData[setId] == null) return false;
	g_EsoBuildToggledSetData[setId].enabled = enable;
}


function UpdateEsoBuildToggledSetData()
{
	
	for (var setName in g_EsoBuildSetData)
	{
		var setData = g_EsoBuildSetData[setName];
		var toggleData = g_EsoBuildToggledSetData[setName];
		if (toggleData == null) continue;
		
		if (setData.averageDesc == null || setData.items[0] == null)
		{
			SetEsoBuildToggledSetValid(setName, false);
			continue;
		}
		
		var setDesc = setData.averageDesc[toggleData.setBonusCount - 1];
		var setCount = setData.items[0]['setBonusCount' + toggleData.setBonusCount];
		
		if (setDesc == null || setCount == null) 
		{
			SetEsoBuildToggledSetValid(setName, false);
			continue;
		}
		
		toggleData.desc = setDesc;
		
		if (setCount > setData.count) 
		{
			SetEsoBuildToggledSetValid(setName, false);
			continue;
		}
		
		SetEsoBuildToggledSetValid(setName, true);
		
		var checkElement = $(".esotbToggledSetItem[setid=\"" + setName + "\"]").find(".esotbToggleSetCheck");
		
		if (checkElement.length > 0)
		{
			SetEsoBuildToggledSetEnable(setName, checkElement.is(":checked"));
		}
	}
}


function UpdateEsoBuildToggleSets()
{
	var element = $("#esotbToggledSetInfo");
	var output = "";
	
	for (var setId in g_EsoBuildToggledSetData)
	{
		var setData = g_EsoBuildToggledSetData[setId];
		if (!setData.valid) continue;
		output += CreateEsoBuildToggleSetHtml(setData);
	}
	
	element.html(output);
	
	$(".esotbToggleSetCheck").click(OnEsoBuildToggleSet);
}


function OnEsoBuildToggleSet(e)
{
	var setId = $(this).parent().attr("setid");
	if (setId == null || setId == "") return;
	
	UpdateEsoComputedStatsList();
}


function CreateEsoBuildToggleSetHtml(setData)
{
	var output = "<div class='esotbToggledSetItem' setid=\"" + setData.id + "\">";
	var checked = setData.enabled ? "checked" : "";
	
	output += "<input type='checkbox' class='esotbToggleSetCheck'  " + checked + " >";
	output += "<div class='esotbToggleSetTitle'>" + setData.id + ":</div> ";
	output += "<div class='esotbToggleSetDesc'>" + setData.desc + "</div>";
	
	output += "</div>";
	return output;
}


function UpdateEsoBuildItemLinkSetCounts()
{
	UpdateEsoBuildItemLinkSetCount("Head");
	UpdateEsoBuildItemLinkSetCount("Shoulders");
	UpdateEsoBuildItemLinkSetCount("Chest");
	UpdateEsoBuildItemLinkSetCount("Hands");
	UpdateEsoBuildItemLinkSetCount("Waist");
	UpdateEsoBuildItemLinkSetCount("Legs");
	UpdateEsoBuildItemLinkSetCount("Feet");
	UpdateEsoBuildItemLinkSetCount("Neck");
	UpdateEsoBuildItemLinkSetCount("Ring1");
	UpdateEsoBuildItemLinkSetCount("Ring2");
	UpdateEsoBuildItemLinkSetCount("MainHand1");
	UpdateEsoBuildItemLinkSetCount("OffHand1");
	UpdateEsoBuildItemLinkSetCount("MainHand2");
	UpdateEsoBuildItemLinkSetCount("OffHand2");
}


function UpdateEsoBuildItemLinkSetCount(slotId)
{
	var itemElement = $(".esotbItem[slotid='" + slotId + "']");
	var iconElement = itemElement.children(".esotbItemIcon");
	var itemData = g_EsoBuildItemData[slotId];
	
	iconElement.attr("setcount", "0");
	
	if (itemData == null) return;
	if (itemData.setName == null || itemData.setName == "") return;
	
	var setData = g_EsoBuildSetData[itemData.setName];
	if (setData == null) return;
	if (setData.count == null) return;
	
	iconElement.attr("setcount", setData.count);
}


function esotbOnDocReady()
{
	CreateEsoComputedStats();
	UpdateEsoComputedStatsList();
	CreateEsoBuildToggledSetData();
		
	$("#esotbRace").change(OnEsoRaceChange)
	$("#esotbClass").change(OnEsoClassChange)
	$("#esotbMundus").change(OnEsoMundusChange)
	$("#esotbMundus2").change(OnEsoMundusChange)
	$("#esotbCPTotalPoints").change(OnEsoCPTotalPointsChange);
	$(".esotbStatComputeButton").click(OnEsoToggleStatComputeItems);
	
	$(".esotbInputValue").on('input', function(e) { OnEsoInputChange.call(this, e); });
	
	$(".esotbItemIcon").click(OnEsoClickItemIcon)
	
	$(".esotbComputeItems").click(OnEsoClickComputeItems);

	$("#esotbItemDetailsCloseButton").click(CloseEsoItemDetailsPopup);
	$("#esotbFormulaCloseButton").click(CloseEsoFormulaPopup);
	$("#esotbClickWall").click(OnEsoClickBuildWall);
	
	$(".esotbStatTab").click(OnEsoClickBuildStatTab);
	
	$(document).on("EsoItemSearchPopupOnClose", OnEsoItemSearchPopupClose);
	
	$(document).on("esocpUpdate", OnEsoBuildCpUpdate);
	
	$(".esotbItemDetailsButton").click(OnEsoItemDetailsClick);
	$(".esotbItemEnchantButton").click(OnEsoItemEnchantClick);
	$("#esotbWeaponSelect1").click(OnEsoWeaponSelect1);
	$("#esotbWeaponSelect2").click(OnEsoWeaponSelect2);
}


$( document ).ready(esotbOnDocReady);