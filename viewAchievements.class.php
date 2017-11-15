<?php 

require_once("/home/uesp/secrets/esolog.secrets");
require_once("/home/uesp/esolog.static/esoCommon.php");
require_once("/home/uesp/esolog.static/esoAchievementData.php");


class CEsoViewAchievements 
{
	public $ESOVA_HTML_TEMPLATE = "";
	public $ESOVA_HTML_TEMPLATE_EMBED = "";
	
	public $isEmbedded = false;
	
	public $db = null;
	
	public $inputParams = array();
	public $htmlTemplate = "";
	public $dataLoaded = false;
	
	public $characterData = null;
	
	
	public function __construct ($parseParams = true)
	{
		$this->ESOVA_HTML_TEMPLATE = __DIR__."/templates/esoachievement_template.txt";
		$this->ESOVA_HTML_TEMPLATE_EMBED = __DIR__."/templates/esoachievement_embed_template.txt";

		if ($parseParams)
		{
			$this->SetInputParams();
			$this->ParseInputParams();
		}
		
		$this->InitDatabase();
	}	
	

	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		return false;
	}
	
	
	public function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		UpdateEsoPageViews("achievementViews");
	
		return true;
	}
	
	
	public function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
	
		$origin = $_SERVER['HTTP_ORIGIN'];
	
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}
		
	
	public function SetInputParams ()
	{
		$this->inputParams = $_REQUEST;
	}
	
	
	public function ParseInputParams ()
	{
	}
	
	
	public function LoadTemplate()
	{
		$templateFile = "";
	
		if ($this->isEmbedded)
			$templateFile .= $this->ESOVA_HTML_TEMPLATE_EMBED;
		else
			$templateFile .= $this->ESOVA_HTML_TEMPLATE;
					
		$this->htmlTemplate = file_get_contents($templateFile);
	}
	
	
	public function escape($input)
	{
		return htmlspecialchars($input, ENT_COMPAT, 'UTF-8');
	}
	
	
	public function LoadData()
	{
		$this->LoadTemplate();
	
		$this->dataLoaded = true;
		return true;
	}
	
	
	public function GetUpdateDate()
	{
		$query = "SELECT * FROM logInfo WHERE id='lastAchievementUpdate';";
		$result = $this->db->query($query);
		if (!$result) return "";
		
		$row = $result->fetch_assoc();
		$updateDate = $row['value'];
		
		return $updateDate;
	}
	
	
	public function GetCharStatField($field, $default = "")
	{
		if ($this->characterData == null) return $default;
		if (!array_key_exists($field, $this->characterData['stats'])) return $default;
		return $this->escape($this->characterData['stats'][$field]['value']);
	}
	
	
	public function GetCharAchievementData($achId)
	{
		$charAchData = $this->GetCharStatField("Achievement:$achId", "");
		if ($charAchData == "") return false;
	
		$data = explode(",", $charAchData);
		$progress = $data[0];
		$timestamp = intval($data[1]);
		if ($progress === null) $progress = "";
	
		return array($progress, $timestamp);
	}
	
	
	public function ParseCharAchievementProgress($achId, $progress)
	{
		global $ESO_ACHIEVEMENT_DATA;
	
		$achData = $ESO_ACHIEVEMENT_DATA[$achId];
		if ($achData == null) return array(0);
	
		if ($progress === null || $progress === "") return array_fill(1, count($achData['criteria']), 0);
		if (count($achData['criteria']) == 1) return array( 1 => intval($progress));
	
		$progressResult = array();
	
		foreach ($achData['criteria'] as $index => $criteria)
		{
			$value = $criteria['value'];
				
			$nextPowerof2 = ceil(log($value + 1, 2));
				
			$progressResult[$index] = $progress & (pow(2, $nextPowerof2) - 1);
			$progress = $progress >> $nextPowerof2;
		}
	
		return $progressResult;
	}
	
	
	public function GetAchievementSummaryContentHtml()
	{
		global $ESO_ACHIEVEMENT_CATEGORIES, $ESO_ACHIEVEMENT_DATA, $ESO_ACHIEVEMENT_TREE;
	
		$output = "";
		$output1 = "";

		$output1 .= "<div id='ecdAch_Summary' class='ecdAchData ecdScrollContent' style='display: block;'>";
		$output1 .= "<div class='ecdAchContentTitle'>SUMMARY</div>";

		$pointsAll = 0;
		$totalAll = 0;
		
		foreach ($ESO_ACHIEVEMENT_TREE as $catName => $catData)
		{
			$category = $ESO_ACHIEVEMENT_CATEGORIES[$catName];
			if ($category == null) continue;
			
			$displayName = strtoupper($catName); 
			$index = $category['index'];
			
			$points = 1;
			$total = 1;
			
			$pointsData = $this->GetCharStatField("AchievementPoints:$index", "");
			
			if ($pointsData != "")
			{
				$splitData = explode(",", $pointsData);
				$points = intval($splitData[0]);
				$total = intval($splitData[1]);
				if ($total <= 0) continue;
				
				$totalAll += $total;
			}
			else
			{
				$total = $category['points'];
				$points = $total;
				$totalAll += $total;
			}
			
			$percentWidth = intval($points * 100 / $total);
			$output .= "<div class='ecdAchSummaryBlock'>";
			$output .= "<div class='ecdAchSummaryName'>$displayName</div>";
			$output .= "<div class='ecdAchSmallStatusBar' style='background-size: $percentWidth% 100%;'><div class='ecdAchSmallStatusBarFrame'></div>";
			$output .= "<div class='ecdAchStatusBarSmallPoints'>$points/$total</div></div>";
			$output .= "</div>";
		}
		
		$pointsAll = $this->GetCharStatField("AchievementEarnedPoints", 0);
		$totalAllChar = $this->GetCharStatField("AchievementTotalPoints", 0);
		if ($totalAllChar == 0) $pointsAll = $totalAll;
		
		if ($totalAll > 0)
		{
			$percentWidth = intval($pointsAll * 100 / $totalAll);
			$output1 .= "<p/>";
			$output1 .= "<div class='ecdAchSummaryName'>ACHIEVEMENT POINTS EARNED</div>";
			$output1 .= "<div class='ecdAchLargeStatusBar' style='background-size: $percentWidth% 100%;'><div class='ecdAchLargeStatusBarFrame'></div>";
			$output1 .= "<div class='ecdAchStatusBarLargePoints'>$pointsAll/$totalAll</div></div>";
		}
		
		$output .= "</div>";
		return $output1 . $output;
	}
	
	
	public function GetAchievementListContentHtml($achList)
	{
		global $ESO_ACHIEVEMENT_DATA;
		
		if ($achList === null || count($achList) == 0) return "";
		
		$output = "";
		$count = 0;
		$progress = "";
		$timestamp = 0;
		
		$displayDate = "";
		$displayId = 0;
		$displayProgress = 0;
		$displayTimestamp = 0;
		$displayIsKnown = false;
		$firstUnknown = true;
		
		foreach ($achList as $index => $achId)
		{
			$achData = $ESO_ACHIEVEMENT_DATA[$achId];
			if ($achData == null) continue;
			
			if ($displayId <= 0) $displayId = $achId;
			
			$charAchData = $this->GetCharAchievementData($achId);
			
			if ($charAchData)
			{
				$progress = $charAchData[0];
				$timestamp = $charAchData[1];
					
				if (($timestamp <= 0 && $firstUnknown) || $timestamp > 0)
				{
					$displayId = $achId;
					$displayProgress = $progress;
					$displayTimestamp = $timestamp;
					$displayDate = date("d/m/Y", $timestamp);
					$displayIsKnown = ($timestamp > 0);
					
					if ($timestamp <= 0) 
					{
						$displayDate = "";
						$firstUnknown = false;
					}
				}
			}
			else
			{
				$displayIsKnown = true;
			}
		}
		
		$knownClass = "ecdAchUnknown";
		if ($displayIsKnown) $knownClass = "";
		
		$achData = $ESO_ACHIEVEMENT_DATA[$displayId];
		
		$iconUrl = MakeEsoIconLink($achData['icon']);
		
		$name = $achData['name'];
		$name = preg_replace("#\<\<[A-Za-z]+\{(.*?)\}\>\>#", '$1', $name);
		$name = $this->escape($name);
		
		$desc = $this->escape($achData['desc']);
		$points = $this->escape($achData['points']);
		
		$blockOutput  = $this->GetAchievementCriteriaHtml($displayId);
		$blockOutput .= $this->GetAchievementSubBlockHtml($achList);
		$rewardOutput = $this->GetAchievementRewardBlockHtml($achList);
		
		$extraClass = "";
		if ($blockOutput != "" || $rewardOutput != "") $extraClass = "ecdSelectAchievement1";
		
		$output .= "<div class='ecdAchievement1 $knownClass $extraClass' achieveid='$achId'>";
		$output .= "<div class='ecdAchIconFrame'><img src='$iconUrl' class='ecdAchIcon'></div>";
		$output .= "<div class='ecdAchMidBlock'>";
		$output .= "<div class='ecdAchName'>$name</div>";
		$output .= "<div class='ecdAchDesc'>$desc</div>";
		$output .= "</div>";
		$output .= "<div class='ecdAchRightBlock'>";
		$output .= "<div class='ecdAchPoints'>$points</div>";
		$output .= "<div class='ecdAchDate'>$displayDate</div>";
		$output .= "</div>";
				
		if ($blockOutput != "" || $rewardOutput != "")
		{
			$output .= "<div class='ecdAchDataBlock' style='display: none;' >";
			$output .= $blockOutput;
			$output .= $rewardOutput;
			$output .= "</div>";
		}
		
		if ($rewardOutput != "")
		{
			$output .= "<img src='//esoicons.uesp.net/esoui/art/achievements/achievements_reward_earned.png' class='achAchRewardIcon1'>";
		}

		$output .= "</div>";
		return $output;
	}
	
	
	public function GetAchievementRewardBlockHtml($achList)
	{
		global $ESO_ACHIEVEMENT_DATA;
				
		foreach ($achList as $index => $achId)
		{
			$achData = $ESO_ACHIEVEMENT_DATA[$achId];
			if ($achData == null) continue;
			
			$charAchData = $this->GetCharAchievementData($achId);
			$isKnown = false;
			
			if ($charAchData)
			{
				$isKnown = $charAchData[1] > 0;
			}
			
			$knownClass = "ecdAchUnknown";
			if ($isKnown || $charAchData == null) $knownClass = "ecdAchKnown";
			
			if ($achData['title'] != null)
			{
				$output .= "<div class='ecdAchReward $knownClass'>";
				$output .= "Title: " . $this->escape($achData['title']);
				$output .= "</div>";
			}
			
			if ($achData['dyeName'] != null)
			{
				$output .= "<div class='ecdAchReward $knownClass'>";
				$output .= "Dye: <div class='ecdAchRewardDyeFrame'><div class='ecdAchRewardDye' style='background-color: #{$achData['dyeColor']};'></div></div> ". $this->escape($achData['dyeName']);
				$output .= "</div>";
			}
			
			if ($achData['itemName'] != null)
			{
				$output .= "<div class='ecdAchReward $knownClass'>";
				$output .= "Item: ";
				
				if ($achData['itemIcon'] != "")
				{
					$iconUrl = MakeEsoIconLink($achData['itemIcon']);
					$output .= "<img src='$iconUrl' class='ecdAchRewardItemIcon'> ";
				}
				
				$output .= $this->escape($achData['itemName']);
				$output .= "</div>";
			}
			
			if ($achData['collectId'] != null)
			{
				$output .= "<div class='ecdAchReward $knownClass' collectid='{$achData['collectId']}'>";
				$output .= "Collectible: ";
				
				if ($achData['collectIcon'] != "")
				{
					$iconUrl = MakeEsoIconLink($achData['collectIcon']);
					$output .= "<img src='$iconUrl' class='ecdAchRewardItemIcon'> ";
				}
				
				$output .= $achData['collectName'];
				$output .= "</div>";
			}
		}
		
		if ($output == "") return "";
		$output = "<div class='ecdAchRewardList'>" . $output . "</div>";
		
		return $output;
	}
	
	
	public function GetAchievementContentHtml()
	{
		global $ESO_ACHIEVEMENT_CATEGORIES, $ESO_ACHIEVEMENT_DATA, $ESO_ACHIEVEMENT_TREE;
		
		$output = "";
		$output .= $this->GetAchievementSummaryContentHtml();
		
		foreach ($ESO_ACHIEVEMENT_TREE as $catName => $catData)
		{
			foreach ($catData as $subCatName => $subCatData)
			{
				$catData = $ESO_ACHIEVEMENT_CATEGORIES["$catName::$subCatName"];
				
				$idName = "ecdAch_" . $catName . "_" . $subCatName;
				$idName = str_replace("'", '', str_replace(' ', '', $idName));
				
				$displayCat = strtoupper($catName);
				$displaySubCat = strtoupper($subCatName);
				
				$points = 0;
				$total = 0;
				
				if ($catData)
				{
					$total = $catData['points'];
					$index = $catData['index'];
					$subIndex = $catData['subIndex'];
					
					$pointsData = $this->GetCharStatField("AchievementPoints:$index:$subIndex", "");
					
					if ($pointsData !== "")
					{
						$splitData = explode(",", $pointsData);
						if ($splitData[0] != null) $points = intval($splitData[0]);
						if ($splitData[1] != null) $total = intval($splitData[1]);
					}
					else
					{
						$points = $total;
					}
				}
				
				$output .= "<div id='$idName' class='ecdAchData ecdScrollContent' style='display: none;'>";
				$output .= "<div class='ecdAchContentTitle'>$displayCat: $displaySubCat</div>";
				
				if ($total > 0)
				{
					$percentWidth = intval(100 * $points / $total);
					$output .= "<div class='ecdAchCatePoints'>";
					$output .= "<div class='ecdAchStatusBar' style='background-size: $percentWidth% 100%;'><div class='ecdAchStatusBarFrame'></div>";
					$output .= "<div class='ecdAchCatePointsText'>$points / $total</div>";
					$output .= "</div></div>";
				}
				
				foreach ($subCatData as $subIndex => $achList) 
				{
					$output .= $this->GetAchievementListContentHtml($achList);	
				}
				
				$output .= "</div>";
			}
		}
		
		return $output; 
	}
	
	
	public function GetAchievementCriteriaHtml($achId)
	{
		global $ESO_ACHIEVEMENT_DATA;
	
		$achData = $ESO_ACHIEVEMENT_DATA[$achId];
		if ($achData == null) return "";
	
		$numCriteria = count($achData['criteria']);
	
		if ($numCriteria <= 0) return "";
		if ($numCriteria == 1 && $achData['criteria'][1]['value'] == 1) return "";
	
		$output = "<div class='ecdAchCriteriaList'>";
	
		$charAchData = null;
		$progressData = array();
		$charAchData = $this->GetCharAchievementData($achId);
		if ($charAchData) $progressData = $this->ParseCharAchievementProgress($achId, $charAchData[0]);
	
		foreach ($achData['criteria'] as $index => $criteria)
		{
			$name = $criteria['name'];
			$value = $criteria['value'];
			$progress = $progressData[$index] ? : 0;
			
			$knownClass = $charAchData ? "ecdAchUnknown" : "ecdAchKnown";
			$img = "";
				
			if ($value == 1)
			{
				if ($progress >= 1)	$knownClass = "";
	
				$img = "<img src='//esoicons.uesp.net/esoui/art/cadwell/check.png' class='ecdAchCriteriaCheck $knownClass'>";
				$output .= "<div class='ecdAchCriteria $knownClass'>$img $name</div>";
			}
			else
			{
				if ($charAchData == null) $progress = $value;
				if ($progress >= $value) $knownClass = "";
				
				$percentWidth = 100;
				if ($value > 0) $percentWidth = intval($progress * 100 / $value);
				
				$progress = $this->FormatAchievementValue($progress);
				$value = $this->FormatAchievementValue($value);
	
				$output .= "<div class='ecdAchCriteria $knownClass'>";
				if ($numCriteria > 1) $output .= "$name<br/>";
				$output .= "<div class='ecdAchStatusBar' style='background-size: $percentWidth% 100%;'><div class='ecdAchStatusBarFrame'></div>";
				$output .= "<div class='ecdAchCriteriaPoints'>$progress/$value</div></div>";
				$output .= "</div>";
			}
		}
	
		$output .= "</div>";
		return $output;
	}
	
	
	public function FormatAchievementValue($value)
	{
		return number_format($value);
	}
	
	
	public function GetAchievementSubBlockHtml($achList)
	{
		global $ESO_ACHIEVEMENT_DATA;
	
		if (count($achList) <= 1) return "";
	
		$output = "<div class='ecdAchList'>";
	
		foreach ($achList as $index => $achId)
		{
			$achData = $ESO_ACHIEVEMENT_DATA[$achId];
			if ($achData == null) continue;
				
			$isKnown = false;
			if ($displayId <= 0) $displayId = $achId;
	
			$charAchData = $this->GetCharAchievementData($achId);
			
			if ($charAchData && $charAchData[1] > 0) $isKnown = true;
			$completeText = "Not Completed";
			$knownClass = $charAchData ? "ecdAchUnknown" : "ecdAchKnown";
				
			if ($isKnown)
			{
				$knownClass = "";
				$displayDate = date("d/m/Y", $charAchData[1]);
				$completeText = "Completed on $displayDate";
			}
				
			$title = $this->escape($achData['name'] . "\nPoints " . $achData['points'] . "\n" . $achData['desc'] . "\n" . $completeText);
				
			$iconUrl = MakeEsoIconLink($achData['icon']);
			$output .= "<div class='ecdAchListItem'>";
			$output .= "<div class='ecdAchSmallIconFrame'><img title=\"$title\" src='$iconUrl' class='$knownClass'></div>";
			$output .= "<br/>{$achData['points']}";
			$output .= "</div>";
		}
	
		$output .= "</div>";
		return $output;
	}
	
	
	public function GetAchievementTreeHtml()
	{
		global $ESO_ACHIEVEMENT_CATEGORIES, $ESO_ACHIEVEMENT_DATA, $ESO_ACHIEVEMENT_TREE;
	
		$output = "";
	
		$output .= "<div class='ecdAchTree1'>";
		$output .= "<div class='ecdAchTreeName1 ecdAchTreeNameHighlight' achcategory='Summary'>";
		$output .= "<img src='//esoicons.uesp.net/esoui/art/treeicons/achievements_indexicon_summary_up.dds'>";
		$output .= "SUMMARY</div></div>";
	
		foreach ($ESO_ACHIEVEMENT_TREE as $catName => $catData)
		{
			$category = $ESO_ACHIEVEMENT_CATEGORIES[$catName];
			$iconUrl = "";
				
			if ($category != null)
			{
				$iconUrl = MakeEsoIconLink($category['icon']);
			}
				
			$displayName = strtoupper($catName);
			$catId = str_replace("'", '', str_replace(' ', '', $catName));
				
			$output .= "<div class='ecdAchTree1'>";
			$output .= "<div class='ecdAchTreeName1' achcategory=\"$catId\"><img src='$iconUrl'>$displayName</div>";
			$output .= "<div class='ecdAchTreeContent1' style='display: none;'>";
				
			foreach ($catData as $subCatName => $subCatData)
			{
				$displayName = $subCatName;
				$subCatId = $catId = str_replace("'", '', str_replace(' ', '', $subCatName));
				$output .= "<div class='ecdAchTreeName2' achsubcategory=\"$subCatId\">$displayName</div>";
			}
				
			$output .= "</div></div>";
		}
	
		return $output;
	}
	
	
	public function CreateOutputHtml()
	{
		$replacePairs = array(
				'{updateDate}' => $this->GetUpdateDate(),
				'{achievementContents}' => $this->GetAchievementContentHtml(),
				'{achievementTree}' => $this->GetAchievementTreeHtml(),
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
	
		return $output;
	}

	
	public function GetOutputHtml()
	{
		if (!$this->dataLoaded)	$this->LoadData();
	
		return $this->CreateOutputHtml();
	}
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
	
		if (!$this->dataLoaded) $this->LoadData();
	
		print($this->CreateOutputHtml());
	}
	
};