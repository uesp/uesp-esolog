<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$TABLE_SUFFIX = "21pts";


$ESO_SKILLS = array();

$ESO_SKILLS["Instant Melee Attack"] = array(
	24631 => "Brace",
	16499 => "Light Attack",
	16668 => "Vortex",
	98751 => "TEST: Execute",
	8690 => "Telekinetic Push",
	33386 => "Assassin's Blade",
	33398 => "Death Stroke",
	25241 => "Repulsing Burst",
	25255 => "Veiled Strike",
	25260 => "Surprise Attack",
	25267 => "Concealed Weapon",
	58047 => "Replenish",
	58123 => "Twin Slashes Enchant Fx",
	8982 => "30% Speed Buff",
	58170 => "Destructive Touch Enchant Draw",
	58223 => "Grand Healing Enchant Draw Fx",
	91310 => "Reverberating Bash",
	107701 => "Deep Slash",
	83216 => "Berserker Strike",
	83229 => "Onslaught",
	83238 => "Berserker Rage",
	1349 => "Press Advantage",
	107946 => "Hunter Speed Buff",
	75278 => "Shadow Cloak",
	1617 => "Batter",
	83551 => "Rip Apart",
	108377 => "Darrem Speed Boost",
	67462 => "Ignite",
	18357 => "Counter Attack",
	108562 => "Wisp Rand Speed",
	34843 => "Killer's Blade",
	108931 => "Tharas speed buff",
	76249 => "Bleeding",
	76325 => "Blade of Woe",
	10896 => "Vortex",
	76993 => "35% Bonus Movement",
	101725 => "Slice Web",
	77211 => "-35% Bonus Movement",
	69158 => "Retaliation",
	3660 => "Shadow Stab",
	3672 => "Sonic Shock",
	28302 => "Reverse Slash",
	28304 => "Low Slash",
	28306 => "Puncture",
	36508 => "Incapacitating Strike",
	28365 => "Power Bash",
	28379 => "Twin Slashes",
	20657 => "Searing Strike",
	20668 => "Venomous Claw",
	20805 => "Molten Whip",
	20816 => "Flame Lash",
	20824 => "Power Lash",
	21033 => "Winged Charge",
	4918 => "Lust",
	70466 => "35% Boss Speed Buff",
	62426 => "Light Attack Infected",
	5098 => "Swift Justice",
	29676 => "Dead Fists",
	103617 => "Q6132 Darien Web attack",
	79172 => "Heavy Armor Riposte",
	38256 => "Ransack",
	38264 => "Heroic Slash",
	38268 => "Deep Slash",
	5533 => "Seize the Edge",
	30123 => "Repulse",
	30130 => "Repulse",
	30137 => "Repulsing Burst",
	30145 => "Repulsing Burst",
	21970 => "Bash",
	79371 => "Flurry Combo #1",
	79373 => "Flurry Combo #1",
	79375 => "Flurry Combo #2",
	79376 => "Flurry Combo #1",
	79380 => "Flurry Combo #3",
	103960 => "Add Yag Scary Speed",
	38452 => "Power Slam",
	79414 => "DSpore Speed",
	38455 => "Reverberating Bash",
	5724 => "Flashing Blade",
	22133 => "Trained Attacker",
	22174 => "Light Strike",
	5890 => "Disrupt",
	5892 => "Disrupt",
	38819 => "Executioner",
	38823 => "Reverse Slice",
	38839 => "Rending Slashes",
	38845 => "Blood Craze",
	71648 => "Shadow Cloak",
	6174 => "Riposte",
	6219 => "Seize the Edge",
	6252 => "Disrupt",
	6253 => "Interrupt Spell",
	55434 => "30% Speed Buff",
	71883 => "Deep Slash",
	63754 => "Light Attack Juggernaut",
	31066 => "Claw Slash",
	113102 => "Incapacitating Strike",
	113105 => "Incapacitating Strike",
	118694 => "Nanobot Speed",
	116762 => "Volendrung Light Attack",
	8668 => "Vortex",
	55885 => "Light Attack",
	102377 => "Spores",
	107917 => "B4 Hunter Init",
	104747 => "2s Snare (-100% Movespeed)",
	20660 => "Burning Embers",
	36514 => "Soul Harvest",
	15391 => "Monster Block Test",
	23604 => "Light Attack",
	15435 => "Light Attack",
	15522 => "Melee Attack",
	40165 => "Scene Choreo Brace",
	23806 => "Lava Whip",
	53259 => "Reverse Slice",
	88299 => "60% Boss Speed Buff",
	56755 => "Pierce Armor",
	56758 => "Deep Slash",
	38250 => "Pierce Armor",
	64976 => "Undead Regiment's Box",
	30127 => "Repulse",
	30141 => "Repulsing Burst",
	5581 => "Pummel",
	76247 => "Twin Slashes",
	76248 => "Twin Slashes",
	24070 => "Winged Charge",
	24076 => "Winged Charge",
	5706 => "Warding Strike",
	22120 => "Reckless Attacks",
	22129 => "Recovery",
	5777 => "Defensive Edge",
	22170 => "Sun Strike",
	22172 => "Sun Thrust",
	82486 => "Stone Armor",
	16037 => "Light Attack",
	24333 => "Repulse",
	15530 => "Melee Builder 2",
	55402 => "Stone Armor",
	52882 => "Unstable Flame",
	32464 => "Light Attack",
);


$ESO_SKILLS["FlameAOE"] = array(
	32785 => "Draw Essence",
	32853 => "Flames of Oblivion",
	16536 => "Meteor",
	32947 => "Standard of Might",
	32958 => "Shifting Standard",
	73953 => "Engulfing Flames",
	98618 => "Engulfing Flames",
	57717 => "Disrupt Blizzard",
	57722 => "Disrupt Rain of Fire",
	57729 => "Disrupt Shadow Army",
	57734 => "Disrupt Blessing of Life",
	82318 => "Dragonknight Standard",
	16788 => "Oil Catapult",
	16795 => "Oil Catapult",
	107641 => "Standard of Might",
	107643 => "Standard of Might",
	50497 => "Fire Storm",
	50509 => "Focused Storm",
	50511 => "Fire Spout",
	50519 => "Flame Vortex",
	50534 => "Chaotic Fires",
	17874 => "Magma Shell",
	50683 => "Explosive Flame Atronach",
	83682 => "Eye of Flame",
	83761 => "Player Pet Defenses",
	59596 => "Valkyn Skoria",
	84492 => "Grothdarr",
	52207 => "Impulse",
	52436 => "Dragonknight Standard",
	3341 => "Lava Flows",
	52765 => "Volcanic Rune",
	52885 => "Engulfing Flames",
	52897 => "Standard of Might",
	61106 => "Cinder Storm",
	20252 => "Burning Talons",
	28794 => "Fire Impulse",
	28807 => "Wall of Fire",
	28967 => "Inferno",
	20783 => "Volcanic Eruption",
	20786 => "Lava Bath",
	20968 => "HA: Flame Torch",
	21052 => "Burn",
	70246 => "Dragonknight Standard",
	54077 => "Cinder Storm",
	21732 => "Reflective Light",
	87960 => "Ash Storm",
	39012 => "Blockade of Fire",
	39053 => "Unstable Wall of Fire",
	71858 => "Standard of Might",
	71864 => "Engulfing Flames",
	39145 => "Fire Ring",
	39162 => "Flame Pulsar",
	104902 => "Standard of Might",
	61273 => "Valkyn Skoria",
	107644 => "Standard of Might",
	64108 => "Oil Catapult",
	107859 => "Standard of Might",
	104699 => "Oil Catapult",
	32792 => "Deep Breath",
	20697 => "Ash Storm",
	31632 => "Fire Rune",
	28988 => "Dragonknight Standard",
	20930 => "Engulfing Flames",
	7218 => "Flame Cloak",
	31837 => "Inhale",
	20917 => "Fiery Breath",
	50501 => "Cataclysm",
	91336 => "Flame Pulsar",
	73952 => "Engulfing Flames",
	87958 => "Ash Storm",
	15774 => "Flaming Oil",
	85126 => "Fiery Rage",
	40465 => "Scalding Rune",
	83625 => "Fire Storm",
	40470 => "Volcanic Rune",
	40493 => "Shooting Star",
	15957 => "Magma Armor",
	72257 => "Volcanic Rune",
	67236 => "Forge-Mother's Embrace",
	61090 => "Standard of Might",
	32710 => "Eruption",
	32715 => "Ferocious Leap",
);

$ESO_SKILLS["Poison Stamina"] = array(
	38645 => "Venom Arrow",
	112940 => "Poison Whip",
	39425 => "Trapping Webs",
	38685 => "Lethal Arrow",
	41990 => "Shadow Silk",
	38701 => "Acid Spray",
	38660 => "Poison Injection",
	28869 => "Poison Arrow",
	42012 => "Tangling Webs",
	20668 => "Venomous Claw",
	20944 => "Noxious Breath",
);


$ESO_SKILLS["Flame Damage Duration"] = array(
	73806 => "Lunar Flare",
	16536 => "Meteor",
	32947 => "Standard of Might",
	32958 => "Shifting Standard",
	32963 => "Shift Standard",
	73953 => "Engulfing Flames",
	57717 => "Disrupt Blizzard",
	57722 => "Disrupt Rain -of Fire",
	57729 => "Disrupt Shadow Army",
	57734 => "Disrupt Blessing of Life",
	82318 => "Dragonknight Standard",
	17874 => "Magma Shell",
	83682 => "Eye of Flame",
	83761 => "Player Pet Defenses",
	67462 => "Ignite",
	75736 => "Lunar Flare",
	84492 => "Grothdarr",
	52436 => "Dragonknight Standard",
	3341 => "Lava Flows",
	52885 => "Engulfing Flames",
	52904 => "Vampire's Bane",
	102125 => "Zaan",
	20252 => "Burning Talons",
	28708 => "Empower",
	53286 => "Crushing Shock",
	61524 => "Scorching Flare",
	28807 => "Wall of Fire",
	20657 => "Searing Strike",
	20779 => "Cinder Storm",
	29059 => "Ash Cloud",
	29073 => "Flame Touch",
	20900 => "Immolated",
	70246 => "Dragonknight Standard",
	29365 => "Heavy Attack (Dest)",
	29368 => "Light Attack",
	54129 => "Fire Chain",
	111778 => "Sun Fire",
	21729 => "Vampire's Bane",
	21732 => "Reflective Light",
	79343 => "Flames of War",
	63198 => "Fiery Chain",
	38985 => "Flame Clench",
	39012 => "Blockade of Fire",
	39053 => "Unstable Wall of Fire",
	71864 => "Engulfing Flames",
	20660 => "Burning Embers",
	28988 => "Dragonknight Standard",
	20930 => "Engulfing Flames",
	20917 => "Fiery Breath",
	73952 => "Engulfing Flames",
	32118 => "Burning Talons",
	32122 => "Burning Talons",
	32126 => "Burning Talons",
	15774 => "Flaming Oil",
	85126 => "Fiery Rage",
	40465 => "Scalding Rune",
	83625 => "Fire Storm",
	40493 => "Shooting Star",
	15957 => "Magma Armor",
	38944 => "Flame Reach",
	72271 => "Fire Chain",
	32710 => "Eruption",
);

$ESO_SKILLS["Melee Attack Damage"] = array(
	53250 => "Wrecking Blow",
	16420 => "Heavy Attack (Dual Wield)",
	36901 => "Power Extraction",
	73808 => "Brutal Strike",
	16499 => "Light Attack",
	45192 => "Implosion",
	45205 => "Master Ritualist",
	77986 => "Crushing Void",
	49430 => "Smash",
	57689 => "Shadow Strike",
	20919 => "Cleave",
	78836 => "Deadly Cloak Prototype",
	112940 => "Poison Whip",
	21033 => "Winged Charge",
	4683 => "Threatening Blow",
	53259 => "Reverse Slice",
	33398 => "Death Stroke",
	108936 => "Whirlwind",
	25260 => "Surprise Attack",
	111299 => "Cleave",
	103998 => "Brutal Pounce",
	9004 => "Ambush",
	4918 => "Lust",
	103483 => "Imbue Weapon",
	73758 => "Strike",
	25484 => "Ambush",
	74687 => "Roaring Impact",
	74690 => "Roaring Impact",
	74693 => "Roaring Impact",
	66518 => "Sword Breaker",
	62426 => "Light Attack Infected",
	41954 => "Tighten",
	29676 => "Dead Fists",
	41990 => "Shadow Silk",
	42012 => "Tangling Webs",
	103617 => "Q6132 Darien Web attack",
	103623 => "Crushing Weapon",
	71786 => "Demolishing Blow",
	71934 => "Steel Tornado",
	38250 => "Pierce Armor",
	38256 => "Ransack",
	38264 => "Heroic Slash",
	38268 => "Deep Slash",
	5533 => "Seize the Edge",
	5581 => "Pummel",
	21970 => "Bash",
	38401 => "Shielded Assault",
	18429 => "Heavy Attack (Unarmed)",
	38405 => "Invasion",
	75744 => "Lunar Smash",
	34355 => "Charge",
	38452 => "Power Slam",
	38455 => "Reverberating Bash",
	5706 => "Warding Strike",
	1617 => "Batter",
	5724 => "Flashing Blade",
	63086 => "Guard Charge",
	22144 => "Empowering Sweep",
	5777 => "Defensive Edge",
	22170 => "Sun Strike",
	1719 => "Throw Weapon",
	42685 => "Tighten",
	42695 => "Tighten",
	42704 => "Tighten",
	5843 => "Steel Cyclone",
	63258 => "Guard Charge",
	85990 => "Wild Guardian",
	38745 => "Carve",
	38754 => "Brawler",
	67430 => "Cleave",
	79734 => "Crushing Void",
	79735 => "Crushing Void",
	38778 => "Critical Rush",
	67459 => "Heavy Smash",
	38788 => "Stampede",
	38807 => "Wrecking Blow",
	38814 => "Dizzying Swing",
	38819 => "Executioner",
	6067 => "Overrun",
	18357 => "Counter Attack",
	38839 => "Rending Slashes",
	38845 => "Blood Craze",
	38861 => "Steel Tornado",
	75741 => "Lunar Smash",
	38901 => "Quick Cloak",
	38906 => "Deadly Cloak",
	10248 => "Ambush",
	34843 => "Killer's Blade",
	6174 => "Riposte",
	6219 => "Seize the Edge",
	6252 => "Disrupt",
	84354 => "Hand of Mephala",
	80022 => "Trapping Webs Test",
	26792 => "Biting Jabs",
	39104 => "Feral Pounce",
	39105 => "Brutal Pounce",
	83238 => "Berserker Rage",
	83229 => "Onslaught",
	83216 => "Berserker Strike",
	63754 => "Light Attack Juggernaut",
	74334 => "Lunar Smash",
	80161 => "Summon Unstable Clannfear",
	113102 => "Incapacitating Strike",
	113105 => "Incapacitating Strike",
	76249 => "Bleeding",
	79362 => "Thews of the Harbinger",
	39425 => "Trapping Webs",
	78839 => "Blade Cloak",
	10820 => "Riposte",
	58864 => "Claws of Anguish",
	58879 => "Claws of Life",
	55885 => "Light Attack",
	55886 => "Heavy Attack",
	76248 => "Twin Slashes",
	76247 => "Twin Slashes",
	76073 => "Unstable Clannfear Prototype",
	74339 => "Lunar Smash",
	73679 => "Crushing Void",
	31422 => "Implosion",
	23319 => "Summon Unstable Clannfear",
	109356 => "Silver Leash",
	69948 => "Monstrous Cleave",
	60229 => "Riposte",
	35713 => "Dawnbreaker",
	15279 => "Heavy Attack (1H)",
	31718 => "Burning Light",
	31749 => "Master Ritualist",
	23604 => "Light Attack",
	15435 => "Light Attack",
	38823 => "Reverse Slice",
	15487 => "Overrun",
	38891 => "Whirling Blades",
	58855 => "Infectious Claws",
	40158 => "Dawnbreaker of Smiting",
	40161 => "Flawless Dawnbreaker",
	58649 => "Fearsome Cleave",
	21031 => "Winged Charge",
	56897 => "Xivkyn Charge",
	55821 => "Nazenaechar's Charge",
	101725 => "Slice Web",
	28719 => "Shield Charge",
	40342 => "Tighten",
	20668 => "Venomous Claw",
	53698 => "Steel Tornado",
	3541 => "Critical Shot",
	29012 => "Dragon Leap",
	52882 => "Unstable Flame",
	25091 => "Soul Shred",
	24070 => "Winged Charge",
	20944 => "Noxious Breath",
	52746 => "Flawless Dawnbreaker",
	24076 => "Winged Charge",
	73255 => "Vicious Cleave",
	28279 => "Uppercut",
	28302 => "Reverse Slash",
	28304 => "Low Slash",
	28306 => "Puncture",
	36508 => "Incapacitating Strike",
	50829 => "Power Cleave",
	16037 => "Light Attack",
	56998 => "Monstrous Cleave",
	16041 => "Heavy Attack (2H)",
	50605 => "Blood Thirsty Familiar",
	44730 => "Burning Light",
	8936 => "Wrecking Blows",
	102090 => "Bloody Cleave",
	28365 => "Power Bash",
	32464 => "Light Attack",
	28379 => "Twin Slashes",
	32477 => "Heavy Attack",
	28448 => "Critical Charge",
	32632 => "Pounce",
	28591 => "Whirlwind",
	28613 => "Blade Cloak",
	22174 => "Light Strike",
	32719 => "Take Flight",
	16357 => "Inferno Cyclone",
	73721 => "Void Rush",
);


function sortSkillsOutputCategory($a, $b)
{
	$s1 = $a['skillLine'];
	$s2 = $b['skillLine'];
	
	if ($s1 == $s2)
	{
		if ($s1 == "ZZZ") return strcasecmp($a['name'], $b['name']);
		return $a['skillIndex'] - $b['skillIndex'];
	}
	
	return strcasecmp($s1, $s2);
}


$result = $db->query("SELECT * FROM minedSkills$TABLE_SUFFIX;");
$skills = array();

while (($row = $result->fetch_assoc()))
{
	$id = $row['id'];
	$skills[$id] = $row;
}

$count = count($skills);
print("\tLoaded $count skills from database...\n");
$skillsOutput = array();
$count = 0;

foreach ($ESO_SKILLS as $category => $catData)
{
	$skillsOutput[$category] = array();
	
	foreach ($catData as $abilityId => $abilityName)
	{
		$ability = $skills[$abilityId];
		
		if ($ability == null)
		{
			print("\tError: Missing ability $abilityId in database!\n");
			continue;
		}
		
		++$count;
		
		$isPlayer = $ability['isPlayer'];
		$skillType = $ability['skillType'];
		$skillLine = $ability['skillLine'];
		
		$skillTypeName = "ZZZ";
		
		if ($skillType && $skillLine) 
		{
			$typeText = GetEsoSkillTypeText($skillType);
			if ($typeText == "Class") $typeText = $ability['classType'];
			if ($typeText == "Racial") $typeText = $ability['raceType'];
			$skillTypeName = $typeText . "::$skillLine";
		}
		
		$newSkill = array();
		$newSkill['id'] = $abilityId;
		$newSkill['skillLine'] = $skillTypeName;
		$newSkill['isPlayer'] = $isPlayer;
		$newSkill['skillIndex'] = $ability['skillIndex'];
		$newSkill['name'] = $abilityName;
		
		$skillsOutput[$category][$abilityId] = $newSkill;
	}
	
	uasort($skillsOutput[$category], sortSkillsOutputCategory);
}

print("\tParsed $count skills...\n");

foreach ($skillsOutput as $category => $catData)
{
	$skillsOutput[$category] = array();
	print("$category:\n");
	$lastSkillLine = "";
	
	foreach ($catData as $abilityId => $abilityData)
	{
		$name = $abilityData['name'];
		$skillLine = $abilityData['skillLine'];
		
		if ($skillLine != $lastSkillLine)
		{
			$lastSkillLine = $skillLine;
			if ($skillLine == "ZZZ") $skillLine = "Other";
			print("\t$skillLine\n");			
		}
		
		if ($skillLine)
			print("\t\t$name ($abilityId)\n");
		else
			print("\t$name ($abilityId)\n");
	}
	
	print("\n");
}