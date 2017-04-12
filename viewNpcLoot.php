<?php 


require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoViewNpcLoot 
{
	public $db = null;
	public $dbReadInitialized = false;
	public $lastQuery = "";
	
	public $htmlTemplate = "";
	
	public $searchResults = array();
	public $npcRecord = array();
	public $zoneQntTotals = array();
	public $itemQntTotals = array();
	public $zoneCountTotals = array();
	public $itemCountTotals = array();
	public $itemLinkData = array();
	
	public $viewNpcName = "";
	public $viewItemName = "";
	
	
	public function __construct ()
	{
		$this->InitDatabase();
		$this->SetInputParams();
		$this->ParseInputParams();
		
		$this->htmlTemplate = file_get_contents(__DIR__."/templates/esoviewnpcloot_template.txt");
	}
	
	
	private function InitDatabase ()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		if ($this->dbReadInitialized) return true;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("Could not connect to mysql database!");
	
		UpdateEsoPageViews("logViews", $this->db);
	
		$this->dbReadInitialized = true;
		return true;
	}
	
	
	public function ReportError ($errorMsg)
	{
		print($errorMsg);
	
		return false;
	}
	
	
	public function SetInputParams ()
	{
		$this->inputParams = $_REQUEST;
	}
	
	
	public function ParseInputParams ()
	{
		if ($this->inputParams['npc'] != null)
		{
			$this->viewNpcName = $this->inputParams['npc'];
		}
		
		if ($this->inputParams['item'] != null)
		{
			$this->viewItemName = $this->inputParams['item'];
		}		
	}
	
	
	public function WriteHeaders ()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
	
		header("content-type: text/html");
	}
	
	
	public function GetNpcSearchQuery()
	{
		$safeName = $this->db->real_escape_string($this->viewNpcName);
		$this->lastQuery = "SELECT * FROM npc WHERE name='$safeName';";
		$result = $this->db->query($this->lastQuery);
		if (!$result || $result->num_rows <= 0) return $this->ReportError("Failed to find NPC matching '{$this->viewNpcName}'!");
				
		$this->npcRecord = $result->fetch_assoc();
		$npcId = $this->npcRecord['id']; 
		
		$query = "SELECT * FROM npcLoot WHERE npcId=$npcId;";
		return $query;
	}
	
	
	public function GetItemSearchQuery()
	{
		$safeName = $this->db->real_escape_string($this->viewItemName);
		
		$query = "SELECT *, npc.name FROM npcLoot LEFT JOIN npc on npcId=npc.id WHERE itemName='$safeName' ORDER BY npc.name, zone, itemName;";
		return $query;
	}
	
	
	public function GetSearchQuery()
	{
		if ($this->viewNpcName != "") return $this->GetNpcSearchQuery();
		if ($this->viewItemName != "") return $this->GetItemSearchQuery();
		
		return "";
	}
	
	
	public function LoadResults()
	{
		$this->lastQuery = $this->GetSearchQuery();
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->ReportError("Failed to load search results!");
		
		$this->searchResults = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$row['itemName'] = MakeEsoTitleCaseName($row['itemName']);
			$this->searchResults[] = $row;
		}
		
		//$this->LoadItemData();
		return true;
	}
	
	
	public function LoadItemData()
	{
		$this->itemLinkData["__gold"] = array(
				'icon' => '/esoui/art/currency/currency_gold_32.dds',
				'quality' => '1',
		);
		
		$this->itemLinkData["__telvar"] = array(
				'icon' => '/esoui/art/currency/currency_telvar_32.dds',
				'quality' => '1',
		);
		
		foreach ($this->searchResults as $i => $result)
		{
			$itemLink = $result['itemLink'];
			if ($itemLink == null || $itemLink == "") continue;
			
			$itemData = &$this->itemLinkData[$itemLink];
			
			if ($itemData != null)
			{
				$this->searchResults[$i]['itemData'] = $itemData;
				continue;				
			}
				
			$matches = ParseEsoItemLink($itemLink);
			if ($matches === false) continue;
			
			$itemId = intval($matches['itemId']);
			$intLevel = intval($matches['level']);
			$intSubtype = intval($matches['subtype']);
			
			$this->lastQuery = "SELECT * FROM minedItem WHERE itemId=$itemId AND internalLevel=$intLevel AND internalSubtype=$intSubtype;";
			$result = $this->db->query($this->lastQuery);
			if (!$result) continue;
			
			if ($result->num_rows == 0)
			{
				$this->lastQuery = "SELECT * FROM minedItem WHERE itemId=$itemId AND internalLevel=1 AND internalSubtype=1;";
				$result = $this->db->query($this->lastQuery);
				if (!$result) continue;
				if ($result->num_rows == 0) continue;
			}
			
			$itemData = $result->fetch_assoc();
			$this->itemLinkData[$itemLink] = $itemData;
			$this->searchResults[$i]['itemData'] = $itemData;				
		}
		
		return true;
	}
	
	
	public function ParseItemResults()
	{
		$totalCount = 1;
		$totalQnt = 1;
		$this->zoneQntTotals = array();
		$this->itemQntTotals = array();
		$this->zoneCountTotals = array();
		$this->itemCountTotals = array();
		$this->itemSummary = array();
		
		foreach ($this->searchResults as $i => $result)
		{
			$itemName = $result['itemName'];
			$itemLink = $result['itemLink'];
			
			if ($itemName == "__totalCount")
			{
				$this->zoneCountTotals[$result['zone']] = $result['count'];
				$this->zoneQntTotals[$result['zone']] = $result['qnt'];
				
				if ($result['zone'] == "")
				{
					$this->zoneCountTotals['All'] = $result['count'];
					$this->zoneQntTotals['All'] = $result['qnt'];
					$this->zoneCountTotals['Summary'] = $result['count'];
					$this->zoneQntTotals['Summary'] = $result['qnt'];
				}
				
				continue;
			}
			elseif ($itemName == "__gold")
			{
				$this->searchResults[$i]['itemName'] = "Gold";
				$itemName = "Gold";
			}
			elseif ($itemName == "__telvar")
			{
				$this->searchResults[$i]['itemName'] = "Telvar";
				$itemName = "Telvar";
			}
			
			if ($this->itemQntTotals[$itemName] == null) 
			{
				$this->itemQntTotals[$itemName] = array();
				$this->itemQntTotals[$itemName]['count'] = 0;
				$this->itemQntTotals[$itemName]['itemName'] = $itemName;
				$this->itemQntTotals[$itemName]['itemLink'] = $result['itemLink'];
				$this->itemQntTotals[$itemName]['icon'] = $result['icon'];
				$this->itemQntTotals[$itemName]['itemType'] = $result['itemType'];
				$this->itemQntTotals[$itemName]['quality'] = $result['quality'];
			}
			
			if ($this->itemCountTotals[$itemName] == null) 
			{
				$this->itemCountTotals[$itemName] = array();
				$this->itemCountTotals[$itemName]['count'] = 0;
				$this->itemCountTotals[$itemName]['itemName'] = $itemName;
				$this->itemCountTotals[$itemName]['itemLink'] = $result['itemLink'];
				$this->itemCountTotals[$itemName]['icon'] = $result['icon'];
				$this->itemCountTotals[$itemName]['itemType'] = $result['itemType'];
				$this->itemCountTotals[$itemName]['quality'] = $result['quality'];
			}
			
			$this->itemQntTotals[$itemName]['count']   += $result['qnt'];
			$this->itemCountTotals[$itemName]['count'] += $result['count'];
				
			if ($result['itemType'] < 0) continue;
			if ($result['quality'] < 0) continue;
			
			$itemType = $result['itemType'];
			$quality = $result['quality'];
			
			if ($this->itemSummary[$itemType] == null) $this->itemSummary[$itemType] = array();
			if ($this->itemSummary[$itemType][$quality] == null) $this->itemSummary[$itemType][$quality] = array( 'count' => 0, 'qnt' => 0, 'numItems' => 0);
			if ($this->itemSummary[$itemType]["all"] == null) $this->itemSummary[$itemType]["all"] = array( 'count' => 0, 'qnt' => 0, 'numItems' => 0);
			
			$this->itemSummary[$itemType][$quality]['count'] += $result['count'];
			$this->itemSummary[$itemType][$quality]['qnt'] += $result['qnt'];
			$this->itemSummary[$itemType][$quality]['numItems'] += 1;
			
			$this->itemSummary[$itemType]["all"]['count'] += $result['count'];
			$this->itemSummary[$itemType]["all"]['qnt'] += $result['qnt'];
			$this->itemSummary[$itemType]["all"]['numItems'] += 1;
		}
		
		if ($this->zoneQntTotals['']   != null) $totalQnt   = $this->zoneQntTotals[''];
		if ($this->zoneCountTotals[''] != null) $totalCount = $this->zoneCountTotals[''];
		if ($totalQnt <= 0) $totalQnt = 1;
		if ($totalCount <= 0) $totalCount = 1;
		
		foreach ($this->searchResults as $i => $result)
		{
			if ($result['itemName'] != "__totalCount")
			{
				$zoneQnt = $this->zoneCountTotals[$result['zone']];
				$this->searchResults[$i]['dropZoneRatio'] = $result['qnt'] / $zoneQnt;
				$this->searchResults[$i]['dropRatio'] = $result['qnt'] / $totalCount;
			}
		}

		foreach ($this->itemQntTotals as $itemName => $qntData)
		{
			$newResult = array();
			$newResult['zone'] = "All";
			$newResult['itemName'] = $qntData['itemName'];
			$newResult['itemLink'] = $qntData['itemLink'];
			
			$newResult['itemType'] = $qntData['itemType'];
			$newResult['quality'] = $qntData['quality'];
			$newResult['icon'] = $qntData['icon'];
			
			$newResult['qnt'] = $qntData['count'];
			$newResult['count'] = $qntData['count'];
			$newResult['dropZoneRatio'] = $qntData['count'] / $totalCount;
			$newResult['dropRatio'] = $newResult['dropZoneRatio'];
			$newResult['totalQnt'] = $totalCount;
			$newResult['totalCount'] = $totalCount;
			
			$this->searchResults[] = $newResult;
		} 
		
		foreach ($this->itemSummary as $itemType => $data1)
		{
			$typeName = GetEsoItemTypeText($itemType);
			
			foreach ($data1 as $quality => $data2)
			{
				if ($quality == "all")
					$qualityName = " (All)";
				else
					$qualityName = " (".GetEsoItemQualityText($quality).")";
				
				$newResult = array();
				
				$newResult['zone'] = "Summary";
				$newResult['itemName'] = "$typeName$qualityName";
				$newResult['itemLink'] = "";
								
				$newResult['itemType'] = $itemType;
				$newResult['quality'] = $quality;
				$newResult['icon'] = "";
				
				$newResult['qnt'] = $data2['qnt'];
				$newResult['count'] = $data2['count'];
				$newResult['dropZoneRatio'] = $data2['qnt'] / $totalCount;
				$newResult['dropRatio'] = $data2['qnt'] / $totalCount;
				$newResult['totalQnt'] = $totalQnt;
				$newResult['totalCount'] = $totalCount;
				
				$this->searchResults[] = $newResult;				
			}			
		}
		
		usort($this->searchResults, array('CEsoViewNpcLoot', 'SortItemSearchResults'));
	}
	
	
	public function SortItemSearchResults($a, $b)
	{
		$zone1 = $a['zone'];
		$zone2 = $b['zone'];
		
		if ($zone1 == "") $zone1 = "ZZZZ";
		if ($zone2 == "") $zone2 = "ZZZZ";
		
		if ($zone1 == "All") $zone1 = " 2";
		if ($zone2 == "All") $zone2 = " 2";
		if ($zone1 == "Summary") $zone1 = " 1";
		if ($zone2 == "Summary") $zone2 = " 1";
		
		$compare = strcasecmp($zone1, $zone2);
		if ($compare != 0) return $compare;
		
		return strcasecmp($a['itemName'], $b['itemName']);
	}
	
	
	public function GetNpcResultsHtml()
	{
		$this->ParseItemResults();
		
		$npcName = $this->npcRecord['name'];
		
		$output = "Showing NPC loot drop data for <b>$npcName</b>:<p>";
		$output .= "<table id='esonplResultsTable'>";
		
		$output .= "<tr>";
		$output .= "<th>Zone</th>";
		$output .= "<th>Item Name</th>";
		$output .= "<th>Item Type</th>";
		$output .= "<th>Qnt</th>";
		$output .= "<th>Total</th>";
		$output .= "<th>Drop Chance</th>";
		$output .= "</tr>";
		
		$totalQnt = 0;
		if ($this->zoneCountTotals[''] != null) $totalQnt = $this->zoneCountTotals[''];
		$lastZone = "";
		
		foreach ($this->searchResults as $result)
		{
			if ($result['itemName'] == "__totalCount") continue;
			
			$zone = $result['zone'];
			$itemLink = $result['itemLink'];
			$itemName = $result['itemName'];
			$qnt = $result['qnt'];
			$totalZoneQnt = $this->zoneCountTotals[$zone];
			$dropChance = round($result['dropZoneRatio'] * 100, 1);
			$itemType = "";
			$quality = "";
			$iconUrl = "";
			if ($result['icon']) $iconUrl = MakeEsoIconLink($result['icon']);
			$itemType = GetEsoItemTypeText($result['itemType']);
			$quality = $result['quality'];
			
			if ($lastZone != $zone && ($lastZone == "All" || $lastZone == "Summary"))
			{
				$output .= "<tr>";
				$output .= "<td colspan='6'></td>";
				$output .= "</tr>";
			}
			
			if (substr($itemLink, 2) != "|H")
			
			$output .= "<tr>";
			$output .= "<td>$zone</td>";
			
			if ($iconUrl == "")
				$output .= "<td><div class='esonplItemLink eso_item_link_q$quality' itemlink='$itemLink'>$itemName</div></td>";
			elseif (substr($itemLink, 0, 2) != "|H")
				$output .= "<td><div class='esonplItemLink eso_item_link_q$quality' itemlink='$itemLink'><img src='$iconUrl' class='esonplItemIcon'>$itemName</div></td>";
			else
				$output .= "<td><div class='eso_item_link esonplItemLink eso_item_link_q$quality' itemlink='$itemLink'><img src='$iconUrl' class='esonplItemIcon'>$itemName</div></td>";
			
			$output .= "<td>$itemType</td>";
			$output .= "<td>$qnt</td>";
			$output .= "<td>$totalZoneQnt</td>";
			$output .= "<td>$dropChance%</td>";
			$output .= "</tr>";
			
			$lastZone = $zone;
		}
		
		$output .= "</table>";
		return $output;
	}
	
	
	public function GetItemResultsHtml() 
	{
		$output = "<table class='esonplResults'>";
		
		$output .= "</table>";
		return $output;
	}
	
	
	public function GetResultsHtml()
	{
		if ($this->viewNpcName != "") return $this->GetNpcResultsHtml();
		if ($this->viewItemName != "") return $this->GetItemResultsHtml();
		
		return "No NPC or item specified!";
	}
	
	
	public function GetOutputHtml()
	{
		$replacePairs = array(
				'{npcLootResults}' => $this->GetResultsHtml(),
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);

		return $output;
	}
	
		
	public function Render()
	{
		$this->WriteHeaders();
		
		$this->LoadResults();
		
		print($this->GetOutputHtml());
	}
	
};


$viewNpcLoot = new CEsoViewNpcLoot();
$viewNpcLoot->Render();
