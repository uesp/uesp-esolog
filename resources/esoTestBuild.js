/*
 * TODO:
 * 		- Description of input types (plain, percent, special, etc...)
 */

ESO_TESTBUILD_SHOWALLRAWINPUTS = false;

ESO_MAX_ATTRIBUTES = 64;
ESO_MAX_LEVEL = 50;
ESO_MAX_CPLEVEL = 16;
ESO_MAX_EFFECTIVELEVEL = 66;

g_EsoBuildLastInputValues = {};

g_EsoBuildEnableUpdates = true;

g_EsoBuildClickWallLinkElement = null;
g_EsoBuildItemData = {};
g_EsoBuildEnchantData = {};
g_EsoBuildSetData = {};
g_EsoBuildToggledSetData = {};
g_EsoBuildToggledSkillData = {};

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


	// Buffs
// Major Resolve and Major Ward
// Minor Savagery


ESO_PASSIVEEFFECT_MATCHES = [
	{
		factorStatId: "ArmorLight",
		statId: "MagickaCost",
		display: "%",
		match: /Reduces the Magicka cost of spells by ([0-9]+\.?[0-9]*)% per piece of Light Armor/i,
	},
	{
		factorStatId: "ArmorLight",
		statId: "MagickaRegen",
		display: "%",
		match: /Increases Magicka Recovery by ([0-9]+\.?[0-9]*)% per piece of Light Armor/i,
	},
	{
		statId: "SpellResist",
		match: /Increases your Spell Resistance for each piece of Light Armor equipped.[\s\S]*?Current Bonus\: ([0-9]+)/i,
	},
	{
		statRequireId: "ArmorLight",
		statRequireValue: 5,
		category: "Skill2",
		statId: "SpellCrit",
		match: /WHEN 5 OR MORE PIECES OF LIGHT ARMOR ARE EQUIPPED[\s\S]*?Increases your Spell Critical rating by ([0-9]+)/i,
	},
	{
		statRequireId: "ArmorLight",
		statRequireValue: 5,
		statId: "SpellPenetration",
		match: /WHEN 5 OR MORE PIECES OF LIGHT ARMOR ARE EQUIPPED[\s\S]*?Increases your Spell Penetration by ([0-9]+)/i,
	},
	{
		category: "Skill2",
		statId: "WeaponCrit",
		match: /Increases your Weapon Critical rating for each piece of Medium Armor equipped.[\s\S]*?Current Bonus\: ([0-9]+)/i,
	},
	{
		factorStatId: "ArmorMedium",
		statId: "StaminaRegen",
		display: '%',
		match: /Increases Stamina Recovery by ([0-9]+\.?[0-9]*)% per piece of Medium Armor equipped/i,
	},
	{
		factorStatId: "ArmorMedium",
		statId: "StaminaCost",
		display: '%',
		match: /Reduces the Stamina cost of abilities by ([0-9]+\.?[0-9]*)% per piece of Medium Armor equipped/i,
	},
	{
		factorStatId: "ArmorMedium",
		statId: "SneakCost",
		display: '%',
		match: /Reduces the cost of sneaking by ([0-9]+\.?[0-9]*)% per piece of Medium Armor equipped/i,
	},
	{
		factorStatId: "ArmorMedium",
		statId: "SneakRange",
		display: '%',
		match: /Reduces the size of your detection area by ([0-9]+\.?[0-9]*)% per piece of Medium Armor equipped/i,
	},
	{
		statRequireId: "ArmorMedium",
		statRequireValue: 5,
		statId: "WeaponDamage",
		display: '%',
		match: /WHEN 5 OR MORE PIECES OF MEDIUM ARMOR ARE EQUIPPED[\s\S]*?Increases your Weapon Damage by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		// factorStatId: "ArmorMedium", // TODO: Check?
		statId: "SprintSpeed",
		display: '%',
		match: /increases your movement speed while using Sprint by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		factorStatId: "ArmorMedium",
		statId: "RollDodgeCost",
		display: '%',
		match: /Reduces the cost of Roll Dodge by ([0-9]+\.?[0-9]*)% per piece of Medium Armor equipped/i,
	},
	{
		statId: "PhysicalResist",
		match: /Increases your Physical Resistance and Spell Resistance for each piece of Heavy Armor equipped.[\s\S]*?Current bonus\: ([0-9]+)/i,
	},
	{
		statId: "SpellResist",
		match: /Increases your Physical Resistance and Spell Resistance for each piece of Heavy Armor equipped.[\s\S]*?Current bonus\: ([0-9]+)/i,
	},
	{
		factorStatId: "ArmorHeavy",
		statId: "HealthRegen",
		display: "%",
		match: /Increases Health Recovery by ([0-9]+\.?[0-9]*)% per piece of Heavy Armor equipped/i,
	},
	{
		statId: "Constitution",
		match: /Also restores Magicka and Stamina each time you are hit[.\s\S]*?Current bonus\: ([0-9]+)/i,
	},
	{
		factorStatId: "ArmorHeavy",
		statId: "Health",
		display: "%",
		match: /Increases Max Health by ([0-9]+\.?[0-9]*)% per piece of Heavy Armor equipped/i,
	},
	{
		statRequireId: "ArmorHeavy",
		statRequireValue: 5,
		statId: "HealingReceived",
		display: "%",
		match: /WITH 5 OR MORE PIECES OF HEAVY ARMOR EQUIPPED[.\s\S]*?Increases your healing received by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "SneakCost",
		display: '%',
		match: /Reduces the Stamina cost of sneaking by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		factorStatId: "ArmorTypes",
		statId: "Health",
		display: '%',
		match: /Increases your Max Health, Stamina, and Magicka by ([0-9]+\.?[0-9]*)% per type of Armor/i,
	},
	{
		factorStatId: "ArmorTypes",
		statId: "Magicka",
		display: '%',
		match: /Increases your Max Health, Stamina, and Magicka by ([0-9]+\.?[0-9]*)% per type of Armor/i,
	},
	{
		factorStatId: "ArmorTypes",
		statId: "Stamina",
		display: '%',
		match: /Increases your Max Health, Stamina, and Magicka by ([0-9]+\.?[0-9]*)% per type of Armor/i,
	},
	{
		statId: "Health",
		display: '%',
		match: /^Increases your Max Health by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "PoisonResist",
		match: /Poison and Disease Resistance by ([0-9]+)/i,
	},
	{
		statId: "DiseaseResist",
		match: /Poison and Disease Resistance by ([0-9]+)/i,
	},
	{
		statId: "HealingReceived",
		display: "%",
		match: /Increases the effectiveness of healing on you by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "Health",
		display: "%",
		match: /Increases Max Health by ([0-9]+\.?[0-9]*)%\./i,
	},
	{
		statId: "Health",
		display: "%",
		match: /Increases Max Health by ([0-9]+\.?[0-9]*)% and /i,
	},
	{
		statId: "Magicka",
		display: "%",
		match: /Increases your Max Magicka by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "Health",
		display: "%",
		match: /Increases Max Health and Max Stamina by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "Stamina",
		display: "%",
		match: /Increases Max Health and Max Stamina by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "Stamina",
		display: "%",
		match: /Increases your Max Stamina by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "SpellResist",
		match: /Increases your Spell Resistance by ([0-9]+)/i,
	},
	{
		statId: "MagickaCost",
		display: "%",
		match: /Reduces the Magicka cost of spells by ([0-9]+\.?[0-9]*)%\./i,
	},
	{
		statId: "Magicka",
		display: "%",
		match: /Increases Max Magicka and Max Stamina by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "Stamina",
		display: "%",
		match: /Increases Max Magicka and Max Stamina by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "FireResist",
		display: "%",
		match: /Increases Flame Resistance by ([0-9]+)/i,
	},
	{
		statId: "Magicka",
		display: "%",
		match: /Increases Flame Resistance by [0-9]+ and increases Max Magicka by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "MagickaRegen",
		display: "%",
		match: /Increases Magicka Recovery by ([0-9]+\.?[0-9]*)%\./i,
	},
	{
		statId: "StaminaRegen",
		display: "%",
		match: /Increases Stamina Recovery by ([0-9]+\.?[0-9]*)%\./i,
	},
	{
		statId: "HealthRegen",
		display: "%",
		match: /Increases Health Recovery by ([0-9]+\.?[0-9]*)%\./i,
	},
	{
		statId: "HealthRegen",
		display: "%",
		match: /Increases Health Recovery by ([0-9]+\.?[0-9]*)%\ and /i,
	},
	{
		statId: "StaminaRegen",
		display: "%",
		match: /Increases Health Recovery by [0-9]+\.?[0-9]*% and Stamina Recovery by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		category: "Skill2",
		statId: "SneakRange",
		match: /Reduces your detection radius in stealth by ([0-9]+\.?[0-9]*) meters/i,
	},
	{
		statId: "WeaponCrit",
		display: "%",
		match: /Increases your Weapon Critical rating by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "ColdResist",
		display: "%",
		match: /Increases Cold Resistance by ([0-9])+/i,
	},
	{
		statId: "Health",
		display: "%",
		match: /Increases Cold Resistance by [0-9]+ and increases Max Health by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "MagickaDamageResist",
		display: "%",
		match: /Reduces incoming damage by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "PhysicalDamageResist",
		display: "%",
		match: /Reduces incoming damage by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "SprintCost",
		display: "%",
		match: /Reduces Sprint cost by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "SprintSpeed",
		display: "%",
		match: /Increases sprint speed by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statRequireId: "Weapon1HShield",
		statRequireValue: 1,
		statId: "BlockCost",
		display: "%",
		match: /WITH ONE HAND WEAPON AND SHIELD EQUIPPED[.\s\S]*?Reduces the cost of blocking by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statRequireId: "Weapon1HShield",
		statRequireValue: 1,
		statId: "BlockCost",
		display: "%",
		match: /WITH ONE HAND WEAPON AND SHIELD EQUIPPED[.\s\S]*?Reduces the cost of blocking by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statRequireId: "Weapon1HShield",
		statRequireValue: 1,
		statId: "WeaponDamage",
		display: "%",
		match: /WITH ONE HAND WEAPON AND SHIELD EQUIPPED[.\s\S]*?Increases your Weapon Damage by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statRequireId: "Weapon1HShield",
		statRequireValue: 1,
		statId: "BlockMitigation",
		display: "%",
		match: /WITH ONE HAND WEAPON AND SHIELD EQUIPPED[.\s\S]*?amount of damage you can block by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statRequireId: "Weapon1HShield",
		statRequireValue: 1,
		statId: "BashDamage",
		display: "%",
		match: /WITH ONE HAND WEAPON AND SHIELD EQUIPPED[.\s\S]*?Bashing deals ([0-9]+\.?[0-9]*)% additional damage/i,
	},
	{
		statRequireId: "Weapon1HShield",
		statRequireValue: 1,
		statId: "BashCost",
		display: "%",
		match: /WITH ONE HAND WEAPON AND SHIELD EQUIPPED[.\s\S]*?Bashing deals [0-9]+\.?[0-9]*% additional damage and costs ([0-9]+\.?[0-9]*)% less Stamina/i,
	},
	{
		statRequireId: "Weapon1H",
		statRequireValue: 2,
		factorStatId: "WeaponMace",
		category: "Skill2",
		statId: "PhysicalPenetration",
		display: "%",
		match: /Each mace causes your attacks to ignore ([0-9]+\.?[0-9]*)% of an enemy's Physical Resistance/i,
	},
	{
		statRequireId: "Weapon1H",
		statRequireValue: 2,
		factorStatId: "WeaponSword",
		statId: "MagickaDamage",
		display: "%",
		match: /Each sword increases your damage done by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statRequireId: "Weapon1H",
		statRequireValue: 2,
		factorStatId: "WeaponSword",
		statId: "PhysicalDamage",
		display: "%",
		match: /Each sword increases your damage done by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statRequireId: "Weapon1H",
		statRequireValue: 2,
		factorStatId: "WeaponDagger",
		category: "Skill2",
		statId: "WeaponCrit",
		match: /Each dagger increases your Weapon Critical rating[\s\S]*?Current bonus\: ([0-9]+)/i,
	},
	{
		statRequireId: "WeaponBow",
		statRequireValue: 1,
		category: "Skill2",
		statId: "WeaponCrit",
		match: /WITH BOW EQUIPPED[.\s\S]*?Increases Weapon Critical rating by ([0-9]+)/i,
	},
	{
		statRequireId: "WeaponDestStaff",
		statRequireValue: 1,
		category: "Skill2",
		statId: "SpellPenetration",
		display: "%",
		match: /WITH DESTRUCTION STAFF EQUIPPED[\s\S]*?Allows your Destruction Staff spells to ignore ([0-9]+)% of an enemy's Spell Resistance/i,
	},
	{
		statRequireId: "WeaponDestStaff",
		statRequireValue: 1,
		statId: "HAChargeTime",
		display: "%",
		match: /WITH DESTRUCTION STAFF EQUIPPED[.\s\S]*?Reduces the time it takes to charge a heavy attack by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statRequireId: "WeaponDestStaff",
		statRequireValue: 1,
		statId: "OtherEffects",
		rawInputMatch: /WITH DESTRUCTION STAFF EQUIPPED[\s]*(.*)/i,
		match: /WITH DESTRUCTION STAFF EQUIPPED[.\s\S]*?Restores ([0-9]+) Magicka when you kill a target with a Destruction Staff spell or weapon attack/i,
	},
	{
		statId: "WeaponDamage",
		display: "%",
		match: /Increases your Weapon Damage by ([0-9]+\.?[0-9]*)% and /i,
	},
	{
		statId: "WeaponDamage",
		match: /and your Spell Resistance by ([0-9]+)/i,
	},
	{
		statId: "MagickaCost",
		display: "%",
		match: /Reduces Magicka, Stamina, and Ultimate ability costs by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "StaminaCost",
		display: "%",
		match: /Reduces Magicka, Stamina, and Ultimate ability costs by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "StaminaCost",
		display: "%",
		match: /Reduces Magicka and Stamina costs for all abilities by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "MagickaCost",
		display: "%",
		match: /Reduces Magicka and Stamina costs for all abilities by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "UltimateCost",
		display: "%",
		match: /Reduces Magicka, Stamina, and Ultimate ability costs by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "UltimateCost",
		display: "%",
		match: /Reduces the cost of Ultimate abilities by ([0-9]+\.?[0-9]*)%/i,
	},	
	{
		statId: "ResurrectSpeed",
		display: "%",
		match: /Increases Resurrection speed by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "HealthRegen",
		display: "%",
		match: /Increases Stamina, Health and Magicka Recovery by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "MagickaRegen",
		display: "%",
		match: /Increases Stamina, Health and Magicka Recovery by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "StaminaRegen",
		display: "%",
		match: /Increases Stamina, Health and Magicka Recovery by ([0-9]+\.?[0-9]*)%/i,
	},
	{
		statId: "BlockMitigation",
		display: "%",
		match: /Block an additional ([0-9]+\.?[0-9]*)% damage/i,
	},
	{
		statId: "SpellResist",
		match: /Increases Spell Resistance by ([0-9]+)/i,
	},
	{
		statRequireId: "ArmorHeavy",
		statRequireValue: 5,
		statId: "Constitution",
		display: "%",
		match: /WITH 5 OR MORE PIECES OF HEAVY ARMOR EQUIPPED[.\s\S]*?increases the Magicka or Stamina your Heavy Attacks restore by ([0-9]+\.?[0-9]*)%/i
	},
	{
		factorSkillLine: "Draconic Power",
		statId: "HealthRegen",
		display: "%",
		match: /WITH DRACONIC POWER ABILITIES SLOTTED[.\s\S]*?Increases Health Recovery by ([0-9]+\.?[0-9]*)% for each Draconic Power ability slotted/i
	},
	{
		factorSkillLine: "Assassination",
		statId: "CritDamage",
		display: "%",
		match: /WITH AN ASSASSINATION ABILITY SLOTTED[.\s\S]*?Increases damage dealt by Critical Strikes by ([0-9]+\.?[0-9]*)%/i
	},
	{
		factorSkillLine: "Assassination",
		category: "Skill2",
		statId: "WeaponCrit",
		statValue: 219,
		skillName: "Pressure Points",
		skillRank: 1,
		//match: /WITH AN ASSASSINATION ABILITY SLOTTED[.\s\S]*?Increases Critical Strike and Spell Critical ratings for each Assassination ability slotted/i
	},
	{
		factorSkillLine: "Assassination",
		category: "Skill2",
		statId: "SpellCrit",
		statValue: 219,
		skillName: "Pressure Points",
		skillRank: 1,
		//match: /WITH AN ASSASSINATION ABILITY SLOTTED[.\s\S]*?Increases Critical Strike and Spell Critical ratings for each Assassination ability slotted/i
	},
	{
		factorSkillLine: "Assassination",
		category: "Skill2",
		statId: "WeaponCrit",
		statValue: 438,
		skillName: "Pressure Points",
		skillRank: 2,
		//match: /WITH AN ASSASSINATION ABILITY SLOTTED[.\s\S]*?Increases Critical Strike and Spell Critical ratings for each Assassination ability slotted/i
	},
	{
		factorSkillLine: "Assassination",
		category: "Skill2",
		statId: "SpellCrit",
		statValue: 438,
		skillName: "Pressure Points",
		skillRank: 2,
		//match: /WITH AN ASSASSINATION ABILITY SLOTTED[.\s\S]*?Increases Critical Strike and Spell Critical ratings for each Assassination ability slotted/i
	},
	{
		factorSkillLine: "Shadow",
		statId: "Health",
		display: "%",
		match: /WITH A SHADOW ABILITY SLOTTED[.\s\S]*?Each Shadow Ability slotted increases your Max Health by ([0-9]+\.?[0-9]*)%/i
	},
	{
		requireSkillLine: "SIPHONING",
		statId: "Magicka",
		display: "%",
		match: /WITH SIPHONING ABILITY SLOTTED[.\s\S]*?Increases Max Magicka ([0-9]+\.?[0-9]*)% while a Siphoning ability is slotted/i
	},
	{
		requireSkillLine: "SIPHONING",
		statId: "HealingDone",
		display: "%",
		match: /WHILE USING SIPHONING ABILITIES[.\s\S]*?Increases the effectiveness of your Healing done by ([0-9]+\.?[0-9]*)% for each Siphoning ability slotted/i
	},
	{
		requireSkillLine: "DAEDRIC SUMMONING",
		statId: "HealthRegen",
		display: "%",
		match: /WHILE A DAEDRIC SUMMONING ABILITY IS SLOTTED[.\s\S]*?Increases your Health and Stamina Recovery by ([0-9]+\.?[0-9]*)%/i
	},
	{
		requireSkillLine: "DAEDRIC SUMMONING",
		statId: "StaminaRegen",
		display: "%",
		match: /WHILE A DAEDRIC SUMMONING ABILITY IS SLOTTED[.\s\S]*?Increases your Health and Stamina Recovery by ([0-9]+\.?[0-9]*)%/i
	},
	{
		factorSkillType: "Sorcerer",
		statId: "SpellDamage",
		display: "%",
		match: /Increases Spell Damage and Weapon Damage by ([0-9]+\.?[0-9]*)% for each Sorcerer ability slotted/i
	},
	{
		factorSkillType: "Sorcerer",
		statId: "WeaponDamage",
		display: "%",
		match: /Increases Spell Damage and Weapon Damage by ([0-9]+\.?[0-9]*)% for each Sorcerer ability slotted/i
	},
	{
		requireSkillLine: "AEDRIC SPEAR",
		statId: "CritDamage",
		display: "%",
		match: /WHILE AN  ABILITY IS SLOTTED[.\s\S]*?Increases the damage bonus for your critical strikes by ([0-9]+\.?[0-9]*)%/i
	},
	{
		requireSkillLine: "AEDRIC SPEAR",
		statId: "OtherEffects",
		display: "%",
		match: /WHILE AN  ABILITY IS SLOTTED[.\s\S]*?your damage against blocking targets by ([0-9]+\.?[0-9]*)%/i
	},
	{
		requireSkillLine: "AEDRIC SPEAR",
		statId: "BlockMitigation",
		display: "%",
		match: /WHILE AN  ABILITY IS SLOTTED[.\s\S]*?Increases the amount of damage you can block against melee attacks by ([0-9]+\.?[0-9]*)%/i
	},
	{
		requireSkillLine: "Fighters Guild",
		statId: "WeaponDamage",
		display: "%",
		match: /Increases Weapon Damage by ([0-9]+\.?[0-9]*)% for each Fighters Guild ability slotted/i
	},
	{
		requireSkillLine: "Mages Guild",
		statId: "Magicka",
		display: "%",
		match: /WITH A MAGES GUILD ABILITY SLOTTED[.\s\S]*?Increases your Max Magicka and your Magicka Recovery by ([0-9]+\.?[0-9]*)%/i
	},
	{
		requireSkillLine: "Mages Guild",
		statId: "MagickaRegen",
		display: "%",
		match: /WITH A MAGES GUILD ABILITY SLOTTED[.\s\S]*?Increases your Max Magicka and your Magicka Recovery by ([0-9]+\.?[0-9]*)%/i
	},
	{
		requireSkillLine: "Support",
		statId: "MagickaRegen",
		display: "%",
		match: /Increases Magicka Recovery by ([0-9]+\.?[0-9]*)% for each Support ability slotted/i
	},
	{
		statId: "HealthRegen",
		display: "%",
		match: /WHILE EMPEROR[.\s\S]*?Increases Health, Magicka, and Stamina recovery in combat by ([0-9]+\.?[0-9]*)% while in your campaign/i
	},
	{
		statId: "MagickaRegen",
		display: "%",
		match: /WHILE EMPEROR[.\s\S]*?Increases Health, Magicka, and Stamina recovery in combat by ([0-9]+\.?[0-9]*)% while in your campaign/i
	},
	{
		statId: "StaminaRegen",
		display: "%",
		match: /WHILE EMPEROR[.\s\S]*?Increases Health, Magicka, and Stamina recovery in combat by ([0-9]+\.?[0-9]*)% while in your campaign/i
	},
	{
		statId: "OtherEffect",
		display: "%",
		match: /WHILE EMPEROR[.\s\S]*?Increases Ultimate gains by ([0-9]+\.?[0-9]*)% while in your campaign/i
	},
	{
		statId: "HealingTaken",
		display: "%",
		match: /WHILE EMPEROR[.\s\S]*?Increases the magnitude of healing effects on Emperors by ([0-9]+\.?[0-9]*)% while in your campaign/i
	},
	{
		statId: "Health",
		display: "%",
		match: /WHILE EMPEROR[.\s\S]*?Increases Health, Magicka, and Stamina by ([0-9]+\.?[0-9]*)% while in your campaign/i
	},
	{
		statId: "Magicka",
		display: "%",
		match: /WHILE EMPEROR[.\s\S]*?Increases Health, Magicka, and Stamina by ([0-9]+\.?[0-9]*)% while in your campaign/i
	},
	{
		statId: "Stamina",
		display: "%",
		match: /WHILE EMPEROR[.\s\S]*?Increases Health, Magicka, and Stamina by ([0-9]+\.?[0-9]*)% while in your campaign/i
	},
	{
		statRequireId: "VampireStage",
		statRequireValue: 2,
		statId: "StaminaRegen",
		display: "%",
		match: /WHILE YOU HAVE VAMPIRISM STAGE 2 OR HIGHER[.\s\S]*?Increases your Magicka and Stamina Recovery by ([0-9]+\.?[0-9]*)%/i
	},
	{
		statRequireId: "VampireStage",
		statRequireValue: 2,
		statId: "MagickaRegen",
		display: "%",
		match: /WHILE YOU HAVE VAMPIRISM STAGE 2 OR HIGHER[.\s\S]*?Increases your Magicka and Stamina Recovery by ([0-9]+\.?[0-9]*)%/i
	},
	{
		statRequireId: "VampireStage",
		statRequireValue: 2,
		statId: "HealthRegen",
		display: "%",
		statValue: "25",
		match: /Reduces the severity of the Health Recovery determent in Vampirism stages 2 through 4/i
	},
	{
		statRequireId: "Werewolf",
		statRequireValue: 1,
		statId: "OtherEffects",
		display: "%",
		match: /WHILE IN WEREWOLF FORM[.\s\S]*?Increases the amount of Stamina your heavy attacks restore by ([0-9]+\.?[0-9]*)%/i
	},
	{
		statRequireId: "Werewolf",
		statRequireValue: 1,
		statId: "WeaponDamage",
		display: "%",
		match: /WHILE IN WEREWOLF FORM[.\s\S]*?Increases Weapon Damage by ([0-9]+\.?[0-9]*)%/i
	},
	
		/* Begin Toggled Passives */
	{
		id: "Wrath",
		baseSkillId: 29773,
		statRequireId: "ArmorHeavy",
		statRequireValue: 5,
		category: "Item",
		statId: "WeaponDamage",
		toggle: true,
		enable: false,
		maxTimes: 10,
		match: /WHEN 5 OR MORE PIECES OF HEAVY ARMOR ARE EQUIPPED[.\s\S]*?Gain ([0-9]+) Weapon and Spell Damage for [0-9]+ seconds when you take damage, stacking up to 10 times/i
	},
	{
		id: "Wrath",
		baseSkillId: 29773,
		statRequireId: "ArmorHeavy",
		statRequireValue: 5,
		category: "Item",
		statId: "SpellDamage",
		toggle: true,
		enable: false,
		maxTimes: 10,
		match: /WHEN 5 OR MORE PIECES OF HEAVY ARMOR ARE EQUIPPED[.\s\S]*?Gain ([0-9]+) Weapon and Spell Damage for [0-9]+ seconds when you take damage, stacking up to 10 times/i
	},
	{
		id: "Burning Heart",
		baseSkillId: 29457,
		statId: "HealingReceived",
		toggle: true,
		enable: false,
		display: "%",
		match: /WHILE USING DRACONIC POWER ABILITIES[.\s\S]*?Increases healing received by ([0-9]+\.?[0-9]*)% while a Draconic Power ability is active/i
	},
	{
		id: "Master Assassin",
		baseSkillId: 36616,
		statId: "WeaponDamage",
		toggle: true,
		enable: false,
		display: "%",
		match: /Increases Weapon and Spell Damage while invisible or stealthed by ([0-9]+\.?[0-9]*)%/i
	},
	{
		id: "Master Assassin",
		baseSkillId: 36616,
		statId: "SpellDamage",
		toggle: true,
		enable: false,
		display: "%",
		match: /Increases Weapon and Spell Damage while invisible or stealthed by ([0-9]+\.?[0-9]*)%/i
	},
	{
		id: "Master Assassin",
		baseSkillId: 36616,
		statId: "OtherEffects",
		toggle: true,
		enable: false,
		display: "%",
		match: /while invisible or stealthed[.\s\S]*?The stun from the Crouch ability stuns for ([0-9]+\.?[0-9]*)% longer/i
	},
	{
		id: "Expert Summoner",
		baseSkillId: 31412,
		statId: "Health",
		toggle: true,
		enable: false,
		display: "%",
		match: /Increases your Max Health by ([0-9]+\.?[0-9]*)% if you have a Daedric Summoning pet active/i
	},
		/* End Toggled Passives */
	

		/* Begin Other Effects */
		/* End Other Effects */
	
	
	
		// Dragonknight
	//Increases the damage of Fiery Breath, Searing Strike, and Dragonknight Standard abilities by 3% and the duration by 2 seconds.
	//Increases the damage of Flame and Poison area of effect abilities by 6%.
	
		// Nightblade
	//
	
		// Sorcerer
	//
	//Increases your Physical and Shock Damage by 5%.		
	
		// Templar
	//Gives you a 25% chance to cause an extra 1803 Damage any time you hit with an Aedric Spear ability. Deals Physical Damage and scales with Weapon Damage, or deals Magic Damage and scales with Spell Damage, based on whichever is higher.
	
		// Restoration Staff
	//WITH RESTORATION STAFF EQUIPPED Increases your healing by 15% on allies under 30% Health.
	//WITH RESTORATION STAFF EQUIPPED Restores an additional 30% Magicka when you complete a heavy attack.
	//WITH RESTORATION STAFF EQUIPPED Restores 540 Magicka when you block a spell.
	//WITH RESTORATION STAFF EQUIPPED Increases healing with Restoration Staff spells by 5%.
	
		// Destruction Staff
	//Grants bonus effects based on the element used: 
		//Fully charged heavy fire attacks deal 12% additional damage.
		//Fully charged heavy frost attacks grant a damage shield that absorbs 1809 damage.
		//Fully charged heavy shock attacks damage nearby enemies for 100% of the damage done.
	//WITH DESTRUCTION STAFF EQUIPPED Reduces the time it takes to charge a heavy attack by 10%.
	
		// Bow
	//WITH BOW ABILITIES Gives you a damage bonus of up to 12% against enemies at longer range.
	//WITH BOW EQUIPPED Reduces the Stamina cost of Bow abilities by 20%.
	//WITH BOW EQUIPPED Your successful Light and Heavy Attacks increase the damage of your Bow abilities by 5% for 4 seconds, stacking up to 3 times.
	
		// Dual Wield
	//WHILE DUAL WIELDING Increases damage with Dual Wield abilities by 20% against enemies with under 25% Health.
	//WHILE DUAL WIELDING Increases Weapon Damage by 6% of off-hand weapon's damage.
	//Grants a bonus based on the type of weapon equipped: 
		//Each axe gives your melee attacks a 8% chance to bleed enemies for 5635 Physical Damage over 6 seconds. 
	
		// One Hand and Shield
	//WITH ONE HAND WEAPON AND SHIELD EQUIPPED Increases the amount of damage you can block from projectiles and ranged attacks by 15%.
	//WITH ONE HAND WEAPON AND SHIELD EQUIPPED Increases your Movement Speed while blocking by 60%
	
		// Two Handed
	//Grants a bonus based on the type of weapon equipped: 
		//Swords increase your damage done by 5%.
		//Axes grant your melee attacks 16% chance to apply a bleed dealing 5635 Physical Damage over 6 seconds.
		//Maces cause your attacks to ignore 20% of your target's Physical Resistance.
	
		// Racial
	//Increases your Damage with Flame effects by 7%
	//Increases your Damage with Frost, Fire, and Shock effects by 4%
	//Gives your melee attacks a 10% chance to restore 854 Health.
	//Increases damage done while in stealth by 10%.
	//Increases your damage with melee weapon attacks by 4%.
	//Restores 361 Stamina whenever you damage an enemy with a melee attack. This can happen no more than once every 3 seconds.
	
	

];


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
		statId: "SprintSpeed",
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
		statId: "ResurrectSpeed",
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
	console.log("GetEsoInputValues");
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
	
	GetEsoInputSpecialValues(inputValues);
	
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
		if ( ( (g_EsoBuildItemData.MainHand1.weaponType >= 1 && g_EsoBuildItemData.MainHand1.weaponType <= 3) ||
				g_EsoBuildItemData.MainHand1.weaponType == 1) &&				
				g_EsoBuildItemData.OffHand1.weaponType == 14) inputValues.Weapon1HShield = 1;
		GetEsoInputItemValues(inputValues, "MainHand1");
		GetEsoInputItemValues(inputValues, "OffHand1");
		GetEsoInputItemValues(inputValues, "Poison1");
	}
	else
	{
		if ( ( (g_EsoBuildItemData.MainHand2.weaponType >= 1 && g_EsoBuildItemData.MainHand2.weaponType <= 3) ||
				g_EsoBuildItemData.MainHand2.weaponType == 1) &&				
				g_EsoBuildItemData.OffHand2.weaponType == 14) inputValues.Weapon1HShield = 1;
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
	
	UpdateEsoBuildToggledSkillData(inputValues);
	GetEsoInputSkillPassives(inputValues);
	//GetEsoInputSkillActiveBar(inputValues);
	
	GetEsoInputMiscValues(inputValues);
	
	if (mergeComputedStats === true) 
	{
		for (var name in g_EsoComputedStats)
		{
			inputValues[name] = g_EsoComputedStats[name].value;
		}
	}
	
	g_EsoBuildLastInputValues = inputValues;
	return inputValues;
}


function GetEsoInputSpecialValues(inputValues)
{
	inputValues.VampireStage = parseInt($("#esotbVampireStage").val());
	
	if (inputValues.VampireStage > 0)
	{
		var healthRegenValue = 0;
		var flameDamageValue = 0;
		var costReduction = 0;
		
		if (inputValues.VampireStage == 1)
		{
			healthRegenValue = 0;
			flameDamageValue = 0;
			costReduction = 0;
		}
		else if (inputValues.VampireStage == 2)
		{
			healthRegenValue = -0.25;
			flameDamageValue = -0.15;
			costReduction = 0.07;
		}
		else if (inputValues.VampireStage == 3)
		{
			healthRegenValue = -0.50;
			flameDamageValue = -0.20;
			costReduction = 0.14;
		}
		else if (inputValues.VampireStage == 4)
		{
			healthRegenValue = -0.75;
			flameDamageValue = -0.25;
			costReduction = 0.21;
		}
		
		if (healthRegenValue != 0)
		{
			inputValues.Skill.HealthRegen += healthRegenValue;
			//AddEsoItemRawOutput(itemData, "Skill.HealthRegen", healthRegenValue);
			AddEsoInputStatSource("Skill.HealthRegen", { source: "Vampire Stage " + inputValues.VampireStage , value: healthRegenValue });
		}
		
		if (flameDamageValue != 0)
		{
			inputValues.Skill.FireDamageResist += flameDamageValue;
			//AddEsoItemRawOutput(itemData, "Skill.FireDamageResist", flameDamageValue);
			AddEsoInputStatSource("Skill.FireDamageResist", { source: "Vampire Stage " + inputValues.VampireStage , value: flameDamageValue });
		}
		
		if (costReduction != 0)
		{
			//AddEsoItemRawOutput(itemData, "OtherEffects", costReduction);
			AddEsoInputStatSource("OtherEffects", { other: true, source: "Vampire Stage " + inputValues.VampireStage , value: "Reduce cost of Vampire abilities by " + (costReduction*100) + "%" });
		}
	}
	
	if ($("#esotbWerewolf").is(":checked"))	inputValues.Werewolf = parseInt($("#esotbWerewolf").val());
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


function GetEsoInputSkillPassives(inputValues)
{
	UpdateEsoTestBuildSkillInputValues(inputValues);
	var skillInputValues = GetEsoTestBuildSkillInputValues();
	
	for (var skillId in g_EsoSkillPassiveData)
	{
		GetEsoInputSkillPassiveValues(inputValues, skillInputValues, g_EsoSkillPassiveData[skillId]);	
	}
	
}


function GetEsoInputSkillPassiveValues(inputValues, skillInputValues, skillData)
{
	var abilityData = g_SkillsData[skillData.abilityId];
	var skillDesc = GetEsoSkillDescription(skillData.abilityId, skillInputValues, false, true);
	var rawDesc = RemoveEsoDescriptionFormats(skillDesc);
	if (rawDesc == "" || abilityData == null) return;
	
	for (var i = 0; i < ESO_PASSIVEEFFECT_MATCHES.length; ++i)
	{
		var matchData = ESO_PASSIVEEFFECT_MATCHES[i];
		var statValue = 0;
		var statFactor = 1;
		var matches = null;
		
		if (matchData.statValue != null) statValue = matchData.statValue;
		
		if (matchData.match != null) 
		{
			matches = rawDesc.match(matchData.match);
			if (matches == null) continue;
			if (matches[1] != null ) statValue = parseFloat(matches[1]);
		}
		
		if (matchData.skillName != null)
		{
			if (abilityData.name.toUpperCase() != matchData.skillName.toUpperCase()) continue;
		}
		
		if (matchData.skillRank != null)
		{
			if (abilityData.rank != matchData.skillRank) continue;
		}
				
		if (matchData.toggle === true && matchData.id != null)
		{
			var toggleData = g_EsoBuildToggledSkillData[matchData.id];
			if (toggleData == null) continue;
			if (!toggleData.enabled) continue;
		}
		
		if (matchData.requireSkillLine != null)
		{
			var count = CountEsoBarSkillsWithSkillLine(matchData.requireSkillLine);
			if (count == 0) continue;
		}
		
		if (matchData.requireSkillType != null)
		{
			var count = CountEsoBarSkillsWithSkillType(matchData.requireSkillType);
			if (count == 0) continue;
		}
		
		if (matchData.statRequireId != null)
		{
			var requiredStat = inputValues[matchData.statRequireId];
			if (requiredStat == null) continue;
			if (parseFloat(requiredStat) < parseFloat(matchData.statRequireValue)) continue;
		}
		
		if (matchData.factorSkillLine != null)
		{
			var count = CountEsoBarSkillsWithSkillLine(matchData.factorSkillLine);
			statFactor = count;
		}
		else if (matchData.factorSkillType != null)
		{
			var count = CountEsoBarSkillsWithSkillType(matchData.factorSkillType);
			statFactor = count;
		}
		else if (matchData.factorStatId != null)
		{
			var factorStat = inputValues[matchData.factorStatId];
			if (factorStat != null) statFactor = parseFloat(factorStat);
		}
		else if (matchData.maxTimes != null)
		{
			var toggleData = g_EsoBuildToggledSkillData[matchData.id];
			if (toggleData != null && toggleData.count != null) statFactor = toggleData.count;
		}
		
		statValue = statValue * statFactor;
		
		if (matchData.display == '%') statValue = statValue / 100;
		if (matchData.round == 'floor') statValue = Math.floor(statValue);
		
		var category = "Skill";
		if (matchData.category != null) category = matchData.category;
		
		if (matchData.statId == "OtherEffects")
		{
			var rawInputDesc = rawDesc;
			
			if (matchData.rawInputMatch != null)
			{
				var rawInputMatches = rawDesc.match(matchData.rawInputMatch);
				if (rawInputMatches != null) rawInputDesc = rawInputMatches[1];
				if (rawInputDesc == null) rawInputDesc = rawDesc;
			}
			
			//AddEsoItemRawOutput(abilityData, "PassiveEffect", rawInputDesc);
			AddEsoInputStatSource("OtherEffects", { other: true, passive: abilityData, value: rawInputDesc });
		}
		else 
		{
			inputValues[category][matchData.statId] += statValue;
			//AddEsoItemRawOutput(abilityData, category + "." + matchData.statId, statValue);
			AddEsoInputStatSource(category + "." + matchData.statId, { passive: abilityData, value: statValue });
		}
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
	
	var traitMatch = null;
	var traitValue = 0;
	if (itemData.traitDesc != null) traitMatch = itemData.traitDesc.match(/[0-9]+.?[0-9]*/);
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

	switch (parseInt(itemData.weaponType))
	{
	case 1:
		++inputValues.WeaponAxe;
		AddEsoItemRawOutput(itemData, "WeaponAxe", 1);
		AddEsoInputStatSource("WeaponAxe", { item: itemData, value: 1, slotId: slotId });
		
		++inputValues.Weapon1H;
		AddEsoItemRawOutput(itemData, "Weapon1H", 1);
		AddEsoInputStatSource("Weapon1H", { item: itemData, value: 1, slotId: slotId });
		break;
	case 2:
		++inputValues.WeaponMace;
		AddEsoItemRawOutput(itemData, "WeaponMace", 1);
		AddEsoInputStatSource("WeaponMace", { item: itemData, value: 1, slotId: slotId });
		
		++inputValues.Weapon1H;
		AddEsoItemRawOutput(itemData, "Weapon1H", 1);
		AddEsoInputStatSource("Weapon1H", { item: itemData, value: 1, slotId: slotId });
		break;
	case 3:
		++inputValues.WeaponSword;
		AddEsoItemRawOutput(itemData, "WeaponSword", 1);
		AddEsoInputStatSource("WeaponSword", { item: itemData, value: 1, slotId: slotId });
		
		++inputValues.Weapon1H;
		AddEsoItemRawOutput(itemData, "Weapon1H", 1);
		AddEsoInputStatSource("Weapon1H", { item: itemData, value: 1, slotId: slotId });
		break;
	case 4:
		++inputValues.WeaponSword;
		AddEsoItemRawOutput(itemData, "WeaponSword", 1);
		AddEsoInputStatSource("WeaponSword", { item: itemData, value: 1, slotId: slotId });
		
		++inputValues.Weapon2H;
		AddEsoItemRawOutput(itemData, "Weapon2H", 1);
		AddEsoInputStatSource("Weapon2H", { item: itemData, value: 1, slotId: slotId });
		break;
	case 5:
		++inputValues.WeaponAxe;
		AddEsoItemRawOutput(itemData, "WeaponAxe", 1);
		AddEsoInputStatSource("WeaponAxe", { item: itemData, value: 1, slotId: slotId });
		
		++inputValues.Weapon2H;
		AddEsoItemRawOutput(itemData, "Weapon2H", 1);
		AddEsoInputStatSource("Weapon2H", { item: itemData, value: 1, slotId: slotId });
		break;
	case 6:
		
		++inputValues.WeaponMace;
		AddEsoItemRawOutput(itemData, "WeaponMace", 1);
		AddEsoInputStatSource("WeaponMace", { item: itemData, value: 1, slotId: slotId });
		
		++inputValues.Weapon2H;
		AddEsoItemRawOutput(itemData, "Weapon2H", 1);
		AddEsoInputStatSource("Weapon2H", { item: itemData, value: 1, slotId: slotId });
		break;
	case 8:
		++inputValues.WeaponBow;
		AddEsoItemRawOutput(itemData, "WeaponBow", 1);
		AddEsoInputStatSource("WeaponBow", { item: itemData, value: 1, slotId: slotId });
		break;
	case 9:
		++inputValues.WeaponRestStaff;
		AddEsoItemRawOutput(itemData, "WeaponRestStaff", 1);
		AddEsoInputStatSource("WeaponRestStaff", { item: itemData, value: 1, slotId: slotId });
		break;
	case 11:
		++inputValues.WeaponDagger;
		AddEsoItemRawOutput(itemData, "WeaponDagger", 1);
		AddEsoInputStatSource("WeaponDagger", { item: itemData, value: 1, slotId: slotId });
		break;
	case 12:
	case 13:
	case 15:
		++inputValues.WeaponDestStaff;
		AddEsoItemRawOutput(itemData, "WeaponDestStaff", 1);
		AddEsoInputStatSource("WeaponDestStaff", { item: itemData, value: 1, slotId: slotId });
		break;
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
		inputValues.Item.Defending += traitValue;
		AddEsoItemRawOutput(itemData, "Item.Defending", traitValue);
		AddEsoInputStatSource("Item.Defending", { item: itemData, value: traitValue, slotId:slotId });
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
		inputValues.Mundus.SprintSpeed = 0.05;
		AddEsoInputStatSource("Mundus.HealthRegen", { mundus: mundusName, value: inputValues.Mundus.HealthRegen });
		AddEsoInputStatSource("Mundus.SprintSpeed", { mundus: mundusName, value: inputValues.Mundus.SprintSpeed });
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
	
	if (inputValues.Level == 50)
		inputValues.EffectiveLevel = inputValues.Level + inputValues.CPLevel;
	else
		inputValues.EffectiveLevel = inputValues.Level;
		
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
	if (!g_EsoBuildEnableUpdates) return;
	
	var inputValues = GetEsoInputValues();
	var deferredStats = [];
	
	UpdateEsoTestBuildSkillInputValues(inputValues);
		
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
	
	
	UpdateEsoTestBuildSkillInputValues(inputValues);
	
	UpdateEsoReadOnlyStats(inputValues);
	UpdateEsoBuildMundusList2();
	UpdateEsoBuildSetInfo();
	UpdateEsoBuildToggleSets();
	UpdateEsoBuildToggleSkills();
	UpdateEsoBuildItemLinkSetCounts();
	UpdateEsoAllSkillDescription();
	UpdateEsoAllSkillCost();
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
	
	g_EsoBuildEnableUpdates = false;
	EnableEsoRaceSkills(newRace);
	g_EsoBuildEnableUpdates = true;
		
	UpdateEsoComputedStatsList();
}


function OnEsoClassChange(e)
{
	var newClass = $(this).val();
	
	g_EsoBuildEnableUpdates = false;
	EnableEsoClassSkills(newClass);
	g_EsoBuildEnableUpdates = true;
		
	UpdateEsoComputedStatsList();
}


function OnEsoVampireChange(e)
{
	if ($("#esotbVampireStage").val() > 0)
	{
		$("#esotbWerewolf").prop("checked", false);
	}
	
	UpdateEsoComputedStatsList();
}


function OnEsoWerewolfChange(e)
{
	if ($("#esotbWerewolf").is(":checked"))
	{
		$("#esotbVampireStage").val("0");
	}
	
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
	
	var iconName = itemData.icon.replace(".dds", ".png");
	var iconUrl = "http://esoicons.uesp.net" + iconName;
	var niceName = itemData.name.charAt(0).toUpperCase() + itemData.name.slice(1);
	
	if (iconName == "" || iconName == "/") iconUrl = "";
	
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
	else if (tabId == "esotbStatBlock3")
	{
		UpdateEsoAllSkillDescription();
		UpdateEsoAllSkillCost();
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


function OnEsoWeaponBarSelect1()
{
	SetEsoBuildActiveWeaponBar(1);
	SetEsoBuildActiveSkillBar(1);
	UpdateEsoComputedStatsList();
}


function OnEsoWeaponBarSelect2()
{
	SetEsoBuildActiveWeaponBar(2);
	SetEsoBuildActiveSkillBar(2);
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
	var suffix = " (" + sourceItem.origStatId + ")";
	
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
	else if (sourceItem.passive != null)
	{
		var skillData = sourceItem.passive;
		
		if (skillData == null || skillData.name == null)
			output += "" + value + ": Unknown skill passive";
		else
			output += "" + value + ": " + skillData.name + " " + skillData.rank + " passive in " + skillData.skillLine + " line";
	}
	else
	{
		output += "" + value + ": Unknown";
	}
	
	output += suffix;
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


function CreateEsoBuildToggledSkillData()
{
	g_EsoBuildToggledSkillData = {};
	
	for (var i = 0; i < ESO_PASSIVEEFFECT_MATCHES.length; ++i)
	{
		var skillEffectData = ESO_PASSIVEEFFECT_MATCHES[i];
		if (skillEffectData.toggle !== true) continue;
		
		var id = skillEffectData.id;
		
		if (g_EsoBuildToggledSkillData[id] == null) 
		{
			g_EsoBuildToggledSkillData[id] = {};
			g_EsoBuildToggledSkillData[id].matchData = skillEffectData;
			g_EsoBuildToggledSkillData[id].baseSkillId = skillEffectData.baseSkillId;
			g_EsoBuildToggledSkillData[id].statIds = [];
		}
		
		g_EsoBuildToggledSkillData[id].id = id;
		g_EsoBuildToggledSkillData[id].desc = "";
		g_EsoBuildToggledSkillData[id].valid = false;
		g_EsoBuildToggledSkillData[id].enabled = skillEffectData.enabled;
		g_EsoBuildToggledSkillData[id].count = 0;
		g_EsoBuildToggledSkillData[id].maxTimes = skillEffectData.maxTimes;
		g_EsoBuildToggledSkillData[id].statIds.push(skillEffectData.statId);
	}
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
		g_EsoBuildToggledSetData[id].valid = true;
		g_EsoBuildToggledSetData[id].enabled = setEffectData.enabled;
		g_EsoBuildToggledSetData[id].statIds.push(setEffectData.statId);
		
		if (g_EsoBuildSetData[id] != null && g_EsoBuildSetData[id].averageDesc != null &&
				g_EsoBuildSetData[id].averageDesc[setEffectData.setBonusCount] != null)
		{
			g_EsoBuildToggledSetData[id].desc = g_EsoBuildSetData[id].averageDesc[setEffectData.setBonusCount];
		}
	}
	
}


function IsEsoBuildToggledSkillEnabled(skillId)
{
	if (g_EsoBuildToggledSkillData[skillId] == null) return false;
	return g_EsoBuildToggledSkillData[skillId].enabled;
}


function SetEsoBuildToggledSkillValid(skillId, valid)
{
	if (g_EsoBuildToggledSkillData[skillId] != null) g_EsoBuildToggledSkillData[skillId].valid = valid;
}


function SetEsoBuildToggledSkillDesc(skillId, desc)
{
	if (g_EsoBuildToggledSkillData[skillId] != null) g_EsoBuildToggledSkillData[skillId].desc = desc;
}


function SetEsoBuildToggledSkillEnable(skillId, enable)
{
	if (g_EsoBuildToggledSkillData[skillId] == null) return false;
	g_EsoBuildToggledSkillData[skillId].enabled = enable;
}


function SetEsoBuildToggledSkillCount(skillId, value)
{
	if (g_EsoBuildToggledSkillData[skillId] == null) return false;
	g_EsoBuildToggledSkillData[skillId].count = parseInt(value);
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


function UpdateEsoBuildToggledSkillData(inputValues)
{
	
	for (var skillId in g_EsoBuildToggledSkillData)
	{
		var toggleSkillData = g_EsoBuildToggledSkillData[skillId];
		var abilityId = toggleSkillData.baseSkillId;
		var abilityData = g_EsoSkillPassiveData[abilityId];
		
		toggleSkillData.valid = false;
		
		if (abilityData == null)
		{
			abilityData = g_EsoSkillActiveData[abilityId];
			if (abilityData == null) continue;
		}			

		if (toggleSkillData.matchData == null) continue;
		
		if (toggleSkillData.matchData.statRequireId != null)
		{
			var requiredStat = inputValues[toggleSkillData.matchData.statRequireId];
			if (requiredStat == null) continue;
			if (parseFloat(requiredStat) < parseFloat(toggleSkillData.matchData.statRequireValue)) continue;
		}
		
		toggleSkillData.valid = true;
		//toggleSkillData.desc = FindMatchingEsoPassiveSkillDescription(toggleSkillData.matchData);
		toggleSkillData.desc = GetEsoSkillDescription(abilityData.abilityId, g_LastSkillInputValues, false, true);
		
		var checkElement = $(".esotbToggledSkillItem[skillid=\"" + skillId + "\"]").find(".esotbToggleSkillCheck");
		
		if (checkElement.length > 0)
		{
			SetEsoBuildToggledSkillEnable(skillId, checkElement.is(":checked"));
			
			var countElement = checkElement.next(".esotbToggleSkillNumber");
			if (countElement.length > 0) SetEsoBuildToggledSkillCount(skillId, countElement.val());
		}
	}
	
}

function FindMatchingEsoPassiveSkillDescription(matchData)
{
	if (matchData == null) return "";
	
	for (var skillId in g_EsoSkillPassiveData)
	{
		var skillData = g_EsoSkillPassiveData[skillId];
		var abilityData = g_SkillsData[skillData.abilityId];
		
		if (abilityData == null) continue;
		
		var skillDesc = GetEsoSkillDescription(abilityData.abilityId, g_LastSkillInputValues, false, true);
		var rawDesc = skillDesc;
		
		if (matchData.match != null)
		{
			var matches = rawDesc.match(matchData.match);
			if (matches == null) continue;
		}
	
		if (matchData.skillName != null)
		{
			if (abilityData.name.toUpperCase() != matchData.skillName.toUpperCase()) continue;
		}
		
		if (matchData.skillRank != null)
		{
			if (abilityData.rank != matchData.skillRank) continue;
		}

		return rawDesc;
	}
	
	return "";
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


function OnEsoBuildToggleSkill(e)
{
	var skillId = $(this).parent().attr("skillId");
	if (skillId == null || skillId == "") return;
	
	UpdateEsoComputedStatsList();
}


function OnEsoBuildToggleSkillNumber(e)
{
	var skillId = $(this).parent().attr("skillId");
	if (skillId == null || skillId == "") return;
	
	var toggleData = g_EsoBuildToggledSkillData[skillId];
	if (toggleData == null) return;
	
	var value = $(this).val();
	
	if (value < 0) $(this).val("0");
	if (toggleData.maxTimes != null && value > toggleData.maxTimes)  $(this).val(toggleData.maxTimes);
	
	UpdateEsoComputedStatsList();
}


function UpdateEsoBuildToggleSkills()
{
	var element = $("#esotbToggledSkillInfo");
	var output = "";
	
	for (var skillId in g_EsoBuildToggledSkillData)
	{
		var skillData = g_EsoBuildToggledSkillData[skillId];
		if (!skillData.valid) continue;
		output += CreateEsoBuildToggleSkillHtml(skillData);
	}
	
	element.html(output);
	$(".esotbToggleSkillCheck").click(OnEsoBuildToggleSkill);
	$(".esotbToggleSkillNumber").on("input", OnEsoBuildToggleSkillNumber);
}


function CreateEsoBuildToggleSkillHtml(skillData)
{
	var output = "<div class='esotbToggledSkillItem' skillid=\"" + skillData.id + "\">";
	var checked = skillData.enabled ? "checked" : "";
	
	output += "<input type='checkbox' class='esotbToggleSkillCheck'  " + checked + " >";
	
	if (skillData.maxTimes != null) 
	{
		output += "<input type='number' class='esotbToggleSkillNumber'  value='" + skillData.count + "' >";
		//output += " (0-" + skillData.maxTimes + ") ";
	}
	
	output += "<div class='esotbToggleSkillTitle'>" + skillData.id + ":</div> ";
	output += "<div class='esotbToggleSkillDesc'>" + skillData.desc + "</div>";
	
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


function OnEsoBuildEscapeKey(e) 
{
	HideEsoBuildClickWall();
}


function GetEsoTestBuildStat(statId)
{
	if (g_EsoComputedStats[statId] != null) return g_EsoComputedStats[statId];
	return g_EsoInputStats[statId];
}


function GetEsoTestBuildSkillInputValues(inputValues)
{
	return g_LastSkillInputValues;
}


function UpdateEsoTestBuildSkillInputValues(inputValues)
{
	if (inputValues == null) return;
	
	var magicka = parseInt(inputValues.Magicka);
	var stamina = parseInt(inputValues.Stamina);
	var health = parseInt(inputValues.Health);
	var spellDamage = parseInt(inputValues.SpellDamage);
	var weaponDamage = parseInt(inputValues.WeaponDamage);
	var level = parseInt(inputValues.EffectiveLevel);
	
	if (isNaN(magicka)) magicka = parseInt(g_EsoComputedStats.Magicka.value);
	if (isNaN(stamina)) stamina = parseInt(g_EsoComputedStats.Stamina.value);
	if (isNaN(health)) health = parseInt(g_EsoComputedStats.Health.value);
	if (isNaN(spellDamage)) spellDamage = parseInt(g_EsoComputedStats.SpellDamage.value);
	if (isNaN(weaponDamage)) weaponDamage = parseInt(g_EsoComputedStats.WeaponDamage.value);
	
	g_LastSkillInputValues = 
	{ 
			Magicka			: magicka,
			Stamina			: stamina,
			Health			: health,
			SpellDamage		: spellDamage,
			WeaponDamage	: weaponDamage,
			MaxStat			: Math.max(stamina, magicka),
			MaxDamage		: Math.max(spellDamage, weaponDamage),
			EffectiveLevel	: level,
			LightArmor		: parseInt(inputValues.ArmorLight),
			MediumArmor		: parseInt(inputValues.ArmorMedium),
			HeavyArmor		: parseInt(inputValues.ArmorHeavy),
			ArmorTypes		: parseInt(inputValues.ArmorTypes),
			DaggerWeapon	: parseInt(inputValues.WepaonDagger),
			
			//DamageBonus?
	};
	
	return g_LastSkillInputValues; 
}


function SetEsoBuildActiveWeaponBar(barIndex)
{
	if (barIndex == 1)
	{
		$("#esotbWeaponBar1").addClass("esotbWeaponSelect");
		$("#esotbWeaponBar2").removeClass("esotbWeaponSelect");
		
		//$("#esotbWeaponSelect1").addClass("esotbWeaponSelect");
		//$("#esotbItemMainHand1").addClass("esotbWeaponSelect");
		//$("#esotbItemOffHand1").addClass("esotbWeaponSelect");
		//$("#esotbItemPoison1").addClass("esotbWeaponSelect");
			
		//$("#esotbWeaponSelect2").removeClass("esotbWeaponSelect");
		//$("#esotbItemMainHand2").removeClass("esotbWeaponSelect");
		//$("#esotbItemOffHand2").removeClass("esotbWeaponSelect");
		//$("#esotbItemPoison2").removeClass("esotbWeaponSelect");
		
		g_EsoBuildActiveWeapon = barIndex;
	}
	else if (barIndex == 2)
	{
		$("#esotbWeaponBar1").removeClass("esotbWeaponSelect");
		$("#esotbWeaponBar2").addClass("esotbWeaponSelect");
		
		//$("#esotbWeaponSelect1").removeClass("esotbWeaponSelect");
		///$("#esotbItemMainHand1").removeClass("esotbWeaponSelect");
		//$("#esotbItemOffHand1").removeClass("esotbWeaponSelect");
		//$("#esotbItemPoison1").removeClass("esotbWeaponSelect");
			
		//$("#esotbWeaponSelect2").addClass("esotbWeaponSelect");
		//$("#esotbItemMainHand2").addClass("esotbWeaponSelect");
		//$("#esotbItemOffHand2").addClass("esotbWeaponSelect");
		//$("#esotbItemPoison2").addClass("esotbWeaponSelect");
		
		g_EsoBuildActiveWeapon = barIndex;
	}
	
}


function SetEsoBuildActiveSkillBar(skillBarIndex)
{
	SetEsoSkillBarSelect(skillBarIndex);
}


function OnEsoBuildSkillBarSwap(e, skillBarIndex)
{
	SetEsoBuildActiveWeaponBar(skillBarIndex);	
	UpdateEsoComputedStatsList();
}


function OnEsoBuildSkillUpdate(e)
{
	UpdateEsoComputedStatsList();
}


function OnEsoBuildSkillBarUpdate(e)
{
	UpdateEsoComputedStatsList();
}


function CountEsoBarSkillsWithSkillLine(skillLine)
{
	var skillBar = g_EsoSkillBarData[g_EsoBuildActiveWeapon - 1];
	var count = 0;
	
	if (skillBar == null) return 0;
	skillLine = skillLine.toUpperCase();
	
	for (var i = 0; i < skillBar.length; ++i)
	{
		var skillId = skillBar[i].skillId;
		if (skillId == null || skillId <= 0) continue;
		
		var skillData = g_SkillsData[skillId];
		if (skillData == null) continue;
		
		if (skillData.skillLine.toUpperCase() == skillLine) ++count;
	}
	
	return count;
}


function CountEsoBarSkillsWithSkillType(skillType)
{
	var skillBar = g_EsoSkillBarData[g_EsoBuildActiveWeapon - 1];
	var count = 0;
	
	if (skillBar == null) return 0;
	skillType = skillType.toUpperCase();
	
	for (var i = 0; i < skillBar.length; ++i)
	{
		var skillId = skillBar[i].skillId;
		if (skillId == null || skillId <= 0) continue;
		
		var skillData = g_SkillsData[skillId];
		if (skillData == null) continue;
		
		if (skillData.skillTypeName.substr(0, skillType.length).toUpperCase() == skillType) ++count;
	}
	
	return count;
}


function esotbOnDocReady()
{
	GetEsoSkillInputValues = GetEsoTestBuildSkillInputValues;
	CreateEsoComputedStats();
	UpdateEsoComputedStatsList();
	CreateEsoBuildToggledSetData();
	CreateEsoBuildToggledSkillData();
		
	$("#esotbRace").change(OnEsoRaceChange)
	$("#esotbClass").change(OnEsoClassChange)
	$("#esotbVampireStage").change(OnEsoVampireChange)
	$("#esotbWerewolf").change(OnEsoWerewolfChange)
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
	
	$("#esotbWeaponBar1").click(OnEsoWeaponBarSelect1);
	$("#esotbWeaponBar2").click(OnEsoWeaponBarSelect2);
	
	$(document).on("EsoSkillBarSwap", OnEsoBuildSkillBarSwap);
	$(document).on("EsoSkillUpdate", OnEsoBuildSkillUpdate);
	$(document).on("EsoSkillBarUpdate", OnEsoBuildSkillBarUpdate);
	
	$(document).keyup(function(e) {
	    if (e.keyCode == 27) OnEsoBuildEscapeKey(e);
	});
}


$( document ).ready(esotbOnDocReady);