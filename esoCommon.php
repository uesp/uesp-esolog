<?php

const UESP_ESO_ICON_URL = "http://esoicons.uesp.net";


const UESP_POWERTYPE_SOULTETHER    = -50;
const UESP_POWERTYPE_LIGHTARMOR    = -51;
const UESP_POWERTYPE_MEDIUMARMOR   = -52;
const UESP_POWERTYPE_HEAVYARMOR    = -53;
const UESP_POWERTYPE_WEAPONDAGGER  = -54;
const UESP_POWERTYPE_ARMORTYPE     = -55;
const UESP_POWERTYPE_DAMAGE        = -56;
const UESP_POWERTYPE_ASSASSINATION = -57;

	// TODO: Change to true when DB is released
const UESP_SHOWCPLEVEL = true;


$APIVERSION_TO_GAMEUPDATE = array(
		"100010" => "5",
		"100011" => "6",
		"100012" => "7",
		"100013" => "8",
		"100014" => "9",
		"100015" => "10",
);


$APIVERSION_TO_GAMEVERSION = array(
		"100010" => "1.5",
		"100011" => "2.0",
		"100012" => "2.1",
		"100013" => "2.2",
		"100014" => "2.3",
		"100015" => "2.4",
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
		13 => "Malacath",
		14 => "Dwemer",
		15 => "Ancient Elf",
		16 => "Imperial",
		17 => "Reach",
		18 => "Bandit",
		19 => "Primitive",
		20 => "Daedric",
		21 => "Trinimac",
		22 => "Ancient Orc",
		23 => "Daggerfall",
		24 => "Ebonheart",
		25 => "Aldmeri",
		26 => "Mercenary",
		27 => "Battlemage Class",
		28 => "Glass",
		29 => "Xivkyn",
		30 => "Soul Shriven",
		31 => "Draugr",
		32 => "Maormer",
		33 => "Akaviri",
		34 => "Imperial",
		35 => "Yokudan",
		36 => "Universal",
		41 => "Abah's Watch",
		46 => "Assassin's League",
		47 => "Outlaw",
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
		32 => "Enchanting Rune",
		25 => "Enchantment Booster",
		28 => "Flavoring",
		4 => "Food",
		21 => "Armor Glyph",
		26 => "Jewelry Glyph",
		20 => "Weapon Glyph",
		10 => "Ingredient",
		22 => "Lockpick",
		16 => "Lure",
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


$ESO_ITEMINTTYPE_QUALITYMAP = array(
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



function GetEsoLevelFromIntType($intType)
{
	global $ESO_ITEMINTTYPE_LEVELMAP;
	
	if (!array_key_exists($intType, $ESO_ITEMINTTYPE_LEVELMAP)) return 1;
	return $ESO_ITEMINTTYPE_LEVELMAP[$intType];
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
	);

	$key = (int) $mechanicType;
	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	
	return GetEsoMechanicTypeText($mechanicType);
}


function GetEsoSkillTypeText ($value)
{
	static $VALUES = array(
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

	$key = (int) $value;

	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "Unknown ($key)";
}


function GetEsoAttributeText($attribute)
{
	global $ESO_ATTRIBUTES;
	$attribute = intval($attribute);
	if (array_key_exists($attribute, $ESO_ATTRIBUTES)) return $ESO_ATTRIBUTES[$attribute];
	return "Unknown ($attribute)";
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
	if ($version == null || $version == "")
		$suffix = GetEsoUpdateVersion();
	else
		$suffix = intval(GetEsoItemTableSuffix($version));
	
	return ($suffix >= $checkVersion);
}


function GetEsoUpdateVersion()
{
	return 10;	
}


function GetEsoItemTableSuffix($version)
{

	switch ($version)
	{
		case '1.5':
		case '15':
		case '5':
			return "5";
		case '1.6':
		case '16':
		case '6':
			return "6";
		case '1.7':
		case '17':
		case '7':
			return "7";
		case '1.8':
		case '18':
		case '8':
			return "8";
		case '1.8pts':
		case '18pts':
			return "8pts";					
		case '1.9pts':
		case '19pts':
			return "9pts";
		case '1.9':
		case '19':
		case '9':
			return "9";
		case '1.10pts':
		case '10pts':
			return "10pts";
		case '1.10':
		case '110':
		case '10':
			return "";
	}

	return "";
}


function FormatEsoItemDescriptionIcons($desc)
{
		//|t32:32:EsoUI/Art/UnitFrames/target_veteranRank_icon.dds|t
	$output = strtolower($desc);
	$output = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*)\.dds\|t#s", "<img src='" . UESP_ESO_ICON_URL . "$3.png' />", $output);

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
	$output = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*champion_icon\.dds)\|t#s", "CP ", $output);
	$output = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*)\|trank #s", "VR ", $output);
	$output = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*)\|t#s", "", $output);
	
	$output = str_replace("\n", "<br />", $output);

	return $output;
}


function FormatRemoveEsoItemDescriptionText($desc)
{
	$output = preg_replace("#\|c([0-9a-fA-F]{6})([^|]+)\|r#s", "$2", $desc);
	$output = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*champion_icon\.dds)\|t#s", "CP ", $output);
	$output = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*)\|trank #s", "VR ", $output);
	$output = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*)\|t#s", "", $output);
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