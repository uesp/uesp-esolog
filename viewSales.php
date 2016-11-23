<?php 


// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esosalesdata.secrets");
require_once(__DIR__."/esoCommon.php");


class EsoViewSalesData
{
	const ESOVSD_ICON_URL = UESP_ESO_ICON_URL;
	const ESOVSD_ICON_UNKNOWN = "unknown.png";
		
	public $ESOVSD_HTML_TEMPLATE = "";
	public $ESOVSD_HTML_TEMPLATE_EMBED = "";
	
	public $server = "na";
	public $isEmbedded = false;

	public $db = null;
	private $dbReadInitialized  = false;
	private $dbWriteInitialized = false;
	public $lastQuery = "";
	
	public $htmlTemplate = "";
	
	public $formValues = array();
	public $finalItemLevel = -1;
	public $finalItemQuality = -1;
	
	public $guildData = array();
	public $searchCount = 0;
	public $itemResults = array();
	public $searchResults = array();
	public $searchLimitCount = 500;
	public $searchItemIdsLimit = 1000;
	
	public $itemQuery = "";
	public $salesQuery = "";
	
	public $errorMessages = array();
	
	static public $ESOVSD_TRAITS = array();
	static public $ESOVSD_QUALITIES = array();
	static public $ESOVSD_ITEMTYPES = array();
	static public $ESOVSD_EQUIPTYPES = array();
	static public $ESOVSD_ARMORTYPES = array();
	static public $ESOVSD_WEAPONTYPES = array();
	

	public function __construct ()
	{
		global $ESO_ITEMTRAIT10_TEXTS, $ESO_ITEMTYPE_TEXTS, $ESO_ITEMEQUIPTYPE_TEXTS;
		global $ESO_ITEMARMORTYPE_TEXTS, $ESO_ITEMWEAPONTYPE_TEXTS, $ESO_ITEMQUALITY_TEXTS;
		
		$this->ESOVSD_HTML_TEMPLATE = __DIR__."/templates/esosales_template.txt";
		$this->ESOVSD_HTML_TEMPLATE_EMBED = __DIR__."/templates/esosales_embed_template.txt";
		
		self::$ESOVSD_TRAITS = self::MakeUniqueArray($ESO_ITEMTRAIT10_TEXTS);
		self::$ESOVSD_QUALITIES = self::MakeUniqueArray($ESO_ITEMQUALITY_TEXTS, true);
		self::$ESOVSD_ITEMTYPES = self::MakeUniqueArray($ESO_ITEMTYPE_TEXTS);
		self::$ESOVSD_EQUIPTYPES = self::MakeUniqueArray($ESO_ITEMEQUIPTYPE_TEXTS);
		self::$ESOVSD_ARMORTYPES = self::MakeUniqueArray($ESO_ITEMARMORTYPE_TEXTS);
		self::$ESOVSD_WEAPONTYPES = self::MakeUniqueArray($ESO_ITEMWEAPONTYPE_TEXTS);
		
		array_unshift(self::$ESOVSD_TRAITS, "(none)");
	
		$this->InitDatabaseRead();

		$this->SetInputParams();
		$this->ParseInputParams();
	}
	
	
	private static function MakeUniqueArray($src, $noSort = false)
	{
		$newArray = array_unique($src);
		if (!$noSort) sort($newArray);
		return $newArray;
	}


	public function ReportError ($errorMsg)
	{
		error_log($errorMsg);

		if ($this->db != null && $this->db->error)
		{
			error_log("\tDB Error:" . $this->db->error);
			error_log("\tLast Query:" . $this->lastQuery);
		}
		return false;
	}


	private function InitDatabaseRead ()
	{
		global $uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase;
		global $uespEsoSalesDataDatabaseNA, $uespEsoSalesDataDatabaseEU;

		$database = $uespEsoSalesDataDatabaseNA;
		if ($this->server == "eu" || $this->server == "EU") $database = $uespEsoSalesDataDatabaseEU;

		if ($this->dbReadInitialized) return true;

		$this->db = new mysqli($uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $database);
		if ($db->connect_error) return $this->ReportError("Could not connect to mysql database!");

		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = false;
		
		return true;
	}


	private function ParseInputParams ()
	{
		$this->ParseFormParam('text');
		$this->ParseFormParam('trait');
		$this->ParseFormParam('quality');
		$this->ParseFormParam('itemtype');
		$this->ParseFormParam('equiptype');
		$this->ParseFormParam('armortype');
		$this->ParseFormParam('weapontype');
		$this->ParseFormParam('level');
		
		$this->finalItemQuality = $this->GetItemQualityValue($this->formValues['quality']);
		$this->finalItemLevel   = $this->GetItemLevelValue($this->formValues['level']);
		if ($this->finalItemLevel == 0) $this->finalItemLevel = -1;
		
		return true;
	}
	
	
	private function ParseFormParam($name)
	{
		$id = $name;
	
		if (array_key_exists($id, $this->inputParams))
		{
			$this->formValues[$name] = $this->inputParams[$id];
			return true;
		}
	
		return false;
	}


	private function SetInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
	}


	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		
		$origin = $_SERVER['HTTP_ORIGIN'];
		
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}


	public function LoadTemplate()
	{
		$templateFile = "";
		
		if ($this->isEmbedded)
			$templateFile .= $this->ESOVSD_HTML_TEMPLATE_EMBED;
		else
			$templateFile .= $this->ESOVSD_HTML_TEMPLATE;
			
		$this->htmlTemplate = file_get_contents($templateFile);
	}
	
	
	public function GetFormValue($id, $default = "")
	{
		if (!array_key_exists($id, $this->formValues)) return $default;
		return htmlspecialchars($this->formValues[$id]);
	}
	
	
	public function GetGeneralListHtml($listArray, $formName)
	{
		$output = "";
		$selectedValue = $this->GetFormValue($formName);
	
		foreach ($listArray as $value)
		{
			if ($value == $selectedValue)
				$selected = "selected";
			else
				$selected = "";
	
			$output .= "<option value='$value' $selected>$value</option>";
		}
	
		return $output;
	}
	
	
	public function GetItemTraitValue($text)
	{
		global $ESO_ITEMTRAIT_TEXTS, $ESO_ITEMTRAIT10_TEXTS;
	
		$value = array_search($text, $ESO_ITEMTRAIT10_TEXTS);
	
		if ($value === FALSE) return -1;
		return $value;
	}
	
	
	public function GetItemQualityValue($text)
	{
		global $ESO_ITEMQUALITY_TEXTS;
	
		$value = array_search($text, $ESO_ITEMQUALITY_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetItemLevelValue($text)
	{
		$result = preg_match("/v[0-9]+/i", $text);
		$result1 = preg_match("/cp[0-9]+/i", $text);
		$level = 0;
	
		if ($result)
		{
			$level = intval(substr($text, 1)) + 50;
		}
		else if ($result1)
		{
			$level = floor(intval(substr($text, 2))/10) + 50;
		}
		else
		{
			$level = intval($text);
		}
	
		if ($level < 0)
			$level = 0;
		else if ($level > 66)
			$level = 66;
					
		return $level;
	}
	
		
	public function GetItemTypeValue($text)
	{
		global $ESO_ITEMTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetEquipTypeValue($text)
	{
		global $ESO_ITEMEQUIPTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMEQUIPTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetWeaponTypeValue($text)
	{
		global $ESO_ITEMWEAPONTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMWEAPONTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetArmorTypeValue($text)
	{
		global $ESO_ITEMARMORTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMARMORTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetOutputFormLevel()
	{
		$level = $this->GetItemLevelValue($this->formValues['level']);
		if ($level <= 0) return "";
	
		if ($level > 50)
		{
			if (UESP_SHOWCPLEVEL) return "CP" . (($level - 50)*10);
			return "v" . ($level - 50);
		}
	
		return (string) $level;
	}
	
	
	public function GetIconUrl($icon)
	{
		if ($icon == null || $icon == "") $icon = self::ESOVSD_ICON_UNKNOWN;
	
		$icon = preg_replace('/dds$/', 'png', $icon);
		$icon = preg_replace('/^\//', '', $icon);
	
		$iconLink = self::ESOVSD_ICON_URL . '/' . $icon;
		return $iconLink;
	}
	
	
	public function GetSearchResultsHtml()
	{
		$output = "<table id='esovsd_searchresults_table'>";
		
		foreach ($this->searchResults as $row)
		{
			$guild = $this->guildData[$row['guildId']];
			$item = $this->itemResults[$row['itemId']];
			$buyDate = gmdate("Y-m-d H:i:s", $row['buyTimestamp']);
			$iconURL = $this->GetIconUrl($item['icon']);
			
			$output .= "<tr>";
			$output .= "<td></td>";
			$output .= "<td><div class='esovsd_itemlink eso_item_link' itemlink='{$row['itemLink']}'>";
			$output .= "<img src='$iconURL' class='esovsd_itemicon'>{$item['name']}</div></td>";
			$output .= "<td>{$guild['name']}</td>";
			$output .= "<td>{$row['sellerName']}</td>";
			$output .= "<td>{$row['buyerName']}</td>";
			$output .= "<td>{$row['price']}</td>";
			$output .= "<td>{$row['qnt']}</td>";
			$output .= "<td>{$buyDate}</td>";
			$output .= "</tr>\n";
		}
	
		$output .= "</table>";
		return $output;
	}
	
	
	public function GetSearchItemIdsQuery()
	{
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM items ";
		$where = array();
		
		if ($this->finalItemLevel   >= 1) $where[] = "level=".$this->finalItemLevel;
		if ($this->finalItemQuality >= 0) $where[] = "quality=".$this->finalItemQuality;
		
		$traitValue = $this->GetItemTraitValue($this->formValues['trait']);
		if ($traitValue > 0) $where[] = "trait=".$traitValue;
		
		$equipTypeValue = $this->GetEquipTypeValue($this->formValues['equiptype']);
		if ($equipTypeValue > 0) $where[] = "equipType=".$equipTypeValue;
		
		$itemTypeValue = $this->GetItemTypeValue($this->formValues['itemtype']);
		if ($itemTypeValue > 0) $where[] = "itemType=".$itemTypeValue;
		
		$weaponTypeValue = $this->GetWeaponTypeValue($this->formValues['weapontype']);
		if ($weaponTypeValue > 0) $where[] = "weaponType=".$weaponTypeValue;
		
		$armorTypeValue = $this->GetArmorTypeValue($this->formValues['armortype']);
		if ($armorTypeValue > 0) $where[] = "armorType=".$armorTypeValue;
		
		if ($this->formValues['text'] != "")
		{
			$safeText = $this->db->real_escape_string($this->formValues['text']);
			//$where[] = "MATCH(name, setName) AGAINST ('$safeText' in BOOLEAN MODE)";
			$where[] = "(name LIKE '%$safeText%' or setName LIKE '%$safeText%')";
		}
		
		if (count($where) > 0) $query .= "WHERE " . implode(" AND ", $where);
		$query .= " LIMIT " . $this->searchItemIdsLimit . ";";
		
		$this->itemQuery = $query;
		return $query;
	}
	
	
	public function GetSearchItemIds()
	{		
		$itemIds = array();
		
		$this->lastQuery = $this->GetSearchItemIdsQuery();
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $itemIds;
		
		if ($result->num_rows == 0)
		{
			$this->errorMessages[] = "No items found matching input search!";
			return $itemIds;
		}
		
		while (($row = $result->fetch_assoc()))
		{
			$this->itemResults[$row['id']] = $row;
			$itemIds[] = $row['id'];
		}
		
		if ($result->num_rows >= $this->searchItemIdsLimit)
		{
			$result = $this->db->query("SELECT FOUND_ROWS() as rowCount;");
			
			if ($result)
			{
				$row = $result->fetch_assoc();
				$totalItems = $row['rowCount'];
				$this->errorMessages[] = "Found $totalItems matching items which exceeds the maximum of {$this->searchItemIdsLimit}. Try using a better search to limit results.";
			}
		}
		
		return $itemIds;
	}
	
	
	public function LoadGuilds()
	{
		$this->lastQuery = "SELECT * FROM guilds;";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to load guild data!");

		while (($row = $result->fetch_assoc()))
		{
			$this->guildData[$row['id']] = $row;
		}
		
		return true;
	}
	
	
	public function GetSearchQuery()
	{
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM sales ";
		$where = array();
		
		$itemIds = $this->GetSearchItemIds();
		
		if (count($itemIds) <= 0) 
			$where[] = "0"; 
		else
			$where[] = "itemId IN (" . implode(",", $itemIds) . ")";
		
		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}
		
		$query .= " LIMIT {$this->searchLimitCount};";
		
		$this->salesQuery = $query;
		return $query;
	}
		
	
	public function LoadSearchResults()
	{
		$this->lastQuery = $this->GetSearchQuery();
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to search for sales data!");
		
		$this->searchCount = $result->num_rows;
		
		if ($this->searchCount == 0)
		{
			$this->errorMessages[] = "No matching sales found!";			
			return true;
		}
		
		while (($row = $result->fetch_assoc()))
		{
			$this->searchResults[] = $row;
		}
		
		if ($result->num_rows >= $this->searchLimitCount)
		{
			$result = $this->db->query("SELECT FOUND_ROWS() as rowCount;");
				
			if ($result)
			{
				$row = $result->fetch_assoc();
				$totalSales = $row['rowCount'];
				$this->errorMessages[] = "Found $totalSales matching sales which exceeds the maximum of {$this->searchLimitCount}. Try using a better search to limit results.";
			}
		}
		
		return true;
	}
	
	
	public function GetErrorMessagesHtml()
	{
		if (count($this->errorMessages) <= 0) return "";
		$output = "<div id='esovsd_errormessages'>";
		
		foreach ($this->errorMessages as $errorMsg)
		{
			$output .= "<div class='esovsd_errormsg'>$errorMsg</div>";	
		}		
		
		$output .= "</div>";
		return $output;
	}
	
	
	public function CreateOutputHtml()
	{
		$replacePairs = array(
				'{formText}' => $this->GetFormValue('text'),
				'{formTrait}' => $this->GetFormValue('trait'),
				'{formQuality}' => $this->GetFormValue('quality'),
				'{formItemType}' => $this->GetFormValue('itemtype'),
				'{formEquipType}' => $this->GetFormValue('equiptype'),
				'{formArmorType}' => $this->GetFormValue('armortype'),
				'{formWeaponType}' => $this->GetFormValue('weapontype'),
				'{formLevel}' => $this->GetOutputFormLevel(),
				
				'{listTrait}' => $this->GetGeneralListHtml(self::$ESOVSD_TRAITS, 'trait'),
				'{listQuality}' => $this->GetGeneralListHtml(self::$ESOVSD_QUALITIES, 'quality'),
				'{listItemType}' => $this->GetGeneralListHtml(self::$ESOVSD_ITEMTYPES, 'itemtype'),
				'{listEquipType}' => $this->GetGeneralListHtml(self::$ESOVSD_EQUIPTYPES, 'equiptype'),
				'{listArmorType}' => $this->GetGeneralListHtml(self::$ESOVSD_ARMORTYPES, 'armortype'),
				'{listWeaponType}' => $this->GetGeneralListHtml(self::$ESOVSD_WEAPONTYPES, 'weapontype'),
				
				'{searchResults}' => $this->GetSearchResultsHtml(),
				'{errorMessages}' => $this->GetErrorMessagesHtml(),
				'{itemQuery}' => $this->itemQuery,
				'{salesQuery}' => $this->salesQuery,
				
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function GetOutputHtml()
	{
		$this->LoadTemplate();
		$this->LoadGuilds();
		$this->LoadSearchResults();

		return $this->CreateOutputHtml();
	}
	
	
	public function Render()
	{
		$output = $this->GetOutputHtml();
		print ($output);
	}
	
};


$viewSales = new EsoViewSalesData();
$viewSales->Render();