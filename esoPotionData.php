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
const ESO_POTIONEFFECT_LOWERARMOR = 9;
const ESO_POTIONEFFECT_PHYSICALRESIST = 9;
const ESO_POTIONEFFECT_FRACTURE = 10;
const ESO_POTIONEFFECT_LOWERPHYSICALRESIST = 10;
const ESO_POTIONEFFECT_SPELLPOWER = 11;
const ESO_POTIONEFFECT_LOWERSPELLPOWER = 12;
const ESO_POTIONEFFECT_WEAPONPOWER = 13;
const ESO_POTIONEFFECT_MAIM = 14;
const ESO_POTIONEFFECT_LOWERWEAPONPOWER = 14;
const ESO_POTIONEFFECT_SPELLCRIT = 15;
const ESO_POTIONEFFECT_UNCERTAINTY = 16;
const ESO_POTIONEFFECT_LOWERSPELLCRIT = 16;
const ESO_POTIONEFFECT_WEAPONCRIT = 17;
const ESO_POTIONEFFECT_ENERVATION = 18;
const ESO_POTIONEFFECT_LOWERWEAPONCRIT = 19;
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


$ESO_POTION_DATA = array(
	1 => array(
				"id" => 1,
				"name" => "Restore Health",
				"potionBaseId" => 54339,
				"poisonBaseId" => 76827,
				"icon" => "resources/crafting_alchemy_trait_ravagehealth.png",
				"isPositive" => true,
			),
	2 =>  array(
				"id" => 2,
				"name" => "Ravage Health",
				"potionBaseId" => 44812,
				"poisonBaseId" => 76826,
				"icon" => "resources/crafting_alchemy_trait_ravagehealth.png",
				"isPositive" => false,
			),
	3 =>  array(
				"id" => 3,
				"name" => "Restore Magicka",
				"potionBaseId" => 54340,
				"poisonBaseId" => 76829,
				"icon" => "resources/crafting_alchemy_trait_restoremagicka.png",
				"isPositive" => true,
			),
	4 =>  array(
				"id" => 4,
				"name" => "Ravage Magicka",
				"potionBaseId" => 44815,
				"poisonBaseId" => 76828,
				"icon" => "resources/crafting_alchemy_trait_ravagemagicka.png",
				"isPositive" => false,
			),
	5 =>  array(
				"id" => 5,
				"name" => "Restore Stamina",
				"potionBaseId" => 54341,
				"poisonBaseId" => 76829,
				"icon" => "resources/crafting_alchemy_trait_restorestamina.png",
				"isPositive" => true,
			),
	6 =>  array(
				"id" => 6,
				"name" => "Ravage Stamina",
				"potionBaseId" => 44809,
				"poisonBaseId" => 76830,
				"icon" => "resources/crafting_alchemy_trait_ravagestamina.png",
				"isPositive" => false,
			),
	7 =>  array(
				"id" => 7,
				"name" => "Increase Spell Resist",
				"potionBaseId" => 44814,
				"poisonBaseId" => 76832,
				"icon" => "resources/crafting_alchemy_trait_increasespellresist.png",
				"isPositive" => true,
			),
	8 =>  array(
				"id" => 8,
				"name" => "Breach", // Lower Spell Resistance
				"potionBaseId" => 44821,
				"poisonBaseId" => 76833,
				"icon" => "resources/crafting_alchemy_trait_lowerspellresist.png",
				"isPositive" => false,
			),
	9 =>  array(
				"id" => 9,
				"name" => "Increase Armor",
				"potionBaseId" => 27042,
				"poisonBaseId" => 76837,
				"icon" => "resources/crafting_alchemy_trait_increasearmor.png",
				"isPositive" => true,
			),
	10 =>  array(
				"id" => 10,
				"name" => "Fracture", // Lower Armor
				"potionBaseId" => 27040,
				"poisonBaseId" => 76835,
				"icon" => "resources/crafting_alchemy_trait_lowerarmor.png",
				"isPositive" => false,
			),
	11 =>  array(
				"id" => 11,
				"name" => "Increase Spell Power",
				"potionBaseId" => 30145,
				"poisonBaseId" => 76840,
				"icon" => "resources/crafting_alchemy_trait_increasespellpower.png",
				"isPositive" => true,
			),
	12 =>  array(
				"id" => 12,
				"name" => "Lower Spell Power",
				"potionBaseId" => 44813,
				"poisonBaseId" => 76834,
				"icon" => "resources/crafting_alchemy_trait_lowerspellpower.png",
				"isPositive" => false,
			),
	13 =>  array(
				"id" => 13,
				"name" => "Increase Weapon Power",
				"potionBaseId" => 44714,
				"poisonBaseId" => 76838,
				"icon" => "resources/crafting_alchemy_trait_increaseweaponpower.png",
				"isPositive" => true,
			),
	14 =>  array(
				"id" => 14,
				"name" => "Maim", // Lower Weapon Power
				"potionBaseId" => 44810,
				"poisonBaseId" => 76839,
				"icon" => "resources/crafting_alchemy_trait_lowerweaponpower.png",
				"isPositive" => false,
			),
	15 =>  array(
				"id" => 15,
				"name" => "Increase Spell Crit",
				"potionBaseId" => 30141,
				"poisonBaseId" => 76836,
				"icon" => "resources/crafting_alchemy_trait_spellcrit.png",
				"isPositive" => true,
			),
	16 =>  array(
				"id" => 16,
				"name" => "Uncertainty", // Lower Spell Critical
				"potionBaseId" => 54336,
				"poisonBaseId" => 76841,
				"icon" => "resources/crafting_alchemy_trait_lowerspellcrit.png",
				"isPositive" => false,
			),
	17 =>  array(
				"id" => 17,
				"name" => "Increase Weapon Crit",
				"potionBaseId" => 30146,
				"poisonBaseId" => 76842,
				"icon" => "resources/crafting_alchemy_trait_weaponcrit.png",
				"isPositive" => true,
			),
	18 =>  array(
				"id" => 18,
				"name" => "Enervation", // Lower Weapon Critical
				"potionBaseId" => 54337,
				"poisonBaseId" => 76843,
				"icon" => "resources/crafting_alchemy_trait_lowerweaponcrit.png",
				"isPositive" => false,
			),
	19 =>  array(
				"id" => 19,
				"name" => "Unstoppable",
				"potionBaseId" => 27039,
				"poisonBaseId" => 81196,
				"icon" => "resources/crafting_alchemy_trait_unstoppable.png",
				"isPositive" => true,
			),
	20 =>  array(
				"id" => 20,
				"name" => "Entrapment", // Stun
				"potionBaseId" => 54333,
				"poisonBaseId" => 76845,
				"icon" => "resources/crafting_alchemy_trait_stun.png",
				"isPositive" => false,
			),
	21 =>  array(
				"id" => 21,
				"name" => "Detection",
				"potionBaseId" => 30142,
				"poisonBaseId" => 76847,
				"icon" => "resources/crafting_alchemy_trait_detection.png",
				"isPositive" => true,
			),
	22 =>  array(
				"id" => 22,
				"name" => "Invisible",
				"potionBaseId" => 44715,
				"poisonBaseId" => 76844, //76846?
				"icon" => "resources/crafting_alchemy_trait_invisible.png",
				"isPositive" => true,
			),
	23 =>  array(
				"id" => 23,
				"name" => "Speed",
				"potionBaseId" => 27041,
				"poisonBaseId" => 0,
				"icon" => "resources/crafting_alchemy_trait_speed.png",
				"isPositive" => true,
			),
	24 =>  array(
				"id" => 24,
				"name" => "Hindrance", // Reduce Speed, Slow
				"potionBaseId" => 54335,
				"poisonBaseId" => 81196, //76849?
				"icon" => "resources/crafting_alchemy_trait_reducespeed.png",
				"isPositive" => false,
			),
	
					/* Update 10 */
	25 =>  array(
				"id" => 25,
				"name" => "Protection",
				"potionBaseId" => 77596,
				"poisonBaseId" => 77597,
				"icon" => "resources/crafting_poison_trait_protection.png",
				"isPositive" => true,
			),
	26 =>  array(
				"id" => 26,
				"name" => "Vulnerability",
				"potionBaseId" => 77598,
				"poisonBaseId" => 77599,
				"icon" => "resources/crafting_poison_trait_damage.png",
				"isPositive" => false,
			),
	27 =>  array(
				"id" => 27,
				"name" => "Sustained Restore Health",
				"potionBaseId" => 77592,
				"poisonBaseId" => 77593,
				"icon" => "resources/crafting_poison_trait_hot.png",
				"isPositive" => true,
			),
	28 =>  array(
				"id" => 28,
				"name" => "Gradual Ravage Health",
				"potionBaseId" => 77594,
				"poisonBaseId" => 81195, //77595?
				"icon" => "resources/crafting_poison_trait_dot.png",
				"isPositive" => false,
			),
	29 =>  array(
				"id" => 29,
				"name" => "Vitality", 	// Increase Healing Taken
				"potionBaseId" => 77600,
				"poisonBaseId" => 77601,
				"icon" => "resources/crafting_poison_trait_increasehealing.png",
				"isPositive" => true,
			),
	30 =>  array(
				"id" => 30,
				"name" => "Defile",		// Reduce Healing Taken
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
		77583 => array(
				"name" => "Beetle Scuttle",
				"itemId" => 77583,
				"icon" => "resources/reagent_scuttle.png",
				"effects" => array(ESO_POTIONEFFECT_BREACH, ESO_POTIONEFFECT_ARMOR, ESO_POTIONEFFECT_PROTECTION, ESO_POTIONEFFECT_VITALITY),
		),
		30157 => array(
				"name" => "Blessed Thistle",
				"itemId" => 30157,
				"icon" => "resources/blessed_thistle.png",
				"effects" => array(ESO_POTIONEFFECT_RESTORESTAMINA, ESO_POTIONEFFECT_WEAPONPOWER, ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_SPEED),
		),
		30148 => array(
				"name" => "Blue Entoloma",
				"itemId" => 30148,
				"icon" => "resources/blue_entoloma_cap_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGEMAGICKA, ESO_POTIONEFFECT_LOWERSPELLPOWER, ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_INVISIBLE),
		),
		30160 => array(
				"name" => "Bugloss",
				"itemId" => 30160,
				"icon" => "resources/vipers_bugloss_r1.png",
				"effects" => array(ESO_POTIONEFFECT_SPELLRESIST, ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_LOWERSPELLPOWER, ESO_POTIONEFFECT_RESTOREMAGICKA),
		),
		77585 => array(
				"name" => "Butterfly Wing",
				"itemId" => 77585,
				"icon" => "resources/reagent_butterfly_wing.png",
				"effects" => array(ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_LOWERSPELLCRIT, ESO_POTIONEFFECT_SUSTAINRESTOREHEALTH, ESO_POTIONEFFECT_VITALITY),
		),
		30164 => array(
				"name" => "Columbine",
				"itemId" => 30164,
				"icon" => "resources/columbine_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_RESTOREMAGICKA, ESO_POTIONEFFECT_RESTORESTAMINA, ESO_POTIONEFFECT_UNSTOPPABLE),
		),
		30161 => array(
				"name" => "Corn Flower",
				"itemId" => 30161,
				"icon" => "resources/corn_flower_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RESTOREMAGICKA, ESO_POTIONEFFECT_SPELLPOWER, ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_DETECTION),
		),
		30162 => array(
				"name" => "Dragonthorn",
				"itemId" => 30162,
				"icon" => "resources/dragonthorn_r2.png",
				"effects" => array(ESO_POTIONEFFECT_WEAPONPOWER, ESO_POTIONEFFECT_RESTORESTAMINA, ESO_POTIONEFFECT_LOWERARMOR, ESO_POTIONEFFECT_WEAPONCRIT),
		),
		30151 => array(
				"name" => "Emetic Russula",
				"itemId" => 30151,
				"icon" => "resources/emetic_russula_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_RAVAGEMAGICKA, ESO_POTIONEFFECT_RAVAGESTAMINA, ESO_POTIONEFFECT_STUN),
		),
		77587 => array(
				"name" => "Fleshfly Larva",
				"itemId" => 77587,
				"icon" => "resources/reagent_fleshfly_larva.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGESTAMINA, ESO_POTIONEFFECT_VULNERABILITY, ESO_POTIONEFFECT_CREEPINGRAVAGEHEALTH, ESO_POTIONEFFECT_VITALITY),
		),
		30156 => array(
				"name" => "Imp Stool",
				"itemId" => 30156,
				"icon" => "resources/imp_stool_r2.png",
				"effects" => array(ESO_POTIONEFFECT_LOWERWEAPONPOWER, ESO_POTIONEFFECT_RAVAGESTAMINA, ESO_POTIONEFFECT_ARMOR, ESO_POTIONEFFECT_LOWERWEAPONCRIT),
		),
		30158 => array(
				"name" => "Lady's Smock",
				"itemId" => 30158,
				"icon" => "resources/ladys_smock_r2.png",
				"effects" => array(ESO_POTIONEFFECT_SPELLPOWER, ESO_POTIONEFFECT_RESTOREMAGICKA, ESO_POTIONEFFECT_LOWERSPELLRESIST, ESO_POTIONEFFECT_SPELLCRIT),
		),
		30155 => array(
				"name" => "Luminous Russula",
				"itemId" => 30155,
				"icon" => "resources/luminous_russula_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGESTAMINA, ESO_POTIONEFFECT_LOWERWEAPONPOWER, ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_REDUCESPEED),
		),
		30163 => array(
				"name" => "Mountain Flower",
				"itemId" => 30163,
				"icon" => "resources/mountain_flower_r1.png",
				"effects" => array(ESO_POTIONEFFECT_ARMOR, ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_LOWERWEAPONPOWER, ESO_POTIONEFFECT_RESTORESTAMINA),
		),
		77591 => array(
				"name" => "Mudcrab Chitin",
				"itemId" => 77591,
				"icon" => "resources/reagent_mudcrab_chitin.png",
				"effects" => array(ESO_POTIONEFFECT_SPELLRESIST, ESO_POTIONEFFECT_ARMOR, ESO_POTIONEFFECT_PROTECTION, ESO_POTIONEFFECT_DEFILE),
		),
		30153 => array(
				"name" => "Namira's Rot",
				"itemId" => 30153,
				"icon" => "resources/namiras_rot_r1.png",
				"effects" => array(ESO_POTIONEFFECT_SPELLCRIT, ESO_POTIONEFFECT_SPEED, ESO_POTIONEFFECT_INVISIBLE, ESO_POTIONEFFECT_UNSTOPPABLE),
		),
		77590 => array(
				"name" => "Nightshade",
				"itemId" => 77590,
				"icon" => "resources/nightshade_01.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_PROTECTION, ESO_POTIONEFFECT_CREEPINGRAVAGEHEALTH, ESO_POTIONEFFECT_DEFILE),
		),
		30165 => array(
				"name" => "Nirnroot",
				"itemId" => 30165,
				"icon" => "resources/plant_nirnroot_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_LOWERSPELLCRIT, ESO_POTIONEFFECT_LOWERWEAPONCRIT, ESO_POTIONEFFECT_INVISIBLE),
		),
		77589 => array(
				"name" => "Scrib Jelly",
				"itemId" => 77589,
				"icon" => "resources/reagent_scrib_jelly.png",
				"effects" => array(ESO_POTIONEFFECT_RAVAGEMAGICKA, ESO_POTIONEFFECT_SPEED, ESO_POTIONEFFECT_VULNERABILITY, ESO_POTIONEFFECT_SUSTAINRESTOREHEALTH),
		),
		77584 => array(
				"name" => "Spider Egg",
				"itemId" => 77584,
				"icon" => "resources/reagent_spider_egg.png",
				"effects" => array(ESO_POTIONEFFECT_REDUCESPEED, ESO_POTIONEFFECT_INVISIBLE, ESO_POTIONEFFECT_SUSTAINRESTOREHEALTH, ESO_POTIONEFFECT_DEFILE),
		),
		30149 => array(
				"name" => "Stinkhorn",
				"itemId" => 30149,
				"icon" => "resources/stinkhorn_cap_r1.png",
				"effects" => array(ESO_POTIONEFFECT_LOWERARMOR, ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_WEAPONPOWER, ESO_POTIONEFFECT_RAVAGESTAMINA),
		),
		77581 => array(
				"name" => "Torchbug Thorax",
				"itemId" => 77581,
				"icon" => "resources/reagent_torchbug_thorax.png",
				"effects" => array(ESO_POTIONEFFECT_LOWERARMOR, ESO_POTIONEFFECT_LOWERWEAPONCRIT, ESO_POTIONEFFECT_DETECTION, ESO_POTIONEFFECT_VITALITY),
		),
		30152 => array(
				"name" => "Violet Coprinus",
				"itemId" => 30152,
				"icon" => "resources/violet_coprinus_r1.png",
				"effects" => array(ESO_POTIONEFFECT_LOWERSPELLRESIST, ESO_POTIONEFFECT_RAVAGEHEALTH, ESO_POTIONEFFECT_SPELLPOWER, ESO_POTIONEFFECT_RAVAGEMAGICKA),
		),
		30166 => array(
				"name" => "Water Hyacinth",
				"itemId" => 30166,
				"icon" => "resources/plant_water_hyacinth_r1.png",
				"effects" => array(ESO_POTIONEFFECT_RESTOREHEALTH, ESO_POTIONEFFECT_SPELLCRIT, ESO_POTIONEFFECT_WEAPONCRIT, ESO_POTIONEFFECT_STUN),
		),
		30154 => array(
				"name" => "White Cap",
				"itemId" => 30154,
				"icon" => "resources/white_cap_r1.png",
				"effects" => array(ESO_POTIONEFFECT_LOWERSPELLPOWER, ESO_POTIONEFFECT_RAVAGEMAGICKA, ESO_POTIONEFFECT_SPELLRESIST, ESO_POTIONEFFECT_DETECTION),
		),
		30159 => array(
				"name" => "Wormwood",
				"itemId" => 30159,
				"icon" => "resources/wormwood_r1.png",
				"effects" => array(ESO_POTIONEFFECT_WEAPONCRIT, ESO_POTIONEFFECT_REDUCESPEED, ESO_POTIONEFFECT_DETECTION, ESO_POTIONEFFECT_UNSTOPPABLE),
		),
);