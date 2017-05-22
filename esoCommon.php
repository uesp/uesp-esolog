<?php

require_once("UespMemcachedSession.php");

$canViewEsoMorrowindPts = false;


const UESP_ESO_ICON_URL = "//esoicons.uesp.net";

const ENABLE_ESO_PAGEVIEW_UPDATES = true;

	/* Make sure these match values used in uespLog */
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


$ESO_CRAFTTYPES = array(
		-1 => "",
		0 => "",
		1 => "Blacksmithing",
		2 => "Clothier",
		3 => "Enchanting",
		4 => "Alchemy",
		5 => "Provisioning",
		6 => "Woodworking",		
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
		39 => "Minotaur",
		40 => "Ebony",
		41 => "Abah's Watch",
		42 => "Skinchanger",
		44 => "Ra Gada",
		45 => "Dro-m'Athra",
		46 => "Assassin's League",
		47 => "Outlaw",
		53 => "Shalhrim Frostcaster",
		56 => "Silken Ring",
		57 => "Mazzatun",
		58 => "Grim Arlequin",
		59 => "Hollowjack",
);


$ESO_ITEMQUALITY_TEXTS = array(
		-1 => "",
		0 => "Trash",
		1 => "Normal",
		2 => "Fine",
		3 => "Superior",
		4 => "Epic",
		5 => "Legendary",
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
		11 => "Additive",
		2 => "Armor",
		24 => "Armor Booster",
		45 => "Armor Trait",
		47 => "Ava Repair",
		41 => "Blacksmith Booster",
		36 => "Blacksmith Material",
		35 => "Blacksmith Raw Material",
		43 => "Clothier Booster",
		40 => "Clothier Material",
		39 => "Clothier Raw Material",
		34 => "Collectible",
		18 => "Container",
		13 => "Costume",
		14 => "Disguise",
		12 => "Drink",
		59 => "Dye Stamp",
		32 => "Enchanting Rune",
		25 => "Enchantment Booster",
		28 => "Flavoring",
		4 => "Food",
		61 => "Furnishing",
		21 => "Armor Glyph",
		26 => "Jewelry Glyph",
		20 => "Weapon Glyph",
		10 => "Ingredient",
		22 => "Lockpick",
		16 => "Lure",
		60 => "Master Writ",		// Update 13
		0 => "",
		3 => "Plug",
		30 => "Poison",
		58 => "Poison Base",		// New in update 10
		7 => "Potion",
		33 => "Potion Base",		// Used to be Alchemy Base prior to update 10
		17 => "Raw Material",
		31 => "Reagent",
		29 => "Recipe",
		8 => "Motif",
		6 => "Siege",
		19 => "Soul Gem",
		27 => "Spice",
		44 => "Style Material",
		15 => "Tabard",
		9 => "Tool",
		48 => "Trash",
		5 => "Trophy",
		1 => "Weapon",
		23 => "Weapon Booster",
		46 => "Weapon Trait",
		42 => "Woodwork Booster",
		38 => "Woodwork Material",
		37 => "Woodwork Raw Material",
		49 => "Spellcrafting Tablet",
		50 => "Mount",
		51 => "Potency Rune",
		52 => "Aspect Rune",
		53 => "Essence Rune",
		54 => "Fish",
		55 => "Crown Repair",
		56 => "Treasure",
		57 => "Crown Store",
);


	// Added in Update 13
$ESO_ITEMSPECIALTYPE_TEXTS = array(
		-1 => "",
		0 => "",
		1 => "Meat Dish",
		100 => "Treasure Map",
		1000 => "Armor Glyph",
		101 => "Survey Report",
		102 => "Key Fragment",
		103 => "Museum Piece",
		104 => "Recipe Fragment",
		105 => "Scroll",
		1050 => "Lockpick",
		106 => "Material Upgrader",
		107 => "Key",
		1100 => "Weapon Booster",
		1150 => "Armor Booster",
		1200 => "Enchantment Booster",
		1250 => "Jewelry Glyph",
		1300 => "Spice",
		1350 => "Flavoring",
		1400 => "Poison",
		1450 => "Potion Solvent",
		150 => "Herb",
		1500 => "Raw Material",
		151 => "Fungus",
		152 => "Animal Parts",
		1550 => "Material",
		1600 => "Raw Material",
		1650 => "Material",
		170 => "Food Recipe",
		1700 => "Raw Material",
		171 => "Drink Recipe",
		172 => "Furnishing Diagram",
		173 => "Furnishing Pattern",
		174 => "Furnishing Schematic",
		175 => "Furnishing Formula",
		1750 => "Material",
		176 => "Furnishing Design",
		177 => "Furnishing Blueprint",
		1800 => "Temper",
		1850 => "Resin",
		1900 => "Tannin",
		1950 => "Style Material",
		2 => "Fruit Dish",
		20 => "Alcoholic Beverage",
		2000 => "Armor Trait",
		2050 => "Weapon Trait",
		21 => "Tea Beverage",
		210 => "Furnishing",
		2100 => "AvA Repair",
		211 => "Light",
		212 => "Seating",
		213 => "Crafting Station",
		214 => "Target Dummy",
		2150 => "Trash",
		22 => "Tonic Beverage",
		2200 => "Tablet",
		2250 => "Mount",
		23 => "Liqueur Beverage",
		2300 => "Potency Runestone",
		2350 => "Aspect Runestone",
		24 => "Tincture Beverage",
		2400 => "Essence Runestone",
		2450 => "Fish",
		25 => "Cordial Tea Beverage",
		250 => "Weapon",
		2500 => "Crown Repair",
		2550 => "Treasure",
		26 => "Distillate Beverage",
		2600 => "Crown Item",
		2650 => "Poison Solvent",
		27 => "Drink",
		2700 => "Dye Stamp",
		2750 => "Master Writ",
		3 => "Vegetable Dish",
		300 => "Armor",
		350 => "Augment",
		4 => "Savoury Dish",
		40 => "Meat Ingredient",
		400 => "Trebuchet",
		401 => "Ballista",
		402 => "Ram",
		403 => "Universal Siege",
		404 => "Catapult",
		405 => "Forward Camp",
		406 => "Monster",
		407 => "Oil",
		408 => "Battle Standard",
		41 => "Vegetable Ingredient",
		42 => "Fruit Ingredient",
		43 => "Food Additive",
		44 => "Alcohol Ingredient",
		45 => "Tea Ingredient",
		450 => "Potion",
		46 => "Tonic Ingredient",
		47 => "Drink Additive",
		48 => "Rare Ingredient",
		5 => "Ragout Dish",
		500 => "Tool",
		550 => "Additive",
		6 => "Entremet Dish",
		60 => "Motif Book",
		600 => "Costume",
		61 => "Motif Chapter",
		650 => "Disguise",
		7 => "Gourmet Dish",
		700 => "Tabard",
		750 => "Lure",
		8 => "Unique Dish",
		80 => "Rare Fish",
		800 => "Raw Material",
		81 => "Monster Trophy",
		850 => "Container",
		900 => "Soul Gem",
	950 => "Weapon Glyph",
);


$ESO_ITEMSPECIALTYPE_RAW_TEXTS = array(
		-1 => "",
		0 => "",
		550 => "Additive",
		300 => "Armor",
		1150 => "Booster",
		2000 => "Trait",
		2100 => "Repair",
		1800 => "Booster",
		1550 => "Material",
		1500 => "Raw Material",
		1900 => "Clothier Booster",
		1750 => "Material",
		1700 => "Raw Material",
		81 => "Monster Trophy",
		80 => "Rare Fish",
		850 => "Container",
		600 => "Costume",
		2600 => "Crown Item",
		2500 => "Crown Repair",
		650 => "Disguise",
		20 => "Alcoholic",
		25 => "Cordial Tea",
		26 => "Distillate",
		23 => "Liqueur",
		21 => "Tea",
		24 => "Tincture",
		22 => "Tonic",
		27 => "Unique",
		2700 => "Dye Stamp",
		2350 => "Rune Aspect",
		2400 => "Rune Essence",
		2300 => "Rune Potency",
		1200 => "Booster",
		2450 => "Fish",
		1350 => "Flavoring",
		6 => "Entremet",
		2 => "Fruit",
		7 => "Gourmet",
		1 => "Meat",
		5 => "Ragout",
		4 => "Savoury",
		8 => "Unique",
		3 => "Vegetable",
		213 => "Crafting Station",
		211 => "Light",
		210 => "Ornamental",
		212 => "Seating",
		214 => "Target Dummy",
		1000 => "Armor",
		1250 => "Jewelry",
		950 => "Weapon",
		44 => "Alcohol",
		47 => "Drink Additive",
		43 => "Food Additive",
		42 => "Fruit",
		40 => "Meat",
		48 => "Rare",
		45 => "Tea",
		46 => "Tonic",
		41 => "Vegetable",
		1050 => "Lockpick",
		750 => "Lure",
		2750 => "Master Writ",
		2750 => "Max Value",
		0 => "Min Value",
		2250 => "Mount",
		0 => "None",
		350 => "Plug",
		1400 => "Poison",
		2650 => "Poison Base",
		450 => "Potion",
		1450 => "Potion Base",
		60 => "Motif Book",
		61 => "Motif Chapter",
		800 => "Raw Material",
		152 => "Animal Part",
		151 => "Fungus",
		150 => "Herb",
		175 => "Alchemy Furnishing",
		172 => "Blacksmithing Furnishing",
		173 => "Clothier Furnishing",
		174 => "Enchanting Furnishing",
		176 => "Provisioning Furnishing",
		171 => "Provisioning Drink",
		170 => "Provisioning Food",
		177 => "Woodworking Furnishing",
		401 => "Ballista",
		408 => "Battle Standard",
		404 => "Catapult",
		405 => "Graveyard",
		406 => "Monster",
		407 => "Oil",
		402 => "Ram",
		400 => "Trebuchet",
		403 => "Universal",
		900 => "Soul Gem",
		2200 => "Spellcrafting Tablet",
		1300 => "Spice",
		1950 => "Style Material",
		700 => "Tabard",
		500 => "Tool",
		2150 => "Trash",
		2550 => "Treasure",
		107 => "Key",
		102 => "Key Fragment",
		106 => "Material Upgrader",
		103 => "Museum Piece",
		104 => "Recipe Fragment",
		105 => "Scroll",
		101 => "Survey Report",
		100 => "Treasure Map",
		250 => "Weapon",
		1100 => "Booster",
		2050 => "Trait",
		1850 => "Booster",
		1650 => "Material",
		1600 => "Raw Material",
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
		163 => "Blood Spawn",
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
		204 => "Endurance Mini-Set",
		205 => "Willpower Mini-Set",
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
		227 => "Bahraha's Curse (aka Bahara)",
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
		242 => "Pelinal's Aptitude",
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
		312 => "Volenfell Tremorscale Mask",
		313 => "Masters Duel Wield",
		314 => "Masters Two Handed",
		315 => "Masters One Hand and Shield",
		316 => "Masters Destruction Staff",
		317 => "Masters Bow",
		318 => "Masters Restoration Staff",
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
);


$ESO_MAPPINTYPE_TEXTS = array(
		-1 => "",
		0 => "Player",
		1 => "Group",
		2 => "Group Leader",
		3 => "Quest Offer",
		4 => "Quest Offer Repeatable",
		5 => "Quest Complete",
		6 => "Quest Talk To",
		7 => "Quest Interact",
		8 => "Quest Give Item",
		9 => "Assisted Quest Condition",
		10 => "Assisted Quest Optional Condition",
		11 => "Assisted Quest Ending",
		12 => "Assisted Quest Repeatable Condition",
		13 => "Assisted Quest Repeatable Optional Condition",
		14 => "Assisted Quest Repeatable Ending",
		15 => "Tracked Quest Condition",
		16 => "Tracked Quest Optional Condition",
		17 => "Tracked Quest Ending",
		18 => "Tracked Quest Repeatable Condition",
		19 => "Tracked Quest Repeatable Optional Condition",
		20 => "Tracked Quest Repeatable Ending",
		21 => "Poi Seen",
		22 => "Poi Complete",
		23 => "Flag Aldmeri Dominion",
		24 => "Flag Ebonheart Pact",
		25 => "Flag Daggerfall Covenant",
		26 => "Flag Neutral",
		27 => "Flag Base Aldmeri Dominion",
		28 => "Flag Base Ebonheart Pact",
		29 => "Flag Base Daggerfall Covenant",
		30 => "Flag Base Neutral",
		31 => "Return Aldmeri Dominion",
		32 => "Return Ebonheart Pact",
		33 => "Return Daggerfall Covenant",
		34 => "Return Neutral",
		35 => "Ball Aldmeri Dominion",
		36 => "Ball Ebonheart Pact",
		37 => "Ball Daggerfall Covenant",
		38 => "Ball Neutral",
		39 => "Capture Flag Aldmeri Dominion",
		40 => "Capture Flag Ebonheart Pact",
		41 => "Capture Flag Daggerfall Covenant",
		42 => "Capture Flag Neutral",
		43 => "Half Capture Flag Aldmeri Dominion",
		44 => "Half Capture Flag Ebonheart Pact",
		45 => "Half Capture Flag Daggerfall Covenant",
		46 => "Artifact Aldmeri Offensive",
		47 => "Artifact Aldmeri Defensive",
		48 => "Artifact Ebonheart Offensive",
		49 => "Artifact Ebonheart Defensive",
		50 => "Artifact Daggerfall Offensive",
		51 => "Artifact Daggerfall Defensive",
		52 => "Artifact Return Aldmeri",
		53 => "Artifact Return Daggerfall",
		54 => "Artifact Return Ebonheart",
		55 => "Keep Neutral",
		56 => "Keep Aldmeri Dominion",
		57 => "Keep Ebonheart Pact",
		58 => "Keep Daggerfall Covenant",
		59 => "Outpost Neutral",
		60 => "Outpost Aldmeri Dominion",
		61 => "Outpost Ebonheart Pact",
		62 => "Outpost Daggerfall Covenant",
		63 => "Farm Neutral",
		64 => "Farm Aldmeri Dominion",
		65 => "Farm Ebonheart Pact",
		66 => "Farm Daggerfall Covenant",
		67 => "Mine Neutral",
		68 => "Mine Aldmeri Dominion",
		69 => "Mine Ebonheart Pact",
		70 => "Mine Daggerfall Covenant",
		71 => "Mill Neutral",
		72 => "Mill Aldmeri Dominion",
		73 => "Mill Ebonheart Pact",
		74 => "Mill Daggerfall Covenant",
		75 => "Border Keep Aldmeri Dominion",
		76 => "Border Keep Ebonheart Pact",
		77 => "Border Keep Daggerfall Covenant",
		78 => "Artifact Keep Aldmeri Dominion",
		79 => "Artifact Keep Ebonheart Pact",
		80 => "Artifact Keep Daggerfall Covenant",
		81 => "Artifact Gate Open Aldmeri Dominion",
		82 => "Artifact Gate Open Ebonheart Pact",
		83 => "Artifact Gate Open Daggerfall Covenant",
		84 => "Artifact Gate Closed Aldmeri Dominion",
		85 => "Artifact Gate Closed Ebonheart Pact",
		86 => "Artifact Gate Closed Daggerfall Covenant",
		87 => "Imperial District Neutral",
		88 => "Imperial District Aldmeri Dominion",
		89 => "Imperial District Ebonheart Pact",
		90 => "Imperial District Daggerfall Covenant",
		91 => "Ava Town Neutral",
		92 => "Ava Town Aldmeri Dominion",
		93 => "Ava Town Ebonheart Pact",
		94 => "Ava Town Daggerfall Covenant",
		95 => "Keep Attacked Large",
		96 => "Keep Attacked Small",
		97 => "Flag Attacked",
		98 => "Location",
		99 => "Harvest Node",
		100 => "Vendor",
		101 => "Trainer",
		102 => "Npc Follower",
		103 => "Ping",
		104 => "Rally Point",
		105 => "Player Waypoint",
		106 => "Tri Battle Small",
		107 => "Tri Battle Medium",
		108 => "Tri Battle Large",
		109 => "Aldmeri Vs Ebonheart Small",
		110 => "Aldmeri Vs Ebonheart Medium",
		111 => "Aldmeri Vs Ebonheart Large",
		112 => "Aldmeri Vs Daggerfall Small",
		113 => "Aldmeri Vs Daggerfall Medium",
		114 => "Aldmeri Vs Daggerfall Large",
		115 => "Ebonheart Vs Daggerfall Small",
		116 => "Ebonheart Vs Daggerfall Medium",
		117 => "Ebonheart Vs Daggerfall Large",
		118 => "Fast Travel Keep Accessible",
		119 => "Fast Travel Border Keep Accessible",
		120 => "Fast Travel Outpost Accessible",
		121 => "Fast Travel Wayshrine",
		122 => "Fast Travel Wayshrine Undiscovered",
		123 => "Fast Travel Wayshrine Current Loc",
		124 => "Forward Camp Aldmeri Dominion",
		125 => "Forward Camp Ebonheart Pact",
		126 => "Forward Camp Daggerfall Covenant",
		127 => "Forward Camp Accessible",
		128 => "Keep Graveyard Accessible",
		129 => "Respawn Border Keep Accessible",
		130 => "Imperial District Graveyard Accessible",
		131 => "Ava Town Graveyard Accessible",
		132 => "Imperial City Open",
		133 => "Imperial City Closed",
		134 => "Restricted Link Aldmeri Dominion",
		135 => "Restricted Link Ebonheart Pact",
		136 => "Restricted Link Daggerfall Covenant",
		137 => "Aggro",
		138 => "Timely Escape Npc",
		139 => "Dark Brotherhood Target",
		140 => "Player Camera",
		141 => "Count",
		142 => "Invalid",
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
);


$ESO_QUESTREWARDITEMTYPE_TEXTS = array(
		-1 => "",
		0 => "Item",
		1 => "Collectible",
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
	51 => array(1, 125, 135, 145, 155, 156),
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
);			//	1  360  361  362  363  364  // Jewelry?



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
	
	foreach ($ESO_ITEMQUALITYLEVEL_INTTYPEMAP as $level => $map) 
	{
		if ($level == $inLevel) return $map;
		if ($level >  $inLevel) return $lastMap;
		
		$lastMap = $map;
	}
	
	return array(1, 1, 1, 1, 1, 1);
}


function GetEsoItemTraitFullText($trait, $version = "")
{
	global $ESO_ITEMTRAIT_FULLTEXTS;
	global $ESO_ITEMTRAIT10_FULLTEXTS;
	
	$key = (int) $trait;
	
	if (IsEsoVersionAtLeast($version, 10))
	{
		if (array_key_exists($key, $ESO_ITEMTRAIT10_FULLTEXTS)) return $ESO_ITEMTRAIT10_FULLTEXTS[$key];
		return "Unknown ($key)";
	}
	
	if (array_key_exists($key, $ESO_ITEMTRAIT_FULLTEXTS)) return $ESO_ITEMTRAIT_FULLTEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoItemCraftTypeText($value)
{
	global $ESO_CRAFTTYPES;

	$key = (int) $value;
	if (array_key_exists($key, $ESO_CRAFTTYPES)) return $ESO_CRAFTTYPES[$key];
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
	
	$key = (int) $trait;
	
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


function GetEsoMechanicTypeText($mechanicType)
{
	global $ESO_MECHANIC_TEXTS;

	$key = (int) $mechanicType;
	if (array_key_exists($key, $ESO_MECHANIC_TEXTS)) return $ESO_MECHANIC_TEXTS[$key];
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


function GetEsoQuestRewardTypeText($value)
{
	global $ESO_QUESTREWARDTYPE_TEXTS;

	$key = (int) $value;
	if (array_key_exists($key, $ESO_QUESTREWARDTYPE_TEXTS)) return $ESO_QUESTREWARDTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoQuestRewardItemTypeText($value)
{
	global $ESO_QUESTREWARDITEMTYPE_TEXTS;

	$key = (int) $value;
	if (array_key_exists($key, $ESO_QUESTREWARDITEMTYPE_TEXTS)) return $ESO_QUESTREWARDITEMTYPE_TEXTS[$key];
	return "Unknown ($key)";
}


function GetEsoCurrencyChangeReasonText($value)
{
	global $ESO_CURRENCYCHANGEREASON_TEXTS;

	$key = (int) $value;
	if (array_key_exists($key, $ESO_CURRENCYCHANGEREASON_TEXTS)) return $ESO_CURRENCYCHANGEREASON_TEXTS[$key];
	return "Unknown ($key)";
}



function GetEsoCustomMechanicTypeText($mechanicType)
{
	static $VALUES = array(
			UESP_POWERTYPE_SOULTETHER => "Ulimate (ignore WD)",
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
	);

	$key = (int) $mechanicType;
	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	
	return GetEsoMechanicTypeText($mechanicType);
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
	$icon = preg_replace("#\.dds#", ".png", $icon);
	if ($icon[0] == '/') return UESP_ESO_ICON_URL . $icon;
	return UESP_ESO_ICON_URL . "/" . $icon;
}


function IsEsoVersionAtLeast($version, $checkVersion)
{
	if ($version === null || $version == "" || $version == GetEsoUpdateVersion())
		$suffix = GetEsoUpdateVersion();
	else
		$suffix = intval(GetEsoItemTableSuffix($version));
	
	return ($suffix >= $checkVersion);
}


function GetEsoUpdateVersion()
{
	return 13;
}


function GetEsoItemTableSuffix($version)
{

	switch ($version)
	{
		case '1.5':
		//case '15':
		case '5':
			return "5";
		case '1.6':
		//case '16':
		case '6':
			return "6";
		case '1.7':
		//case '17':
		case '7':
			return "7";
		case '1.8':
		//case '18':
		case '8':
			return "8";
		case '1.8pts':
		case '18pts':
			return "8pts";					
		case '1.9pts':
		case '19pts':
			return "9pts";
		case '1.9':
		//case '19':
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
			return "";
		case '1.4pts':
		case '114pts':
		case '14pts':
			return "14pts";
		case '1.4':
		case '114':
		case '14':
			return "14";
	}

	return "";
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
	$output = preg_replace("#\|c([0-9a-fA-F]{6})([^|]+)\|r#s", "<div style='color:#$1;display:inline;'>$2</div>", $output);
	
		//|t32:32:EsoUI/Art/UnitFrames/target_veteranRank_icon.dds|t
		//EsoUI/Art/champion/champion_icon.dds
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*champion_icon\.dds)\|t#s", "CP ", $output);
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|trank #s", "VR ", $output);
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|t#s", "", $output);
	
	$output = str_replace("\n", "<br />", $output);

	return $output;
}


function FormatRemoveEsoItemDescriptionText($desc)
{
	$output = preg_replace("#\|c([0-9a-fA-F]{6})([^|]+)\|r#s", "$2", $desc);
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*champion_icon\.dds)\|t#s", "CP ", $output);
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|trank #s", "VR ", $output);
	$output = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|t#s", "", $output);
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
	return strncmp($haystack, $needle, count($needle));
}


function startsWithNoCase($haystack, $needle)
{
	return strncasecmp($haystack, $needle, count($needle));
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
	
	$query = "SELECT * FROM minedItem WHERE itemId='$itemId' AND internalLevel='50' AND internalSubtype='307';";
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
	$query = "SELECT * FROM minedItem WHERE itemId='$writ1' AND internalLevel='50' AND internalSubtype='307';";
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
	$query = "SELECT * FROM minedItem WHERE itemId='$writ1' AND internalLevel='1' AND internalSubtype='1';";
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
	$text .= "Reward: $vouchers|t16:16:esoui/art/currency/currency_writvoucher.dds|t Writ Vouchers";
		
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
	$newName = preg_replace("/ Of /", " of ", $newName);
	$newName = preg_replace("/ The /", " the ", $newName);
	$newName = preg_replace("/ And /", " and ", $newName);
	$newName = preg_replace_callback("/\-[a-z]/", 'EsoNameMatchUpper', $newName);
	$newName = preg_replace_callback("/\[vix]+$/", 'EsoNameMatchUpper', $newName);
	
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