<?php
/*
 * getLeadHtml.php -- by Dave Humphrey (dave@uesp.net), May 2022
 * 
 * Returns HTML fragments of Antiquity Lead information.
 * 
 * TODO:
 *
 */

	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoGetLeadHtml
{
	public $ITEM_TABLE_SUFFIX = "";
	
		// Grab list by running ListItemIds()
	public $ITEMIDS = [
		"Thrassian Stranglers" => 164291,
		"Snow Treaders" => 165879,
		"Ring of the Wild Hunt" => 163052,
		"Torc of Tonal Constancy" => 163451,
		"Malacath's Band of Brutality" => 165880,
		"Bloodlord's Embrace" => 165899,
		"Ring of the Pale Order" => 171436,
		"Pearls of Ehlnofey" => 171437,
		"Death Dealer's Fete" => 175527,
		"Shapeshifter's Chain" => 175528,
		"Harpooner's Wading Kilt" => 175524,
		"Gaze of Sithis" => 175525,
		"Daedric Enchanting Station" => 182302,
		"Spaulder of Ruin" => 181695,
		"Markyn Ring of Majesty" => 182208,
		"Belharza's Band" => 182209,
		"Dov-rha Sabatons" => 187655,
		"Mora's Whispers" => 187654,
		"Lefthander's War Girdle" => 187656,
		"Sea-Serpent's Coil" => 187657,
		"Oakensoul Ring" => 187658,
		"Druidic Provisioning Station" => 187802,
	];
	
	public $inputCategory = "";
	public $inputSet = "";
	public $onlyMythic = true;
	public $includeLore = true;
	public $outputBySet = true;
	public $includeImage = true;
	public $includeIcon = false;
	public $includeIndex = true;
	public $oneTable = true;
	
	public $db = null;
	public $leads = [];
	public $leadSets = [];
	public $leadNotes = [];
	public $leadCategories = [];
	
	public $errorMessages = [];
	
	
	public function __construct()
	{
		$this->ParseInputParams();
		$this->InitDatabase();
	}
	
	
	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		$this->errorMessages[] = $errorMsg;
		return false;
	}
	
	
	private function ParseInputParams ()
	{
		$this->inputParams = $_REQUEST;
		
		if (array_key_exists('set', $this->inputParams)) $this->inputSet = $this->inputParams['set'];
		if (array_key_exists('cat', $this->inputParams)) $this->inputCategory = $this->inputParams['cat'];
		if (array_key_exists('category', $this->inputParams)) $this->inputCategory = $this->inputParams['category'];
		if (array_key_exists('mythic', $this->inputParams)) $this->onlyMythic = (intval($this->inputParams['mythic']) != 0);
		if (array_key_exists('lore', $this->inputParams)) $this->includeLore = (intval($this->inputParams['lore']) != 0);
		if (array_key_exists('image', $this->inputParams)) $this->includeImage = (intval($this->inputParams['image']) != 0);
		if (array_key_exists('icon', $this->inputParams)) $this->includeIcon = (intval($this->inputParams['icon']) != 0);
		if (array_key_exists('index', $this->inputParams)) $this->includeIndex = (intval($this->inputParams['index']) != 0);
		if (array_key_exists('onetable', $this->inputParams)) $this->oneTable = (intval($this->inputParams['onetable']) != 0);
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		return true;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
		
		header("Access-Control-Allow-Origin: *");
	}
	
	
	private function MakeId($name)
	{
		$id = preg_replace('/ /', '_', trim($name));
		$id = preg_replace('/[^A-Za-z0-9_-]+/', '', $id);
		return strtolower($id);
	}
	
	
	private function MakeIconUrl($icon)
	{
		$icon = str_replace(".dds", ".png", $icon);
		$icon = $this->EscapeHtml($icon);
		return "https://esoicons.uesp.net" . $icon;
	}
	
	
	private function EscapeHtml($string)
	{
		return htmlspecialchars($string);
	}
	
	
	private function LoadLeadNotes()
	{
		$this->lastQuery = "SELECT * FROM antiquityLeadNotes;";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load lead note data!");
		if ($result->num_rows == 0) return true;
		
		while ($row = $result->fetch_assoc())
		{
			$id = $row['leadName'];
			$this->leadNotes[$id] = $row;
		}
		
		return true;
	}
	
	
	private function LoadLeads()
	{
		$where = [];
		
		if ($this->onlyMythic) $where[] = "setName !=''";
		
		if ($this->inputSet)
		{
			$safeSet = $this->db->real_escape_string($this->inputSet);
			$where[] = "setName='$safeSet'";
		}
		
		if ($this->inputCategory)
		{
			$safeCate = $this->db->real_escape_string($this->inputCategory);
			$where[] = "categoryName='$safeCate'";
		}
		
		if (count($where) > 0)
		{
			$whereConds = implode(" AND ", $where);
			$this->lastQuery = "SELECT * FROM antiquityLeads WHERE $whereConds;";
		}
		else
		{
			$this->lastQuery = "SELECT * FROM antiquityLeads;";
		}
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load lead data!");
		if ($result->num_rows == 0) return $this->ReportError("No matching leads found!");
		
		while ($row = $result->fetch_assoc())
		{
			$id = intval($row['id']);
			$this->leads[$id] = $row;
			
			$set = $row['setName'];
			$category = $row['categoryName'];
			
			if ($this->leadSets[$set] == null) $this->leadSets[$set] = [];
			$this->leadSets[$set][] = $id;
			
			if ($this->leadCategories[$category] == null) $this->leadCategories[$category] = [];
			$this->leadCategories[$category][] = $id;
		}
		
		return true;
	}
	
	
	private function OutputHtmlError($msg)
	{
		print("Error: $msg\n");
		print(implode("\n", $this->errorMessages));
		return false;
	}
	
	
	static function compareLeadsByName($a, $b)
	{
		return strcmp($a['name'], $b['name']);
	}
	
	
	private function ListItemIds()
	{
		print("Listing all itemIds for lead sets...\n");
		
		foreach ($this->leadSets as $setName => $leadIds)
		{
			$itemId = $this->GetItemItemId($setName);
			
			if ($itemId > 0)
			{
				print("\t\"$setName\" => $itemId,\n");
			}
		}
	}
	
	
	private function GetItemItemId ($itemName)
	{
		$safeName = $this->db->real_escape_string($itemName);
		$this->lastQuery = "SELECT itemId FROM minedItemSummary{$this->ITEM_TABLE_SUFFIX} WHERE name='$safeName';";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return -1;
		if ($result->num_rows == 0) return -1;
		
		$itemData = $result->fetch_assoc();
		$itemId = $itemData['itemId'];
		
		return intval($itemId);
	}
	
	
	private function GetItemTooltipImageUrl ($itemName)
	{
		$itemId = $this->ITEMIDS[$itemName];
		if ($itemId) return "https://esolog.uesp.net/itemLinkImage.php?itemid=$itemId&version={$this->ITEM_TABLE_SUFFIX}";
		return "";
		
				// This is a little slow due to a lack of index by name
		$safeName = $this->db->real_escape_string($itemName);
		$this->lastQuery = "SELECT itemId FROM minedItemSummary{$this->ITEM_TABLE_SUFFIX} WHERE name='$safeName';";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return "";
		if ($result->num_rows == 0) return "";
		
		$itemData = $result->fetch_assoc();
		$itemId = $itemData['itemId'];
		
		return "https://esolog.uesp.net/itemLinkImage.php?itemid=$itemId&version={$this->ITEM_TABLE_SUFFIX}";
	}
	
	
	private function GetItemWikiArticleUrl ($itemName)
	{
		$itemName = ucwords($itemName, " \t\r\n\f\v-");
		$itemName = $this->EscapeHtml($itemName);
		return "https://en.uesp.net/wiki/Online:$itemName";
	}
	
	
	private function GetLeadNotes ($leadName)
	{
		$notes = $this->leadNotes[$leadName];
		if ($notes == null) return "";
		
		$note = $notes['note'];
		$note = str_replace("\xA0", " ", $note);
		
		return $note;
	}
	
	
	private function GetSetIndexHtml($setKeys)
	{
		$output = "<ol class='uespEsoSetIndex'>\n";
		
		foreach ($setKeys as $setName)
		{
			$nameId = $this->MakeId($setName);
			$setName = $this->EscapeHtml($setName);
			$output .= "<li><a href=\"#$nameId\">$setName</a></li>\n";
		}
		
		$output .= "</ol>\n";
		return $output;
	}
	
	
	private function OutputHtml()
	{
		$output = "";
		
		$sortedSetNames = array_keys($this->leadSets);
		sort($sortedSetNames);
		
		if ($this->includeIndex)
		{
			$output .= $this->GetSetIndexHtml($sortedSetNames);
			$output .= "<p></br>\n";
		}
		
		$output .= "<table class='uespEsoLeadsTable'>\n";
		
		foreach ($sortedSetNames as $setName)
		{
			$leadIds = $this->leadSets[$setName];
			if ($leadIds == null) continue;
			
			$setIcon = "";
			if ($leadIds[0]) 
			{
				$lead = $this->leads[$leadIds[0]];
				if ($lead) $setIcon = $this->MakeIconUrl($lead['setIcon']);
			}
			
			$safeSetName = $this->EscapeHtml($setName);
			$nameId = $this->MakeId($setName);
			$output .= "\n<tr><th class='uespEsoLeadSetName' colspan='20'>";
			$output .= "<a name='$nameId'></a>$safeSetName";
			if ($this->includeIcon) $output .= " <img src='$setIcon' />";
			
			if ($this->includeImage)
			{
				$iconUrl = $this->GetItemTooltipImageUrl($setName);
				$articleUrl = $this->GetItemWikiArticleUrl($setName);
				if ($iconUrl != "") $output .= "</th></tr><tr><th colspan='20' class='uespEsoLeadSetTooltip'><a href=\"$articleUrl\"><img src=\"$iconUrl\" /></a>";
			}
			
			$output .= "</th></tr>\n";
			
			$leads = [];
			
			foreach ($leadIds as $leadId)
			{
				$lead = $this->leads[$leadId];
				if ($lead == null) continue;
				
				$leads[] = $lead;
			}
			
			usort($leads, [CEsoGetLeadHtml::class, compareLeadsByName]);
			
			foreach ($leads as $lead)
			{
				$note = $this->EscapeHtml($this->GetLeadNotes($lead['name']));
				if ($note == "") $note = "Unknown";
				
				$name = $this->EscapeHtml($lead['name']);
				$icon = $this->MakeIconUrl($lead['icon']);
				
				$loreDesc1 = $this->EscapeHtml($lead['loreDescription1']);
				$loreDesc2 = $this->EscapeHtml($lead['loreDescription2']);
				$loreDesc3 = $this->EscapeHtml($lead['loreDescription3']);
				$loreDesc4 = $this->EscapeHtml($lead['loreDescription4']);
				$loreDesc5 = $this->EscapeHtml($lead['loreDescription5']);
				
				$loreName1 = $this->EscapeHtml($lead['loreName1']);
				$loreName2 = $this->EscapeHtml($lead['loreName2']);
				$loreName3 = $this->EscapeHtml($lead['loreName3']);
				$loreName4 = $this->EscapeHtml($lead['loreName4']);
				$loreName5 = $this->EscapeHtml($lead['loreName5']);
				
				$output .= "<tr>";
				
				$output .= "<td>$name</td>";
				$output .= "<td><img src='$icon' /></td>";
				
				$output .= "<td>$note</td>";
				
				if ($this->includeLore)
				{
					$output .= "<td>";
					if ($loreDesc1) $output .= "$loreName1 -- $loreDesc1<br/>";
					if ($loreDesc2) $output .= "$loreName1 -- $loreDesc2<br/>";
					if ($loreDesc3) $output .= "$loreName1 -- $loreDesc3<br/>";
					if ($loreDesc4) $output .= "$loreName1 -- $loreDesc4<br/>";
					if ($loreDesc5) $output .= "$loreName1 -- $loreDesc5<br/>";
					
					$output .= "</td>";
				}
				
				$output .= "</tr>\n";
			}
		}
		
		$output .= "</table>\n";
		
		print($output);
		
		return true;
	}
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		
		if (!$this->LoadLeads()) return $this->OutputHtmlError();
		$this->LoadLeadNotes();
		
		$this->OutputHtml();
		
		//$this->ListItemIds();
		
		return true;
	}
};


$lead = new CEsoGetLeadHtml();
$lead->Render();
