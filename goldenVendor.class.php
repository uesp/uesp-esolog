<?php

	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoGetGoldenVendorHtml
{
	public $showAll = false;
	public $showLatestInHistory = false;
	public $showInvalidTimestamps = false;
	
	public $db = null;
	
	public $items = [];
	public $allItems = [];
	public $groupedItems = [];
	public $itemTimestamp = -1;
	public $latestTimestamp = -1;
	public $inputVendor = "golden";
	public $tableName = "goldenVendorItems";
	public $outputName = "golden";
	public $outputTitle = "Golden";
	
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
		
		if (array_key_exists('showall', $this->inputParams)) $this->showAll = intval($this->inputParams['showall']);
		if (array_key_exists('showlatest', $this->inputParams)) $this->$showLatestInHistory = intval($this->inputParams['showlatest']);
		if (array_key_exists('vendor', $this->inputParams)) $this->inputVendor = strtolower($this->inputParams['vendor']);
		
		if ($this->inputVendor == "luxury") 
		{
			$this->tableName = "luxuryVendorItems";
			$this->outputName = "luxury";
			$this->outputTitle = "Luxury";
		}
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
	
	
	private function MakeNameId($name)
	{
		$nameId = strtolower($name);
		+
		$nameId = str_replace(" pauldrons", " shoulders", $nameId);
		$nameId = str_replace(" pauldron", " shoulders", $nameId);
		$nameId = str_replace(" arm cops", " shoulders", $nameId);
		$nameId = str_replace(" epaulets", " shoulders", $nameId);
		$nameId = str_replace(" guise",  " mask", $nameId);
		$nameId = str_replace(" mask",   " mask", $nameId);
		$nameId = str_replace(" visage", " mask", $nameId);
		
		return $nameId;
	}
	
	
	private function TransformName($name)
	{
		$name = str_ireplace(" Pauldrons", " Shoulders", $name);
		$name = str_ireplace(" Pauldron", " Shoulders", $name);
		$name = str_ireplace(" Arm Cops", " Shoulders", $name);
		$name = str_ireplace(" Epaulets", " Shoulders", $name);
		$name = str_ireplace(" Guise",  " Mask", $name);
		$name = str_ireplace(" Mask",   " Mask", $name);
		$name = str_ireplace(" Visage", " Mask", $name);
		
		return $name;
	}
	
	
	private function GroupItems($items)
	{
		$this->groupedItems = [];
		
		foreach ($items as $item)
		{
			$name = $item['name'];
			$nameId = $this->MakeNameId($name);
			
			$groupedItem = &$this->groupedItems[$nameId];
			
			if ($groupedItem == null)
			{
				$this->groupedItems[$nameId] = [];
				$groupedItem = &$this->groupedItems[$nameId];
				
				$groupedItem['items'] = [];
				$groupedItem['prices'] = [];
				$groupedItem['traits'] = [];
				$groupedItem['name'] = $this->TransformName($name);
				$groupedItem['quality'] = $item['quality'];
				$groupedItem['bindType'] = $item['bindType'];
			}
			
			$trait = intval($item['trait']);
			$groupedItem['traits'][$trait] += 1;
			$groupedItem['prices'][$item['price']] += 1;
			$groupedItem['items'][] = $item;
		}
		
		ksort($this->groupedItems);
		
		return $this->groupedItems;
	}
	
	
	private function LoadLatestItems()
	{
		$this->lastQuery = "SELECT max(startTimestamp) as m FROM {$this->tableName};";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to find latest {$this->outputName} vendor timestamp!");
		
		$maxTimestamp = $result->fetch_assoc()['m'];
		if ($maxTimestamp == null || $maxTimestamp <= 0) return $this->ReportError("Error: Failed to find latest {$this->outputName} vendor timestamp!");
		
		$maxTimestamp = intval($maxTimestamp);
		$this->itemTimestamp = $maxTimestamp;
		$this->latestTimestamp = $maxTimestamp;
		
		$this->lastQuery = "SELECT * FROM {$this->tableName} WHERE startTimestamp=$maxTimestamp;";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load {$this->outputName} vendor item data!");
		if ($result->num_rows == 0) return $this->ReportError("No matching {$this->outputName} vendor items found!");
		
		$this->items = [];
		
		while ($row = $result->fetch_assoc())
		{
			$this->items[] = $row;
		}
		
		$this->GroupItems($this->items);
		return true;
	}
	
	
	private function LoadAllItems()
	{
		$this->itemTimestamp = -1;
		$this->lastQuery = "SELECT * FROM {$this->tableName};";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load all {$this->outputName} vendor data!");
		if ($result->num_rows == 0) return $this->ReportError("No {$this->outputName} vendor items found!");
		
		$this->allItems = [];
		$maxTimestamp = 0;
		
		while ($row = $result->fetch_assoc())
		{
			$ts = intval($row['startTimestamp']);
			if ($ts > $maxTimestamp) $maxTimestamp = $ts;
			
			$this->allItems[$ts][] = $row;
		}
		
		$this->latestTimestamp = $maxTimestamp;
		krsort($this->allItems);
		return true;
	}
	
	
	private function OutputHtmlError($msg)
	{
		print("Error: $msg\n");
		print(implode("\n", $this->errorMessages));
		return false;
	}
	
	
	private function GetTraitTexts ($traits)
	{
		$output = "";
		
		foreach ($traits as $trait => $count)
		{
			if ($output) $output .= " / ";
			$output .= GetEsoItemTraitText($trait);
		}
		
		return $output;
	}
	
	
	private function GetItemLinkATag ($item)
	{
		if ($item == null) return "";
		
		$link = $item['link'];
		
		$parsedLink = ParseEsoItemLink($link);
		if ($parsedLink == null) return "";
		
		$itemId = $parsedLink['itemId'];
		if ($itemId == null || $itemId <= 0) return "";
		$itemId = intval($itemId);
		
		$intLevel = intval($parsedLink['level']);
		$intType = intval($parsedLink['subtype']);
		
		return "<a href='https://esolog.uesp.net/itemLink.php?itemid=$itemId&intlevel=$intLevel&inttype=$intType' class='uespesoItemLink eso_item_link' itemid='$itemId' intlevel='$intLevel' inttype='$intType'>";
	}
	
	
	private function FormatPricesHtml($prices)
	{
		$output = "";
		
		$sortedPrices = [];
		
		foreach ($prices as $price => $count)
		{
			$parts = explode(' ', $price, 2);
			$priceType = trim($parts[1]);
			if ($priceType == null) $priceType = $price;
			
			$sortedPrices[$priceType] = $price;
		}
		
		ksort($sortedPrices);
		
		$output = implode(" / ", $sortedPrices);
		$output = str_replace(', ', ' / ', $output);
		$output = $this->EscapeHtml($output);
		
		return $output;
	}
	
	
	private function OutputGroupedItemsHtml($titlePrefix, $groupedItems, $timestamp)
	{
		$output = "";
		
		if ($groupedItems == null) $groupedItems = $this->groupedItems;
		if ($timestamp == null) $timestamp = $this->itemTimestamp;
		
		$formatDate = "";
		$formatDateId = "";
		
		if ($timestamp > 0) 
		{
			$formatDateId = gmdate('Y-m-d', $timestamp + 1);
			$formatDate = gmdate('j F Y', $timestamp + 1);
		}
		
		$output .= "<a name='uespesoGoldenItems_$formatDateId'></a>\n";
		$output .= "<h4>$titlePrefix$formatDate</h4>\n";
		$output .= "<ul class='uespesoGoldenItemList'>\n";
		
		foreach ($groupedItems as $nameId => $groupedItem)
		{
			$traits = $groupedItem['traits'];
			$prices = $groupedItem['prices'];
			$name = $groupedItem['name'];
			$bindType = $groupedItem['bindType'];
			$quality = $groupedItem['quality'];
			$className = "uespesoGoldenItemQuality" . $quality;
			
			$safePrices = $this->FormatPricesHtml($prices);
			$safeName = $this->EscapeHtml($name);
			$safeTraits = $this->GetTraitTexts($traits);
			
			$safeBind = "";
			if ($bindType == 2) $safeBind = " (Bind on Equip)";
			
			$itemLinkAtag = $this->GetItemLinkATag($groupedItem['items'][0]);
			$itemLinkAtagEnd = "";
			if ($itemLinkAtag) $itemLinkAtagEnd = "</a>";
			
			$output .= "<li>";
			
			if ($safeTraits == "")
				$output .= "$itemLinkAtag<span class='uespesoGoldenItem $className'>$safeName</span>$itemLinkAtagEnd -- $safePrices $safeBind";
			else
				$output .= "$itemLinkAtag<span class='uespesoGoldenItem $className'>$safeName ($safeTraits)</span>$itemLinkAtagEnd -- $safePrices $safeBind";
			
			$output .= "</li>\n";
		}
		
		$output .= "</ul>\n";
		return $output;
	}
	
	
	private function OutputLatestItemsHtml($groupedItems = null, $timestamp = null)
	{
		return $this->OutputGroupedItemsHtml("Latest {$this->outputTitle} Vendor Items for ", $this->groupedItems, $this->itemTimestamp);
	}
	
	
	private function OutputAllItemsHtml()
	{
		$output = "<div class='uespesoGoldeVendorHistory'>";
		
		foreach ($this->allItems as $timestamp => $items)
		{
			$groupedItems = $this->GroupItems($items);
			
			if ($timestamp == $this->latestTimestamp && !$this->showLatestInHistory) continue;
			if ($timestamp <= 0 && !$this->showInvalidTimestamps) continue;
			
			$output .= $this->OutputGroupedItemsHtml("Vendor Items for ", $groupedItems, $timestamp);
		}
		
		$output .= "</div>";
		return $output;
	}
	
	
	private function OutputHtml()
	{
		$output = "<div class='uespesoGoldenVendor'>\n";
		
		if ($this->showAll)
			$output .= $this->OutputAllItemsHtml();
		else
			$output .= $this->OutputLatestItemsHtml();
		
		$output .= "</div>\n";
		
		print($output);
		
		return true;
	}
	
	
	public function GetHtml()
	{
		$output = "<div class='uespesoGoldenVendor'>\n";
		
		if ($this->showAll)
		{
			if (!$this->LoadAllItems()) return $this->OutputHtmlError();
			$output .= $this->OutputAllItemsHtml();
		}
		else
		{
			if (!$this->LoadLatestItems()) return $this->OutputHtmlError();
			$output .= $this->OutputLatestItemsHtml();
		}
		
		$output .= "</div>\n";
		
		return $output;
	}
	
	
	public function GetTitleHtml()
	{
		$output = "<h1>UESP:ESO {$this->outputTitle} Vendor Items</h1>\n";
		$output .= "Showing all recorded {$this->outputName} vendor items.\n";
		return $output;
	}
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		
	
		
		if ($this->showAll)
		{
			if (!$this->LoadAllItems()) return $this->OutputHtmlError();
		}
		else
		{
			if (!$this->LoadLatestItems()) return $this->OutputHtmlError();
		}
		
		$this->OutputHtml();
		
		return true;
	}
};