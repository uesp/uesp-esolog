<?php

require_once("esoCommon.php");
require_once("/home/uesp/secrets/esolog.secrets");

if (php_sapi_name() != "cli") die("Can only be run from command line!");

$TABLE_SUFFIX = "";
$PHP_OUTPUT_FILE = "esoSkillRankData1.php";
$LUA_OUTPUT_FILE = "esoSkillRankData1.lua";

$EXTRA_SKILLRANK_DATA = array(
	39012 => array(	//Blockade of Flame
		1 => 39012,
		2 => 41739,
		3 => 41755,
		4 => 41770,
	),
	39018 => array(	//Blockade of Storms
		1 => 39018,
		2 => 41748,
		3 => 41757, 
		4 => 41772,
	),
	39028 => array(	//Blockade of Frost
		1 => 39028,
		2 => 41743,
		3 => 41756, 
		4 => 41771,
	),
	29073 => array(	//Flame Touch
		1 => 29073,
		2 => 40948,
		3 => 40957, 
		4 => 40965,
	),
	29089 => array(	//Shock Touch
		1 => 29089,
		2 => 40953,
		3 => 40962, 
		4 => 40970,
	),
	29078 => array(	//Frost Touch
		1 => 29078,
		2 => 40950,
		3 => 40959, 
		4 => 40967,
	),
	38944 => array(	//Flame Reach
		1 => 38944,
		2 => 41030,
		3 => 41039, 
		4 => 41048,
	),
	38978 => array(	//Shock Reach
		1 => 38978,
		2 => 41036,
		3 => 41045, 
		4 => 41054,
	),
	38970 => array(	//Frost Reach
		1 => 38970,
		2 => 41033,
		3 => 41042, 
		4 => 41051,
	),
	38985 => array(	//Flame Clench
		1 => 38985,
		2 => 40984,
		3 => 40996, 
		4 => 41009,
	),
	38993 => array(	//Shock Clench
		1 => 38993,
		2 => 40991,
		3 => 41003, 
		4 => 41016,
	),
	38989 => array(	//Frost Clench
		1 => 38989,
		2 => 40988,
		3 => 41000, 
		4 => 41013,
	),
	28794 => array(	//Fire Impulse
		1 => 28794,
		2 => 42950,
		3 => 42954, 
		4 => 42958,
	),
	28799 => array(	//Shock Impulse
		1 => 28799,
		2 => 42952,
		3 => 42956, 
		4 => 42960,
	),
	28798 => array(	//Frost Impulse
		1 => 28798,
		2 => 42951,
		3 => 42955, 
		4 => 42959,
	),
	28807 => array(	//Wall of Fire
		1 => 28807,
		2 => 41628,
		3 => 41643, 
		4 => 41659,
	),
	28849 => array(	//Wall of Storms
		1 => 28849,
		2 => 41637,
		3 => 41652, 
		4 => 41668,
	),
	28854 => array(	//Wall of Frost
		1 => 28854,
		2 => 41632,
		3 => 41647, 
		4 => 41663,
	),
	39053 => array(	//Unstable Wall of Fire
		1 => 39053,
		2 => 41674,
		3 => 41692, 
		4 => 41712,
	),
	39073 => array(	//Unstable Wall of Storms
		1 => 39073,
		2 => 41685,
		3 => 41705, 
		4 => 41723,
	),
	39067 => array(	//Unstable Wall of Frost
		1 => 39067,
		2 => 41679,
		3 => 41697, 
		4 => 41717,
	),
	39145 => array(	//Fire Ring
		1 => 39145,
		2 => 42962,
		3 => 42969, 
		4 => 42976,
	),
	39147 => array(	//Shock Ring
		1 => 39147,
		2 => 42966,
		3 => 42973, 
		4 => 42980,
	),
	39146 => array(	//Frost Ring
		1 => 39146,
		2 => 42964,
		3 => 42971, 
		4 => 42978,
	),
	39162 => array(	//Flame Pulsar
		1 => 39162,
		2 => 42983,
		3 => 42990, 
		4 => 42997,
	),	
	39167 => array(	//Storm Pulsar
		1 => 39167,
		2 => 42987,
		3 => 42994, 
		4 => 43001,
	),	
	39163 => array(	//Frost Pulsar
		1 => 39163,
		2 => 42985,
		3 => 42992, 
		4 => 42999,
	),	
	83625 => array(	//Fire Storm
		1 => 83625,
		2 => 86488,
		3 => 86490, 
		4 => 86492,
	),	
	83630 => array(	//Thunder Storm
		1 => 83630,
		2 => 86500,
		3 => 86502, 
		4 => 86504,
	),	
	83628 => array(	//Ice Storm
		1 => 83628,
		2 => 86494,
		3 => 86495, 
		4 => 86496,
	),	
	85126 => array(	//Fiery Rage
		1 => 85126,
		2 => 86512,
		3 => 86513, 
		4 => 86515,
	),	
	85130 => array(	//Thunderous Rage
		1 => 85130,
		2 => 86524,
		3 => 86526, 
		4 => 86528,
	),	
	85128 => array(	//Icy Rage
		1 => 85128,
		2 => 86518,
		3 => 86520, 
		4 => 86522,
	),
	83682 => array(	//Eye of Flame
		1 => 83682,
		2 => 86536,
		3 => 86538, 
		4 => 86540,
	),
	83686 => array(	//Eye of Lightning
		1 => 83686,
		2 => 86548,
		3 => 86550, 
		4 => 86552,
	),
	83684 => array(	//Eye of Frost
		1 => 83684,
		2 => 86542,
		3 => 86544, 
		4 => 86546,
	),
	39075 => array ( //Pack Leader
		1 => 39075,
		2 => 42365,
		3 => 42366,
		4 => 42367,
	),
	39075 => array ( //Pack Leader
		1 => 39075,
		2 => 42365,
		3 => 42366,
		4 => 42367,
	),
	39076 => array ( //Werewolf Berserker
		1 => 39076,
		2 => 42377,
		3 => 42378,
		4 => 42379,
	),

);

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE isPlayer=1 and isPassive=0 and baseAbilityId > 0;";
$result = $db->query($query);
if (!$result) exit("Failed to load skill data!");


$skills = array();
$skillsName = array();

while ($skill = $result->fetch_assoc()) 
{
	$id = intval($skill['id']);
	$name = $skill['name'];
	$rank = intval($skill['rank']);
	
	if ($rank > 4) 
	{
		$rank = $rank % 4 + 1;
		$skill['rank'] = $rank;
	}
	
	if ($rank >= 1 && $rank <= 4)
	{
		$skills[$id] = $skill;
		if ($skillsName[$name] == null) $skillsName[$name] = array( 'id' => $id );
		
		if ($skillsName[$name][$rank] != null) print("\t$id: $name $rank - Duplicate skill rank found!\n");
		$skillsName[$name][$rank] = $id;
	}
	else
	{
		print("\t$id: $name $rank - Invalid rank found!\n");
	}
}

$count = count($skills);
print("\tLoaded $count active player skills!\n");

foreach ($skillsName as $name => $ranks)
{
	$badSkill = false;
	$id = $ranks['id'];
	
	if ($ranks[1] == 0) { print("\t$id: $name 1 missing base id!\n"); $badSkill = true; }
	if ($ranks[2] == 0) { print("\t$id: $name 2 missing base id!\n"); $badSkill = true; }
	if ($ranks[3] == 0) { print("\t$id: $name 3 missing base id!\n"); $badSkill = true; }
	if ($ranks[4] == 0) { print("\t$id: $name 4 missing base id!\n"); $badSkill = true; }
	
	if ($badSkill)
	{
		unset($skills[$id]);
	}
}

$baseRankSkills = array();
$rankSkills = array();

foreach ($skills as $id => $skill)
{
	$rank = intval($skill['rank']);
	$name = $skill['name'];
	$skillRanks = $skillsName[$name];
	$baseId = $skillRanks[1];
	if ($baseId == null) print("\t$id: $name $rank - Base skill not found!\n");
	
	if ($baseRankSkills[$baseId] == null) 
	{
		$baseRankSkills[$baseId] = array( 1 => 0, 2 => 0, 3 => 0, 4 => 0);
	}
	
	if ($rank == 1)
	{
		if ($baseId != $id) print("\t$id: $name $rank - Base skill mismatch!\n");
		$baseRankSkills[$baseId][1] = $baseId;
	}
	elseif ($rank >= 2 && $rank <= 4)
	{
		$baseRankSkills[$baseId][$rank] = $id;
	}
	
	$rankSkills[$id] = array($baseId, $rank);
}

foreach ($EXTRA_SKILLRANK_DATA as $baseId => $rankData)
{
	$id1 = $rankData[1];
	$id2 = $rankData[2];
	$id3 = $rankData[3];
	$id4 = $rankData[4];
	
	$baseRankSkills[$baseId] = array( 1 => $id1, 2 => $id2, 3 => $id3, 4 => $id4);
	
	$rankSkills[$id1] = array($baseId, 1);
	$rankSkills[$id2] = array($baseId, 2);
	$rankSkills[$id3] = array($baseId, 3);
	$rankSkills[$id4] = array($baseId, 4);
}

$phpOutput  = "<?php\n\n" . '$ESO_BASESKILL_RANKDATA = ';
$phpOutput .= var_export($baseRankSkills, true);
$phpOutput .= ";\n";
$phpOutput .= '$ESO_SKILL_RANKDATA = ';
$phpOutput .= var_export($rankSkills, true);
$phpOutput .= ";\n";

file_put_contents($PHP_OUTPUT_FILE, $phpOutput);

$luaOutput = "uespLog.BASESKILL_RANKDATA = {\n";

foreach ($baseRankSkills as $id => $rankData)
{
	$luaOutput .= "\t[$id] = { {$rankData[1]}, {$rankData[2]}, {$rankData[3]}, {$rankData[4]} },\n";
}

$luaOutput .= "}\n";
$luaOutput .= "uespLog.SKILL_RANKDATA = {\n";

foreach ($rankSkills as $id => $rankData)
{
	$luaOutput .= "\t[$id] = { {$rankData[0]}, {$rankData[1]} },\n";
}

$luaOutput .= "}\n";
file_put_contents($LUA_OUTPUT_FILE, $luaOutput);

