<?php

require_once("UespMemcachedSession.php");

$canViewEsoMorrowindPts = false;

	/* If set to true load data from both minedItem and minedItemSummary when loading a specific item.
	 * Introduced in update 30. */
const UESP_ESO_USE_COMBINED_MINEDITEM = true;

const UESP_ESO_ICON_URL = "//esoicons.uesp.net";

const ENABLE_ESO_PAGEVIEW_UPDATES = true;

	/* Make sure these match values used in uespLog */
const POWERTYPE_MAGICKA = 0;
const POWERTYPE_INVALID = -1;
const POWERTYPE_HEALTH = -2;
const POWERTYPE_STAMINA = 6;
const POWERTYPE_ULTIMATE = 10;
const UESP_POWERTYPE_SOULTETHER    = -50;
const UESP_POWERTYPE_LIGHTARMOR    = -51;
const UESP_POWERTYPE_MEDIUMARMOR   = -52;
const UESP_POWERTYPE_HEAVYARMOR    = -53;
const UESP_POWERTYPE_WEAPONDAGGER  = -54;
const UESP_POWERTYPE_ARMORTYPE     = -55;
const UESP_POWERTYPE_DAMAGE        = -56;
const UESP_POWERTYPE_ASSASSINATION = -57;
const UESP_POWERTYPE_FIGHTERSGUILD = -58;
const UESP_POWERTYPE_DRACONICPOWER = -59;
const UESP_POWERTYPE_SHADOW = -60;
const UESP_POWERTYPE_SIPHONING = -61;
const UESP_POWERTYPE_SORCERER = -62;
const UESP_POWERTYPE_MAGESGUILD = -63;
const UESP_POWERTYPE_SUPPORT = -64;
const UESP_POWERTYPE_ANIMALCOMPANION = -65;
const UESP_POWERTYPE_GREENBALANCE = -66;
const UESP_POWERTYPE_WINTERSEMBRACE = -67;
const UESP_POWERTYPE_MAGICHEALTHCAP = -68;
const UESP_POWERTYPE_BONETYRANT = -69;
const UESP_POWERTYPE_GRAVELORD = -70;
const UESP_POWERTYPE_SPELLDAMAGECAPPED = -71;
const UESP_POWERTYPE_MAGICKAWITHWD = -72;
const UESP_POWERTYPE_MAGICKACAPPED = -73;
const UESP_POWERTYPE_WEAPONPOWER = -74;
const UESP_POWERTYPE_CONSTANTVALUE = -75;
const UESP_POWERTYPE_HEALTHORSPELLDAMAGE = -76;
const UESP_POWERTYPE_RESISTANCE = -77;
const UESP_POWERTYPE_MAGICLIGHTARMOR = -78;
const UESP_POWERTYPE_HEALTHORDAMAGE = -79;

	// Set to true to show levels as "CP160", false to show as "VR16"
const UESP_SHOWCPLEVEL = true;


$APIVERSION_TO_GAMEUPDATE = array(
		"100010" => "5",
		"100011" => "6",
		"100012" => "7",
		"100013" => "8",
		"100014" => "9",
		"100015" => "10",
		"100016" => "11",
		"100017" => "12",
		"100018" => "13",
		"100019" => "14",
		"100020" => "15",
		"100021" => "16",
		"100022" => "17",
		"100023" => "18",
		"100024" => "19",
		"100025" => "20",
		"100026" => "21",
		"100027" => "22",
		"100028" => "23",
		"100029" => "24",
		"100030" => "25",
		"100031" => "26",
		"100032" => "27",
		"100033" => "28",
		"100034" => "29",
		"100035" => "30",
		"101031" => "31",
		"101032" => "32",
		"101033" => "33",
		"101034" => "34",
		"101035" => "35",
		"101036" => "36",
);


$APIVERSION_TO_GAMEVERSION = array(
		"100010" => "1.5",
		"100011" => "2.0",
		"100012" => "2.1",
		"100013" => "2.2",
		"100014" => "2.3",
		"100015" => "2.4",
		"100016" => "2.5",
		"100017" => "2.6",
		"100018" => "2.7",
		"100019" => "3.0",
		"100020" => "3.1",
		"100021" => "3.2",
		"100022" => "3.3",
		"100023" => "4.0",
		"100024" => "4.1",
		"100025" => "4.2",
		"100026" => "4.3",
		"100027" => "5.0",
		"100028" => "5.1",
		"100029" => "5.2",
		"100030" => "5.3",
		"100031" => "6.0",
		"100032" => "6.1",
		"100033" => "6.2",
		"100034" => "6.3",
		"100035" => "7.0",
		"101031" => "7.1",
		"101032" => "7.2",
		"101033" => "7.3",
);


$GAMEUPDATE_TO_GAMENAME = array(
		"1" => "Craglorn",
		"2" => "Veteran Crypt of Hearts",
		"3" => "Dye Stations",
		"4" => "Upper Craglorn",
		"5" => "Veteran City of Ash",
		"6" => "Justice and CP",
		"7" => "Combat/PVP Rebalance",
		"8" => "Orsinium",
		"9" => "Hew's Bane",
		"10" => "Gold Coast",
		"11" => "Shadows of the Hist",
		"12" => "One Tamriel",
		"13" => "Homestead",
		"14" => "Morrowind",
		"15" => "Horns of the Reach",
		"16" => "Clockwork City",
		"17" => "Dragon Bones",
		"18" => "Summerset",
		"19" => "Wolfhunter",
		"20" => "Murkmire",
		"21" => "Wrathstone",
		"22" => "Elsweyr",
		"23" => "Scalebreaker",
		"24" => "Dragonhold",
		"25" => "Harrowstorm",
		"26" => "Greymoor",
		"27" => "Harrowstorm",
		"28" => "Markarth",
		"29" => "Flames of Ambition",
		"30" => "Blackwood",
		"31" => "Waking Flame",
	);


$ESO_ITEMTRAIT_FULLTEXTS = array(
		-1 => "",
		18 => "Armor Divines",
		17 => "Armor Exploration",
		12 => "Armor Impenetrable",
		16 => "Armor Infused",
		20 => "Armor Intricate",
		19 => "Armor Ornate",
		13 => "Armor Reinforced",
		11 => "Armor Sturdy",
		15 => "Armor Training",
		14 => "Armor Well Fitted",
		22 => "Jewelry Arcane",
		21 => "Jewelry Healthy",
		24 => "Jewelry Ornate",
		23 => "Jewelry Robust",
		0 => "",
		2 => "Weapon Charged",
		5 => "Weapon Defending",
		4 => "Weapon Infused",
		9 => "Weapon Intricate",
		10 => "Weapon Ornate",
		1 => "Weapon Powered",
		3 => "Weapon Precise",
		7 => "Weapon Sharpened",
		6 => "Weapon Training",
		8 => "Weapon Weighted",
		25 => "Armor Nirnhoned",
		26 => "Weapon Nirnhoned",
);


$ESO_ITEMTRAIT10_FULLTEXTS = array(
		-1 => "",
		18 => "Armor Divines",
		17 => "Armor Prosperous",
		12 => "Armor Impenetrable",
		16 => "Armor Infused",
		20 => "Armor Intricate",
		19 => "Armor Ornate",
		13 => "Armor Reinforced",
		11 => "Armor Sturdy",
		15 => "Armor Training",
		14 => "Armor Well Fitted",
		22 => "Jewelry Arcane",
		21 => "Jewelry Healthy",
		24 => "Jewelry Ornate",
		23 => "Jewelry Robust",
		0 => "",
		2 => "Weapon Charged",
		5 => "Weapon Defending",
		4 => "Weapon Infused",
		9 => "Weapon Intricate",
		10 => "Weapon Ornate",
		1 => "Weapon Powered",
		3 => "Weapon Precise",
		7 => "Weapon Sharpened",
		6 => "Weapon Training",
		8 => "Weapon Decisive",
		25 => "Armor Nirnhoned",
		26 => "Weapon Nirnhoned",
);


$ESO_ITEMTRAIT15_FULLTEXTS = array(
		-1 => "",
		18 => "Armor Divines",
		17 => "Armor Invigorating",
		12 => "Armor Impenetrable",
		16 => "Armor Infused",
		20 => "Armor Intricate",
		19 => "Armor Ornate",
		13 => "Armor Reinforced",
		11 => "Armor Sturdy",
		15 => "Armor Training",
		14 => "Armor Well Fitted",
		22 => "Jewelry Arcane",
		21 => "Jewelry Healthy",
		24 => "Jewelry Ornate",
		23 => "Jewelry Robust",
		
		31 => "Jewelry Bloodthirsty",	// Update 18
		29 => "Jewelry Harmony",
		33 => "Jewelry Infused",
		27 => "Jewelry Intricate",
		32 => "Jewelry Protective",
		28 => "Jewelry Swift",
		30 => "Jewelry Triune",
		
		0 => "",
		2 => "Weapon Charged",
		5 => "Weapon Defending",
		4 => "Weapon Infused",
		9 => "Weapon Intricate",
		10 => "Weapon Ornate",
		1 => "Weapon Powered",
		3 => "Weapon Precise",
		7 => "Weapon Sharpened",
		6 => "Weapon Training",
		8 => "Weapon Decisive",
		25 => "Armor Nirnhoned",
		26 => "Weapon Nirnhoned",
		
			// Update 30 Companion traits
		34 => "Weapon Quickened",
		35 => "Weapon Prolific",
		36 => "Weapon Focused",
		37 => "Weapon Shattering",
		38 => "Weapon Aggressive",
		39 => "Weapon Soothing",
		40 => "Weapon Augmented",
		41 => "Weapon Bolstered",
		42 => "Weapon Vigorous",
		
		43 => "Armor Quickened",
		44 => "Armor Prolific",
		45 => "Armor Focused",
		46 => "Armor Shattering",
		47 => "Armor Aggressive",
		48 => "Armor Soothing",
		49 => "Armor Augmented",
		50 => "Armor Bolstered",
		51 => "Armor Vigorous",
		
		52 => "Jewelry Quickened",
		53 => "Jewelry Prolific",
		54 => "Jewelry Focused",
		55 => "Jewelry Shattering",
		56 => "Jewelry Aggressive",
		57 => "Jewelry Soothing",
		58 => "Jewelry Augmented",
		59 => "Jewelry Bolstered",
		60 => "Jewelry Vigorous",
		
);


$ESO_CRAFTTYPES = array(
		-1 => "",
		0 => "",
		1 => "Blacksmithing",
		2 => "Clothier",
		3 => "Enchanting",
		4 => "Alchemy",
		5 => "Provisioning",
		6 => "Woodworking",
		7 => "Jewelry Crafting",
);


$ESO_STATTYPES = array(
		0 => "None",
		1 => "Attack Power",
		2 => "Weapon Power",
		3 => "Armor Rating",
		4 => "Magicka Max",
		5 => "Magicka Regen Combat",
		6 => "Magicka Regen Idle",
		7 => "Health Max",
		8 => "Health Regen Combat",
		9 => "Health Regen Idle",
		10 => "Healing Taken",
		11 => "Dodge",
		12 => "Healing Done",
		13 => "Spell Resist",
		14 => "Block",
		16 => "Critical Strike",
		20 => "Mitigation",
		22 => "Physical Resist",
		23 => "Spell Critical",
		24 => "Critical Resistance",
		25 => "Spell Power",
		26 => "Spell Mitigation",
		29 => "Stamina Max",
		30 => "Stamina Regen Combat",
		31 => "Stamina Regen Idle",
		32 => "Miss",
		33 => "Physical Penetration",
		34 => "Spell Penetration",
		35 => "Weapon Damage",
		36 => "Damage Resist Start",
		37 => "Damage Resist Generic",
		38 => "Damage Resist Physical",
		39 => "Damage Resist Fire",
		40 => "Damage Resist Shock",
		41 => "Damage Resist Oblivion",
		42 => "Damage Resist Cold",
		43 => "Damage Resist Earth",
		44 => "Damage Resist Magic",
		45 => "Damage Resist Drown",
		46 => "Damage Resist Disease",
		47 => "Damage Resist Poison",
		48 => "Mount Stamina Max",
		49 => "Mount Stamina Regen Combat",
		50 => "Mount Stamina Regen Moving",
);


$ESO_ABILITYTYPES = array(
		0 => "None",
		1 => "Damage",
		2 => "Heal",
		3 => "Resurrect",
		4 => "Blink",
		5 => "Bonus",
		6 => "RegisterTrigger",
		7 => "SetTarget",
		8 => "Threat",
		9 => "Stun",
		10 => "Snare",
		11 => "Silence",
		12 => "RemoveType",
		13 => "SeCcooldown",
		14 => "CombatResource",
		15 => "DamageShield",
		16 => "MovePosition",
		17 => "Knockback",
		18 => "Charge",
		19 => "Immunity",
		20 => "Intercept",
		21 => "Reflection",
		22 => "AreaEffect",
		23 => "Deprecated2",
		24 => "CreateInventoryItem",
		25 => "DamageLimit",
		26 => "AreaTeleport",
		27 => "Fear",
		28 => "Trauma",
		29 => "Stealth",
		30 => "SeeStealth",
		31 => "Flight",
		32 => "Disorient",
		33 => "Stagger",
		34 => "Slowfall",
		35 => "Jump",
		36 => "Wind",
		37 => "Summon",
		38 => "Mount",
		39 => "Charm",
		40 => "Bladeturn",
		41 => "Nonexistent",
		42 => "Nokill",
		43 => "Noaggro",
		44 => "Dispel",
		45 => "Vampire",
		46 => "CreateInteractable",
		47 => "ModifyCooldown",
		48 => "Levitate",
		49 => "Pacify",
		50 => "ActionList",
		51 => "Interrupt",
		52 => "Block",
		53 => "OffBalance",
		54 => "Exhausted",
		55 => "ModifyDuration",
		56 => "Dodge",
		57 => "Shownon",
		58 => "Misdirect",
		59 => "Freecast",
		60 => "SiegeCreate",
		61 => "SiegeAreaEffect",
		62 => "Defend",
		63 => "FreeInteract",
		64 => "ChangeAppearance",
		65 => "AttackerReflect",
		66 => "AttackerIntercept",
		67 => "Disarm",
		68 => "Parry",
		69 => "Pathline",
		70 => "DoubleFire",
		71 => "FireOroc",
		72 => "Leap",
		73 => "Reveal",
		74 => "Siegepackup",
		75 => "Recall",
		76 => "Grantability",
		77 => "Hide",
		78 => "Sethotbar",
		79 => "Nolockpick",
		80 => "Fillsoulgem",
		81 => "SoulgemResurrect",
		82 => "DespawnOverride",
		83 => "UpdateDeathDialog",
		84 => "Clairvoyance",
		85 => "Clientfx",
		86 => "AvoidDeath",
		87 => "NoncombatBonus",
		88 => "NoSeeTarget",
		89 => "Deprecated",
		90 => "SetPersonality",
		91 => "Basic",
		92 => "RewindTime",
		93 => "LightHeavyAttackOverride",
		94 => "DeriveSstatCache",
		95 => "AvaReach",
		96 => "RandomBranch",
		97 => "MountBlock",
		98 => "Deprecated3",
		99 => "HardDismount",
		100 => "LinkTarget",
		101 => "CustomTargetArea",
		102 => "DamageTransfer",
		103 => "DisableItemSets",
		104 => "FollowWaypointPath",
		105 => "SetAimatTarget",
		106 => "FaceTarget",
		107 => "LosMovePosition",
		108 => "DisableClientTurning",
		109 => "DamageImmune",
		110 => "StopMoving",
		111 => "ResourceTap",
);

$ESO_DAMAGETYPES = array(
		0 => "None",
		1 => "Generic",
		2 => "Physical",
		3 => "Flame",
		4 => "Shock",
		5 => "Oblivion",
		6 => "Frost",
		7 => "Earth",
		8 => "Magic",
		9 => "Drown",
		10 => "Disease",
		11 => "Poison",
		12 => "Bleed",
);


$ESO_COMBATMECHANICS = array(
		-2 => "Health",
		-1 => "Invalid",
		0 => "Magicka",
		1 => "Werewolf",
		4 => "Power",
		6 => "Stamina",
		7 => "Momentum",
		9 => "Finesse",
		10 => "Ultimate",
		11 => "Mount Stamina",
		12 => "Health Bonus",
);


$ESO_COMBATMECHANICS_34 = array(
		-1 => "Invalid",
		0 => "",
		1 => "Magicka",
		2 => "Werewolf",
		4 => "Stamina",
		8 => "Ultimate",
		16 => "Mount Stamina",
		32 => "Health",
		64 => "Daedric",
);


$ESO_SKILLTYPES = array(
		-1 => "",
		0 => "",
		1 => "Class",
		2 => "Weapon",
		3 => "Armor",
		4 => "World",
		5 => "Guild",
		6 => "Alliance War",
		7 => "Racial",
		8 => "Craft",
		9 => "Champion",
);


$ESO_CRAFT_REQUIRESKILLS = array(
		-1 => "",
		0 => "",
		1 => "Metalworking",
		2 => "Tailoring",
		3 => "Potency Improvement",
		4 => "Solvent Proficiency",
		5 => "Recipe Improvement",
		6 => "Woodworking",
		7 => "Engraver",
);


$ESO_ATTRIBUTES = array(
		-1 => "",
		0 => "None",
		1 => "Health",
		2 => "Magicka",
		3 => "Stamina",
);


$ESO_ITEMTRAIT_TEXTS = array(
		-1 => "",
		18 => "Divines",
		17 => "Exploration",
		12 => "Impenetrable",
		16 => "Infused",
		20 => "Intricate",
		19 => "Ornate",
		13 => "Reinforced",
		11 => "Sturdy",
		15 => "Training",
		14 => "Well Fitted",
		22 => "Arcane",
		21 => "Healthy",
		24 => "Ornate",
		23 => "Robust",
		0 => "",
		2 => "Charged",
		5 => "Defending",
		4 => "Infused",
		9 => "Intricate",
		10 => "Ornate",
		1 => "Powered",
		3 => "Precise",
		7 => "Sharpened",
		6 => "Training",
		8 => "Weighted",
		25 => "Nirnhoned",
		26 => "Nirnhoned",
);


$ESO_ITEMTRAIT10_TEXTS = array(
		-1 => "",
		18 => "Divines",
		17 => "Prosperous",
		12 => "Impenetrable",
		16 => "Infused",
		20 => "Intricate",
		19 => "Ornate",
		13 => "Reinforced",
		11 => "Sturdy",
		15 => "Training",
		14 => "Well Fitted",
		22 => "Arcane",
		21 => "Healthy",
		24 => "Ornate",
		23 => "Robust",
		0 => "",
		2 => "Charged",
		5 => "Defending",
		4 => "Infused",
		9 => "Intricate",
		10 => "Ornate",
		1 => "Powered",
		3 => "Precise",
		7 => "Sharpened",
		6 => "Training",
		8 => "Decisive",
		25 => "Nirnhoned",
		26 => "Nirnhoned",
);


$ESO_ITEMTRAIT15_TEXTS = array(
		-1 => "",
		18 => "Divines",
		17 => "Invigorating",
		12 => "Impenetrable",
		16 => "Infused",
		20 => "Intricate",
		19 => "Ornate",
		13 => "Reinforced",
		11 => "Sturdy",
		15 => "Training",
		14 => "Well Fitted",
		22 => "Arcane",
		21 => "Healthy",
		24 => "Ornate",
		23 => "Robust",
		
		31 => "Bloodthirsty",	// Update 18
		29 => "Harmony",
		33 => "Infused",
		27 => "Intricate",
		32 => "Protective",
		28 => "Swift",
		30 => "Triune",
		
		0 => "",
		2 => "Charged",
		5 => "Defending",
		4 => "Infused",
		9 => "Intricate",
		10 => "Ornate",
		1 => "Powered",
		3 => "Precise",
		7 => "Sharpened",
		6 => "Training",
		8 => "Decisive",
		25 => "Nirnhoned",
		26 => "Nirnhoned",
		
			// Update 30 Companion traits
		34 => "Quickened",
		35 => "Prolific",
		36 => "Focused",
		37 => "Shattering",
		38 => "Aggressive",
		39 => "Soothing",
		40 => "Augmented",
		41 => "Bolstered",
		42 => "Vigorous",
		
		43 => "Quickened",
		44 => "Prolific",
		45 => "Focused",
		46 => "Shattering",
		47 => "Aggressive",
		48 => "Soothing",
		49 => "Augmented",
		50 => "Bolstered",
		51 => "Vigorous",
		
		52 => "Quickened",
		53 => "Prolific",
		54 => "Focused",
		55 => "Shattering",
		56 => "Aggressive",
		57 => "Soothing",
		58 => "Augmented",
		59 => "Bolstered",
		60 => "Vigorous",
);


$ESO_ITEMTRAIT_DESCRIPTIONS = array(
		-1 => "",
		0 => "",
		
		18 => "Increases Mundus Stone effects by |cffffff{value}|r%.",
		17 => "Increases Health, Magicka, and Stamina Recovery |cffffff{value}|r.",
		12 => "Increases critical resistance by |cffffff{value}|r and this item takes |cffffff50|r% less durability damage.",
		16 => "Increase armor enchantment effect by |cffffff{value}|r%.",
		20 => "Increases inspiration gained from deconstruction of this item by |cffffff{value}|r%, and gain additional refined material upon deconstruction of this item.",
		19 => "This item sells to merchants for |cffffff{value}|r% more.",
		13 => "Increases this item's Armor value by |cffffff{value}|r%.",
		11 => "Reduces the cost of Block by |cffffff{value}|r%.",
		15 => "Increases experience gained from kills by |cffffff{value}|r%.",
		14 => "Increases the cost of Roll Dodge and Sprint by |cffffff{value}|r%.",
		25 => "Increases Physical and Spell Resistance by |cffffff{value}|r.",
		
		22 => "Increases Maximum Magicka by |cffffff{value}|r.",
		21 => "Increases Maximum Health by |cffffff{value}|r.",
		24 => "This item sells to merchants for |cffffff{value}|r% more.",
		23 => "Increases Maximum Stamina by |cffffff{value}|r.",
		
			// Update 18 
		31 => "Increases damage against enemies under |cFFFFFF25|r% health by |cffffff{value}|r%.",	
		29 => "Increases damage, healing, resource restore, and damage shield strength of synergies you activate by |cffffff{value}|r%.",
		33 => "Increases jewelry enchantment effectiveness by |cffffff{value}|r%.",
		27 => "Increases inspiration gained from deconstruction of this item by |cffffff{value}|r%, and gain additional refined material upon deconstruction of this item.",
		32 => "Increases Spell Resistance and Physical Resistance by |cffffff{value}|r.",
		28 => "Increases movement speed by |cffffff{value}|r%.",
		30 => "Increases Health, Magicka, and Stamina. by |cffffff{value}|r.",
		
		2 => "Increases change to apply status effects by |cffffff{value}|r%.",
		5 => "Increases Physical and Spell Resistance by |cffffff{value}|r.",
		4 => "Increases weapon enchantment effect by |cffffff{value}|r% and reduces enchantment cooldown by |cffffff50|r%.",
		9 => "Increases inspiration gained from deconstruction of this item by |cffffff{value}|r%, and gain additional refined material upon deconstruction of this item.",
		10 => "This item sells to merchants for |cffffff{value}|r% more.",
		1 => "Increases healing done by |cffffff{value}|r%.",
		3 => "Increases Weapon and Spell Critical by |cffffff{value}|r%.",
		7 => "Increases Physical and Spell Penetration by |cffffff{value}|r.",
		6 => "Increases experience gained from kills by |cffffff{value}|r%.",
		8 => "When you gain Ultimate you have a |cffffff{value}|r% chance to gain 1 additional Ultimate.",
		26 => "Increases Damage of this weapon by |cffffff{value}|r%.",
		
			// Update 30 Companion traits
		34 => "Reduces ability cooldowns by |cffffff5.2|r%.",
		35 => "Increases Ultimate generation by |cffffff26|r%.",
		36 => "Increases Critical Strike Rating by |cffffff160|r.",
		37 => "Increases Penetration by |cffffff2600|r.",
		38 => "Increases damage done by |cffffff3.4|r%.",
		39 => "Increases healing done by |cffffff3.4|r%.",
		40 => "Increases duration of buffs and debuffs by |cffffff5.2|r%.",
		41 => "Reduces damage taken by |cffffff3.4|r%.",
		42 => "Increases Max Health by |cffffff5.2|r%.",
		
		43 => "Reduces ability cooldowns by |cffffff2.6|r%.",
		44 => "Increases Ultimate generation by |cffffff13|r%.",
		45 => "Increases Critical Strike Rating by |cffffff130|r.",
		46 => "Increases Penetration by |cffffff1300|r.",
		47 => "Increases damage done by |cffffff1.7|r%.",
		48 => "Increases healing done by |cffffff1.7|r%.",
		49 => "Increases duration of buffs and debuffs by |cffffff2.6|r%.",
		50 => "Reduces damage taken by |cffffff1.7|r%.",
		51 => "Increases Max Health by |cffffff2.6|r%.",
		
		52 => "Reduces ability cooldowns by |cffffff2.6|r%.",
		53 => "Increases Ultimate generation by |cffffff13|r%.",
		54 => "Increases Critical Strike Rating by |cffffff130|r.",
		55 => "Increases Penetration by |cffffff1300|r.",
		56 => "Increases damage done by |cffffff1.7|r%.",
		57 => "Increases healing done by |cffffff1.7|r%.",
		58 => "Increases duration of buffs and debuffs by |cffffff2.6|r%.",
		59 => "Reduces damage taken by |cffffff1.7|r%.",
		60 => "Increases Max Health by |cffffff2.6|r%.",
);


$ESO_ITEMTRANSMUTETRAIT_IDS = array(
		
		18 => 4610,		// Armor
		17 => 88106,
		12 => 61001,
		16 => 89276,
		20 => 7556,
		19 => 7321,
		13 => 5832,
		11 => 1759,
		15 => 26139,
		14 => 44259,
		25 => 97399,	//89434,
		
		22 => 29461,	// Jewelry
		21 => 54476,
		24 => 15765,
		23 => 55373,
 
		31 => 139761,	// Update 18	
		29 => 140031,
		33 => 140120,
		27 => 138796,
		32 => 140211,
		28 => 139941,
		30 => 139851,
		
		5 => 89327,		// 1H Weapons (double for 2H)
		1 => 89281,
		3 => 88033,
		7 => 89341,
		6 => 89401,
		8 => 89381,
	
		9 => 46281,		// Weapons
		10 => 49399,
		2 => 89362,
		4 => 89267,
		26 => 89603, //89422
);


$ESO_ITEMTRANSMUTETRAIT_2H_IDS = array(
		
		5 => 89332,		// 2H Weapons
		1 => 89285,
		3 => 88036,
		7 => 89352,
		6 => 88136,
		8 => 88112,
				
		9 => 46281,		// Normal Weapons
		10 => 49399,
		2 => 89368,
		4 => 89267,
		26 => 89603, 	// 89422
);


$ESO_ITEMSTYLE_TEXTS = array(
		-1 => "",
		0 => "",
		1 => "Breton",
		2 => "Redguard",
		3 => "Orc",
		4 => "Dunmer",
		5 => "Nord",
		6 => "Argonian",
		7 => "Altmer",
		8 => "Bosmer",
		9 => "Khajiit",
		10 => "Unique",
		11 => "Thieves Guild",
		12 => "Dark Brotherhood",
		13 => "Malacath",
		14 => "Dwemer",
		15 => "Ancient Elf",
		16 => "Akatosh",
		17 => "Barbaric",
		18 => "Bandit",
		19 => "Primitive",
		20 => "Daedric",
		21 => "Trinimac",
		22 => "Ancient Orc",
		23 => "Daggerfall",
		24 => "Ebonheart",
		25 => "Aldmeri",
		26 => "Mercenary",
		27 => "Celestial",
		28 => "Glass",
		29 => "Xivkyn",
		30 => "Soul Shriven",
		31 => "Draugr",
		32 => "Maormer",
		33 => "Akaviri",
		34 => "Imperial",
		35 => "Yokudan",
		36 => "Universal",
		38 => "Tsaesci",
		39 => "Minotaur",
		40 => "Ebony",
		41 => "Abah's Watch",
		42 => "Skinchanger",
		43 => "Morag Tong",
		44 => "Ra Gada",
		45 => "Dro-m'Athra",
		46 => "Assassin's League",
		47 => "Outlaw",
		48 => "Redoran",
		49 => "Hlaalu",
		50 => "Militant Ordinator",
		51 => "Telvanni",
		52 => "Buoyant Armiger",
		53 => "Stalhrim Frostcaster",
		54 => "Ashlander",
		55 => "Worm Cult",
		56 => "Silken Ring",
		57 => "Mazzatun",
		58 => "Grim Arlequin",
		59 => "Hollowjack",
		61 => "Bloodforge",
		62 => "Dreadhorn",
		65 => "Apostle",
		66 => "Ebonshadow",
		67 => 'Undaunted',
		69 => "Fang Lair",
		70 => "Scalecaller",
		71 => "Psijic Order",
		72 => "Sapiarch",
		73 => "Welkynar",
		74 => "Dremora",
		75 => "Pyandonean",
		76 => 'Divine Prosecution',
		77 => "Huntsman",
		78 => "Silver Dawn",
		79 => "Dead-Water",
		80 => "Honor Guard",
		81 => "Elder Argonian",
		82 => "Coldsnap",
		83 => "Meridian",
		84 => "Anequina",
		85 => "Pellitine",
		86 => "Sunspire",
		87 => "Dragon Bone",
		89 => "Stags of Z'en",
		92 => "Dragonguard",
		93 => "Moongrave Fane",
		94 => "New Moon Priest",
		95 => "Shield of Senchal",
		97 => "Icereach Coven",
		98 => "Pyre Watch",
		99 => "Swordthane",
		100 => "Blacreach Vanguard",
		101 => "Greymoore",
		102 => "Sea Giant",
		103 => "Ancestral Nord",
		104 => "Ancestral High Elf",
		105 => "Ancestral Orc",
		106 => 'Thorn Legion',
		107 => 'Hazardous Alchemy',
		110 => 'Ancestral Reach',
		111 => 'Nighthollow',
		112 => 'Arkthzand Armory',
		113 => 'Wayward Guardian',
		116 => 'True-Sworn',
		117 => 'Waking Flame',
		118 => 'Dremora Kynreeve',
		120 => 'Black Fin Legion',
		121 => 'Ivory Brigade',
		122 => 'Sul-Xan',
);


$ESO_ITEMQUALITY_TEXTS = array(
		-1 => "",
		0 => "Trash",
		1 => "Normal",
		2 => "Fine",
		3 => "Superior",
		4 => "Epic",
		5 => "Legendary",
		6 => "Mythic",
);


$ESO_MECHANIC_TEXTS = array(
		-2 => "Health",
		-1 => "Invalid",
		0 => "Magicka",
		1 => "Werewolf",
		2 => "Fervor",
		3 => "Combo",
		4 => "Power",
		5 => "Charges",
		6 => "Stamina",
		7 => "Momentum",
		8 => "Adrenaline",
		9 => "Finesse",
		10 => "Ultimate",
		11 => "Mount Stamina",
		12 => "Health Bonus",
);


$ESO_MECHANIC_TEXTS_34 = array(
		-1 => "Invalid",
		0 => "",
		1 => "Magicka",
		2 => "Werewolf",
		4 => "Stamina",
		8 => "Ultimate",
		16 => "Mount Stamina",
		32 => "Health",
		64 => "Daedric",
);


$ESO_ITEMARMORTYPE_TEXTS = array(
		-1 => "",
		0 => "",
		1 => "Light",
		2 => "Medium",
		3 => "Heavy",
);


$ESO_ITEMWEAPONTYPE_TEXTS = array(
		-1 => "",
		0 => "",
		1 => "Axe",
		2 => "Hammer",
		3 => "Sword",
		4 => "Two Handed Sword",
		5 => "Two Handed Axe",
		6 => "Two Handed Hammer",
		7 => "Prop",
		8 => "Bow",
		9 => "Healing Staff",
		10 => "Rune",
		11 => "Dagger",
		12 => "Fire Staff",
		13 => "Frost Staff",
		14 => "Shield",
		15 => "Lightning Staff",
);


$ESO_ITEMTYPE_TEXTS = array(
		-1 => "",
		0 => "None",
		1 => "Weapon",
		2 => "Armor",
		3 => "Plug",
		4 => "Food",
		5 => "Trophy",
		6 => "Siege",
		7 => "Potion",
		8 => "Motif",
		9 => "Tool",
		10 => "Ingredient",
		11 => "Additive",
		12 => "Drink",
		13 => "Costume",
		14 => "Disguise",
		15 => "Tabard",
		16 => "Lure",
		17 => "Raw Material",
		18 => "Container",
		19 => "Soul Gem",
		20 => "Weapon Glyph",
		21 => "Armor Glyph",
		22 => "Lockpick",
		23 => "Weapon Booster",
		24 => "Armor Booster",
		25 => "Enchantment Booster",
		26 => "Jewelry Glyph",
		27 => "Spice",
		28 => "Flavoring",
		29 => "Recipe",
		30 => "Poison",
		31 => "Reagent",
		32 => "Enchanting Rune",
		33 => "Potion Base",		// Used to be Alchemy Base prior to update 10
		34 => "Collectible",
		35 => "Blacksmith Raw Material",
		36 => "Blacksmith Material",
		37 => "Woodwork Raw Material",
		38 => "Woodwork Material",
		39 => "Clothier Raw Material",
		40 => "Clothier Material",
		41 => "Blacksmith Booster",
		42 => "Woodwork Booster",
		43 => "Clothier Booster",
		44 => "Style Material",
		45 => "Armor Trait",
		46 => "Weapon Trait",
		47 => "Ava Repair",
		48 => "Trash",
		49 => "Spellcrafting Tablet",
		50 => "Mount",
		51 => "Potency Rune",
		52 => "Aspect Rune",
		53 => "Essence Rune",
		54 => "Fish",
		55 => "Crown Repair",
		56 => "Treasure",
		57 => "Crown Store",
		58 => "Poison Base",		// New in update 10
		59 => "Dye Stamp",
		60 => "Master Writ",		// Update 13
		61 => "Furnishing",
		62 => "Furnishing Material",
		63 => "Jewelry Raw Material",
		64 => "Jewelry Material",
		65 => "Jewelry Booster",	// Update 18
		66 => "Jewelry Trait",
		67 => "Jewelry Raw Booster",
		68 => "Jewelry Raw Trait",
		69 => "Recall Stone",
		70 => "Currency Container",
		71 => "Group Repair",
);


$ESO_ITEMDISPLAYCATEGORY_TEXTS = array(
		-1 => '',
		0 => 'All',
		1 => 'Weapons',
		2 => 'Armor',
		3 => 'Jewelry',
		4 => 'Consumables',
		5 => 'Materials',
		6 => 'Furnishings',
		7 => 'Miscellaneous',
		8 => 'Quest',
		9 => 'Junk',
		10 => 'Blacksmithing',
		11 => 'Clothing',
		12 => 'Woodworking',
		13 => 'Jewelry Crafting',
		14 => 'Alchemy',
		15 => 'Enchanting',
		16 => 'Provisioning',
		17 => 'Style Materials',
		18 => 'Trait Items',
		19 => 'Food',
		20 => 'Drinks',
		21 => 'Recipes',
		22 => 'Potions',
		23 => 'Poisons',
		24 => 'Style Motifs',
		25 => 'Master Writs',
		26 => 'Containers',
		27 => 'Repair Items',
		28 => 'Crown Items',
		29 => 'Appearance Items',
		30 => 'Glyphs',
		31 => 'Soul Gems',
		32 => 'Siege Items',
		33 => 'Tools',
		34 => 'Trophies',
		35 => 'Bait',
		36 => 'Trash',
		37 => 'Food Ingredients',
		38 => 'Drink Ingredients',
		39 => 'Rare Ingredients',
		40 => 'Furnishing Materials',
		41 => 'Companion Items',
);


	// Added in Update 13
$ESO_ITEMSPECIALTYPE_TEXTS = array(
		-1 => "",
		0 => "",
		1 => "Meat Dishes",
		2 => "Fruit Dish",
		3 => "Vegetable Dish",
		4 => "Savoury Dish",
		5 => "Ragout Dish",
		6 => "Entremet Dish",
		7 => "Gourmet Dish",
		8 => "Unique Dish",
		20 => "Alcoholic Beverage",
		21 => "Tea Beverage",
		22 => "Tonic Beverage",
		23 => "Liqueur Beverage",
		24 => "Tincture Beverage",
		25 => "Cordial Tea Beverage",
		26 => "Distillate Beverage",
		27 => "Drink",
		40 => "Meat Ingredient",
		41 => "Vegetable Ingredient",
		42 => "Fruit Ingredient",
		43 => "Food Additive",
		44 => "Alcohol Ingredient",
		45 => "Tea Ingredient",
		46 => "Tonic Ingredient",
		47 => "Drink Additive",
		48 => "Rare Ingredient",
		60 => "Motif Book",
		61 => "Motif Chapter",
		80 => "Rare Fish",
		81 => "Monster Trophy",
		100 => "Treasure Map",
		101 => "Survey Report",
		102 => "Key Fragment",
		103 => "Museum Piece",
		104 => "Recipe Fragment",
		105 => "Scroll",
		106 => "Material Upgrader",
		107 => "Key",
		108 => "Runebox Fragment",
		109 => "Collectible Fragment",
		110 => "Upgrade Fragment",
		111 => "Toy",
		150 => "Herb",
		151 => "Fungus",
		152 => "Animal Parts",
		170 => "Food Recipe",
		171 => "Drink Recipe",
		172 => "Furnishing Diagram",
		173 => "Furnishing Pattern",
		174 => "Furnishing Schematic",
		175 => "Furnishing Formula",
		176 => "Furnishing Design",
		177 => "Furnishing Blueprint",
		178 => "Furnishing Sketch",
		178 => "Jewelry Furnishing Sketch",
		210 => "Furnishing",
		211 => "Light",
		212 => "Seating",
		213 => "Crafting Station",
		214 => "Target Dummy",
		215 => "Attunable Station",
		250 => "Weapon",
		300 => "Armor",
		350 => "Augment",
		400 => "Trebuchet",
		401 => "Ballista",
		402 => "Ram",
		403 => "Universal Siege",
		404 => "Catapult",
		405 => "Forward Camp",
		406 => "Monster",
		407 => "Oil",
		408 => "Battle Standard",
		450 => "Potion",
		500 => "Tool",
		550 => "Additive",
		600 => "Costume",
		650 => "Disguise",
		700 => "Tabard",
		750 => "Lure",
		800 => "Raw Material",
		850 => "Container",
		851 => "Event Container",
		852 => "Style Page Container",
		900 => "Soul Gem",
		950 => "Weapon Glyph",
		1000 => "Armor Glyph",
		1050 => "Lockpick",
		1100 => "Weapon Booster",
		1150 => "Armor Booster",
		1200 => "Enchantment Booster",
		1250 => "Jewelry Glyph",
		1300 => "Spice",
		1350 => "Flavoring",
		1400 => "Poison",
		1450 => "Potion Solvent",
		1460 => "Furnishing Material",
		1465 => "Furnishing Material",
		1500 => "Raw Material",
		1550 => "Material",
		1600 => "Raw Material",
		1650 => "Material",
		1700 => "Raw Material",
		1750 => "Material",
		1800 => "Temper",
		1850 => "Resin",
		1900 => "Tannin",
		1950 => "Style Material",
		2000 => "Armor Trait",
		2050 => "Weapon Trait",
		2100 => "AvA Repair",
		2150 => "Trash",
		2200 => "Tablet",
		2250 => "Mount",
		2300 => "Potency Runestone",
		2350 => "Aspect Runestone",
		2400 => "Essence Runestone",
		2410 => "Furnishing Material",
		2450 => "Fish",
		2500 => "Crown Repair",
		2550 => "Treasure",
		2600 => "Crown Item",
		2650 => "Poison Solvent",
		2700 => "Dye Stamp",
		2750 => "Master Writ",
		2760 => "Holiday Writ",
		2800 => "Jewelry Raw Material",
		2850 => "Jewelry Material",
		2860 => "Jewelry Furnishing Material",	// Update 18
		2900 => "Jewelry Booster",
		2950 => "Jewelry Trait",
		3000 => "Jewelry Raw Booster",
		3050 => "Jewelry Raw Trait",
		3100 => "Keep Recall Stone",
		3150 => 'Group Repair',
);


$ESO_ITEMSPECIALTYPE_RAW_TEXTS = array(
		-1 => "",
		0 => "",
		1 => "Meat",
		2 => "Fruit",
		3 => "Vegetable",
		4 => "Savoury",
		5 => "Ragout",
		6 => "Entremet",
		7 => "Gourmet",
		8 => "Unique",
		20 => "Alcoholic",
		21 => "Tea",
		22 => "Tonic",
		23 => "Liqueur",
		24 => "Tincture",
		25 => "Cordial Tea",
		26 => "Distillate",
		27 => "Unique",
		40 => "Meat",
		41 => "Vegetable",
		42 => "Fruit",
		43 => "Food Additive",
		44 => "Alcohol",
		45 => "Tea",
		46 => "Tonic",
		47 => "Drink Additive",
		48 => "Rare",
		60 => "Motif Book",
		61 => "Motif Chapter",
		80 => "Rare Fish",
		81 => "Monster Trophy",
		100 => "Treasure Map",
		101 => "Survey Report",
		102 => "Key Fragment",
		103 => "Museum Piece",
		104 => "Recipe Fragment",
		105 => "Scroll",
		106 => "Material Upgrader",
		107 => "Key",				// Update 21
		108 => "Runebox Fragment",
		109 => "Collectible Fragment",
		110 => "Upgrade Fragment",
		111 => "Toy",
		150 => "Herb",
		151 => "Fungus",
		152 => "Animal Part",
		170 => "Provisioning Food",
		171 => "Provisioning Drink",
		172 => "Blacksmithing Furnishing",
		173 => "Clothier Furnishing",
		174 => "Enchanting Furnishing",
		175 => "Alchemy Furnishing",
		176 => "Provisioning Furnishing",
		177 => "Woodworking Furnishing",
		178 => "Furnishing Sketch",
		178 => "Jewelry Furnishing Sketch",
		210 => "Ornamental",
		211 => "Light",
		212 => "Seating",
		213 => "Crafting Station",
		214 => "Target Dummy",
		215 => "Attunable Station",
		250 => "Weapon",
		300 => "Armor",
		350 => "Plug",
		400 => "Trebuchet",
		401 => "Ballista",
		402 => "Ram",
		403 => "Universal",
		404 => "Catapult",
		405 => "Graveyard",
		406 => "Monster",
		407 => "Oil",
		408 => "Battle Standard",
		450 => "Potion",
		500 => "Tool",
		550 => "Additive",
		600 => "Costume",
		650 => "Disguise",
		700 => "Tabard",
		750 => "Lure",
		800 => "Raw Material",
		850 => "Container",
		851 => "Event Container",
		852 => "Style Page Container",
		900 => "Soul Gem",
		950 => "Weapon",
		1000 => "Armor",
		1050 => "Lockpick",
		1100 => "Booster",
		1150 => "Booster",
		1200 => "Booster",
		1250 => "Jewelry",
		1300 => "Spice",
		1350 => "Flavoring",
		1400 => "Poison",
		1450 => "Potion Base",
		1460 => "Furnishing Material",
		1465 => "Furnishing Material",
		1500 => "Raw Material",
		1550 => "Material",
		1600 => "Raw Material",
		1650 => "Material",
		1700 => "Raw Material",
		1750 => "Material",
		1800 => "Booster",
		1850 => "Booster",
		1900 => "Clothier Booster",
		1950 => "Style Material",
		2000 => "Trait",
		2050 => "Trait",
		2100 => "Repair",
		2150 => "Trash",
		2200 => "Spellcrafting Tablet",
		2250 => "Mount",
		2300 => "Rune Potency",
		2350 => "Rune Aspect",
		2400 => "Rune Essence",
		2410 => "Furnishing Material",
		2450 => "Fish",
		2500 => "Crown Repair",
		2550 => "Treasure",
		2600 => "Crown Item",
		2650 => "Poison Base",
		2700 => "Dye Stamp",
		2750 => "Master Writ",
		2750 => "Max Value",
		2760 => "Holiday Writ",
		2800 => "Jewelry Raw Material",
		2850 => "Jewelry Material",
		2860 => "Jewelry Furnishing Material",	// Update 18
		2900 => "Jewelry Booster",
		2950 => "Jewelry Trait",
		3000 => "Jewelry Raw Booster",
		3050 => "Jewelry Raw Trait",
		3100 => "Keep Recall Stone",
		3150 => 'Group Repair',
);


$ESO_CUSTOM_MECHANICS = array(
		UESP_POWERTYPE_SOULTETHER => "Ultimate (ignore WD)",
		UESP_POWERTYPE_LIGHTARMOR => "Light Armor",
		UESP_POWERTYPE_MEDIUMARMOR => "Medium Armor",
		UESP_POWERTYPE_HEAVYARMOR => "Heavy Armor",
		UESP_POWERTYPE_WEAPONDAGGER => "Daggers",
		UESP_POWERTYPE_ARMORTYPE => "Armor Types",
		UESP_POWERTYPE_DAMAGE => "Spell + Weapon Damage",
		UESP_POWERTYPE_ASSASSINATION => "Assassination Skills Slotted",
		UESP_POWERTYPE_FIGHTERSGUILD => "Fighters Guild Skills Slotted",
		UESP_POWERTYPE_DRACONICPOWER => "Draconic Power Skills Slotted",
		UESP_POWERTYPE_SHADOW => "Shadow Skills Slotted",
		UESP_POWERTYPE_SIPHONING => "Siphoning Skills Slotted",
		UESP_POWERTYPE_SORCERER => "Sorcerer Skills Slotted",
		UESP_POWERTYPE_MAGESGUILD => "Mages Guild Skills Slotted",
		UESP_POWERTYPE_SUPPORT => "Support Skills Slotted",
		UESP_POWERTYPE_ANIMALCOMPANION => "Animal Companion Skills Slotted",
		UESP_POWERTYPE_GREENBALANCE => "Green Balance Skills Slotted",
		UESP_POWERTYPE_WINTERSEMBRACE => "Winter's Embrace Skills Slotted",
		UESP_POWERTYPE_MAGICHEALTHCAP => "Magicka with Health Cap",
		UESP_POWERTYPE_BONETYRANT => "Bone Tyrant Skills Slotted",
		UESP_POWERTYPE_GRAVELORD => "Grave Lord Skills Slotted",
		UESP_POWERTYPE_SPELLDAMAGECAPPED => "Spell Damage Capped",
		UESP_POWERTYPE_MAGICKAWITHWD => "Magicka and Weapon Damage",
		UESP_POWERTYPE_MAGICKACAPPED => "Magicka Capped",
		UESP_POWERTYPE_WEAPONPOWER => "Weapon Power",
		UESP_POWERTYPE_CONSTANTVALUE => "Constant Value",
		UESP_POWERTYPE_HEALTHORSPELLDAMAGE => "Health or Spell Damage",
		UESP_POWERTYPE_HEALTHORDAMAGE => "Health or Weapon/Spell Damage",
		UESP_POWERTYPE_RESISTANCE => "Max Resistance",
		UESP_POWERTYPE_MAGICLIGHTARMOR => "Magicka and Light Armor (Health Capped)",
);


$ESO_SET_INDEXES = array(
		19 => "Vestments of the Warlock",
		20 => "Witchman Armor",
		21 => "Akaviri Dragonguard",
		22 => "Dreamer's Mantle",
		23 => "Archer's Mind",
		24 => "Footman's Fortune",
		25 => "Desert Rose",
		26 => "Prisoner's Rags",
		27 => "Fiord's Legacy",
		28 => "Barkskin",
		29 => "Sergeant's Mail",
		30 => "Thunderbug's Carapace",
		31 => "Silks of the Sun",
		32 => "Healer's Habit",
		33 => "Viper's Sting",
		34 => "Night Mother's Embrace",
		35 => "Knightmare",
		36 => "Armor of the Veiled Heritance",
		37 => "Death's Wind",
		38 => "Twilight's Embrace",
		39 => "Alessian Order",
		40 => "Night's Silence",
		41 => "Whitestrake's Retribution",
		43 => "Armor of the Seducer",
		44 => "Vampire's Kiss",
		46 => "Noble Duelist's Silks",
		47 => "Robes of the Withered Hand",
		48 => "Magnus' Gift",
		49 => "Shadow of the Red Mountain",
		50 => "The Morag Tong",
		51 => "Night Mother's Gaze",
		52 => "Beckoning Steel",
		53 => "The Ice Furnace",
		54 => "Ashen Grip",
		55 => "Prayer Shawl",
		56 => "Stendarr's Embrace",
		57 => "Syrabane's Grip",
		58 => "Hide of the Werewolf",
		59 => "Kyne's Kiss",
		60 => "Darkstride",
		61 => "Dreugh King Slayer",
		62 => "Hatchling's Shell",
		63 => "The Juggernaut",
		64 => "Shadow Dancer's Raiment",
		65 => "Bloodthorn's Touch",
		66 => "Robes of the Hist",
		67 => "Shadow Walker",
		68 => "Stygian",
		69 => "Ranger's Gait",
		70 => "Seventh Legion Brute",
		71 => "Durok's Bane",
		72 => "Nikulas' Heavy Armor",
		73 => "Oblivion's Foe",
		74 => "Spectre's Eye",
		75 => "Torug's Pact",
		76 => "Robes of Alteration Mastery",
		77 => "Crusader",
		78 => "Hist Bark",
		79 => "Willow's Path",
		80 => "Hunding's Rage",
		81 => "Song of Lamae",
		82 => "Alessia's Bulwark",
		83 => "Elf Bane",
		84 => "Orgnum's Scales",
		85 => "Almalexia's Mercy",
		86 => "Queen's Elegance",
		87 => "Eyes of Mara",
		88 => "Robes of Destruction Mastery",
		89 => "Sentry",
		90 => "Senche's Bite",
		91 => "Oblivion's Edge",
		92 => "Kagrenac's Hope",
		93 => "Storm Knight's Plate",
		94 => "Meridia's Blessed Armor",
		95 => "Shalidor's Curse",
		96 => "Armor of Truth",
		97 => "The Arch-Mage",
		98 => "Necropotence",
		99 => "Salvation",
		100 => "Hawk's Eye",
		101 => "Affliction",
		102 => "Duneripper's Scales",
		103 => "Magicka Furnace",
		104 => "Curse Eater",
		105 => "Twin Sisters",
		106 => "Wilderqueen's Arch",
		107 => "Wyrd Tree's Blessing",
		108 => "Ravager",
		109 => "Light of Cyrodiil",
		110 => "Sanctuary",
		111 => "Ward of Cyrodiil",
		112 => "Night Terror",
		113 => "Crest of Cyrodiil",
		114 => "Soulshine",
		116 => "The Destruction Suite",
		117 => "Relics of the Physician, Ansur",
		118 => "Treasures of the Earthforge",
		119 => "Relics of the Rebellion",
		120 => "Arms of Infernace",
		121 => "Arms of the Ancestors",
		122 => "Ebon Armory",
		123 => "Hircine's Veneer",
		124 => "The Worm's Raiment",
		125 => "Wrath of the Imperium",
		126 => "Grace of the Ancients",
		127 => "Deadly Strike",
		128 => "Blessing of the Potentates",
		129 => "Vengeance Leech",
		130 => "Eagle Eye",
		131 => "Bastion of the Heartland",
		132 => "Shield of the Valiant",
		133 => "Buffer of the Swift",
		134 => "Shroud of the Lich",
		135 => "Draugr's Heritage",
		136 => "Immortal Warrior",
		137 => "Berserking Warrior",
		138 => "Defending Warrior",
		139 => "Wise Mage",
		140 => "Destructive Mage",
		141 => "Healing Mage",
		142 => "Quick Serpent",
		143 => "Poisonous Serpent",
		144 => "Twice-Fanged Serpent",
		145 => "Way of Fire",
		146 => "Way of Air",
		147 => "Way of Martial Knowledge",
		148 => "Way of the Arena",
		155 => "Undaunted Bastion",
		156 => "Undaunted Infiltrator",
		157 => "Undaunted Unweaver",
		158 => "Embershield",
		159 => "Sunderflame",
		160 => "Burning Spellweave",
		161 => "Twice-Born Star",
		162 => "Spawn of Mephala",
		163 => "Bloodspawn",
		164 => "Lord Warden",
		165 => "Scourge Harvester",
		166 => "Engine Guardian",
		167 => "Nightflame",
		168 => "Nerien'eth",
		169 => "Valkyn Skoria",
		170 => "Maw of the Infernal",
		171 => "Eternal Warrior",
		172 => "Infallible Mage",
		173 => "Vicious Serpent",
		176 => "Noble's Conquest",
		177 => "Redistributor",
		178 => "Armor Master",
		179 => "Black Rose",
		180 => "Powerful Assault",
		181 => "Meritorious Service",
		183 => "Molag Kena",
		184 => "Brands of Imperium",
		185 => "Spell Power Cure",
		186 => "Jolting Arms",
		187 => "Swamp Raider",
		188 => "Storm Master",
		190 => "Scathing Mage",
		193 => "Overwhelming Surge",
		194 => "Combat Physician",
		195 => "Sheer Venom",
		196 => "Leeching Plate",
		197 => "Tormentor",
		198 => "Essence Thief",
		199 => "Shield Breaker",
		200 => "Phoenix",
		201 => "Reactive Armor",
		204 => "Endurance",
		205 => "Willpower",
		206 => "Agility",
		207 => "Law of Julianos",
		208 => "Trial by Fire",
		209 => "Armor of the Code",
		210 => "Mark of the Pariah",
		211 => "Permafrost",
		212 => "Briarheart",
		213 => "Glorious Defender",
		214 => "Para Bellum",
		215 => "Elemental Succession",
		216 => "Hunt Leader",
		217 => "Winterborn",
		218 => "Trinimac's Valor",
		219 => "Morkuldin",
		224 => "Tava's Favor",
		225 => "Clever Alchemist",
		226 => "Eternal Hunt",
		227 => "Bahraha's Curse",
		228 => "Syvarra's Scales",
		229 => "Twilight Remedy",
		230 => "Moondancer",
		231 => "Lunar Bastion",
		232 => "Roar of Alkosh",
		234 => "Marksman's Crest",
		235 => "Robes of Transmutation",
		236 => "Vicious Death",
		237 => "Leki's Focus",
		238 => "Fasalla's Guile",
		239 => "Warrior's Fury",
		240 => "Kvatch Gladiator",
		241 => "Varen's Legacy",
		242 => "Pelinal's Wrath",
		243 => "Hide of Morihaus",
		244 => "Flanking Strategist",
		245 => "Sithis' Touch",
		246 => "Galerion's Revenge",
		247 => "Vicecanon of Venom",
		248 => "Thews of the Harbinger",
		253 => "Imperial Physique",
		256 => "Mighty Chudan",
		257 => "Velidreth",
		258 => "Amber Plasm",
		259 => "Heem-Jas' Retribution",
		260 => "Aspect of Mazzatun",
		261 => "Gossamer",
		262 => "Widowmaker",
		263 => "Hand of Mephala",
		264 => "Giant Spider",
		265 => "Shadowrend",
		266 => "Kra'gh",
		267 => "Swarm Mother",
		268 => "Sentinel of Rkugamz",
		269 => "Chokethorn",
		270 => "Slimecraw",
		271 => "Sellistrix",
		272 => "Infernal Guardian",
		273 => "Ilambris",
		274 => "Iceheart",
		275 => "Stormfist",
		276 => "Tremorscale",
		277 => "Pirate Skeleton",
		278 => "The Troll King",
		279 => "Selene",
		280 => "Grothdarr",
		281 => "Armor of the Trainee",
		282 => "Vampire Cloak",
		283 => "Sword-Singer",
		284 => "Order of Diagna",
		285 => "Vampire Lord",
		286 => "Spriggan's Thorns",
		287 => "Green Pact",
		288 => "Beekeeper's Gear",
		289 => "Spinner's Garments",
		290 => "Skooma Smuggler",
		291 => "Shalk Exoskeleton",
		292 => "Mother's Sorrow",
		293 => "Plague Doctor",
		294 => "Ysgramor's Birthright",
		295 => "Jailbreaker",
		296 => "Spelunker",
		297 => "Spider Cultist Cowl",
		298 => "Light Speaker",
		299 => "Toothrow",
		300 => "Netch's Touch",
		301 => "Strength of the Automaton",
		302 => "Leviathan",
		303 => "Lamia's Song",
		304 => "Medusa",
		305 => "Treasure Hunter",
		307 => "Draugr Hulk",
		308 => "Bone Pirate's Tatters",
		309 => "Knight-errant's Mail",
		310 => "Sword Dancer",
		311 => "Rattlecage",
		313 => "Titanic Cleave",
		314 => "Puncturing Remedy",
		315 => "Stinging Slashes",
		316 => "Caustic Arrow",
		317 => "Destructive Impact",
		318 => "Grand Rejuvenation",
		320 => "War Maiden",
		321 => "Defiler",
		322 => "Warrior-Poet",
		323 => "Assassin's Guile",
		324 => "Daedric Trickery",
		325 => "Shacklebreaker",
		326 => "Vanguard's Challenge",
		327 => "Coward's Gear",
		328 => "Knight Slayer",
		329 => "Wizard's Riposte",
		330 => "Automated Defense",
		331 => "War Machine",
		332 => "Master Architect",
		333 => "Inventor's Guard",
		334 => "Impregnable Armor",
		335 => "Draugr's Rest",
		336 => "Pillar of Nirn",
		337 => "Ironblood",
		338 => "Flame Blossom",
		339 => "Blooddrinker",
		340 => "Hagraven's Garden",
		341 => "Earthgore",
		342 => "Domihaus",
		343 => "Caluurion's Legacy",
		344 => "Trappings of Invigoration",
		345 => "Ulfnor's Favor",
		346 => "Jorvuld's Guidance",
		347 => "Plague Slinger",
		348 => "Curse of Doylemish",
		349 => "Thurvokun",
		350 => "Zaan",
		351 => "Innate Axiom",
		352 => "Fortified Brass",
		353 => "Mechanical Acuity",
		354 => "Mad Tinkerer",
		355 => "Unfathomable Darkness",
		356 => "Livewire",
		357 => "Perfected Disciplined Slash",
		358 => "Perfected Defensive Position",
		359 => "Perfected Chaotic Whirlwind",
		360 => "Perfected Piercing Spray",
		361 => "Perfected Concentrated Force",
		362 => "Perfected Timeless Blessing",
		363 => "Disciplined Slash",
		364 => "Defensive Position",
		365 => "Chaotic Whirlwind",
		366 => "Piercing Spray",
		367 => "Concentrated Force",
		368 => "Timeless Blessing",
		369 => "Merciless Charge",
		370 => "Rampaging Slash",
		371 => "Cruel Flurry",
		372 => "Thunderous Volley",
		373 => "Crushing Wall",
		374 => "Precise Regeneration",
		380 => "Prophet's",
		381 => "Broken Soul",
		382 => "Grace of Gloom",
		383 => "Gryphon's Ferocity",
		384 => "Wisdom of Vanus",
		385 => "Adept Rider",
		386 => "Sload's Semblance",
		387 => "Nocturnal's Favor",
		388 => "Aegis of Galenwe",
		389 => "Arms of Relequen",
		390 => "Mantle of Siroria",
		391 => "Vestment of Olorime",
		392 => "Perfected Aegis of Galenwe",
		393 => "Perfected Arms of Relequen",
		394 => "Perfected Mantle of Siroria",
		395 => "Perfected Vestment of Olorime",
		397 => "Balorgh",
		398 => "Vykosa",
		399 => "Hanu's Compassion",
		400 => "Blood Moon",
		401 => "Haven of Ursus",
		402 => "Moon Hunter",
		403 => "Savage Werewolf",
		404 => "Jailer's Tenacity",
		405 => "Bright-Throat's Boast",
		406 => "Dead-Water's Guile",
		407 => "Champion of the Hist",
		408 => "Grave-Stake Collector",
		409 => "Naga Shaman",
		410 => "Might of the Lost Legion",
		411 => "Gallant Charge",
		412 => "Radial Uppercut",
		413 => "Spectral Cloak",
		414 => "Virulent Shot",
		415 => "Wild Impulse",
		416 => "Mender's Ward",
		417 => "Indomitable Fury",
		418 => "Spell Strategist",
		419 => "Battlefield Acrobat",
		420 => "Soldier of Anguish",
		421 => "Steadfast Hero",
		422 => "Battalion Defender",
		423 => "Perfected Gallant Charge",
		424 => "Perfected Radial Uppercut",
		425 => "Perfected Spectral Cloak",
		426 => "Perfected Virulent Shot",
		427 => "Perfected Wild Impulse",
		428 => "Perfected Mender's Ward",
		429 => "Mighty Glacier",
		430 => "Tzogvin's Warband",
		431 => "Icy Conjurer",
		432 => "Stonekeeper",
		433 => "Frozen Watcher",
		434 => "Scavenging Demise",
		435 => "Auroran's Thunder",
		436 => "Symphony of Blades",
		437 => "Coldharbour's Favorite",
		438 => "Senche-raht's Grit",
		439 => "Vastarie's Tutelage",
		440 => "Crafty Alfiq",
		441 => "Vesture of Darloc Brae",
		442 => "Call of the Undertaker",
		443 => "Eye of Nahviintaas",
		444 => "False God's Devotion",
		445 => "Tooth of Lokkestiiz",
		446 => "Claw of Yolnahkriin",
		448 => "Perfected Eye of Nahviintaas",
		449 => "Perfected False God's Devotion",
		450 => "Perfected Tooth of Lokkestiiz",
		451 => "Perfected Claw of Yolnahkriin",
		452 => "Hollowfang Thirst",
		453 => "Dro'Zakar's Claws",
		454 => "Renald's Resolve",
		455 => "Z'en's Redress",
		456 => "Azureblight Reaper",
		457 => "Dragon's Defilement",
		458 => "Grundwulf",
		459 => "Maarselok",
		465 => "Senchal Defender",
		466 => "Marauder's Haste",
		467 => "Dragonguard Elite",
		468 => "Daring Corsair",
		469 => "Ancient Dragonguard",
		470 => "New Moon Acolyte",
		471 => "Hiti's Hearth",
		472 => "Titanborn Strength",
		473 => "Bani's Torment",
		474 => "Draugrkin's Grip",
		475 => "Aegis Caller",
		476 => "Grave Guardian",
		478 => "Mother Ciannait",
		479 => "Kjalnar's Nightmare",
		480 => "Critical Riposte",
		481 => "Unchained Aggressor",
		482 => "Dauntless Combatant",
		487 => "Winter's Respite",
		488 => "Venomous Smite",
		489 => "Eternal Vigor",
		490 => "Stuhn's Favor",
		491 => "Dragon's Appetite",
		492 => "Kyne's Wind",
		493 => "Perfected Kyne's Wind",
		494 => "Vrol's Command",
		495 => "Perfected Vrol's Command",
		496 => "Roaring Opportunist",
		497 => "Perfected Roaring Opportunist",
		498 => "Yandir's Might",
		499 => "Perfected Yandir's Might",
		501 => "Thrassian Stranglers",
		503 => "Ring of the Wild Hunt",
		505 => "Torc of Tonal Constancy",
		506 => "Spell Parasite",
		513 => "Talfyg's Treachery",
		514 => "Unleashed Terror",
		515 => "Crimson Twilight",
		516 => "Elemental Catalyst",
		517 => "Kraglen's Howl",
		518 => "Arkasis's Genius",
		519 => "Snow Treaders",
		520 => "Malacath's Band of Brutality",
		521 => "Bloodlord's Embrace",
		522 => "Perfected Merciless Charge",
		523 => "Perfected Rampaging Slash",
		524 => "Perfected Cruel Flurry",
		525 => "Perfected Thunderous Volley",
		526 => "Perfected Crushing Wall",
		527 => "Perfected Precise Regeneration",
		528 => "Perfected Titanic Cleave",
		529 => "Perfected Puncturing Remedy",
		530 => "Perfected Stinging Slashes",
		531 => "Perfected Caustic Arrow",
		532 => "Perfected Destructive Impact",
		533 => "Perfected Grand Rejuvenation",
		534 => "Stone Husk",
		535 => "Lady Thorn",
		536 => "Radiant Bastion",
		537 => "Voidcaller",
		538 => "Witch-Knight's Defiance",
		539 => "Red Eagle's Fury",
		540 => "Legacy of Karth",
		541 => "Aetherial Ascension",
		542 => "Hex Siphon",
		543 => "Pestilent Host",
		544 => "Explosive Rebuke",
		557 => "Executioner's Blade",
		558 => "Void Bash",
		559 => "Frenzied Momentum",
		560 => "Point-Blank Snipe",
		561 => "Wrath of Elements",
		562 => "Force Overflow",
		563 => "Perfected Executioner's Blade",
		564 => "Perfected Void Bash",
		565 => "Perfected Frenzied Momentum",
		566 => "Perfected Point-Blank Snipe",
		567 => "Perfected Wrath of Elements",
		568 => "Perfected Force Overflow",
		569 => "True-Sworn Fury",
		570 => "Kinras's Wrath",
		571 => "Drake's Rush",
		572 => "Unleashed Ritualist",
		573 => "Dagon's Dominion",
		574 => "Foolkiller's Ward",
		575 => "Ring of the Pale Order",
		576 => "Pearls of Ehlnofey",
		577 => "Encratis's Behemoth",
		578 => "Baron Zaudrus",
		579 => "Frostbite",
		580 => "Deadlands Assassin",
		581 => "Bog Raider",
		582 => "Hist Whisperer",
		583 => "Heartland Conqueror",
		584 => "Diamond's Victory",
		585 => "Saxhleel Champion",
		586 => "Sul-Xan's Torment",
		587 => "Bahsei's Mania",
		588 => "Stone-Talker's Oath",
		589 => "Perfected Saxhleel Champion",
		590 => "Perfected Sul-Xan's Torment",
		591 => "Perfected Bahsei's Mania",
		592 => "Perfected Stone-Talker's Oath",
		593 => "Gaze of Sithis",
		594 => "Harpooner's Wading Kilt",
		596 => "Death Dealer's Fete",
		597 => "Shapeshifter's Chain",
		598 => "Zoal the Ever-Wakeful",
		599 => "Immolator Charr",
		600 => "Glorgoloch the Destroyer",
		602 => "Crimson Oath's Rive",
		603 => "Scorion's Feast",
		604 => "Rush of Agony",
		605 => "Silver Rose Vigil",
		606 => "Thunder Caller",
		607 => "Grisly Gourmet",
		608 => "Prior Thierric",
		609 => "Magma Incarnate",
		610 => "Wretched Vitality",
		611 => "Deadlands Demolisher",
		612 => "Iron Flask",
		613 => "Eye of the Grasp",
		614 => "Hexos' Ward",
		615 => "Kynmarcher's Cruelty",
		616 => "Dark Convergence",
		617 => "Plaguebreak",
		618 => "Hrothgar's Chill",
		619 => "Maligalig's Maelstrom",
		620 => "Gryphon's Reprisal",
		621 => "Glacial Guardian",
		622 => "Turning Tide",
		623 => "Storm-Cursed's Revenge",
		624 => "Spriggan's Vigor",
		625 => "Markyn Ring of Majesty",
		626 => "Belharza's Band",
		627 => "Spaulder of Ruin",
		629 => "Rallying Cry",
		630 => "Hew and Sunder",
		631 => "Enervating Aura",
		632 => "Kargaeda",
		633 => "Nazaray",
		634 => "Nunatak",
		635 => "Lady Malygda",
		636 => "Baron Thirsk",
		640 => "Order's Wrath",
		641 => "Serpent's Disdain",
		642 => "Druid's Braid",
		643 => "Blessing of High Isle",
		644 => "Steadfast's Mettle",
		645 => "Systres' Scowl",
		646 => "Whorl of the Depths",
		647 => "Coral Riptide",
		648 => "Pearlescent Ward",
		649 => "Pillager's Profit",
		650 => "Perfected Pillager's Profit",
		651 => "Perfected Pearlescent Ward",
		652 => "Perfected Coral Riptide",
		653 => "Perfected Whorl of the Depths",
		654 => "Mora's Whispers",
		655 => "Dov-rha Sabatons",
		656 => "Lefthander's Aegis Belt",
		657 => "Sea-Serpent's Coil",
		658 => "Oakensoul Ring",
);

$ESO_ITEMEQUIPTYPE_TEXTS = array(
		-1 => "",
		0 => "",
		1 => "Head",
		2 => "Neck",
		3 => "Chest",
		4 => "Shoulders",
		5 => "One Hand",
		6 => "Two Hand",
		7 => "Off Hand",
		8 => "Waist",
		9 => "Legs",
		10 => "Feet",
		11 => "Costume",
		12 => "Ring",
		13 => "Hand",
		14 => "Main Hand",
		15 => "Poison",
);

$ESO_REACTION_TEXTS = array(
		-1 => "",
		0 => "None",
		1 => "Hostile",
		2 => "Neutral",
		3 => "Friendly",
		4 => "Player Ally",
		5 => "NPC Ally",
);

$ESO_FURNLIMITTYPE_RAWTEXTS = array(
		-1 => "",
		0 => "Low Impact Item",
		1 => "High Impact Item",
		2 => "Low Impact Collectible",
		3 => "High Impact Collectible",
);

$ESO_FURNLIMITTYPE_TEXTS = array(
		-1 => "",
		0 => "Traditional Furnishings",
		1 => "Special Furnishings",
		2 => "Collectible Furnishings",
		3 => "Special Collectibles",
);

$ESO_COLLECTIBLECATEGORYTYPE_TEXTS = array(
		-1 => "",
		0 => "Invalid",
		1 => "DLC",
		2 => "Mount",
		3 => "Vanity Pet",
		4 => "Costume",
		5 => "Trophy",
		6 => "Account Upgrade",
		7 => "Account Service",
		8 => "Assistant",
		9 => "Personality",
		10 => "Hat",
		11 => "Skin",
		12 => "Polymorph",
		13 => "Hair",
		14 => "Facial Hair Horns",
		15 => "Facial Accessory",
		16 => "Piercing Jewelry",
		17 => "Head Marking",
		18 => "Body Marking",
		19 => "House",
		20 => "Furniture",
		21 => "Emote",
		22 => "Chapter",
		23 => "Ability Skin",
		24 => "Outfit Style",
		25 => "House Bank",
		25 => "Combination Fragment",
);

$ESO_ITEMBINDTYPE_TEXTS = array(
		-1 => "",
		0 => "",
		1 => "Bind on Pickup",
		2 => "Bind on Equip",
		3 => "Backpack Bind on Pickup",
);

$ESO_QUEST_REPEATTYPE_TEXTS = array(
		-1 => "",
		0 => "Not Repeatable",
		1 => "Repeatable",
		2 => "Daily",
);


$ESO_QUEST_STEPTYPE_TEXTS = array(
		-1 => "",
		0 => "",
		1 => "And",
		2 => "Or",
		3 => "End",
		4 => "Branch",
);


$ESO_QUEST_STEPVISIBILITYTYPE_TEXTS = array(
		-1 => "",
		0 => "Hint",
		1 => "Optional",
		2 => "Hidden",
);


$ESO_QUESTTYPE_TEXTS = array(
		-1 => "",
		0 => "None",
		1 => "Group",
		2 => "Main Story",
		3 => "Guild",
		4 => "Crafting",
		5 => "Dungeon",
		6 => "Raid",
		7 => "PVP",
		8 => "Class",
		9 => "QA Test",
		10 => "PVP Group",
		11 => "PVP Grand",
		12 => "Holiday Event",
		13 => "Battleground",
		14 => "Prologue",
		15 => "Undaunted Pledge",
		16 => 'Companion',
);


$ESO_MAPPINTYPE_TEXTS = array(
		-1 => "",
		0 => "Player",
		1 => "Group",
		2 => "Group Leader",
		3 => "Quest Offer",
		4 => "Quest Offer Repeatable",
		5 => "Quest Offer Zone Story",
		6 => "Quest Complete",
		7 => "Quest Talk To",
		8 => "Quest Interact",
		9 => "Quest Give Item",
		10 => "Assisted Quest Condition",
		11 => "Assisted Quest Optional Condition",
		12 => "Assisted Quest Ending",
		13 => "Assisted Quest Repeatable Condition",
		14 => "Assisted Quest Repeatable Optional Condition",
		15 => "Assisted Quest Repeatable Ending",
		16 => "Assisted Quest Zone Story Condition",
		17 => "Assisted Quest Zone Story Optional Condition",
		18 => "Assisted Quest Zone Story Ending",
		19 => "Tracked Quest Condition",
		20 => "Tracked Quest Optional Condition",
		21 => "Tracked Quest Ending",
		22 => "Tracked Quest Repeatable Condition",
		23 => "Tracked Quest Repeatable Optional Condition",
		24 => "Tracked Quest Repeatable Ending",
		25 => "Tracked Quest Zone Story Condition",
		26 => "Tracked Quest Zone Story Optional Condition",
		27 => "Tracked Quest Zone Story Ending",
		28 => "Quest Condition",
		29 => "Quest Optional Condition",
		30 => "Quest Ending",
		31 => "Quest Repeatable Condition",
		32 => "Quest Repeatable Optional Condition",
		33 => "Quest Repeatable Ending",
		34 => "Quest Zone Story Condition",
		35 => "Quest Zone Story Optional Condition",
		36 => "Quest Zone Story Ending",
		37 => "Tracked Quest Offer Zone Story",
		38 => "Poi Suggested",
		39 => "Poi Seen",
		40 => "Poi Complete",
		41 => "Antiquity Dig Site",
		42 => "Tracked Antiquity Dig Site",
		43 => "Zone Story Suggested Area",
		44 => "Return Aldmeri Dominion",
		45 => "Return Ebonheart Pact",
		46 => "Return Daggerfall Covenant",
		47 => "Return Neutral",
		48 => "Ava Capture Area Neutral",
		49 => "Ava Capture Area Aldmeri",
		50 => "Ava Capture Area Ebonheart",
		51 => "Ava Capture Area Daggerfall",
		52 => "Bgpin Capture Area Neutral",
		53 => "Bgpin Capture Area Fire Drakes",
		54 => "Bgpin Capture Area Pit Daemons",
		55 => "Bgpin Capture Area Storm Lords",
		56 => "Bgpin Capture Area A Neutral",
		56 => "Bgpin Multi Capture Area A Neutral",
		57 => "Bgpin Capture Area A Fire Drakes",
		57 => "Bgpin Multi Capture Area A Fire Drakes",
		58 => "Bgpin Capture Area A Pit Daemons",
		58 => "Bgpin Multi Capture Area A Pit Daemons",
		59 => "Bgpin Capture Area A Storm Lords",
		59 => "Bgpin Multi Capture Area A Storm Lords",
		60 => "Bgpin Capture Area B Neutral",
		60 => "Bgpin Multi Capture Area B Neutral",
		61 => "Bgpin Capture Area B Fire Drakes",
		61 => "Bgpin Multi Capture Area B Fire Drakes",
		62 => "Bgpin Capture Area B Pit Daemons",
		62 => "Bgpin Multi Capture Area B Pit Daemons",
		63 => "Bgpin Capture Area B Storm Lords",
		63 => "Bgpin Multi Capture Area B Storm Lords",
		64 => "Bgpin Capture Area C Neutral",
		64 => "Bgpin Multi Capture Area C Neutral",
		65 => "Bgpin Capture Area C Fire Drakes",
		65 => "Bgpin Multi Capture Area C Fire Drakes",
		66 => "Bgpin Capture Area C Pit Daemons",
		66 => "Bgpin Multi Capture Area C Pit Daemons",
		67 => "Bgpin Capture Area C Storm Lords",
		67 => "Bgpin Multi Capture Area C Storm Lords",
		68 => "Bgpin Capture Area D Neutral",
		68 => "Bgpin Multi Capture Area D Neutral",
		69 => "Bgpin Capture Area D Fire Drakes",
		69 => "Bgpin Multi Capture Area D Fire Drakes",
		70 => "Bgpin Capture Area D Pit Daemons",
		70 => "Bgpin Multi Capture Area D Pit Daemons",
		71 => "Bgpin Capture Area D Storm Lords",
		71 => "Bgpin Multi Capture Area D Storm Lords",
		72 => "Bgpin Mobile Capture Area Neutral",
		73 => "Bgpin Mobile Capture Area Fire Drakes",
		74 => "Bgpin Mobile Capture Area Pit Daemons",
		75 => "Bgpin Mobile Capture Area Storm Lords",
		76 => "Bgpin Mobile Capture Area A Neutral",
		77 => "Bgpin Mobile Capture Area A Fire Drakes",
		78 => "Bgpin Mobile Capture Area A Pit Daemons",
		79 => "Bgpin Mobile Capture Area A Storm Lords",
		80 => "Bgpin Mobile Capture Area B Neutral",
		81 => "Bgpin Mobile Capture Area B Fire Drakes",
		82 => "Bgpin Mobile Capture Area B Pit Daemons",
		83 => "Bgpin Mobile Capture Area B Storm Lords",
		84 => "Bgpin Mobile Capture Area C Neutral",
		85 => "Bgpin Mobile Capture Area C Fire Drakes",
		86 => "Bgpin Mobile Capture Area C Pit Daemons",
		87 => "Bgpin Mobile Capture Area C Storm Lords",
		88 => "Bgpin Mobile Capture Area D Neutral",
		89 => "Bgpin Mobile Capture Area D Fire Drakes",
		90 => "Bgpin Mobile Capture Area D Pit Daemons",
		91 => "Bgpin Mobile Capture Area D Storm Lords",
		92 => "Bgpin Flag Neutral",
		93 => "Bgpin Flag Fire Drakes",
		94 => "Bgpin Flag Pit Daemons",
		95 => "Bgpin Flag Storm Lords",
		96 => "Bgpin Flag Spawn Neutral",
		97 => "Bgpin Flag Spawn Fire Drakes",
		98 => "Bgpin Flag Spawn Pit Daemons",
		99 => "Bgpin Flag Spawn Storm Lords",
		100 => "Bgpin Flag Return Fire Drakes",
		101 => "Bgpin Flag Return Pit Daemons",
		102 => "Bgpin Flag Return Storm Lords",
		103 => "Bgpin Murderball Spawn Neutral",
		104 => "Bgpin Murderball Neutral",
		105 => "Bgpin Murderball Fire Drakes",
		106 => "Bgpin Murderball Pit Daemons",
		107 => "Bgpin Murderball Storm Lords",
		108 => "Ava Capture Area Aura",
		109 => "Bgpin Capture Area Aura",
		110 => "Bgpin Mobile Capture Area Aura",
		111 => "Bgpin Flag Neutral Aura",
		112 => "Bgpin Flag Fire Drakes Aura",
		113 => "Bgpin Flag Pit Daemons Aura",
		114 => "Bgpin Flag Storm Lords Aura",
		115 => "Ava Daedric Artifact Volendrung Neutral",
		116 => "Ava Daedric Artifact Volendrung Aldmeri",
		117 => "Ava Daedric Artifact Volendrung Ebonheart",
		118 => "Ava Daedric Artifact Volendrung Daggerfall",
		119 => "Artifact Aldmeri Offensive",
		120 => "Artifact Aldmeri Defensive",
		121 => "Artifact Ebonheart Offensive",
		122 => "Artifact Ebonheart Defensive",
		123 => "Artifact Daggerfall Offensive",
		124 => "Artifact Daggerfall Defensive",
		125 => "Artifact Return Aldmeri",
		126 => "Artifact Return Daggerfall",
		127 => "Artifact Return Ebonheart",
		128 => "Keep Neutral",
		129 => "Keep Aldmeri Dominion",
		130 => "Keep Ebonheart Pact",
		131 => "Keep Daggerfall Covenant",
		132 => "Outpost Neutral",
		133 => "Outpost Aldmeri Dominion",
		134 => "Outpost Ebonheart Pact",
		135 => "Outpost Daggerfall Covenant",
		136 => "Farm Neutral",
		137 => "Farm Aldmeri Dominion",
		138 => "Farm Ebonheart Pact",
		139 => "Farm Daggerfall Covenant",
		140 => "Mine Neutral",
		141 => "Mine Aldmeri Dominion",
		142 => "Mine Ebonheart Pact",
		143 => "Mine Daggerfall Covenant",
		144 => "Mill Neutral",
		145 => "Mill Aldmeri Dominion",
		146 => "Mill Ebonheart Pact",
		147 => "Mill Daggerfall Covenant",
		148 => "Border Keep Aldmeri Dominion",
		149 => "Border Keep Ebonheart Pact",
		150 => "Border Keep Daggerfall Covenant",
		151 => "Artifact Keep Aldmeri Dominion",
		152 => "Artifact Keep Ebonheart Pact",
		153 => "Artifact Keep Daggerfall Covenant",
		154 => "Artifact Gate Open Aldmeri Dominion",
		155 => "Artifact Gate Open Ebonheart Pact",
		156 => "Artifact Gate Open Daggerfall Covenant",
		157 => "Artifact Gate Closed Aldmeri Dominion",
		158 => "Artifact Gate Closed Ebonheart Pact",
		159 => "Artifact Gate Closed Daggerfall Covenant",
		160 => "Imperial District Neutral",
		161 => "Imperial District Aldmeri Dominion",
		162 => "Imperial District Ebonheart Pact",
		163 => "Imperial District Daggerfall Covenant",
		164 => "Ava Town Neutral",
		165 => "Ava Town Aldmeri Dominion",
		166 => "Ava Town Ebonheart Pact",
		167 => "Ava Town Daggerfall Covenant",
		168 => "Keep Bridge",
		169 => "Keep Bridge Impassable",
		170 => "Keep Milegate",
		171 => "Keep Milegate Center Destroyed",
		172 => "Keep Milegate Impassable",
		173 => "Keep Attacked Large",
		174 => "Keep Attacked Small",
		175 => "Location",
		176 => "Harvest Node",
		177 => "Vendor",
		178 => "Trainer",
		179 => "Npc Follower",
		180 => "Ping",
		181 => "Rally Point",
		182 => "Player Waypoint",
		183 => "Auto Map Navigation Ping",
		184 => "Quest Ping",
		185 => "Antiquity Dig Site Ping",
		186 => "Tri Battle Small",
		187 => "Tri Battle Medium",
		188 => "Tri Battle Large",
		189 => "Aldmeri vs Ebonheart Small",
		190 => "Aldmeri vs Ebonheart Medium",
		191 => "Aldmeri vs Ebonheart Large",
		192 => "Aldmeri vs Daggerfall Small",
		193 => "Aldmeri vs Daggerfall Medium",
		194 => "Aldmeri vs Daggerfall Large",
		195 => "Ebonheart vs Daggerfall Small",
		196 => "Ebonheart vs Daggerfall Medium",
		197 => "Ebonheart vs Daggerfall Large",
		198 => "Fast Travel Keep Accessible",
		199 => "Fast Travel Border Keep Accessible",
		200 => "Fast Travel Outpost Accessible",
		201 => "Fast Travel Wayshrine",
		202 => "Fast Travel Wayshrine Current Loc",
		203 => "Forward Camp Aldmeri Dominion",
		204 => "Forward Camp Ebonheart Pact",
		205 => "Forward Camp Daggerfall Covenant",
		206 => "Forward Camp Accessible",
		207 => "Keep Graveyard Accessible",
		208 => "Respawn Border Keep Accessible",
		209 => "Imperial District Graveyard Accessible",
		210 => "Ava Town Graveyard Accessible",
		211 => "Outpost Graveyard Accessible",
		212 => "Restricted Link Aldmeri Dominion",
		213 => "Restricted Link Ebonheart Pact",
		214 => "Restricted Link Daggerfall Covenant",
		215 => "Aggro",
		216 => "Timely Escape Npc",
		217 => "Dark Brotherhood Target",
		218 => "Dragon Idle Healthy",
		219 => "Dragon Idle Weak",
		220 => "Dragon Combat Healthy",
		221 => "Dragon Combat Weak",
		222 => "World Event Poi Active",
		223 => "Player Camera",
		224 => "Count",
		225 => "Invalid",
);


$ESO_CHESTTYPE_TEXTS = array(
		-1 => "",
		0 => "None",
		1 => "Simple",
		2 => "Intermediate",
		3 => "Advanced",
		4 => "Master",
		5 => "Impossible",
		6 => "Unknown (6)",
		7 => "Trivial",
);


$ESO_REWARDENTRYTYPE_TEXTS = array(
		-1 => "",
		0 => "Mail Item",
		1 => "Add Currency",
		2 => "Add Title",
		3 => "Remove Title",
		4 => "Advance Achevement",
		5 => "Add Skill Line",
		6 => "Remove Skill Line",
		7 => "Add Effect",
		8 => "Remove Effect",
		9 => "Collectible",
		10 => "Loot Crate",
		11 => "Deprecated1",
		12 => "Item",
		13 => "Choice",
		14 => "Instant Unlock",
		15 => "Reward List",
		16 => "Experience",
);


$ESO_QUESTREWARDTYPE_TEXTS = array(
		-2 => "",
		-1 => "Experience",	//Custom
		0 => "None",
		1 => "Money",
		2 => "Alliance Points",
		3 => "Writ Vouchers",
		4 => "Telvar Stones",
		5 => "Inspiration",
		6 => "Unused",
		7 => "Auto Item",
		8 => "Unused3",
		9 => "Partial Skill Points",
		10 => "Skill Line",
		11 => "Event Tickets",
		12 => "Style Stones",
);


$ESO_QUESTREWARDITEMTYPE_TEXTS = array(
		-1 => "",
		0 => "Item",
		1 => "Collectible",
);


$ESO_TIMEDACTIVITYTYPE_TEXTS = array(
		-1 => "",
		0 => "Daily",
		1 => "Weekly",
);


$ESO_CURRENCYTYPE_TEXTS = array(
		-1 => '',
		0 => 'None',
		1 => 'Gold',
		2 => 'Alliance Points',
		3 => 'Telvar Stones',
		4 => '4',
		5 => 'Chaotic Creatia',
		6 => 'Crown Gems',
		7 => 'Crowns',
		8 => 'Style Stones',
		9 => 'Event Tickets',
		10 => 'Undaunted Keys',
		11 => 'Seals of Endeavor',
);


$ESO_CURRENCYTYPESHORT_TEXTS = array(
		-1 => '',
		0 => '',
		1 => 'GP',
		2 => 'AP',
		3 => 'Telvar',
		4 => '4',
		5 => 'CC',
		6 => 'Gems',
		7 => 'Crowns',
		8 => 'Stones',
		9 => 'Tickets',
		10 => 'Keys',
		11 => 'Seals',
);


$ESO_CURRENCYCHANGEREASON_TEXTS = array(
		-1 => "",
		0 => "Loot",
		1 => "Vendor",
		2 => "Mail",
		3 => "Trade",
		4 => "Questreward",
		5 => "Conversation",
		6 => "Action",
		7 => "Command",
		8 => "Bagspace",
		9 => "Bankspace",
		10 => "Soulweary",
		12 => "Battleground",
		13 => "Kill",
		14 => "Keep Reward",
		15 => "Keep Upgrade",
		16 => "Deconstruct",
		18 => "Soul Heal",
		19 => "Travel Graveyard",
		20 => "Cash On Delivery",
		21 => "Medal",
		22 => "Ability Upgrade Purchase",
		23 => "Hookpoint Store",
		24 => "Craft",
		25 => "Stablespace",
		26 => "Achievement",
		27 => "Reward",
		28 => "Feed Mount",
		29 => "Vendor Repair",
		30 => "Trait Reveal",
		31 => "Tradinghouse Purchase",
		32 => "Tradinghouse Refund",
		33 => "Tradinghouse Listing",
		34 => "Reforge",
		35 => "Player Init",
		36 => "Recipe",
		37 => "Consume Food Drink",
		38 => "Consume Potion",
		39 => "Harvest Reagent",
		40 => "Keep Repair",
		41 => "Pvp Resurrect",
		42 => "Bank Deposit",
		43 => "Bank Withdrawal",
		44 => "Respec Skills",
		45 => "Respec Attributes",
		46 => "Research Trait",
		47 => "Bounty Paid Guard",
		48 => "Stuck",
		49 => "Edit Guild Heraldry",
		50 => "Guild Tabard",
		51 => "Guild Bank Deposit",
		52 => "Guild Bank Withdrawal",
		53 => "Guild Standard",
		54 => "Jump Failure Refund",
		55 => "Respec Morphs",
		56 => "Bounty Paid Fence",
		57 => "Bounty Confiscated",
		58 => "Guild Forward Camp",
		59 => "Pickpocket",
		60 => "Vendor Launder",
		61 => "Respec Champion",
		62 => "Loot Stolen",
		63 => "Sell Stolen",
		64 => "Buyback",
		65 => "Pvp Kill Transfer",
		66 => "Bank Fee",
		67 => "Death",
		68 => "Unknown",
		69 => "Crown Crate Duplicate",
		70 => "Converted to Gems",
		71 => "Purchased With Gems",
		72 => "Purchased With Crowns",
		73 => "Crowns Purchased",
		74 => "Offensive Keep Reward",
		75 => "Defensive Keep Reward",
		76 => "Loot Currency Container",
		77 => "Character Upgrade",
		78 => "Reconstruction",
);


$ESO_BUFFTYPE_TEXTS = array(
		-1 => "",
		0 => "",
		1 => "Minor Brutality",
		2 => "Major Brutality",
		3 => "Minor Savagery",
		4 => "Major Savagery",
		5 => "Minor Sorcery",
		6 => "Major Sorcery",
		7 => "Minor Prophecy",
		8 => "Major Prophecy",
		9 => "Minor Resolve",
		10 => "Major Resolve",
		11 => "Minor Ward",
		12 => "Major Ward",
		13 => "Minor Fortitude",
		14 => "Major Fortitude",
		15 => "Minor Endurance",
		16 => "Major Endurance",
		17 => "Minor Intellect",
		18 => "Major Intellect",
		19 => "Minor Heroism",
		20 => "Major Heroism",
		21 => "Minor Mending",
		22 => "Major Mending",
		23 => "Minor Vitality",
		24 => "Major Vitality",
		25 => "Minor Evasion",
		26 => "Major Evasion",
		27 => "Minor Protection",
		28 => "Major Protection",
		29 => "Minor Maim",
		30 => "Major Maim",
		31 => "Minor Defile",
		32 => "Major Defile",
		33 => "Minor Mangle",
		34 => "Major Mangle",
		35 => "Minor Expedition",
		36 => "Major Expedition",
		37 => "Empower",
		38 => "Minor Fracture",
		39 => "Major Fracture",
		40 => "Minor Spell Shatter",
		41 => "Major Spell Shatter",
		42 => "Minor Berserk",
		43 => "Major Berserk",
		44 => "Minor Force",
		45 => "Major Force",
		46 => "Minor Erosion",
		47 => "Major Erosion",
		48 => "Minor Courage",
		49 => "Major Courage",
		50 => "Minor Toughness",
		51 => "Minor Aegis",
		52 => "Major Aegis",
		53 => "Minor Gallop",
		54 => "Major Gallop",
		55 => "Minor Enervation",
		56 => "Minor Uncertainty",
		57 => "Minor Lifesteal",
		58 => "Minor Magickasteal",
		59 => "Increase Ult Cost (Deprecated)",
		60 => "Minor Vulnerability",
		61 => "Major Vulnerability",
		62 => "Minor Timidity",
		63 => "Major Timidity",
);


$ESO_ITEMINTTYPE_QUALITYMAP = array(
		0 => 1,
		1 => 0,
		2 => 1,
		3 => 2,
		4 => 3,
		5 => 4,
		6 => 5,
		7 => 3,
		8 => 4,
		9 => 2,
		10 => 2,
		11 => 3,
		12 => 5,
		13 => 5,
		14 => 5,
		15 => 5,
		16 => 5,
		17 => 5,
		18 => 2,
		19 => 2,
		20 => 1,
		21 => 2,
		22 => 3,
		23 => 4,
		24 => 5,
		25 => 1,
		26 => 2,
		27 => 3,
		28 => 4,
		29 => 5,
		30 => 1,
		31 => 2,
		32 => 3,
		33 => 4,
		34 => 5,
		35 => 1,
		36 => 1,
		37 => 1,
		38 => 1,
		39 => 2,
		40 => 2,
		41 => 2,
		42 => 2,
		43 => 2,
		44 => 2,
		45 => 2,
		46 => 2,
		47 => 2,
		48 => 2,
		49 => 3,
		50 => 3,
		51 => 2,
		52 => 2,
		53 => 2,
		54 => 2,
		55 => 2,
		56 => 2,
		57 => 2,
		58 => 2,
		59 => 2,
		60 => 2,
		61 => 3,
		62 => 3,
		63 => 3,
		64 => 3,
		65 => 3,
		66 => 3,
		67 => 3,
		68 => 3,
		69 => 3,
		70 => 3,
		71 => 4,
		72 => 4,
		73 => 4,
		74 => 4,
		75 => 4,
		76 => 4,
		77 => 4,
		78 => 4,
		79 => 4,
		80 => 4,
		81 => 3,
		82 => 3,
		83 => 3,
		84 => 3,
		85 => 3,
		86 => 3,
		87 => 3,
		88 => 3,
		89 => 3,
		90 => 3,
		91 => 4,
		92 => 4,
		93 => 4,
		94 => 4,
		95 => 4,
		96 => 4,
		97 => 4,
		98 => 4,
		99 => 4,
		100 => 4,
		101 => 5,
		102 => 5,
		103 => 5,
		104 => 5,
		105 => 5,
		106 => 5,
		107 => 5,
		108 => 5,
		109 => 5,
		110 => 5,
		111 => 1,
		112 => 1,
		113 => 1,
		114 => 1,
		115 => 1,
		116 => 1,
		117 => 1,
		118 => 1,
		119 => 1,
		120 => 1,
		121 => 2,
		122 => 3,
		123 => 4,
		124 => 5,
		125 => 1,
		126 => 1,
		127 => 1,
		128 => 1,
		129 => 1,
		130 => 1,
		131 => 1,
		132 => 1,
		133 => 1,
		134 => 1,
		135 => 2,
		136 => 2,
		137 => 2,
		138 => 2,
		139 => 2,
		140 => 2,
		141 => 2,
		142 => 2,
		143 => 2,
		144 => 2,
		145 => 3,
		146 => 3,
		147 => 3,
		148 => 3,
		149 => 3,
		150 => 3,
		151 => 3,
		152 => 3,
		153 => 3,
		154 => 3,
		155 => 4,
		156 => 4,
		157 => 4,
		158 => 4,
		159 => 4,
		160 => 4,
		161 => 4,
		162 => 4,
		163 => 4,
		164 => 4,
		165 => 5,
		166 => 5,
		167 => 5,
		168 => 5,
		169 => 5,
		170 => 5,
		171 => 5,
		172 => 5,
		173 => 5,
		174 => 5,
		175 => 1,
		176 => 1,
		177 => 0,
		178 => 1,
		179 => 1,
		180 => 1,
		181 => 1,
		182 => 1,
		183 => 1,
		184 => 1,
		185 => 1,
		186 => 1,
		187 => 1,
		188 => 2,
		189 => 2,
		190 => 2,
		191 => 2,
		192 => 2,
		193 => 2,
		194 => 2,
		195 => 2,
		196 => 2,
		197 => 2,
		198 => 3,
		199 => 3,
		200 => 3,
		201 => 3,
		202 => 3,
		203 => 3,
		204 => 3,
		205 => 3,
		206 => 3,
		207 => 3,
		208 => 4,
		209 => 4,
		210 => 4,
		211 => 4,
		212 => 4,
		213 => 4,
		214 => 4,
		215 => 4,
		216 => 4,
		217 => 4,
		218 => 5,
		219 => 5,
		220 => 5,
		221 => 5,
		222 => 5,
		223 => 5,
		224 => 5,
		225 => 5,
		226 => 5,
		227 => 5,
		228 => 2,
		229 => 2,
		230 => 3,
		231 => 4,
		232 => 3,
		233 => 4,
		234 => 5,
		235 => 1,
		236 => 1,
		237 => 2,
		238 => 3,
		239 => 4,
		240 => 5,
		241 => 1,
		242 => 2,
		243 => 3,
		244 => 4,
		245 => 5,
		246 => 2,
		247 => 2,
		248 => 3,
		249 => 4,
		250 => 3,
		251 => 4,
		252 => 5,
		253 => 1,
		254 => 1,
		255 => 2,
		256 => 3,
		257 => 4,
		258 => 5,
		259 => 1,
		260 => 2,
		261 => 3,
		262 => 4,
		263 => 5,
		264 => 2,
		265 => 2,
		266 => 3,
		267 => 4,
		268 => 3,
		269 => 4,
		270 => 5,
		271 => 1,
		272 => 1,
		273 => 2,
		274 => 3,
		275 => 4,
		276 => 5,
		277 => 1,
		278 => 2,
		279 => 3,
		280 => 4,
		281 => 5,
		282 => 2,
		283 => 2,
		284 => 3,
		285 => 4,
		286 => 3,
		287 => 4,
		288 => 5,
		289 => 1,
		290 => 1,
		291 => 2,
		292 => 3,
		293 => 4,
		294 => 5,
		295 => 1,
		296 => 2,
		297 => 3,
		298 => 4,
		299 => 5,
		300 => 2,
		301 => 2,
		302 => 3,
		303 => 4,
		304 => 3,
		305 => 4,
		306 => 5,
		307 => 1,
		308 => 1,
		309 => 2,
		310 => 3,
		311 => 4,
		312 => 5,
		313 => 1,
		314 => 2,
		315 => 3,
		316 => 4,
		317 => 5,
		318 => 0,
		319 => 1,
		320 => 2,
		321 => 3,
		322 => 4,
		323 => 5,
		324 => 1,
		325 => 1,
		326 => 1,
		327 => 1,
		328 => 1,
		329 => 1,
		330 => 1,
		331 => 1,
		332 => 1,
		333 => 1,
		334 => 1,
		335 => 1,
		336 => 1,
		337 => 1,
		338 => 1,
		339 => 2,
		340 => 2,
		341 => 2,
		342 => 2,
		343 => 2,
		344 => 2,
		345 => 2,
		346 => 2,
		347 => 2,
		348 => 2,
		349 => 2,
		350 => 2,
		351 => 2,
		352 => 2,
		353 => 2,
		354 => 4,
		355 => 5,
		356 => 1,
		357 => 1,
		358 => 2,
		359 => 2,
		360 => 3,
		361 => 4,
		362 => 3,
		363 => 4,
		364 => 5,
		365 => 1,
		366 => 1,
		367 => 2,
		368 => 3,
		369 => 4,
		370 => 5,
		371 => 1,
		372 => 2,
		373 => 3,
		374 => 4,
		375 => 5,
		376 => 1,
		377 => 2,
		378 => 1,
		379 => 1,
		380 => 2,
		381 => 2,
		382 => 3,
		383 => 4,
		384 => 3,
		385 => 4,
		386 => 5,
		387 => 1,
		388 => 1,
		389 => 2,
		390 => 3,
		391 => 4,
		392 => 5,
		393 => 1,
		394 => 2,
		395 => 3,
		396 => 4,
		397 => 5,
		398 => 1,
		399 => 2,
		400 => 2,
);

$ESO_ITEMINTTYPE_LEVELMAP = array(
		0 => 1,
		1 => 1,
		2 => 1,
		3 => 1,
		4 => 1,
		5 => 1,
		6 => 1,
		7 => 1,
		8 => 1,
		9 => 1,
		10 => 1,
		11 => 1,
		12 => 1,
		13 => 1,
		14 => 1,
		15 => 1,
		16 => 1,
		17 => 1,
		18 => 1,
		19 => 1,
		20 => 1,
		21 => 1,
		22 => 1,
		23 => 1,
		24 => 1,
		25 => 1,
		26 => 1,
		27 => 1,
		28 => 1,
		29 => 1,
		30 => 1,
		31 => 1,
		32 => 1,
		33 => 1,
		34 => 1,
		35 => 1,
		36 => 1,
		37 => 1,
		38 => 1,
		39 => 51,
		40 => 52,
		41 => 53,
		42 => 54,
		43 => 55,
		44 => 56,
		45 => 57,
		46 => 58,
		47 => 59,
		48 => 60,
		49 => 1,
		50 => 1,
		51 => 51,
		52 => 52,
		53 => 53,
		54 => 54,
		55 => 55,
		56 => 56,
		57 => 57,
		58 => 58,
		59 => 59,
		60 => 60,
		61 => 51,
		62 => 52,
		63 => 53,
		64 => 54,
		65 => 55,
		66 => 56,
		67 => 57,
		68 => 58,
		69 => 59,
		70 => 60,
		71 => 51,
		72 => 52,
		73 => 53,
		74 => 54,
		75 => 55,
		76 => 56,
		77 => 57,
		78 => 58,
		79 => 59,
		80 => 60,
		81 => 51,
		82 => 52,
		83 => 53,
		84 => 54,
		85 => 55,
		86 => 56,
		87 => 57,
		88 => 58,
		89 => 59,
		90 => 60,
		91 => 51,
		92 => 52,
		93 => 53,
		94 => 54,
		95 => 55,
		96 => 56,
		97 => 57,
		98 => 58,
		99 => 59,
		100 => 60,
		101 => 51,
		102 => 52,
		103 => 53,
		104 => 54,
		105 => 55,
		106 => 56,
		107 => 57,
		108 => 58,
		109 => 59,
		110 => 60,
		111 => 51,
		112 => 52,
		113 => 53,
		114 => 54,
		115 => 55,
		116 => 56,
		117 => 57,
		118 => 58,
		119 => 59,
		120 => 60,
		121 => 1,
		122 => 1,
		123 => 1,
		124 => 1,
		125 => 51,
		126 => 52,
		127 => 53,
		128 => 54,
		129 => 55,
		130 => 56,
		131 => 57,
		132 => 58,
		133 => 59,
		134 => 60,
		135 => 51,
		136 => 52,
		137 => 53,
		138 => 54,
		139 => 55,
		140 => 56,
		141 => 57,
		142 => 58,
		143 => 59,
		144 => 60,
		145 => 51,
		146 => 52,
		147 => 53,
		148 => 54,
		149 => 55,
		150 => 56,
		151 => 57,
		152 => 58,
		153 => 59,
		154 => 60,
		155 => 51,
		156 => 52,
		157 => 53,
		158 => 54,
		159 => 55,
		160 => 56,
		161 => 57,
		162 => 58,
		163 => 59,
		164 => 60,
		165 => 51,
		166 => 52,
		167 => 53,
		168 => 54,
		169 => 55,
		170 => 56,
		171 => 57,
		172 => 58,
		173 => 59,
		174 => 60,
		175 => 1,
		176 => 1,
		177 => 1,
		178 => 51,
		179 => 52,
		180 => 53,
		181 => 54,
		182 => 55,
		183 => 56,
		184 => 57,
		185 => 58,
		186 => 59,
		187 => 60,
		188 => 51,
		189 => 52,
		190 => 53,
		191 => 54,
		192 => 55,
		193 => 56,
		194 => 57,
		195 => 58,
		196 => 59,
		197 => 60,
		198 => 51,
		199 => 52,
		200 => 53,
		201 => 54,
		202 => 55,
		203 => 56,
		204 => 57,
		205 => 58,
		206 => 59,
		207 => 60,
		208 => 51,
		209 => 52,
		210 => 53,
		211 => 54,
		212 => 55,
		213 => 56,
		214 => 57,
		215 => 58,
		216 => 59,
		217 => 60,
		218 => 51,
		219 => 52,
		220 => 53,
		221 => 54,
		222 => 55,
		223 => 56,
		224 => 57,
		225 => 58,
		226 => 59,
		227 => 60,
		228 => 61,
		229 => 61,
		230 => 61,
		231 => 61,
		232 => 61,
		233 => 61,
		234 => 61,
		235 => 61,
		236 => 61,
		237 => 61,
		238 => 61,
		239 => 61,
		240 => 61,
		241 => 61,
		242 => 61,
		243 => 61,
		244 => 61,
		245 => 61,
		246 => 62,
		247 => 62,
		248 => 62,
		249 => 62,
		250 => 62,
		251 => 62,
		252 => 62,
		253 => 62,
		254 => 62,
		255 => 62,
		256 => 62,
		257 => 62,
		258 => 62,
		259 => 62,
		260 => 62,
		261 => 62,
		262 => 62,
		263 => 62,
		264 => 63,
		265 => 63,
		266 => 63,
		267 => 63,
		268 => 63,
		269 => 63,
		270 => 63,
		271 => 63,
		272 => 63,
		273 => 63,
		274 => 63,
		275 => 63,
		276 => 63,
		277 => 63,
		278 => 63,
		279 => 63,
		280 => 63,
		281 => 63,
		282 => 64,
		283 => 64,
		284 => 64,
		285 => 64,
		286 => 64,
		287 => 64,
		288 => 64,
		289 => 64,
		290 => 64,
		291 => 64,
		292 => 64,
		293 => 64,
		294 => 64,
		295 => 64,
		296 => 64,
		297 => 64,
		298 => 64,
		299 => 64,
		300 => 65,
		301 => 65,
		302 => 65,
		303 => 65,
		304 => 65,
		305 => 65,
		306 => 65,
		307 => 65,
		308 => 65,
		309 => 65,
		310 => 65,
		311 => 65,
		312 => 65,
		313 => 65,
		314 => 65,
		315 => 65,
		316 => 65,
		317 => 65,
		318 => 1,
		319 => 1,
		320 => 1,
		321 => 1,
		322 => 1,
		323 => 1,
		324 => 51,
		325 => 52,
		326 => 53,
		327 => 54,
		328 => 55,
		329 => 56,
		330 => 57,
		331 => 58,
		332 => 59,
		333 => 60,
		334 => 61,
		335 => 62,
		336 => 63,
		337 => 64,
		338 => 65,
		339 => 51,
		340 => 52,
		341 => 53,
		342 => 54,
		343 => 55,
		344 => 56,
		345 => 57,
		346 => 58,
		347 => 59,
		348 => 60,
		349 => 61,
		350 => 62,
		351 => 63,
		352 => 64,
		353 => 65,
		354 => 62,
		355 => 62,
		356 => 1,
		357 => 1,
		358 => 66,
		359 => 66,
		360 => 66,
		361 => 66,
		362 => 66,
		363 => 66,
		364 => 66,
		365 => 66,
		366 => 66,
		367 => 66,
		368 => 66,
		369 => 66,
		370 => 66,
		371 => 66,
		372 => 66,
		373 => 66,
		374 => 66,
		375 => 66,
		376 => 66,
		377 => 66,
		378 => 1,
		379 => 1,
		380 => 67,
		381 => 67,
		382 => 67,
		383 => 67,
		384 => 67,
		385 => 67,
		386 => 67,
		387 => 67,
		388 => 67,
		389 => 67,
		390 => 67,
		391 => 67,
		392 => 67,
		393 => 67,
		394 => 67,
		395 => 67,
		396 => 67,
		397 => 67,
		398 => 67,
		399 => 67,
		400 => 68,
);

$ESO_ITEMQUALITYLEVEL_INTTYPEMAP = array(
	 1 => array(1,  30,  31,  32,  33,  34),
	 4 => array(1,  25,  26,  27,  28,  29),
	 6 => array(1,  20,  21,  22,  23,  24),
	51 => array(1, 125, 135, 145, 155, 165),
	52 => array(1, 126, 136, 146, 156, 166),
	53 => array(1, 127, 137, 147, 157, 167),
	54 => array(1, 128,	138, 148, 158, 168),
	55 => array(1, 129, 139, 149, 159, 169),
	56 => array(1, 130, 140, 150, 160, 170),
	57 => array(1, 131, 141, 151, 161, 171),
	58 => array(1, 132, 142, 152, 162, 172),
	59 => array(1, 133, 143, 153, 163, 173),
	60 => array(1, 134, 144, 154, 164, 174),
	61 => array(1, 236, 237, 238, 239, 240),
	62 => array(1, 254, 255, 256, 257, 258),
	63 => array(1, 272, 273, 274, 275, 276),
	64 => array(1, 290, 291, 292, 293, 294),
	65 => array(1, 308,	309, 310, 311, 312),
	66 => array(1, 366, 367, 368, 369, 370),
);			//	1  360  361  362  363  364  // Old Jewelry


$ESO_DESTRUCTION_SKILLS = array(
		 	
			/* Blockade */
		39011 => array(
				"flame" => 39012, 
				"shock" => 39018, 
				"frost" => 39028,
				"flameName" => "Blockade of Fire",
				"shockName" => "Blockade of Storms",
				"frostName" => "Blockade of Frost",
		),
		
		41738 => array(
				"flame" => 41739, 
				"shock" => 41748, 
				"frost" => 41743,
				"flameName" => "Blockade of Fire",
				"shockName" => "Blockade of Storms",
				"frostName" => "Blockade of Frost",
		),
		41754 => array(
				"flame" => 41755, 
				"shock" => 41757, 
				"frost" => 41756,
				"flameName" => "Blockade of Fire",
				"shockName" => "Blockade of Storms",
				"frostName" => "Blockade of Frost",
		),
		41769 => array(
				"flame" => 41770,
				"shock" => 41772, 
				"frost" => 41771,
				"flameName" => "Blockade of Fire",
				"shockName" => "Blockade of Storms",
				"frostName" => "Blockade of Frost",
		),
		
			/* Destructive Touch */
		29091 => array(
				"flame" => 29073,
				"shock" => 29089,
				"frost" => 29078,
				"flameName" => "Flame Touch",
				"shockName" => "Shock Touch",
				"frostName" => "Frost Touch",
		),
		40947 => array(
				"flame" => 40948,
				"shock" => 40953,
				"frost" => 40950,
				"flameName" => "Flame Touch",
				"shockName" => "Shock Touch",
				"frostName" => "Frost Touch",
		),
		40956 => array(
				"flame" => 40957,
				"shock" => 40962,
				"frost" => 40959,
				"flameName" => "Flame Touch",
				"shockName" => "Shock Touch",
				"frostName" => "Frost Touch",
		),
		40964 => array(
				"flame" => 40965,
				"shock" => 40970,
				"frost" => 40967,
				"flameName" => "Flame Touch",
				"shockName" => "Shock Touch",
				"frostName" => "Frost Touch",
		),
		
			/* Destructive Reach */
		38937 => array(
				"flame" => 38944,
				"shock" => 38978,
				"frost" => 38970,
				"flameName" => "Flame Reach",
				"shockName" => "Shock Reach",
				"frostName" => "Frost Reach",
		),
		41029 => array(
				"flame" => 41030,
				"shock" => 41036,
				"frost" => 41033,
				"flameName" => "Flame Reach",
				"shockName" => "Shock Reach",
				"frostName" => "Frost Reach",
		),
		41038 => array(
				"flame" => 41039,
				"shock" => 41045,
				"frost" => 41042,
				"flameName" => "Flame Reach",
				"shockName" => "Shock Reach",
				"frostName" => "Frost Reach",
		),
		41047 => array(
				"flame" => 41048,
				"shock" => 41054,
				"frost" => 41051,
				"flameName" => "Flame Reach",
				"shockName" => "Shock Reach",
				"frostName" => "Frost Reach",
		),
		
			/* Destructive Clench */
		38984 => array(
				"flame" => 38985,
				"shock" => 38993,
				"frost" => 38989,
				"flameName" => "Flame Clench",
				"shockName" => "Shock Clench",
				"frostName" => "Frost Clench",
		),
		40977 => array(
				"flame" => 40984,
				"shock" => 40991,
				"frost" => 40988,
				"flameName" => "Flame Clench",
				"shockName" => "Shock Clench",
				"frostName" => "Frost Clench",
		),
		40995 => array(
				"flame" => 40996,
				"shock" => 41003,
				"frost" => 41000,
				"flameName" => "Flame Clench",
				"shockName" => "Shock Clench",
				"frostName" => "Frost Clench",
		),
		41006 => array(
				"flame" => 41009,
				"shock" => 41016,
				"frost" => 41013,
				"flameName" => "Flame Clench",
				"shockName" => "Shock Clench",
				"frostName" => "Frost Clench",
		),
		
			/* Impulse */
		28800 => array(
				"flame" => 28794,
				"shock" => 28799,
				"frost" => 28798,
				"flameName" => "Fire Impulse",
				"shockName" => "Shock Impulse",
				"frostName" => "Frost Impulse",
		),
		42949 => array(
				"flame" => 42950,
				"shock" => 42952,
				"frost" => 42951,
				"flameName" => "Fire Impulse",
				"shockName" => "Shock Impulse",
				"frostName" => "Frost Impulse",
		),
		42953 => array(
				"flame" => 42954,
				"shock" => 42956,
				"frost" => 42955,
				"flameName" => "Fire Impulse",
				"shockName" => "Shock Impulse",
				"frostName" => "Frost Impulse",
		),
		42957 => array(
				"flame" => 42958,
				"shock" => 42960,
				"frost" => 42959,
				"flameName" => "Fire Impulse",
				"shockName" => "Shock Impulse",
				"frostName" => "Frost Impulse",
		),
		
			/* Wall of Elements */
		28858 => array(
				"flame" => 28807,
				"shock" => 28854,
				"frost" => 28849,
				"flameName" => "Wall of Fire",
				"shockName" => "Wall of Storms",
				"frostName" => "Wall of Frost",
		),
		41627 => array(
				"flame" => 41628,
				"shock" => 41632,
				"frost" => 41637,
				"flameName" => "Wall of Fire",
				"shockName" => "Wall of Storms",
				"frostName" => "Wall of Frost",
		),
		41642 => array(
				"flame" => 41643,
				"shock" => 41647,
				"frost" => 41652,
				"flameName" => "Wall of Fire",
				"shockName" => "Wall of Storms",
				"frostName" => "Wall of Frost",
		),
		41658 => array(
				"flame" => 41659,
				"shock" => 41663,
				"frost" => 41668,
				"flameName" => "Wall of Fire",
				"shockName" => "Wall of Storms",
				"frostName" => "Wall of Frost",
		),
		
			/* Unstable wall of Elements */
		39052 => array(
				"flame" => 39053,
				"shock" => 39073,
				"frost" => 39067,
				"flameName" => "Unstable Wall of Fire",
				"shockName" => "Unstable Wall of Storms",
				"frostName" => "Unstable Wall of Frost",
		),
		41673 => array(
				"flame" => 41674,
				"shock" => 41685,
				"frost" => 41679,
				"flameName" => "Unstable Wall of Fire",
				"shockName" => "Unstable Wall of Storms",
				"frostName" => "Unstable Wall of Frost",
		),
		41691 => array(
				"flame" => 41692,
				"shock" => 41705,
				"frost" => 41697,
				"flameName" => "Unstable Wall of Fire",
				"shockName" => "Unstable Wall of Storms",
				"frostName" => "Unstable Wall of Frost",
		),
		41711 => array(
				"flame" => 41712,
				"shock" => 41723,
				"frost" => 41717,
				"flameName" => "Unstable Wall of Fire",
				"shockName" => "Unstable Wall of Storms",
				"frostName" => "Unstable Wall of Frost",
		),
		
		
			/* Elemental Ring */
		39143 => array(
				"flame" => 39145,
				"shock" => 39147,
				"frost" => 39146,
				"flameName" => "Fire Ring",
				"shockName" => "Shock Ring",
				"frostName" => "Frost Ring",
		),
		42961 => array(
				"flame" => 42962,
				"shock" => 42966,
				"frost" => 42964,
				"flameName" => "Fire Ring",
				"shockName" => "Shock Ring",
				"frostName" => "Frost Ring",
		),
		42968 => array(
				"flame" => 42969,
				"shock" => 42973,
				"frost" => 42971,
				"flameName" => "Fire Ring",
				"shockName" => "Shock Ring",
				"frostName" => "Frost Ring",
		),
		42975 => array(
				"flame" => 42976,
				"shock" => 42980,
				"frost" => 42978,
				"flameName" => "Fire Ring",
				"shockName" => "Shock Ring",
				"frostName" => "Frost Ring",
		),
		
			/* Pulsar */
		39161 => array(
				"flame" => 39162,
				"shock" => 39167,
				"frost" => 39163,
				"flameName" => "Flame Pulsar",
				"shockName" => "Storm Pulsar",
				"frostName" => "Frost Pulsar",
		),
		42982 => array(
				"flame" => 42983,
				"shock" => 42987,
				"frost" => 42985,
				"flameName" => "Flame Pulsar",
				"shockName" => "Storm Pulsar",
				"frostName" => "Frost Pulsar",
		),
		42989 => array(
				"flame" => 42990,
				"shock" => 42994,
				"frost" => 42992,
				"flameName" => "Flame Pulsar",
				"shockName" => "Storm Pulsar",
				"frostName" => "Frost Pulsar",
		),
		42996 => array(
				"flame" => 42997,
				"shock" => 43001,
				"frost" => 42999,
				"flameName" => "Flame Pulsar",
				"shockName" => "Storm Pulsar",
				"frostName" => "Frost Pulsar",
		),
		
			/* Elemental Storm */
		83619 => array(
				"flame" => 83625,
				"shock" => 83630,
				"frost" => 83628,
				"flameName" => "Fire Storm",
				"shockName" => "Thunder Storm",
				"frostName" => "Ice Storm",
		),
		86481 => array(
				"flame" => 86488,
				"shock" => 86500,
				"frost" => 86494,
				"flameName" => "Fire Storm",
				"shockName" => "Thunder Storm",
				"frostName" => "Ice Storm",
		),
		86483 => array(
				"flame" => 86490,
				"shock" => 86502,
				"frost" => 86495,
				"flameName" => "Fire Storm",
				"shockName" => "Thunder Storm",
				"frostName" => "Ice Storm",
		),
		86485 => array(
				"flame" => 86492,
				"shock" => 86504,
				"frost" => 86496,
				"flameName" => "Fire Storm",
				"shockName" => "Thunder Storm",
				"frostName" => "Ice Storm",
		),
		
			/* Elemental Rage */
		84434 => array(
				"flame" => 85126,
				"shock" => 85130,
				"frost" => 85128,
				"flameName" => "Fiery Rage",
				"shockName" => "Thunderous Rage",
				"frostName" => "Icy Rage",
		),
		86506 => array(
				"flame" => 86512,
				"shock" => 86524,
				"frost" => 86518,
				"flameName" => "Fiery Rage",
				"shockName" => "Thunderous Rage",
				"frostName" => "Icy Rage",
		),
		86508 => array(
				"flame" => 86513,
				"shock" => 86526,
				"frost" => 86520,
				"flameName" => "Fiery Rage",
				"shockName" => "Thunderous Rage",
				"frostName" => "Icy Rage",
		),
		86510 => array(
				"flame" => 86515,
				"shock" => 86528,
				"frost" => 86522,
				"flameName" => "Fiery Rage",
				"shockName" => "Thunderous Rage",
				"frostName" => "Icy Rage",
		),
		
			/* Eye of the Storm */
		83642 => array(
				"flame" => 83682,
				"shock" => 83686,
				"frost" => 83684,
				"flameName" => "Eye of Flame",
				"shockName" => "Eye of Lightning",
				"frostName" => "Eye of Frost",
		),
		86530 => array(
				"flame" => 86536,
				"shock" => 86548,
				"frost" => 86542,
				"flameName" => "Eye of Flame",
				"shockName" => "Eye of Lightning",
				"frostName" => "Eye of Frost",
		),
		86532 => array(
				"flame" => 86538,
				"shock" => 86550,
				"frost" => 86544,
				"flameName" => "Eye of Flame",
				"shockName" => "Eye of Lightning",
				"frostName" => "Eye of Frost",
		),
		86534 => array(
				"flame" => 86540,
				"shock" => 86552,
				"frost" => 86546,
				"flameName" => "Eye of Flame",
				"shockName" => "Eye of Lightning",
				"frostName" => "Eye of Frost",
		),		
	
);


	/* Skills that are classed as "Poison based" for the DK's World in Ruin Passive. */
$ESO_POISON_SKILLS = array(
		20668 => 1,		// Venemous Claw 		
		41990 => 1,		// Shadow Silk 		
		42012 => 1,		// Tangling Webs 		
		38645 => 1,		// Venom Arrow 		
		38660 => 1,		// Poison Injection 	
		38685 => 1,		// Lethal Arrow 		
		28869 => 1,		// Poison Arrow 		
		38701 => 1,		// Acid Spray 			
		20944 => 1,		// Noxious Breath 		
		39425 => 1,		// Trapping Webs 	
);

	/* Skills that are classed as "Flame AOE" for the Elf Bane set.
	 * 		0 = Only affect description durations.
	 *		1 = Replace duration of entire ability in addition to all description durations.
	 *		2 = Match description duration to skill duration.
	 *		3 = Only replace description durations
	 */
$ESO_ELFBANE_SKILLS = array(
		3341  => 0,  // Lava Flows
		15957 => 0,  // Magma Armor 1
		15957 => 0,  // Magma Armor 2
		15957 => 0,  // Magma Armor 3
		15957 => 0,  // Magma Armor 4
		17874 => 0,  // Magma Shell 1
		17874 => 0,  // Magma Shell 2
		17874 => 0,  // Magma Shell 3
		17874 => 0,  // Magma Shell 4
		20657 => 0,  // Searing Strike 1
		20657 => 0,  // Searing Strike 2
		20657 => 0,  // Searing Strike 3
		20657 => 0,  // Searing Strike 4
		20660 => 0,  // Burning Embers 1
		20660 => 0,  // Burning Embers 2
		20660 => 0,  // Burning Embers 3
		20660 => 0,  // Burning Embers 4
		20917 => 0,  // Fiery Breath 1
		20917 => 0,  // Fiery Breath 2
		20917 => 0,  // Fiery Breath 3
		20917 => 0,  // Fiery Breath 4
		20930 => 0,  // Engulfing Flames 1
		20930 => 0,  // Engulfing Flames 2
		20930 => 0,  // Engulfing Flames 3
		20930 => 0,  // Engulfing Flames 4
		21726 => 0,  // Sun Fire 1
		21726 => 0,  // Sun Fire 2
		21726 => 0,  // Sun Fire 3
		21726 => 0,  // Sun Fire 4
		21729 => 0,  // Vampire's Bane 1
		21729 => 0,  // Vampire's Bane 2
		21729 => 0,  // Vampire's Bane 3
		21729 => 0,  // Vampire's Bane 4
		21732 => 0,  // Reflective Light 1
		21732 => 0,  // Reflective Light 2
		21732 => 0,  // Reflective Light 3
		21732 => 0,  // Reflective Light 4
		28708 => 0,  // Empower
		28807 => 0,  // Wall of Fire 1
		28807 => 0,  // Wall of Fire 2
		28807 => 0,  // Wall of Fire 3
		28807 => 0,  // Wall of Fire 4
		28988 => 0,  // Dragonknight Standard 1
		28988 => 0,  // Dragonknight Standard 2
		28988 => 0,  // Dragonknight Standard 3
		28988 => 0,  // Dragonknight Standard 4
		32710 => 0,  // Eruption 1
		32710 => 0,  // Eruption 2
		32710 => 0,  // Eruption 3
		32710 => 0,  // Eruption 4
		32947 => 0,  // Standard of Might 1
		32947 => 0,  // Standard of Might 2
		32947 => 0,  // Standard of Might 3
		32947 => 0,  // Standard of Might 4
		32958 => 0,  // Shifting Standard 1
		32958 => 0,  // Shifting Standard 2
		32958 => 0,  // Shifting Standard 3
		32958 => 0,  // Shifting Standard 4
		39012 => 0,  // Blockade of Fire 1
		39012 => 0,  // Blockade of Fire 2
		39012 => 0,  // Blockade of Fire 3
		39012 => 0,  // Blockade of Fire 4
		39053 => 0,  // Unstable Wall of Fire 1
		39053 => 0,  // Unstable Wall of Fire 2
		39053 => 0,  // Unstable Wall of Fire 3
		39053 => 0,  // Unstable Wall of Fire 4
		54129 => 0,  // Fire Chain
		63198 => 0,  // Fiery Chain
		83625 => 0,  // Fire Storm 1
		83625 => 0,  // Fire Storm 2
		83625 => 0,  // Fire Storm 3
		83625 => 0,  // Fire Storm 4
		83682 => 0,  // Eye of Flame 1
		83682 => 0,  // Eye of Flame 2
		83682 => 0,  // Eye of Flame 3
		83682 => 0,  // Eye of Flame 4
		85126 => 0,  // Fiery Rage 1
		85126 => 0,  // Fiery Rage 2
		85126 => 0,  // Fiery Rage 3
		85126 => 0,  // Fiery Rage 4
		100155 => 0,  // Crushing Wall

				// Older data no longer needed (stored in skillTooltips database)
		3341  => 2, // Lava Flows
		15774 => 2, // Flaming Oil
		15957 => 1,	// Magma Armor
		//16536 => 0,	// Meteor
		17874 => 1,	// Magma Shell
		20252 => 1,	// Burning Talons
		20657 => 1,	// Searing Strike
		20660 => 1,	// Burning Embers
		20917 => 1,	// Fiery Breath
		20930 => 1,	// Engulfing Flames
		21726 => 1, // Sun Fire
		21729 => 1,	// Vampire's Bane
		21732 => 1,	// Reflective Light
		//28708 => 0, // Empower
		//28807 => 0,	// Wall of Fire
		28988 => 2,	// Dragonknight Standard
		//29073 => 0,	// Flame Touch
		29368 => 2, // Light Attack
		//32710 => 0,	// Eruption
		32947 => 2,	// Standard of Might
		32958 => 2,	// Shifting Standard
		38937 => 2, // Destructive Reach
		38944 => 2,	// Flame Reach
		//39012 => 0,	// Blockade of Fire
		//39053 => 0,	// Unstable Wall of Fire
		40465 => 3,	// Scalding Rune
		40493 => 1,	// Shooting Star
		//40948 => 0,
		//40957 => 0,
		//40965 => 0,
		//40984 => 0,
		//40996 => 0,
		//41009 => 0,
		//41030 => 0,
		//41039 => 0,
		//41048 => 0,
		41628 => 1,
		41643 => 1,
		41659 => 1,
		41674 => 1,
		41692 => 1,
		41712 => 1,
		41739 => 1,
		41755 => 1,
		41770 => 1,
		52436 => 1, // Dragonknight Standard
		83625 => 2,	// Fire Storm
		83682 => 2,	// Eye of Flame
		85126 => 1,	// Fiery Rage
		83761 => 3, // Player Pet Defenses
		//84492 => 3, // Grothdarr
		85126 => 2, // Fiery Rage
		86488 => 1,
		86490 => 1,
		86491 => 1,
		86512 => 1,
		86513 => 1,
		86515 => 1,
		86536 => 1,
		86538 => 1,
		86540 => 1,
);


	/* Skills that are have flame AOE damage for the DK's World in Ruin Passive.
	 * Empty arrays mean all flame damage in the skill tooltip is increased. Otherwise
	 * only the listed skill coefficient indices are affected. */
$ESO_FLAMEAOE_SKILLS = array(
		15957 => array(),		// Magma Armor
		16536 => array(),		// Meteor, confirmed update 20
		17874 => array(),		// Magma Shell
		20252 => array(2 => 1),	// Burning Talons, confirmed update 20
		20917 => array(),		// Fiery Breath, DoT Component confirmed update 20
		20930 => array(),		// Engulfing Flames, DoT Component confirmed update 20
		21732 => array(1 => 1),	// Reflective Light, confirmed update 20
		28794 => array(),		// Fire Impluse
		28807 => array(),		// Wall of Fire
		28967 => array(),		// Inferno 
		28988 => array(),		// Dragonknight Standard, confirmed update 20
		31632 => array(),		// Fire Rune
		31837 => array(2 => 1),	// Inhale
		32710 => array(),		// Eruption, confirmed update 20
		32715 => array(),		// Ferocious Leap
		32785 => array(2 => 1),	// Draw Essence
		32792 => array(2 => 1),	// Deep Breath
		32853 => array(),		// Flames of Oblivion, confirmed update 20
		32947 => array(),		// Standard of Might, confirmed update 20
		32958 => array(),		// Shifting Standard, confirmed update 20
		39012 => array(),		// Blockade of Fire
		39053 => array(),		// Unstable Wall of Fire
		39145 => array(),		// Fire Ring
		39162 => array(),		// Flame Pulsar
		40465 => array(1 => 1),	// Scalding Rune, confirmed update 20
		40470 => array(),		// Volcanic Rune
		40493 => array(),		// Shooting Star, confirmed update 20
		83625 => array(),		// Fire Storm
		83682 => array(),		// Eye of Flame
		85126 => array(),		// Fiery Rage
);


$ESO_ALLIANCES = array(
		-1 => "",
		0 => "",
		1 => "Aldmeri Dominion",
		2 => "Daggerfall Covenant",
		3 => "Ebonheart Pact",
);


$ESO_ALLIANCES_SHORT = array(
		-1 => "",
		0 => "",
		1 => "Aldmeri",
		2 => "Daggerfall",
		3 => "Ebonheart",
);


$ESO_CLASSIDS = array(
		-1 => "",
		0 => "",
		1 => "Dragonknight",
		2 => "Sorcerer",
		3 => "Nightblade",
		4 => "Warden",
		5 => "Necromancer",
		6 => "Templar",
);


$ESO_SKILLTYPESTYPE = array(
		0 => "Class",
		1 => "Dragonknight",
		2 => "Nightblade",
		3 => "Sorcerer",
		4 => "Templar",
		5 => "Weapon",
		6 => "Armor",
		7 => "World",
		8 => "Guild",
		9 => "Alliance War",
		10 => "Racial",
		11 => "Craft",
);


$ESO_FREE_SKILLS = array(
		78219 => "passive",
		74580 => "passive",
		45542 => "passive",
		47276 => "passive",
		47288 => "passive",
		46727 => "passive",
		44590 => "passive",
		47282 => "passive",
		46758 => "passive",
		44625 => "passive",
		36582 => "passive",
		36247 => "passive",
		36588 => "passive",
		35965 => "passive",
		36312 => "passive",
		36063 => "passive",
		36626 => "passive",
		33293 => "passive",
		84680 => "passive",
		36008 => "passive",
		
		152778 => "passive",		// Armor bonuses/penalties (Update 29)
		150185 => "passive",
		150181 => "passive",
		152780 => "passive",
		150184 => "passive",
		
		43056 => "active",
		//41920 => "ultimate",  // Bat Swarm not free?
		
		42358 => "ultimate",
		32455 => "ultimate",	// Werewolf Transformation 1
		
		103632 => "passive", 	// Update 18
		103793 => "passive",
		
		32634 => "passive",		// Update 20: Devour
		
		116096 => "ultimate",	// Update 22: Volendrung
		116093 => "active",
		116094 => "active",
		117979 => "active",
		116095 => "active",
		117985 => "active",
		
);


$ESO_FREE_RACIAL_SKILLS = array(
		36582 => "Argonian",
		36247 => "Breton",
		36588 => "Dark Elf",
		35965 => "High Elf",
		36312 => "Imperial",
		36063 => "Khajiit",
		36626 => "Nord",
		33293 => "Orc",
		84680 => "Redguard",
		36008 => "Wood Elf",
);


if (!function_exists('imageantialias'))
{
	function imageantialias($image, $enabled)
	{
		return false;
	}
}


function GetEsoDisplayVersion($version)
{
	$version = GetEsoItemTableSuffix($version);
	if ($version == '') $version = GetEsoUpdateVersion();
	return strtoupper($version);
}


function GetEsoUpdateVersion()
{
	return 34;
}


function GetEsoItemTableSuffix($version)
{

	//if ($version == GetEsoUpdateVersion()) return "";
	
	switch ($version)
	{
		case '1.5':
		case '5':
			return "5";
		case '1.6':
		case '6':
			return "6";
		case '1.7':
		case '7':
			return "7";
		case '1.8':
		case '8':
			return "8";
		case '1.8pts':
			return "8pts";
		case '1.9pts':
			return "9pts";
		case '1.9':
		case '9':
			return "9";
		case '1.10pts':
		case '10pts':
			return "10pts";
		case '1.10':
		case '110':
		case '10':
			return "10";
		case '1.11pts':
		case '111pts':
		case '11pts':
			return "11pts";
		case '1.11':
		case '111':
		case '11':
			return "11";
		case '1.12pts':
		case '112pts':
		case '12pts':
			return "12pts";
		case '1.12':
		case '112':
		case '12':
			return "12";
		case '1.13pts':
		case '113pts':
		case '13pts':
			return "13pts";
		case '1.13':
		case '113':
		case '13':
			return "13";
		case '1.4pts':
		case '114pts':
		case '14pts':
			return "14pts";
		case '1.4':
		case '114':
		case '14':
			return "14";
		case '1.5pts':
		case '115pts':
		case '15pts':
			return "15pts";
		case '1.5':
		case '115':
		case '15':
			return "15";
		case '1.6pts':
		case '116pts':
		case '16pts':
			return "16pts";
		case '1.6':
		case '116':
		case '16':
			return "16";
		case '1.7pts':
		case '117pts':
		case '17pts':
			return "17pts";
		case '1.7':
		case '117':
		case '17':
			return "17";
		case '1.8pts':
		case '118pts':
		case '18pts':
			return "18pts";
		case '1.8':
		case '118':
		case '18':
			return "18";
		case '1.9pts':
		case '119pts':
		case '19pts':
			return "19pts";
		case '1.9':
		case '119':
		case '19':
			return "19";
		case '120pts':
		case '20pts':
			return "20pts";
		case '120':
		case '20':
			return "20";
		case '121pts':
		case '21pts':
			return "21pts";
		case '121':
		case '21':
			return "21";
		case '22pts':
			return "22pts";
		case '22':
			return "22";
		case '23pts':
			return "23pts";
		case '23':
			return "23";
		case '24pts':
			return "24pts";
		case '24':
			return "24";
		case '25pts':
			return "25pts";
		case '25':
			return "25";
		case '26pts':
			return "26pts";
		case '26':
			return "26";
		case '27pts':
			return "27pts";
		case '27':
			return "27";
		case '28pts':
			return "28pts";
		case '28':
			return "28";
		case '29pts':
			return "29pts";
		case '29':
			return "29";
		case '30pts':
			return "30pts";
		case '30pts2':
			return "30pts2";
		case '30':
			return "30";
		case '31pts':
			return "31pts";
		case '31':
			return "";
		case '32pts':
			return "32pts";
		case '32':
			return "32";
		case '33pts':
			return "33pts";
		case '33':
			return "33";
		case '34pts':
			return "34pts";
		case '34':
			return "";
	}

	return "";
}


function GetEsoLevelFromIntType($intType, $intLevel = 1)
{
	global $ESO_ITEMINTTYPE_LEVELMAP;
	
	if (!array_key_exists($intType, $ESO_ITEMINTTYPE_LEVELMAP)) return $intLevel;
	
	$level = $ESO_ITEMINTTYPE_LEVELMAP[$intType];
	if ($level == 1) return $intLevel;
	
	return $level;
}


function GetEsoQualityFromIntType($intType)
{
	global $ESO_ITEMINTTYPE_QUALITYMAP;
	
	if (!array_key_exists($intType, $ESO_ITEMINTTYPE_QUALITYMAP)) return 1;
	return $ESO_ITEMINTTYPE_QUALITYMAP[$intType];
}


function FindEsoItemLevelIntTypeMap($inLevel)
{
	global $ESO_ITEMQUALITYLEVEL_INTTYPEMAP;
	$lastMap = array();
	
	$inLevel = intval($inLevel);
	
	foreach ($ESO_ITEMQUALITYLEVEL_INTTYPEMAP as $level => $map)
	{
		if ($level == $inLevel) return $map;
		if ($level >  $inLevel) return $lastMap;
		
		$lastMap = $map;
	}
	
	return array(1, 1, 1, 1, 1, 1);
}


function GetEsoUpdateName($update)
{
	global $GAMEUPDATE_TO_GAMENAME;
	
	if (array_key_exists($update, $GAMEUPDATE_TO_GAMENAME)) return $GAMEUPDATE_TO_GAMENAME[$update];
	return "Unknown Update $update";
}


function GetEsoItemTraitFullText($trait, $version = "")
{
	global $ESO_ITEMTRAIT_FULLTEXTS;
	global $ESO_ITEMTRAIT10_FULLTEXTS;
	global $ESO_ITEMTRAIT15_FULLTEXTS;
	
	$key = (int) $trait;
	
	if (IsEsoVersionAtLeast($version, 15))
	{
		if (array_key_exists($key, $ESO_ITEMTRAIT15_FULLTEXTS)) return $ESO_ITEMTRAIT15_FULLTEXTS[$key];
		return "Unknown ($key)";
	}
	
	if (IsEsoVersionAtLeast($version, 10))
	{
		if (array_key_exists($key, $ESO_ITEMTRAIT10_FULLTEXTS)) return $ESO_ITEMTRAIT10_FULLTEXTS[$key];
		return "Unknown ($key)";
	}
	
	if (array_key_exists($key, $ESO_ITEMTRAIT_FULLTEXTS)) return $ESO_ITEMTRAIT_FULLTEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoStatTypeText($value)
{
	global $ESO_STATTYPES;

	$key = (int) $value;
	if (array_key_exists($key, $ESO_STATTYPES)) return $ESO_STATTYPES[$key];
	return "Unknown ($key)";
}


function GetEsoAbilityTypeText($value)
{
	global $ESO_ABILITYTYPES;

	$key = (int) $value;
	if (array_key_exists($key, $ESO_ABILITYTYPES)) return $ESO_ABILITYTYPES[$key];
	return "Unknown ($key)";
}


function GetEsoDamageTypeText($value)
{
	global $ESO_DAMAGETYPES;

	$key = (int) $value;
	if (array_key_exists($key, $ESO_DAMAGETYPES)) return $ESO_DAMAGETYPES[$key];
	return "Unknown ($key)";
}


function GetEsoCombatMechanicText($value)
{
	global $ESO_COMBATMECHANICS;

	$key = (int) $value;
	if (array_key_exists($key, $ESO_COMBATMECHANICS)) return $ESO_COMBATMECHANICS[$key];
	return "Unknown ($key)";
}


function GetEsoCombatMechanicText34($value)
{
	global $ESO_COMBATMECHANICS_34;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_COMBATMECHANICS_34)) return $ESO_COMBATMECHANICS_34[$key];
	return "Unknown ($key)";
}


function GetEsoItemCraftTypeText($value)
{
	global $ESO_CRAFTTYPES;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_CRAFTTYPES)) return $ESO_CRAFTTYPES[$key];
	return "Unknown ($key)";
}


function GetEsoAllianceText($value)
{
	global $ESO_ALLIANCES;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_ALLIANCES)) return $ESO_ALLIANCES[$key];
	return "Unknown ($key)";
}


function GetEsoAllianceShortText($value)
{
	global $ESO_ALLIANCES_SHORT;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_ALLIANCES_SHORT)) return $ESO_ALLIANCES_SHORT[$key];
	return "Unknown ($key)";
}


function GetEsoClassIdText($value)
{
	global $ESO_CLASSIDS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_CLASSIDS)) return $ESO_CLASSIDS[$key];
	return "Unknown ($key)";
}


function GetEsoSkillTypeTypeText($value)
{
	global $ESO_SKILLTYPESTYPE;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_SKILLTYPESTYPE)) return $ESO_SKILLTYPESTYPE[$key];
	return "Unknown ($key)";
}


function GetEsoItemCraftRequireText($value)
{
	global $ESO_CRAFT_REQUIRESKILLS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_CRAFT_REQUIRESKILLS)) return $ESO_CRAFT_REQUIRESKILLS[$key];
	return "Unknown ($key)";
}


function GetEsoItemTraitText($trait, $version = "")
{
	global $ESO_ITEMTRAIT_TEXTS;
	global $ESO_ITEMTRAIT10_TEXTS;
	global $ESO_ITEMTRAIT15_TEXTS;
	
	$key = (int) $trait;
	
	if (IsEsoVersionAtLeast($version, 15))
	{
		if (array_key_exists($key, $ESO_ITEMTRAIT15_TEXTS)) return $ESO_ITEMTRAIT15_TEXTS[$key];
		return "Unknown ($key)";
	}
	
	if (IsEsoVersionAtLeast($version, 10))
	{
		if (array_key_exists($key, $ESO_ITEMTRAIT10_TEXTS)) return $ESO_ITEMTRAIT10_TEXTS[$key];
		return "Unknown ($key)";
	}
	
	if (array_key_exists($key, $ESO_ITEMTRAIT_TEXTS)) return $ESO_ITEMTRAIT_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoItemStyleText($style)
{
	global $ESO_ITEMSTYLE_TEXTS;
	
	$key = (int) $style;
	if (array_key_exists($key, $ESO_ITEMSTYLE_TEXTS)) return $ESO_ITEMSTYLE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoItemQualityText($quality)
{
	global $ESO_ITEMQUALITY_TEXTS;
	
	$key = (int) $quality;
	if (array_key_exists($key, $ESO_ITEMQUALITY_TEXTS)) return $ESO_ITEMQUALITY_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoItemArmorTypeText($armorType)
{
	global $ESO_ITEMARMORTYPE_TEXTS;
	
	$key = (int) $armorType;
	if (array_key_exists($key, $ESO_ITEMARMORTYPE_TEXTS)) return $ESO_ITEMARMORTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoItemWeaponTypeText($weaponType)
{
	global $ESO_ITEMWEAPONTYPE_TEXTS;
	
	$key = (int) $weaponType;
	if (array_key_exists($key, $ESO_ITEMWEAPONTYPE_TEXTS)) return $ESO_ITEMWEAPONTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoItemTypeText($type)
{
	global $ESO_ITEMTYPE_TEXTS;
	
	$key = (int) $type;
	if (array_key_exists($key, $ESO_ITEMTYPE_TEXTS)) return $ESO_ITEMTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoItemSpecialTypeText($type)
{
	global $ESO_ITEMSPECIALTYPE_TEXTS;

	$key = (int) $type;
	if (array_key_exists($key, $ESO_ITEMSPECIALTYPE_TEXTS)) return $ESO_ITEMSPECIALTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoItemSpecialTypeRawText($type)
{
	global $ESO_ITEMSPECIALTYPE_RAW_TEXTS;

	$key = (int) $type;
	if (array_key_exists($key, $ESO_ITEMSPECIALTYPE_RAW_TEXTS)) return $ESO_ITEMSPECIALTYPE_RAW_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoItemEquipTypeText($equipType)
{
	global $ESO_ITEMEQUIPTYPE_TEXTS;
	
	$key = (int) $equipType;
	if (array_key_exists($key, $ESO_ITEMEQUIPTYPE_TEXTS)) return $ESO_ITEMEQUIPTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoItemBindTypeText($bindType)
{
	global $ESO_ITEMBINDTYPE_TEXTS;
	
	$key = (int) $bindType;
	if (array_key_exists($key, $ESO_ITEMBINDTYPE_TEXTS)) return $ESO_ITEMBINDTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoReactionText($bindType)
{
	global $ESO_REACTION_TEXTS;
	
	$key = (int) $bindType;
	if (array_key_exists($key, $ESO_REACTION_TEXTS)) return $ESO_REACTION_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoFurnLimitTypeRawText($bindType)
{
	global $ESO_FURNLIMITTYPE_RAWTEXTS;
	
	$key = (int) $bindType;
	if (array_key_exists($key, $ESO_FURNLIMITTYPE_RAWTEXTS)) return $ESO_FURNLIMITTYPE_RAWTEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoFurnLimitTypeText($bindType)
{
	global $ESO_FURNLIMITTYPE_TEXTS;
	
	$key = (int) $bindType;
	if (array_key_exists($key, $ESO_FURNLIMITTYPE_TEXTS)) return $ESO_FURNLIMITTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoCollectibleCategoryTypeText($bindType)
{
	global $ESO_COLLECTIBLECATEGORYTYPE_TEXTS;
	
	$key = (int) $bindType;
	if (array_key_exists($key, $ESO_COLLECTIBLECATEGORYTYPE_TEXTS)) return $ESO_COLLECTIBLECATEGORYTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoMechanicTypeText($mechanicType, $version = '')
{
	global $ESO_MECHANIC_TEXTS;
	
	if (intval($version) >= 34) return GetEsoMechanicTypeText34($mechanicType);
	
	$key = (int) $mechanicType;
	if (array_key_exists($key, $ESO_MECHANIC_TEXTS)) return $ESO_MECHANIC_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoMechanicTypeText34($mechanicType)
{
	global $ESO_MECHANIC_TEXTS_34;
	
	$key = (int) $mechanicType;
	if (array_key_exists($key, $ESO_MECHANIC_TEXTS_34)) return $ESO_MECHANIC_TEXTS_34[$key];
	return "Unknown ($key)";
}


function GetEsoBuffTypeText($value)
{
	global $ESO_BUFFTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_BUFFTYPE_TEXTS)) return $ESO_BUFFTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoQuestRepeatTypeText($value)
{
	global $ESO_QUEST_REPEATTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_QUEST_REPEATTYPE_TEXTS)) return $ESO_QUEST_REPEATTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoQuestStepTypeText($value)
{
	global $ESO_QUEST_STEPTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_QUEST_STEPTYPE_TEXTS)) return $ESO_QUEST_STEPTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoQuestStepVisibilityTypeText($value)
{
	global $ESO_QUEST_STEPVISIBILITYTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_QUEST_STEPVISIBILITYTYPE_TEXTS)) return $ESO_QUEST_STEPVISIBILITYTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoQuestTypeText($value)
{
	global $ESO_QUESTTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_QUESTTYPE_TEXTS)) return $ESO_QUESTTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoMapPinTypeText($value)
{
	global $ESO_MAPPINTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_MAPPINTYPE_TEXTS)) return $ESO_MAPPINTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoChestTypeText($value)
{
	global $ESO_CHESTTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_CHESTTYPE_TEXTS)) return $ESO_CHESTTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoRewardEntryTypeText($value)
{
	global $ESO_REWARDENTRYTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_REWARDENTRYTYPE_TEXTS)) return $ESO_REWARDENTRYTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoQuestRewardTypeText($value)
{
	global $ESO_QUESTREWARDTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_QUESTREWARDTYPE_TEXTS)) return $ESO_QUESTREWARDTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoTimedActivityTypeText($value)
{
	global $ESO_TIMEDACTIVITYTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_TIMEDACTIVITYTYPE_TEXTS)) return $ESO_TIMEDACTIVITYTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoQuestRewardItemTypeText($value)
{
	global $ESO_QUESTREWARDITEMTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_QUESTREWARDITEMTYPE_TEXTS)) return $ESO_QUESTREWARDITEMTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoCurrencyTypeText($value)
{
	global $ESO_CURRENCYTYPE_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_CURRENCYTYPE_TEXTS)) return $ESO_CURRENCYTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoCurrencyTypeShortText($value)
{
	global $ESO_CURRENCYTYPESHORT_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_CURRENCYTYPESHORT_TEXTS)) return $ESO_CURRENCYTYPESHORT_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoCurrencyChangeReasonText($value)
{
	global $ESO_CURRENCYCHANGEREASON_TEXTS;
	
	$key = (int) $value;
	if (array_key_exists($key, $ESO_CURRENCYCHANGEREASON_TEXTS)) return $ESO_CURRENCYCHANGEREASON_TEXTS[$key];
	return "Unknown ($key)";
}



function GetEsoCustomMechanicTypeText($mechanicType, $version = '')
{
	global $ESO_CUSTOM_MECHANICS;
	
	$key = (int) $mechanicType;
	if (array_key_exists($key, $ESO_CUSTOM_MECHANICS)) return $ESO_CUSTOM_MECHANICS[$key];
	
	return GetEsoMechanicTypeText($mechanicType, $version);
}


function GetEsoSkillTypeText ($value)
{
	global $ESO_SKILLTYPES;
	
	$key = (int) $value;
	
	if (array_key_exists($key, $ESO_SKILLTYPES)) return $ESO_SKILLTYPES[$key];
	return "Unknown ($key)";
}


function GetEsoAttributeText($attribute)
{
	global $ESO_ATTRIBUTES;
	
	$attribute = intval($attribute);
	if (array_key_exists($attribute, $ESO_ATTRIBUTES)) return $ESO_ATTRIBUTES[$attribute];
	
	return "Unknown ($attribute)";
}


function GetEsoSetIndexText($value)
{
	global $ESO_SET_INDEXES;

	$value = intval($value);
	if (array_key_exists($value, $ESO_SET_INDEXES)) return $ESO_SET_INDEXES[$value];
	
	return "";
}


function GetEsoItemLevelText($level)
{
	if ($level <= 50) return strval($level);
	if (UESP_SHOWCPLEVEL) return "CP" . (($level - 50) * 10); 
	return "V" . strval($level - 50);
}


function GetEsoItemFullLevelText($level)
{
	if ($level <= 50) return "Level " . strval($level);
	if (UESP_SHOWCPLEVEL) return "Level 50 CP" . (($level - 50) * 10);
	return "Rank V" . strval($level - 50);
}


function MakeEsoIconLink($icon)
{
	$icon = preg_replace('#\.dds$#', ".png", $icon);
	if ($icon[0] == '/') return UESP_ESO_ICON_URL . $icon;
	return UESP_ESO_ICON_URL . "/" . $icon;
}


function IsEsoVersionAtLeast($version, $checkVersion)
{
	if ($version === null || $version == "" || $version == GetEsoUpdateVersion())
	{
		$suffix = GetEsoUpdateVersion();
	}
	else
	{
		//$version = str_replace("pts", "", $version);
		$suffix = intval(GetEsoItemTableSuffix($version));
	}
	
	return ($suffix >= $checkVersion);
}


function FormatEsoItemDescriptionIcons($desc)
{
		//|t32:32:EsoUI/Art/UnitFrames/target_veteranRank_icon.dds|t

	$output = preg_replace_callback("#\|t([0-9%]*):([0-9%]*):([^\|]*)\.dds\|t#s",  
			function ($matches) {
				$extra = "";
				
				if (stripos($matches[3], "currency_writvoucher") > 0)
				{
					$extra = "style=\"position: relative; top: 3px;\"";	
				}
				
				if ($matches[1] != "" && $matches[2] != "")
				{
					if (substr($matches[1], -1) == "%" || substr($matches[2], -1) == "%")
						return "<img src='" . UESP_ESO_ICON_URL . "/" . strtolower($matches[3]) . ".png' width=\"16px\" height=\"16px\" $extra style=\"position: relative; top: 3px;\" />";
					else
						return "<img src='" . UESP_ESO_ICON_URL . "/" . strtolower($matches[3]) . ".png' width=\"$matches[1]\" height=\"$matches[2]\" $extra />";
				}
				else
				{
					return "<img src='" . UESP_ESO_ICON_URL . "/" . strtolower($matches[3]) . ".png' $extra />";
				}
			},
			$desc);

	return $output;
}


function FormatEsoItemDescriptionText($desc)
{
	$output = preg_replace("| by ([0-9\-\.]+)|s", " by <div class='esoil_white'>$1</div>", $desc);
	$output = preg_replace("|Adds ([0-9\-\.]+)|s", "Adds <div class='esoil_white'>$1</div>", $output);
	$output = preg_replace("|for ([0-9\-\.]+)|s", "for <div class='esoil_white'>$1</div>", $output);
	
	$output = preg_replace("#\|c[0-9a-fA-F]{6}\|c([0-9a-fA-F]{6})([^|]+)\|r\|r#s", "<div style='color:#$1;display:inline;'>$2</div>", $output);
	$output = preg_replace("#\|c([0-9a-fA-F]{6})([^|]+)\|r#s", "<div style='color:#$1;display:inline;'>$2</div>", $output);
	$output = preg_replace("#\|c([0-9a-fA-F]{6})(.*)#s", "<div style='color:#$1;display:inline;'>$2</div>", $output);
	
		//|t32:32:EsoUI/Art/UnitFrames/target_veteranRank_icon.dds|t
		//EsoUI/Art/champion/champion_icon.dds
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*champion_icon\.dds)\|t#s", "CP ", $output);
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|trank #s", "VR ", $output);
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|t#s", "", $output);
	
	$output = str_replace("|o", "", $output);
	$output = str_replace("\n", "<br />", $output);

	return $output;
}


function FormatRemoveEsoItemDescriptionText($desc)
{
	$output = preg_replace("#\|c[0-9a-fA-F]{6}\|c([0-9a-fA-F]{6})([^|]+)\|r\|r#s", "$2", $desc);
	$output = preg_replace("#\|c([0-9a-fA-F]{6})([^|]+)\|r#s", "$2", $desc);
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*champion_icon\.dds)\|t#s", "CP ", $output);
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|trank #s", "VR ", $output);
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|t#s", "", $output);
	
	$output = preg_replace("#\|c[0-9a-fA-F]{6}#s", "", $output);
	$output = preg_replace("#\|r#s", "", $output);
	
	$output = str_replace("|o", "", $output);
	$output = str_replace("\n", " ", $output);
	
	return $output;
}


function ConvertEsoCriticalValueToPercent($value, $level)
{
	if ($level <= 0) return $value;
	$newValue = $value / (2 * $level * (100 + $level) / 100);
	return sprintf("%.1f", $newValue);
}


function FormatEsoCriticalDescriptionText($desc, $level)
{
	// 1% Critical = 2 * Level * (100 + Level) / 100
	// Level = NormalLevel(1-50) + VeteranRank(0-16)
	
	if ($level <= 0 || $desc == "") return $desc;
	$newDesc = $desc;
	
	$matches = array();
	$result = preg_match("#([0-9]+)-([0-9]+)(\|r)? (Spell|Weapon) Critical#i", $newDesc, $matches);
	
	if ($result)
	{
		$newValue1 = ConvertEsoCriticalValueToPercent($matches[1], 1);
		$newValue2 = ConvertEsoCriticalValueToPercent($matches[2], 66);
		$newDesc = preg_replace("#([0-9]+)-([0-9]+)(\|r)? ((Spell|Weapon) Critical)#i", "$newValue1-$newValue2%$3 $4", $newDesc);
		return $newDesc;
	}
	
	$matches = array();
	$result = preg_match("#([0-9]+)(\|r)? (Spell|Weapon) Critical#i", $newDesc, $matches);
	
	if ($result)
	{
		$newValue = ConvertEsoCriticalValueToPercent($matches[1], $level);
		$newDesc = preg_replace("#([0-9]+)(\|r)? ((Spell|Weapon) Critical)#i", "$newValue%$2 $3", $newDesc);
	}
	
	return $newDesc;
}


function startsWith($haystack, $needle) 
{
	return strncmp($haystack, $needle, strlen($needle)) === 0;
}


function startsWithNoCase($haystack, $needle)
{
	return strncasecmp($haystack, $needle, strlen($needle)) === 0;
}


function ParseEsoItemLink($itemLink)
{
	$matches = array();
	
	$result = preg_match('/\|H(?P<color>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId1>[0-9]*)\:' .
						 '(?P<enchantSubtype1>[0-9]*)\:(?P<enchantLevel1>[0-9]*)\:(?P<writ1>[0-9]*)\:(?P<writ2>[0-9]*)\:' .
						 '(?P<writ3>[0-9]*)\:(?P<writ4>[0-9]*)\:(?P<writ5>[0-9]*)\:(?P<writ6>[0-9]*)\:(.*?)\:' .
							'(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<stolen>[0-9]*)\:' .
						 '(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h\[?(?P<name>[a-zA-Z0-9 %_\(\)\'\-]*)(?P<nameCode>.*?)\]?\|h/',
						$itemLink, $matches);
	if (!$result) return false;

	return $matches;
}


function CreateEsoMasterWritText($db, $name, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers)
{
	if (stripos($name, "alchemy")) return CreateEsoMasterWritAlchemyText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers);
	if (stripos($name, "blacksmith")) return CreateEsoMasterWritSmithingText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers);
	if (stripos($name, "clothier")) return CreateEsoMasterWritSmithingText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers);
	if (stripos($name, "enchant")) return CreateEsoMasterWritEnchantingText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers);
	if (stripos($name, "provision")) return CreateEsoMasterWritProvisioningText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers);
	if (stripos($name, "woodwork")) return CreateEsoMasterWritSmithingText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers);
	if (stripos($name, "jewelry")) return CreateEsoMasterWritJewelryText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers);
	
	return "";
}


function CreateEsoMasterWritAlchemyText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers)
{
	global $ESO_POTIONEFFECT_DATA;
	
	$itemId = 1;
	$name = "Unknown Potion";
	$properties = "Unknown";
	$isPoison = false;
		
	if ($writ1 == 239) 
	{
		$isPoison = true;
		$name = "Unknown Poison";
		$itemId = 2;
	}
	
	if ($ESO_POTIONEFFECT_DATA != null)
	{
		$props = array();
		$effect1 = $ESO_POTIONEFFECT_DATA[$writ2];
		$effect2 = $ESO_POTIONEFFECT_DATA[$writ3];
		$effect3 = $ESO_POTIONEFFECT_DATA[$writ4];
		
		if ($effect3 != null) 
		{
			$itemId = $isPoison ? $effect3['poisonBaseId'] : $effect3['potionBaseId'];
			$props[] = $effect3['name'];
		}
		
		if ($effect2 != null) 
		{
			$itemId = $isPoison ? $effect2['poisonBaseId'] : $effect2['potionBaseId'];
			$props[] = $effect2['name'];
		}
		
		if ($effect1 != null) 
		{
			$itemId = $isPoison ? $effect1['poisonBaseId'] : $effect1['potionBaseId'];
			$props[] = $effect1['name'];
		}
		
		$properties = implode(", ", $props);
	}
	
	$query = "SELECT name FROM minedItem WHERE itemId='$itemId' AND internalLevel='50' AND internalSubtype='307';";
	$result = $db->query($query);
	
	if ($result !== false && $result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$name = $row['name'];
	}
	
	if ($rawVouchers < 0) $rawVouchers = 0;
	$vouchers = round($rawVouchers/10000);
	
	$text  = "Consume to start quest:\n";
	$text .= FixVowelArticles("Craft a $name with the following properties: ");
	$text .= "$properties\n\n";
	$text .= "Reward: $vouchers|t16:16:esoui/art/currency/currency_writvoucher.dds|t Writ Vouchers";
	
	return $text;
}


function CreateEsoMasterWritEnchantingText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers)
{
	$query = "SELECT name FROM minedItem WHERE itemId='$writ1' AND internalLevel='50' AND internalSubtype='307';";
	$result = $db->query($query);
	$name = "Unknown Glyph";
	
	if ($result !== false && $result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$name = $row['name'];
	}
	
	$quality = GetEsoItemQualityText($writ3);
	
	if ($rawVouchers < 0) $rawVouchers = 0;
	$vouchers = round($rawVouchers/10000);
	
	$text  = "Consume to start quest:\n";
	$text .= FixVowelArticles("Craft a $name;");
	$text .= " Quality: $quality\n\n";
	$text .= "Reward: $vouchers|t16:16:esoui/art/currency/currency_writvoucher.dds|t Writ Vouchers";
	
	return $text;
}


function FixVowelArticles($text)
{
	return preg_replace('/(^| )a ([aeiouAEIOU])/', '$1an $2', $text);
}


function CreateEsoMasterWritProvisioningText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers)
{
	$query = "SELECT name FROM minedItem WHERE itemId='$writ1' AND internalLevel='1' AND internalSubtype='1';";
	$result = $db->query($query);
	$name = "Unknown Food";
	
	if ($result !== false && $result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$name = $row['name'];
	}
	
	if ($rawVouchers < 0) $rawVouchers = 0;
	$vouchers = round($rawVouchers/10000);
	
	$text  = "Consume to start quest:\n";
	$text .= FixVowelArticles("Craft a $name\n\n");
	$text .= " Reward: $vouchers|t16:16:esoui/art/currency/currency_writvoucher.dds|t Writ Vouchers";
		
	return $text;
}


function CreateEsoMasterWritJewelryText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers)
{
	if ($rawVouchers < 0) $rawVouchers = 0;
	$vouchers = round($rawVouchers/10000);
	
	$itemType = "Unknown";
	
	if ($writ1 == 24)
		$itemType = "Ring";
	elseif ($writ1 == 18)
		$itemType = "Necklace";
		
	$quality = GetEsoItemQualityText($writ3);
	$set = GetEsoSetIndexText($writ4);
	$trait = GetEsoItemTraitText($writ5);
		
	if ($rawVouchers < 0) $rawVouchers = 0;
	$vouchers = round($rawVouchers/10000);
	
	$text  = "Consume to start quest:\n";
	$text .= "Craft a $itemType;";
	$text .= " Quality: $quality;";
	$text .= " Trait: $trait;";
	if ($set) $text .= " Set: $set;";
	$text .= " Reward: $vouchers|t16:16:esoui/art/currency/currency_writvoucher.dds|t Writ Vouchers";
	
	return $text;
}


function CreateEsoMasterWritSmithingText($db, $writ1, $writ2, $writ3, $writ4, $writ5, $writ6, $rawVouchers)
{
	static $ESO_SMITHING_CRAFTTYPE = array(
			188 => "Rubedite",
			190 => "Rubedo Leather",
			192 => "Ruby Ash",
			194 => "Ancestor Silk",
	);
	
	static $ESO_SMITHING_MASTERWRIT_TYPES = array(
			17 => "Helmet",
			18 => "Necklace",
			19 => "Chest",
			20 => "Shoulder",
			21 => "Belt",
			22 => "Leg",
			23 => "Feet",
			24 => "Ring",
			25 => "Gloves",
			26 => "Hat",
			27 => "Necklace",
			28 => "Shirt",
			29 => "Epaulets",
			30 => "Sash",
			31 => "Breeches",
			32 => "Shoes",
			33 => "Ring",
			34 => "Gloves",
			35 => "Helmet",
			36 => "Necklace",
			37 => "Jack",
			38 => "Arm Cops",
			39 => "Belt",
			40 => "Guards",
			41 => "Boots",
			42 => "Ring",
			43 => "Bracers",
			44 => "Helm",
			45 => "Necklace",
			46 => "Cuirass",
			47 => "Pauldron",
			48 => "Girdle",
			49 => "Greaves",
			50 => "Sabatons",
			51 => "Ring",
			52 => "Gauntlets",
			53 => "Axe",
			56 => "Mace",
			59 => "Sword",
			62 => "Dagger",
			65 => "Shield",
			66 => "Rune/Off-Hand",
			67 => "Greatsword",
			68 => "Battle Axe",
			69 => "Maul",
			70 => "Bow",
			71 => "Restoration Staff",
			72 => "Inferno Staff",
			73 => "Frost Staff",
			74 => "Lightning Staff",
			75 => "Chest",
			76 => "Bread",
			77 => "Meat",
			78 => "Stew",
			80 => "Wine",
			81 => "Spirits",
			82 => "Beer",
	);
		
	$craftPrefix = "Unknown";
	$itemType = "Unknown";
	
	if ($ESO_SMITHING_CRAFTTYPE[$writ2] != null) $craftPrefix = $ESO_SMITHING_CRAFTTYPE[$writ2];
	if ($ESO_SMITHING_MASTERWRIT_TYPES[$writ1] != null) $itemType = $ESO_SMITHING_MASTERWRIT_TYPES[$writ1];
	
	$quality = GetEsoItemQualityText($writ3);
	$set = GetEsoSetIndexText($writ4);
	$trait = GetEsoItemTraitText($writ5);
	$style = GetEsoItemStyleText($writ6);
	
	if ($rawVouchers < 0) $rawVouchers = 0;
	$vouchers = round($rawVouchers/10000);
	
	$text  = "Consume to start quest:\n";
	$text .= FixVowelArticles("Craft a $craftPrefix $itemType;");
	$text .= " Quality: $quality;";
	$text .= " Trait: $trait;";
	if ($set) $text .= " Set: $set;";
	$text .= " Style: $style\n\n";
	$text .= "Reward: $vouchers|t16:16:esoui/art/currency/currency_writvoucher.dds|t Writ Vouchers";
	
	return $text;
}


function UpdateEsoPageViews($id, $db = null)
{
	global $uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase;
		
	if (!ENABLE_ESO_PAGEVIEW_UPDATES) return false;
	
	$deleteDb = false;
	
	if ($db == null)
	{
		$deleteDb = true;
		$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
		if ($db->connect_error) return false;
	}
	
	$query = "UPDATE uesp_esolog.logInfo SET value=value+1 WHERE id='$id';";
	$result = $db->query($query);
	
	if ($deleteDb) $db->close();
	
	return $result !== false; 
}


function EsoNameMatchUpper($matches)
{
	return strtoupper($matches[0]);
}


function MakeEsoTitleCaseName($name)
{
	$newName = preg_replace("#\^.*#", "", $name);
	$newName = preg_replace("#\|\|.*#", "", $newName);
	
	$newName = ucwords($newName);
	
	$newName = preg_replace("/ In /", " in ", $newName);
	$newName = preg_replace("/, in /", ", In ", $newName);
	
	$newName = preg_replace("/ As /", " as ", $newName);
	$newName = preg_replace("/ Of /", " of ", $newName);
	
	$newName = preg_replace("/ And /", " and ", $newName);
	$newName = preg_replace("/ To /", " to ", $newName);
	
	$newName = preg_replace("/ A /", " a ", $newName);
	$newName = preg_replace("/, a /", ", A ", $newName);
	$newName = preg_replace("/: a /", ": A ", $newName);
	
	$newName = preg_replace("/ At /", " at ", $newName);
	$newName = preg_replace("/: at /", ": At ", $newName);
	
	$newName = preg_replace("/ On /", " on ", $newName);
	$newName = preg_replace("/ With /", " with ", $newName);
	
	$newName = preg_replace("/ The /", " the ", $newName);
	$newName = preg_replace("/, the /", ", The ", $newName);
	$newName = preg_replace("/: the /", ": The ", $newName);
	$newName = preg_replace("/, The Hungering Dark/", ", the Hungering Dark", $newName);
	$newName = preg_replace("/, The Madgod/", ", the Madgod", $newName);
	
	$newName = preg_replace("/ the Rift/", " The Rift", $newName);
	$newName = preg_replace("/ the Reach$/", " The Reach", $newName);
	$newName = preg_replace("/ the Reach /", " The Reach ", $newName);
	$newName = preg_replace("/ the Reach:/", " The Reach:", $newName);
	$newName = preg_replace("/ the Ritual/", " The Ritual", $newName);
	$newName = preg_replace("/ the Shadow/", " The Shadow", $newName);
	$newName = preg_replace("/ the Tower/", " The Tower", $newName);
	$newName = preg_replace("/ the Apprentice/", " The Apprentice", $newName);
	$newName = preg_replace("/ the Heart of Transparent Law/", " The Heart of Transparent Law", $newName);
	$newName = preg_replace("/, Feast of All Flames/", ", Feast of all Flames", $newName);
	
	//$newName = preg_replace("/ the Atronach/", " The Atronach", $newName);
	//$newName = preg_replace("/ the Lady/", " The Lady", $newName);
	//$newName = preg_replace("/ the Lover/", " The Lover", $newName);
	//$newName = preg_replace("/ the Mage/", " The Mage", $newName);
	//$newName = preg_replace("/ the Serpent/", " The Serpent", $newName);
	//$newName = preg_replace("/ the Thief/", " The Thief", $newName);
	//$newName = preg_replace("/ the Warrior/", " The Warrior", $newName);
	//$newName = preg_replace("/ the Ritual/", " The Ritual", $newName);
	//$newName = preg_replace("/ the Shadow/", " The Shadow", $newName);
	//$newName = preg_replace("/ the Tower/", " The Tower", $newName);
	//$newName = preg_replace("/ the Ayleids/", " The Ayleids", $newName);
	//$newName = preg_replace("/ the Dragon's Glare/", " The Dragon's Glare", $newName);
	//$newName = preg_replace("/ the Fish and the Unicorn/", " The Fish and the Unicorn", $newName);
	//$newName = preg_replace("/ the Sea-Monster's Surprise/", " The Sea-Monster's Surprise", $newName);
	//$newName = preg_replace("/ the Taming of the Gryphon/", " The Taming of the Gryphon", $newName);
	//$newName = preg_replace("/ the Dance/", " The Dance", $newName);
	//$newName = preg_replace("/ the Gate/", " The Gate", $newName);
	//$newName = preg_replace("/ the Demon/", " The Demon", $newName);
	//$newName = preg_replace("/ the Gathering/", " The Gathering", $newName);
	//$newName = preg_replace("/ the Open Path/", " The Open Path", $newName);
	//$newName = preg_replace("/ the Webspinner/", " The Webspinner", $newName);
	//$newName = preg_replace("/ the Ghosts of Frostfall/", " The Ghosts of Frostfall", $newName);
	//$newName = preg_replace("/ the Liberation of Leyawiin/", " The Liberation of Leyawiin", $newName);
	//$newName = preg_replace("/ the Mad Harlequin's Reverie/", " The Mad Harlequin's Reverie", $newName);
	//$newName = preg_replace("/ the Merry Meadmaker/", " The Merry Meadmaker", $newName);
	//$newName = preg_replace("/ the Mirefrog's Hymn/", " The Mirefrog's Hymn", $newName);
	//$newName = preg_replace("/ the Shadows Stir/", " The Shadows Stir", $newName);
	//$newName = preg_replace("/ the Mistress of Decay/", " The Mistress of Decay", $newName);
	//$newName = preg_replace("/ the Taskmaster/", " The Taskmaster", $newName);
	//$newName = preg_replace("/ the Dread Father/", " The Dread Father", $newName);
	//$newName = preg_replace("/ the Clockwork God/", " The Clockwork God", $newName);
	
	$newName = preg_replace_callback("/\-[a-z]/", 'EsoNameMatchUpper', $newName);
	$newName = preg_replace_callback("/\[vix]+$/", 'EsoNameMatchUpper', $newName);
	
	$newName = preg_replace("/-And-/", "-and-", $newName);
	$newName = preg_replace("/-Of-/", "-of-", $newName);
	
	$newName = preg_replace("/,$/", "", $newName);	//Trailing commas
	
	if ($newName == "Daedra Worship: The Ayleids") $newName = "Daedra Worship: the Ayleids";
	if ($newName == "From Old Life to New") $newName = "From Old Life To New";
	if ($newName == "Totem of The Reach") $newName = "Totem of the Reach";
	
	return $newName;
}


function IsSessionStarted()
{
	if ( php_sapi_name() !== 'cli' ) {
		if ( version_compare(phpversion(), '5.4.0', '>=') ) {
			return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
		} else {
			return session_id() === '' ? FALSE : TRUE;
		}
	}
	return FALSE;
}


function SetupUespSession()
{
	global $canViewEsoMorrowindPts;
	
	if (!IsSessionStarted())
	{
		if (class_exists("UespMemcachedSession"))
		{
			UespMemcachedSession::install();
		}
		
		session_name('uesp_net_wiki5_session');
	
		$startedSession = session_start();
		//if (!$startedSession) error_log("Failed to start session!");
	}

	if ($_SESSION['uesp_eso_morrowind'] == 654321)
	{
		$canViewEsoMorrowindPts = true;
	}
	
}


function CanViewEsoLogVersion($version)
{
	global $canViewEsoMorrowindPts;
	
	return true;
	
	$tableSuffix = GetEsoItemTableSuffix($version);
	if ($tableSuffix != "14pts") return true;
	
	return $canViewEsoMorrowindPts;
}


function CanViewEsoLogTable($table)
{
	global $canViewEsoMorrowindPts;
	
	return true;
	
	$index = strpos($table, "14pts");
	if ($index === false) return true;
	
	return $canViewEsoMorrowindPts;
}


function GetEsoTransmuteTraitItemId ($trait, $equipType)
{
	global $ESO_ITEMTRANSMUTETRAIT_IDS, $ESO_ITEMTRANSMUTETRAIT_2H_IDS;
	
	$traitId = intval($trait);
	if ($traitId <= 0) return -1;
	
	if ($equipType == 6)
		$itemId = $ESO_ITEMTRANSMUTETRAIT_2H_IDS[$traitId];
	else
		$itemId = $ESO_ITEMTRANSMUTETRAIT_IDS[$traitId];
	
	if ($itemId == null) return -1;
	return $itemId;
}


function LoadEsoTraitDescription ($trait, $intLevel, $intSubtype, $equipType, $db, $version="")
{
	$itemId = GetEsoTransmuteTraitItemId($trait, $equipType);
	if ($itemId < 0) return "Unknown trait $trait found!";
	
	$intLevel = intval($intLevel);
	$intSubtype = intval($intSubtype);
	
	$tableSuffix = GetEsoItemTableSuffix($version);
	
	$query = "SELECT traitDesc from minedItem$tableSuffix WHERE itemId='$itemId' AND internalLevel='$intLevel' AND internalSubtype='$intSubtype' LIMIT 1;";
	
	$result = $db->query($query);
	if ($result === false) return "Failed to load trait description for $trait!"; 
	
	$row = $result->fetch_assoc();
	
	return $row['traitDesc'];
}


function LoadEsoTraitSummaryDescription ($trait, $equipType, $db, $version="")
{
	$itemId = GetEsoTransmuteTraitItemId($trait, $equipType);
	if ($itemId < 0) return "Unknown trait $trait found!";
	
	$tableSuffix = GetEsoItemTableSuffix($version);

	$query = "SELECT traitDesc from minedItemSummary$tableSuffix WHERE itemId=$itemId LIMIT 1;";

	$result = $db->query($query);
	if ($result === false) return "Failed to load trait description for $trait!";

	$row = $result->fetch_assoc();

	return $row['traitDesc'];
}


$uespMemcache = null;


function ConnectUespMemcache()
{
	global $uespMemcache;
	global $UESP_SERVER_MEMCACHED;
	
	if ($uespMemcache != null) return true;
	
	$uespMemcache = new Memcache;
	$result = $uespMemcache->connect($UESP_SERVER_MEMCACHED, 11000);
	
	if (!$result)
	{
		$uespMemcache = null;
		return false;
	}
	
	return true;
}


function GetUespMemcache($objName)
{
	global $uespMemcache;
	
	if (!ConnectUespMemcache()) return false;
	
	return $uespMemcache->get($objName);
}


function SetUespMemcache($objName, $objValue)
{
	global $uespMemcache;
	
	if (!ConnectUespMemcache()) return false;
	
	return $uespMemcache->set($objName, $objValue);
}


function SetUespMemcacheCompress($objName, $objValue)
{
	global $uespMemcache;
	
	if (!ConnectUespMemcache()) return false;
	
	return $uespMemcache->set($objName, $objValue, MEMCACHE_COMPRESSED);
}


function LoadEsoMinedItem($db, $itemId, $internalLevel, $internalSubtype, $tableSuffix = "")
{
	if (!UESP_ESO_USE_COMBINED_MINEDITEM) return LoadEsoMinedItemOld($db, $itemId, $internalLevel, $internalSubtype, $tableSuffix);
	
	$item = LoadEsoMinedItemExact($db, $itemId, $internalLevel, $internalSubtype, $tableSuffix);
	if ($item) return $item;
	
	$item = LoadEsoMinedItemExact($db, $itemId, 1, 1, $tableSuffix);
	if ($item) return $item;
	
	return false;
}


function LoadEsoMinedItemExact($db, $itemId, $internalLevel, $internalSubtype, $tableSuffix = "")
{
	if (!UESP_ESO_USE_COMBINED_MINEDITEM) return LoadEsoMinedItemExactOld($db, $itemId, $internalLevel, $internalSubtype, $tableSuffix);
	
	$itemId = intval($itemId);
	$internalLevel = intval($internalLevel);
	$internalSubtype = intval($internalSubtype);
	
	$minedTable = "minedItem$tableSuffix";
	$summaryTable = "minedItemSummary$tableSuffix";
	
	$query = "SELECT $summaryTable.*, $minedTable.* FROM $minedTable LEFT JOIN $summaryTable ON $minedTable.itemId=$summaryTable.itemId WHERE $minedTable.itemId='$itemId' AND $minedTable.internalLevel='$internalLevel' AND $minedTable.internalSubtype='$internalSubtype';";
	$result = $db->query($query);
	if ($result === false) return false;
	if ($result->num_rows <= 0) return false;
	
	$item = $result->fetch_assoc();
	
	$item['link'] = "|H0:item:$itemId:$internalSubtype:$internalLevel:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|h|h";
	return $item;
}


function LoadEsoMinedItemOld($db, $itemId, $internalLevel, $internalSubtype, $tableSuffix = "")
{
	$item = LoadEsoMinedItemExactOld($db, $itemId, $internalLevel, $internalSubtype, $tableSuffix);
	if ($item) return $item;
	
	$item = LoadEsoMinedItemExactOld($db, $itemId, 1, 1, $tableSuffix);
	if ($item) return $item;
	
	return false;
}


function LoadEsoMinedItemExactOld($db, $itemId, $internalLevel, $internalSubtype, $tableSuffix = "")
{
	$itemId = intval($itemId);
	$internalLevel = intval($internalLevel);
	$internalSubtype = intval($internalSubtype);
	
	$query = "SELECT * FROM minedItem$tableSuffix WHERE itemId='$itemId' AND internalLevel='$internalLevel' AND internalSubtype='$internalSubtype';";
	$result = $db->query($query);
	if ($result === false) return false;
	if ($result->num_rows <= 0) return false;
	
	$item = $result->fetch_assoc();
	return $item;
}