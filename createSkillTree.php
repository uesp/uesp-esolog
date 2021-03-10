<?php

require_once("esoCommon.php");
require_once("/home/uesp/secrets/esolog.secrets");

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Creating skill tree from mined skill data...\n");


class CEsoCreateSkillTree
{
	public $TABLE_SUFFIX = "29";
	public $PRINT_TABLE = false;
	public $USE_UPDATE18 = false;

	public $db = null;
	
	public $skills = array();
	public $skillRoots = array();
	public $skillTree = array();
	public $skillRootData = array();
	public $passiveSkills = array();
	public $passiveIds = array();
	public $passiveMaxLevel = array();
	
	public $DUPLICATE_SKILLS = array(
		/*
			array(		// Robust
					"id" => array(36064, 45297, 45298),
					"race1" => "Nord",
					"race2" => "Orc",
					"index1" => 2,
					"index2" => 3,
			), //*/	
/*					// Changed in update 21
			array(		// Magnus
					"id" => array(35995, 45259, 45260),
					"race1" => "Breton",
					"race2" => "High Elf",
					"index1" => 2,
					"index2" => 3,
					"req1" => array(5, 15, 30),
					"req2" => array(10, 20, 40),
			), 
			array(		// Steathly
					"id" => array(36022, 45295, 45296),
					"race1" => "Wood Elf",
					"race2" => "Khajiit",
					"index1" => 4,
					"index2" => 3,
					"req1" => array(25, 35, 50),
					"req2" => array(10, 20, 40),
			), //*/
			/*
			array(		// Shield Affinity
					"id" => array(36312),
					"race1" => "Imperial",
					"race2" => "Redguard",
					"index1" => 1,
					"index2" => 1,
			), 
			array(		// Conditioning
					"id" => array(36153, 45279, 45280),
					"race1" => "Imperial",
					"race2" => "Redguard",
					"index1" => 3,
					"index2" => 3,
			),//*/
	);
	
		
	public function __construct()
	{
	}
	
	
	public function ReportError($msg)
	{
		exit($msg . "\nDB Error: " . $this->db->error . "\n");
		return false;
	}
	
	
	public function InitializeDatabase()
	{
		global $uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("Could not connect to mysql database!");
		
		return true;
	}
	
	
	public function CreateTables()
	{
		$query = "CREATE TABLE IF NOT EXISTS skillTree" . $this->TABLE_SUFFIX . "(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			abilityId BIGINT NOT NULL,
			displayId BIGINT NOT NULL,
			skillTypeName TINYTEXT NOT NULL,
			learnedLevel INTEGER NOT NULL DEFAULT -1,
			maxRank TINYINT NOT NULL DEFAULT -1,
			rank INTEGER NOT NULL DEFAULT -1,
			baseName TINYTEXT NOT NULL,
			name TINYTEXT NOT NULL,
			description TEXT NOT NULL,
			type TINYTEXT NOT NULL,
			cost TINYTEXT NOT NULL,
			icon TINYTEXT NOT NULL,
			skillIndex TINYINT NOT NULL DEFAULT -1,
			INDEX index_abilityId(abilityId),
			INDEX index_skillTypeName(skillTypeName(20)),
			INDEX index_type(type(8))
		) ENGINE=MYISAM;";

		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error creating table!");

		return true;
	}
	
	
	public function ClearTables()
	{
		$query = "DELETE FROM skillTree" . $this->TABLE_SUFFIX . ";";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error (clearing skill tree)!");
		
		return true;
	}
	
	
	public function LoadActiveSkills()
	{
		$query = "SELECT * from minedSkills" . $this->TABLE_SUFFIX . " WHERE nextSkill >= 0 AND isPassive=0 AND isPlayer=1;";
		$skillResult = $this->db->query($query);
		if (!$skillResult) return $this->ReportError("ERROR: Database query error (finding skill lines)!");
		
		$count = 0;
		
		while (($skill = $skillResult->fetch_assoc()))
		{
			$id = $skill['id'];
			$skill['maxLevel'] = 4;
			$this->skills[$id] = $skill;
			$count++;
		}
		
		print("\tFound $count skills with a skill progression.\n");
		return true;
	}
	
	
	public function FindRootActiveSkills()
	{
		
		foreach ($this->skills as $id => $skill)
		{
			if ($skill['nextSkill'] > 0 && $skill['prevSkill'] <= 0)
			{
				$this->skillTree[$id] = array();
				$this->skillTree[$id][1] = $id;
				$this->skillTree[$id][2] = $skill['nextSkill'];
			}		
		}
		
		print("\tFound " . count($this->skillTree) . " root skills\n");
	}
	
	
	public function FindRootActiveSkills18()
	{
		
		foreach ($this->skills as $id => $skill)
		{
			if ($skill['nextSkill'] > 0 && $skill['prevSkill'] <= 0)
			{
				$this->skillTree[$id] = array();
				$this->skillTree[$id][1] = $id;
				$this->skillTree[$id][2] = $id;
				$this->skillTree[$id][3] = $id;
				$this->skillTree[$id][4] = $id;
				$this->skillTree[$id][5] = $skill['nextSkill'];
				$this->skillTree[$id][6] = $skill['nextSkill'];
				$this->skillTree[$id][7] = $skill['nextSkill'];
				$this->skillTree[$id][8] = $skill['nextSkill'];
				$this->skillTree[$id][9] = $skill['nextSkill2'];
				$this->skillTree[$id][10] = $skill['nextSkill2'];
				$this->skillTree[$id][11] = $skill['nextSkill2'];
				$this->skillTree[$id][12] = $skill['nextSkill2'];
			}		
		}
		
		print("\tFound " . count($this->skillTree) . " root skills\n");
	}
	
	
	public function FollowActiveSkillTree()
	{
		
		for ($skillIndex = 2; $skillIndex <= 12; $skillIndex++)
		{
			foreach ($this->skillTree as $id => $skillLine)
			{
				$lastSkillId = $skillLine[$skillIndex];
				$nextSkill  = $this->skills[$lastSkillId]['nextSkill'];
				$nextSkill2 = $this->skills[$lastSkillId]['nextSkill2'];
				
				if ($nextSkill  > 0) $this->skillTree[$id][$skillIndex + 1] = $nextSkill;
				if ($nextSkill2 > 0) $this->skillTree[$id][$skillIndex + 5] = $nextSkill2;
			}
		}
		
	}
	
	
	public function CreateSkillRootData()
	{
		
		foreach ($this->skillTree as $id => $skillTreeLine)
		{
			$this->skillRootData[$id] = array();
			
			foreach ($skillTreeLine as $index => $skillLineId)
			{
				$skill = $this->skills[$skillLineId];
				
				if ($skill['skillType']    > 0) $this->skillRootData[$id]['skillType'] = $skill['skillType'];
				if ($skill['learnedLevel'] > 0) $this->skillRootData[$id]['learnedLevel'] = $skill['learnedLevel'];
				if ($skill['skillIndex']   > 0) $this->skillRootData[$id]['skillIndex'] = $skill['skillIndex'];
				if ($skill['skillLine']  != "") $this->skillRootData[$id]['skillLine'] = $skill['skillLine'];
				if ($skill['classType']  != "") $this->skillRootData[$id]['classType'] = $skill['classType'];
				if ($skill['raceType']   != "") $this->skillRootData[$id]['raceType']  = $skill['raceType'];
			}
		}
	}
	
	
	public function PropagateActiveSkillData()
	{

		foreach($this->skillTree as $id => $skillTreeLine)
		{
			foreach ($skillTreeLine as $index => $skillLineId)
			{
				$this->skills[$skillLineId]['skillType']    = $this->skillRootData[$id]['skillType'];
				$this->skills[$skillLineId]['learnedLevel'] = $this->skillRootData[$id]['learnedLevel'];
				$this->skills[$skillLineId]['skillIndex']   = $this->skillRootData[$id]['skillIndex'];
				$this->skills[$skillLineId]['skillLine']    = $this->skillRootData[$id]['skillLine'];
				$this->skills[$skillLineId]['classType']    = $this->skillRootData[$id]['classType'];
				$this->skills[$skillLineId]['raceType']     = $this->skillRootData[$id]['raceType'];
			}
		}
	}
	
	
	public function PrintSkillTree()
	{
		foreach($this->skillTree as $id => $skillTreeLine)
		{
			$skillId1 = $skillTreeLine[1];
			$skillId2 = $skillTreeLine[5];
			$skillId3 = $skillTreeLine[9];
			$line = $this->skills[$skillId1]['skillLine'];
			$type = $this->skills[$skillId1]['skillType'];
			$class = $this->skills[$skillId1]['classType'];
			$race = $this->skills[$skillId1]['raceType'];
			$skillIndex = $this->skills[$skillId1]['skillIndex'];
			$name1 = $this->skills[$skillId1]['name'];
			$name2 = $this->skills[$skillId2]['name'];
			$name3 = $this->skills[$skillId3]['name'];
			
			$desc1 = $this->skills[$skillTreeLine[1]]['description'];
			$desc2 = $this->skills[$skillTreeLine[2]]['description'];
			$desc3 = $this->skills[$skillTreeLine[3]]['description'];
			$desc4 = $this->skills[$skillTreeLine[4]]['description'];
			
			print("\t$name1 $skillId1: $type $line $class $race $skillIndex\n");
			//print("\t\t Rank 1: $desc1\n");
			//print("\t\t Rank 2: $desc2\n");
			//print("\t\t Rank 3: $desc3\n");
			//print("\t\t Rank 4: $desc4\n");
			
			$desc1 = $this->skills[$skillTreeLine[5]]['description'];
			$desc2 = $this->skills[$skillTreeLine[6]]['description'];
			$desc3 = $this->skills[$skillTreeLine[7]]['description'];
			$desc4 = $this->skills[$skillTreeLine[8]]['description'];
			
			print("\t\t$name2 $skillId2\n");
			//print("\t\t Rank 1: $desc1\n");
			//print("\t\t Rank 2: $desc2\n");
			//print("\t\t Rank 3: $desc3\n");
			//print("\t\t Rank 4: $desc4\n");
			
			$desc1 = $this->skills[$skillTreeLine[9]]['description'];
			$desc2 = $this->skills[$skillTreeLine[10]]['description'];
			$desc3 = $this->skills[$skillTreeLine[11]]['description'];
			$desc4 = $this->skills[$skillTreeLine[12]]['description'];
			
			print("\t\t$name3 $skillId3\n");
			//print("\t\t Rank 1: $desc1\n");
			//print("\t\t Rank 2: $desc2\n");
			//print("\t\t Rank 3: $desc3\n");
			//print("\t\t Rank 4: $desc4\n");
		}
	}
	
	
	public function PropagateActiveSkillEffects()
	{
	
		foreach($this->skillTree as $id => $skillTreeLine)
		{
			if ($skillTreeLine[5] == null || $skillTreeLine[9] == null) continue;
			
			$skillLineId5 = $skillTreeLine[5];
			$skillLineId9 = $skillTreeLine[9];
			if ($this->skills[$skillLineId5] == null || $this->skills[$skillLineId9] == null) continue;
			
			$effectLine1 = $this->skills[$skillLineId5]['effectLines'];
			$effectLine2 = $this->skills[$skillLineId9]['effectLines'];
			if ($effectLine1 == "" && $effectLine2 == "") continue;
			
			for ($index = 1; $index <= 12; $index++)
			{
				$skillLineId = $skillTreeLine[$index];
				$skill = $this->skills[$skillLineId];
				$rank = $index;
		
				if ($skillLineId != "" && $rank > 5 && $rank < 9)
				{
					//print("\tSetting effect line for $id morph 1\n");
					$this->skills[$skillLineId]['effectLines'] = $effectLine1;
				}
				elseif ($skillLineId != "" && $rank > 9 && $rank < 13)
				{
					//print("\tSetting effect line for $id morph 2\n");
					$this->skills[$skillLineId]['effectLines'] = $effectLine2;
				}
			}
		}
		
	}
	
	
	public function SaveActiveSkills()
	{
		print("Updating skill data...\n");
		
		foreach ($this->skills as $id => $skill)
		{
			
			if ($id == "")
			{
				print_r($skill);
				continue;
			}
			
			$classType = $this->db->real_escape_string($skill['classType']);
			$raceType = $this->db->real_escape_string($skill['raceType']);
			$skillType = $this->db->real_escape_string($skill['skillType']);
			$skillLine = $this->db->real_escape_string($skill['skillLine']);
			$learnedLevel = $this->db->real_escape_string($skill['learnedLevel']);
			$skillIndex = $this->db->real_escape_string($skill['skillIndex']);
			$effectLines = $this->db->real_escape_string($skill['effectLines']);
			
			$query = "UPDATE minedSkills" . $this->TABLE_SUFFIX . " SET skillType=\"$skillType\", raceType=\"$raceType\", classType=\"$classType\", skillLine=\"$skillLine\", learnedLevel=\"$learnedLevel\", skillIndex=\"$skillIndex\", effectLines=\"$effectLines\" WHERE id=$id;";
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("ERROR: Database query error updating skills table!\n$query");
		}
		
		return true;
	}
	
	
	public function SaveActiveSkillTree()
	{
		print("Creating skill tree...\n");
		
		foreach ($this->skillTree as $id => $skillTreeLine)
		{
			$skill = $this->skills[$id];
			
			if ($skill['skillType'] == 1)
			{
				$skillTypeName = $skill['classType'] . "::" . $skill['skillLine'];
			}
			elseif ($skill['skillType'] == 7)
			{
				$skillTypeName = "Racial::" . $skill['skillLine'];
			}
			else
			{
				$skillTypeName = GetEsoSkillTypeText($skill['skillType']) . "::" . $skill['skillLine'];
			}
			
			$rootSkill = $this->skills[$skillTreeLine[1]];
			$skillTypeName = $this->db->real_escape_string($skillTypeName);
			$baseName = $this->db->real_escape_string($rootSkill['name']);
			
			$type = "Active";
			
			if ($rootSkill['mechanic']  == 10) $type = "Ultimate";
			if ($rootSkill['isPassive'] ==  1) $type = "Passive";
			
			for($index = 1; $index <= 12; $index++)
			{
				$skillLineId = $skillTreeLine[$index];
				$thisSkill = $this->skills[$skillLineId];
				$displayId = $thisSkill['displayId'];
				$name = $this->db->real_escape_string($thisSkill['name']);
				$desc = $this->db->real_escape_string($thisSkill['description']);
				$cost = "" . $thisSkill['cost'] . " " . GetEsoCombatMechanicText($thisSkill['mechanic']);
				$icon = $this->db->real_escape_string($thisSkill['texture']);
				$learnedLevel = $thisSkill['learnedLevel'];
				$abilityIndex = $thisSkill['skillIndex'];
				$maxLevel = $thisSkill['maxLevel'];
				
				$query = "INSERT INTO skillTree" . $this->TABLE_SUFFIX . "(abilityId,skillTypeName,rank,baseName,name,description,type,cost,icon,learnedLevel,skillIndex,maxRank,displayId) ";
				$query .= " VALUES('$skillLineId','$skillTypeName','$index',\"$baseName\",\"$name\",\"$desc\",'$type','$cost',\"$icon\", \"$learnedLevel\",\"$abilityIndex\", \"$maxLevel\", \"$displayId\")";
				$result = $this->db->query($query);
				if (!$result) $this->ReportError("ERROR: Database query error inserting into skillTree database!");
			}
		}
		
		return true;
	}
	
	public function LoadPassives()
	{
		$query = "SELECT * FROM minedSkills" . $this->TABLE_SUFFIX . " WHERE isPassive=1 AND isPlayer=1;";
		$passiveResult = $this->db->query($query);
		if (!$passiveResult) return $this->ReportError("ERROR: Database query error finding passive skills!");
		
		$count = 0;
		$type = "Passive";
		$skillTypeName = "";
		$index = 0;
		
		print("Loading passives...\n");
		
		while (($passive = $passiveResult->fetch_assoc()))
		{
			$id = $passive['id'];
			$passive['name'] = preg_replace("#(.*) [IV]+$#", "$1", $passive['name']);
			$passive['baseName'] = preg_replace("#(.*) [IV]+$#", "$1", $passive['baseName']);
			$passive['maxLevel'] = 1;
			
			$this->passiveSkills[] = $passive;
			$this->passiveIds[$id] = &$this->passiveSkills[count($this->passiveSkills) - 1];
			$this->passiveMaxLevel[$id] = 1;
			$count++;
		}
		
		print("Loaded ".count($this->passiveIds)." passives!\n");
		return true;
	}
	
	
	public function UpdatePassiveNextPrevData()
	{
		print("Updating next/prev pointers in passive data...\n");

		foreach ($this->passiveSkills as $index => $passive)
		{
			$id = $passive['id'];
			$nextSkill = $passive['nextSkill'];
			$prevSkill = $passive['prevSkill'];
			
			$hasPrevSkill = array_key_exists($prevSkill, $this->passiveIds);
			$hasNextSkill = array_key_exists($nextSkill, $this->passiveIds);
				
			if ($hasPrevSkill && $hasNextSkill)
			{
				$this->passiveIds[$nextSkill]['prevSkill'] = $id;
				$this->passiveIds[$prevSkill]['nextSkill'] = $id;
			}
			else if ($hasPrevSkill)
			{
				$this->passiveIds[$prevSkill]['nextSkill'] = $id;
			}
			else if ($hasNextSkill)
			{
				$this->passiveIds[$nextSkill]['prevSkill'] = $id;
			}
				
			if ($passive['skillType'] == 1)
			{
				$skillTypeName = $passive['classType'] . "::" . $passive['skillLine'];
			}
			elseif ($passive['skillType'] == 7)
			{
				$skillTypeName = "Racial::" . $passive['skillLine'];
			}
			else
			{
				$skillTypeName = GetEsoSkillTypeText($passive['skillType']) . "::" . $passive['skillLine'];
			}
		
			$this->passiveSkills[$index]['skillTypeName'] = $skillTypeName;
		}
	}
	
	
	public function UpdatePassiveTypeNames()
	{
		print("Updating passive skill type name...\n");

		foreach ($this->passiveSkills as $index => $passive)
		{
			if ($passive['skillType'] == 1)
			{
				$skillTypeName = $passive['classType'] . "::" . $passive['skillLine'];
			}
			elseif ($passive['skillType'] == 7)
			{
				$skillTypeName = "Racial::" . $passive['skillLine'];
			}
			else
			{
				$skillTypeName = GetEsoSkillTypeText($passive['skillType']) . "::" . $passive['skillLine'];
			}
		
			$this->passiveSkills[$index]['skillTypeName'] = $skillTypeName;
		}
	}
	
	
	public function CountPassiveLevels()
	{
		print("Counting passive maxLevels...\n");
		
		foreach ($this->passiveSkills as $passive)
		{
			$id = $passive['id'];
			$nextSkillId = $passive['nextSkill'];
			$prevSkillId = $passive['prevSkill'];
			
			if ($nextSkillId <= 0 || $prevSkillId > 0) continue;
			if (!array_key_exists($nextSkillId, $this->passiveIds)) continue;
			$maxLevelCount = 1;
			$nextSkill = $this->passiveIds[$nextSkillId];
			
			while ($nextSkill != null)
			{
				$maxLevelCount = $maxLevelCount + 1;
				$nextSkillId = $nextSkill['nextSkill'];
				if (!array_key_exists($nextSkillId, $this->passiveIds)) break;
				$nextSkill = $this->passiveIds[$nextSkillId];
			
				if ($maxLevelCount > 20) break;
			}
			
			$this->passiveMaxLevel[$id] = $maxLevelCount;
			//print("\tMaxLevel for $id is $maxLevelCount\n");
		}
	}
	
	
	public function PropagatePassiveMaxLevel()
	{
		print("Propagating passive maxLevels...\n");
		
		foreach ($this->passiveSkills as $index => $passive)
		{
			$id = $passive['id'];
			$nextSkillId = $passive['nextSkill'];
			$prevSkillId = $passive['prevSkill'];
				
			if ($nextSkillId <= 0 || $prevSkillId > 0) continue;
			if (!array_key_exists($nextSkillId, $this->passiveIds)) continue;
			$nextSkill = $this->passiveIds[$nextSkillId];
			$maxLevelCount = 1;
			
			$maxLevel = $this->passiveMaxLevel[$id];
			$this->passiveIds[$id]['maxLevel'] = $maxLevel;
				
			while ($nextSkill != null)
			{
				$maxLevelCount = $maxLevelCount + 1;
				
				$this->passiveIds[$nextSkillId]['maxLevel'] = $maxLevel;
				$nextSkillId = $nextSkill['nextSkill'];
				if (!array_key_exists($nextSkillId, $this->passiveIds)) break;
				$nextSkill = $this->passiveIds[$nextSkillId];
				
				if ($maxLevelCount > 20) break;
			}
		}
	}
	
	
	public function HandleDuplicatePassives()
	{
		$newPassives = array();
		
		foreach ($this->DUPLICATE_SKILLS as $dupSkill)
		{
			$ids = $dupSkill['id'];
			$race1 = $dupSkill['race1'];
			$race2 = $dupSkill['race2'];
			$index1 = $dupSkill['index1'];
			$index2 = $dupSkill['index2'];
			$req1 = $dupSkill['req1'];
			$req2 = $dupSkill['req2'];
			
			foreach ($ids as $id)
			{
				$passive = $this->passiveIds[$id];
				$rank = intval($passive['rank']);
				
				if ($passive == null) 
				{
					print("\tSkill $id not found!\n");
					continue;
				}
				
				if ($passive['raceType'] != $race1)
				{
					$newPassive = $passive;
					$newPassive['raceType'] = $race1;
					$newPassive['skillLine'] = $race1 . " Skills";
					$newPassive['skillTypeName'] = "Racial::" . $newPassive['skillLine'];
					$newPassive['skillIndex'] = $index1;
					if ($req1 && $req1[$rank-1]) $newPassive['learnedLevel'] = $req1[$rank-1];
					$newPassives[] = $newPassive;
					print("\tAdded skill $id for $race1\n");
								
					if ($req2 && $req2[$rank-1]) $this->passiveSkills[$id]['learnedLevel'] = $req2[$rank-1];
				}
				
				if ($passive['raceType'] != $race2)
				{
					$newPassive = $passive;
					$newPassive['raceType'] = $race2;
					$newPassive['skillLine'] = $race2 . " Skills";
					$newPassive['skillTypeName'] = "Racial::" . $newPassive['skillLine'];
					$newPassive['skillIndex'] = $index2;
					if ($req2 && $req2[$rank-1]) $newPassive['learnedLevel'] = $req2[$rank-1];
					$newPassives[] = $newPassive;
					print("\tAdded skill $id for $race2\n");
					
					if ($req1 && $req1[$rank-1]) $this->passiveSkills[$id]['learnedLevel'] = $req1[$rank-1];
				}
			}
		}
		
		$this->passiveSkills = array_merge($this->passiveSkills, $newPassives);
	}

	
	public function SortPassives()
	{
		print("Sorting passive data...\n");
		usort($this->passiveSkills, "ComparePassives");
	}
	
	
	public function SavePassiveSkillTree()
	{
		print("Creating passive skill tree and saving data...\n");
		$count = 0;

		foreach ($this->passiveSkills as $passive)
		{	
			$count++;
			
			$id = $passive['id'];
			$displayId = $passive['displayId'];
			$name = $this->db->real_escape_string($passive['name']);
			$baseName = $name;
			$desc = $this->db->real_escape_string($passive['description']);
			$icon = $this->db->real_escape_string($passive['texture']);
			$rank = $passive['rank'];
			$learnedLevel = $passive['learnedLevel'];
			$abilityIndex = $passive['skillIndex'];
			$maxLevel = $passive['maxLevel'];
			
			if ($learnedLevel < 0) $learnedLevel = 1;
			
			$skillTypeName = $this->db->real_escape_string($passive['skillTypeName']);
			
			$query = "INSERT INTO skillTree" . $this->TABLE_SUFFIX . "(abilityId,skillTypeName,rank,baseName,name,description,type,cost,icon,learnedLevel,skillIndex,maxRank,displayId) ";
			$query .= " VALUES('$id','$skillTypeName','$rank',\"$baseName\",\"$name\",\"$desc\",'Passive','None',\"$icon\", \"$learnedLevel\", \"$abilityIndex\", \"$maxLevel\", \"$displayId\")";
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("ERROR: Database query error inserting into skillTree table!");
			
			$nextSkill = $passive['nextSkill'];
			$prevSkill = $passive['prevSkill'];
			
			$query = "UPDATE minedSkills" . $this->TABLE_SUFFIX . " SET nextSkill='$nextSkill', prevSkill='$prevSkill' WHERE id='$id';";
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("ERROR: Database query error updating minedSkill table!");
		}
		
		print("\tFound and saved $count passive player skills.\n");
		return true;
	}
	
	
	public function Create18()
	{
		if (!$this->InitializeDatabase()) return false;
		if (!$this->CreateTables()) return false;
		if (!$this->ClearTables()) return false;
		if (!$this->LoadActiveSkills()) return false;
		
		//$this->FindRootActiveSkills18();
		$this->FindRootActiveSkills();
		$this->CreateSkillRootData();
		$this->PropagateActiveSkillData();
		$this->PropagateActiveSkillEffects();
		
		if ($this->PRINT_TABLE) $this->PrintSkillTree();
		
		//if (!$this->SaveActiveSkills()) return false;
		if (!$this->SaveActiveSkillTree()) return false;
		
		if (!$this->LoadPassives()) return false;
		$this->UpdatePassiveNextPrevData();
		$this->UpdatePassiveTypeNames();
		$this->CountPassiveLevels();
		$this->PropagatePassiveMaxLevel();
		$this->HandleDuplicatePassives();
		$this->SortPassives();
		
		if (!$this->SavePassiveSkillTree()) return false;
		
		print("Done!\n");
		return true;
	}
	
				
	public function Create()
	{
		if ($this->USE_UPDATE18) return $this->Create18();
		
		if (!$this->InitializeDatabase()) return false;
		if (!$this->CreateTables()) return false;
		if (!$this->ClearTables()) return false;
		if (!$this->LoadActiveSkills()) return false;
		
		$this->FindRootActiveSkills();
		$this->FollowActiveSkillTree();
		$this->CreateSkillRootData();
		$this->PropagateActiveSkillData();
		$this->PropagateActiveSkillEffects();
		
		if ($this->PRINT_TABLE) $this->PrintSkillTree();
		
		if (!$this->SaveActiveSkills()) return false;
		if (!$this->SaveActiveSkillTree()) return false;
		
		if (!$this->LoadPassives()) return false;
		$this->UpdatePassiveNextPrevData();
		$this->UpdatePassiveTypeNames();
		$this->CountPassiveLevels();
		$this->PropagatePassiveMaxLevel();
		$this->HandleDuplicatePassives();
		$this->SortPassives();
		
		if (!$this->SavePassiveSkillTree()) return false;
		
		print("Done!\n");
		return true;
	}
	

};

	
function ComparePassives($a, $b)
{
	$compare = strcmp($a['skillTypeName'], $b['skillTypeName']);
	$compare = $a['skillIndex'] - $b['skillIndex'];
	$compare = $a['learnedLevel'] - $b['learnedLevel'];
	return $compare;
	
	if ($compare == 0)
	{
		$compare = $a['skillIndex'] - $b['skillIndex'];
		
		if ($compare == 0)
		{
			$compare = $a['learnedLevel'] - $b['learnedLevel'];
			
			if ($compare == 0)
			{
				$compare = $a['rank'] - $b['rank'];
			}
		}
	}
	
	return $compare;
}


$g_CreateSkillTree = new CEsoCreateSkillTree();
$g_CreateSkillTree->Create();


