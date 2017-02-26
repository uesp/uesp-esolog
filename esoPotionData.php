<?php

const ESO_POTIONEFFECT_RESTOREHEALTH = 1;
const ESO_POTIONEFFECT_RAVAGEHEALTH = 2;
const ESO_POTIONEFFECT_RESTOREMAGICKA = 3;
const ESO_POTIONEFFECT_RAVAGEMAGICKA = 4;
const ESO_POTIONEFFECT_RESTORESTAMINA = 5;
const ESO_POTIONEFFECT_RAVAGESTAMINA = 6;
const ESO_POTIONEFFECT_SPELLRESIST = 7;
const ESO_POTIONEFFECT_BREACH = 8;
const ESO_POTIONEFFECT_LOWERSPELLRESIST = 8;
const ESO_POTIONEFFECT_ARMOR = 9;
const ESO_POTIONEFFECT_PHYSICALRESIST = 9;
const ESO_POTIONEFFECT_FRACTURE = 10;
const ESO_POTIONEFFECT_LOWERARMOR = 10;
const ESO_POTIONEFFECT_LOWERPHYSICALRESIST = 10;
const ESO_POTIONEFFECT_SPELLPOWER = 11;
const ESO_POTIONEFFECT_LOWERSPELLPOWER = 12;
const ESO_POTIONEFFECT_COWARDICE = 12;
const ESO_POTIONEFFECT_WEAPONPOWER = 13;
const ESO_POTIONEFFECT_MAIM = 14;
const ESO_POTIONEFFECT_LOWERWEAPONPOWER = 14;
const ESO_POTIONEFFECT_SPELLCRIT = 15;
const ESO_POTIONEFFECT_UNCERTAINTY = 16;
const ESO_POTIONEFFECT_LOWERSPELLCRIT = 16;
const ESO_POTIONEFFECT_WEAPONCRIT = 17;
const ESO_POTIONEFFECT_ENERVATION = 18;
const ESO_POTIONEFFECT_LOWERWEAPONCRIT = 18;
const ESO_POTIONEFFECT_UNSTOPPABLE = 19;
const ESO_POTIONEFFECT_STUN = 20;
const ESO_POTIONEFFECT_DETECTION = 21;
const ESO_POTIONEFFECT_INVISIBLE = 22;
const ESO_POTIONEFFECT_SPEED = 23;
const ESO_POTIONEFFECT_HINDRANCE = 24;
const ESO_POTIONEFFECT_SLOW = 24;
const ESO_POTIONEFFECT_REDUCESPEED = 24;
const ESO_POTIONEFFECT_PROTECTION = 25;
const ESO_POTIONEFFECT_VULNERABILITY = 26;
const ESO_POTIONEFFECT_SUSTAINRESTOREHEALTH = 27;
const ESO_POTIONEFFECT_GRADUALRAVAGEHEALTH = 28;
const ESO_POTIONEFFECT_CREEPINGRAVAGEHEALTH = 28;
const ESO_POTIONEFFECT_VITALITY = 29;
const ESO_POTIONEFFECT_DEFILE = 30;


$ESO_POTIONEFFECT_DATA = array(
	1 => array(
				"id" => 1,
				"oppositeId" => 2,
				"name" => "Restore Health",
				"name2" => "Drain Health",
				"potionBaseId" => 54339,
				"poisonBaseId" => 76827,
				"icon" => "resources/crafting_alchemy_trait_restorehealth.png",
				"isPositive" => true,
			),
	2 =>  array(
				"id" => 2,
				"oppositeId" => 1,
				"name" => "Ravage Health",
				"name2" => "Poison Damage",
				"potionBaseId" => 44812,
				"poisonBaseId" => 76826,
				"icon" => "resources/crafting_alchemy_trait_ravagehealth.png",
				"isPositive" => false,
			),
	3 =>  array(
				"id" => 3,
				"oppositeId" => 4,
				"name" => "Restore Magicka",
				"name2" => "Drain Magicka",
				"potionBaseId" => 54340,
				"poisonBaseId" => 76829,
				"icon" => "resources/crafting_alchemy_trait_restoremagicka.png",
				"isPositive" => true,
			),
	4 =>  array(
				"id" => 4,
				"oppositeId" => 3,
				"name" => "Ravage Magicka",
				"name2" => "Increase Magicka Cost",
				"potionBaseId" => 44815,
				"poisonBaseId" => 76828,
				"icon" => "resources/crafting_alchemy_trait_ravagemagicka.png",
				"isPositive" => false,
			),
	5 =>  array(
				"id" => 5,
				"oppositeId" => 6,
				"name" => "Restore Stamina",
				"name2" => "Drain Stamina",
				"potionBaseId" => 54341,
				"poisonBaseId" => 76829,
				"icon" => "resources/crafting_alchemy_trait_restorestamina.png",
				"isPositive" => true,
			),
	6 =>  array(
				"id" => 6,
				"oppositeId" => 5,
				"name" => "Ravage Stamina",
				"name2" => "Increase Stamina Cost",
				"potionBaseId" => 44809,
				"poisonBaseId" => 76830,
				"icon" => "resources/crafting_alchemy_trait_ravagestamina.png",
				"isPositive" => false,
			),
	7 =>  array(
				"id" => 7,
				"oppositeId" => 8,
				"name" => "Increase Spell Resist",
				"name2" => "Minor Breach and Ward",
				"potionBaseId" => 44814,
				"poisonBaseId" => 76832,
				"icon" => "resources/crafting_alchemy_trait_increasespellresist.png",
				"isPositive" => true,
			),
	8 =>  array(
				"id" => 8,
				"oppositeId" => 7,
				"name" => "Breach", // Lower Spell Resistance
				"name1" => "Lower Spell Resist",
				"name2" => "Minor Breach",
				"potionBaseId" => 44821,
				"poisonBaseId" => 76833,
				"icon" => "resources/crafting_alchemy_trait_lowerspellresist.png",
				"isPositive" => false,
			),
	9 =>  array(
				"id" => 9,
				"oppositeId" => 10,
				"name" => "Increase Armor",
				"name2" => "Minor Fracture and Resolve",
				"potionBaseId" => 27042,
				"poisonBaseId" => 76837,
				"icon" => "resources/crafting_alchemy_trait_increasearmor.png",
				"isPositive" => true,
			),
	10 =>  array(
				"id" => 10,
				"oppositeId" => 9,
				"name" => "Fracture", // Lower Armor
				"name1" => "Lower Armor",
				"name2" => "Minor Fracture",
				"potionBaseId" => 27040,
				"poisonBaseId" => 76835,
				"icon" => "resources/crafting_alchemy_trait_lowerarmor.png",
				"isPositive" => false,
			),
	11 =>  array(
				"id" => 11,
				"oppositeId" => 12,
				"name" => "Increase Spell Power",
				"name2" => "Minor Cowardice and Sorcery",
				"potionBaseId" => 30145,
				"poisonBaseId" => 76840,
				"icon" => "resources/crafting_alchemy_trait_increasespellpower.png",
				"isPositive" => true,
			),
	12 =>  array(
				"id" => 12,
				"oppositeId" => 11,
				"name" => "Cowardice",
				"name1" => "Increase Ultimate Cost",
				"name2" => "Minor Cowardice",
				"potionBaseId" => 44813,
				"poisonBaseId" => 76834,
				"icon" => "resources/crafting_alchemy_trait_lowerspellpower.png",
				"isPositive" => false,
			),
	13 =>  array(
				"id" => 13,
				"oppositeId" => 14,
				"name" => "Increase Weapon Power",
				"name2" => "Minor Maim and Brutality",
				"potionBaseId" => 44714,
				"poisonBaseId" => 76838,
				"icon" => "resources/crafting_alchemy_trait_increaseweaponpower.png",
				"isPositive" => true,
			),
	14 =>  array(
				"id" => 14,
				"oppositeId" => 13,
				"name" => "Maim", // Lower Weapon Power
				"name1" => "Lower Weapon Power",
				"name2" => "Minor Maim",
				"potionBaseId" => 44810,
				"poisonBaseId" => 76839,
				"icon" => "resources/crafting_alchemy_trait_lowerweaponpower.png",
				"isPositive" => false,
			),
	15 =>  array(
				"id" => 15,
				"oppositeId" => 16,
				"name" => "Increase Spell Crit",
				"name2" => "Minor Uncertainty and Prophecy",
				"potionBaseId" => 30141,
				"poisonBaseId" => 76836,
				"icon" => "resources/crafting_alchemy_trait_spellcrit.png",
				"isPositive" => true,
			),
	16 =>  array(
				"id" => 16,
				"oppositeId" => 15,
				"name" => "Uncertainty", // Lower Spell Critical
				"name2" => "Lower Spell Crit",
				"name2" => "Minor Uncertainty",
				"potionBaseId" => 54336,
				"poisonBaseId" => 76841,
				"icon" => "resources/crafting_alchemy_trait_lowerspellcrit.png",
				"isPositive" => false,
			),
	17 =>  array(
				"id" => 17,
				"oppositeId" => 18,
				"name" => "Increase Weapon Crit",
				"name2" => "Minor Evervation and Savagery",
				"potionBaseId" => 30146,
				"poisonBaseId" => 76842,
				"icon" => "resources/crafting_alchemy_trait_weaponcrit.png",
				"isPositive" => true,
			),
	18 =>  array(
				"id" => 18,
				"oppositeId" => 17,
				"name" => "Enervation", // Lower Weapon Critical
				"name1" => "Lower Weapon Crit",
				"name2" => "Minor Enervation",
				"potionBaseId" => 54337,
				"poisonBaseId" => 76843,
				"icon" => "resources/crafting_alchemy_trait_lowerweaponcrit.png",
				"isPositive" => false,
			),
	19 =>  array(
				"id" => 19,
				"oppositeId" => 20,
				"name" => "Unstoppable",
				"name2" => "Immobilize and Unstoppable",
				"potionBaseId" => 27039,
				"poisonBaseId" => 81196,
				"icon" => "resources/crafting_alchemy_trait_unstoppable.png",
				"isPositive" => true,
			),
	20 =>  array(
				"id" => 20,
				"oppositeId" => 19,
				"name" => "Entrapment", // Stun
				"name1" => "Stun",
				"name2" => "Immobilize",
				"potionBaseId" => 54333,
				"poisonBaseId" => 76845,
				"icon" => "resources/crafting_alchemy_trait_stun.png",
				"isPositive" => false,
			),
	21 =>  array(
				"id" => 21,
				"oppositeId" => 22,
				"name" => "Detection",
				"name2" => "Expose Victim",
				"potionBaseId" => 30142,
				"poisonBaseId" => 76847,
				"icon" => "resources/crafting_alchemy_trait_detection.png",
				"isPositive" => true,
			),
	22 =>  array(
				"id" => 22,
				"oppositeId" => 21,
				"name" => "Invisible",
				"name2" => "Mark Victim",
				"potionBaseId" => 44715,
				"poisonBaseId" => 76844, //76846?
				"icon" => "resources/crafting_alchemy_trait_invisible.png",
				"isPositive" => true,
			),
	23 =>  array(
				"id" => 23,
				"oppositeId" => 24,
				"name" => "Speed",
				"name2" => "Hindrance and Major Expedition",
				"potionBaseId" => 27041,
				"poisonBaseId" => 0,
				"icon" => "resources/crafting_alchemy_trait_speed.png",
				"isPositive" => true,
			),
	24 =>  array(
				"id" => 24,
				"oppositeId" => 23,
				"name" => "Hindrance", // Reduce Speed, Slow
				"name1" => "Slow",
				"name2" => "Hindrance",
				"potionBaseId" => 54335,
				"poisonBaseId" => 81196, //76849?
				"icon" => "resources/crafting_alchemy_trait_reducespeed.png",
				"isPositive" => false,
			),
	
					/* Update 10 */
	25 =>  array(
				"id" => 25,
				"oppositeId" => 26,
				"name" => "Protection",
				"name2" => "Minor Vulnerability and Protection",
				"potionBaseId" => 77596,
				"poisonBaseId" => 77597,
				"icon" => "resources/crafting_poison_trait_protection.png",
				"isPositive" => true,
			),
	26 =>  array(
				"id" => 26,
				"oppositeId" => 25,
				"name" => "Vulnerability",
				"name2" => "Minor Vulnerability",
				"potionBaseId" => 77598,
				"poisonBaseId" => 77599,
				"icon" => "resources/crafting_poison_trait_damage.png",
				"isPositive" => false,
			),
	27 =>  array(
				"id" => 27,
				"oppositeId" => 28,
				"name" => "Sustained Restore Health",
				"name2" => "Gradual Drain Health",
				"potionBaseId" => 77592,
				"poisonBaseId" => 77593,
				"icon" => "resources/crafting_poison_trait_hot.png",
				"isPositive" => true,
			),
	28 =>  array(
				"id" => 28,
				"oppositeId" => 27,
				"name" => "Gradual Ravage Health",
				"name2" => "Gradual Ravage Health",
				"potionBaseId" => 77594,
				"poisonBaseId" => 81195, //77595?
				"icon" => "resources/crafting_poison_trait_dot.png",
				"isPositive" => false,
			),
	29 =>  array(
				"id" => 29,
				"oppositeId" => 30,				
				"name" => "Vitality", 	// Increase Healing Taken
				"name1" => "Increase Healing Taken",
				"name2" => "Minor Defile and Vitality",
				"potionBaseId" => 77600,
				"poisonBaseId" => 77601,
				"icon" => "resources/crafting_poison_trait_increasehealing.png",
				"isPositive" => true,
			),
	30 =>  array(
				"id" => 30,
				"oppositeId" => 29,
				"name" => "Defile",		// Reduce Healing Taken
				"name1" => "Reduce Healing Taken",
				"name2" => "Defile",
				"potionBaseId" => 77602,
				"poisonBaseId" => 77603,
				"icon" => "resources/crafting_poison_trait_decreasehealing.png",
				"isPositive" => false,
			),		
);


$ESO_UNKNOWN_POTION_EFFECT = array(
		"id" => 0,
		"name" => "none",
		"potionBaseId" => 0,
		"poisonBaseId" => 0,
		"icon" => "",
		"isPositive" => false,
);


$ESO_REAGENT_DATA = array(
		"Beetle Scuttle" => array(
				"name" => "Beetle Scuttle",
				"itemId" => 77583,
				"icon" => "resources/reagent_scuttle.png",
				"effects" => array(ESO_POTIONEFFECT_BREACH, ESO_POTIONEFFECT_ARMOR, ESO_POTIONEFFECT_PROTECTION, ESO_POTIONEFFECT_VITALITY),
		),
		"Blessed Thistle" => array(
				"name" => "Blessed Thistle",
				"itemId" => 30157,
				"icon" => "resources/blessed_thistle.png",
				"effects" => array(ESO_POTIONEFFECT_RESTORESTAMINA, ESO_POTIONEFFECT_WEAPONPOWER, ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_SPEED),
		),
		"Blue Entoloma" => array(
				"name" => "Blue Entoloma",
				"itemId" => 30148,
				"icon" => "resources/blue_entoloma_cap_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGEMAGICKA, ESO_POTIONEFFECT_LOWERSPELLPOWER, ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_INVISIBLE),
		),
		"Bugloss" => array(
				"name" => "Bugloss",
				"itemId" => 30160,
				"icon" => "resources/vipers_bugloss_r1.png",
				"effects" => array(ESO_POTIONEFFECT_SPELLRESIST, ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_LOWERSPELLPOWER, ESO_POTIONEFFECT_RESTOREMAGICKA),
		),
		"Butterfly Wing" => array(
				"name" => "Butterfly Wing",
				"itemId" => 77585,
				"icon" => "resources/reagent_butterfly_wing.png",
				"effects" => array(ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_LOWERSPELLCRIT, ESO_POTIONEFFECT_SUSTAINRESTOREHEALTH, ESO_POTIONEFFECT_VITALITY),
		),
		"Columbine" => array(
				"name" => "Columbine",
				"itemId" => 30164,
				"icon" => "resources/columbine_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_RESTOREMAGICKA, ESO_POTIONEFFECT_RESTORESTAMINA, ESO_POTIONEFFECT_UNSTOPPABLE),
		),
		"Corn Flower" => array(
				"name" => "Corn Flower",
				"itemId" => 30161,
				"icon" => "resources/corn_flower_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RESTOREMAGICKA, ESO_POTIONEFFECT_SPELLPOWER, ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_DETECTION),
		),
		"Dragonthorn" => array(
				"name" => "Dragonthorn",
				"itemId" => 30162,
				"icon" => "resources/dragonthorn_r2.png",
				"effects" => array(ESO_POTIONEFFECT_WEAPONPOWER, ESO_POTIONEFFECT_RESTORESTAMINA, ESO_POTIONEFFECT_LOWERARMOR, ESO_POTIONEFFECT_WEAPONCRIT),
		),
		"Emetic Russula" => array(
				"name" => "Emetic Russula",
				"itemId" => 30151,
				"icon" => "resources/emetic_russula_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_RAVAGEMAGICKA, ESO_POTIONEFFECT_RAVAGESTAMINA, ESO_POTIONEFFECT_STUN),
		),
		"Fleshfly Larva" => array(
				"name" => "Fleshfly Larva",
				"itemId" => 77587,
				"icon" => "resources/reagent_fleshfly_larva.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGESTAMINA, ESO_POTIONEFFECT_VULNERABILITY, ESO_POTIONEFFECT_CREEPINGRAVAGEHEALTH, ESO_POTIONEFFECT_VITALITY),
		),
		"Imp Stool" => array(
				"name" => "Imp Stool",
				"itemId" => 30156,
				"icon" => "resources/imp_stool_r2.png",
				"effects" => array(ESO_POTIONEFFECT_LOWERWEAPONPOWER, ESO_POTIONEFFECT_RAVAGESTAMINA, ESO_POTIONEFFECT_ARMOR, ESO_POTIONEFFECT_LOWERWEAPONCRIT),
		),
		"Lady's Smock" => array(
				"name" => "Lady's Smock",
				"itemId" => 30158,
				"icon" => "resources/ladys_smock_r2.png",
				"effects" => array(ESO_POTIONEFFECT_SPELLPOWER, ESO_POTIONEFFECT_RESTOREMAGICKA, ESO_POTIONEFFECT_LOWERSPELLRESIST, ESO_POTIONEFFECT_SPELLCRIT),
		),
		"Luminous Russula" => array(
				"name" => "Luminous Russula",
				"itemId" => 30155,
				"icon" => "resources/luminous_russula_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGESTAMINA, ESO_POTIONEFFECT_LOWERWEAPONPOWER, ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_REDUCESPEED),
		),
		"Mountain Flower" => array(
				"name" => "Mountain Flower",
				"itemId" => 30163,
				"icon" => "resources/mountain_flower_r1.png",
				"effects" => array(ESO_POTIONEFFECT_ARMOR, ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_LOWERWEAPONPOWER, ESO_POTIONEFFECT_RESTORESTAMINA),
		),
		"Mudcrab Chitin" => array(
				"name" => "Mudcrab Chitin",
				"itemId" => 77591,
				"icon" => "resources/reagent_mudcrab_chitin.png",
				"effects" => array(ESO_POTIONEFFECT_SPELLRESIST, ESO_POTIONEFFECT_ARMOR, ESO_POTIONEFFECT_PROTECTION, ESO_POTIONEFFECT_DEFILE),
		),
		"Namira's Rot" => array(
				"name" => "Namira's Rot",
				"itemId" => 30153,
				"icon" => "resources/namiras_rot_r1.png",
				"effects" => array(ESO_POTIONEFFECT_SPELLCRIT, ESO_POTIONEFFECT_SPEED, ESO_POTIONEFFECT_INVISIBLE, ESO_POTIONEFFECT_UNSTOPPABLE),
		),
		"Nightshade" => array(
				"name" => "Nightshade",
				"itemId" => 77590,
				"icon" => "resources/nightshade_01.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_PROTECTION, ESO_POTIONEFFECT_CREEPINGRAVAGEHEALTH, ESO_POTIONEFFECT_DEFILE),
		),
		"Nirnroot" => array(
				"name" => "Nirnroot",
				"itemId" => 30165,
				"icon" => "resources/plant_nirnroot_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_LOWERSPELLCRIT, ESO_POTIONEFFECT_LOWERWEAPONCRIT, ESO_POTIONEFFECT_INVISIBLE),
		),
		"Scrib Jelly" => array(
				"name" => "Scrib Jelly",
				"itemId" => 77589,
				"icon" => "resources/reagent_scrib_jelly.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGEMAGICKA, ESO_POTIONEFFECT_SPEED, ESO_POTIONEFFECT_VULNERABILITY, ESO_POTIONEFFECT_SUSTAINRESTOREHEALTH),
		),
		"Spider Egg" => array(
				"name" => "Spider Egg",
				"itemId" => 77584,
				"icon" => "resources/reagent_spider_egg.png",
				"effects" => array(ESO_POTIONEFFECT_REDUCESPEED, ESO_POTIONEFFECT_INVISIBLE, ESO_POTIONEFFECT_SUSTAINRESTOREHEALTH, ESO_POTIONEFFECT_DEFILE),
		),
		"Stinkhorn" => array(
				"name" => "Stinkhorn",
				"itemId" => 30149,
				"icon" => "resources/stinkhorn_cap_r1.png",
				"effects" => array(ESO_POTIONEFFECT_LOWERARMOR, ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_WEAPONPOWER, ESO_POTIONEFFECT_RAVAGESTAMINA),
		),
		"Torchbug Thorax" => array(
				"name" => "Torchbug Thorax",
				"itemId" => 77581,
				"icon" => "resources/reagent_torchbug_thorax.png",
				"effects" => array(ESO_POTIONEFFECT_LOWERARMOR, ESO_POTIONEFFECT_LOWERWEAPONCRIT, ESO_POTIONEFFECT_DETECTION, ESO_POTIONEFFECT_VITALITY),
		),
		"Violet Coprinus" => array(
				"name" => "Violet Coprinus",
				"itemId" => 30152,
				"icon" => "resources/violet_coprinus_r1.png",
				"effects" => array(ESO_POTIONEFFECT_LOWERSPELLRESIST, ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_SPELLPOWER, ESO_POTIONEFFECT_RAVAGEMAGICKA),
		),
		"Water Hyacinth" => array(
				"name" => "Water Hyacinth",
				"itemId" => 30166,
				"icon" => "resources/plant_water_hyacinth_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_SPELLCRIT, ESO_POTIONEFFECT_WEAPONCRIT, ESO_POTIONEFFECT_STUN),
		),
		"White Cap" => array(
				"name" => "White Cap",
				"itemId" => 30154,
				"icon" => "resources/white_cap_r1.png",
				"effects" => array(ESO_POTIONEFFECT_LOWERSPELLPOWER, ESO_POTIONEFFECT_RAVAGEMAGICKA, ESO_POTIONEFFECT_SPELLRESIST, ESO_POTIONEFFECT_DETECTION),
		),
		"Wormwood" => array(
				"name" => "Wormwood",
				"itemId" => 30159,
				"icon" => "resources/wormwood_r1.png",
				"effects" => array(ESO_POTIONEFFECT_WEAPONCRIT, ESO_POTIONEFFECT_REDUCESPEED, ESO_POTIONEFFECT_DETECTION, ESO_POTIONEFFECT_UNSTOPPABLE),
		),
);


$ESO_SOLVENT_DATA = array(
		"Natural Water" => array(
				"name" => "Natural Water",
				"level" => 3,
				"itemId" => 883,
				"internalLevel" => 3,
				"internalType" => 30,
				"isPoison" => false,
				"prefix" => "Sip of",
				"icon" => "resources/crafting_forester_potion_vendor_001.png",
		),
		"Clear Water" => array(
				"name" => "Clear Water",
				"level" => 10,
				"itemId" => 1187,
				"internalLevel" => 10,
				"internalType" => 30,
				"isPoison" => false,
				"prefix" => "Tincture of",
				"icon" => "resources/crafting_potion_base_water_2_r2.png",
		),
		"Pristine Water" => array(
				"name" => "Pristine Water",
				"level" => 20,
				"itemId" => 4570,
				"internalLevel" => 20,
				"internalType" => 30,
				"isPoison" => false,
				"prefix" => "Dram of",
				"icon" => "resources/crafting_potion_base_water_2_r3.png",
		),
		"Cleansed Water" => array(
				"name" => "Cleansed Water",
				"level" => 30,
				"itemId" => 23265,
				"internalLevel" => 30,
				"internalType" => 30,
				"isPoison" => false,
				"prefix" => "Potion of",
				"icon" => "resources/crafting_potion_base_water_3_r1.png",
		),
		"Filtered Water" => array(
				"name" => "Filtered Water",
				"level" => 40,
				"itemId" => 23266,
				"internalLevel" => 40,
				"internalType" => 30,
				"isPoison" => false,
				"prefix" => "Solution of",
				"icon" => "resources/crafting_potion_base_water_3_r2.png",
		),
		"Purified Water" => array(
				"name" => "Purified Water",
				"level" => 51,
				"itemId" => 23267,
				"internalLevel" => 50,
				"internalType" => 125,
				"isPoison" => false,
				"prefix" => "Elixir of",
				"icon" => "resources/crafting_potion_base_water_3_r3.png",
		),
		"Cloud Mist" => array(
				"name" => "Cloud Mist",
				"level" => 55,
				"itemId" => 23268,
				"internalLevel" => 50,
				"internalType" => 129,
				"isPoison" => false,
				"prefix" => "Panacea of",
				"icon" => "resources/crafting_potion_base_water_4_r1.png",
		),
		"Star Dew" => array(
				"name" => "Star Dew",
				"level" => 60,
				"itemId" => 64500,
				"internalLevel" => 50,
				"internalType" => 134,
				"isPoison" => false,
				"prefix" => "Distillate of",
				"icon" => "resources/crafting_stardew.png",
		),
		"Lorkhan's Tears" => array(
				"name" => "Lorkhan's Tears",
				"level" => 65,
				"itemId" => 64501,
				"internalLevel" => 50,
				"internalType" => 308,
				"isPoison" => false,
				"prefix" => "Essence of",
				"icon" => "resources/crafting_lorkhanstears.png",
		),
		
		"Grease" => array(
				"name" => "Grease",
				"level" => 3,
				"itemId" => 75357,
				"internalLevel" => 3,
				"internalType" => 30,
				"isPoison" => true,
				"prefix" => "Poison I",
				"icon" => "resources/crafting_potion_base_water_1_r1.png",
		),
		"Ichor" => array(
				"name" => "Ichor",
				"level" => 10,
				"itemId" => 75358,
				"internalLevel" => 10,
				"internalType" => 30,
				"isPoison" => true,
				"prefix" => "Poison II",
				"icon" => "resources/crafting_potion_base_oil_2_r2.png",
		),
		"Slime" => array(
				"name" => "Slime",
				"level" => 20,
				"itemId" => 75359,
				"internalLevel" => 20,
				"internalType" => 30,
				"isPoison" => true,
				"prefix" => "Poison III",
				"icon" => "resources/crafting_potion_base_oil_2_r3.png",
		),
		"Gall" => array(
				"name" => "Gall",
				"level" => 30,
				"itemId" => 75360,
				"internalLevel" => 30,
				"internalType" => 30,
				"isPoison" => true,
				"prefix" => "Poison IV",
				"icon" => "resources/crafting_potion_base_oil_3_r1.png",
		),
		"Terebinthine" => array(
				"name" => "Terebinthine",
				"level" => 40,
				"itemId" => 75361,
				"internalLevel" => 40,
				"internalType" => 30,
				"isPoison" => true,
				"prefix" => "Poison V",
				"icon" => "resources/crafting_potion_base_oil_3_r2.png",
		),
		"Pitch-Bile" => array(
				"name" => "Pitch-Bile",
				"level" => 51,
				"itemId" => 75362,
				"internalLevel" => 50,
				"internalType" => 125,
				"isPoison" => true,
				"prefix" => "Poison VI",
				"icon" => "resources/crafting_potion_base_oil_3_r3.png",
		),
		"Tarblack" => array(
				"name" => "Tarblack",
				"level" => 55,
				"itemId" => 75363,
				"internalLevel" => 50,
				"internalType" => 129,
				"isPoison" => true,
				"prefix" => "Poison VII",
				"icon" => "resources/crafting_potion_base_oil_4_r1.png",
		),
		"Night-Oil" => array(
				"name" => "Night-Oil",
				"level" => 60,
				"itemId" => 75364,
				"internalLevel" => 50,
				"internalType" => 134,
				"isPoison" => true,
				"prefix" => "Poison VIII",
				"icon" => "resources/crafting_potion_base_oil_4_r2.png",
		),
		"Alkahest" => array(
				"name" => "Alkahest",
				"level" => 65,
				"itemId" => 75365,
				"internalLevel" => 50,
				"internalType" => 308,
				"isPoison" => true,
				"prefix" => "Poison IX",
				"icon" => "resources/crafting_potion_base_oil_4_r3.png",
		),
);